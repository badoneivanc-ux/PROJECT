<?php

namespace Project\Entities;

use Project\Core\Prestations;

/**
 * Prestation demandée pour une réservation. Le libellé et le prix sont figés
 * au moment de la demande pour que l'historique ne change pas avec les tarifs.
 */
class ReservationPrestation
{
    public const TYPE_PRINCIPALE = 'principale';
    public const TYPE_SUPPLEMENT = 'supplement';

    public string $code;
    public string $libelle;
    public string $type;
    public ?float $prix;
    public bool $evalueSurPlace;

    public function __construct(
        string $code,
        string $libelle,
        string $type,
        ?float $prix,
        bool   $evalueSurPlace = false
    ) {
        $this->code           = $code;
        $this->libelle        = $libelle;
        $this->type           = $type;
        $this->prix           = $prix;
        $this->evalueSurPlace = $evalueSurPlace;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['code'],
            $data['libelle'],
            $data['type'],
            $data['prix'] !== null ? (float) $data['prix'] : null,
            (bool) $data['evalue_sur_place']
        );
    }

    public function isSupplement(): bool
    {
        return $this->type === self::TYPE_SUPPLEMENT;
    }

    /** Ex. « 60 € », « +10 € », « +20 € / 30 min (évalué sur place) », « Sur devis ». */
    public function tarifAffiche(bool $avecMention = true): string
    {
        if ($this->prix === null) {
            return 'Sur devis';
        }
        $tarif = ($this->isSupplement() ? '+' : '') . Prestations::formatPrix($this->prix);
        if (!$this->evalueSurPlace) {
            return $tarif;
        }
        return $tarif . ' / 30 min' . ($avecMention ? ' (évalué sur place)' : '');
    }
}
