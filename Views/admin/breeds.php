<div class="admin-layout">

    <aside class="admin-sidebar">
        <div class="admin-sidebar__brand">
            <img src="<?= BASE_URL ?>/logo.jpg" alt="Atelier du Museau" class="admin-sidebar__logo">
        </div>
        <ul class="admin-sidebar__nav">
            <li><a href="<?= BASE_URL ?>/index.php?controller=admin&action=dashboard">Tableau de bord</a></li>
            <li><a href="<?= BASE_URL ?>/index.php?controller=admin&action=reservations">Réservations</a></li>
            <li><a href="<?= BASE_URL ?>/index.php?controller=admin&action=dogs">Chiens</a></li>
            <li><a href="<?= BASE_URL ?>/index.php?controller=admin&action=breeds" class="active">Races</a></li>
            <li><a href="<?= BASE_URL ?>/index.php?controller=admin&action=users">Utilisateurs</a></li>
            <li><a href="<?= BASE_URL ?>/">← Retour au site</a></li>
        </ul>
    </aside>

    <div class="admin-content">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem">
            <h1 style="font-size:1.6rem;color:var(--brown-dark)">Référentiel des races</h1>
            <a href="<?= BASE_URL ?>/index.php?controller=dog&action=createBreed" class="btn btn-primary">+ Ajouter une race</a>
        </div>

        <?php if (empty($breeds)): ?>
            <div class="empty-state">Aucune race enregistrée. <a href="<?= BASE_URL ?>/index.php?controller=dog&action=createBreed">Ajouter la première race</a>.</div>
        <?php else: ?>
            <div class="card">
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Race</th>
                                <th>Poids moyen</th>
                                <th>Fiche complète</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($breeds as $breed): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($breed->nomRace) ?></strong></td>
                                    <td><?= $breed->poids ?> kg</td>
                                    <td>
                                        <?php $hasFullSheet = $breed->hasFullSheet(); ?>
                                        <span class="badge <?= $hasFullSheet ? 'badge--confirmed' : 'badge--waiting' ?>">
                                            <?= $hasFullSheet ? 'Complète' : 'Partielle' ?>
                                        </span>
                                    </td>
                                    <td style="display:flex;gap:.5rem;flex-wrap:wrap">
                                        <a href="<?= BASE_URL ?>/index.php?controller=dog&action=show&id=<?= $breed->id ?>"
                                           class="btn btn-outline btn-sm" target="_blank">Voir</a>
                                        <a href="<?= BASE_URL ?>/index.php?controller=dog&action=editBreed&id=<?= $breed->id ?>"
                                           class="btn btn-brown btn-sm">Modifier</a>
                                        <form method="POST" action="<?= BASE_URL ?>/index.php?controller=dog&action=deleteBreed"
                                              onsubmit="return confirm('Supprimer cette race ?')">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                            <input type="hidden" name="id" value="<?= $breed->id ?>">>
                                            <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>
