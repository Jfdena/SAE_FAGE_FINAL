<?php
// Backend/config/Database.php

class Database {
    // Configuration - à adapter selon ton serveur
    private static $host = "localhost";
    private static $db_name = "FAGE";
    private static $username = "root";
    private static $password = "root";  // Laisse vide si pas de mot de passe

    private static $conn = null;

    public static function getConnection() {
        // Si pas encore connecté, on se connecte
        if (self::$conn === null) {
            try {
                // Créer la connexion PDO
                self::$conn = new PDO(
                    "mysql:host=" . self::$host . ";dbname=" . self::$db_name . ";charset=utf8",
                    self::$username,
                    self::$password
                );

                // Configurer PDO pour afficher les erreurs (utile en développement)
                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                echo "✅ Connexion BDD réussie<br>"; // À enlever après test

            } catch(PDOException $e) {
                // En cas d'erreur, afficher un message clair
                die("❌ Erreur de connexion à la base de données : " . $e->getMessage());
            }
        }

        return self::$conn;
    }
}
?>