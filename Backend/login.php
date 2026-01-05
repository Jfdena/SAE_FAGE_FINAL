<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
$docRoot = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2);
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/login_request.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeload();

$dbHost = $_ENV['DB_HOST'];
$dbName = $_ENV['DB_NAME'];
$dbUser = $_ENV['DB_USER'];
$dbPass = $_ENV['DB_PASS'];

$db = Database::getInstance();
$mysqli = $db->getConnection();

$loginError = "";
if (isset($_POST["connectButton"])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $result = connectToDatabase($mysqli, $username, $password);
    if ($result['success']) {
        $role = $result['role'];
        $ref_user = $result['ref_user'];
        if ($role == "admin" || $role == "user") {
            $_SESSION['role'] = "admin";
            $_SESSION['loggedIn'] = true;
            header("Location: dashboard.php");
        }
        else {
            $loginError = "This account is not a human user.";
        }
    } else {
        $loginError = $result['error'];
    }

}
?>


<!-- HTML Login Form -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>AB Legacy - Connexion</title>
</head>
<body>
<div class="container mt-5">
    <h3 class="text-center">FAGE</h3>
    <div class="row justify-content-center">
        <div class="col-6">
            <form method="post">
                <h4>Connectez-vous :</h4>
                <input class="form-control mb-2" type="text" name="username" placeholder="Nom d'utilisateur" required>
                <input class="form-control mb-2" type="password" name="password" placeholder="Mot de passe" required>
                <button class="btn btn-primary" type="submit" name="connectButton">Se connecter</button>
            </form>
            <?php if (!empty($loginError)): ?>
                <div class="alert alert-danger mt-2"><?php echo htmlspecialchars($loginError); ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>