<?php
// Backend/views/admin/adherents/editFiche.php

// Protection
require_once '../../../test/session_check.php';

// Vérifier l'ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: listeAdherent.php?error=no_id');
    exit();
}

$benevole_id = (int)$_GET['id'];

// Connexion BDD
require_once '../../../config/Database.php';
$db = new Database();
$conn = $db->getConnection();
require_once '../../../config/Constraints.php';
$constraints = new Constraints($conn);

// Récupérer le bénévole existant
$stmt = $conn->prepare("SELECT * FROM benevole WHERE id_benevole = ?");
$stmt->execute([$benevole_id]);
$benevole = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier si le bénévole existe
if (!$benevole) {
    header('Location: listeAdherent.php?error=not_found');
    exit();
}

// Variables
$errors = [];
$success = false;
$formData = $benevole; // Pré-remplir avec les données existantes

// Traitement du formulaire
// Remplacer la validation par :
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Ajouter l'ID pour l'exclusion
    $_POST['id'] = $benevole_id;

    // Validation avec Constraints
    $rules = [
        'nom' => ['required' => true, 'name' => true, 'max' => 50],
        'prenom' => ['required' => true, 'name' => true, 'max' => 50],
        'email' => ['email' => true, 'unique' => ['benevole', 'email', 'id']],
        'telephone' => ['phone' => true],
        'date_naissance' => ['date' => true, 'birth_date' => true],
        'date_inscription' => ['date' => true, 'not_future' => true]
    ];

    $errors = $constraints->validateFormData($_POST, $rules);

    // Si pas d'erreurs, nettoyer et mettre à jour
    if (empty($errors)) {
        // Nettoyer et formater les données
        $nom = $constraints->sanitize($_POST['nom']);
        $prenom = $constraints->sanitize($_POST['prenom']);
        $email = $constraints->sanitize($_POST['email']);
        $telephone = $constraints->sanitize($_POST['telephone']);
        $date_naissance = $constraints->formatDateForDB($_POST['date_naissance']);
        $date_inscription = $constraints->formatDateForDB($_POST['date_inscription']);
        $statut = $_POST['statut'] ?? 'actif';

        try {
            $sql = "UPDATE Benevole SET 
                    nom = ?, 
                    prenom = ?, 
                    email = ?, 
                    telephone = ?, 
                    date_naissance = ?, 
                    date_inscription = ?, 
                    statut = ?
                    WHERE id_benevole = ?";

            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $nom,
                $prenom,
                $email ?: null,
                $telephone ?: null,
                $date_naissance ?: null,
                $date_inscription ?: null,
                $statut,
                $benevole_id
            ]);

            $success = true;

            // Recharger les données
            $stmt = $conn->prepare("SELECT * FROM Benevole WHERE id_benevole = ?");
            $stmt->execute([$benevole_id]);
            $benevole = $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            $errors[] = "Erreur lors de la modification : " . $e->getMessage();
            error_log("Erreur SQL: " . $e->getMessage());
        }
    }
}

// Formater la date pour l'input HTML
$date_naissance_form = !empty($formData['date_naissance'])
    ? date('Y-m-d', strtotime($formData['date_naissance']))
    : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier bénévole - FAGE</title>

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
            background: linear-gradient(135deg, #f0ad4e 0%, #eea236 100%);
            color: white;
        }
        .info-box {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #f0ad4e;
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
            <i class="bi bi-pencil-square"></i> Modifier bénévole
        </div>
    </div>
</nav>

<div class="container py-4">
    <div class="form-container">
        <!-- Informations du bénévole -->
        <div class="info-box">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-1">
                        <i class="bi bi-person"></i>
                        <?php echo htmlspecialchars($formData['prenom'] . ' ' . $formData['nom']); ?>
                    </h5>
                    <p class="mb-0 text-muted small">
                        ID : #<?php echo $benevole_id; ?> •
                        Inscrit le : <?php echo date('d/m/Y', strtotime($formData['date_inscription'])); ?>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="voirDetails.php?id=<?php echo $benevole_id; ?>"
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
                    <i class="bi bi-pencil-square"></i> Modification du bénévole
                </h4>
            </div>

            <div class="card-body p-4">
                <!-- Messages -->
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <h5><i class="bi bi-check-circle"></i> Succès !</h5>
                        <p class="mb-0">Les modifications ont été enregistrées avec succès.</p>
                        <div class="mt-2">
                            <a href="voirDetails.php?id=<?php echo $benevole_id; ?>"
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
                                   value="<?php echo htmlspecialchars($date_naissance_form); ?>"
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

                    <!-- Informations non modifiables -->
                    <div class="alert alert-info mt-4">
                        <h6><i class="bi bi-info-circle"></i> Informations système</h6>
                        <div class="row small">
                            <div class="col-md-6">
                                <strong>ID :</strong> #<?php echo $benevole_id; ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Date d'inscription :</strong>
                                <?php echo date('d/m/Y', strtotime($formData['date_inscription'])); ?>
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
                            <a href="voirDetails.php?id=<?php echo $benevole_id; ?>"
                               class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Annuler
                            </a>
                            <a href="listeAdherent.php" class="btn btn-link text-muted">
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
                    Les changements concernant le statut peuvent affecter la participation aux événements.
                </small>
            </div>
        </div>

        <!-- Historique des modifications (à implémenter plus tard) -->
        <div class="card mt-3 border-warning">
            <div class="card-body">
                <h6><i class="bi bi-clock-history"></i> Historique des modifications</h6>
                <p class="small text-muted mb-0">
                    <i>Fonctionnalité à venir : suivi des modifications apportées à cette fiche.</i>
                </p>
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

        // Calcul automatique de l'âge max
        const today = new Date();
        const maxDate = new Date(today.getFullYear() - 16, today.getMonth(), today.getDate());
        document.getElementById('date_naissance').max = maxDate.toISOString().split('T')[0];

        // Afficher l'âge si date de naissance
        const dateInput = document.getElementById('date_naissance');
        if (dateInput.value) {
            const birthDate = new Date(dateInput.value);
            let age = today.getFullYear() - birthDate.getFullYear();
            const m = today.getMonth() - birthDate.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            console.log('Âge actuel :', age, 'ans');
        }

        // Vérification avant soumission
        const originalData = {
            nom: document.getElementById('nom').value,
            prenom: document.getElementById('prenom').value,
            email: document.getElementById('email').value,
            telephone: document.getElementById('telephone').value,
            date_naissance: document.getElementById('date_naissance').value,
            statut: document.getElementById('statut').value
        };

        document.querySelector('form').addEventListener('submit', function(e) {
            const currentData = {
                nom: document.getElementById('nom').value,
                prenom: document.getElementById('prenom').value,
                email: document.getElementById('email').value,
                telephone: document.getElementById('telephone').value,
                date_naissance: document.getElementById('date_naissance').value,
                statut: document.getElementById('statut').value
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
            if (!currentData.nom.trim() || !currentData.prenom.trim()) {
                e.preventDefault();
                alert('Veuillez remplir les champs obligatoires (Nom et Prénom)');
                return false;
            }

            return true;
        });
    });

    // Réinitialisation intelligente
    document.querySelector('button[type="reset"]').addEventListener('click', function() {
        return confirm("Réinitialiser tous les champs aux valeurs originales ?");
    });
</script>
</body>
</html>