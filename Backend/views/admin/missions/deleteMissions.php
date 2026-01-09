<?php
// Backend/views/admin/missions/deleteMissions.php

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
try {
    $conn = $db->getConnection();
} catch (Exception $e) {

}

// Récupérer l'événement pour afficher ses infos
$stmt = $conn->prepare("SELECT * FROM Evenement WHERE id_evenement = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier si l'événement existe
if (!$event) {
    header('Location: calendrier.php?error=not_found');
    exit();
}

// Variables
$deleted = false;
$error = '';
$redirect_url = 'calendrier.php';

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier la confirmation
    if (isset($_POST['confirm']) && strtoupper($_POST['confirm']) === 'OUI') {
        try {
            // Déterminer le type de suppression
            $soft_delete = isset($_POST['soft_delete']) && $_POST['soft_delete'] === 'true';

            if ($soft_delete) {
                // Suppression logique : marquer comme annulé
                $sql = "UPDATE Evenement SET statut = 'annule' WHERE id_evenement = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$event_id]);

                // Message de succès
                $_SESSION['success_message'] = "L'événement a été marqué comme 'annulé'.";
                header('Location: calendrier.php?status=annule');
                exit();
            } else {
                // Suppression physique
                $sql = "DELETE FROM Evenement WHERE id_evenement = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$event_id]);

                // Message de succès
                $_SESSION['success_message'] = "L'événement a été définitivement supprimé.";
                header('Location: calendrier.php?status=deleted');
                exit();
            }

        } catch (Exception $e) {
            $error = "Erreur lors de la suppression : " . $e->getMessage();
        }
    } else {
        // Annulation - retour aux détails
        header('Location: voirDetailsMissions.php?id=' . $event_id);
        exit();
    }
}

// Si on arrive ici, c'est qu'on affiche le formulaire de confirmation
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer événement - FAGE</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <style>
        .warning-card {
            border: 3px solid #dc3545;
            border-left: 10px solid #dc3545;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
            100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
        }
        .event-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
        }
        .danger-zone {
            background: linear-gradient(135deg, #fff5f5 0%, #ffe6e6 100%);
            border-radius: 10px;
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
            <i class="bi bi-exclamation-triangle"></i> Confirmation suppression
        </div>
    </div>
</nav>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Carte d'avertissement -->
            <div class="card warning-card shadow-lg">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        CONFIRMATION DE SUPPRESSION
                    </h4>
                </div>

                <div class="card-body">
                    <!-- Message d'erreur -->
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger">
                            <h5><i class="bi bi-x-circle"></i> Erreur</h5>
                            <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Information de l'événement -->
                    <div class="event-info mb-4">
                        <h5 class="text-danger">
                            <i class="bi bi-calendar-x"></i>
                            <?php echo htmlspecialchars($event['nom']); ?>
                        </h5>
                        <div class="row small">
                            <div class="col-md-6">
                                <strong>ID :</strong> #<?php echo $event_id; ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Type :</strong>
                                <?php echo htmlspecialchars($event['type']); ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Dates :</strong>
                                <?php echo date('d/m/Y', strtotime($event['date_debut'])); ?>
                                <?php if ($event['date_debut'] !== $event['date_fin']): ?>
                                    - <?php echo date('d/m/Y', strtotime($event['date_fin'])); ?>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Lieu :</strong>
                                <?php echo !empty($event['lieu']) ? htmlspecialchars($event['lieu']) : 'Non spécifié'; ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Participants :</strong>
                                <?php echo $event['nb_participants_prevus']; ?> prévus
                            </div>
                            <div class="col-md-6">
                                <strong>Statut actuel :</strong>
                                <span class="badge bg-<?php
                                $status_colors = [
                                    'planifie' => 'warning',
                                    'en cours' => 'success',
                                    'termine' => 'secondary',
                                    'annule' => 'danger'
                                ];
                                echo $status_colors[$event['statut']] ?? 'secondary';
                                ?>">
                                    <?php echo ucfirst($event['statut']); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Zone de danger -->
                    <div class="danger-zone p-4 mb-4">
                        <h5 class="text-danger mb-3">
                            <i class="bi bi-shield-exclamation"></i> Attention !
                        </h5>

                        <div class="alert alert-danger">
                            <h6><i class="bi bi-exclamation-octagon"></i> Cette action est irréversible !</h6>
                            <ul class="mb-0">
                                <li>Les données pourront être perdues définitivement</li>
                                <li>Les participations des bénévoles seront affectées</li>
                                <li>Cette action ne peut pas être annulée</li>
                            </ul>
                        </div>

                        <!-- Options de suppression -->
                        <div class="mb-4">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="delete_type"
                                       id="soft_delete" value="soft" checked>
                                <label class="form-check-label" for="soft_delete">
                                    <strong class="text-warning">Annulation (recommandé)</strong>
                                </label>
                                <div class="form-text small">
                                    L'événement sera marqué comme "annulé" mais conservé dans la base.
                                    Vous pourrez le consulter dans les archives.
                                </div>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="delete_type"
                                       id="hard_delete" value="hard">
                                <label class="form-check-label" for="hard_delete">
                                    <strong class="text-danger">Suppression définitive</strong>
                                </label>
                                <div class="form-text small text-danger">
                                    L'événement sera complètement supprimé de la base de données.
                                    Cette action est définitive et irréversible.
                                </div>
                            </div>
                        </div>

                        <!-- Formulaire de confirmation -->
                        <form method="POST" action="" id="deleteForm">
                            <!-- Champ caché pour le type de suppression -->
                            <input type="hidden" name="soft_delete" id="soft_delete_input" value="true">

                            <div class="mb-4">
                                <label for="confirm" class="form-label fw-bold">
                                    <i class="bi bi-question-circle"></i>
                                    Tapez "OUI" pour confirmer la suppression :
                                </label>
                                <input type="text"
                                       id="confirm"
                                       name="confirm"
                                       class="form-control form-control-lg text-center"
                                       placeholder="OUI"
                                       required
                                       autocomplete="off">
                                <div class="form-text text-center">
                                    Cette mesure de sécurité empêche les suppressions accidentelles.
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <div>
                                    <a href="voirDetailsMissions.php?id=<?php echo $event_id; ?>"
                                       class="btn btn-success">
                                        <i class="bi bi-arrow-left"></i> Annuler, retour aux détails
                                    </a>
                                </div>

                                <div>
                                    <button type="submit"
                                            class="btn btn-danger btn-lg"
                                            id="confirmBtn">
                                        <i class="bi bi-trash-fill"></i> CONFIRMER LA SUPPRESSION
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Statistiques de sécurité -->
                    <div class="alert alert-info">
                        <h6><i class="bi bi-shield-check"></i> Mesures de sécurité</h6>
                        <div class="row small">
                            <div class="col-md-4 text-center">
                                <div class="mb-1">
                                    <i class="bi bi-card-checklist" style="font-size: 1.5rem;"></i>
                                </div>
                                <div>Confirmation écrite</div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="mb-1">
                                    <i class="bi bi-clock-history" style="font-size: 1.5rem;"></i>
                                </div>
                                <div>Annulation recommandée</div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="mb-1">
                                    <i class="bi bi-exclamation-octagon" style="font-size: 1.5rem;"></i>
                                </div>
                                <div>Avertissements multiples</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pied de carte -->
                <div class="card-footer bg-light">
                    <div class="text-center">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i>
                            Cette action est enregistrée dans les logs système.
                            En cas de doute, choisissez l'annulation plutôt que la suppression.
                        </small>
                    </div>
                </div>
            </div>

            <!-- Lien de secours -->
            <div class="text-center mt-4">
                <a href="calendrier.php" class="btn btn-outline-secondary">
                    <i class="bi bi-calendar-event"></i> Retour au calendrier
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Focus sur le champ de confirmation
        document.getElementById('confirm').focus();

        // Gérer le type de suppression
        const softDeleteRadio = document.getElementById('soft_delete');
        const hardDeleteRadio = document.getElementById('hard_delete');
        const softDeleteInput = document.getElementById('soft_delete_input');

        function updateDeleteType() {
            if (hardDeleteRadio.checked) {
                softDeleteInput.value = 'false';
                document.getElementById('confirmBtn').innerHTML =
                    '<i class="bi bi-trash-fill"></i> SUPPRIMER DÉFINITIVEMENT';
            } else {
                softDeleteInput.value = 'true';
                document.getElementById('confirmBtn').innerHTML =
                    '<i class="bi bi-trash-fill"></i> ANNULER L\'ÉVÉNEMENT';
            }
        }

        softDeleteRadio.addEventListener('change', updateDeleteType);
        hardDeleteRadio.addEventListener('change', updateDeleteType);

        // Validation du formulaire
        document.getElementById('deleteForm').addEventListener('submit', function(e) {
            const confirmInput = document.getElementById('confirm');
            const deleteType = hardDeleteRadio.checked ? 'DÉFINITIVE' : 'ANNULATION';

            if (confirmInput.value.toUpperCase() !== 'OUI') {
                e.preventDefault();
                alert('Vous devez taper "OUI" pour confirmer la suppression.');
                confirmInput.focus();
                return false;
            }

            // Dernière confirmation
            let message = '';
            if (hardDeleteRadio.checked) {
                message = `Êtes-vous ABSOLUMENT CERTAIN de vouloir SUPPRIMER DÉFINITIVEMENT cet événement ?\n\n` +
                    `Cette action est IRRÉVERSIBLE et les données seront PERDUES.`;
            } else {
                message = `Confirmez-vous l'annulation de cet événement ?\n\n` +
                    `Il sera marqué comme "annulé" mais pourra être consulté dans les archives.`;
            }

            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }

            return true;
        });

        // Protection contre les pressions accidentelles
        let submitCount = 0;
        const confirmBtn = document.getElementById('confirmBtn');

        confirmBtn.addEventListener('click', function(e) {
            submitCount++;

            // Si c'est le premier clic, on empêche la soumission directe
            if (submitCount === 1) {
                e.preventDefault();

                // Changer le bouton pour indiquer qu'un second clic est nécessaire
                const originalText = confirmBtn.innerHTML;
                confirmBtn.innerHTML = '<i class="bi bi-exclamation-triangle"></i> CLIQUEZ À NOUVEAU POUR CONFIRMER';
                confirmBtn.classList.remove('btn-danger');
                confirmBtn.classList.add('btn-warning');

                // Réinitialiser après 3 secondes
                setTimeout(function() {
                    confirmBtn.innerHTML = originalText;
                    confirmBtn.classList.remove('btn-warning');
                    confirmBtn.classList.add('btn-danger');
                    submitCount = 0;
                }, 3000);

                return false;
            }
        });

        // Touche Échap pour annuler
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (confirm('Annuler l\'opération et retourner aux détails de l\'événement ?')) {
                    window.location.href = 'voirDetailsMissions.php?id=<?php echo $event_id; ?>';
                }
            }
        });
    });
</script>
</body>
</html>