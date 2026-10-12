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
            self::$messages[] = ['to' => $to, 'subject' => $subject, 'body' => $htmlBody];
            return true;
        }

        public static function getLastError(): string
        {
            return '';
        }
    }
}

namespace Project\Controllers {
    const ADMIN_NOTIFICATION_EMAIL = 'admin@example.test';
}

namespace {
    use Project\Controllers\AdminController;
    use Project\Controllers\ReservationController;
    use Project\Core\Database;
    use Project\Core\Mailer;
    use Project\Core\Prestations;
    use Project\Models\ReservationModel;
    use Project\Models\UserModel;

    define('BASE_URL', '');
    require_once __DIR__ . '/../Views/Autoloader.php';
    require_once __DIR__ . '/../Core/dbConnect.php';

    final class PrestationsTestRedirect extends RuntimeException {}

    final class PrestationsTestController extends ReservationController
    {
        public array $viewData = [];

        protected function redirect(string $url): void
        {
            throw new PrestationsTestRedirect($url);
        }

        protected function render(string $path, array $data = []): void
        {
            $this->viewData = $data + ['csrfToken' => $this->generateCsrfToken()];
        }
    }

    final class PrestationsTestAdmin extends AdminController
    {
        protected function redirect(string $url): void
        {
            throw new PrestationsTestRedirect($url);
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
        email TEXT, id_role TEXT, date_inscription TEXT, telephone TEXT DEFAULT '',
        adresse TEXT DEFAULT '', code_postal TEXT DEFAULT '', ville TEXT DEFAULT ''
    );
    CREATE TABLE chien (
        id_chien_PK INTEGER PRIMARY KEY, nom TEXT, nom_race TEXT,
        poids REAL, age INTEGER, sexe INTEGER, id_user_FK INTEGER
    );
    CREATE TABLE reservation (
        id_rdv INTEGER PRIMARY KEY AUTOINCREMENT, date_rdv TEXT, heure_rdv TEXT,
        statut TEXT, id_utilisateur_PK INTEGER,
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
        VALUES (1, 'Admin', 'Alice', 'admin@example.test', 'admin', '2026-01-01'),
               (2, 'Client', 'Jean', 'client@example.test', 'user', '2026-01-01');
    INSERT INTO chien VALUES
        (10, 'Rex', 'Caniche', 10, 3, 1, 2),
        (11, 'Milo', 'Bouledogue', 18.5, 2, 1, 2),
        (12, 'Titan', 'Berger', 25, 4, 1, 2)");

    $date = (new DateTimeImmutable('+3 days'))->format('Y-m-d');
    while ((int) date('w', strtotime($date)) === 0) {
        $date = (new DateTimeImmutable($date))->modify('+1 day')->format('Y-m-d');
    }
    $checks = 0;
    function prestationsCheck(bool $condition, string $message): void
    {
        global $checks;
        if (!$condition) {
            throw new RuntimeException($message);
        }
        $checks++;
    }

    function renderView(string $view, array $vars): string
    {
        extract($vars);
        ob_start();
        include __DIR__ . '/../Views/' . $view . '.php';
        return ob_get_clean();
    }

    // Seuils de gabarit : petit <= 10 kg, moyen > 10 et < 25 kg, grand >= 25 kg.
    foreach ([[0.5, 'petit'], [10, 'petit'], [10.01, 'moyen'], [24.99, 'moyen'], [25, 'grand'], [60, 'grand']] as [$poids, $attendu]) {
        prestationsCheck(Prestations::gabaritPourPoids($poids) === $attendu, "Wrong size for $poids kg");
    }
    prestationsCheck(array_keys(Prestations::principalesPour('grand')) === ['bain_brushing', 'tonte_complete', 'coupe_ciseaux', 'debourrage_mue'], 'Wrong services for large dogs');
    prestationsCheck(!isset(Prestations::principalesPour('petit')['debourrage_mue']), 'Quote-only service offered to small dogs');

    $model = new ReservationModel();
    $basePost = ['csrf_token' => 'test-csrf', 'id_chien' => 10, 'date_rdv' => $date, 'heure_rdv' => '09:00'];
    $resetClient = static function (): void {
        $_SESSION = ['user_id' => 2, 'user_role' => 'user', 'csrf_token' => 'test-csrf'];
        $_SERVER['REQUEST_METHOD'] = 'POST';
    };

    // Saisies refusées : aucune réservation, aucun email, formulaire réaffiché avec les choix.
    foreach ([
        'missing'          => [[], 'Merci de choisir une prestation.'],
        'empty'            => [['prestation' => ''], 'Merci de choisir une prestation.'],
        'unknown'          => [['prestation' => 'massage'], 'gabarit'],
        'array'            => [['prestation' => ['bain_brushing']], 'gabarit'],
        'wrong-size'       => [['id_chien' => 12, 'prestation' => 'epilation'], 'gabarit'],
        'quote-small'      => [['prestation' => 'debourrage_mue'], 'gabarit'],
        'bad-supplement'   => [['prestation' => 'bain_brushing', 'supplements' => ['massage']], 'gabarit'],
        'string-supplements' => [['prestation' => 'bain_brushing', 'supplements' => 'coupe_griffes'], 'gabarit'],
        'nested-supplement' => [['prestation' => 'bain_brushing', 'supplements' => [['coupe_griffes']]], 'gabarit'],
    ] as $cas => [$input, $message]) {
        $db->exec('DELETE FROM reservation');
        $resetClient();
        $_POST = array_replace($basePost, $input);
        $sent = Mailer::$sent;
        $controller = new PrestationsTestController();
        $controller->create();
        prestationsCheck($model->countReservations() === 0 && Mailer::$sent === $sent, "Invalid input accepted: $cas");
        prestationsCheck($_SESSION['flash']['type'] === 'error' && str_contains($_SESSION['flash']['message'], $message), "Wrong error for $cas");
        prestationsCheck((int) $db->query('SELECT COUNT(*) FROM reservation_prestation')->fetchColumn() === 0, "Orphan service saved: $cas");
        $html = renderView('reservation/index', $controller->viewData);
        prestationsCheck(str_contains($html, 'name="prestation"') && str_contains($html, 'name="supplements[]"'), "Service fields missing after error: $cas");
    }

    // Réaffichage après erreur : chien grand, prestations indisponibles masquées, choix conservés.
    $resetClient();
    $_POST = array_replace($basePost, ['id_chien' => 12, 'date_rdv' => '', 'prestation' => 'coupe_ciseaux', 'supplements' => ['coupe_griffes', 'demelage']]);
    $controller = new PrestationsTestController();
    $controller->create();
    $html = renderView('reservation/index', $controller->viewData);
    prestationsCheck(preg_match('/<label class="prestation-option" hidden>\s*<input type="radio" name="prestation" value="epilation"[^>]*disabled/', $html) === 1, 'Unavailable service not hidden/disabled');
    prestationsCheck(preg_match('/value="coupe_ciseaux"[^>]*checked/', $html) === 1, 'Selected service lost after error');
    prestationsCheck(preg_match('/value="coupe_griffes"[^>]*checked/', $html) === 1 && preg_match('/value="demelage"[^>]*checked/', $html) === 1, 'Selected supplements lost after error');
    prestationsCheck(preg_match('/value="soins_poil"[^>]*checked/', $html) === 0, 'Unselected supplement checked');
    prestationsCheck(str_contains($html, 'data-gabarit="grand"') && str_contains($html, 'data-gabarit="petit"') && str_contains($html, 'data-gabarit="moyen"'), 'Dog size not exposed to the form');
    prestationsCheck(str_contains($html, "Total estimé : <strong>135\u{00A0}€</strong>"), 'Server-side estimate wrong after error');
    prestationsCheck(str_contains($html, 'évalués sur place'), 'On-site evaluation notice missing');

    // Réservation valide : prix toujours issus du catalogue, doublons ignorés, ordre du catalogue.
    $db->exec('DELETE FROM reservation');
    $resetClient();
    $_POST = $basePost + [
        'prestation' => 'bain_brushing',
        'supplements' => ['demelage', 'coupe_griffes', 'coupe_griffes'],
        'prix' => '1',
        'informations' => 'Premier toilettage',
    ];
    $sent = Mailer::$sent;
    try {
        (new PrestationsTestController())->create();
        throw new RuntimeException('Expected successful reservation redirect');
    } catch (PrestationsTestRedirect $response) {
        prestationsCheck($response->getMessage() === '/index.php?controller=reservation&action=index', 'Wrong redirect');
    }
    $reservation = $model->getReservationsByUserId(2)[0];
    prestationsCheck($reservation->gabarit === 'petit' && $reservation->prixEstime === 70.0, 'Size or estimate not stored');
    prestationsCheck(array_map(fn($p) => [$p->code, $p->type, $p->prix, $p->evalueSurPlace], $reservation->prestations) === [
        ['bain_brushing', 'principale', 60.0, false],
        ['demelage', 'supplement', 20.0, true],
        ['coupe_griffes', 'supplement', 10.0, false],
    ], 'Stored service lines wrong');
    prestationsCheck($model->getReservationById($reservation->id)->prestations == $reservation->prestations, 'Single reservation lost services');

    $messages = array_slice(Mailer::$messages, $sent);
    prestationsCheck(count($messages) === 2 && $messages[0]['to'] === 'client@example.test' && $messages[1]['to'] === 'admin@example.test', 'Creation emails not sent');
    foreach ($messages as $message) {
        prestationsCheck(str_contains($message['body'], 'Prestations demandées (Petit chien) :'), 'Email recap title missing');
        prestationsCheck(str_contains($message['body'], "<li>Bain + brushing : 60\u{00A0}€</li>"), 'Email main service missing');
        prestationsCheck(str_contains($message['body'], "<li>Coupe des griffes : +10\u{00A0}€</li>"), 'Email supplement missing');
        prestationsCheck(str_contains($message['body'], "<li>Démêlage important : +20\u{00A0}€ / 30 min (évalué sur place)</li>"), 'Email per-slot supplement missing');
        prestationsCheck(str_contains($message['body'], "Total estimé : 70\u{00A0}€"), 'Email total missing');
        prestationsCheck(str_contains($message['body'], Prestations::MENTION_DEPLACEMENT) && str_contains($message['body'], 'évalués sur place'), 'Email notices missing');
        prestationsCheck(strpos($message['body'], 'Prestations demandées') < strpos($message['body'], 'Informations complémentaires'), 'Email blocks in wrong order');
    }

    // Prestation sur devis pour un grand chien : pas de total chiffré.
    $db->exec('DELETE FROM reservation');
    $resetClient();
    $_POST = array_replace($basePost, ['id_chien' => 12, 'prestation' => 'debourrage_mue', 'supplements' => ['soins_poil']]);
    $sent = Mailer::$sent;
    try {
        (new PrestationsTestController())->create();
    } catch (PrestationsTestRedirect) {
    }
    $devis = $model->getReservationsByUserId(2)[0];
    prestationsCheck($devis->gabarit === 'grand' && $devis->prixEstime === null && $devis->prestations[0]->prix === null, 'Quote reservation stored wrongly');
    prestationsCheck(str_contains(Mailer::$messages[$sent]['body'], 'Total estimé : sur devis') && str_contains(Mailer::$messages[$sent]['body'], 'Débourrage / Mue : Sur devis'), 'Quote email wrong');
    prestationsCheck(!str_contains(Mailer::$messages[$sent]['body'], 'évalués sur place'), 'On-site notice shown without per-slot supplement');

    // Email de confirmation envoyé par l'admin.
    $_SESSION = ['user_id' => 1, 'user_role' => 'admin', 'csrf_token' => 'test-csrf'];
    $_POST = ['csrf_token' => 'test-csrf', 'id' => $devis->id, 'statut' => 'confirmé'];
    $sent = Mailer::$sent;
    try {
        (new PrestationsTestAdmin())->updateReservation();
    } catch (PrestationsTestRedirect) {
    }
    prestationsCheck(Mailer::$sent === $sent + 1 && str_contains(Mailer::$messages[$sent]['body'], 'Prestations demandées (Grand chien) :'), 'Confirmation email lacks services');
    prestationsCheck(str_contains(Mailer::$messages[$sent]['body'], "Soins poil abîmé ou pelage sensibilisé : +15\u{00A0}€"), 'Confirmation email lacks supplement');

    // Réservation antérieure à la fonctionnalité : affichage « — », email sans rubrique.
    $db->exec("INSERT INTO reservation (date_rdv, heure_rdv, statut, id_utilisateur_PK, id_chien_PK)
               VALUES ('$date', '14:00', 'en attente', 2, 11)");
    $legacyId = (int) $db->lastInsertId();
    $legacy = $model->getReservationById($legacyId);
    prestationsCheck($legacy->prestations === [] && $legacy->gabarit === null && $legacy->prixEstime === null, 'Legacy reservation hydration broken');
    prestationsCheck(Prestations::recapitulatifHtml($legacy->prestations, null, null) === '', 'Legacy email shows services');

    // Affichage dans les tableaux client, profil et administration.
    $reservations = $model->getReservationsByUserId(2);
    $vars = [
        'reservations' => $reservations, 'dogs' => [], 'showForm' => false, 'csrfToken' => 'test-csrf',
        'user' => (new UserModel())->getUserById(2), 'completedReservations' => [], 'unavailabilities' => [],
    ];
    foreach (['reservation/index', 'user/profile', 'admin/reservation_form'] as $view) {
        $html = renderView($view, $vars);
        prestationsCheck(str_contains($html, '<th>Prestations</th>'), "Services column missing in $view");
        prestationsCheck(str_contains($html, 'Débourrage / Mue') && str_contains($html, 'Total estimé : <strong>sur devis</strong>'), "Services missing in $view");
        prestationsCheck(str_contains($html, 'Grand chien'), "Size missing in $view");
        prestationsCheck(preg_match('/<td class="reservation-prestations-cell">\s*—\s*<\/td>/', $html) === 1, "Legacy placeholder missing in $view");
    }

    // Transaction : une erreur sur les prestations annule la réservation.
    $db->exec('DELETE FROM reservation');
    $db->exec('ALTER TABLE reservation_prestation RENAME TO reservation_prestation_off');
    $lignes = Prestations::selectionner('petit', 'tonte_complete', []);
    prestationsCheck($model->createReservation(2, 10, $date, '09:00', [], null, 'petit', $lignes, 75.0) === false, 'Failed service insert reported success');
    prestationsCheck($model->countReservations() === 0, 'Reservation kept after failed service insert');
    $db->exec('ALTER TABLE reservation_prestation_off RENAME TO reservation_prestation');

    // Page Tarifs générée depuis le catalogue, à l'identique.
    $html = renderView('home/tarifs', []);
    prestationsCheck(substr_count($html, '<li') === 14, 'Wrong number of price lines');
    foreach (["<span>Bain + brushing</span><span>60\u{00A0}€</span>", "<span>Épilation</span><span>105\u{00A0}€</span>",
              "<span>Coupe ciseaux</span><span>125\u{00A0}€</span>", '<li class="tarifs-card__devis"><span>Débourrage / Mue</span><span>Sur devis</span></li>',
              "+20\u{00A0}€ / 30 min", ">10\u{00A0}€<", "+15\u{00A0}€"] as $attendu) {
        prestationsCheck(str_contains($html, $attendu), "Tarifs page missing $attendu");
    }

    echo "OK: $checks reservation service checks (SQLite in memory, mocked emails).\n";
}
