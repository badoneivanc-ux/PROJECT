<?php

namespace Project\Models;

use Project\Core\Database;
use Project\Entities\Reservation;
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
        return array_map(fn($row) => Reservation::fromArray($row), $stmt->fetchAll());
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
        return array_map(fn($row) => Reservation::fromArray($row), $stmt->fetchAll());
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
        return $row ? Reservation::fromArray($row) : null;
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
     * Crée une réservation et retourne son id.
     */
    public function createReservation(
        int    $userId,
        int    $idChien,
        string $dateRdv,
        string $heureRdv,
        int    $dureeMinutes = 60,
        array  $creneauxSession = []
    ) {
        $lockName = 'reservation_' . $dateRdv . '_' . ($creneauxSession[0] ?? $heureRdv);
        $lockAcquired = false;

        try {
            $lockStmt = $this->db->prepare('SELECT GET_LOCK(:lock_name, 5)');
            $lockStmt->execute([':lock_name' => $lockName]);

            if ((int) $lockStmt->fetchColumn() !== 1) {
                return false;
            }
            $lockAcquired = true;

            if ($creneauxSession !== [] && $this->isSessionBooked($dateRdv, $creneauxSession)) {
                return false;
            }

            $stmt = $this->db->prepare(
                'INSERT INTO reservation (date_rdv, heure_rdv, duree_minutes, statut, id_utilisateur_PK, id_chien_PK)
                 VALUES (:date_rdv, :heure_rdv, :duree_minutes, :statut, :userId, :idChien)'
            );

            $success = $stmt->execute([
                ':date_rdv'      => $dateRdv,
                ':heure_rdv'     => $heureRdv,
                ':duree_minutes' => $dureeMinutes,
                ':statut'        => 'en attente',
                ':userId'        => $userId,
                ':idChien'       => $idChien,
            ]);

            return $success ? (int) $this->db->lastInsertId() : false;
        } catch (\PDOException $e) {
            error_log('[ReservationModel] Création échouée : ' . $e->getMessage());
            return false;
        } finally {
            if ($lockAcquired) {
                $unlockStmt = $this->db->prepare('SELECT RELEASE_LOCK(:lock_name)');
                $unlockStmt->execute([':lock_name' => $lockName]);
            }
        }
    }

    /**
     * Vérifie si une réservation active existe déjà pour une demi-journée donnée.
     * Une seule réservation active est autorisée par matin ou par après-midi.
     */
    public function isSessionBooked(string $dateRdv, array $creneauxSession): bool
    {
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
     */
    public function isSessionBookedExcluding(string $dateRdv, array $creneauxSession, int $excludeId): bool
    {
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

    // -------------------------------------------------------------------------
    // UPDATE
    // -------------------------------------------------------------------------

    /**
     * Met à jour le statut d'une réservation.
     * Valeurs autorisées : 'en attente', 'confirmé', 'annulé', 'terminé'
     */
    public function updateStatus(int $id, string $statut): bool
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
