<section class="tarifs-hero">
    <div class="container">
        <h1 class="tarifs-title"><span style="display:inline-block;color:var(--blanc);"><i class="fa-solid fa-paw"></i></span> Tarifs </h1>
        <p class="tarifs-subtitle">Déplacement : gratuit à Vincennes - forfait appliqué hors secteur.</p>
    </div>
</section>

<!-- GRILLES DE PRIX PAR TAILLE (générées depuis Core/Prestations.php) -->
<section class="tarifs-section">
    <div class="container">
        <div class="tarifs-grid">

            <?php foreach (\Project\Core\Prestations::GABARITS as $gabarit => $libelleGabarit): ?>
                <div class="tarifs-card tarifs-card--featured">
                    <h2 class="tarifs-card__title"><?= htmlspecialchars($libelleGabarit) ?></h2>
                    <ul class="tarifs-card__list">
                        <?php foreach (\Project\Core\Prestations::principalesPour($gabarit) as $code => $prestation): ?>
                            <li<?= $prestation['prix'] === null ? ' class="tarifs-card__devis"' : '' ?>><span><?= htmlspecialchars($prestation['libelle']) ?></span><span><?= htmlspecialchars(\Project\Core\Prestations::tarifPrincipale($code, $gabarit)) ?></span></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>

        </div>
    </div>
</section>

<!-- SUPPLÉMENTS -->
<section class="tarifs-supplements">
    <div class="container">
        <h2 class="section-title"><span style="display:inline-block;color:var(--blanc);"><i class="fa-solid fa-paw"></i></span> Suppléments </h2>
        <div class="supplements-grid">
            <?php foreach (\Project\Core\Prestations::SUPPLEMENTS as $code => $supplement): ?>
                <div class="supplement-card">
                    <p class="supplement-card__label"><?= htmlspecialchars($supplement['libelle']) ?></p>
                    <p class="supplement-card__price"><?= htmlspecialchars(\Project\Core\Prestations::tarifSupplement($code)) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="tarifs-cta">
    <div class="container">
        <p class="tarifs-cta__quote">Un toilettage tout en douceur, chez vous</p>
        <p class="tarifs-cta__text">Votre chien reste dans son environnement familier sans stress ni déplacement.<br>
        Je prends le temps nécessaire pour un toilettage adapté à ses besoins, en toute bienveillance.</p>
        <div class="tarifs-cta__actions">
            <a href="tel:0185135985" class="btn btn-outline" aria-label="Nous appeler au 01 85 13 59 85"><i class="fa-solid fa-phone" aria-hidden="true"></i> Nous appeler</a>
            <a href="mailto:atelierdumuseau@gmail.com" class="btn btn-outline" aria-label="Nous écrire à atelierdumuseau@gmail.com"><i class="fa-solid fa-envelope" aria-hidden="true"></i> Nous écrire</a>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="<?= BASE_URL ?>/index.php?controller=user&action=register" class="btn btn-primary">Créer un compte &amp; réserver</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/index.php?controller=reservation&action=create" class="btn btn-primary">Prendre rendez-vous</a>
            <?php endif; ?>
        </div>
    </div>
</section>
