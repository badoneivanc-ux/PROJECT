<?php

namespace Project\Core;

use Project\Entities\ReservationPrestation;

/**
 * Catalogue des prestations : source unique pour la page Tarifs,
 * le formulaire de réservation et la validation côté serveur.
 */
final class Prestations
{
    /** Petit : jusqu'à 10 kg inclus ; moyen : plus de 10 kg et moins de 25 kg ; grand : 25 kg et plus. */
    public const POIDS_MAX_PETIT = 10.0;
    public const POIDS_MIN_GRAND = 25.0;

    public const GABARITS = [
        'petit' => 'Petit chien',
        'moyen' => 'Chien moyen',
        'grand' => 'Grand chien',
    ];

    /** Prix par gabarit : null = sur devis ; gabarit absent = prestation non proposée. */
    public const PRINCIPALES = [
        'bain_brushing' => [
            'libelle' => 'Bain + brushing',
            'prix'    => ['petit' => 60, 'moyen' => 75, 'grand' => 95],
        ],
        'tonte_complete' => [
            'libelle' => 'Tonte complète',
            'prix'    => ['petit' => 75, 'moyen' => 90, 'grand' => 110],
        ],
        'coupe_ciseaux' => [
            'libelle' => 'Coupe ciseaux',
            'prix'    => ['petit' => 80, 'moyen' => 100, 'grand' => 125],
        ],
        'epilation' => [
            'libelle' => 'Épilation',
            'prix'    => ['petit' => 85, 'moyen' => 105],
        ],
        'entretien_visage_pattes' => [
            'libelle' => 'Entretien visage / pattes',
            'prix'    => ['petit' => 45, 'moyen' => 50],
        ],
        'debourrage_mue' => [
            'libelle' => 'Débourrage / Mue',
            'prix'    => ['grand' => null],
        ],
    ];

    /**
     * « par_tranche » : facturé par tranche de 30 minutes, temps évalué sur place.
     * « majoration » : prix affiché avec un « + » sur la page Tarifs.
     */
    public const SUPPLEMENTS = [
        'demelage' => [
            'libelle'    => 'Démêlage important',
            'prix'       => 20,
            'par_tranche' => true,
            'majoration' => true,
        ],
        'coupe_griffes' => [
            'libelle'    => 'Coupe des griffes',
            'prix'       => 10,
            'par_tranche' => false,
            'majoration' => false,
        ],
        'soins_poil' => [
            'libelle'    => 'Soins poil abîmé ou pelage sensibilisé',
            'prix'       => 15,
            'par_tranche' => false,
            'majoration' => true,
        ],
        'temps_comportement' => [
            'libelle'    => 'Temps supplémentaire lié au comportement ou à l\'anxiété du chien',
            'prix'       => 20,
            'par_tranche' => true,
            'majoration' => true,
        ],
    ];

    public const MENTION_DEPLACEMENT = 'Hors frais de déplacement hors secteur.';
    public const MENTION_SUR_PLACE = 'Les suppléments facturés par tranche de 30 minutes seront évalués sur place, selon le temps réellement nécessaire.';

    public static function gabaritPourPoids(float $poids): string
    {
        if ($poids <= self::POIDS_MAX_PETIT) {
            return 'petit';
        }
        return $poids < self::POIDS_MIN_GRAND ? 'moyen' : 'grand';
    }

    public static function libelleGabarit(?string $gabarit): string
    {
        return self::GABARITS[$gabarit] ?? '';
    }

    /**
     * Prestations principales proposées pour un gabarit : code => ['libelle' => ..., 'prix' => ?float].
     */
    public static function principalesPour(string $gabarit): array
    {
        $principales = [];
        foreach (self::PRINCIPALES as $code => $prestation) {
            if (array_key_exists($gabarit, $prestation['prix'])) {
                $principales[$code] = ['libelle' => $prestation['libelle'], 'prix' => $prestation['prix'][$gabarit]];
            }
        }
        return $principales;
    }

    /** Montant en euros avec espace insécable, sans décimales inutiles (ex. « 60 € », « 12,50 € »). */
    public static function formatPrix(float $prix): string
    {
        $decimales = fmod($prix, 1.0) === 0.0 ? 0 : 2;
        return number_format($prix, $decimales, ',', "\u{00A0}") . "\u{00A0}€";
    }

    /**
     * Tarif d'une prestation principale : prix du gabarit, « Sur devis »,
     * ou prix minimal (« dès … ») lorsque le gabarit n'est pas encore connu.
     */
    public static function tarifPrincipale(string $code, ?string $gabarit = null): string
    {
        $prix = self::PRINCIPALES[$code]['prix'] ?? [];
        if ($gabarit !== null) {
            $montant = $prix[$gabarit] ?? null;
            return $montant === null ? 'Sur devis' : self::formatPrix($montant);
        }
        $connus = array_filter($prix, static fn($montant) => $montant !== null);
        if ($connus === []) {
            return 'Sur devis';
        }
        return count(array_unique($connus)) > 1
            ? 'dès ' . self::formatPrix(min($connus))
            : self::formatPrix(min($connus));
    }

    /** Tarif d'un supplément tel qu'affiché sur la page Tarifs (« 10 € », « +20 € / 30 min »…). */
    public static function tarifSupplement(string $code): string
    {
        $supplement = self::SUPPLEMENTS[$code];
        return ($supplement['majoration'] ? '+' : '')
            . self::formatPrix($supplement['prix'])
            . ($supplement['par_tranche'] ? ' / 30 min' : '');
    }

    /**
     * Construit les lignes de prestation à partir des codes saisis.
     * Retourne null si la prestation principale n'est pas proposée pour le gabarit
     * ou si un supplément est inconnu. Les prix viennent toujours du catalogue.
     *
     * @return ReservationPrestation[]|null
     */
    public static function selectionner(string $gabarit, mixed $principale, mixed $supplements): ?array
    {
        if (!is_string($principale) || !is_array($supplements)) {
            return null;
        }
        $principales = self::principalesPour($gabarit);
        if (!isset($principales[$principale])) {
            return null;
        }

        $lignes = [new ReservationPrestation(
            $principale,
            $principales[$principale]['libelle'],
            ReservationPrestation::TYPE_PRINCIPALE,
            $principales[$principale]['prix'] === null ? null : (float) $principales[$principale]['prix']
        )];

        $codes = [];
        foreach ($supplements as $code) {
            if (!is_string($code) || !isset(self::SUPPLEMENTS[$code])) {
                return null;
            }
            $codes[$code] = true;
        }
        // L'ordre du catalogue est conservé, quel que soit l'ordre des cases envoyées.
        foreach (self::SUPPLEMENTS as $code => $supplement) {
            if (isset($codes[$code])) {
                $lignes[] = new ReservationPrestation(
                    $code,
                    $supplement['libelle'],
                    ReservationPrestation::TYPE_SUPPLEMENT,
                    (float) $supplement['prix'],
                    $supplement['par_tranche']
                );
            }
        }

        return $lignes;
    }

    /**
     * Total estimé des prix fixes. Null si une prestation est sur devis.
     * Les suppléments évalués sur place ne sont pas comptés.
     *
     * @param ReservationPrestation[] $lignes
     */
    public static function totalEstime(array $lignes): ?float
    {
        $total = 0.0;
        foreach ($lignes as $ligne) {
            if ($ligne->prix === null) {
                return null;
            }
            if (!$ligne->evalueSurPlace) {
                $total += $ligne->prix;
            }
        }
        return $total;
    }

    public static function totalTexte(?float $prixEstime): string
    {
        return $prixEstime === null ? 'sur devis' : self::formatPrix($prixEstime);
    }

    /** @param ReservationPrestation[] $lignes */
    public static function contientSurPlace(array $lignes): bool
    {
        foreach ($lignes as $ligne) {
            if ($ligne->evalueSurPlace) {
                return true;
            }
        }
        return false;
    }

    /**
     * Bloc HTML récapitulant les prestations, pour les emails. Vide s'il n'y a aucune prestation
     * (réservations antérieures à la fonctionnalité).
     *
     * @param ReservationPrestation[] $lignes
     */
    public static function recapitulatifHtml(array $lignes, ?float $prixEstime, ?string $gabarit): string
    {
        if ($lignes === []) {
            return '';
        }

        $libelleGabarit = self::libelleGabarit($gabarit);
        $html = '<p><strong>Prestations demandées'
            . ($libelleGabarit !== '' ? ' (' . htmlspecialchars($libelleGabarit) . ')' : '')
            . ' :</strong></p><ul>';
        foreach ($lignes as $ligne) {
            $html .= '<li>' . htmlspecialchars($ligne->libelle) . ' : ' . htmlspecialchars($ligne->tarifAffiche()) . '</li>';
        }
        $html .= '</ul><p><strong>Total estimé : ' . htmlspecialchars(self::totalTexte($prixEstime)) . '</strong><br>'
            . htmlspecialchars(self::MENTION_DEPLACEMENT);
        if (self::contientSurPlace($lignes)) {
            $html .= '<br>' . htmlspecialchars(self::MENTION_SUR_PLACE);
        }

        return $html . '</p>';
    }
}
