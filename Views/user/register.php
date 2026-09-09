<div class="container">
    <div class="form-page">
        <div class="card mt-3">
            <h2 class="card__title">Créer un compte</h2>

            <form method="POST" action="<?= BASE_URL ?>/index.php?controller=user&action=register">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" required
                               value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" required
                               value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Adresse email</label>
                    <input type="email" id="email" name="email" required autocomplete="email"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="telephone">Téléphone <small style="color:var(--grey)">(10 chiffres)</small></label>
                    <input type="tel" id="telephone" name="telephone" required pattern="[0-9]{10}" maxlength="10"
                           value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="adresse">Adresse <small style="color:var(--grey)">(pour le toilettage à domicile)</small></label>
                    <input type="text" id="adresse" name="adresse" required
                           value="<?= htmlspecialchars($_POST['adresse'] ?? '') ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="code_postal">Code postal</label>
                        <input type="text" id="code_postal" name="code_postal" required
                               value="<?= htmlspecialchars($_POST['code_postal'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="ville">Ville</label>
                        <input type="text" id="ville" name="ville" required
                               value="<?= htmlspecialchars($_POST['ville'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe <small style="color:var(--grey)">(8 caractères min.)</small></label>
                    <input type="password" id="password" name="password" required minlength="8">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirmer le mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%">Créer mon compte</button>
            </form>

            <p class="text-center mt-2">
                Déjà un compte ?
                <a href="<?= BASE_URL ?>/index.php?controller=user&action=login">Se connecter</a>
            </p>
        </div>
    </div>
</div>
