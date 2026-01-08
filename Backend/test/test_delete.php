<?php
// Backend/test_delete_partenaire.php
require_once '../config/Database.php';

echo "<h1>Test Suppression Partenaire</h1>";

try {
    $db = new Database();
    $conn = $db->getConnection();

    // 1. Vérifier structure
    echo "<h3>1. Structure table Partenaire</h3>";
    $stmt = $conn->query("DESCRIBE Partenaire");
    $cols = $stmt->fetchAll();

    foreach ($cols as $col) {
        echo $col['Field'] . " : " . $col['Type'] . "<br>";

        // Vérifier statut
        if ($col['Field'] == 'statut') {
            if (stripos($col['Type'], 'enum') === false) {
                echo "<p style='color:red'>❌ ERREUR: statut est " . $col['Type'] . " au lieu de ENUM</p>";
            } else {
                echo "<p style='color:green'>✅ statut est ENUM (correct)</p>";
            }
        }
    }

    // 2. Tester la requête UPDATE
    echo "<h3>2. Test requête UPDATE</h3>";

    // D'abord créer un partenaire test
    $conn->exec("INSERT INTO Partenaire (nom, type, statut) VALUES ('Test Delete', 'donateur', 'actif')");
    $test_id = $conn->lastInsertId();
    echo "<p>Partenaire test créé (ID: $test_id)</p>";

    // Tester UPDATE
    $sql = "UPDATE Partenaire SET statut = 'inactif' WHERE id_partenaire = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$test_id]);

    echo "<p style='color:green'>✅ UPDATE réussi</p>";

    // Vérifier
    $stmt = $conn->prepare("SELECT statut FROM Partenaire WHERE id_partenaire = ?");
    $stmt->execute([$test_id]);
    $result = $stmt->fetch();

    echo "<p>Nouveau statut: " . $result['statut'] . "</p>";

    // Nettoyer
    $conn->exec("DELETE FROM Partenaire WHERE id_partenaire = $test_id");

} catch (Exception $e) {
    echo "<p style='color:red'>❌ ERREUR: " . $e->getMessage() . "</p>";
    echo "<p>Détail: " . $e->getTraceAsString() . "</p>";
}
