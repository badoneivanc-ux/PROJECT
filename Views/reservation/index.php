<div class="container">

    <!-- Formulaire de réservation (si showForm est vrai ou s'il n'y a aucune résa) -->
    <?php if (!empty($showForm) || empty($reservations)): ?>
        <h2 class="section-title mt-3"><i class="fa-solid fa-calendar-check"></i> Prendre un rendez-vous</h2>

        <?php if (empty($dogs)): ?>
            <div class="card">
                <p>Vous n'avez pas encore enregistré de chien.<br>
                Veuillez d'abord ajouter un chien depuis votre
                <a href="<?= BASE_URL ?>/index.php?controller=user&action=profile">profil</a>.</p>
            </div>
        <?php else: ?>
            <div class="card" style="max-width:520px">
                <form method="POST" action="<?= BASE_URL ?>/index.php?controller=reservation&action=create">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                    <div class="form-group">
                        <label for="id_chien">Chien</label>
                        <select id="id_chien" name="id_chien" required>
                            <option value="">-- Sélectionnez votre chien --</option>
                            <?php foreach ($dogs as $dog): ?>
                                <option value="<?= $dog->id ?>"
                                    <?= (($_POST['id_chien'] ?? '') == $dog->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dog->nom) ?> (<?= htmlspecialchars($dog->nomRace) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="date_rdv">Date</label>
                            <input type="date" id="date_rdv" name="date_rdv" required
                                   value="<?= htmlspecialchars($_POST['date_rdv'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="heure_rdv">Créneau</label>
                            <select id="heure_rdv" name="heure_rdv" required>
                                <option value="">-- Choisir un créneau --</option>
                                <?php $selected = $_POST['heure_rdv'] ?? ''; ?>
                                <optgroup label="Matin">
                                    <option value="08:30" <?= $selected === '08:30' ? 'selected' : '' ?>>08h30</option>
                                    <option value="09:00" <?= $selected === '09:00' ? 'selected' : '' ?>>09h00</option>
                                    <option value="09:30" <?= $selected === '09:30' ? 'selected' : '' ?>>09h30</option>
                                </optgroup>
                                <optgroup label="Après-midi">
                                    <option value="13:30" <?= $selected === '13:30' ? 'selected' : '' ?>>13h30</option>
                                    <option value="14:00" <?= $selected === '14:00' ? 'selected' : '' ?>>14h00</option>
                                    <option value="14:30" <?= $selected === '14:30' ? 'selected' : '' ?>>14h30</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%">
                        Confirmer la réservation
                    </button>
                </form>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dateInput = document.getElementById('date_rdv');
            if (!dateInput) {
                return;
            }

            const validateDate = function () {
                const value = dateInput.value;
                if (!value) {
                    dateInput.setCustomValidity('');
                    return;
                }

                const selectedTime = document.getElementById('heure_rdv')?.value;
                const date = new Date(value + 'T12:00:00');
                const now = new Date();
                const selectedDateTime = selectedTime ? new Date(value + 'T' + selectedTime) : null;

                if (Number.isNaN(date.getTime()) || date.getDay() === 0) {
                    dateInput.setCustomValidity('Les réservations sont fermées le dimanche.');
                } else if (selectedDateTime && selectedDateTime <= new Date(now.getTime() + (24 * 60 * 60 * 1000))) {
                    dateInput.setCustomValidity('Les réservations doivent être faites au moins 24 heures avant le rendez-vous.');
                } else {
                    dateInput.setCustomValidity('');
                }
            };

            const timeInput = document.getElementById('heure_rdv');
            if (timeInput) {
                timeInput.addEventListener('change', validateDate);
            }

            dateInput.addEventListener('input', validateDate);
            dateInput.addEventListener('change', validateDate);
            dateInput.closest('form')?.addEventListener('submit', function (event) {
                validateDate();
                if (dateInput.validity.customError) {
                    event.preventDefault();
                }
            });
        });
    </script>

    <!-- Liste des réservations -->
    <h2 class="section-title mt-3">Mes rendez-vous</h2>

    <?php if (empty($reservations)): ?>
        <div class="empty-state">Aucun rendez-vous pour le moment.</div>
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
                                        <form method="POST"
                                              action="<?= BASE_URL ?>/index.php?controller=reservation&action=cancel"
                                              onsubmit="return confirm('Annuler ce rendez-vous ?')">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                            <input type="hidden" name="id" value="<?= $r->id ?>">>
                                            <button type="submit" class="btn btn-danger btn-sm">Annuler</button>
                                        </form>
                                    <?php else: ?>
                                        &mdash;
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if (empty($showForm)): ?>
            <div class="mt-2">
                <a href="<?= BASE_URL ?>/index.php?controller=reservation&action=create" class="btn btn-primary">
                    + Nouveau rendez-vous
                </a>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</div>
