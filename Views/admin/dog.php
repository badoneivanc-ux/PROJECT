<div class="admin-layout">

    <aside class="admin-sidebar">
        <div class="admin-sidebar__brand">
            <img src="<?= BASE_URL ?>/logo.jpg" alt="Atelier du Museau" class="admin-sidebar__logo">
        </div>
        <ul class="admin-sidebar__nav">
            <li><a href="<?= BASE_URL ?>/index.php?controller=admin&action=dashboard">Tableau de bord</a></li>
            <li><a href="<?= BASE_URL ?>/index.php?controller=admin&action=reservations">Réservations</a></li>
            <li><a href="<?= BASE_URL ?>/index.php?controller=admin&action=dogs" class="active">Chiens</a></li>
            <li><a href="<?= BASE_URL ?>/index.php?controller=admin&action=breeds">Races</a></li>
            <li><a href="<?= BASE_URL ?>/index.php?controller=admin&action=users">Utilisateurs</a></li>
            <li><a href="<?= BASE_URL ?>/">← Retour au site</a></li>
        </ul>
    </aside>

    <div class="admin-content">
        <div style="margin-bottom:1.5rem">
            <h1 style="font-size:1.6rem;color:var(--brown-dark)">Gestion des chiens</h1>
        </div>

        <?php if (empty($dogs)): ?>
            <div class="empty-state">Aucun chien enregistré.</div>
        <?php else: ?>
            <div class="dog-admin-grid">
                <?php foreach ($dogs as $dog): ?>
                    <article class="dog-admin-card">
                        <?php if (!empty($dog->photoChien)): ?>
                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($dog->photoChien) ?>"
                                 alt="<?= htmlspecialchars($dog->nom) ?>" class="dog-admin-card__photo">
                        <?php else: ?>
                            <div class="dog-admin-card__photo dog-admin-card__photo--empty"><i class="fa-solid fa-dog"></i></div>
                        <?php endif; ?>

                        <div class="dog-admin-card__body">
                            <h2 class="dog-admin-card__name"><?= htmlspecialchars($dog->nom) ?></h2>
                            <div class="dog-admin-card__race"><?= htmlspecialchars($dog->nomRace) ?></div>

                            <div class="dog-admin-card__tags">
                                <span class="badge"><?= $dog->sexe ? 'Mâle' : 'Femelle' ?></span>
                                <span class="badge"><?= $dog->age ?> ans</span>
                                <span class="badge"><?= $dog->poids ?> kg</span>
                            </div>

                            <div class="dog-admin-card__owner">
                                <i class="fa-solid fa-user"></i>
                                <div>
                                    <strong><?= htmlspecialchars(trim(($dog->proprioPrenom ?? '') . ' ' . ($dog->proprioNom ?? ''))) ?></strong>
                                    <?php if (!empty($dog->proprioEmail)): ?>
                                        <small><?= htmlspecialchars($dog->proprioEmail) ?></small>
                                    <?php endif; ?>
                                    <?php if (!empty($dog->proprioTelephone)): ?>
                                        <small><?= htmlspecialchars($dog->proprioTelephone) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <form method="POST" action="<?= BASE_URL ?>/index.php?controller=dog&action=delete"
                                  onsubmit="return confirm('Supprimer ce chien ?')">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                <input type="hidden" name="id" value="<?= $dog->id ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>
