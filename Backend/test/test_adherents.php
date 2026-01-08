<?php
// Backend/test_adherents.php
require_once '../config/Database.php';

echo "<h1>🧪 TEST COMPLET MODULE ADHÉRENTS</h1>";

try {
    $db = new Database();
    $conn = $db->getConnection();

    echo "<h2 style='color:green'>✅ Connexion BDD OK</h2>";

    // 1. Vérifier la table benevole
    echo "<h3>1. Vérification table 'benevole'</h3>";
    try {
        $stmt = $conn->query("DESCRIBE benevole");
        $columns = $stmt->fetchAll();

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
    } catch (Exception $e) {
        echo "<p style='color:red'>❌ Table 'benevole' non trouvée</p>";
    }

    // 2. Vérifier la table cotisation
    echo "<h3>2. Vérification table 'cotisation'</h3>";
    try {
        $stmt = $conn->query("DESCRIBE cotisation");
        $columns = $stmt->fetchAll();

        if (empty($columns)) {
            echo "<p style='color:orange'>⚠️ Table 'cotisation' vide ou inexistante</p>";
        } else {
            echo "<p style='color:green'>✅ Table 'cotisation' OK</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:orange'>⚠️ Table 'cotisation' non trouvée : " . $e->getMessage() . "</p>";
    }

    // 3. Insérer un bénévole de test
    echo "<h3>3. Insertion d'un bénévole test</h3>";
    try {
        // Vérifier si déjà existant
        $stmt = $conn->query("SELECT COUNT(*) FROM benevole WHERE email = 'test@example.com'");
        $count = $stmt->fetchColumn();

        if ($count == 0) {
            $sql = "INSERT INTO benevole (nom, prenom, email, telephone, date_naissance, date_inscription, statut) 
                    VALUES (?, ?, ?, ?, ?, CURDATE(), ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['Dupont', 'Jean', 'test@example.com', '0612345678', '1990-05-15', 'actif']);

            $test_id = $conn->lastInsertId();
            echo "<p style='color:green'>✅ Bénévole test inséré (ID: $test_id)</p>";
        } else {
            echo "<p style='color:blue'>ℹ️ Bénévole test déjà existant</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>❌ Erreur insertion : " . $e->getMessage() . "</p>";
    }

    // 4. Lister tous les bénévoles
    echo "<h3>4. Liste des bénévoles en base</h3>";
    $stmt = $conn->query("SELECT * FROM benevole ORDER BY id_benevole DESC");
    $benevoles = $stmt->fetchAll();

    if (empty($benevoles)) {
        echo "<p style='color:orange'>⚠️ Aucun bénévole dans la base</p>";
    } else {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Nom</th><th>Prénom</th><th>Email</th><th>Téléphone</th><th>Statut</th></tr>";
        foreach ($benevoles as $benevole) {
            echo "<tr>";
            echo "<td>" . $benevole['id_benevole'] . "</td>";
            echo "<td>" . htmlspecialchars($benevole['nom']) . "</td>";
            echo "<td>" . htmlspecialchars($benevole['prenom']) . "</td>";
            echo "<td>" . htmlspecialchars($benevole['email']) . "</td>";
            echo "<td>" . htmlspecialchars($benevole['telephone'] ?? '') . "</td>";
            echo "<td>" . $benevole['statut'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    // 5. Tester les chemins des fichiers
    echo "<h3>5. Vérification des fichiers PHP</h3>";
    $files = [
        'listeAdherent.php' => '../views/admin/adherents/listeAdherent.php',
        'addFiche.php' => '../views/admin/adherents/addFiche.php',
        'voirDetails.php' => '../views/admin/adherents/voirDetails.php',
        'editFiche.php' => '../views/admin/adherents/editFiche.php',
        'CotisationsAdherent.php' => '../views/admin/adherents/CotisationsAdherent.php'
    ];

    foreach ($files as $name => $path) {
        if (file_exists($path)) {
            echo "<p style='color:green'>✅ $name trouvé</p>";
        } else {
            echo "<p style='color:red'>❌ $name introuvable à : $path</p>";
        }
    }

    // 6. Liens de test
    echo "<h3>6. Liens de test vers les pages</h3>";
    echo "<ul>";
    echo "<li><a href='../views/admin/adherents/listeAdherent.php' target='_blank'>📋 Liste des bénévoles</a></li>";
    echo "<li><a href='../views/admin/adherents/addFiche.php' target='_blank'>➕ Ajouter un bénévole</a></li>";

    // Lien vers détail du bénévole test si existe
    if (!empty($benevoles)) {
        $first_id = $benevoles[0]['id_benevole'];
        echo "<li><a href='../views/admin/adherents/voirDetails.php?id=$first_id' target='_blank'>👁️ Voir détail bénévole (ID: $first_id)</a></li>";
        echo "<li><a href='../views/admin/adherents/editFiche.php?id=$first_id' target='_blank'>✏️ Modifier bénévole (ID: $first_id)</a></li>";
    }

    echo "<li><a href='../views/admin/adherents/CotisationsAdherent.php' target='_blank'>💰 Gestion cotisations</a></li>";
    echo "</ul>";

} catch (Exception $e) {
    echo "<h2 style='color:red'>❌ ERREUR GLOBALE</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
