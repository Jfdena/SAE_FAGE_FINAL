<?php
// Backend/session_check.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

require_once 'config/Database.php';
require_once 'config/Security.php';

// Vérifier si l'utilisateur est connecté
if (!Security::isLoggedIn()) {
    // Rediriger vers la page de login avec message
    $_SESSION['error'] = "Veuillez vous connecter pour accéder à l'espace membre.";
    header('Location: ../HTML/Accueil.html');
    exit();
}

// Vérifier le rôle (seuls les membres du bureau peuvent accéder)
if (!Security::hasPermission('bureau')) {
    $_SESSION['error'] = "Accès réservé aux membres du bureau.";
    header('Location: ../HTML/Accueil.html');
    exit();
}

// Si tout est OK, rediriger vers le dashboard
header('Location: views/admin/dashboard.php');
exit();
?>