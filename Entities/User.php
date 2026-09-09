<?php

namespace Project\Entities;

class User
{
    public $id;
    public $nom;
    public $prenom;
    public $email;
    public $mdp;
    public $role;
    public $dateInscription;
    public $adresse;
    public $codePostal;
    public $ville;
    public $telephone;

    public function __construct(
        int    $id,
        string $nom,
        string $prenom,
        string $email,
        string $mdp,
        string $role,
        string $dateInscription,
        string $adresse    = '',
        string $codePostal = '',
        string $ville      = '',
        string $telephone  = ''
    ) {
        $this->id              = $id;
        $this->nom             = $nom;
        $this->prenom          = $prenom;
        $this->email           = $email;
        $this->mdp             = $mdp;
        $this->role            = $role;
        $this->dateInscription = $dateInscription;
        $this->adresse         = $adresse;
        $this->codePostal      = $codePostal;
        $this->ville           = $ville;
        $this->telephone       = $telephone;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int) $data['id_utilisateur_PK'],
                  $data['nom'],
                  $data['prenom'],
                  $data['email'],
                  $data['mdp']              ?? '',
                  $data['id_role'],
                  $data['date_inscription'] ?? '',
                  $data['adresse']          ?? '',
                  $data['code_postal']      ?? '',
                  $data['ville']            ?? '',
                  $data['telephone']        ?? ''
        );
    }

    /**
     * Adresse complète prête à afficher, ou chaîne vide si non renseignée.
     */
    public function adresseComplete(): string
    {
        if ($this->adresse === '' || $this->codePostal === '' || $this->ville === '') {
            return '';
        }
        return $this->adresse . ', ' . $this->codePostal . ' ' . $this->ville;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
