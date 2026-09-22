<?php

namespace Project\Controllers;

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
                $this->setFlash('success', 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.');
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

}