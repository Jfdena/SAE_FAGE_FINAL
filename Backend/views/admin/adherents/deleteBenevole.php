<?php
// Backend/views/admin/adherents/deleteMissions.php

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

// Récupérer le bénévole pour afficher ses infos
$stmt = $conn->prepare("SELECT * FROM benevole WHERE id_benevole = ?");
$stmt->execute([$benevole_id]);
$benevole = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier si le bénévole existe
if (!$benevole) {
    header('Location: listeAdherent.php?error=not_found');
    exit();
}

// Variables
$deleted = false;
$error = '';
$redirect_url = 'listeAdherent.php';

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier la confirmation
    if (isset($_POST['confirm']) && $_POST['confirm'] === 'oui') {
        try {
            // OPTION 1 : Suppression logique (recommandé) - juste changer le statut
            if (isset($_POST['soft_delete']) && $_POST['soft_delete'] === 'true') {
                $sql = "UPDATE benevole SET statut = 'inactif' WHERE id_benevole = ?";
                $message = "Le bénévole a été désactivé (statut mis à 'inactif').";
                $redirect_url = 'listeAdherent.php?status=disabled';
            }
            // OPTION 2 : Suppression physique (définitif)
            else {
                $sql = "DELETE FROM benevole WHERE id_benevole = ?";
                $message = "Le bénévole a été définitivement supprimé.";
                $redirect_url = 'listeAdherent.php?status=deleted';
            }

            // Exécuter la suppression
            $stmt = $conn->prepare($sql);
            $stmt->execute([$benevole_id]);

            $deleted = true;

            // Redirection après succès
            header('Location: ' . $redirect_url);
            exit();

        } catch (Exception $e) {
            $error = "Erreur lors de la suppression : " . $e->getMessage();
        }
    } else {
        // Annulation
        header('Location: voirDetails.php?id=' . $benevole_id);
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
    <title>Supprimer bénévole - FAGE</title>

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
        .benevole-info {
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

                    <!-- Information du bénévole -->
                    <div class="benevole-info mb-4">
                        <h5 class="text-danger">
                            <i class="bi bi-person-x"></i>
                            <?php echo htmlspecialchars($benevole['prenom'] . ' ' . $benevole['nom']); ?>
                        </h5>
                        <div class="row small">
                            <div class="col-md-6">
                                <strong>ID :</strong> #<?php echo $benevole_id; ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Email :</strong>
                                <?php echo !empty($benevole['email']) ? htmlspecialchars($benevole['email']) : 'Non renseigné'; ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Date d'inscription :</strong>
                                <?php echo date('d/m/Y', strtotime($benevole['date_inscription'])); ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Statut actuel :</strong>
                                <span class="badge bg-<?php echo $benevole['statut'] == 'actif' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($benevole['statut']); ?>
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
                                <li>Les participations aux événements seront affectées</li>
                                <li>Cette action ne peut pas être annulée</li>
                            </ul>
                        </div>

                        <!-- Options de suppression -->
                        <div class="mb-4">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="delete_type"
                                       id="soft_delete" value="soft" checked>
                                <label class="form-check-label" for="soft_delete">
                                    <strong class="text-warning">Désactivation (recommandé)</strong>
                                </label>
                                <div class="form-text small">
                                    Le bénévole sera marqué comme "inactif" mais conservé dans la base.
                                    Vous pourrez le réactiver ultérieurement.
                                </div>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="delete_type"
                                       id="hard_delete" value="hard">
                                <label class="form-check-label" for="hard_delete">
                                    <strong class="text-danger">Suppression définitive</strong>
                                </label>
                                <div class="form-text small text-danger">
                                    Le bénévole sera complètement supprimé de la base de données.
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
                                    <a href="voirDetails.php?id=<?php echo $benevole_id; ?>"
                                       class="btn btn-success">
                                        <i class="bi bi-arrow-left"></i> Annuler, retour à la fiche
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
                                <div>Désactivation recommandée</div>
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
                            En cas de doute, choisissez la désactivation plutôt que la suppression.
                        </small>
                    </div>
                </div>
            </div>

            <!-- Lien de secours -->
            <div class="text-center mt-4">
                <a href="listeAdherent.php" class="btn btn-outline-secondary">
                    <i class="bi bi-list"></i> Retour à la liste des bénévoles
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
                    '<i class="bi bi-trash-fill"></i> DÉSACTIVER LE BÉNÉVOLE';
            }
        }

        softDeleteRadio.addEventListener('change', updateDeleteType);
        hardDeleteRadio.addEventListener('change', updateDeleteType);

        // Validation du formulaire
        document.getElementById('deleteForm').addEventListener('submit', function(e) {
            const confirmInput = document.getElementById('confirm');
            const deleteType = hardDeleteRadio.checked ? 'DÉFINITIVE' : 'DÉSACTIVATION';

            if (confirmInput.value.toUpperCase() !== 'OUI') {
                e.preventDefault();
                alert('Vous devez taper "OUI" pour confirmer la suppression.');
                confirmInput.focus();
                return false;
            }

            // Dernière confirmation
            let message = '';
            if (hardDeleteRadio.checked) {
                message = `Êtes-vous ABSOLUMENT CERTAIN de vouloir SUPPRIMER DÉFINITIVEMENT ce bénévole ?\n\n` +
                    `Cette action est IRRÉVERSIBLE et les données seront PERDUES.`;
            } else {
                message = `Confirmez-vous la désactivation de ce bénévole ?\n\n` +
                    `Il sera marqué comme "inactif" mais pourra être réactivé ultérieurement.`;
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
                if (confirm('Annuler l\'opération et retourner à la fiche du bénévole ?')) {
                    window.location.href = 'voirDetails.php?id=<?php echo $benevole_id; ?>';
                }
            }
        });
    });
</script>
</body>
</html>