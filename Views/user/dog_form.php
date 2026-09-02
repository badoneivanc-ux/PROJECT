<div class="container">

    <div class="card mt-3" style="max-width:600px;margin-inline:auto">
          <h2 class="section-title"><?= !empty($dog) ? 'Modifier mon chien' : 'Ajouter mon chien' ?></h2>

        <form method="POST"
              action="<?= BASE_URL ?>/index.php?controller=dog&action=<?= !empty($dog) ? 'editDog&id=' . $dog->id : 'addDog' ?>"
              enctype="multipart/form-data"
              class="form-grid">

            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <div class="form-group">
                <label for="nom">Nom du chien *</label>
                <input type="text" id="nom" name="nom" required
                      maxlength="100" placeholder="Ex : Rex"
                      value="<?= htmlspecialchars($dog->nom ?? '') ?>">
            </div>

            <!-- Race : sélection depuis le référentiel -->
            <div class="form-group">
                <label for="id_race_FK">Race *</label>
                <select id="id_race_FK" name="id_race_FK">
                    <option value="">— Race non répertoriée —</option>
                    <?php foreach ($breeds as $b): ?>
                        <option value="<?= $b->id ?>" <?= !empty($dog) && (int)$dog->idRaceFk === (int)$b->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b->nomRace) ?> (<?= $b->poids ?> kg moy.)
                        </option>
                    <?php endforeach; ?>
                </select>
                <small style="opacity:.7">Si votre race n'est pas dans la liste, renseignez-la ci-dessous.</small>
            </div>

            <!-- Champ libre si race non répertoriée -->
            <div class="form-group" id="custom-race-group" style="display:none">
                <label for="nom_race_custom">Précisez la race</label>
                <input type="text" id="nom_race_custom" name="nom_race_custom"
                       maxlength="100" placeholder="Ex : Berger de Beauce"
                       value="<?= !empty($dog) && empty($dog->idRaceFk) ? htmlspecialchars($dog->nomRace) : '' ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="age">Âge (années) *</label>
                    <input type="number" id="age" name="age" required
                               min="0" max="30" step="0.5" placeholder="Ex : 3"
                               value="<?= htmlspecialchars((string)($dog->age ?? '')) ?>">
                </div>

                <div class="form-group">
                    <label for="poids">Poids (kg) *</label>
                    <input type="number" id="poids" name="poids" required
                              min="0.1" max="150" step="0.1" placeholder="Ex : 12.5"
                              value="<?= htmlspecialchars((string)($dog->poids ?? '')) ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Sexe *</label>
                <div class="form-radio-group">
                    <label class="radio-option">
                        <input type="radio" name="sexe" value="1" required <?= !empty($dog) && (int)$dog->sexe === 1 ? 'checked' : '' ?>>
                        <span>Mâle</span>
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="sexe" value="0" <?= empty($dog) || (int)$dog->sexe === 0 ? 'checked' : '' ?>>
                        <span>Femelle</span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label for="photo_chien">Photo (optionnelle)</label>
                <input type="file" id="photo_chien" name="photo_chien"
                       accept="image/jpeg,image/png,image/webp">
                <small style="opacity:.7">Formats acceptés : JPG, PNG, WEBP</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= !empty($dog) ? 'Enregistrer les modifications' : 'Ajouter mon chien' ?></button>
                <a href="<?= BASE_URL ?>/index.php?controller=user&action=profile"
                   class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>

</div>

<script>
(function () {
    const select = document.getElementById('id_race_FK');
    const customGroup = document.getElementById('custom-race-group');
    const customInput = document.getElementById('nom_race_custom');

    function toggle() {
        const show = select.value === '';
        customGroup.style.display = show ? '' : 'none';
        customInput.required = show;
    }

    select.addEventListener('change', toggle);
    toggle();
})();
</script>
