<?php
// Backend/views/partials/sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<div class="sidebar">
    <div class="logo">
        <img src="../../../assets/img/logo_navbar.png" alt="FAGE">
        <div class="logo-text text-white mt-2" style="font-size: 0.9rem;">Backoffice</div>
    </div>

    <nav class="nav flex-column">
        <a href="dashboard.php" class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
            <i class="bi bi-speedometer2"></i>
            <span>Tableau de bord</span>
        </a>

        <div class="mt-4 px-3 text-uppercase small text-white-50">Gestion</div>

        <a href="adherents/list.php" class="nav-link <?php echo (strpos($current_page, 'adherents') !== false) ? 'active' : ''; ?>">
            <i class="bi bi-people"></i>
            <span>Bénévoles</span>
        </a>

        <a href="missions/list.php" class="nav-link <?php echo (strpos($current_page, 'missions') !== false) ? 'active' : ''; ?>">
            <i class="bi bi-calendar-event"></i>
            <span>Événements</span>
        </a>

        <a href="partenaires/list.php" class="nav-link <?php echo (strpos($current_page, 'partenaires') !== false) ? 'active' : ''; ?>">
            <i class="bi bi-handshake"></i>
            <span>Partenaires</span>
        </a>

        <div class="mt-4 px-3 text-uppercase small text-white-50">Communication</div>

        <a href="communication/actualites.php" class="nav-link">
            <i class="bi bi-newspaper"></i>
            <span>Actualités</span>
        </a>

        <a href="statistiques/index.php" class="nav-link <?php echo (strpos($current_page, 'statistiques') !== false) ? 'active' : ''; ?>">
            <i class="bi bi-graph-up"></i>
            <span>Statistiques</span>
        </a>

        <div class="mt-4 px-3 text-uppercase small text-white-50">Compte</div>

        <a href="../../Backend/controllers/AuthController.php?action=logout" class="nav-link text-danger">
            <i class="bi bi-box-arrow-right"></i>
            <span>Déconnexion</span>
        </a>
    </nav>
</div>