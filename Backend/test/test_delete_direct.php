<?php
// Backend/test_delete_direct.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/Database.php';

$db = new Database();
$conn = $db->getConnection();

// Créer un partenaire test
$conn->exec("INSERT INTO Partenaire (nom, type, statut) VALUES ('Test DELETE', 'donateur', 'actif')");
$test_id = $conn->lastInsertId();

echo "<h1>Test DELETE direct</h1>";
echo "<p>Partenaire test ID: $test_id</p>";

// Test UPDATE (désactivation)
try {
    $sql = "UPDATE Partenaire SET statut = 'inactif' WHERE id_partenaire = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$test_id]);

    echo "<p style='color:green'>✅ UPDATE réussi</p>";

    // Vérifier
    $stmt = $conn->prepare("SELECT * FROM Partenaire WHERE id_partenaire = ?");
    $stmt->execute([$test_id]);
    $result = $stmt->fetch();

    echo "<p>Statut après UPDATE: " . $result['statut'] . "</p>";

} catch (Exception $e) {
    echo "<p style='color:red'>❌ ERREUR UPDATE: " . $e->getMessage() . "</p>";
}

// Nettoyer
$conn->exec("DELETE FROM Partenaire WHERE id_partenaire = $test_id");
echo "<p>✅ Test nettoyé</p>";