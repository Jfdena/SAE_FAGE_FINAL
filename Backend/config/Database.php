<?php
// Backend/config/Database.php

class Database {
    private $host = "localhost";
    private $db_name = "fage";
    private $username = "root";
    private $password = "root";          // À adapter (ton mot de passe MySQL)
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch(PDOException $exception) {
            // Ne pas afficher l'erreur en production
            error_log("Erreur de connexion BDD : " . $exception->getMessage());
            throw new Exception("Erreur de connexion à la base de données");
        }
        return $this->conn;
    }

    // Méthode pour exécuter des requêtes préparées
    public function executeQuery($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            error_log("Erreur requête SQL : " . $e->getMessage());
            throw new Exception("Erreur lors de l'exécution de la requête");
        }
    }
}
?>