<?php

namespace Project\Core;

use Project\Controllers\HomeController;
use Project\Controllers\UserController;
use Project\Controllers\DogController;
use Project\Controllers\ReservationController;
use Project\Controllers\AdminController;

class Routeur
{
    /**
     * Liste blanche des controllers autorisés.
     * Clé  = valeur du paramètre GET "controller"
     * Valeur = nom complet de la classe
     *
     * ⚠️  Ne jamais construire un nom de classe dynamiquement depuis $_GET
     *     sans liste blanche : cela permettrait l'instanciation de classes arbitraires.
     */
    private array $routes = [
        'home'        => HomeController::class,
        'user'        => UserController::class,
        'dog'         => DogController::class,
        'reservation' => ReservationController::class,
        'admin'       => AdminController::class,
    ];

    /**
     * Lit les paramètres GET, instancie le bon controller et appelle l'action.
     *
     * URL examples :
     *   index.php                                        → HomeController::index()
     *   index.php?controller=user&action=login           → UserController::login()
     *   index.php?controller=dog&action=show&id=3        → DogController::show()
     *   index.php?controller=admin&action=reservations   → AdminController::reservations()
     */
    public function dispatch(): void
    {
        $controllerKey = strtolower(trim($_GET['controller'] ?? 'home'));
        $action        = trim($_GET['action'] ?? 'index');

        // Vérification : controller dans la liste blanche
        if (!array_key_exists($controllerKey, $this->routes)) {
            $this->notFound();
            return;
        }

        $controllerClass = $this->routes[$controllerKey];
        $controller      = new $controllerClass();

        // Vérification : action = méthode publique déclarée dans le controller concret
        // (empêche d'appeler render(), redirect(), verifyCsrfToken(), etc.)
        if (!$this->isActionAllowed($controllerClass, $action)) {
            $this->notFound();
            return;
        }

        $controller->$action();
    }

    /**
     * Vérifie que l'action est une méthode publique propre au controller concret
     * (non héritée de la classe abstraite Controller).
     */
    private function isActionAllowed(string $class, string $action): bool
    {
        if (!method_exists($class, $action)) {
            return false;
        }

        $reflection = new \ReflectionMethod($class, $action);

        return $reflection->isPublic()
            && $reflection->getDeclaringClass()->getName() === $class;
    }

    /**
     * Réponse 404 générique.
     */
    private function notFound(): void
    {
        http_response_code(404);
        echo '<h1>404 — Page introuvable</h1>';
        echo '<p><a href="' . BASE_URL . '/index.php">Retour à l\'accueil</a></p>';
    }
}