<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Backend/config/Security.php
class Security {

    // Générer un token CSRF pour les formulaires
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    // Vérifier le token CSRF
    public static function verifyCSRFToken($token) {
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            return false;
        }
        return true;
    }

    // Échapper les données pour éviter XSS
    public static function sanitize($data) {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    // Vérifier si l'utilisateur est connecté
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && isset($_SESSION['role']);
    }

    // Vérifier les permissions
    public static function hasPermission($requiredRole) {
        if (!self::isLoggedIn()) {
            return false;
        }

        $userRole = $_SESSION['role'];

        // Hiérarchie des rôles
        $roles = [
            'admin' => 3,
            'bureau' => 2,
            'responsable' => 1,
            'membre' => 0
        ];

        return isset($roles[$userRole]) &&
            isset($roles[$requiredRole]) &&
            $roles[$userRole] >= $roles[$requiredRole];
    }

    // Rediriger vers la page de login si non connecté
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: ../Backend/views/auth/login.php');
            exit();
        }
    }
}
?>