<?php
// Backend/create_admin.php
require_once '../config/Database.php';

echo "<h1>👑 Création d'un compte administrateur</h1>";
echo "<p>Ce script va créer un compte admin pour tester le login.</p>";
echo "<hr>";

try {
    $db = Database::getConnection();

    // Vérifier d'abord si la table existe (en minuscules)
    $tables = $db->query("SHOW TABLES LIKE 'membrebureau'")->fetchAll();

    if (empty($tables)) {
        echo "<div style='background: #ff0000; color: white; padding: 15px;'>
              ❌ ERREUR : La table 'membrebureau' n'existe pas !
              <br>Crée-la d'abord avec cette commande SQL :</div>";

        echo "<pre style='background: #f0f0f0; padding: 10px;'>";
        echo "CREATE TABLE membrebureau (
    id_membre INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','responsable') NOT NULL
);";
        echo "</pre>";
        exit;
    }

    // Afficher les admins existants
    echo "<h3>📊 Admins existants :</h3>";
    $admins = $db->query("SELECT id_membre, nom, prenom, email, role FROM membrebureau")->fetchAll(PDO::FETCH_ASSOC);

    if (empty($admins)) {
        echo "<p>Aucun admin trouvé.</p>";
    } else {
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>ID</th><th>Nom</th><th>Prénom</th><th>Email</th><th>Rôle</th></tr>";
        foreach ($admins as $admin) {
            echo "<tr>";
            echo "<td>" . $admin['id_membre'] . "</td>";
            echo "<td>" . htmlspecialchars($admin['nom']) . "</td>";
            echo "<td>" . htmlspecialchars($admin['prenom']) . "</td>";
            echo "<td>" . htmlspecialchars($admin['email']) . "</td>";
            echo "<td>" . htmlspecialchars($admin['role']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    echo "<hr>";

    // Formulaire pour créer un admin
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nom = $_POST['nom'] ?? 'Admin';
        $prenom = $_POST['prenom'] ?? 'Test';
        $email = $_POST['email'] ?? 'admin@fage.org';
        $password = $_POST['password'] ?? 'admin123';
        $role = $_POST['role'] ?? 'admin';

        // Valider l'email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "<div style='background: #ff9900; padding: 10px;'>Email invalide !</div>";
        } else {
            // Hash du mot de passe
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Insérer dans la base
            $stmt = $db->prepare("INSERT INTO membrebureau (nom, prenom, email, password, role) 
                                 VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $prenom, $email, $password_hash, $role]);

            echo "<div style='background: #90EE90; padding: 15px; border-radius: 5px;'>
                  <h3>✅ ADMIN CRÉÉ AVEC SUCCÈS !</h3>
                  <p><strong>Email :</strong> $email</p>
                  <p><strong>Mot de passe :</strong> $password</p>
                  <p><strong>Rôle :</strong> $role</p>
                  <p><small>Note ce mot de passe, il ne sera plus affiché !</small></p>
                  <a href='views/auth/login.php' style='background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>
                  🚀 Tester la connexion maintenant
                  </a>
                  </div>";
        }
    }

} catch(Exception $e) {
    echo "<div style='background: #ffcccc; padding: 15px;'>
          <h3>❌ Erreur :</h3>
          <p>" . htmlspecialchars($e->getMessage()) . "</p>
          </div>";
}
?>

<hr>

<h3>➕ Créer un nouvel administrateur :</h3>
<form method="POST" style="background: #f9f9f9; padding: 20px; border-radius: 5px;">
    <table cellpadding="10">
        <tr>
            <td><label>Nom :</label></td>
            <td><input type="text" name="nom" value="Admin" required></td>
        </tr>
        <tr>
            <td><label>Prénom :</label></td>
            <td><input type="text" name="prenom" value="Test" required></td>
        </tr>
        <tr>
            <td><label>Email :</label></td>
            <td><input type="email" name="email" value="admin@fage.org" required></td>
        </tr>
        <tr>
            <td><label>Mot de passe :</label></td>
            <td><input type="text" name="password" value="admin123" required></td>
        </tr>
        <tr>
            <td><label>Rôle :</label></td>
            <td>
                <select name="role">
                    <option value="admin">Administrateur</option>
                    <option value="responsable">Responsable</option>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center;">
                <input type="submit" value="Créer cet administrateur"
                       style="background: #183146; color: white; padding: 10px 20px; border: none; cursor: pointer;">
            </td>
        </tr>
    </table>
</form>

<hr>

<p><strong>⚠️ IMPORTANT :</strong></p>
<ol>
    <li>Ce compte servira <strong>UNIQUEMENT pour les tests</strong></li>
    <li>Tu pourras te connecter avec ces identifiants</li>
    <li>Après test, tu pourras créer de vrais comptes</li>
    <li>Ne pas utiliser en production !</li>
</ol>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    input, select { padding: 5px; width: 200px; }
</style>