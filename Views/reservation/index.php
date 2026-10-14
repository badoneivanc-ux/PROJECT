<div class="container container--wide">

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
                <?php
                    $gabaritsChiens = [];
                    foreach ($dogs as $dog) {
                        $gabaritsChiens[$dog->id] = \Project\Core\Prestations::gabaritPourPoids((float) $dog->poids);
                    }
                    $chienChoisi = is_scalar($_POST['id_chien'] ?? null) ? (int) $_POST['id_chien'] : 0;
                    $gabaritChoisi = $gabaritsChiens[$chienChoisi] ?? null;
                    $prestationChoisie = is_string($_POST['prestation'] ?? null) ? $_POST['prestation'] : '';
                    $supplementsChoisis = is_array($_POST['supplements'] ?? null) ? array_filter($_POST['supplements'], 'is_string') : [];
                    $lignesChoisies = $gabaritChoisi !== null
                        ? \Project\Core\Prestations::selectionner($gabaritChoisi, $prestationChoisie, array_values($supplementsChoisis))
                        : null;
                ?>
                <form method="POST" action="<?= BASE_URL ?>/index.php?controller=reservation&action=create">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                    <div class="form-group">
                        <label for="id_chien">Chien</label>
                        <select id="id_chien" name="id_chien" required aria-describedby="gabarit_help">
                            <option value="">-- Sélectionnez votre chien --</option>
                            <?php foreach ($dogs as $dog): ?>
                                <option value="<?= $dog->id ?>"
                                    data-gabarit="<?= htmlspecialchars($gabaritsChiens[$dog->id]) ?>"
                                    <?= (($_POST['id_chien'] ?? '') == $dog->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dog->nom) ?> (<?= htmlspecialchars($dog->nomRace) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small id="gabarit_help" style="display:block;margin-top:.35rem;color:var(--texte-doux)"
                               data-defaut="Les prestations et les prix dépendent du gabarit de votre chien : petit jusqu'à 10 kg, moyen de plus de 10 kg à moins de 25 kg, grand à partir de 25 kg.">
                            <?= $gabaritChoisi !== null
                                ? 'Gabarit retenu d\'après le poids enregistré : ' . htmlspecialchars(mb_strtolower(\Project\Core\Prestations::libelleGabarit($gabaritChoisi))) . '.'
                                : 'Les prestations et les prix dépendent du gabarit de votre chien : petit jusqu\'à 10 kg, moyen de plus de 10 kg à moins de 25 kg, grand à partir de 25 kg.' ?>
                        </small>
                    </div>

                    <fieldset class="prestations-fieldset">
                        <legend>Prestation</legend>
                        <?php foreach (\Project\Core\Prestations::PRINCIPALES as $code => $principale): ?>
                            <?php $disponible = $gabaritChoisi === null || array_key_exists($gabaritChoisi, $principale['prix']); ?>
                            <label class="prestation-option" <?= $disponible ? '' : 'hidden' ?>>
                                <input type="radio" name="prestation" value="<?= htmlspecialchars($code) ?>" required
                                       data-prix="<?= htmlspecialchars(json_encode($principale['prix'])) ?>"
                                       <?= $disponible ? '' : 'disabled' ?>
                                       <?= $disponible && $prestationChoisie === $code ? 'checked' : '' ?>>
                                <span class="prestation-option__libelle"><?= htmlspecialchars($principale['libelle']) ?></span>
                                <span class="prestation-option__prix"><?= htmlspecialchars(\Project\Core\Prestations::tarifPrincipale($code, $gabaritChoisi)) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </fieldset>

                    <fieldset class="prestations-fieldset">
                        <legend>Suppléments (facultatif)</legend>
                        <?php foreach (\Project\Core\Prestations::SUPPLEMENTS as $code => $supplement): ?>
                            <label class="prestation-option">
                                <input type="checkbox" name="supplements[]" value="<?= htmlspecialchars($code) ?>"
                                       data-prix="<?= (float) $supplement['prix'] ?>"
                                       data-par-tranche="<?= $supplement['par_tranche'] ? '1' : '0' ?>"
                                       <?= in_array($code, $supplementsChoisis, true) ? 'checked' : '' ?>>
                                <span class="prestation-option__libelle"><?= htmlspecialchars($supplement['libelle']) ?></span>
                                <span class="prestation-option__prix">
                                    +<?= htmlspecialchars(\Project\Core\Prestations::formatPrix($supplement['prix'])) ?><?= $supplement['par_tranche'] ? ' / 30 min' : '' ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                        <small class="prestations-note">
                            Le démêlage important et le temps supplémentaire lié au comportement sont facturés par tranche de 30 minutes :
                            le temps nécessaire sera évalué sur place.
                        </small>
                    </fieldset>

                    <p class="prestations-total" id="prestations_total" aria-live="polite">
                        <?php if ($lignesChoisies !== null): ?>
                            <?php $totalChoisi = \Project\Core\Prestations::totalEstime($lignesChoisies); ?>
                            Total estimé : <strong><?= htmlspecialchars(\Project\Core\Prestations::totalTexte($totalChoisi)) ?></strong>
                            <small><?= htmlspecialchars(\Project\Core\Prestations::MENTION_DEPLACEMENT) ?><?= \Project\Core\Prestations::contientSurPlace($lignesChoisies) ? ' Suppléments par tranche de 30 min évalués sur place.' : '' ?></small>
                        <?php else: ?>
                            Total estimé : <strong>choisissez un chien et une prestation</strong>
                        <?php endif; ?>
                    </p>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="date_rdv">Date</label>
                            <input type="date" id="date_rdv" name="date_rdv" required
                                   min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                                   aria-describedby="date_rdv_help"
                                   value="<?= htmlspecialchars($_POST['date_rdv'] ?? '') ?>">
                            <small id="date_rdv_help" style="display:block;margin-top:.35rem;color:var(--texte-doux)">
                                Du lundi au samedi, au moins 24 h à l'avance.
                            </small>
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

                    <div class="form-group">
                        <label for="informations">Informations complémentaires (facultatif)</label>
                        <textarea id="informations" name="informations" rows="4"
                                  maxlength="<?= \Project\Entities\Reservation::INFORMATIONS_MAX_LENGTH ?>"
                                  aria-describedby="informations_help"
                                  placeholder="Premier toilettage, poil emmêlé, comportement particulier…"><?= htmlspecialchars(is_string($_POST['informations'] ?? '') ? ($_POST['informations'] ?? '') : '') ?></textarea>
                        <small id="informations_help" style="display:block;margin-top:.35rem;color:var(--texte-doux)">
                            Indiquez ce qui pourrait nous aider à accueillir votre chien (1 000 caractères maximum).
                        </small>
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

        // Prestations : options et prix selon le gabarit du chien, total estimé en direct.
        // Le serveur revalide tout et recalcule les prix à partir du catalogue.
        document.addEventListener('DOMContentLoaded', function () {
            const chien = document.getElementById('id_chien');
            const total = document.getElementById('prestations_total');
            const aide = document.getElementById('gabarit_help');
            if (!chien || !total || !aide) {
                return;
            }

            const principales = Array.from(document.querySelectorAll('input[name="prestation"]'));
            const supplements = Array.from(document.querySelectorAll('input[name="supplements[]"]'));
            const libelles = <?= json_encode(array_map('mb_strtolower', \Project\Core\Prestations::GABARITS)) ?>;
            const mentionDeplacement = <?= json_encode(\Project\Core\Prestations::MENTION_DEPLACEMENT) ?>;
            const formatPrix = function (montant) {
                return montant.toLocaleString('fr-FR', { maximumFractionDigits: 2 }) + '\u00a0€';
            };
            const gabaritCourant = function () {
                return chien.selectedOptions[0]?.dataset.gabarit || null;
            };
            const tarif = function (prix, gabarit) {
                if (gabarit) {
                    if (!Object.prototype.hasOwnProperty.call(prix, gabarit)) {
                        return '';
                    }
                    return prix[gabarit] === null ? 'Sur devis' : formatPrix(prix[gabarit]);
                }
                const connus = Object.values(prix).filter(function (montant) { return montant !== null; });
                if (connus.length === 0) {
                    return 'Sur devis';
                }
                const minimum = Math.min.apply(null, connus);
                return new Set(connus).size > 1 ? 'dès ' + formatPrix(minimum) : formatPrix(minimum);
            };

            const majTotal = function () {
                const gabarit = gabaritCourant();
                const choisie = principales.find(function (radio) { return radio.checked && !radio.disabled; });
                const valeur = document.createElement('strong');
                total.replaceChildren('Total estimé : ', valeur);

                if (!gabarit || !choisie) {
                    valeur.textContent = 'choisissez un chien et une prestation';
                    return;
                }

                let montant = JSON.parse(choisie.dataset.prix)[gabarit];
                let surPlace = false;
                supplements.forEach(function (caseSupplement) {
                    if (!caseSupplement.checked) {
                        return;
                    }
                    if (caseSupplement.dataset.parTranche === '1') {
                        surPlace = true;
                    } else if (montant !== null) {
                        montant += parseFloat(caseSupplement.dataset.prix);
                    }
                });

                valeur.textContent = montant === null ? 'sur devis' : formatPrix(montant);
                const mention = document.createElement('small');
                mention.textContent = mentionDeplacement + (surPlace ? ' Suppléments par tranche de 30 min évalués sur place.' : '');
                total.append(' ', mention);
            };

            const majPrestations = function () {
                const gabarit = gabaritCourant();
                aide.textContent = gabarit
                    ? 'Gabarit retenu d\'après le poids enregistré : ' + libelles[gabarit] + '.'
                    : aide.dataset.defaut;

                principales.forEach(function (radio) {
                    const prix = JSON.parse(radio.dataset.prix);
                    const disponible = !gabarit || Object.prototype.hasOwnProperty.call(prix, gabarit);
                    const option = radio.closest('.prestation-option');
                    option.hidden = !disponible;
                    radio.disabled = !disponible;
                    if (!disponible) {
                        radio.checked = false;
                    }
                    option.querySelector('.prestation-option__prix').textContent = tarif(prix, gabarit);
                });
                majTotal();
            };

            chien.addEventListener('change', majPrestations);
            principales.concat(supplements).forEach(function (champ) {
                champ.addEventListener('change', majTotal);
            });
            majPrestations();
        });
    </script>

    <!-- Liste des réservations -->
    <h2 class="section-title mt-3">Mes rendez-vous</h2>

    <?php if (empty($reservations)): ?>
        <div class="empty-state">Aucun rendez-vous pour le moment.</div>
    <?php else: ?>
        <?php if (empty($showForm)): ?>
            <div class="mb-2">
                <a href="<?= BASE_URL ?>/index.php?controller=reservation&action=create" class="btn btn-primary">
                    + Nouveau rendez-vous
                </a>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="table-wrapper">
                <table class="reservation-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Créneau</th>
                            <th>Chien</th>
                            <th>Race</th>
                            <th>Prestations</th>
                            <th>Informations</th>
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
                                <td class="cell-nowrap"><?= date('d/m/Y', strtotime($r->dateRdv)) ?></td>
                                <td class="cell-nowrap"><?= htmlspecialchars(substr($r->heureRdv, 0, 5)) ?></td>
                                <td><?= htmlspecialchars($r->chienNom) ?></td>
                                <td><?= htmlspecialchars($r->chienRace) ?></td>
                                <td class="reservation-prestations-cell"><?php include __DIR__ . '/_prestations.php'; ?></td>
                                <td class="reservation-informations"><?= $r->informations !== null && $r->informations !== '' ? nl2br(htmlspecialchars($r->informations)) : '—' ?></td>
                                <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($r->statut) ?></span></td>
                                <td>
                                    <?php if ($r->isPending()): ?>
                                        <form method="POST"
                                              action="<?= BASE_URL ?>/index.php?controller=reservation&action=cancel"
                                              onsubmit="return confirm('Annuler ce rendez-vous ?')">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                            <input type="hidden" name="id" value="<?= $r->id ?>">
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

    <?php endif; ?>

</div>
