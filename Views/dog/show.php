<div class="container">
    <div style="margin:2rem 0">
        <a href="<?= BASE_URL ?>/index.php?controller=dog&action=index" style="color:var(--or)">
            &larr; Retour aux races
        </a>
    </div>

    <!-- EN-TÊTE -->
    <div class="breed-sheet__header">
        <?php if (!empty($breed->photoRace)): ?>
            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($breed->photoRace) ?>"
                 alt="<?= htmlspecialchars($breed->nomRace) ?>"
                 class="breed-sheet__photo">
        <?php else: ?>
            <div class="breed-sheet__icon">🐕</div>
        <?php endif; ?>
        <div>
            <h1 class="breed-sheet__title"><?= htmlspecialchars($breed->nomRace) ?></h1>
            <?php if (!empty($breed->poids)): ?>
                <p class="breed-sheet__poids">Poids moyen : <strong><?= $breed->poids ?> kg</strong></p>
            <?php endif; ?>

            <?php if (!empty($breed->caracteristiques)): ?>
                <div class="breed-sheet__tags">
                    <?php foreach (explode('|', $breed->caracteristiques) as $tag): ?>
                        <span class="breed-sheet__tag"><?= htmlspecialchars(trim($tag)) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="breed-sheet__grid">

        <!-- Colonne gauche -->
        <div class="breed-sheet__col">

            <?php if (!empty($breed->description)): ?>
            <div class="breed-sheet__section">
                <h2 class="breed-sheet__section-title">📋 Description</h2>
                <p><?= nl2br(htmlspecialchars($breed->description)) ?></p>
            </div>
            <?php endif; ?>

            <?php if (!empty($breed->historique)): ?>
            <div class="breed-sheet__section">
                <h2 class="breed-sheet__section-title">📜 Historique</h2>
                <p><?= nl2br(htmlspecialchars($breed->historique)) ?></p>
            </div>
            <?php endif; ?>

        </div>

        <!-- Colonne droite -->
        <div class="breed-sheet__col">

            <?php if (!empty($breed->entretien)): ?>
            <div class="breed-sheet__section">
                <h2 class="breed-sheet__section-title">✂️ Conseils d'entretien</h2>
                <p><?= nl2br(htmlspecialchars($breed->entretien)) ?></p>
            </div>
            <?php endif; ?>

            <?php if (!empty($breed->astucesToilettage)): ?>
            <div class="breed-sheet__section breed-sheet__section--highlight">
                <h2 class="breed-sheet__section-title">💡 Astuces de toilettage</h2>
                <p><?= nl2br(htmlspecialchars($breed->astucesToilettage)) ?></p>
            </div>
            <?php endif; ?>

        </div>

    </div>

    <!-- CTA -->
    <div class="breed-sheet__cta">
        <a href="<?= BASE_URL ?>/index.php?controller=reservation&action=create" class="btn btn-primary">
            📅 Prendre rendez-vous pour votre <?= htmlspecialchars($breed->nomRace) ?>
        </a>
    </div>

</div>
