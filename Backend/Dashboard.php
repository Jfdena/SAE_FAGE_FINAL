<?php

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
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="../HTML/Accueil.html">Retour au site</a></li>
            </ul>
            <span class="navbar-text">Espace Administration</span>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-12 mb-4">
            <h2>Tableau de Bord</h2>
            <p class="text-muted">Bienvenue dans votre espace de gestion.</p>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row text-center mb-5">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-primary text-white p-4">
                <h3>50</h3>
                <p class="mb-0">Villes</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-success text-white p-4">
                <h3>2000</h3>
                <p class="mb-0">Associations</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-info text-white p-4">
                <h3>1M</h3>
                <p class="mb-0">Étudiants</p>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Actions Section -->
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white"><strong>Actions Récentes</strong></div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>Date</th>
                            <th>Action</th>
                            <th>Statut</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>03/01/2026</td>
                            <td>Nouvelle inscription Asso</td>
                            <td><span class="badge bg-warning">En attente</span></td>
                        </tr>
                        <tr>
                            <td>02/01/2026</td>
                            <td>Mise à jour Actualité</td>
                            <td><span class="badge bg-success">Publié</span></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Shortcuts -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white"><strong>Raccourcis</strong></div>
                <div class="list-group list-group-flush">
                    <a href="#" class="list-group-item list-group-item-action">Gérer les Assos</a>
                    <a href="#" class="list-group-item list-group-item-action">Ajouter une Actualité</a>
                    <a href="#" class="list-group-item list-group-item-action text-danger">Déconnexion</a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>