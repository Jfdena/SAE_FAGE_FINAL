<?php
// Backend/session_check.php

// 1. Démarrer la session si pas déjà faite
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_email'])) {
    // Si pas connecté, rediriger vers login
    header('Location: ../views/auth/login.php');


    exit();
}

// 3. Vérifier le rôle si nécessaire (optionnel)
// Décommenter si tu veux restreindre certaines pages aux admins seulement
/*
$allowed_roles = ['admin', 'responsable']; // Rôles autorisés
if (!in_array($_SESSION['user_role'], $allowed_roles)) {
    // Rediriger vers une page d'erreur ou le dashboard
    header('Location: views/admin/dashboard.php?error=unauthorized');
    exit();
}
*/

// 4. Vérifier l'expiration de session (optionnel sécurité avancée)
$session_timeout = 3600; // 1 heure en secondes
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_timeout)) {
    // Session expirée
    session_unset();
    session_destroy();
    header('Location: ../views/auth/login.php?error=session_expired');
    exit();
}

// 5. Mettre à jour le timestamp de dernière activité
$_SESSION['last_activity'] = time();

// 6. Fonctions utiles pour les pages qui incluent ce fichier
function isAdmin(): bool
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function getUserFullName(): string
{
    $prenom = $_SESSION['user_prenom'] ?? '';
    $nom = $_SESSION['user_nom'] ?? '';
    return trim("$prenom $nom");
}

function getUserRole() {
    return $_SESSION['user_role'] ?? 'invité';
}
