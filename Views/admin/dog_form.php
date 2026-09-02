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
        <div style="margin-bottom:1rem">
            <a href="<?= BASE_URL ?>/index.php?controller=admin&action=dogs" style="color:var(--brown)">&larr; Retour</a>
        </div>

        <h1 style="font-size:1.6rem;color:var(--brown-dark);margin-bottom:1.5rem">
            Ajouter un chien
        </h1>

        <div class="card" style="max-width:600px">
            <form method="POST" action="<?= BASE_URL ?>/index.php?controller=dog&action=create" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label for="nom">Nom du chien</label>
                        <input type="text" id="nom" name="nom" required
                               value="<?= htmlspecialchars(($dog ? $dog->nom : null) ?? $_POST['nom'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="nom_race">Race</label>
                        <input type="text" id="nom_race" name="nom_race" required
                               value="<?= htmlspecialchars(($dog ? $dog->nomRace : null) ?? $_POST['nom_race'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="poids">Poids (kg)</label>
                        <input type="number" id="poids" name="poids" step="0.1" min="0.1" required
                               value="<?= htmlspecialchars((string)(($dog ? $dog->poids : null) ?? $_POST['poids'] ?? '')) ?>">
                    </div>
                    <div class="form-group">
                        <label for="age">Âge (ans)</label>
                        <input type="number" id="age" name="age" step="0.1" min="0" required
                               value="<?= htmlspecialchars((string)(($dog ? $dog->age : null) ?? $_POST['age'] ?? '')) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="sexe">Sexe</label>
                    <select id="sexe" name="sexe" required>
                        <option value="1" <?= ($dog && $dog->sexe === 1) ? 'selected' : '' ?>>Mâle</option>
                        <option value="0" <?= ($dog && $dog->sexe === 0) ? 'selected' : '' ?>>Femelle</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="photo_chien">Photo</label>
                    <?php if (!empty($dog ? $dog->photoChien : '')): ?>
                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($dog->photoChien) ?>"
                             style="width:80px;height:80px;object-fit:cover;border-radius:8px;margin-bottom:.5rem">
                    <?php endif; ?>
                    <input type="file" id="photo_chien" name="photo_chien" accept="image/jpeg,image/png,image/webp">
                </div>

                <button type="submit" class="btn btn-primary">
                    <?= $dog ? 'Enregistrer les modifications' : 'Ajouter le chien' ?>
                </button>
            </form>
        </div>
    </div>

</div>
