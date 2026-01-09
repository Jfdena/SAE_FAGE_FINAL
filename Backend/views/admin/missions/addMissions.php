<?php
# ⭐ PRIORITAIRE - Création
// Backend/views/admin/missions/addMissions.php

// Protection
require_once '../../../session_check.php';

// Connexion BDD
require_once '../../../config/Database.php';
$db = new Database();
try {
    $conn = $db->getConnection();
} catch (Exception $e) {

}

// Variables
$errors = [];
$success = false;
$new_event_id = null;

// Données par défaut
$formData = [
    'nom' => '',
    'type' => 'formation',
    'lieu' => '',
    'date_debut' => $_GET['date'] ?? date('Y-m-d'),
    'date_fin' => $_GET['date'] ?? date('Y-m-d'),
    'heure_debut' => '09:00',
    'heure_fin' => '17:00',
    'nb_participants_prevus' => 10,
    'budget' => '',
    'statut' => 'planifié',
    'description' => ''
];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et nettoyer
    $formData['nom'] = trim($_POST['nom'] ?? '');
    $formData['type'] = $_POST['type'] ?? 'formation';
    $formData['lieu'] = trim($_POST['lieu'] ?? '');
    $formData['date_debut'] = $_POST['date_debut'] ?? date('Y-m-d');
    $formData['date_fin'] = $_POST['date_fin'] ?? date('Y-m-d');
    $formData['heure_debut'] = $_POST['heure_debut'] ?? '09:00';
    $formData['heure_fin'] = $_POST['heure_fin'] ?? '17:00';
    $formData['nb_participants_prevus'] = (int)($_POST['nb_participants_prevus'] ?? 10);
    $formData['budget'] = $_POST['budget'] ? (float)$_POST['budget'] : null;
    $formData['statut'] = $_POST['statut'] ?? 'planifié';
    $formData['description'] = trim($_POST['description'] ?? '');

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

    if ($formData['nb_participants_prevus'] < 1) {
        $errors[] = "Le nombre de participants doit être au moins 1";
    }

    // Si pas d'erreurs, insérer
    if (empty($errors)) {
        try {
            // Combiner date et heure
            $datetime_debut = $formData['date_debut'] . ' ' . $formData['heure_debut'] . ':00';
            $datetime_fin = $formData['date_fin'] . ' ' . $formData['heure_fin'] . ':00';

            // Insérer l'événement
            $sql = "INSERT INTO Evenement (nom, type, lieu, date_debut, date_fin, 
                    nb_participants_prevus, budget, statut) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $formData['nom'],
                $formData['type'],
                $formData['lieu'],
                $datetime_debut,
                $datetime_fin,
                $formData['nb_participants_prevus'],
                $formData['budget'],
                $formData['statut']
            ]);

            $new_event_id = $conn->lastInsertId();
            $success = true;

            // Réinitialiser le formulaire après succès
            $formData = [
                'nom' => '',
                'type' => 'formation',
                'lieu' => '',
                'date_debut' => date('Y-m-d'),
                'date_fin' => date('Y-m-d'),
                'heure_debut' => '09:00',
                'heure_fin' => '17:00',
                'nb_participants_prevus' => 10,
                'budget' => '',
                'statut' => 'planifié',
                'description' => ''
            ];

        } catch (Exception $e) {
            $errors[] = "Erreur lors de la création : " . $e->getMessage();
        }
    }
}

// Types d'événements prédéfinis
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
    <title>Nouvel événement - FAGE</title>

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
                <i class="bi bi-calendar-plus"></i> Nouvel événement
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="form-container">
            <!-- En-tête -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="bi bi-calendar-plus"></i> Créer un événement</h2>
                    <p class="text-muted mb-0">
                        Planifiez un nouvel événement ou mission pour la FAGE
                    </p>
                </div>
                <div>
                    <a href="calendrier.php" class="btn btn-outline-primary">
                        <i class="bi bi-calendar-event"></i> Retour au calendrier
                    </a>
                </div>
            </div>

            <!-- Carte principale -->
            <div class="card shadow">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-pencil"></i> Informations de l'événement
                    </h4>
                </div>

                <div class="card-body p-4">
                    <!-- Messages -->
                    <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <h5><i class="bi bi-check-circle"></i> Événement créé !</h5>
                        <p class="mb-0">
                            L'événement a été créé avec succès.
                            <?php if ($new_event_id): ?>
                            <br><small>ID : #<?php echo $new_event_id; ?></small>
                            <?php endif; ?>
                        </p>
                        <div class="mt-3">
                            <a href="voirDetailsMissions.php?id=<?php echo $new_event_id; ?>"
                               class="btn btn-sm btn-success">
                                <i class="bi bi-eye"></i> Voir l'événement
                            </a>
                            <a href="addMissions.php" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-plus-circle"></i> Créer un autre
                            </a>
                            <a href="calendrier.php" class="btn btn-sm btn-primary">
                                <i class="bi bi-calendar-event"></i> Voir le calendrier
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
                    <div class="event-preview">
                        <h6><i class="bi bi-eye"></i> Aperçu :</h6>
                        <div id="eventPreview">
                            <span class="text-muted">Remplissez le formulaire pour voir l'aperçu...</span>
                        </div>
                    </div>

                    <!-- Formulaire -->
                    <form method="POST" action="" id="eventForm">
                        <div class="row g-3">
                            <!-- Nom -->
                            <div class="col-md-8">
                                <label for="nom" class="form-label required">Nom de l'événement</label>
                                <input type="text"
                                       id="nom"
                                       name="nom"
                                       class="form-control"
                                       value="<?php echo htmlspecialchars($formData['nom']); ?>"
                                       required
                                       placeholder="Ex: Formation premiers secours"
                                       oninput="updatePreview()">
                                <div class="form-text">Nom clair et descriptif de l'événement</div>
                            </div>

                            <!-- Type -->
                            <div class="col-md-4">
                                <label for="type" class="form-label">Type</label>
                                <select id="type" name="type" class="form-select" onchange="updatePreview()">
                                    <?php foreach ($event_types as $value => $label): ?>
                                    <option value="<?php echo $value; ?>"
                                        <?php echo $formData['type'] === $value ? 'selected' : ''; ?>>
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

                            <!-- Participants -->
                            <div class="col-md-3">
                                <label for="nb_participants_prevus" class="form-label required">
                                    <i class="bi bi-people"></i> Participants
                                </label>
                                <input type="number"
                                       id="nb_participants_prevus"
                                       name="nb_participants_prevus"
                                       class="form-control"
                                       value="<?php echo $formData['nb_participants_prevus']; ?>"
                                       min="1"
                                       max="1000"
                                       required
                                       oninput="updatePreview()">
                                <div class="form-text">Nombre prévu</div>
                            </div>

                            <!-- Budget -->
                            <div class="col-md-3">
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

                            <!-- Dates et heures -->
                            <div class="col-12">
                                <div class="datetime-group">
                                    <h6><i class="bi bi-clock"></i> Dates et horaires</h6>
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label for="date_debut" class="form-label required">Date début</label>
                                            <input type="date"
                                                   id="date_debut"
                                                   name="date_debut"
                                                   class="form-control"
                                                   value="<?php echo $formData['date_debut']; ?>"
                                                   required
                                                   onchange="updateDates()">
                                        </div>
                                        <div class="col-md-2">
                                            <label for="heure_debut" class="form-label">Heure début</label>
                                            <input type="time"
                                                   id="heure_debut"
                                                   name="heure_debut"
                                                   class="form-control"
                                                   value="<?php echo $formData['heure_debut']; ?>"
                                                   onchange="updatePreview()">
                                        </div>

                                        <div class="col-md-1 text-center pt-4">
                                            <i class="bi bi-arrow-right" style="font-size: 1.5rem;"></i>
                                        </div>

                                        <div class="col-md-3">
                                            <label for="date_fin" class="form-label required">Date fin</label>
                                            <input type="date"
                                                   id="date_fin"
                                                   name="date_fin"
                                                   class="form-control"
                                                   value="<?php echo $formData['date_fin']; ?>"
                                                   required
                                                   onchange="updatePreview()">
                                        </div>
                                        <div class="col-md-2">
                                            <label for="heure_fin" class="form-label">Heure fin</label>
                                            <input type="time"
                                                   id="heure_fin"
                                                   name="heure_fin"
                                                   class="form-control"
                                                   value="<?php echo $formData['heure_fin']; ?>"
                                                   onchange="updatePreview()">
                                        </div>

                                        <div class="col-md-1">
                                            <div class="form-check pt-4">
                                                <input class="form-check-input"
                                                       type="checkbox"
                                                       id="same_date"
                                                       onchange="toggleSameDate()">
                                                <label class="form-check-label small" for="same_date">
                                                    Même jour
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-text mt-2">
                                        <span id="durationInfo">Durée : 1 jour</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Statut -->
                            <div class="col-md-4">
                                <label for="statut" class="form-label">
                                    <i class="bi bi-toggle-on"></i> Statut
                                </label>
                                <select id="statut" name="statut" class="form-select" onchange="updatePreview()">
                                    <option value="planifié" <?php echo $formData['statut'] == 'planifié' ? 'selected' : ''; ?>>
                                        Planifié
                                    </option>
                                    <option value="en cours" <?php echo $formData['statut'] == 'en cours' ? 'selected' : ''; ?>>
                                        En cours
                                    </option>
                                    <option value="termine" <?php echo $formData['statut'] == 'termine' ? 'selected' : ''; ?>>
                                        Terminé
                                    </option>
                                    <option value="annule" <?php echo $formData['statut'] == 'annule' ? 'selected' : ''; ?>>
                                        Annulé
                                    </option>
                                </select>
                            </div>

                            <!-- Description -->
                            <div class="col-12">
                                <label for="description" class="form-label">
                                    <i class="bi bi-card-text"></i> Description
                                </label>
                                <textarea id="description"
                                          name="description"
                                          class="form-control"
                                          rows="3"
                                          placeholder="Description détaillée de l'événement, objectifs, programme..."
                                          oninput="updatePreview()"><?php echo htmlspecialchars($formData['description']); ?></textarea>
                                <div class="form-text">Optionnel - Détails complémentaires</div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Boutons -->
                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="calendrier.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> Annuler
                                </a>
                                <button type="reset" class="btn btn-outline-warning ms-2">
                                    <i class="bi bi-eraser"></i> Réinitialiser
                                </button>
                            </div>

                            <div>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-check-circle"></i> Créer l'événement
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
                                L'événement apparaîtra immédiatement dans le calendrier.
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
                    <h6><i class="bi bi-lightbulb"></i> Conseils pour un bon événement :</h6>
                    <ul class="small mb-0">
                        <li><strong>Nom clair</strong> : Ex: "Formation premiers secours" plutôt que "Formation"</li>
                        <li><strong>Lieu précis</strong> : Salle, adresse, point de rendez-vous</li>
                        <li><strong>Dates réalistes</strong> : Vérifiez les disponibilités du lieu</li>
                        <li><strong>Budget estimé</strong> : Aide à la planification financière</li>
                        <li><strong>Participants</strong> : Estimez selon la capacité du lieu</li>
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
        const nom = document.getElementById('nom').value || '[Nom de l\'événement]';
        const type = document.getElementById('type').options[document.getElementById('type').selectedIndex].text;
        const lieu = document.getElementById('lieu').value || '[Lieu non spécifié]';
        const participants = document.getElementById('nb_participants_prevus').value || '0';
        const dateDebut = document.getElementById('date_debut').value;
        const dateFin = document.getElementById('date_fin').value;
        const heureDebut = document.getElementById('heure_debut').value || '09:00';
        const heureFin = document.getElementById('heure_fin').value || '17:00';
        const statut = document.getElementById('statut').options[document.getElementById('statut').selectedIndex].text;
        const description = document.getElementById('description').value;

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

        // Construire l'aperçu
        let previewHTML = `
            <div class="row">
                <div class="col-md-8">
                    <h5 class="mb-1">${nom}</h5>
                    <p class="mb-1">
                        <span class="badge bg-secondary">${type}</span>
                        <span class="badge bg-${getStatusColor(statut)} ms-1">${statut}</span>
                    </p>
                    <p class="mb-1 small">
                        <i class="bi bi-geo-alt"></i> ${lieu}
                    </p>
                    <p class="mb-1 small">
                        <i class="bi bi-calendar"></i> ${formatDate(dateDebut)}
                        ${dateDebut !== dateFin ? ` au ${formatDate(dateFin)}` : ''}
                    </p>
                    <p class="mb-1 small">
                        <i class="bi bi-clock"></i> ${heureDebut} - ${heureFin}
                        <span class="ms-2"><i class="bi bi-people"></i> ${participants} participants</span>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="text-muted small">
                        <div><i class="bi bi-hourglass"></i> ${duration}</div>
                    </div>
                </div>
            </div>
        `;

        if (description) {
            previewHTML += `
                <div class="row mt-2">
                    <div class="col-12">
                        <p class="mb-0 small text-muted">
                            <strong>Description :</strong> ${description.substring(0, 100)}${description.length > 100 ? '...' : ''}
                        </p>
                    </div>
                </div>
            `;
        }

        document.getElementById('eventPreview').innerHTML = previewHTML;
    }

    function getStatusColor(statut) {
        const colors = {
            'Planifie': 'warning',
            'En cours': 'success',
            'Termine': 'secondary',
            'Annule': 'danger'
        };
        return colors[statut] || 'secondary';
    }

    // Gestion des dates
    function updateDates() {
        const dateDebut = document.getElementById('date_debut').value;
        const dateFin = document.getElementById('date_fin');
        const sameDateCheckbox = document.getElementById('same_date');

        // Si date de fin vide ou antérieure à date début, mettre à jour
        if (!dateFin.value || new Date(dateFin.value) < new Date(dateDebut)) {
            dateFin.value = dateDebut;
        }

        updatePreview();
    }

    function toggleSameDate() {
        const sameDateCheckbox = document.getElementById('same_date');
        const dateDebut = document.getElementById('date_debut');
        const dateFin = document.getElementById('date_fin');

        if (sameDateCheckbox.checked) {
            dateFin.value = dateDebut.value;
            dateFin.disabled = true;
        } else {
            dateFin.disabled = false;
        }

        updatePreview();
    }

    // Validation du formulaire
    document.getElementById('eventForm').addEventListener('submit', function(e) {
        const nom = document.getElementById('nom').value.trim();
        const dateDebut = document.getElementById('date_debut').value;
        const dateFin = document.getElementById('date_fin').value;

        // Validation basique
        if (!nom) {
            e.preventDefault();
            alert('Le nom de l\'événement est obligatoire');
            document.getElementById('nom').focus();
            return false;
        }

        if (!dateDebut || !dateFin) {
            e.preventDefault();
            alert('Les dates sont obligatoires');
            return false;
        }

        if (new Date(dateFin) < new Date(dateDebut)) {
            e.preventDefault();
            alert('La date de fin ne peut pas être avant la date de début');
            return false;
        }

        // Calcul de la durée pour avertissement
        const start = new Date(dateDebut);
        const end = new Date(dateFin);
        const diffDays = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;

        if (diffDays > 30) {
            if (!confirm(`Attention : Cet événement dure ${diffDays} jours. Confirmez-vous cette durée ?`)) {
                e.preventDefault();
                return false;
            }
        }

        return true;
    });

    // Initialisation
    document.addEventListener('DOMContentLoaded', function() {
        // Mettre à jour l'aperçu initial
        updatePreview();

        // Focus sur le nom
        document.getElementById('nom').focus();

        // Vérifier si c'est le même jour
        const dateDebut = document.getElementById('date_debut').value;
        const dateFin = document.getElementById('date_fin').value;
        const sameDateCheckbox = document.getElementById('same_date');

        if (dateDebut === dateFin) {
            sameDateCheckbox.checked = true;
            document.getElementById('date_fin').disabled = true;
        }

        // Set min date to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('date_debut').min = today;
        document.getElementById('date_fin').min = today;
    });

    // Gestion du reset
    document.querySelector('button[type="reset"]').addEventListener('click', function() {
        if (confirm('Voulez-vous vraiment réinitialiser tous les champs ?')) {
            setTimeout(updatePreview, 100); // Mettre à jour après reset
            return true;
        }
        return false;
    });
    </script>
</body>
</html>