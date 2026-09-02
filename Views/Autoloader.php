<?php

/**
 * Autoloader PSR-4 pour le namespace Project\
 * Project\Controllers\HomeController  → /Controllers/HomeController.php
 * Project\Models\UserModel            → /Models/UserModel.php
 * Project\Entities\Dog                → /Entities/Dog.php
 * Project\Core\Routeur                → /Core/Routeur.php
 */
spl_autoload_register(function (string $class): void {
    $prefix  = 'Project\\';
    $baseDir = __DIR__ . '/../';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
