<?php

namespace Project\Controllers;

use Project\Core\Mailer;
use Project\Models\ReservationModel;
use Project\Models\UserModel;
use Project\Models\DogModel;

class ReservationController extends Controller
{
    private ReservationModel $reservationModel;

    /** Créneaux fixes proposés tant que l'activité ne compte qu'une seule intervenante. */
    private const SESSION_MATIN         = ['08:30', '09:00', '09:30'];
    private const SESSION_APRES_MIDI    = ['13:30', '14:00', '14:30'];
    private const CRENEAUX_AUTORISES    = ['08:30', '09:00', '09:30', '13:30', '14:00', '14:30'];
    private const DUREE_CRENEAU_MINUTES = 60;

    public function __construct()
    {
        $this->reservationModel = new ReservationModel();
    }

    /**
     * Liste les réservations de l'utilisateur connecté.
     */
    public function index(): void
    {
        $this->requireUser();

        $reservations = $this->reservationModel->getReservationsByUserId($_SESSION['user_id']);
        $this->render('reservation/index', ['reservations' => $reservations]);
    }

    /**
     * Formulaire + traitement de création d'une réservation.
     */
    public function create(): void
    {
        $this->requireUser();

        $reservations = $this->reservationModel->getReservationsByUserId($_SESSION['user_id']);
        $dogModel     = new DogModel();
        $dogs         = $dogModel->getDogsByUserId($_SESSION['user_id']);

        if ($this->isPost()) {
            $this->verifyCsrfToken($_POST['csrf_token'] ?? '');

            $id_chien  = (int)($_POST['id_chien'] ?? 0);
            $date_rdv  = trim($_POST['date_rdv'] ?? '');
            $heure_rdv = trim($_POST['heure_rdv'] ?? '');

            if (!$id_chien || empty($date_rdv) || !in_array($heure_rdv, self::CRENEAUX_AUTORISES, true)) {
                $this->setFlash('error', 'Tous les champs sont requis.');
                $this->render('reservation/index', ['dogs' => $dogs, 'reservations' => $reservations, 'showForm' => true]);
                return;
            }

            $dog = $dogModel->getDogById($id_chien);
            if (!$dog || $dog->idUserFk !== (int) $_SESSION['user_id']) {
                $this->setFlash('error', 'Chien introuvable ou n\'appartenant pas à votre compte.');
                $this->render('reservation/index', ['dogs' => $dogs, 'reservations' => $reservations, 'showForm' => true]);
                return;
            }

            // La date doit être dans le futur
            if (strtotime($date_rdv . ' ' . $heure_rdv) <= time()) {
                $this->setFlash('error', 'La date et l\'heure du rendez-vous doivent être dans le futur.');
                $this->render('reservation/index', ['dogs' => $dogs, 'reservations' => $reservations, 'showForm' => true]);
                return;
            }

            // Une réservation ne peut pas être prise à moins de 24 h du rendez-vous.
            $reservationDateTime = strtotime($date_rdv . ' ' . $heure_rdv);
            $minimumNoticeTime = time() + (24 * 60 * 60);
            if ($reservationDateTime <= $minimumNoticeTime) {
                $this->setFlash('error', 'Les réservations doivent être faites au moins 24 heures avant le rendez-vous.');
                $this->render('reservation/index', ['dogs' => $dogs, 'reservations' => $reservations, 'showForm' => true]);
                return;
            }

            // Le dimanche est fermé.
            if ((int) date('w', strtotime($date_rdv)) === 0) {
                $this->setFlash('error', 'Les réservations sont fermées le dimanche. Merci de choisir un autre jour.');
                $this->render('reservation/index', ['dogs' => $dogs, 'reservations' => $reservations, 'showForm' => true]);
                return;
            }

            $session = in_array($heure_rdv, self::SESSION_MATIN, true) ? self::SESSION_MATIN : self::SESSION_APRES_MIDI;

            if ($this->reservationModel->isSessionBooked($date_rdv, $session)) {
                $label = $session === self::SESSION_MATIN ? 'du matin' : 'de l\'après-midi';
                $this->setFlash('error', 'La séance ' . $label . ' est déjà réservée pour cette date. Merci de choisir une autre date.');
                $this->render('reservation/index', ['dogs' => $dogs, 'reservations' => $reservations, 'showForm' => true]);
                return;
            }

            $reservationId = $this->reservationModel->createReservation(
                $_SESSION['user_id'],
                $id_chien,
                $date_rdv,
                $heure_rdv,
                self::DUREE_CRENEAU_MINUTES,
                $session
            );

            if ($reservationId) {
                if ($this->envoyerDemandeRecue($dog, $date_rdv, $heure_rdv)) {
                    $this->setFlash('success', 'Réservation créée avec succès. Un email vous confirme la bonne réception de votre demande ; elle sera validée par l’Atelier du Museau.');
                } else {
                    $erreurEmail = Mailer::getLastError();
                    $messageEmail = $erreurEmail !== '' ? ' Détail : ' . $erreurEmail : '';
                    $this->setFlash('success', 'Réservation créée avec succès, mais l’email de confirmation de réception n’a pas pu être envoyé.' . $messageEmail);
                }
                $this->envoyerNotificationAdmin($dog, $date_rdv, $heure_rdv);
                $this->redirect('/index.php?controller=reservation&action=index');
            } else {
                $this->setFlash('error', 'Erreur lors de la création de la réservation.');
                $this->render('reservation/index', ['dogs' => $dogs, 'reservations' => $reservations, 'showForm' => true]);
            }
        } else {
            $this->render('reservation/index', ['dogs' => $dogs, 'reservations' => $reservations, 'showForm' => true]);
        }
    }

    /**
     * Annule une réservation appartenant à l'utilisateur connecté.
     */
    public function cancel(): void
    {
        $this->requireUser();
        $this->verifyCsrfToken($_POST['csrf_token'] ?? '');

        $id          = (int)($_POST['id'] ?? 0);
        $reservation = $id ? $this->reservationModel->getReservationById($id) : null;

        if (!$reservation || $reservation->idUtilisateur !== (int)$_SESSION['user_id']) {
            $this->setFlash('error', 'Réservation introuvable ou accès refusé.');
            $this->redirect('/index.php?controller=reservation&action=index');
        }

        if ($this->reservationModel->updateStatus($id, 'annulé')) {
            $this->setFlash('success', 'Réservation annulée avec succès.');
        } else {
            $this->setFlash('error', 'Erreur lors de l\'annulation.');
        }

        $this->redirect('/index.php?controller=reservation&action=index');
    }

    /**
     * Envoie un email accusant réception de la demande de rendez-vous, encore soumise à validation.
     * Un échec d'envoi n'empêche pas la réservation d'être créée.
     */
    private function envoyerDemandeRecue($dog, string $dateRdv, string $heureRdv): bool
    {
        $user = (new UserModel())->getUserById((int) $_SESSION['user_id']);
        if (!$user || empty($user->email)) {
            return false;
        }

        $dateFormatee = date('d/m/Y', strtotime($dateRdv));
        $sujet = 'Votre demande de rendez-vous a bien été reçue - Atelier du Museau';
        $corps = sprintf(
            '<p>Bonjour %s,</p>'
            . '<p>Votre demande de rendez-vous de toilettage pour <strong>%s</strong> a bien été reçue et est en attente de validation par l\'Atelier du Museau :</p>'
            . '<ul><li>Date souhaitée : %s</li><li>Créneau souhaité : %s</li><li>Adresse d\'intervention : %s</li></ul>'
            . '<p>Vous recevrez un nouvel email dès que ce rendez-vous sera confirmé.</p>'
            . '<p>À bientôt !<br>Atelier du Museau</p>',
            htmlspecialchars($user->prenom),
            htmlspecialchars($dog->nom),
            $dateFormatee,
            htmlspecialchars($heureRdv),
            htmlspecialchars($user->adresseComplete())
        );

        return Mailer::send($user->email, $user->prenom . ' ' . $user->nom, $sujet, $corps);
    }

    /**
     * Prévient l'administrateur qu'une nouvelle demande de rendez-vous attend sa validation.
     * Un échec d'envoi n'empêche pas la réservation d'être créée.
     */
    private function envoyerNotificationAdmin($dog, string $dateRdv, string $heureRdv): void
    {
        if (empty(ADMIN_NOTIFICATION_EMAIL)) {
            return;
        }

        $user = (new UserModel())->getUserById((int) $_SESSION['user_id']);
        if (!$user) {
            return;
        }

        $dateFormatee = date('d/m/Y', strtotime($dateRdv));
        $sujet = 'Nouvelle demande de rendez-vous - Atelier du Museau';
        $corps = sprintf(
            '<p>Bonjour,</p>'
            . '<p>Une nouvelle demande de rendez-vous vient d\'être déposée par %s :</p>'
            . '<ul>'
            . '<li>Chien : %s</li>'
            . '<li>Date souhaitée : %s</li>'
            . '<li>Créneau souhaité : %s</li>'
            . '<li>Adresse d\'intervention : %s</li>'
            . '<li>Contact client : %s</li>'
            . '</ul>'
            . '<p>Merci de la valider ou de la refuser depuis l\'espace d\'administration.</p>',
            htmlspecialchars($user->prenom . ' ' . $user->nom),
            htmlspecialchars($dog->nom),
            $dateFormatee,
            htmlspecialchars($heureRdv),
            htmlspecialchars($user->adresseComplete()),
            htmlspecialchars($user->email)
        );

        Mailer::send(ADMIN_NOTIFICATION_EMAIL, 'Atelier du Museau', $sujet, $corps);
    }
}