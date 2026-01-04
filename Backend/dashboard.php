<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>FAGE - Dashboard</title>
    <link rel="stylesheet" href="../assets/css/stylesheet.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container-fluid">
            <a class="navbar-brand" href="../HTML/Accueil.html">
                <img src="../assets/img/logo_navbar.png" height="40" width="auto" alt="Logo">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav flex-grow-1 justify-content-evenly mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="../HTML/Accueil.html">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link" href="../HTML/Assos_Projet.html">Asso & Projets</a></li>
                    <li class="nav-item"><a class="nav-link" href="../HTML/Actualité_Ressources.html">Actualité & Ressources</a></li>
                    <li class="nav-item"><a class="nav-link" href="../HTML/Dons_Engagement.html">Dons & Engagement</a></li>
                    <li class="nav-item"><a class="nav-link" href="../HTML/A_propos.html">À propos</a></li>
                </ul>
                <div class="d-flex align-items-center">
                    <span class="navbar-text me-3 fw-bold text-primary">Espace Administration</span>
                    <a href="dashboard.php" class="btn btn-primary me-2">Tableau de bord</a>
                    <a href="login.php" class="btn btn-outline-danger btn-sm">Déconnexion</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5">

        <script>
            window.addEventListener("DOMContentLoaded", () => {
                const params = new URLSearchParams(window.location.search);
                const section = params.get("section");
                const radios = document.querySelectorAll('input[name="section-toggle"]');
                const sections = document.querySelectorAll('section');

                if (section) {
                    const radio = document.querySelector(`input[value="${section}"]`);
                    if (radio) radio.checked = true;
                }

                function updateVisibleSection() {
                    sections.forEach(section => section.classList.add('d-none'));
                    const checked = document.querySelector('input[name="section-toggle"]:checked');
                    if (checked) {
                        const target = document.getElementById(checked.value);
                        if (target) target.classList.remove('d-none');
                    }
                }

                radios.forEach(radio => radio.addEventListener('change', updateVisibleSection));
                updateVisibleSection();
            });
        </script>
        <div class="col mx-auto p-4 btn-group" role="group">
            <input type="radio" class="btn-check" id="radio-dashboard" name="section-toggle" value="Dashboard" checked>
            <label class="btn btn-primary" for="radio-dashboard">Dashboard</label>

        </div>
        <section id="Dashboard"><?php require_once "./Sections/section_dashboard.php"?></section>
    </div>