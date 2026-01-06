<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

// Données de test SIMPLES
$valid_users = [
    'admin@fage.org' => ['password' => 'admin123', 'role' => 'bureau'],
    'test@fage.org' => ['password' => 'test123', 'role' => 'bureau']
];


// Afficher un formulaire si on arrive en GET (sinon ça fait une page blanche)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $error = $_SESSION['error'] ?? null;
    unset($_SESSION['error']);
    ?>
    <!doctype html>
    <html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>Test login</title>
    </head>
    <body>
    <h1>Test login</h1>
    <?php if ($error): ?>
        <p style="color:red;"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <form method="post" action="/Backend/test_login.php">
        <label>Email <input type="email" name="email" required></label><br>
        <label>Mot de passe <input type="password" name="password" required></label><br>
        <button type="submit">Se connecter</button>
    </form>

    <p>Comptes: admin@fage.org/admin123 — test@fage.org/test123</p>
    </body>
    </html>
    <?php
    exit();
}

// ... existing code ...
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (isset($valid_users[$email]) && $valid_users[$email]['password'] === $password) {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_name'] = 'Administrateur';
    $_SESSION['role'] = $valid_users[$email]['role'];
    $_SESSION['login_time'] = time();

    header('Location: /Backend/views/admin/dashboard.php');
    exit();
} else {
    $_SESSION['error'] = 'Identifiants incorrects';
    header('Location: /Backend/views/auth/login.php');
    exit();
}