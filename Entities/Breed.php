<?php

namespace Project\Entities;

class Breed
{
    public $id;
    public $nomRace;
    public $poids;
    public $description;
    public $entretien;
    public $historique;
    public $caracteristiques;
    public $astucesToilettage;
    public $photoRace;

    public function __construct(
        int    $id,
        string $nomRace,
        float  $poids,
        string $description,
        string $entretien,
        string $historique,
        string $caracteristiques,
        string $astucesToilettage,
        string $photoRace
    ) {
        $this->id                = $id;
        $this->nomRace            = $nomRace;
        $this->poids              = $poids;
        $this->description        = $description;
        $this->entretien           = $entretien;
        $this->historique          = $historique;
        $this->caracteristiques    = $caracteristiques;
        $this->astucesToilettage   = $astucesToilettage;
        $this->photoRace           = $photoRace;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int)   $data['id_race_PK'],
                    $data['nom_race'],
            (float) $data['poids'],
                    $data['description']        ?? '',
                    $data['entretien']          ?? '',
                    $data['historique']         ?? '',
                    $data['caracteristiques']   ?? '',
                    $data['astuces_toilettage'] ?? '',
                    $data['photo_race']         ?? ''
        );
    }

    public function hasFullSheet(): bool
    {
        return $this->historique !== '' && $this->caracteristiques !== '';
    }
}
