<div class="container">

    <!-- En-tête profil -->
    <div class="profile-header mt-3">
        <div class="profile-avatar">
            <?= strtoupper(mb_substr($user->prenom, 0, 1)) ?>
        </div>
        <div>
            <div class="profile-header__name">
                <?= htmlspecialchars($user->prenom . ' ' . $user->nom) ?>
            </div>
            <div class="profile-header__email"><?= htmlspecialchars($user->email) ?></div>
            <div style="margin-top:.4rem;font-size:.85rem;opacity:.7">
                Membre depuis le <?= date('d/m/Y', strtotime($user->dateInscription)) ?>
            </div>
        </div>
    </div>

    <!-- Adresse d'intervention -->
    <h2 class="section-title mt-3">Mon adresse d'intervention</h2>
    <div class="card">
        <div id="address-display" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem">
            <p style="margin:0"> <?= htmlspecialchars($user->adresseComplete()) ?></p>
            <button type="button" id="address-edit-btn" class="btn btn-secondary btn-sm">Modifier</button>
        </div>

        <form method="POST" action="<?= BASE_URL ?>/index.php?controller=user&action=updateAddress"
              id="address-form" style="display:none;margin-top:1rem">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <div class="form-group">
                <label for="adresse">Adresse</label>
                <input type="text" id="adresse" name="adresse" required
                       value="<?= htmlspecialchars($user->adresse) ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="code_postal">Code postal</label>
                    <input type="text" id="code_postal" name="code_postal" required
                           value="<?= htmlspecialchars($user->codePostal) ?>">
                </div>
                <div class="form-group">
                    <label for="ville">Ville</label>
                    <input type="text" id="ville" name="ville" required
                           value="<?= htmlspecialchars($user->ville) ?>">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Enregistrer l'adresse</button>
            <button type="button" id="address-cancel-btn" class="btn btn-secondary">Annuler</button>
        </form>
    </div>

    <script>
    (function () {
        const displayBlock = document.getElementById('address-display');
        const form         = document.getElementById('address-form');
        const editBtn      = document.getElementById('address-edit-btn');
        const cancelBtn    = document.getElementById('address-cancel-btn');

        editBtn.addEventListener('click', function () {
            displayBlock.style.display = 'none';
            form.style.display = '';
        });
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function () {
                form.style.display = 'none';
                displayBlock.style.display = 'flex';
            });
        }
    })();
    </script>

    <!-- Mes chiens -->
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem">
        <h2 class="section-title" style="margin-bottom:0">Mes chiens</h2>
        <a href="<?= BASE_URL ?>/index.php?controller=dog&action=addDog" class="btn btn-primary">
            + Ajouter un chien
        </a>
    </div>

    <?php if (empty($dogs)): ?>
        <div class="empty-state">Vous n'avez pas encore ajouté de chien.</div>
    <?php else: ?>
        <div class="breeds-grid">
            <?php foreach ($dogs as $dog): ?>
                <div class="breed-card">
                    <?php if (!empty($dog->photoChien)): ?>
                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($dog->photoChien) ?>"
                             alt="<?= htmlspecialchars($dog->nom) ?>" class="breed-card__img">
                    <?php else: ?>
                        <div class="breed-card__img" style="display:flex;align-items:center;justify-content:center;font-size:3rem">🐶</div>
                    <?php endif; ?>
                    <div class="breed-card__body">
                        <div class="breed-card__name"><?= htmlspecialchars($dog->nom) ?></div>
                        <div class="breed-card__info">
                            Race : <?= htmlspecialchars($dog->nomRace) ?><br>
                            Âge : <?= $dog->age ?> ans &mdash;
                            <?= $dog->sexe ? 'Mâle' : 'Femelle' ?> &mdash;
                            <?= $dog->poids ?> kg
                        </div>
                        <?php if (!empty($dog->idRaceFk)): ?>
                            <a href="<?= BASE_URL ?>/index.php?controller=dog&action=show&id=<?= $dog->idRaceFk ?>"
                               class="btn btn-brown btn-sm" style="margin-top:.75rem">
                                📋 Fiche de ma race
                            </a>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>/index.php?controller=dog&action=editDog&id=<?= $dog->id ?>"
                           class="btn btn-secondary btn-sm" style="margin-top:.75rem">
                            Modifier mon chien
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Mes réservations -->
    <h2 class="section-title mt-3">Mes réservations</h2>

    <?php if (empty($reservations)): ?>
        <div class="empty-state">
            Aucune réservation pour le moment.
            <br><br>
            <a href="<?= BASE_URL ?>/index.php?controller=reservation&action=create" class="btn btn-primary">Prendre rendez-vous</a>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Créneau</th>
                            <th>Chien</th>
                            <th>Race</th>
                            <th>Statut</th>
                            <th>Action</th>
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
                                <td><?= htmlspecialchars($r->chienNom) ?></td>
                                <td><?= htmlspecialchars($r->chienRace) ?></td>
                                <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($r->statut) ?></span></td>
                                <td>
                                    <?php if ($r->isPending()): ?>
                                        <form method="POST" action="<?= BASE_URL ?>/index.php?controller=reservation&action=cancel"
                                              onsubmit="return confirm('Annuler ce rendez-vous ?')">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                            <input type="hidden" name="id" value="<?= $r->id ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Annuler</button>
                                        </form>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-2">
            <a href="<?= BASE_URL ?>/index.php?controller=reservation&action=create" class="btn btn-primary">
                + Nouveau rendez-vous
            </a>
        </div>
    <?php endif; ?>

</div>
