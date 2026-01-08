<?php
// Backend/test/test_database.php

require_once __DIR__ . '/../config/Database.php';

try {
    echo "<h1>Test de connexion à la base de données</h1>";

    // 1. Instanciation de la classe Database
    $database = new Database();

    // 2. Tentative de connexion
    $db = $database->getConnection();

    if ($db) {
        echo "<p style='color: green;'>✅ Connexion réussie à la base de données !</p>";

        // 3. Petit test de requête pour vérifier que tout fonctionne
        $sql = "SELECT VERSION() as version";
        $stmt = $db->query($sql);
        $row = $stmt->fetch();

        echo "<p>Version du serveur MySQL : <strong>" . $row['version'] . "</strong></p>";

        // Optionnel : lister les tables pour confirmer l'accès
        echo "<h3>Liste des tables disponibles :</h3>";
        $stmt = $db->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (count($tables) > 0) {
            echo "<ul>";
            foreach ($tables as $table) {
                echo "<li>$table</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>Aucune table trouvée dans la base de données '" . $database->getDbName() . "'.</p>";
        }

    }

} catch (Exception $e) {
    // En cas d'erreur, on l'affiche proprement pour le debug
    echo "<p style='color: red;'>❌ Erreur : " . $e->getMessage() . "</p>";
    echo "<p>Vérifiez vos identifiants dans <code>Backend/config/Database.php</code>.</p>";
}