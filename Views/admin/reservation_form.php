<div class="admin-layout admin-reservations-layout">

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
            <li><a href="<?= BASE_URL ?>/">← Retour au site</a></li>
        </ul>
    </aside>

    <div class="admin-content">
        <h1 style="font-size:1.6rem;color:var(--brown-dark);margin-bottom:1.5rem">Gestion des réservations</h1>

        <h2 class="dashboard-section-title">Rendez-vous</h2>
        <?php foreach (['current' => $reservations, 'completed' => $completedReservations] as $group => $groupReservations): ?>
        <?php if ($group === 'completed'): ?>
            <details class="admin-completed-reservations">
                <summary class="btn btn-brown">Prestations terminées</summary>
        <?php endif; ?>

        <?php if (empty($groupReservations)): ?>
            <div class="empty-state">
                <?= $group === 'completed' ? 'Aucune prestation terminée pour le moment.' : 'Aucune réservation en cours pour le moment.' ?>
            </div>
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
                            <?php foreach ($groupReservations as $r): ?>
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
        <?php if ($group === 'completed'): ?>
            </details>
        <?php endif; ?>
        <?php endforeach; ?>

        <section class="admin-planning-section" aria-labelledby="planning-title">
        <h2 id="planning-title" class="admin-planning-section__title">Disponibilités du planning</h2>
        <p class="admin-planning-section__description">Bloquez ou libérez des demi-journées pour vos absences et congés. Ces blocages sont distincts des rendez-vous clients.</p>
        <details id="block-session" class="admin-session-block"<?= !empty($showBlockForm) ? ' open' : '' ?>>
            <summary class="btn btn-brown">Bloquer une séance</summary>
            <div class="card">
                <p>Rendez une demi-journée indisponible, sans créer de réservation ni envoyer d’email. Le motif reste interne.</p>
                <form method="POST" action="<?= BASE_URL ?>/index.php?controller=admin&action=blockSession">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="date_seance">Date</label>
                            <input type="date" id="date_seance" name="date_seance" required min="<?= date('Y-m-d') ?>"
                                   value="<?= htmlspecialchars($blockForm['date_seance'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="seance">Séance</label>
                            <select id="seance" name="seance" required>
                                <option value="">-- Choisir une séance --</option>
                                <option value="matin" <?= ($blockForm['seance'] ?? '') === 'matin' ? 'selected' : '' ?>>Matin</option>
                                <option value="apres_midi" <?= ($blockForm['seance'] ?? '') === 'apres_midi' ? 'selected' : '' ?>>Après-midi</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="motif">Motif interne (facultatif)</label>
                        <input type="text" id="motif" name="motif" maxlength="255"
                               value="<?= htmlspecialchars($blockForm['motif'] ?? '') ?>" placeholder="Congé, rendez-vous personnel…">
                    </div>
                    <button type="submit" class="btn btn-brown">Bloquer la séance</button>
                </form>
            </div>
        </details>

        <h3 class="dashboard-section-title">Séances bloquées à venir</h3>
        <?php if (empty($unavailabilities)): ?>
            <div class="empty-state">Aucune séance bloquée à partir d’aujourd’hui.</div>
        <?php else: ?>
            <div class="card">
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr><th>Date</th><th>Séance</th><th>Motif interne</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($unavailabilities as $unavailability): ?>
                                <tr>
                                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($unavailability['date_seance']))) ?></td>
                                    <td><?= $unavailability['seance'] === 'matin' ? 'Matin' : 'Après-midi' ?></td>
                                    <td><?= $unavailability['motif'] !== '' ? htmlspecialchars($unavailability['motif']) : '—' ?></td>
                                    <td>
                                        <form method="POST" action="<?= BASE_URL ?>/index.php?controller=admin&action=unblockSession">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                            <input type="hidden" name="id" value="<?= (int) $unavailability['id'] ?>">
                                            <button type="submit" class="btn btn-brown btn-sm">Débloquer</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
        </section>

    </div>

</div>
