<?php
# ⭐ PRIORITAIRE - Login bureau uniquement

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Backend/controllers/AuthController.php
session_start();
require_once '../config/Database.php';
require_once '../models/MembreBureau.php';

class AuthController
{
    private $db;
    private $bureau;

    public function __construct($database)
    {
        $this->db = $database->getConnection();
        $this->bureau = new MembreBureau($database);
    }

    public function login($email, $password)
    {
        session_start();

        if ($this->bureau->authenticate($email, $password)) {
            $_SESSION['user_id'] = $this->bureau->id_benevole;
            $_SESSION['user_name'] = $this->bureau->prenom . ' ' . $this->bureau->nom;
            $_SESSION['role'] = 'bureau'; // Simplifié pour l'instant

            // Générer un token de session
            $_SESSION['session_token'] = bin2hex(random_bytes(32));

            // Enregistrer la connexion
            $_SESSION['login_time'] = time();

            return [
                'success' => true,
                'message' => 'Connexion réussie',
                'redirect' => 'views/admin/dashboard.php'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Identifiants incorrects ou accès non autorisé'
            ];
        }
    }

    public function logout()
    {
        session_start();

        // Détruire toutes les données de session
        $_SESSION = array();

        // Supprimer le cookie de session
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Détruire la session
        session_destroy();

        return [
            'success' => true,
            'message' => 'Déconnexion réussie',
            'redirect' => '../../HTML/Accueil.html'
        ];
    }

    public function checkSession()
    {
        session_start();

        if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_token'])) {
            return false;
        }

        // Vérifier si la session a expiré (30 minutes)
        if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 1800) {
            $this->logout();
            return false;
        }

        return true;
    }
}

// ⚡ CODE D'EXÉCUTION - Placé APRÈS la classe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    require_once '../config/Database.php';
    require_once '../models/MembreBureau.php';

    $database = new Database();
    $authController = new AuthController($database);

    if ($_GET['action'] === 'login') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $result = $authController->login($email, $password);

        if ($result['success']) {
            header('Location: ' . $result['redirect']);
            exit();
        } else {
            session_start();
            $_SESSION['error'] = $result['message'];
            header('Location: ../views/auth/login.php');
            exit();
        }
    }
}
?>