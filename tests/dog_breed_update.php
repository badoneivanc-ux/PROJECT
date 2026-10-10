<?php

require_once __DIR__ . '/../Entities/Dog.php';
require_once __DIR__ . '/../Entities/Breed.php';
require_once __DIR__ . '/../Models/DogModel.php';
require_once __DIR__ . '/../Controllers/Controller.php';
require_once __DIR__ . '/../Controllers/DogController.php';

use Project\Controllers\DogController;
use Project\Models\DogModel;

class TestRedirect extends RuntimeException {}

class TestDogController extends DogController
{
    public bool $formRendered = false;

    protected function redirect(string $url): void
    {
        if ($url !== '/index.php?controller=user&action=profile') {
            throw new RuntimeException('Unexpected redirect: ' . $url);
        }
        throw new TestRedirect();
    }

    protected function render(string $path, array $data = []): void
    {
        $this->formRendered = $path === 'user/dog_form';
    }
}

function check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$db = new PDO('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$db->exec('PRAGMA foreign_keys = ON');
$db->exec('CREATE TABLE dog_base (id_race_PK INTEGER PRIMARY KEY, nom_race TEXT, poids REAL)');
$db->exec("INSERT INTO dog_base VALUES (1, 'Bichon Maltais', 4), (2, 'Caniche', 8)");
$db->exec('CREATE TABLE chien (
    id_chien_PK INTEGER PRIMARY KEY, nom TEXT, nom_race TEXT, poids REAL,
    age REAL, sexe INTEGER, photo_chien TEXT, id_user_FK INTEGER,
    id_race_FK INTEGER REFERENCES dog_base(id_race_PK)
)');
$db->exec("INSERT INTO chien VALUES (1, 'Vix', 'Croisement', 14, 5, 0, 'photo.webp', 7, NULL)");

$model = (new ReflectionClass(DogModel::class))->newInstanceWithoutConstructor();
(new ReflectionProperty(DogModel::class, 'db'))->setValue($model, $db);
$controller = (new ReflectionClass(TestDogController::class))->newInstanceWithoutConstructor();
(new ReflectionProperty(DogController::class, 'dogModel'))->setValue($controller, $model);

define('BASE_URL', '');
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SESSION = ['user_id' => 7, 'csrf_token' => 'test-token'];
$_GET = ['id' => 1];
$_FILES = [];
$user = new class {
    public string $prenom = 'Test';
    public string $nom = 'Client';
    public string $email = 'test@example.invalid';
    public string $telephone = '0102030405';
    public string $dateInscription = '2026-01-01';
    public string $adresse = '';
    public string $codePostal = '';
    public string $ville = '';

    public function adresseComplete(): string
    {
        return '';
    }
};
$csrfToken = 'test-token';
$reservations = [];

foreach ([
    [1, 'Bichon Maltais'],
    [2, 'Caniche'],
    [2, 'Caniche'],
    [null, 'Croisement'],
] as [$breedId, $expectedName]) {
    $_POST = [
        'csrf_token' => $csrfToken, 'nom' => 'Vix',
        'id_race_FK' => $breedId ?? '', 'nom_race_custom' => 'Croisement',
        'poids' => 14, 'age' => 5, 'sexe' => 0,
    ];
    try {
        $controller->editDog();
        throw new RuntimeException('Successful update did not redirect.');
    } catch (TestRedirect $e) {
        check($_SESSION['flash']['type'] === 'success', 'Update failed.');
    }

    $dogs = $model->getDogsByUserId(7);
    $dog = $dogs[0];
    check($dog->idRaceFk === $breedId, 'Breed association was not saved.');
    check($dog->nomRace === $expectedName, 'Breed name was not saved.');
    check($dog->photoChien === 'photo.webp', 'Existing photo was lost.');
    check($dog->poids === 14.0 && $dog->age === 5.0 && $dog->sexe === 0, 'Dog details changed.');
    ob_start();
    include __DIR__ . '/../Views/user/profile.php';
    $html = ob_get_clean();
    check(str_contains($html, 'Fiche de ma race') === ($breedId !== null), 'Incorrect breed link visibility.');
    if ($breedId !== null) {
        check(str_contains($html, 'controller=dog&action=show&id=' . $breedId . '"'), 'Incorrect breed link target.');
    }
}

$_POST['id_race_FK'] = 999;
$controller->editDog();
check($controller->formRendered, 'Invalid breed did not redisplay the form.');
check($_SESSION['flash']['type'] === 'error', 'Invalid breed did not report an error.');
$dog = $model->getDogById(1);
check($dog->idRaceFk === null && $dog->nomRace === 'Croisement', 'Invalid breed changed the dog.');

echo "OK: breed changes persist, profile links follow the breed, invalid breeds are rejected.\n";
