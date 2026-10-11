<div class="admin-layout admin-deletion-layout">
    <aside class="admin-sidebar">
        <div class="admin-sidebar__brand">
            <img src="<?= BASE_URL ?>/logo.jpg" alt="Atelier du Museau" class="admin-sidebar__logo">
        </div>
        <ul class="admin-sidebar__nav">
            <li><a href="<?= BASE_URL ?>/index.php?controller=admin&action=dashboard">Tableau de bord</a></li>
            <li><a href="<?= BASE_URL ?>/index.php?controller=admin&action=reservations">Réservations</a></li>
            <li><a href="<?= BASE_URL ?>/index.php?controller=admin&action=dogs"<?= $type === 'dog' ? ' class="active"' : '' ?>>Chiens</a></li>
            <li><a href="<?= BASE_URL ?>/index.php?controller=admin&action=breeds">Races</a></li>
            <li><a href="<?= BASE_URL ?>/index.php?controller=admin&action=users"<?= $type === 'user' ? ' class="active"' : '' ?>>Utilisateurs</a></li>
            <li><a href="<?= BASE_URL ?>/">← Retour au site</a></li>
        </ul>
    </aside>

    <div class="admin-content">
        <div class="card deletion-confirmation">
            <h1 class="card__title">Confirmer la suppression <?= $type === 'user' ? 'de l’utilisateur' : 'du chien' ?></h1>
            <p><strong><?= htmlspecialchars($targetName) ?></strong><br><?= htmlspecialchars($targetDetails) ?></p>
            <div class="deletion-confirmation__warning" id="deletion-warning">
                <strong>Attention : cette action est irréversible.</strong>
                <p>Elle supprimera définitivement :</p>
                <ul>
                    <?php if ($type === 'user'): ?>
                        <li>Le compte de cet utilisateur.</li>
                    <?php endif; ?>
                    <li><?= $impact['dogs'] ?> chien<?= $impact['dogs'] > 1 ? 's' : '' ?><?= $type === 'user' ? ' associé' . ($impact['dogs'] > 1 ? 's' : '') : '' ?>.</li>
                    <li><?= $impact['reservations'] ?> réservation<?= $impact['reservations'] > 1 ? 's' : '' ?>, tous statuts confondus (y compris les rendez-vous terminés ou annulés).</li>
                </ul>
                <?php if ($type === 'dog'): ?>
                    <p>Le compte du propriétaire et ses autres chiens seront conservés.</p>
                <?php endif; ?>
            </div>

            <form method="POST" action="<?= BASE_URL ?><?= htmlspecialchars($deleteUrl) ?>" id="deletion-confirmation-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="id" value="<?= $id ?>">
                <input type="hidden" name="deletion_token" value="<?= htmlspecialchars($deletionToken) ?>">
                <div class="form-group">
                    <label for="deletion-confirmation-input">Saisissez exactement SUPPRIMER pour confirmer</label>
                    <input type="text" name="confirmation" id="deletion-confirmation-input"
                           required pattern="SUPPRIMER" autocomplete="off" spellcheck="false"
                           aria-describedby="deletion-warning">
                </div>
                <div class="deletion-confirmation__actions">
                    <a href="<?= BASE_URL ?><?= htmlspecialchars($returnUrl) ?>" class="btn btn-primary" autofocus>Annuler</a>
                    <button type="submit" class="btn btn-danger" id="deletion-confirmation-submit">Supprimer définitivement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (() => {
        const form = document.getElementById('deletion-confirmation-form');
        const input = document.getElementById('deletion-confirmation-input');
        const button = document.getElementById('deletion-confirmation-submit');
        const updateButton = () => {
            button.disabled = input.value !== 'SUPPRIMER';
        };
        updateButton();
        input.addEventListener('input', updateButton);
        window.addEventListener('pageshow', updateButton);
        form.addEventListener('submit', (event) => {
            if (input.value !== 'SUPPRIMER') {
                event.preventDefault();
            }
        });
    })();
</script>
