<?php
// Fichier d'entrée principal de l'application

// Activation du strict typing
declare(strict_types=1);


// Le cookie est inaccessible au JavaScript et est marqué Secure sous HTTPS.
$isHttps = !empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off';
session_set_cookie_params([
	'lifetime' => 0,
	'path'     => '/',
	'secure'   => $isHttps,
	'httponly' => true,
	'samesite' => 'Lax',
]);

// Démarrage de la session avant tout output
session_start();

// Autoloader PSR-4 (Project\ → racine du projet)
require_once __DIR__ . '/../Views/Autoloader.php';

// Connexion PDO (disponible via Project\Core\Database::getInstance())
require_once __DIR__ . '/../Core/dbConnect.php';

// Dispatch de la requête
$router = new Project\Core\Routeur();
$router->dispatch();
