<?php

namespace Project\Controllers;

use Project\Models\UserModel;
use Project\Models\ReservationModel;
use Project\Models\DogModel;
use Project\Core\Mailer;

class AdminController extends Controller
{
    /**
     * Tableau de bord : statistiques globales.
     */
    public function dashboard(): void
    {
        $this->requireAdmin();

        $userModel        = new UserModel();
        $reservationModel = new ReservationModel();
        $dogModel         = new DogModel();

        $this->render('admin/dashboard', [
            'totalUsers'          => $userModel->countUsers(),
            'totalReservations'   => $reservationModel->countReservations(),
            'totalDogs'           => $dogModel->countDogs(),
            'pendingReservations' => $reservationModel->countByStatus('en attente'),
        ]);
    }

    /**
     * Liste tous les utilisateurs.
     */
    public function users(): void
    {
        $this->requireAdmin();

        $userModel = new UserModel();
        $users     = $userModel->getAllUsers();

        $this->render('admin/user_form', ['users' => $users]);
    }

    /**
     * Supprime un utilisateur (POST).
     */
    public function deleteUser(): void
    {
        $this->requireAdmin();
        $this->verifyCsrfToken($_POST['csrf_token'] ?? '');
        // Récupère l'ID de l'utilisateur à supprimer depuis le formulaire POST.
        $id = (int)($_POST['id'] ?? 0);

        // Empêche l'admin de se supprimer lui-même
        if ($id && $id !== (int)$_SESSION['user_id']) {
            $userModel = new UserModel();
            $userModel->deleteUser($id);
            $this->setFlash('success', 'Utilisateur supprimé avec succès.');
        }

        $this->redirect('/index.php?controller=admin&action=users');
    }

    /**
     * Liste toutes les réservations.
     */
    public function reservations(): void
    {
        $this->requireAdmin();

        $reservationModel = new ReservationModel();
        $reservations     = $reservationModel->getAllReservations();

        $this->render('admin/reservation_form', ['reservations' => $reservations]);
    }

    /**
     * Met à jour le statut d'une réservation (POST).
     * Statuts autorisés : en attente, confirmé, annulé, terminé
     */
    public function updateReservation(): void
    {
        $this->requireAdmin();
        $this->verifyCsrfToken($_POST['csrf_token'] ?? '');

        $id     = (int)($_POST['id'] ?? 0);
        $statut = $_POST['statut'] ?? '';

        $allowedStatuts = ['en attente', 'confirmé', 'annulé', 'terminé'];
        $sessionMatin      = ['08:30', '09:00', '09:30'];
        $sessionApresMidi  = ['13:30', '14:00', '14:30'];

        if ($id && in_array($statut, $allowedStatuts, true)) {
            $reservationModel = new ReservationModel();
            $reservation      = $reservationModel->getReservationById($id);

            // Réactiver une réservation (vers en attente/confirmé) ne doit pas créer un doublon sur la demi-journée.
            if ($reservation && in_array($statut, ['en attente', 'confirmé'], true)) {
                $heureRdv = substr($reservation->heureRdv, 0, 5);
                $session  = in_array($heureRdv, $sessionMatin, true) ? $sessionMatin : $sessionApresMidi;

                if ($reservationModel->isSessionBookedExcluding($reservation->dateRdv, $session, $id)) {
                    $this->setFlash('error', 'Impossible : une autre réservation active occupe déjà cette demi-journée.');
                    $this->redirect('/index.php?controller=admin&action=reservations');
                }
            }

            $reservationModel->updateStatus($id, $statut);

            // Le client n'est prévenu par email qu'au moment où le rendez-vous passe réellement à "confirmé".
            if ($reservation && $statut === 'confirmé' && $reservation->statut !== 'confirmé') {
                $this->envoyerConfirmationClient($reservation);
            }

            $this->setFlash('success', 'Statut mis à jour avec succès.');
        } else {
            $this->setFlash('error', 'Données invalides.');
        }

        $this->redirect('/index.php?controller=admin&action=reservations');
    }

    /**
     * Envoie au client l'email confirmant que son rendez-vous est validé par l'Atelier du Museau.
     * Un échec d'envoi n'empêche pas le changement de statut.
     */
    private function envoyerConfirmationClient($reservation): bool
    {
        if (empty($reservation->userEmail)) {
            return false;
        }

        $dateFormatee = date('d/m/Y', strtotime($reservation->dateRdv));
        $sujet = 'Votre rendez-vous est confirmé - Atelier du Museau';
        $corps = sprintf(
            '<p>Bonjour %s,</p>'
            . '<p>Votre rendez-vous de toilettage pour <strong>%s</strong> est confirmé par l\'Atelier du Museau :</p>'
            . '<ul><li>Date : %s</li><li>Heure : %s</li></ul>'
            . '<p>À bientôt !<br>Atelier du Museau</p>',
            htmlspecialchars($reservation->userPrenom ?? ''),
            htmlspecialchars($reservation->chienNom ?? ''),
            $dateFormatee,
            htmlspecialchars(substr($reservation->heureRdv, 0, 5))
        );

        return Mailer::send(
            $reservation->userEmail,
            trim(($reservation->userPrenom ?? '') . ' ' . ($reservation->userNom ?? '')),
            $sujet,
            $corps
        );
    }

    /**
     * Liste tous les chiens enregistrés.
     */
    public function dogs(): void
    {
        $this->requireAdmin();

        $dogModel = new DogModel();
        $dogs     = $dogModel->getAllDogs();

        $this->render('admin/dog', ['dogs' => $dogs]);
    }

    /**
     * Liste toutes les races du référentiel (dog_base).
     */
    public function breeds(): void
    {
        $this->requireAdmin();

        $dogModel = new DogModel();
        $breeds   = $dogModel->getAllBreeds();

        $this->render('admin/breeds', ['breeds' => $breeds]);
    }
}