<?php
$estAdmin = ($_SESSION['user_role'] ?? '') === 'admin';
$reservationUrl = BASE_URL . ($estAdmin
    ? '/index.php?controller=admin&action=dashboard'
    : (isset($_SESSION['user_id'])
        ? '/index.php?controller=reservation&action=create'
        : '/index.php?controller=user&action=register'));
$reservationLabel = $estAdmin ? 'Mon espace admin' : 'Prendre rendez-vous';
?>
<section class="zone-hero" aria-labelledby="zone-title">
    <div class="container zone-hero__layout">
        <div class="zone-hero__content">
            <p class="zone-eyebrow">Notre zone d'intervention</p>
            <h1 id="zone-title">Toilettage à domicile <em>à Vincennes et alentours</em></h1>
            <span class="zone-rule" aria-hidden="true"></span>
            <p>L'Atelier du Museau se déplace directement à votre domicile pour prendre soin de votre chien, dans un environnement familier et rassurant.</p>
            <a class="btn zone-button" href="#communes"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> Découvrir les communes desservies <span aria-hidden="true">→</span></a>
        </div>
        <div class="zone-hero__visual">
            <img src="<?= BASE_URL ?>/uploads/images-zoneIntervention/hero.webp" alt="Un petit chien blanc pendant son toilettage" width="900" height="1200" fetchpriority="high">
        </div>
    </div>
</section>

<section class="zone-section" aria-labelledby="zone-location-title">
    <div class="container zone-location">
        <img class="zone-portrait" src="<?= BASE_URL ?>/uploads/images-zoneIntervention/dog2.webp" alt="Un chiot assis qui lève la tête" width="470" height="600" loading="lazy">
        <div class="zone-location__copy">
            <h2 id="zone-location-title"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> Où intervenons-nous ?</h2>
            <p>Je me déplace à Vincennes et dans les communes voisines pour offrir un service de toilettage à domicile, pratique et adapté aux besoins de chaque chien.</p>
            <div class="zone-note"><i class="fa-solid fa-map-location-dot" aria-hidden="true"></i><p>Déplacement gratuit à Vincennes. Un forfait de déplacement est appliqué hors secteur : contactez-moi pour connaître les conditions à votre adresse.</p></div>
            <a class="zone-text-link" href="#communes">Voir le plan des communes <span aria-hidden="true">→</span></a>
        </div>
        <aside class="zone-contact">
            <i class="fa-solid fa-paw" aria-hidden="true"></i>
            <h3>Et peut-être chez vous ?</h3>
            <p>Une question sur votre adresse ?</p>
            <a class="btn zone-button zone-button--dark" href="tel:0185135985">Me contacter</a>
        </aside>
    </div>
    <div class="container zone-communes" id="communes">
        <h3 id="zone-map-heading">Le plan des communes desservies</h3>
        <figure class="zone-map" aria-labelledby="zone-map-heading">
            <?php include __DIR__ . '/../../public/uploads/images-zoneIntervention/communes.svg'; ?>
            <figcaption>
                <div class="zone-map__legend">
                    <span><span class="zone-map__key zone-map__key--vincennes" aria-hidden="true"></span> Vincennes</span>
                    <span><span class="zone-map__key" aria-hidden="true"></span> Autres communes desservies</span>
                </div>
                <p class="zone-map__sources">Sources :
                    <a href="https://geo.api.gouv.fr/" target="_blank" rel="noopener noreferrer">API Découpage administratif</a>
                    et <a href="https://opendata.paris.fr/explore/dataset/arrondissements/" target="_blank" rel="noopener noreferrer">Ville de Paris</a>.
                </p>
            </figcaption>
        </figure>
        <details class="zone-communes__list">
            <summary>Consulter la liste des communes</summary>
            <ul>
                <?php foreach ($communes as $commune): ?>
                    <li><?= htmlspecialchars($commune, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </details>
        <p>Votre commune n'est pas dans la liste ? <a class="zone-text-link" href="mailto:atelierdumuseau@gmail.com">Écrivez-moi pour vérifier votre adresse.</a></p>
    </div>
</section>

<section class="zone-section zone-section--soft" aria-labelledby="zone-steps-title">
    <div class="container zone-process">
        <img class="zone-portrait" src="<?= BASE_URL ?>/uploads/images-zoneIntervention/dog3.webp" alt="" width="236" height="314" loading="lazy">
        <div>
            <h2 id="zone-steps-title"><i class="fa-regular fa-calendar-days" aria-hidden="true"></i> Comment se déroule un rendez-vous ?</h2>
            <p class="zone-intro">Un accompagnement simple, du premier contact à la fin du toilettage.</p>
            <ol class="zone-steps">
                <li><span class="zone-step-icon"><i class="fa-regular fa-calendar-check" aria-hidden="true"></i></span><h3>Compte et réservation</h3><p>Créez votre compte, ajoutez votre chien et choisissez une prestation et un créneau en ligne.</p></li>
                <li><span class="zone-step-icon"><i class="fa-regular fa-comments" aria-hidden="true"></i></span><h3>Échange et préparation</h3><p>Précisez les besoins de votre chien dans votre demande. Vous recevez un email lors de la confirmation du rendez-vous.</p></li>
                <li><span class="zone-step-icon"><i class="fa-solid fa-scissors" aria-hidden="true"></i></span><h3>Toilettage à domicile</h3><p>Votre chien est toiletté chez vous, avec une prestation adaptée à son pelage et à ses besoins.</p></li>
                <li><span class="zone-step-icon"><i class="fa-solid fa-paw" aria-hidden="true"></i></span><h3>Résultat et conseils</h3><p>Découvrez le résultat et profitez de nos derniers échanges, avec quelques conseils d'entretien si besoin.</p></li>
                <li><span class="zone-step-icon"><i class="fa-regular fa-star" aria-hidden="true"></i></span><h3>Gardons le contact !</h3><p>Votre retour sur Facebook, Google ou Instagram aide à faire connaître l'Atelier du Museau. Merci pour votre soutien !</p></li>
            </ol>
        </div>
    </div>
</section>

<section class="zone-section" aria-labelledby="zone-benefits-title">
    <div class="container zone-benefits">
        <div>
            <h2 id="zone-benefits-title"><i class="fa-regular fa-heart" aria-hidden="true"></i> Les bienfaits du toilettage à domicile</h2>
            <p class="zone-intro">Le confort de la maison, une attention personnalisée.</p>
            <ul class="zone-benefits__grid">
                <li><i class="fa-solid fa-house" aria-hidden="true"></i><h3>Pas de déplacement</h3><p>Évitez le trajet jusqu'au salon : je viens directement chez vous.</p></li>
                <li><i class="fa-regular fa-heart" aria-hidden="true"></i><h3>Un environnement familier</h3><p>Votre chien reste dans son cadre de vie habituel, avec ses repères.</p></li>
                <li><i class="fa-regular fa-user" aria-hidden="true"></i><h3>Un échange direct</h3><p>Vous pouvez me poser vos questions et m'expliquer ses habitudes.</p></li>
                <li><i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i><h3>Des soins adaptés</h3><p>La prestation tient compte de son gabarit, de son pelage et de ses besoins.</p></li>
                <li><i class="fa-solid fa-shield-heart" aria-hidden="true"></i><h3>Plus de sérénité</h3><p>Un rendez-vous à la maison, sans passage par la salle d'attente d'un salon.</p></li>
                <li><i class="fa-solid fa-paw" aria-hidden="true"></i><h3>Un pelage entretenu</h3><p>Des soins réguliers contribuent au confort et au bien-être de votre compagnon.</p></li>
            </ul>
        </div>
        <figure class="zone-benefits__visual">
            <img src="<?= BASE_URL ?>/uploads/images-zoneIntervention/dog1.webp" alt="Un Yorkshire allongé, détendu, un nœud dans les poils" width="1200" height="600" loading="lazy">
            <figcaption>Un petit moment de bien-être à la maison</figcaption>
        </figure>
    </div>
</section>

<section class="zone-cta" aria-labelledby="zone-cta-title">
    <div class="container zone-cta__layout">
        <div><h2 id="zone-cta-title"><i class="fa-solid fa-paw" aria-hidden="true"></i> Envie d'en savoir plus ?</h2><p>Découvrez les prestations, les tarifs et réservez votre rendez-vous en ligne.</p></div>
        <div class="zone-actions">
            <a class="btn zone-button zone-button--outline" href="<?= BASE_URL ?>/index.php?controller=home&action=tarifs"><i class="fa-solid fa-scissors" aria-hidden="true"></i> Prestations & tarifs</a>
            <a class="btn zone-button" href="<?= htmlspecialchars($reservationUrl, ENT_QUOTES, 'UTF-8') ?>"><i class="fa-regular fa-calendar-check" aria-hidden="true"></i> <?= $reservationLabel ?></a>
        </div>
    </div>
</section>

<section class="zone-section zone-section--soft" aria-labelledby="zone-faq-title">
    <div class="container">
        <h2 id="zone-faq-title"><i class="fa-regular fa-circle-question" aria-hidden="true"></i> FAQ — Vos questions, mes réponses</h2>
        <p class="zone-intro">Les réponses pour préparer votre rendez-vous en toute tranquillité.</p>
        <div class="zone-faq">
            <details><summary>Comment préparer le rendez-vous ?</summary><p>Prévoyez un accès sûr à votre domicile et un espace adapté au toilettage, avec un point d'eau et de l'électricité. Indiquez les besoins particuliers de votre chien lors de votre demande de rendez-vous.</p></details>
            <details><summary>Combien de temps dure un toilettage ?</summary><p>La durée dépend de la prestation, du pelage et des besoins de votre chien. Contactez-moi pour en discuter avant le rendez-vous.</p></details>
            <details><summary>Comment réserver une première séance ?</summary><p>Créez votre compte, ajoutez la fiche de votre chien et choisissez une prestation et un créneau disponible, au moins 24 h à l'avance. Votre demande reste en attente jusqu'à sa confirmation par email.</p></details>
            <details><summary>Quelles communes sont desservies ?</summary><p>Vincennes et les communes du <a class="zone-text-link" href="#communes">plan ci-dessus</a>, dont Montreuil, Saint-Mandé et Paris 12e. Le déplacement est gratuit à Vincennes ; un forfait s'applique hors secteur.</p></details>
        </div>
    </div>
</section>
