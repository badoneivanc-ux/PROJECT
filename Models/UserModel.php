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
     * Met à jour les informations d'un utilisateur (nom, prénom, email).
     */
    public function updateUser(int $id, string $nom, string $prenom, string $email): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE user_dog
             SET nom = :nom, prenom = :prenom, email = :email
             WHERE id_utilisateur_PK = :id'
        );

        return $stmt->execute([
            ':nom'    => $nom,
            ':prenom' => $prenom,
            ':email'  => $email,
            ':id'     => $id,
        ]);
    }

    /**
     * Met à jour l'adresse d'intervention d'un utilisateur.
     */
    public function updateAddress(int $id, string $adresse, string $codePostal, string $ville): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE user_dog
             SET adresse = :adresse, code_postal = :code_postal, ville = :ville
             WHERE id_utilisateur_PK = :id'
        );

        return $stmt->execute([
            ':adresse'     => $adresse,
            ':code_postal' => $codePostal,
            ':ville'       => $ville,
            ':id'          => $id,
        ]);
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

        return $stmt->execute([':id' => $id]);
    }
}
