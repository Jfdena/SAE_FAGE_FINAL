<?php
# ⭐ PRIORITAIRE - Liste avec filtres
// Backend/views/admin/adherents/addFiche.php

// Protection
require_once '../../../session_check.php';

// Connexion BDD
require_once '../../../config/Database.php';
$db = new Database();
try {
    $conn = $db->getConnection();
} catch (Exception $e) {

}


// Variables pour le formulaire
$errors = [];
$success = false;

// Données du formulaire (pour pré-remplissage en cas d'erreur)
$formData = [
    'nom' => '',
    'prenom' => '',
    'email' => '',
    'telephone' => '',
    'date_naissance' => '',
    'statut' => 'actif'
];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et nettoyer les données
    $formData['nom'] = trim($_POST['nom'] ?? '');
    $formData['prenom'] = trim($_POST['prenom'] ?? '');
    $formData['email'] = trim($_POST['email'] ?? '');
    $formData['telephone'] = trim($_POST['telephone'] ?? '');
    $formData['date_naissance'] = $_POST['date_naissance'] ?? '';
    $formData['statut'] = $_POST['statut'] ?? 'actif';

    // Validation
    if (empty($formData['nom'])) {
        $errors[] = "Le nom est obligatoire";
    }

    if (empty($formData['prenom'])) {
        $errors[] = "Le prénom est obligatoire";
    }

    if (!empty($formData['email']) && !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email n'est pas valide";
    }
    // Si pas d'erreurs, insérer en BDD
    if (empty($errors)) {
        try {
            // Préparer la requête d'insertion
            $sql = "INSERT INTO benevole (nom, prenom, email, telephone, date_naissance, date_inscription, statut) 
                        VALUES (?, ?, ?, ?, ?, CURDATE(), ?)";

            $stmt = $conn->prepare($sql);

            // Préparation des variables pour bind_param (null si vide)
            $v_email = $formData['email'] ?: null;
            $v_tel = $formData['telephone'] ?: null;
            $v_naiss = $formData['date_naissance'] ?: null;

            // Liaison des paramètres : 6 chaînes (s)
            $stmt->bind_param("ssssss",
                    $formData['nom'],
                    $formData['prenom'],
                    $v_email,
                    $v_tel,
                    $v_naiss,
                    $formData['statut']
            );

            $stmt->execute();

            // Récupérer l'ID du nouveau bénévole (mysqli version)
            $newId = $conn->insert_id;
            $success = true;

            // Réinitialiser le formulaire après succès
            $formData = [
                'nom' => '',
                'prenom' => '',
                'email' => '',
                'telephone' => '',
                'date_naissance' => '',
                'statut' => 'actif'
            ];

        } catch (Exception $e) {
            $errors[] = "Erreur lors de l'insertion : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un bénévole - FAGE</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <style>
        .form-container {
            max-width: 700px;
            margin: 0 auto;
        }
        .required::after {
            content: " *";
            color: #dc3545;
        }
        .card-header {
            background: linear-gradient(135deg, #183146 0%, #0d6efd 100%);
            color: white;
        }
        .form-control:focus {
            border-color: #183146;
            box-shadow: 0 0 0 0.2rem rgba(24, 49, 70, 0.25);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../dashboard.php">
                <i class="bi bi-house"></i> Dashboard
            </a>
            <div class="navbar-text text-white">
                <i class="bi bi-person-plus"></i> Nouveau bénévole
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="form-container">
            <!-- Carte principale -->
            <div class="card shadow">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-person-plus"></i> Ajouter un nouveau bénévole
                    </h4>
                </div>

                <div class="card-body p-4">
                    <!-- Messages -->
                    <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <h5><i class="bi bi-check-circle"></i> Succès !</h5>
                        <p class="mb-0">
                            Le bénévole a été ajouté avec succès.
                            <?php if (isset($newId)): ?>
                            <br><small>ID : #<?php echo $newId; ?></small>
                            <?php endif; ?>
                        </p>
                        <div class="mt-3">
                            <a href="listeAdherent.php" class="btn btn-outline-success btn-sm">
                                <i class="bi bi-list"></i> Voir la liste
                            </a>
                            <a href="addFiche.php" class="btn btn-success btn-sm">
                                <i class="bi bi-plus-circle"></i> Ajouter un autre
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <h5><i class="bi bi-exclamation-triangle"></i> Erreurs :</h5>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <!-- Formulaire -->
                    <form method="POST" action="">
                        <div class="row g-3">
                            <!-- Nom -->
                            <div class="col-md-6">
                                <label for="nom" class="form-label required">Nom</label>
                                <input type="text"
                                       id="nom"
                                       name="nom"
                                       class="form-control"
                                       value="<?php echo htmlspecialchars($formData['nom']); ?>"
                                       required
                                       placeholder="Dupont">
                                <div class="form-text">Nom de famille</div>
                            </div>

                            <!-- Prénom -->
                            <div class="col-md-6">
                                <label for="prenom" class="form-label required">Prénom</label>
                                <input type="text"
                                       id="prenom"
                                       name="prenom"
                                       class="form-control"
                                       value="<?php echo htmlspecialchars($formData['prenom']); ?>"
                                       required
                                       placeholder="Jean">
                                <div class="form-text">Prénom usuel</div>
                            </div>

                            <!-- Email -->
                            <div class="col-md-6">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope"></i> Email
                                </label>
                                <input type="email"
                                       id="email"
                                       name="email"
                                       class="form-control"
                                       value="<?php echo htmlspecialchars($formData['email']); ?>"
                                       placeholder="jean.dupont@email.com">
                                <div class="form-text">Facultatif mais recommandé</div>
                            </div>

                            <!-- Téléphone -->
                            <div class="col-md-6">
                                <label for="telephone" class="form-label">
                                    <i class="bi bi-telephone"></i> Téléphone
                                </label>
                                <input type="tel"
                                       id="telephone"
                                       name="telephone"
                                       class="form-control"
                                       value="<?php echo htmlspecialchars($formData['telephone']); ?>"
                                       placeholder="06 12 34 56 78">
                                <div class="form-text">Facultatif</div>
                            </div>

                            <!-- Date de naissance -->
                            <div class="col-md-6">
                                <label for="date_naissance" class="form-label">
                                    <i class="bi bi-calendar"></i> Date de naissance
                                </label>
                                <input type="date"
                                       id="date_naissance"
                                       name="date_naissance"
                                       class="form-control"
                                       value="<?php echo htmlspecialchars($formData['date_naissance']); ?>"
                                       max="<?php echo date('Y-m-d'); ?>">
                                <div class="form-text">Facultatif</div>
                            </div>

                            <!-- Statut -->
                            <div class="col-md-6">
                                <label for="statut" class="form-label">
                                    <i class="bi bi-toggle-on"></i> Statut
                                </label>
                                <select id="statut" name="statut" class="form-select">
                                    <option value="actif" <?php echo $formData['statut'] == 'actif' ? 'selected' : ''; ?>>
                                        Actif
                                    </option>
                                    <option value="inactif" <?php echo $formData['statut'] == 'inactif' ? 'selected' : ''; ?>>
                                        Inactif
                                    </option>
                                </select>
                                <div class="form-text">Statut du bénévole dans l'association</div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Boutons -->
                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="listeAdherent.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Retour à la liste
                                </a>
                                <a href="../dashboard.php" class="btn btn-link text-muted">
                                    <i class="bi bi-x-circle"></i> Annuler
                                </a>
                            </div>

                            <div>
                                <button type="reset" class="btn btn-outline-warning me-2">
                                    <i class="bi bi-eraser"></i> Effacer
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Enregistrer
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Aide -->
                <div class="card-footer bg-light">
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i>
                        Tous les bénévoles ajoutés seront visibles dans la liste et pourront participer aux événements.
                        La date d'inscription est automatiquement enregistrée à la date du jour.
                    </small>
                </div>
            </div>

            <!-- Conseils -->
            <div class="card mt-3 border-info">
                <div class="card-body">
                    <h6><i class="bi bi-lightbulb"></i> Bonnes pratiques :</h6>
                    <ul class="small mb-0">
                        <li>Vérifiez l'exactitude des informations avant validation</li>
                        <li>Demandez toujours l'autorisation pour enregistrer les coordonnées</li>
                        <li>Les champs marqués d'une * sont obligatoires</li>
                        <li>Un email valide facilite la communication</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // Focus sur le premier champ
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('nom').focus();

        // Calcul automatique de l'âge max (18 ans minimum pour un bénévole)
        const today = new Date();
        const maxDate = new Date(today.getFullYear() - 16, today.getMonth(), today.getDate());
        document.getElementById('date_naissance').max = maxDate.toISOString().split('T')[0];

        // Calcul de l'âge si date saisie
        const dateInput = document.getElementById('date_naissance');
        dateInput.addEventListener('change', function() {
            if (this.value) {
                const birthDate = new Date(this.value);
                let age = today.getFullYear() - birthDate.getFullYear();
                const m = today.getMonth() - birthDate.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                console.log('Âge calculé :', age, 'ans');
            }
        });
    });

    // Validation côté client
    document.querySelector('form').addEventListener('submit', function(e) {
        const nom = document.getElementById('nom').value.trim();
        const prenom = document.getElementById('prenom').value.trim();

        if (!nom || !prenom) {
            e.preventDefault();
            alert('Veuillez remplir les champs obligatoires (Nom et Prénom)');
            return false;
        }

        return true;
    });
    </script>
</body>
</html>