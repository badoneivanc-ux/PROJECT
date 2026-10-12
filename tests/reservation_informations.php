<?php

declare(strict_types=1);

namespace Project\Core {
    // Aucun email reel ne doit partir pendant ce test.
    final class Mailer
    {
        public static int $sent = 0;
        public static array $messages = [];

        public static function send(string $to, string $toName, string $subject, string $htmlBody): bool
        {
            self::$sent++;
            self::$messages[] = ['to' => $to, 'body' => $htmlBody];
            return true;
        }
    }
}

namespace Project\Controllers {
    const ADMIN_NOTIFICATION_EMAIL = 'admin@example.test';
}

namespace {
    use Project\Controllers\ReservationController;
    use Project\Core\Database;
    use Project\Core\Mailer;
    use Project\Entities\Reservation;
    use Project\Models\ReservationModel;
    use Project\Models\UserModel;

    define('BASE_URL', '');
    require_once __DIR__ . '/../Views/Autoloader.php';
    require_once __DIR__ . '/../Core/dbConnect.php';

    final class InformationsTestRedirect extends RuntimeException {}

    final class InformationsTestController extends ReservationController
    {
        public array $viewData = [];

        protected function redirect(string $url): void
        {
            throw new InformationsTestRedirect($url);
        }

        protected function render(string $path, array $data = []): void
        {
            $this->viewData = $data + ['csrfToken' => $this->generateCsrfToken()];
        }
    }

    $sqliteClass = class_exists('Pdo\\Sqlite') ? 'Pdo\\Sqlite' : PDO::class;
    $db = new $sqliteClass('sqlite::memory:', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $registerFunction = method_exists($db, 'createFunction') ? 'createFunction' : 'sqliteCreateFunction';
    $db->$registerFunction('TIME_FORMAT', static fn(string $time, string $format): string => substr($time, 0, 5), 2);
    $db->$registerFunction('GET_LOCK', static fn(string $name, int $timeout): int => 1, 2);
    $db->$registerFunction('RELEASE_LOCK', static fn(string $name): int => 1, 1);
    (new ReflectionProperty(Database::class, 'instance'))->setValue(null, $db);
    $db->exec("CREATE TABLE user_dog (
        id_utilisateur_PK INTEGER PRIMARY KEY, nom TEXT, prenom TEXT,
        email TEXT, id_role TEXT, date_inscription TEXT,
        adresse TEXT DEFAULT '', code_postal TEXT DEFAULT '', ville TEXT DEFAULT ''
    );
    CREATE TABLE chien (
        id_chien_PK INTEGER PRIMARY KEY, nom TEXT, nom_race TEXT,
        poids INTEGER, age INTEGER, sexe INTEGER, id_user_FK INTEGER
    );
    CREATE TABLE reservation (
        id_rdv INTEGER PRIMARY KEY AUTOINCREMENT, date_rdv TEXT, heure_rdv TEXT,
        duree_minutes INTEGER, statut TEXT, id_utilisateur_PK INTEGER,
        id_chien_PK INTEGER, informations TEXT DEFAULT NULL,
        gabarit TEXT DEFAULT NULL, prix_estime REAL DEFAULT NULL
    );
    CREATE TABLE reservation_prestation (
        id INTEGER PRIMARY KEY AUTOINCREMENT, id_rdv_FK INTEGER NOT NULL, code TEXT NOT NULL,
        libelle TEXT NOT NULL, type TEXT NOT NULL, prix REAL DEFAULT NULL,
        evalue_sur_place INTEGER NOT NULL DEFAULT 0
    );
    CREATE TABLE indisponibilite (id INTEGER PRIMARY KEY, date_seance TEXT, seance TEXT);
    INSERT INTO user_dog (id_utilisateur_PK, nom, prenom, email, id_role, date_inscription)
        VALUES (2, 'Client', 'Jean', 'client@example.test', 'user', '2026-01-01');
    INSERT INTO chien VALUES (10, 'Rex', 'Caniche', 10, 3, 1, 2)");

    $date = (new DateTimeImmutable('+3 days'))->format('Y-m-d');
    while ((int) date('w', strtotime($date)) === 0) {
        $date = (new DateTimeImmutable($date))->modify('+1 day')->format('Y-m-d');
    }
    $checks = 0;
    function informationsCheck(bool $condition, string $message): void
    {
        global $checks;
        if (!$condition) {
            throw new RuntimeException($message);
        }
        $checks++;
    }

    $model = new ReservationModel();
    $text = "Premier toilettage <script>alert('test')</script>\nPoil emmêlé";
    $basePost = ['csrf_token' => 'test-csrf', 'id_chien' => 10, 'date_rdv' => $date, 'heure_rdv' => '09:00', 'prestation' => 'bain_brushing'];
    foreach ([
        [[], null],
        [['informations' => ''], null],
        [['informations' => " \n\t "], null],
        [['informations' => "  $text  "], $text],
        [['informations' => str_repeat('é', 1000)], str_repeat('é', 1000)],
        [['informations' => '0'], '0'],
    ] as [$input, $expected]) {
        $db->exec('DELETE FROM reservation');
        $_SESSION = ['user_id' => 2, 'user_role' => 'user', 'csrf_token' => 'test-csrf'];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = $basePost + $input;
        $sent = Mailer::$sent;
        try {
            (new InformationsTestController())->create();
            throw new RuntimeException('Expected successful reservation redirect');
        } catch (InformationsTestRedirect $response) {
            informationsCheck($response->getMessage() === '/index.php?controller=reservation&action=index', 'Wrong redirect');
        }
        informationsCheck($model->countReservations() === 1, 'Reservation not created');
        $reservation = $model->getReservationsByUserId(2)[0];
        informationsCheck($reservation->informations === $expected, 'Information not normalized or hydrated');
        informationsCheck($model->getReservationById($reservation->id)->informations === $expected, 'Single reservation lost information');
        $messages = array_slice(Mailer::$messages, $sent);
        informationsCheck(count($messages) === 2, 'Client or admin email not sent');
        informationsCheck($messages[0]['to'] === 'client@example.test' && $messages[1]['to'] === 'admin@example.test', 'Wrong email recipients');
        foreach ($messages as $message) {
            if ($expected === null) {
                informationsCheck(!str_contains($message['body'], 'Informations complémentaires'), 'Empty information section shown in email');
            } else {
                informationsCheck(str_contains($message['body'], '<strong>Informations complémentaires :</strong><br>' . nl2br(htmlspecialchars($expected))), 'Email information missing or not formatted');
                informationsCheck(!str_contains($message['body'], '<script>alert'), 'Unsafe HTML in email');
            }
        }
        informationsCheck($_SESSION['flash']['type'] === 'success', 'Creation did not report success');
    }

    foreach ([[], str_repeat('é', 1001), "\xff"] as $invalid) {
        $db->exec('DELETE FROM reservation');
        $_POST = $basePost + ['informations' => $invalid];
        $controller = new InformationsTestController();
        $sent = Mailer::$sent;
        $controller->create();
        informationsCheck($model->countReservations() === 0 && Mailer::$sent === $sent, 'Invalid information created reservation or sent mail');
        informationsCheck($_SESSION['flash']['type'] === 'error', 'Invalid information not reported');
        extract($controller->viewData);
        ob_start();
        include __DIR__ . '/../Views/reservation/index.php';
        $html = ob_get_clean();
        informationsCheck(str_contains($html, 'name="informations"') && str_contains($html, 'maxlength="1000"'), 'Optional field missing or wrong limit');
        informationsCheck(!preg_match('/<textarea[^>]*\brequired\b/', $html), 'Optional field marked required');
        if (is_string($invalid) && preg_match('//u', $invalid) === 1) {
            informationsCheck(str_contains($html, $invalid), 'Rejected text not preserved');
        }
    }

    $_POST = $basePost + ['informations' => $text];
    $_POST['id_chien'] = 0;
    $controller = new InformationsTestController();
    $controller->create();
    extract($controller->viewData);
    ob_start();
    include __DIR__ . '/../Views/reservation/index.php';
    $html = ob_get_clean();
    informationsCheck(str_contains($html, htmlspecialchars($text)) && !str_contains($html, '<script>alert'), 'Form lost text or failed escaping');

    $id = $model->createReservation(2, 10, $date, '09:00', 60, [], $text);
    informationsCheck(is_int($id), 'Model failed to save information');
    $reservations = $model->getReservationsByUserId(2);
    $dogs = [];
    $showForm = false;
    $user = (new UserModel())->getUserById(2);
    $csrfToken = 'test-csrf';
    foreach (['reservation/index', 'user/profile', 'admin/reservation_form'] as $view) {
        if ($view === 'admin/reservation_form') {
            $reservations = [];
            $completedReservations = $model->getReservationsByUserId(2);
            $unavailabilities = [];
        }
        ob_start();
        include __DIR__ . '/../Views/' . $view . '.php';
        $html = ob_get_clean();
        informationsCheck(str_contains($html, nl2br(htmlspecialchars($text))) && !str_contains($html, '<script>alert'), 'Information missing or unsafe in ' . $view);
    }
    informationsCheck($model->updateStatus($id, 'terminé'), 'Status update failed');
    informationsCheck($model->getReservationById($id)->informations === $text, 'Status update lost information');
    $legacy = $db->query('SELECT * FROM reservation')->fetch();
    unset($legacy['informations']);
    informationsCheck(Reservation::fromArray($legacy)->informations === null, 'Legacy hydration broken');

    echo "OK: $checks reservation information checks (SQLite in memory, mocked emails).\n";
}
