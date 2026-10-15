<?php

namespace Project\Controllers;

use Project\Core\Mailer;
use Project\Models\UserModel;
use Project\Models\DogModel;
use Project\Models\ReservationModel;

class UserController extends Controller
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Affiche le formulaire de connexion et gère la soumission.
     */
    public function login(): void
    {
        if ($this->isPost()) {
            $this->verifyCsrfToken($_POST['csrf_token'] ?? '');

            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $this->setFlash('error', 'Tous les champs sont requis.');
                $this->render('user/login');
                return;
            }

            $user = $this->userModel->getUserByEmail($email);

            if ($user && password_verify($password, $user->mdp)) {
                $this->regenerateSession();
                $_SESSION['user_id']     = $user->id;
                $_SESSION['user_nom']    = $user->nom;
                $_SESSION['user_prenom'] = $user->prenom;
                $_SESSION['user_role']   = $user->role;

                $this->setFlash('success', 'Bienvenue ' . htmlspecialchars($user->prenom) . ' !');

                if ($user->isAdmin()) {
                    $this->redirect('/index.php?controller=admin&action=dashboard');
                } else {
                    $this->redirect('/index.php?controller=user&action=profile');
                }
            } else {
                $this->setFlash('error', 'Email ou mot de passe incorrect.');
                $this->render('user/login');
            }
        } else {
            $this->render('user/login');
        }
    }

    /**
     * Envoie un lien de réinitialisation sans révéler si l'adresse existe.
     */
    public function forgotPassword(): void
    {
        if ($this->isPost()) {
            $csrfToken = $_POST['csrf_token'] ?? '';
            $this->verifyCsrfToken(is_string($csrfToken) ? $csrfToken : '');

            $email = $_POST['email'] ?? '';
            $email = is_string($email) ? trim($email) : '';

            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $user = $this->userModel->getUserByEmail($email);
                if ($user) {
                    $token = bin2hex(random_bytes(32));
                    $tokenHash = hash('sha256', $token);
                    $expiresAt = (new \DateTimeImmutable())->modify('+30 minutes');

                    if ($this->userModel->issuePasswordResetToken($user->id, $tokenHash, $expiresAt)) {
                        $resetUrl = $this->passwordResetBaseUrl()
                            . '/index.php?controller=user&action=resetPassword&token='
                            . rawurlencode($token);
                        $safeUrl = htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8');
                        $body = '<p>Bonjour,</p>'
                            . '<p>Une demande de réinitialisation du mot de passe de votre compte a été effectuée.</p>'
                            . '<p><a href="' . $safeUrl . '">Choisir un nouveau mot de passe</a></p>'
                            . '<p>Ce lien expirera dans 30 minutes et ne peut être utilisé qu’une seule fois.</p>'
                            . '<p>Si vous n’êtes pas à l’origine de cette demande, ignorez cet email.</p>';

                        if (!Mailer::send($user->email, $user->prenom . ' ' . $user->nom, 'Réinitialisation de votre mot de passe', $body)) {
                            error_log('[Réinitialisation mot de passe] Email non envoyé : ' . Mailer::getLastError());
                        }
                    }
                }
            }

            $this->setFlash(
                'success',
                'Si un compte correspond à cette adresse, un lien de réinitialisation vient d’être envoyé.'
            );
            $this->redirect('/index.php?controller=user&action=forgotPassword');
        }

        $this->render('user/forgot_password');
    }

    /**
     * Valide le lien puis permet de définir un nouveau mot de passe.
     */
    public function resetPassword(): void
    {
        if ($this->isPost()) {
            $csrfToken = $_POST['csrf_token'] ?? '';
            $this->verifyCsrfToken(is_string($csrfToken) ? $csrfToken : '');

            $token = $_POST['token'] ?? '';
            $sessionToken = $_SESSION['password_reset_token'] ?? '';
            if (!is_string($token) || !is_string($sessionToken)
                || $token === '' || !hash_equals($sessionToken, $token)) {
                unset($_SESSION['password_reset_token']);
                $this->setFlash('error', 'Ce lien de réinitialisation est invalide ou a expiré.');
                $this->render('user/reset_password', ['validToken' => false]);
                return;
            }

            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $validToken = $this->userModel->getPasswordResetUserId(hash('sha256', $token)) !== null;

            if (!$validToken) {
                unset($_SESSION['password_reset_token']);
                $this->setFlash('error', 'Ce lien de réinitialisation est invalide ou a expiré.');
                $this->render('user/reset_password', ['validToken' => false]);
                return;
            }

            if (!is_string($password) || !is_string($confirmPassword)
                || strlen($password) < 8 || strlen($password) > 72) {
                $this->setFlash('error', 'Le mot de passe doit contenir entre 8 et 72 caractères.');
                $this->render('user/reset_password', ['validToken' => true, 'token' => $token]);
                return;
            }

            if ($password !== $confirmPassword) {
                $this->setFlash('error', 'Les mots de passe ne correspondent pas.');
                $this->render('user/reset_password', ['validToken' => true, 'token' => $token]);
                return;
            }

            if (!$this->userModel->resetPassword(hash('sha256', $token), password_hash($password, PASSWORD_BCRYPT))) {
                unset($_SESSION['password_reset_token']);
                $this->setFlash('error', 'Ce lien de réinitialisation est invalide ou a expiré.');
                $this->render('user/reset_password', ['validToken' => false]);
                return;
            }

            unset($_SESSION['password_reset_token']);
            unset($_SESSION['user_id'], $_SESSION['user_nom'], $_SESSION['user_prenom'], $_SESSION['user_role']);
            $this->regenerateSession();
            $this->setFlash('success', 'Votre mot de passe a été modifié. Vous pouvez maintenant vous connecter.');
            $this->redirect('/index.php?controller=user&action=login');
        }

        $token = $_GET['token'] ?? '';
        if (is_string($token) && preg_match('/^[a-f0-9]{64}$/', $token) === 1) {
            if ($this->userModel->getPasswordResetUserId(hash('sha256', $token)) !== null) {
                $_SESSION['password_reset_token'] = $token;
                $this->redirect('/index.php?controller=user&action=resetPassword');
            }
        }

        $token = $_SESSION['password_reset_token'] ?? '';
        $validToken = is_string($token)
            && $token !== ''
            && $this->userModel->getPasswordResetUserId(hash('sha256', $token)) !== null;
        if (!$validToken) {
            unset($_SESSION['password_reset_token']);
            $this->setFlash('error', 'Ce lien de réinitialisation est invalide ou a expiré.');
        }

        $this->render('user/reset_password', [
            'validToken' => $validToken,
            'token' => $validToken ? $token : '',
        ]);
    }

    /**
     * Affiche le formulaire d'inscription et gère la soumission.
     */
    public function register(): void
    {
        if ($this->isPost()) {
            $this->verifyCsrfToken($_POST['csrf_token'] ?? '');

            $nom            = trim($_POST['nom'] ?? '');
            $prenom         = trim($_POST['prenom'] ?? '');
            $email          = trim($_POST['email'] ?? '');
            $password       = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $adresse        = trim($_POST['adresse'] ?? '');
            $codePostal     = trim($_POST['code_postal'] ?? '');
            $ville          = trim($_POST['ville'] ?? '');
            $telephone      = trim($_POST['telephone'] ?? '');

            if (empty($nom) || empty($prenom) || empty($email) || empty($password)
                || empty($adresse) || empty($codePostal) || empty($ville) || empty($telephone)) {
                $this->setFlash('error', 'Tous les champs sont requis, y compris l\'adresse d\'intervention et le téléphone.');
                $this->render('user/register');
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->setFlash('error', 'Adresse email invalide.');
                $this->render('user/register');
                return;
            }

            if (empty($_POST['terms'])) {
                $this->setFlash('error', 'Vous devez accepter les conditions générales pour créer un compte.');
                $this->render('user/register');
                return;
            }

            if (!preg_match('/^[0-9]{5}$/', $codePostal)) {
                $this->setFlash('error', 'Le code postal doit contenir exactement 5 chiffres.');
                $this->render('user/register');
                return;
            }

            if (!preg_match('/^[0-9]+$/', $telephone) || strlen($telephone) !== 10) {
                $this->setFlash('error', 'Le numéro de téléphone doit contenir exactement 10 chiffres.');
                $this->render('user/register');
                return;
            }

            if (strlen($password) < 8) {
                $this->setFlash('error', 'Le mot de passe doit contenir au moins 8 caractères.');
                $this->render('user/register');
                return;
            }

            if ($password !== $confirmPassword) {
                $this->setFlash('error', 'Les mots de passe ne correspondent pas.');
                $this->render('user/register');
                return;
            }

            if ($this->userModel->getUserByEmail($email)) {
                $this->setFlash('error', 'Cette adresse email est déjà utilisée.');
                $this->render('user/register');
                return;
            }

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $userId = $this->userModel->createUser($nom, $prenom, $email, $hashedPassword, $adresse, $codePostal, $ville, $telephone);

            if ($userId) {
                $client = [
                    'prenom'      => $prenom,
                    'nom'         => $nom,
                    'email'       => $email,
                    'adresse'     => $adresse,
                    'code_postal' => $codePostal,
                    'ville'       => $ville,
                    'telephone'   => $telephone,
                ];
                $detailEmail = '';
                try {
                    if (!$this->sendWelcomeEmail($client)) {
                        $detailEmail = Mailer::getLastError() ?: 'envoi refusé';
                        error_log('[Inscription] Mail de bienvenue non envoyé à ' . $email . ' : ' . $detailEmail);
                    }
                } catch (\Throwable $e) {
                    $detailEmail = $e->getMessage();
                    error_log('[Inscription] Mail de bienvenue en erreur : ' . $e->getMessage() . ' (' . $e->getFile() . ':' . $e->getLine() . ')');
                }

                try {
                    $this->sendNotificationNewUser($client);
                } catch (\Throwable $e) {
                    error_log('[Inscription] Notification admin en erreur : ' . $e->getMessage() . ' (' . $e->getFile() . ':' . $e->getLine() . ')');
                }

                $message = 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.';
                if ($detailEmail !== '') {
                    $message .= ' Toutefois, l\'email de bienvenue n\'a pas pu être envoyé. Détail : ' . $detailEmail;
                }
                $this->setFlash('success', $message);
                $this->redirect('/index.php?controller=user&action=login');
            } else {
                $this->setFlash('error', 'Une erreur est survenue lors de la création du compte.');
                $this->render('user/register');
            }
        } else {
            $this->render('user/register');
        }
    }

    /**
     * Déconnecte l'utilisateur et détruit la session.
     */
    public function logout(): void
    {
        session_destroy();
        $this->redirect('/');
    }

    /**
     * Affiche le profil de l'utilisateur connecté avec ses chiens et réservations.
     */
    public function profile(): void
    {
        $this->requireUser();

        $userId = $_SESSION['user_id'];
        $user   = $this->userModel->getUserById($userId);

        $dogModel  = new DogModel();
        $dogs      = $dogModel->getDogsByUserId($userId);

        $reservationModel = new ReservationModel();
        $reservations     = $reservationModel->getReservationsByUserId($userId);

        $this->render('user/profile', [
            'user'         => $user,
            'dogs'         => $dogs,
            'reservations' => $reservations,
        ]);
    }

    /**
     * Met à jour les informations du client connecté.
     */
    public function updateProfile(): void
    {
        $this->requireUser();
        $this->verifyCsrfToken($_POST['csrf_token'] ?? '');

        $nom        = trim($_POST['nom'] ?? '');
        $prenom     = trim($_POST['prenom'] ?? '');
        $email      = trim($_POST['email'] ?? '');
        $telephone  = trim($_POST['telephone'] ?? '');
        $adresse    = trim($_POST['adresse'] ?? '');
        $codePostal = trim($_POST['code_postal'] ?? '');
        $ville      = trim($_POST['ville'] ?? '');

        $existingUser = $this->userModel->getUserByEmail($email);

        if (empty($nom) || empty($prenom) || empty($email) || empty($telephone)
            || empty($adresse) || empty($codePostal) || empty($ville)) {
            $this->setFlash('error', 'Tous les champs sont requis.');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('error', 'Adresse email invalide.');
        } elseif ($existingUser && $existingUser->id !== (int) $_SESSION['user_id']) {
            $this->setFlash('error', 'Cette adresse email est déjà utilisée.');
        } elseif (!preg_match('/^[0-9]{5}$/', $codePostal)) {
            $this->setFlash('error', 'Le code postal doit contenir exactement 5 chiffres.');
        } elseif (!preg_match('/^[0-9]{10}$/', $telephone)) {
            $this->setFlash('error', 'Le numéro de téléphone doit contenir exactement 10 chiffres.');
        } elseif ($this->userModel->updateUser(
            (int) $_SESSION['user_id'],
            $nom,
            $prenom,
            $email,
            $adresse,
            $codePostal,
            $ville,
            $telephone
        )) {
            $this->setFlash('success', 'Profil mis à jour avec succès.');
        } else {
            $this->setFlash('error', 'Erreur lors de la mise à jour du profil.');
        }

        $this->redirect('/index.php?controller=user&action=profile');
    }

    /**
     * URL absolue du site pour les liens des emails. Se replie sur SITE_URL du .env,
     * puis sur l'adresse de la requête, si la constante n'est pas définie par config.php.
     */
    private function siteUrl(): string
    {
        if (defined('SITE_URL')) {
            return SITE_URL;
        }

        $url = $_ENV['SITE_URL'] ?? getenv('SITE_URL') ?: '';
        if ($url === '' && !empty($_SERVER['HTTP_HOST'])) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $url    = $scheme . '://' . $_SERVER['HTTP_HOST'] . (defined('BASE_URL') ? BASE_URL : '');
        }

        return rtrim($url, '/');
    }

    /**
     * N'utilise pas l'en-tête Host de la requête pour fabriquer un lien de récupération en production.
     */
    private function passwordResetBaseUrl(): string
    {
        $configuredUrl = $_ENV['SITE_URL'] ?? getenv('SITE_URL') ?: '';
        if ($configuredUrl !== '') {
            $parts = parse_url($configuredUrl);
            if (is_array($parts)
                && in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
                && !empty($parts['host'])
                && !isset($parts['user'])
                && !isset($parts['pass'])
                && !isset($parts['query'])
                && !isset($parts['fragment'])) {
                return rtrim($configuredUrl, '/');
            }

            error_log('[Réinitialisation mot de passe] SITE_URL invalide.');
        }

        if ((defined('APP_ENV') ? APP_ENV : 'production') === 'development') {
            return $this->siteUrl();
        }

        return 'https://latelierdumuseau.fr';
    }

    /**
     * Envoie le mail de bienvenue avec le récapitulatif des informations saisies.
     * Un échec d'envoi n'empêche pas l'inscription.
     */
    private function sendWelcomeEmail(array $client): bool
    {
        $sujet = 'Bienvenue à l\'Atelier du Museau !';
        $corps = sprintf(
            '<p>Bonjour %s,</p>'
            . '<p>Merci de vous être inscrit(e) à l\'Atelier du Museau, votre service de toilettage canin à domicile. Votre compte est bien créé !</p>'
            . '<p>Voici les informations enregistrées :</p>'
            . '<ul>'
            . '<li>Nom : %s %s</li>'
            . '<li>Email : %s</li>'
            . '<li>Téléphone : %s</li>'
            . '<li>Adresse d\'intervention : %s, %s %s</li>'
            . '</ul>'
            . '<p><strong>Prochaines étapes :</strong></p>'
            . '<ol>'
            . '<li><a href="%s">Connectez-vous à votre espace client</a>.</li>'
            . '<li>Ajoutez votre compagnon depuis votre profil.</li>'
            . '<li>Demandez un rendez-vous : vous recevrez un email dès qu\'il sera confirmé.</li>'
            . '</ol>'
            . '<p>Une erreur dans vos informations ? Vous pouvez les modifier à tout moment depuis votre profil.</p>'
            . '<p>À bientôt !<br><br><span style="display: inline-block; line-height: 1.2;">Marion<br>L\'Atelier du Museau</span></p>',
            htmlspecialchars($client['prenom']),
            htmlspecialchars($client['prenom']),
            htmlspecialchars($client['nom']),
            htmlspecialchars($client['email']),
            htmlspecialchars($client['telephone']),
            htmlspecialchars($client['adresse']),
            htmlspecialchars($client['code_postal']),
            htmlspecialchars($client['ville']),
            htmlspecialchars($this->siteUrl() . '/index.php?controller=user&action=login')
        );

        return Mailer::send(
            $client['email'],
            $client['prenom'] . ' ' . $client['nom'],
            $sujet,
            $corps
        );
    }

    /**
     * Prévient l'administrateur qu'un nouveau client vient de s'inscrire.
     * Un échec d'envoi n'empêche pas l'inscription.
     */
    private function sendNotificationNewUser(array $client): void
    {
        if (empty(ADMIN_NOTIFICATION_EMAIL)) {
            return;
        }

        $sujet = 'Nouveau(elle) client(e) : ' . $client['prenom'] . ' ' . $client['nom'];
        $corps = sprintf(
            '<p>Bonjour,</p>'
            . '<p>Un(e) nouveau(elle) client(e) vient de s\'inscrire sur le site le %s :</p>'
            . '<ul>'
            . '<li>Prénom : %s</li>'
            . '<li>Nom : %s</li>'
            . '<li>Email : %s</li>'
            . '<li>Téléphone : %s</li>'
            . '<li>Adresse d\'intervention : %s, %s %s</li>'
            . '</ul>',
            date('d/m/Y à H:i'),
            htmlspecialchars($client['prenom']),
            htmlspecialchars($client['nom']),
            htmlspecialchars($client['email']),
            htmlspecialchars($client['telephone']),
            htmlspecialchars($client['adresse']),
            htmlspecialchars($client['code_postal']),
            htmlspecialchars($client['ville'])
        );

        Mailer::send(ADMIN_NOTIFICATION_EMAIL, 'Atelier du Museau', $sujet, $corps);
    }
}