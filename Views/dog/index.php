<div class="container">
    <h1 class="section-title mt-3">Races toilettées</h1>
    <p style="color:var(--grey);margin-bottom:1rem">Découvrez les races que nous toilettons et leurs spécificités d'entretien.</p>

    <?php if (empty($breeds)): ?>
        <div class="empty-state">Aucune race renseignée pour le moment.</div>
    <?php else: ?>
        <div class="breeds-grid">
            <?php foreach ($breeds as $breed): ?>
                <div class="breed-card">
                    <?php if (!empty($breed->photoRace)): ?>
                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($breed->photoRace) ?>"
                             alt="<?= htmlspecialchars($breed->nomRace) ?>"
                             class="breed-card__img">
                    <?php else: ?>
                        <div class="breed-card__img" style="display:flex;align-items:center;justify-content:center;font-size:3rem;color:var(--or);background:var(--beige)">
                            <i class="fa-solid fa-dog"></i>
                        </div>
                    <?php endif; ?>
                    <div class="breed-card__body">
                        <div class="breed-card__name"><?= htmlspecialchars($breed->nomRace) ?></div>
                        <div class="breed-card__info">
                            Poids moyen : <?= $breed->poids ?> kg
                        </div>
                        <p style="font-size:.9rem;color:var(--text);margin-bottom:1rem">
                            <?= htmlspecialchars(mb_substr($breed->description, 0, 100)) ?>...
                        </p>
                        <a href="<?= BASE_URL ?>/index.php?controller=dog&action=show&id=<?= $breed->id ?>"
                           class="btn btn-brown btn-sm">En savoir plus</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
