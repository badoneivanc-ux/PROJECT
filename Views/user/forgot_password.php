<div class="container">
    <div class="form-page">
        <div class="card mt-3">
            <h2 class="card__title">Mot de passe oublié</h2>
            <p class="mb-2">Saisissez l’adresse email associée à votre compte. Si un compte existe, vous recevrez un lien de réinitialisation.</p>

            <form method="POST" action="<?= BASE_URL ?>/index.php?controller=user&action=forgotPassword">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                <div class="form-group">
                    <label for="email">Adresse email</label>
                    <input type="email" id="email" name="email" required autocomplete="email">
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%">Envoyer le lien</button>
            </form>

            <p class="text-center mt-2">
                <a href="<?= BASE_URL ?>/index.php?controller=user&action=login">Retour à la connexion</a>
            </p>
        </div>
    </div>
</div>
