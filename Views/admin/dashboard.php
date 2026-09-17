<div class="admin-layout">

    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="admin-sidebar__brand">
            <img src="<?= BASE_URL ?>/logo.jpg" alt="Atelier du Museau" class="admin-sidebar__logo">
        </div>
        <nav>
            <p class="admin-sidebar__section-label">Navigation</p>
            <ul class="admin-sidebar__nav">
                <li>
                    <a href="<?= BASE_URL ?>/index.php?controller=admin&action=dashboard" class="active">Tableau de bord</a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/index.php?controller=admin&action=reservations">
                        Réservations
                        <?php if ((int)$pendingReservations > 0): ?>
                            <span class="admin-nav-badge"><?= (int)$pendingReservations ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/index.php?controller=admin&action=dogs">Chiens</a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/index.php?controller=admin&action=breeds">Races</a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/index.php?controller=admin&action=users">Utilisateurs</a>
                </li>
            </ul>

            <p class="admin-sidebar__section-label" style="margin-top:1.5rem">Compte</p>
            <ul class="admin-sidebar__nav">
                <li>
                    <a href="<?= BASE_URL ?>/">Voir le site</a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/index.php?controller=user&action=logout" class="admin-nav-logout">Déconnexion</a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Contenu principal -->
    <div class="admin-content">

        <!-- En-tête du tableau de bord -->
        <div class="dashboard-header">
            <div class="dashboard-header__left">
                <h1 class="dashboard-header__title">Tableau de bord</h1>
                <p class="dashboard-header__sub">
                    Bonjour, <strong><?= htmlspecialchars($_SESSION['user_prenom'] ?? 'Admin') ?></strong> —
                    <?= (new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE))->format(new DateTime()) ?>
                </p>
            </div>
            <?php if ((int)$pendingReservations > 0): ?>
            <div class="dashboard-alert">
                <span><?= (int)$pendingReservations ?> réservation<?= $pendingReservations > 1 ? 's' : '' ?> en attente de confirmation</span>
                <a href="<?= BASE_URL ?>/index.php?controller=admin&action=reservations">Traiter →</a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Cartes de statistiques -->
        <div class="stats-grid">
            <a href="<?= BASE_URL ?>/index.php?controller=admin&action=users" class="stat-card stat-card--link">
                <div class="stat-card__value"><?= (int)$totalUsers ?></div>
                <div class="stat-card__label">Utilisateurs</div>
            </a>
            <a href="<?= BASE_URL ?>/index.php?controller=admin&action=dogs" class="stat-card stat-card--link">
                <div class="stat-card__value"><?= (int)$totalDogs ?></div>
                <div class="stat-card__label">Chiens enregistrés</div>
            </a>
            <a href="<?= BASE_URL ?>/index.php?controller=admin&action=reservations" class="stat-card stat-card--link">
                <div class="stat-card__value"><?= (int)$totalReservations ?></div>
                <div class="stat-card__label">Réservations totales</div>
            </a>
            <a href="<?= BASE_URL ?>/index.php?controller=admin&action=reservations" class="stat-card stat-card--link stat-card--alert">
                <div class="stat-card__value"><?= (int)$pendingReservations ?></div>
                <div class="stat-card__label">En attente</div>
            </a>
        </div>

        <!-- Actions rapides -->
        <h2 class="dashboard-section-title">Actions rapides</h2>
        <div class="quick-actions-grid">
            <a href="<?= BASE_URL ?>/index.php?controller=admin&action=reservations" class="action-card">
                <div class="action-card__body">
                    <div class="action-card__title">Réservations</div>
                    <div class="action-card__desc">Consulter, confirmer ou annuler les rendez-vous</div>
                </div>
                <span class="action-card__arrow">→</span>
            </a>
            <a href="<?= BASE_URL ?>/index.php?controller=admin&action=users" class="action-card">
                <div class="action-card__body">
                    <div class="action-card__title">Utilisateurs</div>
                    <div class="action-card__desc">Gérer les comptes et les accès</div>
                </div>
                <span class="action-card__arrow">→</span>
            </a>
            <a href="<?= BASE_URL ?>/index.php?controller=admin&action=dogs" class="action-card">
                <div class="action-card__body">
                    <div class="action-card__title">Chiens</div>
                    <div class="action-card__desc">Voir et supprimer les fiches des chiens</div>
                </div>
                <span class="action-card__arrow">→</span>
            </a>
            <a href="<?= BASE_URL ?>/index.php?controller=admin&action=breeds" class="action-card">
                <div class="action-card__body">
                    <div class="action-card__title">Races</div>
                    <div class="action-card__desc">Enrichir le catalogue des races toilettées</div>
                </div>
                <span class="action-card__arrow">→</span>
            </a>
        </div>

    </div><!-- /.admin-content -->

</div><!-- /.admin-layout -->
