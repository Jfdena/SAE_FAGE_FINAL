<?php
// Backend/test_bdd.php
require_once '../config/Database.php';

try {
    $db = Database::getConnection();
    echo "🎉 Félicitations ! La connexion MySQL fonctionne !";
} catch(Exception $e) {
    echo "Problème : " . $e->getMessage();
}
?>