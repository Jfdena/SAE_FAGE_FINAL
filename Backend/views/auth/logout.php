<?php
// Backend/views/auth/logout.php

// 1. Démarrer la session
session_start();

// 2. Enregistrer un log de déconnexion (optionnel, mais utile)
if (isset($_SESSION['user_email'])) {
    $log_message = "Déconnexion de " . $_SESSION['user_email'] . " - " . date('d/m/Y H:i:s');
    // Tu pourrais écrire dans un fichier log plus tard
}

// 3. Détruire complètement la session
session_unset();    // Supprime toutes les variables de session
session_destroy();  // Détruit la session

// 4. Supprimer le cookie de session côté navigateur
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 5. Rediriger vers la page de login avec un message
header('Location: login.php?message=deconnected');
exit();
