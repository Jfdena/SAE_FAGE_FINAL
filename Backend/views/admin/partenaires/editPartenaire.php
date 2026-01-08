<?php
// Backend/views/admin/partenaires/editPartenaire.php

// Protection
require_once '../../../test/session_check.php';

// Vérifier l'ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: listePartenaires.php?error=no_id');
    exit();
}

$partenaire_id = (int)$_GET['id'];

// Connexion BDD
require_once '../../../config/Database.php';
$db = new Database();
$conn = $db->getConnection();

require_once '../../../config/Constraints.php';
$constraints = new Constraints($conn);

// Récupérer le partenaire existant
$stmt = $conn->prepare("SELECT * FROM Partenaire WHERE id_partenaire = ?");
$stmt->execute([$partenaire_id]);
$partenaire = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier si le partenaire existe
if (!$partenaire) {
    header('Location: listePartenaires.php?error=not_found');
    exit();
}

// Variables
$errors = [];
$success = false;

// Données du formulaire (initialiser avec les données existantes)
$formData = [
    'nom' => $partenaire['nom'],
    'type' => $partenaire['type'],
    'montant' => $partenaire['montant'] ?? '',
    'date_contribution' => $partenaire['date_contribution'] ?? date('Y-m-d'),
    'contact_email' => $partenaire['contact_email'] ?? '',
    'contact_telephone' => $partenaire['contact_telephone'] ?? '',
    'description' => $partenaire['description'] ?? '',
    'statut' => $partenaire['statut']
];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validation avec Constraints (avec exclusion d'ID)
    $rules = [
        'nom' => [
            'required' => true,
            'max' => 100,
            'unique_partenaire' => true  // ID sera automatiquement exclu via $_GET['id']
        ],
        'type' => ['required' => true],
        'montant' => ['amount' => ['allow_zero' => true]],
        'date_contribution' => ['date' => true, 'not_future' => true],
        'contact_email' => ['email' => true],
        'contact_telephone' => ['phone' => true],
        'description' => ['max' => 500]
    ];

    // Ajouter l'ID au POST pour l'exclusion
    $_POST['id'] = $partenaire_id;

    $errors = $constraints->validateFormData($_POST, $rules);

    // Si pas d'erreurs, nettoyer et mettre à jour
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
            // Mettre à jour le partenaire
            $sql = "UPDATE Partenaire SET 
                    nom = ?, 
                    type = ?, 
                    montant = ?, 
                    date_contribution = ?, 
                    contact_email = ?, 
                    contact_telephone = ?, 
                    description = ?, 
                    statut = ?
                    WHERE id_partenaire = ?";

            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $formData['nom'],
                $formData['type'],
                $formData['montant'],
                $formData['date_contribution'] ?: null,
                $formData['contact_email'] ?: null,
                $formData['contact_telephone'] ?: null,
                $formData['description'] ?: null,
                $formData['statut'],
                $partenaire_id
            ]);

            $success = true;

            // Recharger les données depuis la BDD
            $stmt = $conn->prepare("SELECT * FROM Partenaire WHERE id_partenaire = ?");
            $stmt->execute([$partenaire_id]);
            $partenaire = $stmt->fetch(PDO::FETCH_ASSOC);
            $formData = [
                'nom' => $partenaire['nom'],
                'type' => $partenaire['type'],
                'montant' => $partenaire['montant'] ?? '',
                'date_contribution' => $partenaire['date_contribution'] ?? date('Y-m-d'),
                'contact_email' => $partenaire['contact_email'] ?? '',
                'contact_telephone' => $partenaire['contact_telephone'] ?? '',
                'description' => $partenaire['description'] ?? '',
                'statut' => $partenaire['statut']
            ];

        } catch (Exception $e) {
            $errors[] = "Erreur lors de la modification : " . $e->getMessage();
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
    <title>Modifier partenaire - FAGE</title>

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
        .info-box {
            background-color: #e3f2fd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #183146;
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
        .preview-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #183146;
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
            <i class="bi bi-pencil-square"></i> Modifier partenaire
        </div>
    </div>
</nav>

<div class="container py-4">
    <div class="form-container">
        <!-- Informations du partenaire -->
        <div class="info-box">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-1">
                        <i class="bi bi-handshake"></i>
                        <?php echo htmlspecialchars($formData['nom']); ?>
                    </h5>
                    <p class="mb-0 text-muted small">
                        ID : #<?php echo $partenaire_id; ?> •
                        Créé le : <?php echo date('d/m/Y', strtotime($partenaire['date_creation'])); ?>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="voirDetailsPartenaire.php?id=<?php echo $partenaire_id; ?>"
                       class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye"></i> Voir fiche
                    </a>
                </div>
            </div>
        </div>

        <!-- Carte principale -->
        <div class="card shadow">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="bi bi-pencil-square"></i> Modification du partenaire
                </h4>
            </div>

            <div class="card-body p-4">
                <!-- Messages -->
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <h5><i class="bi bi-check-circle"></i> Succès !</h5>
                        <p class="mb-0">Les modifications ont été enregistrées avec succès.</p>
                        <div class="mt-2">
                            <a href="voirDetailsPartenaire.php?id=<?php echo $partenaire_id; ?>"
                               class="btn btn-sm btn-success">
                                <i class="bi bi-eye"></i> Voir les modifications
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

                <!-- Aperçu dynamique -->
                <div class="preview-box">
                    <h6><i class="bi bi-eye"></i> Aperçu des modifications :</h6>
                    <div id="partnerPreview">
                        <span class="text-muted">Remplissez le formulaire pour voir l'aperçu...</span>
                    </div>
                </div>

                <!-- Formulaire -->
                <form method="POST" action="">
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
                                      rows="4"
                                      placeholder="Informations complémentaires, domaine d'activité, historique..."
                                      oninput="updatePreview()"><?php echo htmlspecialchars($formData['description']); ?></textarea>
                        </div>
                    </div>

                    <!-- Informations non modifiables -->
                    <div class="alert alert-info mt-4">
                        <h6><i class="bi bi-info-circle"></i> Informations système</h6>
                        <div class="row small">
                            <div class="col-md-6">
                                <strong>ID :</strong> #<?php echo $partenaire_id; ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Date de création :</strong>
                                <?php echo date('d/m/Y H:i', strtotime($partenaire['date_creation'])); ?>
                            </div>
                        </div>
                        <p class="mb-0 mt-2 small">
                            Ces informations ne peuvent pas être modifiées car elles sont gérées automatiquement par le système.
                        </p>
                    </div>

                    <hr class="my-4">

                    <!-- Boutons -->
                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="voirDetailsPartenaire.php?id=<?php echo $partenaire_id; ?>"
                               class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Annuler
                            </a>
                            <a href="listePartenaires.php" class="btn btn-link text-muted">
                                <i class="bi bi-list"></i> Retour à la liste
                            </a>
                        </div>

                        <div>
                            <button type="reset" class="btn btn-outline-warning me-2">
                                <i class="bi bi-arrow-counterclockwise"></i> Réinitialiser
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Aide -->
            <div class="card-footer bg-light">
                <small class="text-muted">
                    <i class="bi bi-exclamation-triangle"></i>
                    Toute modification est immédiatement appliquée.
                    Les changements concernant le statut peuvent affecter les statistiques.
                </small>
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

        // Formater le montant
        const formatMontant = (montantStr) => {
            if (!montantStr) return 'Non spécifié';
            return parseFloat(montantStr).toFixed(2) + ' €';
        };

        // Construire l'aperçu
        let previewHTML = `
            <div class="row">
                <div class="col-md-8">
                    <strong>${nom}</strong>
                    <div class="mt-1">
                        <span class="badge ${type === 'donateur' ? 'bg-primary' : 'bg-warning text-dark'} me-2">
                            ${typeText}
                        </span>
                        <span class="badge bg-${statutColor}">
                            ${statutText}
                        </span>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <strong>${formatMontant(montant)}</strong>
                </div>
            </div>
            <div class="row mt-2 small">
                <div class="col-md-6">
                    <i class="bi bi-calendar"></i> ${formatDate(dateContribution)}
                </div>
                <div class="col-md-6">
                    ${email ? `<i class="bi bi-envelope"></i> ${email}` : ''}
                    ${telephone ? `<br><i class="bi bi-telephone"></i> ${telephone}` : ''}
                </div>
            </div>
        `;

        if (description) {
            previewHTML += `
                <div class="row mt-2">
                    <div class="col-12">
                        <p class="mb-0 small">
                            <strong>Notes :</strong> ${description.substring(0, 100)}${description.length > 100 ? '...' : ''}
                        </p>
                    </div>
                </div>
            `;
        }

        document.getElementById('partnerPreview').innerHTML = previewHTML;
    }

    // Initialisation
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

        // Données originales pour détection de changements
        const originalData = {
            nom: document.getElementById('nom').value,
            type: document.getElementById('type').value,
            statut: document.getElementById('statut').value,
            montant: document.getElementById('montant').value,
            date_contribution: document.getElementById('date_contribution').value,
            contact_email: document.getElementById('contact_email').value,
            contact_telephone: document.getElementById('contact_telephone').value,
            description: document.getElementById('description').value
        };

        // Validation avant soumission
        document.querySelector('form').addEventListener('submit', function(e) {
            const currentData = {
                nom: document.getElementById('nom').value,
                type: document.getElementById('type').value,
                statut: document.getElementById('statut').value,
                montant: document.getElementById('montant').value,
                date_contribution: document.getElementById('date_contribution').value,
                contact_email: document.getElementById('contact_email').value,
                contact_telephone: document.getElementById('contact_telephone').value,
                description: document.getElementById('description').value
            };

            // Vérifier si des changements ont été faits
            let hasChanges = false;
            for (const key in originalData) {
                if (originalData[key] !== currentData[key]) {
                    hasChanges = true;
                    break;
                }
            }

            if (!hasChanges) {
                e.preventDefault();
                return confirm("Aucune modification détectée. Souhaitez-vous quand même continuer ?");
            }

            // Validation de base
            if (!currentData.nom.trim()) {
                e.preventDefault();
                alert('Le nom du partenaire est obligatoire');
                document.getElementById('nom').focus();
                return false;
            }

            if (currentData.contact_email && !validateEmail(currentData.contact_email)) {
                e.preventDefault();
                alert('L\'email n\'est pas valide');
                document.getElementById('contact_email').focus();
                return false;
            }

            if (currentData.montant && parseFloat(currentData.montant) < 0) {
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

        // Réinitialisation intelligente
        document.querySelector('button[type="reset"]').addEventListener('click', function() {
            if (confirm("Réinitialiser tous les champs aux valeurs originales ?")) {
                setTimeout(updatePreview, 100);
                return true;
            } else {
                return false;
            }
        });
    });
</script>
</body>
</html>