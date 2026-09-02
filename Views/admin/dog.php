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
            <li><a href="<?= BASE_URL ?>/index.php">← Retour au site</a></li>
        </ul>
    </aside>

    <div class="admin-content">
        <div style="margin-bottom:1.5rem">
            <h1 style="font-size:1.6rem;color:var(--brown-dark)">Gestion des chiens</h1>
        </div>

        <?php if (empty($dogs)): ?>
            <div class="empty-state">Aucun chien enregistré.</div>
        <?php else: ?>
            <div class="card">
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Race</th>
                                <th>Poids</th>
                                <th>Âge</th>
                                <th>Sexe</th>
                                <th>Propriétaire</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dogs as $dog): ?>
                                <tr>
                                    <td><?= htmlspecialchars($dog->nom) ?></td>
                                    <td><?= htmlspecialchars($dog->nomRace) ?></td>
                                    <td><?= $dog->poids ?> kg</td>
                                    <td><?= $dog->age ?> ans</td>
                                    <td><?= $dog->sexe ? 'Mâle' : 'Femelle' ?></td>
                                    <td><?= htmlspecialchars(($dog->proprioPrenom ?? '') . ' ' . ($dog->proprioNom ?? '')) ?></td>
                                                <td>
                                        <form method="POST" action="<?= BASE_URL ?>/index.php?controller=dog&action=delete"
                                              onsubmit="return confirm('Supprimer ce chien ?')">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                            <input type="hidden" name="id" value="<?= $dog->id ?>">>
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
