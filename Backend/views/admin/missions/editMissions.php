<?php
// Backend/views/admin/missions/editMissions.php

// Protection
require_once '../../../test/session_check.php';

// Vérifier l'ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: calendrier.php?error=no_id');
    exit();
}

$event_id = (int)$_GET['id'];  // Renommer la variable

// Connexion BDD
require_once '../../../config/Database.php';
$db = new Database();
$conn = $db->getConnection();

// Récupérer la mission existante
$stmt = $conn->prepare("SELECT * FROM Evenement WHERE id_evenement = ?");
$stmt->execute([$event_id]);
$mission = $stmt->fetch(PDO::FETCH_ASSOC); // OK de garder $mission même si table evenement

// Vérifier si la mission existe
if (!$mission) {
    header('Location: calendrier.php?error=not_found');
    exit();
}

// Variables
$errors = [];
$success = false;

// Données du formulaire (initialiser avec les données existantes)
$formData = [
    'nom' => $mission['nom'],  // CHANGÉ: 'nom' au lieu de 'titre'
    'type' => $mission['type'] ?? 'formation',
    'details' => $mission['details'] ?? '',
    'lieu' => $mission['lieu'] ?? '',
    'date_debut' => $mission['date_debut'] ?? date('Y-m-d'),
    'date_fin' => $mission['date_fin'] ?? date('Y-m-d'),
    'nb_participants_prevus' => $mission['nb_participants_prevus'] ?? 10,
    'budget' => $mission['budget'] ?? '',
    'statut' => $mission['statut'] ?? 'planifie'
];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et nettoyer
    $formData['nom'] = trim($_POST['nom'] ?? '');
    $formData['type'] = $_POST['type'] ?? 'formation';
    $formData['details'] = trim($_POST['details'] ?? '');
    $formData['lieu'] = trim($_POST['lieu'] ?? '');
    $formData['date_debut'] = $_POST['date_debut'] ?? '';
    $formData['date_fin'] = $_POST['date_fin'] ?? '';
    $formData['nb_participants_prevus'] = (int)($_POST['nb_participants_prevus'] ?? 10);
    $formData['budget'] = $_POST['budget'] ? (float)$_POST['budget'] : null;
    $formData['statut'] = $_POST['statut'] ?? 'planifie';

    // Validation
    if (empty($formData['nom'])) {
        $errors[] = "Le nom de l'événement est obligatoire";
    }

    if (empty($formData['date_debut'])) {
        $errors[] = "La date de début est obligatoire";
    }

    if (!empty($formData['date_debut']) && !empty($formData['date_fin'])) {
        if (strtotime($formData['date_fin']) < strtotime($formData['date_debut'])) {
            $errors[] = "La date de fin ne peut pas être avant la date de début";
        }
    }

    // Si pas d'erreurs, mettre à jour
    if (empty($errors)) {
        try {
            // Mettre à jour la mission
            $sql = "UPDATE Evenement SET 
        nom = ?, 
        type = ?,
        details = ?,
        lieu = ?, 
        date_debut = ?, 
        date_fin = ?, 
        nb_participants_prevus = ?, 
        budget = ?, 
        statut = ?
        WHERE id_evenement = ?";

            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $formData['nom'],  // CHANGÉ
                $formData['type'], // NOUVEAU
                $formData['details'],
                $formData['lieu'],
                $formData['date_debut'],
                $formData['date_fin'],
                $formData['nb_participants_prevus'], // NOUVEAU
                $formData['budget'],
                $formData['statut'], // NOUVEAU
                $event_id
            ]);

            $success = true;

        } catch (Exception $e) {
            $errors[] = "Erreur lors de la modification : " . $e->getMessage();
        }
    }
}
$event_types = [
    'formation' => 'Formation',
    'reunion' => 'Réunion',
    'evenement' => 'Événement public',
    'collecte' => 'Collecte',
    'manifestation' => 'Manifestation',
    'festival' => 'Festival',
    'assemblee' => 'Assemblée générale',
    'autre' => 'Autre'
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier événement - FAGE</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <style>
        .form-container {
            max-width: 800px;
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
        .mission-preview {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #183146;
        }
        .datetime-group {
            background: #e9ecef;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .info-box {
            background-color: #e3f2fd;
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
            <i class="bi bi-pencil-square"></i> Modifier mission
        </div>
    </div>
</nav>

<div class="container py-4">
    <div class="form-container">
        <!-- En-tête -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="bi bi-pencil-square"></i> Modifier mission</h2>
                <p class="text-muted mb-0">
                    Modifiez les informations de la mission #<?php echo $event_id; ?>
                </p>
            </div>
            <div>
                <a href="voirDetailsMissions.php?id=<?php echo $event_id; ?>"
                   class="btn btn-outline-primary me-2">
                    <i class="bi bi-eye"></i> Voir détails
                </a>
                <a href="calendrier.php" class="btn btn-outline-secondary">
                    <i class="bi bi-calendar-event"></i> Retour au calendrier
                </a>
            </div>
        </div>

        <!-- Informations actuelles -->
        <div class="info-box mb-4">
            <h6><i class="bi bi-info-circle"></i> Informations actuelles</h6>
            <div class="row small">
                <div class="col-md-6">
                    <strong>ID :</strong> #<?php echo $event_id; ?>
                </div>
                <div class="col-md-6">
                    <strong>Statut :</strong>
                    <?php
                    // Calculer le statut dynamiquement
                    $now = new DateTime();
                    $date_debut = new DateTime($mission['date_debut'] ?? 'now');
                    $date_fin = new DateTime($mission['date_fin'] ?? 'now');

                    $statut = 'Planifie';
                    if ($date_fin < $now) {
                        $statut = 'Terminé';
                    } elseif ($date_debut <= $now && $date_fin >= $now) {
                        $statut = 'En cours';
                    }

                    $status_colors = [
                        'Planifie' => 'warning',
                        'En cours' => 'success',
                        'Terminé' => 'secondary'
                    ];
                    ?>
                    <span class="badge bg-<?php echo $status_colors[$statut] ?? 'secondary'; ?>">
                            <?php echo $statut; ?>
                        </span>
                </div>
            </div>
        </div>

        <!-- Carte principale -->
        <div class="card shadow">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="bi bi-pencil"></i> Modifier les informations
                </h4>
            </div>

            <div class="card-body p-4">
                <!-- Messages -->
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <h5><i class="bi bi-check-circle"></i> Modifications enregistrées !</h5>
                        <p class="mb-0">
                            La mission a été modifiée avec succès.
                        </p>
                        <div class="mt-3">
                            <a href="voirDetailsMissions.php?id=<?php echo $event_id; ?>"
                               class="btn btn-sm btn-success">
                                <i class="bi bi-eye"></i> Voir les modifications
                            </a>
                            <a href="calendrier.php" class="btn btn-sm btn-primary">
                                <i class="bi bi-calendar-event"></i> Retour au calendrier
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
                <div class="mission-preview">
                    <h6><i class="bi bi-eye"></i> Aperçu des modifications :</h6>
                    <div id="missionPreview">
                        <span class="text-muted">Remplissez le formulaire pour voir l'aperçu...</span>
                    </div>
                </div>

                <!-- Formulaire -->
                <form method="POST" action="" id="missionForm">
                    <div class="row g-3">
                        <!-- Titre (nom) -->
                        <div class="col-12">
                            <label for="nom" class="form-label required">Nom de l'événement</label>
                            <input type="text"
                                   id="nom"
                                   name="nom"
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($formData['nom']); ?>"
                                   required
                                   placeholder="Ex: Formation premiers secours"
                                   oninput="updatePreview()">
                            <div class="form-text">Titre clair et descriptif de l'événement</div>
                        </div>

                        <!-- Type -->
                        <div class="col-md-6">
                            <label for="type" class="form-label">
                                <i class="bi bi-tags"></i> Type d'événement
                            </label>
                            <select id="type" name="type" class="form-select" oninput="updatePreview()">
                                <?php foreach ($event_types as $value => $label): ?>
                                    <option value="<?php echo $value; ?>" <?php echo $formData['type'] == $value ? 'selected' : ''; ?>>
                                        <?php echo $label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Lieu -->
                        <div class="col-md-6">
                            <label for="lieu" class="form-label">
                                <i class="bi bi-geo-alt"></i> Lieu
                            </label>
                            <input type="text"
                                   id="lieu"
                                   name="lieu"
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($formData['lieu']); ?>"
                                   placeholder="Ex: Campus universitaire, Salle A12"
                                   oninput="updatePreview()">
                            <div class="form-text">Lieu précis de l'événement</div>
                        </div>

                        <!-- Budget -->
                        <div class="col-md-6">
                            <label for="budget" class="form-label">
                                <i class="bi bi-cash-coin"></i> Budget (€)
                            </label>
                            <input type="number"
                                   id="budget"
                                   name="budget"
                                   class="form-control"
                                   value="<?php echo $formData['budget']; ?>"
                                   min="0"
                                   step="0.01"
                                   placeholder="Optionnel">
                            <div class="form-text">Budget estimé</div>
                        </div>

                        <!-- Nombre de participants -->
                        <div class="col-md-6">
                            <label for="nb_participants_prevus" class="form-label">
                                <i class="bi bi-people"></i> Nombre de participants prévus
                            </label>
                            <input type="number"
                                   id="nb_participants_prevus"
                                   name="nb_participants_prevus"
                                   class="form-control"
                                   value="<?php echo $formData['nb_participants_prevus']; ?>"
                                   min="1"
                                   required>
                            <div class="form-text">Nombre estimé de participants</div>
                        </div>

                        <!-- Dates -->
                        <div class="col-12">
                            <div class="datetime-group">
                                <h6><i class="bi bi-calendar"></i> Dates</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="date_debut" class="form-label required">Date début</label>
                                        <input type="date"
                                               id="date_debut"
                                               name="date_debut"
                                               class="form-control"
                                               value="<?php echo $formData['date_debut']; ?>"
                                               required
                                               onchange="updateDates()">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="date_fin" class="form-label required">Date fin</label>
                                        <input type="date"
                                               id="date_fin"
                                               name="date_fin"
                                               class="form-control"
                                               value="<?php echo $formData['date_fin']; ?>"
                                               required
                                               onchange="updatePreview()">
                                    </div>
                                </div>
                                <div class="form-text mt-2">
                                    <span id="durationInfo">Durée : 1 jour</span>
                                </div>
                            </div>
                        </div>

                        <!-- Statut -->
                        <div class="col-md-6">
                            <label for="statut" class="form-label">
                                <i class="bi bi-clipboard-check"></i> Statut
                            </label>
                            <select id="statut" name="statut" class="form-select" onchange="updatePreview()">
                                <option value="planifie" <?php echo $formData['statut'] == 'planifie' ? 'selected' : ''; ?>>Planifie</option>
                                <option value="en cours" <?php echo $formData['statut'] == 'en cours' ? 'selected' : ''; ?>>En cours</option>
                                <option value="termine" <?php echo $formData['statut'] == 'termine' ? 'selected' : ''; ?>>Terminé</option>
                                <option value="annule" <?php echo $formData['statut'] == 'annule' ? 'selected' : ''; ?>>Annulé</option>
                            </select>
                        </div>

                        <!-- Description (details) -->
                        <div class="col-12">
                            <label for="details" class="form-label">
                                <i class="bi bi-card-text"></i> Détails
                            </label>
                            <textarea id="details"
                                      name="details"
                                      class="form-control"
                                      rows="4"
                                      placeholder="Description détaillée de l'événement, objectifs, programme..."
                                      oninput="updatePreview()"><?php echo htmlspecialchars($formData['details']); ?></textarea>
                            <div class="form-text">Optionnel - Détails complémentaires</div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Boutons -->
                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="voirDetailsMissions.php?id=<?php echo $event_id; ?>"
                               class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Annuler
                            </a>
                            <button type="reset" class="btn btn-outline-warning ms-2">
                                <i class="bi bi-arrow-counterclockwise"></i> Réinitialiser
                            </button>
                        </div>

                        <div>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle"></i> Enregistrer les modifications
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
                            Les modifications sont immédiatement appliquées.
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
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Aperçu dynamique
    function updatePreview() {
        const nom = document.getElementById('nom').value || '[Nom de l\'événement]';
        const type = document.getElementById('type').options[document.getElementById('type').selectedIndex].text;
        const lieu = document.getElementById('lieu').value || '[Lieu non spécifié]';
        const budget = document.getElementById('budget').value || '0';
        const dateDebut = document.getElementById('date_debut').value;
        const dateFin = document.getElementById('date_fin').value;
        const details = document.getElementById('details').value;
        const statut = document.getElementById('statut').value;
        const participants = document.getElementById('nb_participants_prevus').value || '0';

        // Formater les dates
        const formatDate = (dateStr) => {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            return date.toLocaleDateString('fr-FR', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        };

        // Calculer la durée
        let duration = '1 jour';
        if (dateDebut && dateFin) {
            const start = new Date(dateDebut);
            const end = new Date(dateFin);
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            duration = diffDays + ' jour' + (diffDays > 1 ? 's' : '');
            document.getElementById('durationInfo').textContent = `Durée : ${duration}`;
        }

        // Mapper les statuts aux couleurs
        const statutColors = {
            'planifie': 'warning',
            'en cours': 'success',
            'termine': 'secondary',
            'annule': 'danger'
        };

        // Construire l'aperçu
        let previewHTML = `
            <div class="row">
                <div class="col-md-8">
                    <h5 class="mb-1">${nom}</h5>
                    <p class="mb-1">
                        <span class="badge bg-${statutColors[statut] || 'secondary'} text-capitalize">
                            ${statut}
                        </span>
                        <span class="badge bg-info ms-2">
                            ${type}
                        </span>
                    </p>
                    <p class="mb-1 small">
                        <i class="bi bi-geo-alt"></i> ${lieu}
                    </p>
                    <p class="mb-1 small">
                        <i class="bi bi-calendar"></i> ${formatDate(dateDebut)}
                        ${dateDebut !== dateFin ? ` au ${formatDate(dateFin)}` : ''}
                    </p>
                    <p class="mb-1 small">
                        <i class="bi bi-people"></i> ${participants} participants prévus
                    </p>
                    ${budget > 0 ? `<p class="mb-1 small"><i class="bi bi-cash-coin"></i> Budget : ${budget} €</p>` : ''}
                </div>
                <div class="col-md-4 text-end">
                    <div class="text-muted small">
                        <div><i class="bi bi-hourglass"></i> ${duration}</div>
                        <div>ID : #<?php echo $event_id; ?></div>
                    </div>
                </div>
            </div>
        `;

        if (details) {
            previewHTML += `
                <div class="row mt-2">
                    <div class="col-12">
                        <p class="mb-0 small text-muted">
                            <strong>Détails :</strong> ${details.substring(0, 100)}${details.length > 100 ? '...' : ''}
                        </p>
                    </div>
                </div>
            `;
        }

        document.getElementById('missionPreview').innerHTML = previewHTML;
    }

    // Gestion des dates
    function updateDates() {
        const dateDebut = document.getElementById('date_debut').value;
        const dateFin = document.getElementById('date_fin').value;

        // Si date de fin vide ou antérieure à date début, mettre à jour
        if (!dateFin || new Date(dateFin) < new Date(dateDebut)) {
            document.getElementById('date_fin').value = dateDebut;
        }

        updatePreview();
    }

    // Initialisation
    document.addEventListener('DOMContentLoaded', function() {
        // Mettre à jour l'aperçu initial
        updatePreview();

        // Focus sur le nom
        document.getElementById('nom').focus();

        // Validation avant soumission
        const originalData = {
            nom: document.getElementById('nom').value,
            details: document.getElementById('details').value,
            date_debut: document.getElementById('date_debut').value,
            date_fin: document.getElementById('date_fin').value,
            lieu: document.getElementById('lieu').value,
            budget: document.getElementById('budget').value,
            type: document.getElementById('type').value,
            statut: document.getElementById('statut').value,
            nb_participants_prevus: document.getElementById('nb_participants_prevus').value
        };

        document.getElementById('missionForm').addEventListener('submit', function(e) {
            const currentData = {
                nom: document.getElementById('nom').value,
                details: document.getElementById('details').value,
                date_debut: document.getElementById('date_debut').value,
                date_fin: document.getElementById('date_fin').value,
                lieu: document.getElementById('lieu').value,
                budget: document.getElementById('budget').value,
                type: document.getElementById('type').value,
                statut: document.getElementById('statut').value,
                nb_participants_prevus: document.getElementById('nb_participants_prevus').value
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
                alert('Le nom de l\'événement est obligatoire');
                document.getElementById('nom').focus();
                return false;
            }

            if (!currentData.date_debut || !currentData.date_fin) {
                e.preventDefault();
                alert('Les dates sont obligatoires');
                return false;
            }

            if (new Date(currentData.date_fin) < new Date(currentData.date_debut)) {
                e.preventDefault();
                alert('La date de fin ne peut pas être avant la date de début');
                return false;
            }

            if (!currentData.nb_participants_prevus || currentData.nb_participants_prevus < 1) {
                e.preventDefault();
                alert('Le nombre de participants doit être au moins de 1');
                document.getElementById('nb_participants_prevus').focus();
                return false;
            }

            return true;
        });
    });
</script>
</body>
</html>