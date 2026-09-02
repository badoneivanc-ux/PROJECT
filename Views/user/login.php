<div class="container">
    <div class="form-page">
        <div class="card mt-3">
            <h2 class="card__title">Connexion</h2>

            <form method="POST" action="<?= BASE_URL ?>/index.php?controller=user&action=login">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                <div class="form-group">
                    <label for="email">Adresse email</label>
                    <input type="email" id="email" name="email" required autocomplete="email"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%">Se connecter</button>
            </form>

            <p class="text-center mt-2">
                Pas encore de compte ?
                <a href="<?= BASE_URL ?>/index.php?controller=user&action=register">S'inscrire</a>
            </p>
        </div>
    </div>
</div>
