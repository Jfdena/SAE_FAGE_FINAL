<?php

// Backend/test_direct.php
require_once '../config/Database.php';

$db = new Database();
$conn = $db->getConnection();

try {
    $sql = "INSERT INTO Partenaire (nom, type, montant, statut) 
            VALUES ('Test Direct', 'donateur', 1000, 'actif')";

    $conn->exec($sql);
    echo "✅ SUCCÈS ! ID: " . $conn->lastInsertId();

} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage();
}
