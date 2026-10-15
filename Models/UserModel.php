<?php

namespace Project\Models;

use Project\Core\Database;
use Project\Entities\User;
use PDO;

class UserModel
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
     * Récupère un utilisateur par son email (pour la connexion et la vérification d'unicité).
     */
    public function getUserByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM user_dog WHERE email = :email LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row ? User::fromArray($row) : null;
    }

    /**
     * Récupère un utilisateur par son id.
     */
    public function getUserById(int $id): ?User
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM user_dog WHERE id_utilisateur_PK = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? User::fromArray($row) : null;
    }

    /**
     * Retourne tous les utilisateurs (sans leur mot de passe).
     */
    public function getAllUsers(): array
    {
        $stmt = $this->db->query(
            'SELECT id_utilisateur_PK, nom, prenom, email, telephone, date_inscription, id_role
             FROM user_dog
             ORDER BY date_inscription DESC'
        );
        return array_map(fn($row) => User::fromArray($row), $stmt->fetchAll());
    }

    /**
     * Compte le nombre total d'utilisateurs.
     */
    public function countUsers(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM user_dog')->fetchColumn();
    }

    /** @return array{dogs: int, reservations: int} */
    public function getDeletionImpact(int $id): array
    {
        $stmt = $this->db->prepare(
            'SELECT
                (SELECT COUNT(*) FROM chien WHERE id_user_FK = :dogUser) AS dogs,
                (SELECT COUNT(*) FROM reservation
                 WHERE id_utilisateur_PK = :reservationUser
                    OR id_chien_PK IN (SELECT id_chien_PK FROM chien WHERE id_user_FK = :dogOwner)) AS reservations'
        );
        $stmt->execute([':dogUser' => $id, ':reservationUser' => $id, ':dogOwner' => $id]);
        $row = $stmt->fetch();

        return ['dogs' => (int)$row['dogs'], 'reservations' => (int)$row['reservations']];
    }

    // -------------------------------------------------------------------------
    // CREATE
    // -------------------------------------------------------------------------

    /**
     * Crée un nouvel utilisateur et retourne son id, ou false en cas d'échec.
     */
    public function createUser(
        string $nom,
        string $prenom,
        string $email,
        string $hashedPassword,
        string $adresse    = '',
        string $codePostal = '',
        string $ville      = '',
        string $telephone  = ''
    ) {
        $stmt = $this->db->prepare(
            'INSERT INTO user_dog (nom, prenom, email, mdp, id_role, adresse, code_postal, ville, telephone)
             VALUES (:nom, :prenom, :email, :mdp, :role, :adresse, :code_postal, :ville, :telephone)'
        );

        $success = $stmt->execute([
            ':nom'         => $nom,
            ':prenom'      => $prenom,
            ':email'       => $email,
            ':mdp'         => $hashedPassword,
            ':role'        => 'user',
            ':adresse'     => $adresse,
            ':code_postal' => $codePostal,
            ':ville'       => $ville,
            ':telephone'   => $telephone,
        ]);

        return $success ? (int) $this->db->lastInsertId() : false;
    }

    // -------------------------------------------------------------------------
    // UPDATE
    // -------------------------------------------------------------------------

    /**
     * Met à jour les informations personnelles et l'adresse d'un utilisateur.
     * Le mot de passe est modifié séparément.
     */
    public function updateUser(
        int    $id,
        string $nom,
        string $prenom,
        string $email,
        string $adresse,
        string $codePostal,
        string $ville,
        string $telephone
    ): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE user_dog
             SET nom = :nom, prenom = :prenom, email = :email,
                 adresse = :adresse, code_postal = :code_postal,
                 ville = :ville, telephone = :telephone
             WHERE id_utilisateur_PK = :id'
        );

        return $stmt->execute([
            ':nom'         => $nom,
            ':prenom'      => $prenom,
            ':email'       => $email,
            ':adresse'     => $adresse,
            ':code_postal' => $codePostal,
            ':ville'       => $ville,
            ':telephone'   => $telephone,
            ':id'          => $id,
        ]);
    }

    /**
     * Enregistre un jeton de réinitialisation, avec un délai minimal entre les envois.
     */
    public function issuePasswordResetToken(
        int $userId,
        string $tokenHash,
        \DateTimeImmutable $expiresAt
    ): bool {
        $now = new \DateTimeImmutable();
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT IGNORE INTO password_reset (user_id, token_hash, expires_at, requested_at)
                 VALUES (:user_id, :token_hash, :expires_at, :initial_requested_at)'
            );
            $stmt->execute([
                ':user_id' => $userId,
                ':token_hash' => $tokenHash,
                ':expires_at' => $expiresAt->format('Y-m-d H:i:s'),
                ':initial_requested_at' => '2000-01-01 00:00:00',
            ]);

            $stmt = $this->db->prepare(
                'SELECT requested_at
                 FROM password_reset
                 WHERE user_id = :user_id
                 FOR UPDATE'
            );
            $stmt->execute([':user_id' => $userId]);
            $requestedAt = $stmt->fetchColumn();

            if ($requestedAt === false) {
                throw new \RuntimeException('Impossible de réserver le jeton de réinitialisation.');
            }

            $lastRequest = new \DateTimeImmutable($requestedAt);
            if ($lastRequest > $now->modify('-60 seconds')) {
                $this->db->commit();
                return false;
            }

            $stmt = $this->db->prepare(
                'UPDATE password_reset
                 SET token_hash = :token_hash,
                     expires_at = :expires_at,
                     requested_at = :requested_at
                 WHERE user_id = :user_id'
            );
            $stmt->execute([
                ':token_hash' => $tokenHash,
                ':expires_at' => $expiresAt->format('Y-m-d H:i:s'),
                ':requested_at' => $now->format('Y-m-d H:i:s'),
                ':user_id' => $userId,
            ]);

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Vérifie qu'un jeton existe et n'a pas expiré.
     */
    public function getPasswordResetUserId(string $tokenHash): ?int
    {
        $stmt = $this->db->prepare(
            'SELECT user_id, expires_at
             FROM password_reset
             WHERE token_hash = :token_hash
               AND expires_at IS NOT NULL'
        );
        $stmt->execute([':token_hash' => $tokenHash]);
        $row = $stmt->fetch();

        if (!$row || new \DateTimeImmutable($row['expires_at']) <= new \DateTimeImmutable()) {
            return null;
        }

        return (int) $row['user_id'];
    }

    /**
     * Change le mot de passe et consomme le jeton dans une seule transaction.
     */
    public function resetPassword(string $tokenHash, string $hashedPassword): bool
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'SELECT user_id, expires_at
                 FROM password_reset
                 WHERE token_hash = :token_hash
                   AND expires_at IS NOT NULL
                 FOR UPDATE'
            );
            $stmt->execute([':token_hash' => $tokenHash]);
            $row = $stmt->fetch();

            if (!$row || new \DateTimeImmutable($row['expires_at']) <= new \DateTimeImmutable()) {
                $this->db->commit();
                return false;
            }

            $stmt = $this->db->prepare(
                'UPDATE user_dog
                 SET mdp = :password
                 WHERE id_utilisateur_PK = :user_id'
            );
            $stmt->execute([
                ':password' => $hashedPassword,
                ':user_id' => (int) $row['user_id'],
            ]);

            if ($stmt->rowCount() !== 1) {
                throw new \RuntimeException('Le mot de passe du compte n’a pas pu être modifié.');
            }

            $stmt = $this->db->prepare(
                'UPDATE password_reset
                 SET token_hash = NULL, expires_at = NULL
                 WHERE user_id = :user_id'
            );
            $stmt->execute([':user_id' => (int) $row['user_id']]);

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    // -------------------------------------------------------------------------
    // DELETE
    // -------------------------------------------------------------------------

    /**
     * Supprime un utilisateur (ses chiens et réservations sont supprimés en cascade).
     */
    public function deleteUser(int $id): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM user_dog WHERE id_utilisateur_PK = :id'
        );

        return $stmt->execute([':id' => $id]) && $stmt->rowCount() === 1;
    }
}
