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
        <div style="margin-bottom:1rem">
            <a href="<?= BASE_URL ?>/index.php?controller=admin&action=breeds" style="color:var(--brown)">&larr; Retour aux races</a>
        </div>

        <h1 style="font-size:1.6rem;color:var(--brown-dark);margin-bottom:1.5rem">
            <?= $breed ? 'Modifier la race' : 'Ajouter une race' ?>
        </h1>

        <div class="card">
            <?php
                $formAction = $breed
                    ? BASE_URL . '/index.php?controller=dog&action=editBreed&id=' . $breed->id
                    : BASE_URL . '/index.php?controller=dog&action=createBreed';
            ?>
            <form method="POST" action="<?= $formAction ?>" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label for="nom_race">Nom de la race *</label>
                        <input type="text" id="nom_race" name="nom_race" required maxlength="100"
                               value="<?= htmlspecialchars($breed ? $breed->nomRace : '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="poids">Poids moyen (kg)</label>
                        <input type="number" id="poids" name="poids" step="0.1" min="0.1"
                               value="<?= htmlspecialchars((string)($breed ? $breed->poids : '')) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="photo_race">Photo de la race</label>
                    <?php if (!empty($breed ? $breed->photoRace : '')): ?>
                        <div style="margin-bottom:.5rem">
                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($breed->photoRace) ?>"
                                 alt="<?= htmlspecialchars($breed ? $breed->nomRace : '') ?>"
                                 style="height:100px;border-radius:var(--radius);object-fit:cover">
                        </div>
                    <?php endif; ?>
                    <input type="file" id="photo_race" name="photo_race"
                           accept="image/jpeg,image/png,image/webp">
                    <small style="opacity:.7">Formats acceptés : JPG, PNG, WEBP<?= !empty($breed ? $breed->photoRace : '') ? ' — laisser vide pour conserver la photo actuelle' : '' ?></small>
                </div>

                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" required rows="4"
                              maxlength="1000"><?= htmlspecialchars($breed ? $breed->description : '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="historique">Historique de la race</label>
                    <textarea id="historique" name="historique" rows="4"
                              maxlength="2000"><?= htmlspecialchars($breed ? $breed->historique : '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="caracteristiques">Caractéristiques
                        <small style="opacity:.7;font-weight:normal">(Taille, caractère, énergie, longévité...)</small>
                    </label>
                    <input type="text" id="caracteristiques" name="caracteristiques" maxlength="500"
                           placeholder="Ex : Taille : petit (25-30 cm) | Caractère : joueur | Énergie : modérée"
                           value="<?= htmlspecialchars($breed ? $breed->caracteristiques : '') ?>">
                </div>

                <div class="form-group">
                    <label for="entretien">Conseils d'entretien *</label>
                    <textarea id="entretien" name="entretien" required rows="4"
                              maxlength="1000"><?= htmlspecialchars($breed ? $breed->entretien : '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="astuces_toilettage">Astuces de toilettage</label>
                    <textarea id="astuces_toilettage" name="astuces_toilettage" rows="4"
                              maxlength="1000"><?= htmlspecialchars($breed ? $breed->astucesToilettage : '') ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <?= $breed ? 'Enregistrer les modifications' : 'Ajouter la race' ?>
                    </button>
                    <a href="<?= BASE_URL ?>/index.php?controller=admin&action=breeds" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>

    </div>

</div>
