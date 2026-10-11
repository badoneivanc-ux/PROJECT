<?php

declare(strict_types=1);

use Project\Controllers\AdminController;
use Project\Controllers\DogController;
use Project\Core\Database;
use Project\Models\DogModel;
use Project\Models\UserModel;

define('BASE_URL', '');
require_once __DIR__ . '/../Views/Autoloader.php';
require_once __DIR__ . '/../Core/dbConnect.php';

final class TestRedirect extends RuntimeException {}

trait CaptureResponse
{
    public array $viewData = [];
    public string $viewPath = '';

    protected function redirect(string $url): void
    {
        throw new TestRedirect($url);
    }

    protected function render(string $path, array $data = []): void
    {
        $this->viewPath = $path;
        $this->viewData = $data + ['csrfToken' => $this->generateCsrfToken()];
    }
}

final class TestAdminController extends AdminController
{
    use CaptureResponse;
}

final class TestDogController extends DogController
{
    use CaptureResponse;
}

function resetFixture(): PDO
{
    $db = new PDO('sqlite::memory:', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $db->exec('PRAGMA foreign_keys = ON');
    $db->exec(
        "CREATE TABLE user_dog (
            id_utilisateur_PK INTEGER PRIMARY KEY, nom TEXT, prenom TEXT, email TEXT,
            id_role TEXT, date_inscription TEXT, telephone TEXT
        );
        CREATE TABLE chien (
            id_chien_PK INTEGER PRIMARY KEY, nom TEXT, nom_race TEXT, poids REAL,
            age REAL, sexe INTEGER,
            id_user_FK INTEGER REFERENCES user_dog(id_utilisateur_PK) ON DELETE CASCADE
        );
        CREATE TABLE reservation (
            id_rdv INTEGER PRIMARY KEY, statut TEXT,
            id_utilisateur_PK INTEGER REFERENCES user_dog(id_utilisateur_PK) ON DELETE CASCADE,
            id_chien_PK INTEGER REFERENCES chien(id_chien_PK) ON DELETE CASCADE
        );
        INSERT INTO user_dog VALUES
            (1, 'Admin', 'Alice', 'admin@example.test', 'admin', '2026-01-01', ''),
            (2, '<Client>', 'Jean', 'client@example.test', 'user', '2026-01-02', ''),
            (3, 'Client', 'Marie', 'marie@example.test', 'user', '2026-01-03', '');
        INSERT INTO chien VALUES
            (10, 'Rex <test>', 'Caniche', 10, 3, 1, 2),
            (11, 'Luna', 'Caniche', 8, 2, 0, 2),
            (12, 'Max', 'Caniche', 9, 4, 1, 3);
        INSERT INTO reservation VALUES
            (1, 'en attente', 2, 10), (2, 'confirmé', 2, 10),
            (3, 'annulé', 2, 11), (4, 'terminé', 2, 12),
            (5, 'terminé', 3, 10), (6, 'en attente', 3, 12);"
    );
    $property = new ReflectionProperty(Database::class, 'instance');
    $property->setValue(null, $db);
    $_SESSION = ['user_id' => 1, 'user_role' => 'admin', 'csrf_token' => 'test-csrf'];
    $_GET = [];
    $_POST = [];
    $_SERVER['REQUEST_METHOD'] = 'GET';
    return $db;
}

$checks = 0;
function check(bool $condition, string $message): void
{
    global $checks;
    if (!$condition) {
        throw new RuntimeException($message);
    }
    $checks++;
}

function redirected(callable $action, string $expected): void
{
    try {
        $action();
    } catch (TestRedirect $response) {
        check($response->getMessage() === $expected, 'Unexpected redirect');
        return;
    }
    throw new RuntimeException('Expected a redirect');
}

function confirmation(string $type, int $id): array
{
    $_GET = ['type' => $type, 'id' => $id];
    $controller = new TestAdminController();
    $controller->confirmDeletion();
    check($controller->viewPath === 'admin/delete_confirmation', 'Wrong confirmation view');
    return $controller->viewData;
}

function confirmedPost(array $data): void
{
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = [
        'id' => $data['id'], 'csrf_token' => 'test-csrf',
        'confirmation' => 'SUPPRIMER', 'deletion_token' => $data['deletionToken'],
    ];
}

function renderConfirmation(array $data): string
{
    extract($data);
    ob_start();
    include __DIR__ . '/../Views/admin/delete_confirmation.php';
    return ob_get_clean();
}

if (($argv[1] ?? '') === '--invalid-csrf') {
    $db = resetFixture();
    confirmedPost(confirmation('user', 2));
    $_POST['csrf_token'] = 'invalid';
    register_shutdown_function(static function () use ($db): void {
        check(http_response_code() === 403, 'Invalid CSRF did not return 403');
        check((int)$db->query('SELECT COUNT(*) FROM user_dog')->fetchColumn() === 3, 'Invalid CSRF deleted data');
        echo ' CSRF_BLOCKED';
    });
    (new TestAdminController())->deleteUser();
    throw new RuntimeException('Invalid CSRF was accepted');
}

$db = resetFixture();
$userData = confirmation('user', 2);
check($userData['impact'] === ['dogs' => 2, 'reservations' => 5], 'Incorrect user cascade count');
check((int)$db->query('SELECT COUNT(*) FROM user_dog')->fetchColumn() === 3, 'Confirmation GET deleted data');
$userHtml = renderConfirmation($userData);
check(str_contains($userHtml, 'Jean &lt;Client&gt;'), 'User identity is not escaped');
check(str_contains($userHtml, '2 chiens associés') && str_contains($userHtml, '5 réservations'), 'Missing impact counts');
check(str_contains($userHtml, 'required pattern="SUPPRIMER"'), 'Missing no-JavaScript validation');
check(str_contains($userHtml, 'class="btn btn-primary" autofocus>Annuler'), 'Cancel is not prominent');

$db = resetFixture();
$dogData = confirmation('dog', 10);
check($dogData['impact'] === ['dogs' => 1, 'reservations' => 3], 'Incorrect dog cascade count');
$dogHtml = renderConfirmation($dogData);
check(str_contains($dogHtml, 'Rex &lt;test&gt;') && str_contains($dogHtml, 'client@example.test'), 'Missing escaped dog and owner identity');
check(str_contains($dogHtml, 'ses autres chiens seront conservés'), 'Missing preservation warning');

foreach (['user', 'dog'] as $type) {
    $id = $type === 'user' ? 2 : 10;
    $returnUrl = '/index.php?controller=admin&action=' . ($type === 'user' ? 'users' : 'dogs');
    $action = static function () use ($type): void {
        if ($type === 'user') {
            (new TestAdminController())->deleteUser();
        } else {
            (new TestDogController())->delete();
        }
    };
    foreach (['get', 'missing-word', 'wrong-word', 'array-word', 'missing-token', 'wrong-token', 'array-token', 'wrong-target', 'wrong-type'] as $scenario) {
        $db = resetFixture();
        $data = confirmation($type, $id);
        confirmedPost($data);
        switch ($scenario) {
            case 'get': $_SERVER['REQUEST_METHOD'] = 'GET'; break;
            case 'missing-word': unset($_POST['confirmation']); break;
            case 'wrong-word': $_POST['confirmation'] = 'supprimer'; break;
            case 'array-word': $_POST['confirmation'] = ['SUPPRIMER']; break;
            case 'missing-token': unset($_POST['deletion_token']); break;
            case 'wrong-token': $_POST['deletion_token'] = 'wrong'; break;
            case 'array-token': $_POST['deletion_token'] = [$data['deletionToken']]; break;
            case 'wrong-target': $_POST['id'] = $type === 'user' ? 3 : 11; break;
            case 'wrong-type':
                unset($_SESSION['deletion_confirmation'][$type]);
                $_SESSION['deletion_confirmation'][$type === 'user' ? 'dog' : 'user'][$id] = $data['deletionToken'];
                break;
        }
        redirected($action, $returnUrl);
        check($_SESSION['flash']['type'] === 'error', "$type/$scenario did not report an error");
        check((int)$db->query('SELECT COUNT(*) FROM user_dog')->fetchColumn() === 3, "$type/$scenario deleted a user");
        check((int)$db->query('SELECT COUNT(*) FROM chien')->fetchColumn() === 3, "$type/$scenario deleted a dog");
        check((int)$db->query('SELECT COUNT(*) FROM reservation')->fetchColumn() === 6, "$type/$scenario deleted reservations");
    }

    $db = resetFixture();
    confirmedPost(confirmation($type, $id));
    redirected($action, $returnUrl);
    check($_SESSION['flash']['type'] === 'success', "$type valid confirmation failed");
    check(!isset($_SESSION['deletion_confirmation'][$type][$id]), 'Token was not consumed');
    check((int)$db->query('SELECT COUNT(*) FROM user_dog')->fetchColumn() === ($type === 'user' ? 2 : 3), 'Wrong user cascade');
    check((int)$db->query('SELECT COUNT(*) FROM chien')->fetchColumn() === ($type === 'user' ? 1 : 2), 'Wrong dog cascade');
    check((int)$db->query('SELECT COUNT(*) FROM reservation')->fetchColumn() === ($type === 'user' ? 1 : 3), 'Wrong reservation cascade');
    redirected($action, $returnUrl);
    check($_SESSION['flash']['type'] === 'error', 'Replayed confirmation accepted');
}

resetFixture();
$_GET = ['type' => 'user', 'id' => 1];
redirected(static fn() => (new TestAdminController())->confirmDeletion(), '/index.php?controller=admin&action=users');
check($_SESSION['flash']['type'] === 'error', 'Own account confirmation allowed');
confirmedPost(['id' => 1, 'deletionToken' => 'test-token']);
$_SESSION['deletion_confirmation']['user'][1] = 'test-token';
redirected(static fn() => (new TestAdminController())->deleteUser(), '/index.php?controller=admin&action=users');
check((new UserModel())->getUserById(1) !== null, 'Own account was deleted');

foreach (['user', 'dog'] as $type) {
    resetFixture();
    $url = '/index.php?controller=admin&action=' . ($type === 'user' ? 'users' : 'dogs');
    $_GET = ['type' => $type, 'id' => 999];
    redirected(static fn() => (new TestAdminController())->confirmDeletion(), $url);
    check($_SESSION['flash']['type'] === 'error', 'Missing target not reported');
}

resetFixture();
check((new UserModel())->getDeletionImpact(1) === ['dogs' => 0, 'reservations' => 0], 'Empty user counts incorrect');
check(!(new UserModel())->deleteUser(999) && !(new DogModel())->deleteDog(999), 'Missing deletion reported as success');
$_SESSION['user_role'] = 'user';
$_GET = ['type' => 'user', 'id' => 2];
redirected(static fn() => (new TestAdminController())->confirmDeletion(), '/');
redirected(static fn() => (new TestDogController())->delete(), '/');
unset($_SESSION['user_id']);
redirected(static fn() => (new TestAdminController())->confirmDeletion(), '/index.php?controller=user&action=login');

$process = proc_open([PHP_BINARY, __FILE__, '--invalid-csrf'], [
    1 => ['pipe', 'w'], 2 => ['pipe', 'w'],
], $pipes);
check(is_resource($process), 'Could not start CSRF check');
$output = stream_get_contents($pipes[1]);
$errors = stream_get_contents($pipes[2]);
fclose($pipes[1]);
fclose($pipes[2]);
check(proc_close($process) === 0 && $errors === '' && str_contains($output, 'CSRF_BLOCKED'), 'CSRF check failed: ' . $errors);

if (($argv[1] ?? '') === '--html') {
    echo '<!DOCTYPE html><html lang="fr"><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<link rel="stylesheet" href="file://' . dirname(__DIR__) . '/public/style.css"><body>';
    echo $userHtml, '</body></html>';
} else {
    echo "OK: $checks deletion confirmation checks (SQLite in memory).\n";
}
