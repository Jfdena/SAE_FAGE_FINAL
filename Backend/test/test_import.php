<?php
// Backend/test_import.php
require_once '../config/Database.php';

echo "<h1>🔍 Test Importation Tables FAGE</h1>";
echo "<p>Vérification de la structure de la base de données...</p>";
echo "<hr>";

try {
    $db = Database::getConnection();

    // 1. Liste toutes les tables
    echo "<h3>📋 Tables trouvées dans la base 'FAGE' :</h3>";
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($tables)) {
        echo "<div style='background: #ffcccc; padding: 10px; border-radius: 5px;'>
              ⚠️ Aucune table trouvée ! Tu dois importer ton SQL.</div>";
    } else {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li><strong>$table</strong>";

            // Compter le nombre de lignes
            $count = $db->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            echo " - $count ligne(s)";

            // Afficher les 2 premières lignes si pas vide
            if ($count > 0) {
                $sample = $db->query("SELECT * FROM $table LIMIT 2")->fetchAll(PDO::FETCH_ASSOC);
                echo "<br><small>Exemple : " . json_encode($sample) . "</small>";
            }

            echo "</li>";
        }
        echo "</ul>";
    }

    echo "<hr>";

    // 2. Test CRITIQUE : la table MembreBureau (pour le login)
    echo "<h3>🔐 Test Table MembreBureau (ESSENTIELLE pour le login) :</h3>";

    if (in_array('MembreBureau', $tables)) {
        $result = $db->query("DESCRIBE MembreBureau");
        $columns = $result->fetchAll(PDO::FETCH_ASSOC);

        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Colonne</th><th>Type</th><th>Null</th><th>Clé</th></tr>";
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td>" . $col['Field'] . "</td>";
            echo "<td>" . $col['Type'] . "</td>";
            echo "<td>" . $col['Null'] . "</td>";
            echo "<td>" . $col['Key'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";

        // Vérifier si au moins un admin existe
        $adminCount = $db->query("SELECT COUNT(*) FROM MembreBureau WHERE role = 'admin'")->fetchColumn();
        if ($adminCount == 0) {
            echo "<div style='background: #ff9900; padding: 10px; margin-top: 10px;'>
                  ⚠️ Attention : Aucun administrateur dans la table !
                  <form method='POST' style='margin-top: 10px;'>
                  <input type='submit' name='create_admin' value='Créer un admin test' class='btn'>
                  </form>
                  </div>";

            if (isset($_POST['create_admin'])) {
                $hash = password_hash('admin123', PASSWORD_DEFAULT);
                $db->exec("INSERT INTO MembreBureau (nom, prenom, email, password, role) 
                          VALUES ('Admin', 'Test', 'admin@fage.org', '$hash', 'admin')");
                echo "✅ Admin créé : admin@fage.org / admin123";
            }
        }
    } else {
        echo "<div style='background: #ff0000; color: white; padding: 10px;'>
              ❌ CRITIQUE : Table MembreBureau manquante !
              <br>Tu dois créer cette table pour le login.</div>";
    }

    echo "<hr>";

    // 3. Proposition de correction si problème
    echo "<h3>🔧 Actions possibles :</h3>";
    echo "<ol>";
    echo "<li><strong>Si tables manquantes</strong> : Exécute ton fichier SQL dans phpMyAdmin</li>";
    echo "<li><strong>Si MembreBureau manque</strong> : <button onclick='showSQL()'>Voir SQL à exécuter</button></li>";
    echo "<li><strong>Si connexion échoue</strong> : Vérifie config/Database.php</li>";
    echo "</ol>";

    echo "<div id='sql_code' style='display:none; background:#f0f0f0; padding:10px;'>";
    echo "<pre>";
    echo "CREATE TABLE MembreBureau (
    id_membre INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','responsable') NOT NULL
);";
    echo "</pre>";
    echo "</div>";

} catch(Exception $e) {
    echo "<div style='background: #ffcccc; padding: 15px; border-radius: 5px;'>
          <h3>❌ Erreur :</h3>
          <p>" . htmlspecialchars($e->getMessage()) . "</p>
          </div>";
}
?>

<script>
    function showSQL() {
        document.getElementById('sql_code').style.display = 'block';
    }
</script>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .btn { background: #4CAF50; color: white; padding: 5px 10px; border: none; cursor: pointer; }
</style>