<?php

namespace Project\Entities;

class Reservation
{
    public const INFORMATIONS_MAX_LENGTH = 1000;

    public ?string $informations;
    public $id;
    public $dateRdv;
    public $heureRdv;
    public $statut;
    public $idUtilisateur;
    public $idChien;
    public $chienNom;
    public $chienRace;
    public $userNom;
    public $userPrenom;
    public $userEmail;
    public $userAdresse;
    public $userCodePostal;
    public $userVille;
    /** Gabarit du chien au moment de la demande (null pour les réservations antérieures). */
    public ?string $gabarit = null;
    /** Total des prix fixes ; null si sur devis ou réservation antérieure aux prestations. */
    public ?float $prixEstime = null;
    /** @var ReservationPrestation[] */
    public array $prestations = [];

    public function __construct(
        int     $id,
        string  $dateRdv,
        string  $heureRdv,
        string  $statut,
        int     $idUtilisateur,
        int     $idChien,
        ?string $chienNom       = null,
        ?string $chienRace      = null,
        ?string $userNom        = null,
        ?string $userPrenom     = null,
        ?string $userEmail      = null,
        ?string $userAdresse    = null,
        ?string $userCodePostal = null,
        ?string $userVille      = null,
        ?string $informations  = null
    ) {
        $this->id            = $id;
        $this->dateRdv       = $dateRdv;
        $this->heureRdv      = $heureRdv;
        $this->statut        = $statut;
        $this->idUtilisateur = $idUtilisateur;
        $this->idChien       = $idChien;
        $this->chienNom      = $chienNom;
        $this->chienRace     = $chienRace;
        $this->userNom       = $userNom;
        $this->userPrenom    = $userPrenom;
        $this->userEmail     = $userEmail;
        $this->userAdresse    = $userAdresse;
        $this->userCodePostal = $userCodePostal;
        $this->userVille      = $userVille;
        $this->informations   = $informations;
    }

    public static function fromArray(array $data): self
    {
        $reservation = new self(
            (int) $data['id_rdv'],
                  $data['date_rdv'],
                  $data['heure_rdv'],
                  $data['statut'],
            (int) $data['id_utilisateur_PK'],
            (int) $data['id_chien_PK'],
                  $data['chien_nom']    ?? null,
                  $data['chien_race']   ?? null,
                  $data['user_nom']     ?? null,
                  $data['user_prenom']  ?? null,
                  $data['user_email']   ?? null,
                  $data['user_adresse']     ?? null,
                  $data['user_code_postal'] ?? null,
                  $data['user_ville']       ?? null,
                  $data['informations'] ?? null
        );
        $reservation->gabarit    = $data['gabarit'] ?? null;
        $reservation->prixEstime = isset($data['prix_estime']) ? (float) $data['prix_estime'] : null;

        return $reservation;
    }

    public function isPending(): bool
    {
        return $this->statut === 'en attente';
    }
}
