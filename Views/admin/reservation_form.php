<div class="admin-layout">

    <aside class="admin-sidebar">
        <div class="admin-sidebar__brand">
            <img src="<?= BASE_URL ?>/logo.jpg" alt="Atelier du Museau" class="admin-sidebar__logo">
        </div>
        <ul class="admin-sidebar__nav">
            <li><a href="<?= BASE_URL ?>/index.php?controller=admin&action=dashboard">Tableau de bord</a></li>
            <li><a href="<?= BASE_URL ?>/index.php?controller=admin&action=reservations" class="active">Réservations</a></li>
            <li><a href="<?= BASE_URL ?>/index.php?controller=admin&action=dogs">Chiens</a></li>
            <li><a href="<?= BASE_URL ?>/index.php?controller=admin&action=breeds">Races</a></li>
            <li><a href="<?= BASE_URL ?>/index.php?controller=admin&action=users">Utilisateurs</a></li>
            <li><a href="<?= BASE_URL ?>/index.php">← Retour au site</a></li>
        </ul>
    </aside>

    <div class="admin-content">
        <h1 style="font-size:1.6rem;color:var(--brown-dark);margin-bottom:1.5rem">Gestion des réservations</h1>

        <?php if (empty($reservations)): ?>
            <div class="empty-state">Aucune réservation pour le moment.</div>
        <?php else: ?>
            <div class="card">
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Créneau</th>
                                <th>Client</th>
                                <th>Adresse</th>
                                <th>Chien</th>
                                <th>Statut</th>
                                <th>Modifier le statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservations as $r): ?>
                                <?php
                                    $badgeClasses = [
                                        'confirmé' => 'badge--confirmed',
                                        'annulé'   => 'badge--cancelled',
                                        'terminé'  => 'badge--done',
                                    ];
                                    $badgeClass = $badgeClasses[$r->statut] ?? 'badge--waiting';
                                ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($r->dateRdv)) ?></td>
                                    <td><?= htmlspecialchars(substr($r->heureRdv, 0, 5)) ?> - <?= htmlspecialchars($r->heureFin()) ?></td>
                                    <td><?= htmlspecialchars($r->userPrenom . ' ' . $r->userNom) ?></td>
                                    <td><?= $r->userAdresse ? htmlspecialchars($r->userAdresse . ', ' . $r->userCodePostal . ' ' . $r->userVille) : '—' ?></td>
                                    <td><?= htmlspecialchars($r->chienNom) ?> <small style="color:var(--grey)">(<?= htmlspecialchars($r->chienRace) ?>)</small></td>
                                    <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($r->statut) ?></span></td>
                                    <td>
                                        <form method="POST" action="<?= BASE_URL ?>/index.php?controller=admin&action=updateReservation"
                                              style="display:flex;gap:.5rem;align-items:center">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                            <input type="hidden" name="id" value="<?= $r->id ?>">
                                            <select name="statut" style="padding:.3rem .5rem;border-radius:6px;border:1px solid var(--beige-dark)">
                                                <?php foreach (['en attente', 'confirmé', 'annulé', 'terminé'] as $s): ?>
                                                    <option value="<?= $s ?>" <?= $r->statut === $s ? 'selected' : '' ?>>
                                                        <?= $s ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="btn btn-brown btn-sm">OK</button>
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
