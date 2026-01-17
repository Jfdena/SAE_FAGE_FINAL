<?php
// Backend/views/admin/missions/editMissions.php

// Protection
require_once '../../../session_check.php';

// Vérifier l'ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: calendrier.php?error=no_id');
    exit();
}

$event_id = (int)$_GET['id'];

// Connexion BDD
require_once '../../../config/Database.php';
$db = new Database();
$conn = $db->getConnection();

// Récupérer l'événement existant
$stmt = $conn->prepare("SELECT * FROM Evenement WHERE id_evenement = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc(); // Renommé en $event pour cohérence

// Vérifier si l'événement existe
if (!$event) {
    header('Location: calendrier.php?error=not_found');
    exit();
}

// Variables
$errors = [];
$success = false;

// Extraire date et heure séparément
$date_debut = date('Y-m-d', strtotime($event['date_debut']));
$date_fin = date('Y-m-d', strtotime($event['date_fin']));
$heure_debut = date('H:i', strtotime($event['date_debut']));
$heure_fin = date('H:i', strtotime($event['date_fin']));

// Données du formulaire
$formData = [
        'nom' => $event['nom'],
        'type' => $event['type'] ?? 'formation',
        'description' => $event['details'] ?? '',
        'lieu' => $event['lieu'] ?? '',
        'date_debut' => $date_debut,
        'date_fin' => $date_fin,
        'heure_debut' => $heure_debut,
        'heure_fin' => $heure_fin,
        'nb_participants_prevus' => $event['nb_participants_prevus'] ?? 10,
        'budget' => $event['budget'] ?? '',
        'statut' => $event['statut'] ?? 'planifié'
];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et nettoyer
    $formData['nom'] = trim($_POST['nom'] ?? '');
    $formData['type'] = $_POST['type'] ?? 'formation';
    $formData['description'] = trim($_POST['description'] ?? '');
    $formData['lieu'] = trim($_POST['lieu'] ?? '');
    $formData['date_debut'] = $_POST['date_debut'] ?? '';
    $formData['date_fin'] = $_POST['date_fin'] ?? '';
    $formData['heure_debut'] = $_POST['heure_debut'] ?? '09:00';
    $formData['heure_fin'] = $_POST['heure_fin'] ?? '17:00';
    $formData['nb_participants_prevus'] = (int)($_POST['nb_participants_prevus'] ?? 10);
    $formData['budget'] = !empty($_POST['budget']) ? (float)$_POST['budget'] : null;
    $formData['statut'] = $_POST['statut'] ?? 'planifié';

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
            // Combiner date et heure
            $datetime_debut = $formData['date_debut'] . ' ' . $formData['heure_debut'] . ':00';
            $datetime_fin = $formData['date_fin'] . ' ' . $formData['heure_fin'] . ':00';

            // Mettre à jour l'événement
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
            $stmt->bind_param("sssssiidsi",
                    $formData['nom'],
                    $formData['type'],
                    $formData['description'],
                    $formData['lieu'],
                    $datetime_debut,
                    $datetime_fin,
                    $formData['nb_participants_prevus'],
                    $formData['budget'],
                    $formData['statut'],
                    $event_id
            );

            if ($stmt->execute()) {
                $success = true;
            } else {
                $errors[] = "Erreur lors de la modification : " . $stmt->error;
            }

            $stmt->close();

        } catch (Exception $e) {
            $errors[] = "Erreur lors de la modification : " . $e->getMessage();
        }
    }
}

// Types d'événements
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

// Récupérer les statuts disponibles depuis la base
$statut_types = ['planifié', 'en cours', 'termine', 'annule'];
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
        .event-preview {
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
            <i class="bi bi-pencil-square"></i> Modifier événement
        </div>
    </div>
</nav>

<div class="container py-4">
    <div class="form-container">
        <!-- En-tête -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="bi bi-pencil-square"></i> Modifier événement</h2>
                <p class="text-muted mb-0">
                    Modifiez les informations de l'événement #<?php echo $event_id; ?>
                </p>
            </div>
            <div>
                <a href="voirDetailsMissions.php?id=<?php echo $event_id; ?>"
                   class="btn btn-outline-primary me-2">
                    <i class="bi bi-eye"></i> Voir détails
                </a>
                <a href="calendrier.php" class="btn btn-outline-secondary">
                    <i class="bi bi-calendar-event"></i> Retour calendrier
                </a>
            </div>
        </div>

        <!-- Messages -->
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i>
                <strong>Succès !</strong> L'événement a été modifié avec succès.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                <div class="mt-2">
                    <a href="voirDetailsMissions.php?id=<?php echo $event_id; ?>" class="btn btn-sm btn-success">
                        Voir les détails
                    </a>
                    <a href="calendrier.php" class="btn btn-sm btn-outline-success">
                        Retour au calendrier
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <h5><i class="bi bi-exclamation-triangle"></i> Erreurs</h5>
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Formulaire -->
        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-calendar-plus"></i> Informations de l'événement</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="" id="editForm">
                    <!-- Informations générales -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <label for="nom" class="form-label required">Nom de l'événement</label>
                            <input type="text"
                                   id="nom"
                                   name="nom"
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($formData['nom']); ?>"
                                   required>
                        </div>
                        <div class="col-md-4">
                            <label for="type" class="form-label required">Type d'événement</label>
                            <select id="type" name="type" class="form-select" required>
                                <?php foreach ($event_types as $value => $label): ?>
                                    <option value="<?php echo $value; ?>"
                                            <?php echo ($formData['type'] === $value) ? 'selected' : ''; ?>>
                                        <?php echo $label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description"
                                      name="description"
                                      class="form-control"
                                      rows="3"><?php echo htmlspecialchars($formData['description']); ?></textarea>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="lieu" class="form-label">Lieu</label>
                            <input type="text"
                                   id="lieu"
                                   name="lieu"
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($formData['lieu']); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="nb_participants_prevus" class="form-label">Participants prévus</label>
                            <input type="number"
                                   id="nb_participants_prevus"
                                   name="nb_participants_prevus"
                                   class="form-control"
                                   min="1"
                                   value="<?php echo $formData['nb_participants_prevus']; ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="budget" class="form-label">Budget (€)</label>
                            <input type="number"
                                   id="budget"
                                   name="budget"
                                   class="form-control"
                                   step="0.01"
                                   min="0"
                                   value="<?php echo $formData['budget']; ?>">
                        </div>
                    </div>

                    <!-- Dates et heures -->
                    <div class="datetime-group">
                        <h6 class="mb-3"><i class="bi bi-clock"></i> Dates et heures</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="date_debut" class="form-label required">Date de début</label>
                                <input type="date"
                                       id="date_debut"
                                       name="date_debut"
                                       class="form-control"
                                       value="<?php echo $formData['date_debut']; ?>"
                                       required>
                            </div>
                            <div class="col-md-6">
                                <label for="heure_debut" class="form-label required">Heure de début</label>
                                <input type="time"
                                       id="heure_debut"
                                       name="heure_debut"
                                       class="form-control"
                                       value="<?php echo $formData['heure_debut']; ?>"
                                       required>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label for="date_fin" class="form-label">Date de fin</label>
                                <input type="date"
                                       id="date_fin"
                                       name="date_fin"
                                       class="form-control"
                                       value="<?php echo $formData['date_fin']; ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="heure_fin" class="form-label">Heure de fin</label>
                                <input type="time"
                                       id="heure_fin"
                                       name="heure_fin"
                                       class="form-control"
                                       value="<?php echo $formData['heure_fin']; ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Statut -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label for="statut" class="form-label">Statut</label>
                            <select id="statut" name="statut" class="form-select">
                                <?php foreach ($statut_types as $statut): ?>
                                    <option value="<?php echo $statut; ?>"
                                            <?php echo ($formData['statut'] === $statut) ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($statut); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="voirDetailsMissions.php?id=<?php echo $event_id; ?>"
                               class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Annuler
                            </a>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Actions dangereuses -->
        <div class="card border-danger mt-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Zone de danger</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    Ces actions sont irréversibles. Soyez certain de ce que vous faites.
                </p>
                <div class="d-flex justify-content-end">
                    <a href="deleteMissions.php?id=<?php echo $event_id; ?>"
                       class="btn btn-outline-danger"
                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')">
                        <i class="bi bi-trash"></i> Supprimer cet événement
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Validation des dates
        const dateDebut = document.getElementById('date_debut');
        const dateFin = document.getElementById('date_fin');

        function validateDates() {
            if (dateDebut.value && dateFin.value) {
                if (new Date(dateFin.value) < new Date(dateDebut.value)) {
                    alert('La date de fin ne peut pas être avant la date de début');
                    dateFin.value = dateDebut.value;
                    return false;
                }
            }
            return true;
        }

        dateDebut.addEventListener('change', validateDates);
        dateFin.addEventListener('change', validateDates);

        // Validation du formulaire
        document.getElementById('editForm').addEventListener('submit', function(e) {
            if (!validateDates()) {
                e.preventDefault();
                return false;
            }

            // Validation du nom
            const nom = document.getElementById('nom').value.trim();
            if (nom === '') {
                alert('Le nom de l\'événement est obligatoire');
                e.preventDefault();
                return false;
            }

            return true;
        });
    });
</script>
</body>
</html>