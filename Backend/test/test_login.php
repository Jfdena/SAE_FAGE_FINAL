<?php
// Backend/views/auth/login.php
session_start();
require_once '../config/Database.php';

echo "<h3>Debug Login</h3>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    echo "<p>Email saisi : $email</p>";

    $db = new Database();
    $conn = $db->getConnection();

    // Vérifier si la table existe
    try {
        $stmt = $conn->query("SELECT COUNT(*) FROM Membrebureau");
        $count = $stmt->fetchColumn();
        echo "<p>Nombre d'utilisateurs dans Membrebureau : $count</p>";
    } catch (Exception $e) {
        echo "<p style='color:red'>Table Membrebureau non trouvée : " . $e->getMessage() . "</p>";
    }

    // Rechercher l'utilisateur
    $stmt = $conn->prepare("SELECT * FROM Membrebureau WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        echo "<p>Utilisateur trouvé : " . $user['prenom'] . " " . $user['nom'] . "</p>";
        echo "<p>Mot de passe hashé : " . substr($user['password'], 0, 20) . "...</p>";

        if (password_verify($password, $user['password'])) {
            echo "<p style='color:green'>✅ Mot de passe correct !</p>";
            // ... redirection
        } else {
            echo "<p style='color:red'>❌ Mot de passe incorrect</p>";
        }
    } else {
        echo "<p style='color:red'>❌ Utilisateur non trouvé</p>";
    }
}
?>

<form method="POST">
    <input type="email" name="email" placeholder="admin@fage.fr">
    <input type="password" name="password" placeholder="admin123">
    <button type="submit">Login Debug</button>
</form>