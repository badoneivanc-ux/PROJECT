<!-- HERO -->
<section class="hero">
    <div class="container">
        <h1><span style="display:inline-block;filter:brightness(0) invert(1);">🐾</span> Bienvenue à l'Atelier du <span style="color:var(--blanc-off);">Museau</span></h1>
        <p>Salon de toilettage professionnel pour votre compagnon à quatre pattes.<br>Prenez soin de lui avec amour et expertise.</p>
        <div class="hero__actions">
            <a href="<?= BASE_URL ?>/index.php?controller=reservation&action=create" class="btn btn-primary">Prendre rendez-vous</a>
            <a href="<?= BASE_URL ?>/index.php?controller=dog&action=index" class="btn btn-outline">Nos races toilettées</a>
        </div>
    </div>
</section>

<!-- SERVICES -->
<section class="services">
    <div class="container">
        <h2 class="section-title">Nos services</h2>
        <div class="services-grid">
            <div class="service-card">
                <div class="service-card__title">Toilettage complet</div>
                <p>Bain, séchage, coupe et finitions soignées adaptées à chaque race.</p>
            </div>
            <div class="service-card">

                <div class="service-card__title">Bain & Séchage</div>
                <p>Shampoings professionnels respectueux de la peau et du pelage.</p>
            </div>
            <div class="service-card">
                <div class="service-card__title">Soin des griffes</div>
                <p>Coupe et limage des griffes en toute sécurité pour votre animal.</p>
            </div>
            <div class="service-card">
                <div class="service-card__title">Réservation en ligne</div>
                <p>Choisissez votre créneau 24h/24 depuis votre espace personnel.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section style="background:var(--beige);padding:3rem 0;text-align:center">
    <div class="container">
        <h2 style="color:var(--brown-dark);margin-bottom:1rem">Prêt à chouchouter votre compagnon ?</h2>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="<?= BASE_URL ?>/index.php?controller=user&action=register" class="btn btn-brown">Créer un compte</a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>/index.php?controller=reservation&action=create" class="btn btn-primary">Réserver maintenant</a>
        <?php endif; ?>
    </div>
</section>
