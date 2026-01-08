<?php
// Backend/test_partenaire.php
require_once '../config/Database.php';

echo "<h1>🔍 Test Table Partenaire</h1>";

try {
    $db = new Database();
    $conn = $db->getConnection();

    echo "<p style='color:green'>✅ Connexion BDD OK</p>";

    // 1. Vérifier si la table existe
    echo "<h3>1. Vérification table 'partenaire'</h3>";
    try {
        $stmt = $conn->query("DESCRIBE partenaire");
        $columns = $stmt->fetchAll();

        if (empty($columns)) {
            echo "<p style='color:red'>❌ Table 'partenaire' vide ou inexistante</p>";

            // Tenter de la créer
            echo "<h4>Création de la table...</h4>";
            $sql = "CREATE TABLE IF NOT EXISTS partenaire (
                id_partenaire INT AUTO_INCREMENT PRIMARY KEY,
                nom VARCHAR(100) NOT NULL,
                type ENUM('donateur','subvention') NOT NULL,
                montant DECIMAL(10,2),
                date_contribution DATE,
                contact_email VARCHAR(100),
                contact_telephone VARCHAR(20),
                description TEXT,
                statut ENUM('actif','inactif') DEFAULT 'actif',
                date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";

            $conn->exec($sql);
            echo "<p style='color:green'>✅ Table 'partenaire' créée</p>";
        } else {
            echo "<p style='color:green'>✅ Table 'partenaire' existe avec " . count($columns) . " colonnes</p>";

            // Afficher la structure
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Default</th></tr>";
            foreach ($columns as $col) {
                echo "<tr>";
                echo "<td>" . $col['Field'] . "</td>";
                echo "<td>" . $col['Type'] . "</td>";
                echo "<td>" . $col['Null'] . "</td>";
                echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>❌ Erreur : " . $e->getMessage() . "</p>";
    }

    // 2. Tester une insertion
    echo "<h3>2. Test d'insertion</h3>";
    try {
        $sql = "INSERT INTO partenaire (nom, type, montant, statut) 
                VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['Test Entreprise', 'donateur', 1000.00, 'actif']);

        $test_id = $conn->lastInsertId();
        echo "<p style='color:green'>✅ Insertion test réussie (ID: $test_id)</p>";

        // Supprimer le test
        $conn->exec("DELETE FROM partenaire WHERE id_partenaire = $test_id");

    } catch (Exception $e) {
        echo "<p style='color:red'>❌ Erreur insertion : " . $e->getMessage() . "</p>";
    }

    // 3. Liens vers les pages
    echo "<h3>3. Pages disponibles</h3>";
    $pages = [
        'Ajouter' => '../views/admin/partenaires/addPartenaire.php',
        'Liste' => '../views/admin/partenaires/listePartenaires.php',
        'Historique' => '../views/admin/partenaires/HistoContribution.php'
    ];

    foreach ($pages as $nom => $fichier) {
        if (file_exists($fichier)) {
            echo "<p style='color:green'>✅ $nom : <a href='$fichier' target='_blank'>Tester</a></p>";
        } else {
            echo "<p style='color:red'>❌ $nom : Fichier introuvable</p>";
        }
    }

} catch (Exception $e) {
    echo "<p style='color:red'>❌ Erreur globale : " . $e->getMessage() . "</p>";
}
?>