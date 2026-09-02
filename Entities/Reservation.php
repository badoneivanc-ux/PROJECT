<?php

namespace Project\Entities;

class Reservation
{
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
    public $dureeMinutes;

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
        int     $dureeMinutes   = 60
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
        $this->dureeMinutes   = $dureeMinutes;
    }

    public static function fromArray(array $data): self
    {
        return new self(
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
            (int) ($data['duree_minutes'] ?? 60)
        );
    }

    /**
     * Heure de fin estimée du rendez-vous, calculée à partir de l'heure de début et de la durée.
     */
    public function heureFin(): string
    {
        $debut = \DateTime::createFromFormat('H:i:s', $this->heureRdv) ?: \DateTime::createFromFormat('H:i', $this->heureRdv);
        if (!$debut) {
            return '';
        }
        $debut->modify('+' . $this->dureeMinutes . ' minutes');
        return $debut->format('H:i');
    }

    public function isPending(): bool
    {
        return $this->statut === 'en attente';
    }
}
