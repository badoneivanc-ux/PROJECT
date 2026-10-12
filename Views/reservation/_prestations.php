<?php
/** Prestations d'une réservation ($r) dans les tableaux client et admin. */
?>
<?php if ($r->prestations === []): ?>
    —
<?php else: ?>
    <?php if ($r->gabarit !== null): ?>
        <small class="reservation-prestations__gabarit"><?= htmlspecialchars(\Project\Core\Prestations::libelleGabarit($r->gabarit)) ?></small>
    <?php endif; ?>
    <ul class="reservation-prestations">
        <?php foreach ($r->prestations as $prestation): ?>
            <li>
                <span>
                    <?= htmlspecialchars($prestation->libelle) ?>
                    <?php if ($prestation->evalueSurPlace): ?>
                        <small class="reservation-prestations__mention">évalué sur place</small>
                    <?php endif; ?>
                </span>
                <span class="reservation-prestations__prix"><?= htmlspecialchars($prestation->tarifAffiche(false)) ?></span>
            </li>
        <?php endforeach; ?>
    </ul>
    <p class="reservation-prestations__total">
        Total estimé : <strong><?= htmlspecialchars(\Project\Core\Prestations::totalTexte($r->prixEstime)) ?></strong>
    </p>
<?php endif; ?>
