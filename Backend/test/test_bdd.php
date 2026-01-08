<?php
// Backend/test/test_bdd.php
require_once '../config/Database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    echo "<h1 style='color:green'>🎉 Félicitations ! La connexion MySQL fonctionne !</h1>";

    // Lister les tables CORRECTEMENT
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "<h3>📋 Tables dans la base 'fage' :</h3>";

    if (empty($tables)) {
        echo "<p style='color:orange'>⚠️ Aucune table trouvée. Créez les tables avec le script SQL ci-dessous.</p>";
    } else {
        echo "<ul>";
        foreach ($tables as $tableName) {
            echo "<li>" . htmlspecialchars($tableName) . "</li>";
        }
        echo "</ul>";
    }

    echo "<p>✅ Connexion établie avec succès !</p>";

} catch(Exception $e) {
    echo "<h1 style='color:red'>❌ Problème de connexion</h1>";
    echo "<p><strong>Erreur :</strong> " . $e->getMessage() . "</p>";
}
?>