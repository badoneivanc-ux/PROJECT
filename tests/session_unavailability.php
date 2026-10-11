<?php

declare(strict_types=1);

use Project\Controllers\AdminController;
use Project\Controllers\ReservationController;
use Project\Core\Database;
use Project\Core\ReservationSchedule;
use Project\Models\ReservationModel;

define('BASE_URL', '');
require_once __DIR__ . '/../Views/Autoloader.php';
require_once __DIR__ . '/../Core/dbConnect.php';

final class SessionTestRedirect extends RuntimeException {}

trait SessionTestResponse
{
    public array $viewData = [];
    public string $viewPath = '';

    protected function redirect(string $url): void
    {
        throw new SessionTestRedirect($url);
    }

    protected function render(string $path, array $data = []): void
    {
        $this->viewPath = $path;
        $this->viewData = $data + ['csrfToken' => $this->generateCsrfToken()];
    }
}

final class SessionTestAdmin extends AdminController
{
    use SessionTestResponse;
}

final class SessionTestClient extends ReservationController
{
    use SessionTestResponse;
}

$mysql = in_array('--mysql', $argv, true);
$sqliteClass = class_exists('Pdo\\Sqlite') ? 'Pdo\\Sqlite' : PDO::class;
$db = $mysql ? Database::getInstance() : new $sqliteClass('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$lockNames = [];
$lockAvailable = true;
$onLock = null;
if (!$mysql) {
    $registerFunction = method_exists($db, 'createFunction') ? 'createFunction' : 'sqliteCreateFunction';
    $db->$registerFunction('TIME_FORMAT', static fn(string $time, string $format): string => substr($time, 0, 5), 2);
    $db->$registerFunction('GET_LOCK', static function (string $name, int $timeout) use (&$lockNames, &$lockAvailable, &$onLock): int {
        $lockNames[] = $name;
        if ($onLock !== null) {
            $callback = $onLock;
            $onLock = null;
            $callback();
        }
        return $lockAvailable ? 1 : 0;
    }, 2);
    $db->$registerFunction('RELEASE_LOCK', static fn(string $name): int => 1, 1);
}
(new ReflectionProperty(Database::class, 'instance'))->setValue(null, $db);

// Les tables temporaires MySQL masquent les tables réelles sur cette connexion uniquement.
$create = $mysql ? 'CREATE TEMPORARY TABLE' : 'CREATE TABLE';
$db->exec("$create user_dog (
    id_utilisateur_PK INTEGER PRIMARY KEY, nom VARCHAR(100), prenom VARCHAR(100),
    email VARCHAR(150), id_role VARCHAR(20), date_inscription DATE,
    adresse VARCHAR(255), code_postal VARCHAR(5), ville VARCHAR(100)
)");
$db->exec("$create chien (
    id_chien_PK INTEGER PRIMARY KEY, nom VARCHAR(100), nom_race VARCHAR(100),
    poids DECIMAL(5,2), age DECIMAL(4,1), sexe INTEGER, id_user_FK INTEGER
)");
$db->exec("$create reservation (
    id_rdv INTEGER PRIMARY KEY " . ($mysql ? 'AUTO_INCREMENT' : 'AUTOINCREMENT') . ",
    date_rdv DATE, heure_rdv TIME, duree_minutes INTEGER, statut VARCHAR(20),
    id_utilisateur_PK INTEGER, id_chien_PK INTEGER
)");
if ($mysql) {
    $schema = file_get_contents(__DIR__ . '/../install.sql');
    if ($schema === false || preg_match('/CREATE TABLE `indisponibilite` \(.*?\) ENGINE[^;]*;/s', $schema, $matches) !== 1) {
        throw new RuntimeException('Définition de la table indisponibilite introuvable dans install.sql.');
    }
    $db->exec(str_replace('CREATE TABLE', 'CREATE TEMPORARY TABLE', $matches[0]));
} else {
    $db->exec("CREATE TABLE indisponibilite (
        id INTEGER PRIMARY KEY AUTOINCREMENT, date_seance DATE,
        seance TEXT CHECK (seance IN ('matin', 'apres_midi')), motif TEXT,
        UNIQUE(date_seance, seance)
    )");
}
$db->exec("INSERT INTO user_dog VALUES
    (1, 'Admin', 'Alice', 'admin@example.test', 'admin', '2026-01-01', '', '', ''),
    (2, 'Client', 'Jean', 'client@example.test', 'user', '2026-01-01', '', '', '')");
$db->exec("INSERT INTO chien VALUES (10, 'Rex', 'Caniche', 10, 3, 1, 2)");

$date = (new DateTimeImmutable('+3 days'))->format('Y-m-d');
while ((int) date('w', strtotime($date)) === 0) {
    $date = (new DateTimeImmutable($date))->modify('+1 day')->format('Y-m-d');
}
$checks = 0;
function sessionCheck(bool $condition, string $message): void
{
    global $checks;
    if (!$condition) {
        throw new RuntimeException($message);
    }
    $checks++;
}

function adminSession(): void
{
    $_SESSION = ['user_id' => 1, 'user_role' => 'admin', 'csrf_token' => 'test-csrf'];
    $_SERVER['REQUEST_METHOD'] = 'POST';
}

function sessionRedirect(callable $action, string $url = '/index.php?controller=admin&action=reservations'): void
{
    try {
        $action();
    } catch (SessionTestRedirect $response) {
        sessionCheck($response->getMessage() === $url, 'Unexpected redirect');
        return;
    }
    throw new RuntimeException('Expected redirect');
}

function blockedCount(): int
{
    global $db;
    return (int) $db->query('SELECT COUNT(*) FROM indisponibilite')->fetchColumn();
}

adminSession();
$_POST = ['csrf_token' => 'test-csrf', 'date_seance' => $date, 'seance' => 'matin', 'motif' => 'Congé <interne>'];
if (in_array('--invalid-csrf', $argv, true)) {
    $_POST['csrf_token'] = 'invalid';
    register_shutdown_function(static function (): void {
        sessionCheck(http_response_code() === 403, 'Invalid CSRF must return 403');
        sessionCheck(blockedCount() === 0, 'Invalid CSRF wrote data');
        echo ' CSRF_BLOCKED';
    });
    (new SessionTestAdmin())->blockSession();
    throw new RuntimeException('Invalid CSRF accepted');
}

sessionRedirect(static fn() => (new SessionTestAdmin())->blockSession());
sessionCheck(blockedCount() === 1 && $_SESSION['flash']['type'] === 'success', 'Admin block failed');
sessionCheck(!isset($_SESSION['block_form']), 'Successful form not cleared');
$model = new ReservationModel();
$morning = ReservationSchedule::SESSIONS['matin'];
$afternoon = ReservationSchedule::SESSIONS['apres_midi'];
foreach (ReservationSchedule::CRENEAUX as $slot) {
    $slots = ReservationSchedule::slotsForSession(ReservationSchedule::sessionForTime($slot));
    sessionCheck($model->isSessionBooked($date, $slots) === in_array($slot, $morning, true), 'Wrong session availability');
}
sessionCheck($model->isSessionBookedExcluding($date, $morning, 999), 'Exclusion ignored unavailability');
sessionCheck($model->createReservation(2, 10, $date, '09:00') === false, 'Booking without explicit slots bypassed block');
sessionCheck($model->countReservations() === 0 && $model->countByStatus('en attente') === 0, 'Block polluted stats');

$_SESSION['user_id'] = 2;
$_SESSION['user_role'] = 'user';
$_POST = ['csrf_token' => 'test-csrf', 'id_chien' => 10, 'date_rdv' => $date, 'heure_rdv' => '09:30'];
$client = new SessionTestClient();
$client->create();
sessionCheck($client->viewPath === 'reservation/index', 'Client failure did not redisplay form');
sessionCheck($_SESSION['flash']['type'] === 'error' && str_contains($_SESSION['flash']['message'], 'indisponible'), 'Client did not see availability error');
sessionCheck(!str_contains($_SESSION['flash']['message'], 'interne'), 'Internal reason leaked');
sessionCheck($model->countReservations() === 0, 'Client booked blocked session');

adminSession();
$_POST = ['csrf_token' => 'test-csrf', 'date_seance' => $date, 'seance' => 'matin'];
sessionRedirect(static fn() => (new SessionTestAdmin())->blockSession());
sessionCheck(blockedCount() === 1 && $_SESSION['flash']['type'] === 'error', 'Duplicate block accepted');
$reservationId = $model->createReservation(2, 10, $date, '14:00', 60, $afternoon);
sessionCheck(is_int($reservationId), 'Other half-day not bookable');
sessionCheck($model->updateStatus($reservationId, 'confirmé'), 'Normal confirmation failed');
$_POST['seance'] = 'apres_midi';
sessionRedirect(static fn() => (new SessionTestAdmin())->blockSession());
sessionCheck(blockedCount() === 1 && $_SESSION['flash']['type'] === 'error', 'Active reservation overwritten');
sessionCheck($model->updateStatus($reservationId, 'annulé'), 'Cancellation failed');
sessionRedirect(static fn() => (new SessionTestAdmin())->blockSession());
sessionCheck(blockedCount() === 2, 'Cancelled booking still prevented block');
sessionCheck(!$model->updateStatus($reservationId, 'en attente'), 'Reactivation bypassed block');
sessionCheck(!$model->updateStatus($reservationId, 'confirmé'), 'Confirmation bypassed block');
$_POST = ['csrf_token' => 'test-csrf', 'id' => $reservationId, 'statut' => 'confirmé'];
sessionRedirect(static fn() => (new SessionTestAdmin())->updateReservation(), '/index.php?controller=admin&action=reservations');
sessionCheck($_SESSION['flash']['type'] === 'error', 'Admin reactivation reported success');

$admin = new SessionTestAdmin();
$admin->dashboard();
sessionCheck($admin->viewData['totalReservations'] === 1 && $admin->viewData['pendingReservations'] === 0, 'Dashboard stats include blocks');
extract($admin->viewData);
ob_start();
include __DIR__ . '/../Views/admin/dashboard.php';
$html = ob_get_clean();
sessionCheck(!str_contains($html, 'action=blockSession') && !str_contains($html, 'action=unblockSession'), 'Dashboard still contains forms');
sessionCheck(str_contains($html, 'action=reservations&block=1#block-session'), 'Dashboard shortcut missing');
unset($_SESSION['block_form']);
$_GET = [];
$admin->reservations();
sessionCheck($admin->viewPath === 'admin/reservation_form' && count($admin->viewData['unavailabilities']) === 2, 'Reservation page missing blocks');
sessionCheck(!$admin->viewData['showBlockForm'], 'Form not collapsed by default');
extract($admin->viewData);
ob_start();
include __DIR__ . '/../Views/admin/reservation_form.php';
$html = ob_get_clean();
sessionCheck(str_contains($html, 'Congé &lt;interne&gt;') && !str_contains($html, 'Congé <interne>'), 'Reason not escaped');
sessionCheck(str_contains($html, 'action=blockSession') && str_contains($html, 'action=unblockSession'), 'Reservation page forms missing');
sessionCheck(substr_count($html, 'name="csrf_token"') === 3, 'Forms missing CSRF tokens');
sessionCheck(str_contains($html, '<details id="block-session" class="admin-session-block">'), 'Form not rendered collapsed');
$_GET['block'] = '1';
$admin->reservations();
sessionCheck($admin->viewData['showBlockForm'], 'Dashboard shortcut does not open form');
$_GET = [];
if (in_array('--preview', $argv, true)) {
    echo '<!doctype html><html lang="fr"><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<link rel="stylesheet" href="http://localhost:8888/atelierDuMuseau/public/style.css"><title>Test indisponibilités</title>';
    echo str_replace('src="/logo.jpg"', 'src="http://localhost:8888/atelierDuMuseau/public/logo.jpg"', $html);
    echo '</html>';
    exit;
}

$blocks = $model->getUpcomingUnavailabilities();
$_POST = ['csrf_token' => 'test-csrf', 'id' => $blocks[0]['id']];
sessionRedirect(static fn() => (new SessionTestAdmin())->unblockSession());
sessionCheck(blockedCount() === 1 && !$model->isSessionBooked($date, $morning), 'Unblock failed');
sessionCheck(is_int($model->createReservation(2, 10, $date, '08:30')), 'Unblocked half-day not bookable');
sessionRedirect(static fn() => (new SessionTestAdmin())->unblockSession());
sessionCheck($_SESSION['flash']['type'] === 'error', 'Repeated unblock silently succeeded');

foreach ([
    ['date_seance' => '2026-02-30'], ['date_seance' => 'not-a-date'],
    ['date_seance' => "2026-01-01\0"], ['date_seance' => date('Y-m-d', time() - 86400)],
    ['date_seance' => []], ['seance' => 'journee'], ['seance' => []],
    ['motif' => []], ['motif' => str_repeat('é', 256)], ['motif' => "\xff"],
] as $invalid) {
    $_POST = array_replace(['csrf_token' => 'test-csrf', 'date_seance' => $date, 'seance' => 'matin', 'motif' => ''], $invalid);
    sessionRedirect(static fn() => (new SessionTestAdmin())->blockSession());
    sessionCheck($_SESSION['flash']['type'] === 'error' && blockedCount() === 1, 'Invalid input accepted');
    $admin->dashboard();
    sessionCheck(isset($_SESSION['block_form']), 'Dashboard consumed failed form');
    $admin->reservations();
    sessionCheck($admin->viewData['showBlockForm'], 'Failed form not reopened');
    sessionCheck(!isset($_SESSION['block_form']), 'Failed form not consumed on reservation page');
    if (is_string($_POST['date_seance']) && is_string($_POST['seance']) && is_string($_POST['motif'])) {
        sessionCheck($admin->viewData['blockForm'] === [
            'date_seance' => $_POST['date_seance'], 'seance' => $_POST['seance'], 'motif' => trim($_POST['motif']),
        ], 'Failed form values not preserved');
    }
}
extract($admin->viewData);
ob_start();
include __DIR__ . '/../Views/admin/reservation_form.php';
$errorHtml = ob_get_clean();
sessionCheck(str_contains($errorHtml, '<details id="block-session" class="admin-session-block" open>'), 'Failed form not rendered open');
$admin->reservations();
sessionCheck(!$admin->viewData['showBlockForm'], 'Form error state persisted after being consumed');
$_POST = ['csrf_token' => 'test-csrf', 'date_seance' => date('Y-m-d'), 'seance' => 'matin', 'motif' => str_repeat('é', 255)];
sessionRedirect(static fn() => (new SessionTestAdmin())->blockSession());
sessionCheck($_SESSION['flash']['type'] === 'success', 'Today or exactly 255 characters rejected');

foreach (['blockSession', 'unblockSession'] as $action) {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $before = blockedCount();
    sessionRedirect(static fn() => (new SessionTestAdmin())->$action());
    sessionCheck(blockedCount() === $before && $_SESSION['flash']['type'] === 'error', 'GET mutated blocks');
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SESSION['user_role'] = 'user';
    sessionRedirect(static fn() => (new SessionTestAdmin())->$action(), '/');
    sessionCheck(blockedCount() === $before, 'Non-admin mutated blocks');
    unset($_SESSION['user_id']);
    sessionRedirect(static fn() => (new SessionTestAdmin())->$action(), '/index.php?controller=user&action=login');
    sessionCheck(blockedCount() === $before, 'Guest mutated blocks');
    adminSession();
}

$past = (new DateTimeImmutable('-2 days'))->format('Y-m-d');
$stmt = $db->prepare("INSERT INTO indisponibilite (date_seance, seance, motif) VALUES (?, 'matin', 'past')");
$stmt->execute([$past]);
sessionCheck(count($model->getUpcomingUnavailabilities()) === 2, 'Past blocks displayed');

if (!$mysql) {
    $raceDate = (new DateTimeImmutable($date))->modify('+1 day')->format('Y-m-d');
    $onLock = static function () use ($db, $raceDate): void {
        $stmt = $db->prepare("INSERT INTO indisponibilite (date_seance, seance, motif) VALUES (?, 'matin', 'concurrent')");
        $stmt->execute([$raceDate]);
    };
    sessionCheck(!$model->isSessionBooked($raceDate, $morning), 'Race fixture occupied');
    sessionCheck($model->createReservation(2, 10, $raceDate, '09:00') === false, 'Booking did not recheck under lock');
    $reactivationId = $model->createReservation(2, 10, $raceDate, '13:30');
    sessionCheck(is_int($reactivationId) && $model->updateStatus($reactivationId, 'annulé'), 'Race reactivation fixture failed');
    $onLock = static function () use ($db, $raceDate): void {
        $stmt = $db->prepare("INSERT INTO indisponibilite (date_seance, seance, motif) VALUES (?, 'apres_midi', 'concurrent')");
        $stmt->execute([$raceDate]);
    };
    sessionCheck(!$model->updateStatus($reactivationId, 'confirmé'), 'Reactivation did not recheck under lock');
    sessionCheck(in_array('reservation_' . $raceDate . '_08:30', $lockNames, true), 'Booking and block used different locks');
    $lockAvailable = false;
    sessionCheck(!$model->createUnavailability('2099-01-01', 'matin', ''), 'Failed lock accepted');
} else {
    $lockName = 'reservation_' . $date . '_08:30';
    $stmt = $db->prepare('SELECT IS_USED_LOCK(?)');
    $stmt->execute([$lockName]);
    sessionCheck($stmt->fetchColumn() === null, 'Session lock leaked');
    $otherConnection = new PDO(
        sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', DB_HOST, DB_PORT, DB_NAME, DB_CHARSET),
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $concurrentLock = 'reservation_2099-01-01_08:30';
    $stmt = $otherConnection->prepare('SELECT GET_LOCK(?, 0)');
    $stmt->execute([$concurrentLock]);
    sessionCheck((int) $stmt->fetchColumn() === 1, 'Could not acquire concurrent lock');
    try {
        $before = blockedCount();
        sessionCheck(!$model->createUnavailability('2099-01-01', 'matin', ''), 'Concurrent lock bypassed');
        sessionCheck(blockedCount() === $before, 'Concurrent lock failure wrote data');
    } finally {
        $stmt = $otherConnection->prepare('SELECT RELEASE_LOCK(?)');
        $stmt->execute([$concurrentLock]);
    }
    sessionCheck($model->createUnavailability('2099-01-01', 'matin', ''), 'Released session not usable');
}

echo "OK: $checks session unavailability checks (" . ($mysql ? 'MySQL temporary tables' : 'SQLite in memory') . ").\n";
