<?php
// Backend/views/admin/partenaires/addPartenaire.php

// Protection
require_once '../../../test/session_check.php';

// Connexion BDD
require_once '../../../config/Database.php';
$db = new Database();
$conn = $db->getConnection();
require_once '../../../config/Constraints.php';
$constraints = new Constraints($conn);
// Variables
$errors = [];
$success = false;
$new_partenaire_id = null;

// Données par défaut
$formData = [
    'nom' => '',
    'type' => 'donateur',
    'montant' => '',
    'date_contribution' => date('Y-m-d'),
    'contact_email' => '',
    'contact_telephone' => '',
    'description' => '',
    'statut' => 'actif'
];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ========== NOUVELLE VALIDATION AVEC CONSTRAINTS ==========
    $rules = [
        'nom' => ['required' => true, 'max' => 100, 'unique_partenaire' => true],
        'type' => ['required' => true],
        'montant' => ['amount' => ['allow_zero' => true]],
        'date_contribution' => ['date' => true, 'not_future' => true],
        'contact_email' => ['email' => true],
        'contact_telephone' => ['phone' => true],
        'description' => ['max' => 500]
    ];

    $errors = $constraints->validateFormData($_POST, $rules);

    // Si pas d'erreurs, nettoyer et insérer
    if (empty($errors)) {
        // Nettoyer et formater les données
        $formData['nom'] = $constraints->sanitize($_POST['nom']);
        $formData['type'] = $_POST['type'];
        $formData['montant'] = $constraints->formatAmountForDB($_POST['montant']);
        $formData['date_contribution'] = $constraints->formatDateForDB($_POST['date_contribution']);
        $formData['contact_email'] = $constraints->sanitize($_POST['contact_email']);
        $formData['contact_telephone'] = $constraints->sanitize($_POST['contact_telephone']);
        $formData['description'] = $constraints->sanitize($_POST['description']);
        $formData['statut'] = $_POST['statut'] ?? 'actif';

        try {
            $sql = "INSERT INTO Partenaire (nom, type, montant, date_contribution, 
                    contact_email, contact_telephone, description, statut) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $formData['nom'],
                $formData['type'],
                $formData['montant'],
                $formData['date_contribution'],
                $formData['contact_email'] ?: null,
                $formData['contact_telephone'] ?: null,
                $formData['description'] ?: null,
                $formData['statut']
            ]);

            $new_partenaire_id = $conn->lastInsertId();
            $success = true;

            // Réinitialiser après succès
            $formData = [
                'nom' => '',
                'type' => 'donateur',
                'montant' => '',
                'date_contribution' => date('Y-m-d'),
                'contact_email' => '',
                'contact_telephone' => '',
                'description' => '',
                'statut' => 'actif'
            ];

        } catch (Exception $e) {
            $errors[] = "Erreur lors de l'ajout : " . $e->getMessage();
            error_log("Erreur SQL: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau partenaire - FAGE</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <style>
        .form-container {
            max-width: 700px;
            margin: 0 auto;
        }
        .card-header {
            background: linear-gradient(135deg, #183146 0%, #0d6efd 100%);
            color: white;
        }
        .required::after {
            content: " *";
            color: #dc3545;
        }
        .montant-input {
            position: relative;
        }
        .montant-input::after {
            content: "€";
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        .statut-badge {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        .statut-actif {
            background-color: #28a745;
        }
        .statut-inactif {
            background-color: #6c757d;
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
            <i class="bi bi-building-add"></i> Nouveau partenaire
        </div>
    </div>
</nav>

<div class="container py-4">
    <div class="form-container">
        <!-- En-tête -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="bi bi-handshake"></i> Ajouter un partenaire</h2>
                <p class="text-muted mb-0">
                    Enregistrez un nouveau partenaire ou donateur de la FAGE
                </p>
            </div>
            <div>
                <a href="listePartenaires.php" class="btn btn-outline-primary">
                    <i class="bi bi-list"></i> Liste des partenaires
                </a>
            </div>
        </div>

        <!-- Carte principale -->
        <div class="card shadow">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="bi bi-info-circle"></i> Informations du partenaire
                </h4>
            </div>

            <div class="card-body p-4">
                <!-- Messages -->
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <h5><i class="bi bi-check-circle"></i> Partenaire ajouté !</h5>
                        <p class="mb-0">
                            Le partenaire a été enregistré avec succès.
                            <?php if ($new_partenaire_id): ?>
                                <br><small>ID : #<?php echo $new_partenaire_id; ?></small>
                            <?php endif; ?>
                        </p>
                        <div class="mt-3">
                            <a href="listePartenaires.php" class="btn btn-sm btn-success">
                                <i class="bi bi-list"></i> Voir la liste
                            </a>
                            <a href="addPartenaire.php" class="btn btn-sm btn-outline-success">
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

                <!-- Aperçu rapide -->
                <div class="alert alert-info mb-4">
                    <h6><i class="bi bi-eye"></i> Aperçu :</h6>
                    <div id="preview">
                        <span class="text-muted">Remplissez le formulaire pour voir l'aperçu...</span>
                    </div>
                </div>

                <!-- Formulaire -->
                <form method="POST" action="" id="partenaireForm">
                    <div class="row g-3">
                        <!-- Nom -->
                        <div class="col-md-8">
                            <label for="nom" class="form-label required">Nom du partenaire</label>
                            <input type="text"
                                   id="nom"
                                   name="nom"
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($formData['nom']); ?>"
                                   required
                                   placeholder="Ex: Entreprise ABC, Fondation XYZ..."
                                   oninput="updatePreview()">
                            <div class="form-text">Nom complet de l'organisme ou entreprise</div>
                        </div>

                        <!-- Type -->
                        <div class="col-md-4">
                            <label for="type" class="form-label">Type</label>
                            <select id="type" name="type" class="form-select" onchange="updatePreview()">
                                <option value="donateur" <?php echo $formData['type'] == 'donateur' ? 'selected' : ''; ?>>Donateur</option>
                                <option value="subvention" <?php echo $formData['type'] == 'subvention' ? 'selected' : ''; ?>>Subvention</option>
                            </select>
                        </div>

                        <!-- Statut -->
                        <div class="col-md-4">
                            <label for="statut" class="form-label">
                                <i class="bi bi-toggle-on"></i> Statut
                            </label>
                            <select id="statut" name="statut" class="form-select" onchange="updatePreview()">
                                <option value="actif" <?php echo $formData['statut'] == 'actif' ? 'selected' : ''; ?>>
                                    <span class="statut-badge statut-actif"></span> Actif
                                </option>
                                <option value="inactif" <?php echo $formData['statut'] == 'inactif' ? 'selected' : ''; ?>>
                                    <span class="statut-badge statut-inactif"></span> Inactif
                                </option>
                            </select>
                            <div class="form-text">Statut du partenariat</div>
                        </div>

                        <!-- Montant -->
                        <div class="col-md-4">
                            <label for="montant" class="form-label">
                                <i class="bi bi-cash-coin"></i> Montant (€)
                            </label>
                            <div class="input-group">
                                <input type="number"
                                       id="montant"
                                       name="montant"
                                       class="form-control"
                                       value="<?php echo $formData['montant']; ?>"
                                       min="0"
                                       step="0.01"
                                       placeholder="Optionnel"
                                       oninput="updatePreview()">
                                <span class="input-group-text">€</span>
                            </div>
                            <div class="form-text">Montant du don ou de la subvention</div>
                        </div>

                        <!-- Date de contribution -->
                        <div class="col-md-4">
                            <label for="date_contribution" class="form-label">
                                <i class="bi bi-calendar"></i> Date de contribution
                            </label>
                            <input type="date"
                                   id="date_contribution"
                                   name="date_contribution"
                                   class="form-control"
                                   value="<?php echo $formData['date_contribution']; ?>"
                                   onchange="updatePreview()">
                            <div class="form-text">Date du don ou versement</div>
                        </div>

                        <!-- Contact email -->
                        <div class="col-md-6">
                            <label for="contact_email" class="form-label">
                                <i class="bi bi-envelope"></i> Email de contact
                            </label>
                            <input type="email"
                                   id="contact_email"
                                   name="contact_email"
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($formData['contact_email']); ?>"
                                   placeholder="contact@entreprise.com"
                                   oninput="updatePreview()">
                        </div>

                        <!-- Contact téléphone -->
                        <div class="col-md-6">
                            <label for="contact_telephone" class="form-label">
                                <i class="bi bi-telephone"></i> Téléphone
                            </label>
                            <input type="tel"
                                   id="contact_telephone"
                                   name="contact_telephone"
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($formData['contact_telephone']); ?>"
                                   placeholder="01 23 45 67 89"
                                   oninput="updatePreview()">
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label for="description" class="form-label">
                                <i class="bi bi-card-text"></i> Description / Notes
                            </label>
                            <textarea id="description"
                                      name="description"
                                      class="form-control"
                                      rows="3"
                                      placeholder="Informations complémentaires, domaine d'activité, historique..."
                                      oninput="updatePreview()"><?php echo htmlspecialchars($formData['description']); ?></textarea>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Boutons -->
                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="listePartenaires.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Annuler
                            </a>
                            <button type="reset" class="btn btn-outline-warning ms-2" onclick="resetForm()">
                                <i class="bi bi-eraser"></i> Réinitialiser
                            </button>
                        </div>

                        <div>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle"></i> Enregistrer le partenaire
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Aide -->
            <div class="card-footer bg-light">
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i>
                            Les partenaires apparaîtront dans la liste et les statistiques.
                        </small>
                    </div>
                    <div class="col-md-6 text-end">
                        <small class="text-muted">
                            <i class="bi bi-shield-check"></i>
                            Tous les champs marqués d'un * sont obligatoires.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conseils -->
        <div class="card mt-3 border-info">
            <div class="card-body">
                <h6><i class="bi bi-lightbulb"></i> Conseils pour un bon enregistrement :</h6>
                <ul class="small mb-0">
                    <li><strong>Nom complet</strong> : Utilisez le nom officiel de l'organisme</li>
                    <li><strong>Montant précis</strong> : Indiquez le montant exact pour les statistiques</li>
                    <li><strong>Contact</strong> : Un email valide facilite la communication</li>
                    <li><strong>Statut</strong> : "Actif" pour partenaires actuels, "Inactif" pour anciens partenaires</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Aperçu dynamique
    function updatePreview() {
        const nom = document.getElementById('nom').value || '[Nom du partenaire]';
        const type = document.getElementById('type').value;
        const typeText = type === 'donateur' ? 'Donateur' : 'Subvention';
        const statut = document.getElementById('statut').value;
        const statutText = statut === 'actif' ? 'Actif' : 'Inactif';
        const statutColor = statut === 'actif' ? 'success' : 'secondary';
        const montant = document.getElementById('montant').value;
        const dateContribution = document.getElementById('date_contribution').value;
        const email = document.getElementById('contact_email').value;
        const telephone = document.getElementById('contact_telephone').value;
        const description = document.getElementById('description').value;

        // Formater la date
        const formatDate = (dateStr) => {
            if (!dateStr) return 'Non spécifiée';
            const date = new Date(dateStr);
            return date.toLocaleDateString('fr-FR');
        };

        // Construire l'aperçu
        let previewHTML = `
            <div class="row">
                <div class="col-md-8">
                    <strong>${nom}</strong>
                    <div class="mt-1">
                        <span class="badge bg-${type === 'donateur' ? 'primary' : 'warning'} me-2">
                            ${typeText}
                        </span>
                        <span class="badge bg-${statutColor}">
                            ${statutText}
                        </span>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    ${montant ? `<strong>${parseFloat(montant).toFixed(2)} €</strong>` : '<em>Montant non spécifié</em>'}
                </div>
            </div>
            <div class="row mt-2 small">
                <div class="col-md-6">
                    <i class="bi bi-calendar"></i> ${formatDate(dateContribution)}
                </div>
                <div class="col-md-6">
                    ${email ? `<i class="bi bi-envelope"></i> ${email}` : ''}
                </div>
            </div>
        `;

        if (description) {
            previewHTML += `
                <div class="row mt-2">
                    <div class="col-12">
                        <p class="mb-0 small">
                            <strong>Notes :</strong> ${description.substring(0, 80)}${description.length > 80 ? '...' : ''}
                        </p>
                    </div>
                </div>
            `;
        }

        document.getElementById('preview').innerHTML = previewHTML;
    }

    function resetForm() {
        if (confirm('Voulez-vous vraiment réinitialiser tous les champs ?')) {
            setTimeout(updatePreview, 100);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser l'aperçu
        updatePreview();

        // Focus sur le nom
        document.getElementById('nom').focus();

        // Formatage automatique du montant
        const montantInput = document.getElementById('montant');
        montantInput.addEventListener('blur', function() {
            if (this.value) {
                this.value = parseFloat(this.value).toFixed(2);
                updatePreview();
            }
        });

        // Validation
        document.getElementById('partenaireForm').addEventListener('submit', function(e) {
            const nom = document.getElementById('nom').value.trim();
            const email = document.getElementById('contact_email').value.trim();
            const montant = document.getElementById('montant').value;

            if (!nom) {
                e.preventDefault();
                alert('Le nom du partenaire est obligatoire');
                document.getElementById('nom').focus();
                return false;
            }

            if (email && !validateEmail(email)) {
                e.preventDefault();
                alert('L\'email n\'est pas valide');
                document.getElementById('contact_email').focus();
                return false;
            }

            if (montant && parseFloat(montant) < 0) {
                e.preventDefault();
                alert('Le montant ne peut pas être négatif');
                document.getElementById('montant').focus();
                return false;
            }

            return true;
        });

        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
    });
</script>
</body>
</html>