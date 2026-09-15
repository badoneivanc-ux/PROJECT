<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? "L'Atelier du Museau", ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDescription ?? "Toilettage canin à domicile à Vincennes, réalisé avec douceur et adapté aux besoins de votre chien.", ENT_QUOTES, 'UTF-8') ?>">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/faviconAtelierDuMuseau.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Raleway:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/style.css">
</head>
<body>

<!-- ===== NAVBAR ===== -->
<nav class="navbar">
    <a class="navbar__brand" href="<?= BASE_URL ?>/index.php">
        <img src="<?= BASE_URL ?>/logo.jpg" alt="" class="navbar__logo">
        <span class="navbar__brand-text">L'Atelier du <em>Museau</em><small>✦ Toilettage à domicile ✦</small></span>
    </a>
    <button class="navbar__burger" aria-label="Menu" aria-expanded="false">
        <span></span>
        <span></span>
        <span></span>
    </button>
    <ul class="navbar__links">
        <li><a href="<?= BASE_URL ?>/index.php">Accueil</a></li>
        <li><a href="<?= BASE_URL ?>/index.php?controller=home&action=tarifs">Tarifs</a></li>
        <li><a href="<?= BASE_URL ?>/index.php?controller=dog&action=index">Races toilettées</a></li>

        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                <li><a href="<?= BASE_URL ?>/index.php?controller=admin&action=dashboard">Admin</a></li>
            <?php else: ?>
                <li><a href="<?= BASE_URL ?>/index.php?controller=reservation&action=create">Réserver</a></li>
                <li><a href="<?= BASE_URL ?>/index.php?controller=user&action=profile">
                    Mon profil (<?= htmlspecialchars($_SESSION['user_prenom'] ?? '') ?>)
                </a></li>
            <?php endif; ?>
            <li><a class="btn-nav" href="<?= BASE_URL ?>/index.php?controller=user&action=logout">Déconnexion</a></li>
        <?php else: ?>
            <li><a href="<?= BASE_URL ?>/index.php?controller=user&action=login">Connexion</a></li>
            <li><a class="btn-nav" href="<?= BASE_URL ?>/index.php?controller=user&action=register">Inscription</a></li>
        <?php endif; ?>
    </ul>
</nav>


<!-- ===== FLASH MESSAGE ===== -->
<?php if (!empty($_SESSION['flash'])): ?>
    <div class="flash flash--<?= htmlspecialchars($_SESSION['flash']['type']) ?>">
        <?= htmlspecialchars($_SESSION['flash']['message']) ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<!-- ===== CONTENU ===== -->
<main>
    <?= $content ?>
</main>

<!-- ===== FOOTER ===== -->
<footer>
    <div class="footer__social">
        <span class="footer__social-label">Retrouvez-nous sur</span>
        <a href="https://www.facebook.com/share/19Uj5iHaQt/?mibextid=wwXlfr" target="_blank" rel="noopener noreferrer" aria-label="Suivez-nous sur Facebook">
            <i class="fa-brands fa-facebook-f"></i>
        </a>
    </div>
    <p>&copy; <?= date('Y') ?> <span>L'Atelier du Museau</span> — Tous droits réservés &nbsp;✦&nbsp; Vincennes 94300</p>
    <p class="footer__legal-links">
        <a href="<?= BASE_URL ?>/index.php?controller=home&action=mentionsLegales">Mentions légales</a>
        &nbsp;·&nbsp;
        <a href="<?= BASE_URL ?>/index.php?controller=home&action=cgv">CGV</a>
        &nbsp;·&nbsp;
        <a href="<?= BASE_URL ?>/index.php?controller=home&action=confidentialite">Confidentialité (RGPD)</a>
    </p>
</footer>

<script>
(function () {
    const burger = document.querySelector('.navbar__burger');
    const links  = document.querySelector('.navbar__links');
    const hero = document.querySelector('.hero');

    if (burger && links) {
        burger.addEventListener('click', function () {
            const open = links.classList.toggle('is-open');
            burger.classList.toggle('is-open', open);
            burger.setAttribute('aria-expanded', open);
        });
        document.addEventListener('click', function (e) {
            if (!e.target.closest('.navbar')) {
                links.classList.remove('is-open');
                burger.classList.remove('is-open');
                burger.setAttribute('aria-expanded', 'false');
            }
        });
    }

    function updateHeroParallax() {
        if (!hero) return;

        if (window.innerWidth > 768) {
            hero.style.setProperty('--hero-logo-shift', '0px');
            return;
        }

        const shift = Math.min(window.scrollY * 0.2, 120);
        hero.style.setProperty('--hero-logo-shift', shift + 'px');
    }

    window.addEventListener('scroll', updateHeroParallax, { passive: true });
    window.addEventListener('resize', updateHeroParallax);
    updateHeroParallax();
})();
</script>
</body>
</html>
