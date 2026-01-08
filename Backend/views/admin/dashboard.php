<?php
// Backend/views/admin/dashboard.php

// 1. VÉRIFIER SI L'UTILISATEUR EST CONNECTÉ

require_once '../../test/session_check.php';

//TEST
// Après require_once '../session_check.php';
echo "<p>Bonjour " . getUserFullName() . "!</p>";
echo "<p>Rôle : " . getUserRole() . "</p>";
echo "<p>Admin ? " . (isAdmin() ? 'OUI' : 'NON') . "</p>";

// 2. INCLURE LA CONNEXION BDD
require_once '../../config/Database.php';

// 3. RÉCUPÉRER LES INFOS DE SESSION
$user_nom = $_SESSION['user_nom'] ?? 'Utilisateur';
$user_prenom = $_SESSION['user_prenom'] ?? '';
$user_role = $_SESSION['user_role'] ?? 'invité';
$user_email = $_SESSION['user_email'] ?? '';

// 4. CONNEXION BDD POUR LES STATS
$db = new Database();
try {
    $conn = $db->getConnection();
} catch (Exception $e) {

}

// 5. RÉCUPÉRER QUELQUES STATISTIQUES SIMPLES
$stats = [
    'benevoles' => 0,
    'missions' => 0,
    'partenaires' => 0
];

try {
    // Compter les bénévoles actifs
    $stmt = $conn->query("SELECT COUNT(*) FROM benevole WHERE statut = 'actif'");
    $stats['benevoles'] = $stmt->fetchColumn();

    // Compter les missions/événements
    $stmt = $conn->query("SELECT COUNT(*) FROM evenement");
    $stats['missions'] = $stmt->fetchColumn();

    // Compter les partenaires
    $stmt = $conn->query("SELECT COUNT(*) FROM partenaire");
    $stats['partenaires'] = $stmt->fetchColumn();

} catch(Exception $e) {
    // En cas d'erreur, on garde les stats à vide
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - FAGE Backoffice</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar-custom {
            background: linear-gradient(135deg, #183146 0%, #0d6efd 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .sidebar {
            background: white;
            min-height: calc(100vh - 56px);
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 56px;
        }

        .sidebar .nav-link {
            color: #333;
            padding: 12px 20px;
            border-left: 3px solid transparent;
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover {
            background-color: #f0f7ff;
            border-left: 3px solid #183146;
            color: #183146;
        }

        .sidebar .nav-link.active {
            background-color: #f0f7ff;
            border-left: 3px solid #183146;
            color: #183146;
            font-weight: 600;
        }

        .stat-card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .welcome-card {
            background: linear-gradient(135deg, #f0f7ff 0%, #e3f2fd 100%);
            border: none;
            border-left: 4px solid #183146;
        }
    </style>
</head>
<body>
<!-- ========== NAVBAR ========== -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">
            <i class="bi bi-speedometer2"></i>
            <strong>FAGE Backoffice</strong>
        </a>

        <div class="d-flex align-items-center text-white">
            <div class="me-3 text-end">
                <small>Connecté en tant que</small><br>
                <strong><?php echo htmlspecialchars($user_prenom . ' ' . $user_nom); ?></strong>
                <span class="badge bg-light text-dark ms-2"><?php echo htmlspecialchars($user_role); ?></span>
            </div>
            <a href="../auth/logout.php" class="btn btn-outline-light btn-sm">
                <i class="bi bi-box-arrow-right"></i> Déconnexion
            </a>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <!-- ========== SIDEBAR ========== -->
        <div class="col-md-3 col-lg-2 px-0 sidebar d-none d-md-block">
            <div class="p-3">
                <h6 class="text-muted mb-3"><i class="bi bi-menu-button"></i> MENU</h6>

                <nav class="nav flex-column">
                    <a class="nav-link active" href="dashboard.php">
                        <i class="bi bi-speedometer2 me-2"></i> Tableau de bord
                    </a>

                    <a class="nav-link" href="adherents/listeAdherent.php">
                        <i class="bi bi-people me-2"></i> Bénévoles
                        <span class="badge bg-primary float-end"><?php echo $stats['benevoles']; ?></span>
                    </a>

                    <a class="nav-link" href="missions/calendrier.php">
                        <i class="bi bi-calendar-event me-2"></i> Événements
                        <span class="badge bg-success float-end"><?php echo $stats['missions']; ?></span>
                    </a>

                    <a class="nav-link" href="partenaires/listePartenaires.php">
                        <i class="bi bi-handshake me-2"></i> Partenaires
                        <span class="badge bg-warning text-dark float-end"><?php echo $stats['partenaires']; ?></span>
                    </a>

                    <hr class="my-2">


                    <a class="nav-link" href="../auth/logout.php">
                        <i class="bi bi-box-arrow-right me-2"></i> Déconnexion
                    </a>
                </nav>
            </div>
        </div>

        <!-- ========== CONTENU PRINCIPAL ========== -->
        <main class="col-md-9 col-lg-10 px-md-4 py-4">
            <!-- Message de bienvenue -->
            <div class="card welcome-card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-2">👋 Bienvenue, <?php echo htmlspecialchars($user_prenom); ?> !</h4>
                            <p class="mb-0 text-muted">
                                Vous êtes connecté au backoffice FAGE.
                                Utilisez le menu pour gérer les bénévoles, événements et partenaires.
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                                <span class="badge bg-primary fs-6 p-2">
                                    <?php echo date('d/m/Y'); ?>
                                </span>
                        </div>
                    </div>
                </div>
            </div>

            <h5 class="mb-3">📊 Vue d'ensemble</h5>

            <!-- Cartes de statistiques -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card stat-card border-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted">Bénévoles actifs</h6>
                                    <h2 class="mb-0"><?php echo $stats['benevoles']; ?></h2>
                                </div>
                                <div class="bg-primary text-white rounded-circle p-3">
                                    <i class="bi bi-people fs-4"></i>
                                </div>
                            </div>
                            <a href="adherents/listeAdherent.php" class="stretched-link"></a>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card stat-card border-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted">Événements</h6>
                                    <h2 class="mb-0"><?php echo $stats['missions']; ?></h2>
                                </div>
                                <div class="bg-success text-white rounded-circle p-3">
                                    <i class="bi bi-calendar-check fs-4"></i>
                                </div>
                            </div>
                            <a href="missions/calendrier.php" class="stretched-link"></a>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card stat-card border-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted">Partenaires</h6>
                                    <h2 class="mb-0"><?php echo $stats['partenaires']; ?></h2>
                                </div>
                                <div class="bg-warning text-white rounded-circle p-3">
                                    <i class="bi bi-hand-thumbs-up fs-4"></i>
                                </div>
                            </div>
                            <a href="partenaires/listePartenaires.php" class="stretched-link"></a>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card stat-card border-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted">Votre rôle</h6>
                                    <h4 class="mb-0"><?php echo htmlspecialchars(ucfirst($user_role)); ?></h4>
                                </div>
                                <div class="bg-info text-white rounded-circle p-3">
                                    <i class="bi bi-person-badge fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="bi bi-people" style="font-size: 2rem; color: #0d6efd;"></i>
                            <h5>Bénévoles</h5>
                            <a href="adherents/listeAdherent.php" class="btn btn-outline-primary btn-sm">
                                Gérer les bénévoles
                            </a>
                            <a href="adherents/CotisationsAdherent.php" class="btn btn-outline-success btn-sm mt-2">
                                Cotisations
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="bi bi-calendar-event" style="font-size: 2rem; color: #198754;"></i>
                            <h5>Événements</h5>
                            <a href="missions/calendrier.php" class="btn btn-outline-success btn-sm">
                                Calendrier
                            </a>
                            <a href="missions/addMissions.php" class="btn btn-outline-warning btn-sm mt-2">
                                Nouvel événement
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="bi bi-handshake" style="font-size: 2rem; color: #fd7e14;"></i>
                            <h5>Partenaires</h5>
                            <a href="partenaires/listePartenaires.php" class="btn btn-outline-warning btn-sm">
                                Liste partenaires
                            </a>
                            <a href="partenaires/HistoContribution.php" class="btn btn-outline-info btn-sm mt-2">
                                Contributions
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions rapides (garder l'ancien aussi si vous voulez) -->
            <div class="card mt-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-lightning"></i> Actions rapides</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <a href="adherents/addFiche.php" class="btn btn-outline-primary w-100 py-3">
                                <i class="bi bi-person-plus fs-4 d-block mb-2"></i>
                                Ajouter un bénévole
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="missions/addMissions.php" class="btn btn-outline-success w-100 py-3">
                                <i class="bi bi-plus-circle fs-4 d-block mb-2"></i>
                                Créer un événement
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="partenaires/addPartenaire.php" class="btn btn-outline-warning w-100 py-3">
                                <i class="bi bi-building-add fs-4 d-block mb-2"></i>
                                Ajouter un partenaire
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pied de page -->
            <footer class="mt-5 pt-4 border-top">
                <div class="row">
                    <div class="col-md-6">
                        <p class="text-muted small">
                            <i class="bi bi-shield-check"></i>
                            Backoffice FAGE - Accès sécurisé
                        </p>
                    </div>
                    <div class="col-md-6 text-end">
                        <p class="text-muted small">
                            Version 1.0 • Développement en cours
                        </p>
                    </div>
                </div>
            </footer>
        </main>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Animation simple pour les cartes
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.stat-card');
        cards.forEach((card, index) => {
            card.style.animationDelay = (index * 0.1) + 's';
        });
    });
</script>
</body>
</html>