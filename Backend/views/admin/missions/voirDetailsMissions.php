<?php
// Backend/views/admin/missions/voirDetailsMissions.php

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

// Récupérer l'événement
$stmt = $conn->prepare("SELECT * FROM Evenement WHERE id_evenement = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier si l'événement existe
if (!$event) {
    header('Location: calendrier.php?error=not_found');
    exit();
}

// Formater les dates
$date_debut = new DateTime($event['date_debut']);
$date_fin = new DateTime($event['date_fin']);
$now = new DateTime();

// Calculer le statut dynamiquement si nécessaire
if (empty($event['statut']) || $event['statut'] === 'planifié') {
    if ($date_fin < $now) {
        $event['statut'] = 'termine';
    } elseif ($date_debut <= $now && $date_fin >= $now) {
        $event['statut'] = 'en cours';
    }
}

// Récupérer les bénévoles participants (simulation - à adapter avec ta table PARTICIPATION)
$participants = [];
try {
    // Essaie de récupérer depuis une table de participation si elle existe
    $stmt = $conn->prepare("
        SELECT b.id_benevole, b.nom, b.prenom, b.email 
        FROM benevole b
        LIMIT 5  -- Simulation, à remplacer par la vraie jointure
    ");
    $stmt->execute();
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Table non existante ou autre erreur
}

// Calculer les statistiques
$jours_restants = $date_debut->diff($now)->days;
if ($now > $date_debut) {
    $jours_restants = -$jours_restants; // Événement passé
}

$duree_jours = $date_debut->diff($date_fin)->days + 1;

// Badge de statut avec couleur
$status_colors = [
    'planifié' => 'warning',
    'en cours' => 'success',
    'termine' => 'secondary',
    'annule' => 'danger'
];
$status_color = $status_colors[$event['statut']] ?? 'secondary';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails événement - FAGE</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <style>
        .event-header {
            background: linear-gradient(135deg, #183146 0%, #0d6efd 100%);
            color: white;
            border-radius: 10px 10px 0 0;
            padding: 30px;
        }
        .stat-card {
            border-radius: 10px;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-3px);
        }
        .info-box {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .participant-avatar {
            width: 40px;
            height: 40px;
            background-color: #183146;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: #183146;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -25px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #183146;
            border: 2px solid white;
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
            <i class="bi bi-calendar-event"></i> Détails événement
        </div>
    </div>
</nav>

<div class="container py-4">
    <!-- En-tête avec actions -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="calendrier.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Retour au calendrier
            </a>
        </div>

        <div class="btn-group">
            <a href="editMissions.php?id=<?php echo $event_id; ?>"
               class="btn btn-warning">
                <i class="bi bi-pencil"></i> Modifier
            </a>
            <a href="addMissions.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nouvel événement
            </a>
        </div>
    </div>

    <!-- Carte principale -->
    <div class="card shadow mb-4">
        <!-- En-tête événement -->
        <div class="event-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="h3 mb-2">
                        <?php echo htmlspecialchars($event['nom']); ?>
                    </h1>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                            <span class="badge bg-<?php echo $status_color; ?> rounded-pill">
                                <i class="bi bi-circle-fill"></i>
                                <?php echo ucfirst($event['statut']); ?>
                            </span>
                        <span class="badge bg-info rounded-pill">
                                <i class="bi bi-tag"></i>
                                <?php echo htmlspecialchars($event['type']); ?>
                            </span>
                        <span class="badge bg-light text-dark rounded-pill">
                                ID : #<?php echo $event_id; ?>
                            </span>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="bg-white text-dark rounded-circle d-inline-flex align-items-center justify-content-center"
                         style="width: 80px; height: 80px;">
                        <i class="bi bi-calendar-event" style="font-size: 2.5rem; color: #183146;"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Corps -->
        <div class="card-body">
            <div class="row g-4">
                <!-- Colonne 1 : Informations principales -->
                <div class="col-lg-8">
                    <!-- Description -->
                    <?php if (!empty($event['description'])): ?>
                        <div class="info-box mb-4">
                            <h5><i class="bi bi-card-text"></i> Description</h5>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Dates et lieu -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-box h-100">
                                <h5><i class="bi bi-calendar"></i> Dates et horaires</h5>
                                <div class="mb-2">
                                    <strong>Début :</strong><br>
                                    <?php echo $date_debut->format('d/m/Y'); ?> à <?php echo $date_debut->format('H:i'); ?>
                                </div>
                                <div class="mb-2">
                                    <strong>Fin :</strong><br>
                                    <?php echo $date_fin->format('d/m/Y'); ?> à <?php echo $date_fin->format('H:i'); ?>
                                </div>
                                <div class="mb-0">
                                    <strong>Durée :</strong> <?php echo $duree_jours; ?> jour(s)
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-box h-100">
                                <h5><i class="bi bi-geo-alt"></i> Lieu</h5>
                                <?php if (!empty($event['lieu'])): ?>
                                    <p class="mb-0">
                                        <i class="bi bi-pin-map"></i>
                                        <?php echo htmlspecialchars($event['lieu']); ?>
                                    </p>
                                <?php else: ?>
                                    <p class="text-muted mb-0">
                                        <i class="bi bi-question-circle"></i> Lieu non spécifié
                                    </p>
                                <?php endif; ?>

                                <div class="mt-3">
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-map"></i> Voir sur la carte
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Participants -->
                    <div class="info-box">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">
                                <i class="bi bi-people"></i> Participants
                            </h5>
                            <span class="badge bg-primary">
                                    <?php echo $event['nb_participants_prevus']; ?> prévus
                                </span>
                        </div>

                        <?php if (!empty($participants)): ?>
                            <div class="row">
                                <?php foreach ($participants as $participant): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="participant-avatar">
                                                <?php echo strtoupper(substr($participant['prenom'], 0, 1) . substr($participant['nom'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <strong><?php echo htmlspecialchars($participant['prenom'] . ' ' . $participant['nom']); ?></strong>
                                                <?php if (!empty($participant['email'])): ?>
                                                    <br>
                                                    <small class="text-muted">
                                                        <a href="mailto:<?php echo htmlspecialchars($participant['email']); ?>">
                                                            <?php echo htmlspecialchars($participant['email']); ?>
                                                        </a>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-3">
                                <i class="bi bi-people" style="font-size: 2rem; color: #6c757d;"></i>
                                <p class="text-muted mb-0 mt-2">Aucun participant enregistré pour le moment</p>
                                <button class="btn btn-sm btn-outline-primary mt-2">
                                    <i class="bi bi-person-plus"></i> Ajouter des participants
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Colonne 2 : Statistiques et actions -->
                <div class="col-lg-4">
                    <!-- Statistiques -->
                    <h5 class="border-bottom pb-2 mb-3">
                        <i class="bi bi-bar-chart"></i> Statistiques
                    </h5>

                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="stat-card card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Jours</h6>
                                    <h3 class="mb-0"><?php echo $duree_jours; ?></h3>
                                </div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="stat-card card bg-info text-white">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Participants</h6>
                                    <h3 class="mb-0"><?php echo $event['nb_participants_prevus']; ?></h3>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="stat-card card <?php echo $jours_restants >= 0 ? 'bg-warning text-dark' : 'bg-secondary text-white'; ?>">
                                <div class="card-body text-center">
                                    <h6 class="card-title">
                                        <?php echo $jours_restants >= 0 ? 'Jours restants' : 'Jours écoulés'; ?>
                                    </h6>
                                    <h3 class="mb-0"><?php echo abs($jours_restants); ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Budget -->
                    <?php if (!empty($event['budget'])): ?>
                        <div class="info-box mb-4">
                            <h5><i class="bi bi-cash-coin"></i> Budget</h5>
                            <h3 class="text-success"><?php echo number_format($event['budget'], 2, ',', ' '); ?> €</h3>
                            <small class="text-muted">Budget alloué à cet événement</small>
                        </div>
                    <?php endif; ?>

                    <!-- Actions rapides -->
                    <div class="info-box">
                        <h5><i class="bi bi-lightning"></i> Actions rapides</h5>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary">
                                <i class="bi bi-envelope"></i> Envoyer rappel
                            </button>
                            <button class="btn btn-outline-success">
                                <i class="bi bi-clipboard-check"></i> Feuille d'émargement
                            </button>
                            <button class="btn btn-outline-info" onclick="window.print()">
                                <i class="bi bi-printer"></i> Imprimer cette fiche
                            </button>
                            <a href="deleteMissions.php?id=<?php echo $event_id; ?>"
                               class="btn btn-outline-danger">
                                <i class="bi bi-trash"></i> Supprimer
                            </a>
                        </div>
                    </div>

                    <!-- Timeline de l'événement -->
                    <div class="info-box mt-4">
                        <h5><i class="bi bi-clock-history"></i> Chronologie</h5>
                        <div class="timeline">
                            <div class="timeline-item">
                                <strong>Création</strong><br>
                                <small class="text-muted">Date de création de l'événement</small>
                            </div>
                            <div class="timeline-item">
                                <strong>Planification</strong><br>
                                <small class="text-muted">Phase de préparation</small>
                            </div>
                            <div class="timeline-item">
                                <strong>Événement</strong><br>
                                <small class="text-muted">
                                    <?php echo $date_debut->format('d/m/Y'); ?> -
                                    <?php echo $date_fin->format('d/m/Y'); ?>
                                </small>
                            </div>
                            <div class="timeline-item">
                                <strong>Bilan</strong><br>
                                <small class="text-muted">Retour et évaluation</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents associés (à implémenter plus tard) -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="info-box">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">
                                <i class="bi bi-paperclip"></i> Documents associés
                            </h5>
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-upload"></i> Ajouter un document
                            </button>
                        </div>
                        <div class="text-center py-3">
                            <i class="bi bi-folder" style="font-size: 2rem; color: #6c757d;"></i>
                            <p class="text-muted mb-0 mt-2">Aucun document pour le moment</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pied de carte -->
        <div class="card-footer bg-light">
            <div class="row">
                <div class="col-md-6">
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i>
                        Événement créé dans le backoffice FAGE
                    </small>
                </div>
                <div class="col-md-6 text-end">
                    <small class="text-muted">
                        Dernière mise à jour : <?php echo date('d/m/Y H:i'); ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation entre événements -->
    <div class="d-flex justify-content-between">
        <?php
        // Chercher l'événement précédent
        $stmt = $conn->prepare("
                SELECT id_evenement, nom 
                FROM Evenement 
                WHERE date_debut < ? 
                ORDER BY date_debut DESC 
                LIMIT 1
            ");
        $stmt->execute([$event['date_debut']]);
        $prev = $stmt->fetch(PDO::FETCH_ASSOC);
        ?>

        <?php if ($prev): ?>
            <a href="voirDetailsMissions.php?id=<?php echo $prev['id_evenement']; ?>"
               class="btn btn-outline-secondary">
                <i class="bi bi-chevron-left"></i>
                <?php echo htmlspecialchars(substr($prev['nom'], 0, 20)); ?>
                <?php if (strlen($prev['nom']) > 20) echo '...'; ?>
            </a>
        <?php else: ?>
            <div></div>
        <?php endif; ?>

        <?php
        // Chercher l'événement suivant
        $stmt = $conn->prepare("
                SELECT id_evenement, nom 
                FROM Evenement 
                WHERE date_debut > ? 
                ORDER BY date_debut ASC 
                LIMIT 1
            ");
        $stmt->execute([$event['date_debut']]);
        $next = $stmt->fetch(PDO::FETCH_ASSOC);
        ?>

        <?php if ($next): ?>
            <a href="voirDetailsMissions.php?id=<?php echo $next['id_evenement']; ?>"
               class="btn btn-outline-secondary">
                <?php echo htmlspecialchars(substr($next['nom'], 0, 20)); ?>
                <?php if (strlen($next['nom']) > 20) echo '...'; ?>
                <i class="bi bi-chevron-right"></i>
            </a>
        <?php else: ?>
            <div></div>
        <?php endif; ?>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Calcul dynamique des jours restants
        function updateCountdown() {
            const now = new Date();
            const eventStart = new Date('<?php echo $event['date_debut']; ?>');
            const diffTime = eventStart - now;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            const countdownEl = document.querySelector('.stat-card.bg-warning h3, .stat-card.bg-secondary h3');
            if (countdownEl) {
                countdownEl.textContent = Math.abs(diffDays);
            }
        }

        // Mettre à jour toutes les minutes
        setInterval(updateCountdown, 60000);
        updateCountdown();

        // Confirmation suppression
        document.querySelector('a.btn-outline-danger').addEventListener('click', function(e) {
            e.preventDefault();
            const eventName = '<?php echo addslashes($event["nom"]); ?>';
            if (confirm(`Voulez-vous vraiment supprimer l'événement "${eventName}" ?\n\nCette action est irréversible.`)) {
                window.location.href = this.href;
            }
        });

        // Impression améliorée
        window.addEventListener('beforeprint', function() {
            // Cacher les boutons d'action avant impression
            document.querySelectorAll('.btn-group, .btn-outline-danger, .btn-outline-primary').forEach(btn => {
                btn.style.display = 'none';
            });
        });

        window.addEventListener('afterprint', function() {
            // Réafficher les boutons après impression
            document.querySelectorAll('.btn-group, .btn-outline-danger, .btn-outline-primary').forEach(btn => {
                btn.style.display = '';
            });
        });

        // Partage rapide (simulation)
        document.querySelector('button.btn-outline-primary').addEventListener('click', function() {
            const eventDetails = `Événement FAGE: ${document.title}\nDates: <?php echo $date_debut->format('d/m/Y'); ?> - <?php echo $date_fin->format('d/m/Y'); ?>\nLieu: <?php echo htmlspecialchars($event['lieu']); ?>`;
            alert('Copiez ces informations pour partager :\n\n' + eventDetails);
        });
    });

    // Calcul du coût par participant
    function calculateCostPerParticipant() {
        const budget = <?php echo $event['budget'] ?? 0; ?>;
        const participants = <?php echo $event['nb_participants_prevus'] ?? 1; ?>;

        if (budget > 0 && participants > 0) {
            const costPerPerson = budget / participants;
            return costPerPerson.toFixed(2);
        }
        return '0.00';
    }
</script>
</body>
</html>