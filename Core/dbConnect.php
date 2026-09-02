<?php

namespace Project\Core;

use PDO;
use PDOException;

require_once __DIR__ . '/config.php';

/**
 * Classe Database — Singleton PDO
 *
 * Utilisation dans un Model :
 *   $pdo = Database::getInstance();
 *   $stmt = $pdo->prepare('SELECT * FROM user_dog WHERE id_utilisateur_PK = :id');
 *   $stmt->execute([':id' => $id]);
 */
class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    /**
     * Retourne l'unique instance PDO (crée la connexion au premier appel).
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            // La connexion utilise le port depuis les variables d'environnement si présent.
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_PORT,
                DB_NAME,
                DB_CHARSET
            );

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    // Lève une PDOException sur chaque erreur SQL
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    // Tableaux associatifs par défaut (pas besoin de FETCH_ASSOC à chaque requête)
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    // Désactive les requêtes préparées émulées : types PHP corrects + sécurité renforcée
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    // Timeout de connexion en secondes
                    PDO::ATTR_TIMEOUT            => 5,
                ]);
            } catch (PDOException $e) {
                // On logue l'erreur réelle sans l'exposer à l'utilisateur
                error_log('[Database] Connexion échouée : ' . $e->getMessage());
                http_response_code(500);
                die('Erreur de connexion à la base de données. Veuillez réessayer plus tard.');
            }
        }

        return self::$instance;
    }
}