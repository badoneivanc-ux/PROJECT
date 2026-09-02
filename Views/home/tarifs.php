<section class="tarifs-hero">
    <div class="container">
        <h1 class="tarifs-title">✦ Tarifs ✦</h1>
        <p class="tarifs-subtitle">Les tarifs varient selon le poids, le comportement du chien et l'état du pelage.</p>
    </div>
</section>

<!-- GRILLES DE PRIX PAR TAILLE -->
<section class="tarifs-section">
    <div class="container">
        <div class="tarifs-grid">

            <!-- Petit chien -->
            <div class="tarifs-card">
                <h2 class="tarifs-card__title">Petit chien</h2>
                <ul class="tarifs-card__list">
                    <li><span>Bain + brushing</span><span>50&nbsp;€</span></li>
                    <li><span>Tonte complète</span><span>65&nbsp;€</span></li>
                    <li><span>Coupe ciseaux</span><span>70&nbsp;€</span></li>
                    <li><span>Épilation</span><span>75&nbsp;€</span></li>
                    <li><span>Entretien visage / pattes</span><span>35&nbsp;€</span></li>
                </ul>
            </div>

            <!-- Chien moyen -->
            <div class="tarifs-card tarifs-card--featured">
                <h2 class="tarifs-card__title">Chien moyen</h2>
                <ul class="tarifs-card__list">
                    <li><span>Bain + brushing</span><span>65&nbsp;€</span></li>
                    <li><span>Tonte complète</span><span>80&nbsp;€</span></li>
                    <li><span>Coupe ciseaux</span><span>90&nbsp;€</span></li>
                    <li><span>Épilation</span><span>95&nbsp;€</span></li>
                    <li><span>Entretien visage / pattes</span><span>40&nbsp;€</span></li>
                </ul>
            </div>

            <!-- Grand chien -->
            <div class="tarifs-card">
                <h2 class="tarifs-card__title">Grand chien</h2>
                <ul class="tarifs-card__list">
                    <li><span>Bain + brushing</span><span>85&nbsp;€</span></li>
                    <li><span>Tonte complète</span><span>100&nbsp;€</span></li>
                    <li><span>Coupe ciseaux</span><span>115&nbsp;€</span></li>
                    <li class="tarifs-card__devis"><span>Débourrage / Mue</span><span>Sur devis</span></li>
                </ul>
            </div>

        </div>
    </div>
</section>

<!-- SUPPLÉMENTS -->
<section class="tarifs-supplements">
    <div class="container">
        <h2 class="section-title">🐾 Suppléments 🐾</h2>
        <div class="supplements-grid">
            <div class="supplement-card">
                <p class="supplement-card__label">Démêlage important</p>
                <p class="supplement-card__price">+20&nbsp;€ / 30 min</p>
            </div>
            <div class="supplement-card">
                <p class="supplement-card__label">Coupe des griffes</p>
                <p class="supplement-card__price">10&nbsp;€</p>
            </div>
            <div class="supplement-card">
                <p class="supplement-card__label">Soins poil abîmé ou pelage sensibilisé</p>
                <p class="supplement-card__price">+15&nbsp;€</p>
            </div>
            <div class="supplement-card">
                <p class="supplement-card__label">Temps supplémentaire lié au comportement ou à l'anxiété du chien</p>
                <p class="supplement-card__price">+20&nbsp;€ / 30 min</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="tarifs-cta">
    <div class="container">
        <p class="tarifs-cta__quote">Un toilettage tout en douceur, chez vous</p>
        <p class="tarifs-cta__text">Votre chien reste dans son environnement familier sans stress ni déplacement.<br>
        Je prends le temps nécessaire pour un toilettage adapté à ses besoins, en toute bienveillance.</p>
        <div class="tarifs-cta__contact">
            <a href="tel:0185135985" class="btn btn-outline">📞 01 85 13 59 85</a>
            <a href="mailto:atelierdumuseau@gmail.com" class="btn btn-primary">✉ atelierdumuseau@gmail.com</a>
        </div>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="<?= BASE_URL ?>/index.php?controller=user&action=register" class="btn btn-brown" style="margin-top:1rem">Créer un compte &amp; réserver</a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>/index.php?controller=reservation&action=create" class="btn btn-brown" style="margin-top:1rem">Prendre rendez-vous</a>
        <?php endif; ?>
    </div>
</section>
