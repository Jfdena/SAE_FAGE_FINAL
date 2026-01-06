<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// test_db.php
require_once 'config/Database.php';
$db = new Database();
$conn = $db->getConnection();
echo $conn ? "✅ Connexion BDD OK" : "❌ Erreur BDD";
?>