<?php

namespace Project\Models;

use Project\Core\Database;
use Project\Core\ReservationSchedule;
use Project\Entities\Reservation;
use Project\Entities\ReservationPrestation;
use PDO;

class ReservationModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // -------------------------------------------------------------------------
    // READ
    // -------------------------------------------------------------------------

    /**
     * Retourne toutes les réservations avec les infos utilisateur et chien.
     * Utilisé par l'admin.
     */
    public function getAllReservations(): array
    {
        $stmt = $this->db->query(
            'SELECT r.*,
                    u.nom          AS user_nom,
                    u.prenom       AS user_prenom,
                    u.email        AS user_email,
                    u.adresse      AS user_adresse,
                    u.code_postal  AS user_code_postal,
                    u.ville        AS user_ville,
                    c.nom        AS chien_nom,
                    c.nom_race   AS chien_race
             FROM reservation r
             JOIN user_dog u ON r.id_utilisateur_PK = u.id_utilisateur_PK
             JOIN chien    c ON r.id_chien_PK       = c.id_chien_PK
             ORDER BY r.date_rdv DESC, r.heure_rdv DESC'
        );
        return $this->attachPrestations(array_map(fn($row) => Reservation::fromArray($row), $stmt->fetchAll()));
    }

    /**
     * Retourne les réservations d'un utilisateur avec les infos du chien.
     */
    public function getReservationsByUserId(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.*,
                    c.nom      AS chien_nom,
                    c.nom_race AS chien_race
             FROM reservation r
             JOIN chien c ON r.id_chien_PK = c.id_chien_PK
             WHERE r.id_utilisateur_PK = :userId
             ORDER BY r.date_rdv DESC, r.heure_rdv DESC'
        );
        $stmt->execute([':userId' => $userId]);
        return $this->attachPrestations(array_map(fn($row) => Reservation::fromArray($row), $stmt->fetchAll()));
    }

    /**
     * Retourne une réservation par son id, avec les infos utilisateur et chien
     * (nécessaires notamment pour l'envoi de l'email de confirmation par l'admin).
     */
    public function getReservationById(int $id): ?Reservation
    {
        $stmt = $this->db->prepare(
            'SELECT r.*,
                    u.nom          AS user_nom,
                    u.prenom       AS user_prenom,
                    u.email        AS user_email,
                    u.adresse      AS user_adresse,
                    u.code_postal  AS user_code_postal,
                    u.ville        AS user_ville,
                    c.nom        AS chien_nom,
                    c.nom_race   AS chien_race
             FROM reservation r
             JOIN user_dog u ON r.id_utilisateur_PK = u.id_utilisateur_PK
             JOIN chien    c ON r.id_chien_PK       = c.id_chien_PK
             WHERE r.id_rdv = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->attachPrestations([Reservation::fromArray($row)])[0] : null;
    }

    /**
     * Charge en une requête les prestations des réservations fournies.
     *
     * @param Reservation[] $reservations
     * @return Reservation[]
     */
    private function attachPrestations(array $reservations): array
    {
        if ($reservations === []) {
            return $reservations;
        }

        $parId = [];
        foreach ($reservations as $reservation) {
            $parId[$reservation->id] = $reservation;
        }
        $placeholders = implode(',', array_fill(0, count($parId), '?'));
        $stmt = $this->db->prepare(
            "SELECT id_rdv_FK, code, libelle, type, prix, evalue_sur_place
             FROM reservation_prestation
             WHERE id_rdv_FK IN ($placeholders)
             ORDER BY id_rdv_FK, CASE WHEN type = 'principale' THEN 0 ELSE 1 END, id"
        );
        $stmt->execute(array_keys($parId));
        foreach ($stmt->fetchAll() as $row) {
            $parId[(int) $row['id_rdv_FK']]->prestations[] = ReservationPrestation::fromArray($row);
        }

        return $reservations;
    }

    /**
     * Compte le nombre total de réservations.
     */
    public function countReservations(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM reservation')->fetchColumn();
    }

    /**
     * Compte les réservations ayant un statut donné.
     */
    public function countByStatus(string $statut): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM reservation WHERE statut = :statut'
        );
        $stmt->execute([':statut' => $statut]);
        return (int) $stmt->fetchColumn();
    }

    // -------------------------------------------------------------------------
    // CREATE
    // -------------------------------------------------------------------------

    /**
     * Crée une réservation et ses prestations (dans une même transaction) et retourne son id.
     *
     * @param ReservationPrestation[] $prestations
     */
    public function createReservation(
        int    $userId,
        int    $idChien,
        string $dateRdv,
        string $heureRdv,
        array  $creneauxSession = [],
        ?string $informations = null,
        ?string $gabarit = null,
        array  $prestations = [],
        ?float $prixEstime = null
    ) {
        $session = ReservationSchedule::sessionForTime($heureRdv);
        $slots = ReservationSchedule::slotsForSession($session);
        if ($creneauxSession !== [] && $creneauxSession !== $slots) {
            throw new \InvalidArgumentException('Créneaux incompatibles avec la séance.');
        }

        return $this->withSessionLock($dateRdv, $session, function () use ($userId, $idChien, $dateRdv, $heureRdv, $slots, $informations, $gabarit, $prestations, $prixEstime) {
            if ($this->isSessionBooked($dateRdv, $slots)) {
                return false;
            }

            $this->db->beginTransaction();
            try {
                $stmt = $this->db->prepare(
                    'INSERT INTO reservation (date_rdv, heure_rdv, statut, id_utilisateur_PK, id_chien_PK, informations, gabarit, prix_estime)
                     VALUES (:date_rdv, :heure_rdv, :statut, :userId, :idChien, :informations, :gabarit, :prix_estime)'
                );
                $stmt->execute([
                    ':date_rdv' => $dateRdv,
                    ':heure_rdv' => $heureRdv,
                    ':statut' => 'en attente',
                    ':userId' => $userId,
                    ':idChien' => $idChien,
                    ':informations' => $informations,
                    ':gabarit' => $gabarit,
                    ':prix_estime' => $prixEstime,
                ]);
                $reservationId = (int) $this->db->lastInsertId();

                $ligneStmt = $this->db->prepare(
                    'INSERT INTO reservation_prestation (id_rdv_FK, code, libelle, type, prix, evalue_sur_place)
                     VALUES (:id_rdv, :code, :libelle, :type, :prix, :evalue_sur_place)'
                );
                foreach ($prestations as $prestation) {
                    $ligneStmt->execute([
                        ':id_rdv' => $reservationId,
                        ':code' => $prestation->code,
                        ':libelle' => $prestation->libelle,
                        ':type' => $prestation->type,
                        ':prix' => $prestation->prix,
                        ':evalue_sur_place' => $prestation->evalueSurPlace ? 1 : 0,
                    ]);
                }

                $this->db->commit();
                return $reservationId;
            } catch (\Throwable $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                throw $e;
            }
        });
    }

    private function withSessionLock(string $date, string $session, callable $operation): int|bool
    {
        // Même verrou pour réserver, bloquer et réactiver une demi-journée.
        $lockName = 'reservation_' . $date . '_' . ReservationSchedule::slotsForSession($session)[0];
        $lockAcquired = false;

        try {
            $lockStmt = $this->db->prepare('SELECT GET_LOCK(:lock_name, 5)');
            $lockStmt->execute([':lock_name' => $lockName]);

            if ((int) $lockStmt->fetchColumn() !== 1) {
                error_log('[ReservationModel] Verrou de séance indisponible : ' . $lockName);
                return false;
            }
            $lockAcquired = true;

            return $operation();
        } catch (\PDOException $e) {
            error_log('[ReservationModel] Modification de séance échouée : ' . $e->getMessage());
            return false;
        } finally {
            if ($lockAcquired) {
                $unlockStmt = $this->db->prepare('SELECT RELEASE_LOCK(:lock_name)');
                $unlockStmt->execute([':lock_name' => $lockName]);
            }
        }
    }

    /**
     * Vérifie si une réservation active ou une indisponibilité occupe la demi-journée.
     * Une seule réservation active est autorisée par matin ou par après-midi.
     */
    public function isSessionBooked(string $dateRdv, array $creneauxSession): bool
    {
        if ($this->isSessionUnavailable($dateRdv, $creneauxSession)) {
            return true;
        }
        $placeholders = implode(',', array_fill(0, count($creneauxSession), '?'));
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM reservation
                         WHERE DATE(date_rdv) = ?
                             AND TIME_FORMAT(heure_rdv, '%H:%i') IN ($placeholders)
               AND statut IN ('en attente', 'confirmé')"
        );

        $stmt->execute(array_merge([$dateRdv], $creneauxSession));

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Comme isSessionBooked(), mais ignore une réservation donnée (utile lors
     * du changement de statut d'une réservation existante par l'admin).
     * Les indisponibilités restent toujours prises en compte.
     */
    public function isSessionBookedExcluding(string $dateRdv, array $creneauxSession, int $excludeId): bool
    {
        if ($this->isSessionUnavailable($dateRdv, $creneauxSession)) {
            return true;
        }
        $placeholders = implode(',', array_fill(0, count($creneauxSession), '?'));
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM reservation
                         WHERE DATE(date_rdv) = ?
                             AND TIME_FORMAT(heure_rdv, '%H:%i') IN ($placeholders)
               AND statut IN ('en attente', 'confirmé')
               AND id_rdv != ?"
        );

        $stmt->execute(array_merge([$dateRdv], $creneauxSession, [$excludeId]));

        return (int) $stmt->fetchColumn() > 0;
    }

    private function isSessionUnavailable(string $date, array $slots): bool
    {
        if ($slots === []) {
            throw new \InvalidArgumentException('Les créneaux de la séance sont requis.');
        }
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM indisponibilite WHERE date_seance = :date AND seance = :seance'
        );
        $stmt->execute([
            ':date' => $date,
            ':seance' => ReservationSchedule::sessionForTime($slots[0]),
        ]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function getUpcomingUnavailabilities(): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, date_seance, seance, motif FROM indisponibilite
             WHERE date_seance >= :today
             ORDER BY date_seance, CASE WHEN seance = 'matin' THEN 0 ELSE 1 END"
        );
        $stmt->execute([':today' => date('Y-m-d')]);
        return $stmt->fetchAll();
    }

    public function createUnavailability(string $date, string $session, string $reason): bool
    {
        $slots = ReservationSchedule::slotsForSession($session);
        return $this->withSessionLock($date, $session, function () use ($date, $session, $reason, $slots): bool {
            if ($this->isSessionBooked($date, $slots)) {
                return false;
            }
            $stmt = $this->db->prepare(
                'INSERT INTO indisponibilite (date_seance, seance, motif) VALUES (:date, :seance, :motif)'
            );
            return $stmt->execute([':date' => $date, ':seance' => $session, ':motif' => $reason]);
        });
    }

    public function deleteUnavailability(int $id): bool
    {
        try {
            $stmt = $this->db->prepare('DELETE FROM indisponibilite WHERE id = :id');
            $stmt->execute([':id' => $id]);
            return $stmt->rowCount() === 1;
        } catch (\PDOException $e) {
            error_log('[ReservationModel] Déblocage échoué : ' . $e->getMessage());
            return false;
        }
    }

    // -------------------------------------------------------------------------
    // UPDATE
    // -------------------------------------------------------------------------

    /**
     * Met à jour le statut d'une réservation.
     * Valeurs autorisées : 'en attente', 'confirmé', 'annulé', 'terminé'
     */
    public function updateStatus(int $id, string $statut): bool
    {
        if (in_array($statut, ['en attente', 'confirmé'], true)) {
            $stmt = $this->db->prepare('SELECT date_rdv, heure_rdv FROM reservation WHERE id_rdv = :id');
            $stmt->execute([':id' => $id]);
            $reservation = $stmt->fetch();
            if (!$reservation) {
                return false;
            }
            $session = ReservationSchedule::sessionForTime($reservation['heure_rdv']);
            return $this->withSessionLock($reservation['date_rdv'], $session, function () use ($reservation, $session, $id, $statut): bool {
                if ($this->isSessionBookedExcluding($reservation['date_rdv'], ReservationSchedule::slotsForSession($session), $id)) {
                    return false;
                }
                return $this->writeStatus($id, $statut);
            });
        }
        return $this->writeStatus($id, $statut);
    }

    private function writeStatus(int $id, string $statut): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE reservation SET statut = :statut WHERE id_rdv = :id'
        );

        return $stmt->execute([':statut' => $statut, ':id' => $id]);
    }

    // -------------------------------------------------------------------------
    // DELETE
    // -------------------------------------------------------------------------

    /**
     * Supprime une réservation.
     */
    public function deleteReservation(int $id): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM reservation WHERE id_rdv = :id'
        );

        return $stmt->execute([':id' => $id]);
    }
}
