<?php

namespace Project\Models;

use Project\Core\Database;
use Project\Entities\Dog;
use Project\Entities\Breed;
use PDO;

class DogModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // =========================================================================
    // TABLE chien  (chiens appartenant aux utilisateurs)
    // =========================================================================

    // -------------------------------------------------------------------------
    // READ
    // -------------------------------------------------------------------------

    /**
     * Retourne tous les chiens enregistrés avec le prénom/nom de leur propriétaire.
     */
    public function getAllDogs(): array
    {
        $stmt = $this->db->query(
            'SELECT c.*, u.nom AS proprio_nom, u.prenom AS proprio_prenom
             FROM chien c
             JOIN user_dog u ON c.id_user_FK = u.id_utilisateur_PK
             ORDER BY c.nom ASC'
        );
        return array_map(fn($row) => Dog::fromArray($row), $stmt->fetchAll());
    }

    /**
     * Retourne les chiens d'un utilisateur donné.
     */
    public function getDogsByUserId(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM chien WHERE id_user_FK = :userId ORDER BY nom ASC'
        );
        $stmt->execute([':userId' => $userId]);
        return array_map(fn($row) => Dog::fromArray($row), $stmt->fetchAll());
    }

    /**
     * Retourne un chien par son id.
     */
    public function getDogById(int $id): ?Dog
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM chien WHERE id_chien_PK = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? Dog::fromArray($row) : null;
    }

    /**
     * Compte le nombre total de chiens enregistrés.
     */
    public function countDogs(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM chien')->fetchColumn();
    }

    // -------------------------------------------------------------------------
    // CREATE
    // -------------------------------------------------------------------------

    /**
     * Ajoute un chien pour un utilisateur et retourne son id.
     */
    public function createDog(
        string $nom,
        string $nom_race,
        float  $poids,
        float  $age,
        int    $sexe,
        string $photo,
        int    $userId
    ) {
        $stmt = $this->db->prepare(
            'INSERT INTO chien (nom, nom_race, poids, age, sexe, photo_chien, id_user_FK)
             VALUES (:nom, :nom_race, :poids, :age, :sexe, :photo, :userId)'
        );

        $success = $stmt->execute([
            ':nom'      => $nom,
            ':nom_race' => $nom_race,
            ':poids'    => $poids,
            ':age'      => $age,
            ':sexe'     => $sexe,
            ':photo'    => $photo,
            ':userId'   => $userId,
        ]);

        return $success ? (int) $this->db->lastInsertId() : false;
    }

    // -------------------------------------------------------------------------
    // UPDATE
    // -------------------------------------------------------------------------

    /**
     * Modifie les informations d'un chien.
     */
    public function updateDog(
        int    $id,
        string $nom,
        string $nom_race,
        float  $poids,
        float  $age,
        int    $sexe,
        string $photo
    ): bool {
        $stmt = $this->db->prepare(
            'UPDATE chien
             SET nom = :nom, nom_race = :nom_race, poids = :poids,
                 age = :age, sexe = :sexe, photo_chien = :photo
             WHERE id_chien_PK = :id'
        );

        return $stmt->execute([
            ':nom'      => $nom,
            ':nom_race' => $nom_race,
            ':poids'    => $poids,
            ':age'      => $age,
            ':sexe'     => $sexe,
            ':photo'    => $photo,
            ':id'       => $id,
        ]);
    }

    // -------------------------------------------------------------------------
    // DELETE
    // -------------------------------------------------------------------------

    /**
     * Supprime un chien (ses réservations sont supprimées en cascade).
     */
    public function deleteDog(int $id): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM chien WHERE id_chien_PK = :id'
        );

        return $stmt->execute([':id' => $id]);
    }

    // =========================================================================
    // TABLE dog_base  (référentiel des races — vitrine publique)
    // =========================================================================

    // -------------------------------------------------------------------------
    // READ
    // -------------------------------------------------------------------------

    /**
     * Retourne toutes les races disponibles.
     */
    public function getAllBreeds(): array
    {
        $stmt = $this->db->query(
            'SELECT * FROM dog_base ORDER BY nom_race ASC'
        );
        return array_map(fn($row) => Breed::fromArray($row), $stmt->fetchAll());
    }

    /**
     * Retourne une race par son id.
     */
    public function getBreedById(int $id): ?Breed
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM dog_base WHERE id_race_PK = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? Breed::fromArray($row) : null;
    }

    // -------------------------------------------------------------------------
    // CREATE
    // -------------------------------------------------------------------------

    /**
     * Ajoute une race dans le référentiel.
     */
    public function createBreed(
        string $nom_race,
        float  $poids,
        string $description,
        string $entretien,
        string $historique        = '',
        string $caracteristiques  = '',
        string $astuces_toilettage = '',
        string $photo_race        = ''
    ) {
        $stmt = $this->db->prepare(
            'INSERT INTO dog_base (nom_race, poids, photo_race, description, entretien, historique, caracteristiques, astuces_toilettage)
             VALUES (:nom_race, :poids, :photo_race, :description, :entretien, :historique, :caracteristiques, :astuces)'
        );

        $success = $stmt->execute([
            ':nom_race'         => $nom_race,
            ':poids'            => $poids,
            ':photo_race'       => $photo_race,
            ':description'      => $description,
            ':entretien'        => $entretien,
            ':historique'       => $historique,
            ':caracteristiques' => $caracteristiques,
            ':astuces'          => $astuces_toilettage,
        ]);

        return $success ? (int) $this->db->lastInsertId() : false;
    }

    // -------------------------------------------------------------------------
    // UPDATE
    // -------------------------------------------------------------------------

    /**
     * Modifie une race du référentiel.
     */
    public function updateBreed(
        int    $id,
        string $nom_race,
        float  $poids,
        string $description,
        string $entretien,
        string $historique        = '',
        string $caracteristiques  = '',
        string $astuces_toilettage = '',
        string $photo_race        = ''
    ): bool {
        // Ne mettre à jour la photo que si une nouvelle est fournie
        if ($photo_race !== '') {
            $stmt = $this->db->prepare(
                'UPDATE dog_base
                 SET nom_race = :nom_race, poids = :poids, photo_race = :photo_race,
                     description = :description, entretien = :entretien,
                     historique = :historique, caracteristiques = :caracteristiques,
                     astuces_toilettage = :astuces
                 WHERE id_race_PK = :id'
            );
            return $stmt->execute([
                ':nom_race'         => $nom_race,
                ':poids'            => $poids,
                ':photo_race'       => $photo_race,
                ':description'      => $description,
                ':entretien'        => $entretien,
                ':historique'       => $historique,
                ':caracteristiques' => $caracteristiques,
                ':astuces'          => $astuces_toilettage,
                ':id'               => $id,
            ]);
        }

        $stmt = $this->db->prepare(
            'UPDATE dog_base
             SET nom_race = :nom_race, poids = :poids,
                 description = :description, entretien = :entretien,
                 historique = :historique, caracteristiques = :caracteristiques,
                 astuces_toilettage = :astuces
             WHERE id_race_PK = :id'
        );
        return $stmt->execute([
            ':nom_race'         => $nom_race,
            ':poids'            => $poids,
            ':description'      => $description,
            ':entretien'        => $entretien,
            ':historique'       => $historique,
            ':caracteristiques' => $caracteristiques,
            ':astuces'          => $astuces_toilettage,
            ':id'               => $id,
        ]);
    }

    // -------------------------------------------------------------------------
    // DELETE
    // -------------------------------------------------------------------------

    /**
     * Supprime une race du référentiel.
     */
    public function deleteBreed(int $id): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM dog_base WHERE id_race_PK = :id'
        );

        return $stmt->execute([':id' => $id]);
    }

    /**
     * Associe un chien utilisateur à une race du référentiel.
     */
    public function linkDogToBreed(int $dogId, int $breedId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE chien SET id_race_FK = :breedId WHERE id_chien_PK = :dogId'
        );
        return $stmt->execute([':breedId' => $breedId, ':dogId' => $dogId]);
    }
}
