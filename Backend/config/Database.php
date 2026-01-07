<?php
// Backend/config/Database.php
class Database {
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" .  $_ENV['DB_HOST'] . ";dbname=" . $_ENV['DB_NAME'] . ";charset=utf8mb4", $_ENV['DB_USER'], $_ENV['DB_PASS']);
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