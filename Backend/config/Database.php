<?php
// Backend/config/Database.php

require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

class Database
{
    public $conn;

    /**
     * @return mysqli
     * @throws Exception
     */
    public function getConnection(): mysqli
    {
        $this->conn = null;
        try {
            // Utilisation de mysqli (connexion standard)
            $this->conn = new mysqli(
                $_ENV['DB_HOST'],
                $_ENV['DB_USER'],
                $_ENV['DB_PASS'],
                $_ENV['DB_NAME']
            );

            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }

            $this->conn->set_charset("utf8mb4");

        } catch (Exception $exception) {
            error_log("Erreur de connexion BDD (mysqli) : " . $exception->getMessage());
            throw new Exception("Erreur technique : Impossible de se connecter à la base de données");
        }
        return $this->conn;
    }

    public function getDbName(): string
    {
        return $_ENV['DB_NAME'] ?? '';
    }

    /**
     * Méthode pour exécuter des requêtes préparées avec mysqli
     */
    public function executeQuery($sql, $params = [])
    {
        try {
            $stmt = $this->conn->prepare($sql);
            if ($params) {
                $types = str_repeat('s', count($params)); // On considère tout comme string par défaut
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            return $stmt;
        } catch (Exception $e) {
            error_log("Erreur requête SQL : " . $e->getMessage());
            throw new Exception("Erreur lors de l'exécution de la requête");
        }
    }
}