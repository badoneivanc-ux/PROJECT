<div class="container">
    <div class="form-page">
        <div class="card mt-3">
            <h2 class="card__title">Nouveau mot de passe</h2>

            <?php if (!empty($validToken)): ?>
                <p class="mb-2">Choisissez un nouveau mot de passe de 8 à 72 caractères.</p>

                <form method="POST" action="<?= BASE_URL ?>/index.php?controller=user&action=resetPassword">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">

                    <div class="form-group">
                        <label for="password">Nouveau mot de passe</label>
                        <input type="password" id="password" name="password" required minlength="8" maxlength="72" autocomplete="new-password">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmer le mot de passe</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8" maxlength="72" autocomplete="new-password">
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%">Modifier le mot de passe</button>
                </form>
            <?php else: ?>
                <p>Ce lien de réinitialisation est invalide ou a expiré. Demandez un nouveau lien pour continuer.</p>
                <p class="text-center mt-2">
                    <a class="btn btn-primary" href="<?= BASE_URL ?>/index.php?controller=user&action=forgotPassword">Demander un nouveau lien</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>
