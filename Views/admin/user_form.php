<div class="admin-layout">

    <aside class="admin-sidebar">
        <div class="admin-sidebar__brand">
            <img src="<?= BASE_URL ?>/logo.jpg" alt="Atelier du Museau" class="admin-sidebar__logo">
        </div>
        <ul class="admin-sidebar__nav">
            <li><a href="<?= BASE_URL ?>/index.php?controller=admin&action=dashboard">Tableau de bord</a></li>
            <li><a href="<?= BASE_URL ?>/index.php?controller=admin&action=reservations">Réservations</a></li>
            <li><a href="<?= BASE_URL ?>/index.php?controller=admin&action=dogs">Chiens</a></li>
            <li><a href="<?= BASE_URL ?>/index.php?controller=admin&action=breeds">Races</a></li>
            <li><a href="<?= BASE_URL ?>/index.php?controller=admin&action=users" class="active">Utilisateurs</a></li>
            <li><a href="<?= BASE_URL ?>/index.php">← Retour au site</a></li>
        </ul>
    </aside>

    <div class="admin-content">
        <h1 style="font-size:1.6rem;color:var(--brown-dark);margin-bottom:1.5rem">Gestion des utilisateurs</h1>

        <?php if (empty($users)): ?>
            <div class="empty-state">Aucun utilisateur enregistré.</div>
        <?php else: ?>
            <div class="card">
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Inscription</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><?= htmlspecialchars($u->nom) ?></td>
                                    <td><?= htmlspecialchars($u->prenom) ?></td>
                                    <td><?= htmlspecialchars($u->email) ?></td>
                                    <td>
                                        <span class="badge <?= $u->isAdmin() ? 'badge--confirmed' : 'badge--waiting' ?>">
                                            <?= htmlspecialchars($u->role) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($u->dateInscription)) ?></td>
                                    <td>
                                        <?php if ($u->id != ($_SESSION['user_id'] ?? 0)): ?>
                                            <form method="POST"
                                                  action="<?= BASE_URL ?>/index.php?controller=admin&action=deleteUser"
                                                  onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                <input type="hidden" name="id" value="<?= $u->id ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                                            </form>
                                        <?php else: ?>
                                            <em style="color:var(--grey);font-size:.85rem">Vous</em>
                                        <?php endif; ?>
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
