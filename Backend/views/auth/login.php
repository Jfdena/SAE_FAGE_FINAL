<?php
// Backend/views/auth/login.php
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../../vendor/autoload.php';


use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../../');
$dotenv->load();
session_start();

// Si déjà connecté, rediriger vers le dashboard
// Check for valid user_id (must exist, be set, and be a positive number)
/*if (!empty($_SESSION['user_id']) && $_SESSION['user_id'] > 0 && isset($_SESSION['user_email'])) {
    header('Location: ../admin/dashboard.php');
    exit();
}*/

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validation basique
    if (empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs";
    } else {
        try {
            // Connexion à la BDD
            require_once '../../config/Database.php';
            $db = new Database();
            $conn = $db->getConnection();

            // Chercher l'utilisateur (NOTE : table en minuscules 'membre bureau')
            $stmt = $conn->prepare("SELECT * FROM membrebureau WHERE email = ?");
            $stmt->execute([$email]);
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user) {
                // Vérifier le mot de passe
                $is_valid = password_verify($password, $user['password']);

                if ($is_valid) {
                    // SUCCÈS : créer la session
                    $_SESSION['user_id'] = $user['id_membre'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_nom'] = $user['nom'];
                    $_SESSION['user_prenom'] = $user['prenom'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['last_activity'] = time();

                    // Redirection vers le dashboard
                    header('Location: ../admin/dashboard.php');
                    exit();
                } else {
                    $error = "Mot de passe incorrect";
                }
            } else {
                $error = "Aucun compte avec l'email : " . htmlspecialchars($email);
            }

        } catch(Exception $e) {
            $error = "Erreur technique : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Backoffice FAGE</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #183146 0%, #0d6efd 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
        }

        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .login-header {
            background: #183146;
            color: white;
            padding: 25px;
            text-align: center;
        }

        .login-header img {
            height: 50px;
            margin-bottom: 15px;
        }

        .login-body {
            padding: 30px;
        }

        .form-control {
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        .btn-login {
            background: #183146;
            color: white;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            width: 100%;
            border: none;
            transition: background 0.3s;
        }

        .btn-login:hover {
            background: #0d6efd;
        }

        .test-credentials {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            border-left: 4px solid #183146;
        }
    </style>
</head>
<body>
<div class="login-container">
    <div class="login-card">
        <!-- En-tête -->
        <div class="login-header">
            <img src="../../../assets/img/logo_navbar.png" alt="FAGE Logo">
            <h4 class="mb-0">Backoffice FAGE</h4>
            <small>Accès réservé au bureau</small>
        </div>

        <!-- Corps -->
        <div class="login-body">
            <h5 class="text-center mb-4">🔐 Connexion</h5>

            <!-- Message d'erreur -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Formulaire -->
            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email"
                           name="email"
                           class="form-control"
                           value="<?php echo htmlspecialchars($email); ?>"
                           placeholder="admin@fage.org"
                           required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Mot de passe</label>
                    <input type="password"
                           name="password"
                           class="form-control"
                           placeholder="••••••••"
                           required>
                </div>

                <button type="submit" class="btn btn-login mb-3">
                    <i class="bi bi-box-arrow-in-right"></i> Se connecter
                </button>

                <div class="text-center">
                    <small class="text-muted">
                        <i class="bi bi-shield-lock"></i> Interface sécurisée
                    </small>
                </div>
            </form>


        </div>
    </div>

    <!-- Infos techniques -->
    <div class="text-center mt-3">
        <small class="text-white">
            PHP/MySQL • Backoffice FAGE • Version développement
        </small>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Focus sur le premier champ
    document.querySelector('input[name="email"]').focus();

</script>
</body>
</html>