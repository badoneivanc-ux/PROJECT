<?php

namespace Project\Entities;

class Dog
{
    public $id;
    public $nom;
    public $nomRace;
    public $poids;
    public $age;
    public $sexe;
    public $photoChien;
    public $idUserFk;
    public $idRaceFk;
    public $proprioNom;
    public $proprioPrenom;

    public function __construct(
        int     $id,
        string  $nom,
        string  $nomRace,
        float   $poids,
        float   $age,
        int     $sexe,
        string  $photoChien,
        int     $idUserFk,
        ?int    $idRaceFk,
        ?string $proprioNom    = null,
        ?string $proprioPrenom = null
    ) {
        $this->id            = $id;
        $this->nom           = $nom;
        $this->nomRace       = $nomRace;
        $this->poids         = $poids;
        $this->age           = $age;
        $this->sexe          = $sexe;
        $this->photoChien    = $photoChien;
        $this->idUserFk      = $idUserFk;
        $this->idRaceFk      = $idRaceFk;
        $this->proprioNom    = $proprioNom;
        $this->proprioPrenom = $proprioPrenom;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int)   $data['id_chien_PK'],
                    $data['nom'],
                    $data['nom_race'],
            (float) $data['poids'],
            (float) $data['age'],
            (int)   $data['sexe'],
                    $data['photo_chien']  ?? '',
            (int)   $data['id_user_FK'],
                    isset($data['id_race_FK']) && $data['id_race_FK'] !== null
                        ? (int) $data['id_race_FK']
                        : null,
                    $data['proprio_nom']    ?? null,
                    $data['proprio_prenom'] ?? null
        );
    }

    public function isMale(): bool
    {
        return $this->sexe === 1;
    }
}
