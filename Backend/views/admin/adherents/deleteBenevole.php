<?php
// Backend/views/admin/adherents/deleteBenevole.php

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
try {
    $conn = $db->getConnection();
} catch (Exception $e) {
    // Si erreur de connexion, rediriger avec message
    header('Location: listeAdherent.php?error=db_error');
    exit();
}

// Récupérer le bénévole pour afficher ses infos
try {
    $stmt = $conn->prepare("SELECT * FROM benevole WHERE id_benevole = ?");
    $stmt->execute([$benevole_id]);
    $benevole = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    header('Location: listeAdherent.php?error=db_query');
    exit();
}

// Vérifier si le bénévole existe
if (!$benevole) {
    header('Location: listeAdherent.php?error=not_found');
    exit();
}

// Variables
$error = '';

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier la confirmation
    if (isset($_POST['confirm']) && strtoupper(trim($_POST['confirm'])) === 'OUI') {
        try {
            // OPTION 1 : Suppression logique (recommandé) - juste changer le statut
            if (isset($_POST['soft_delete']) && $_POST['soft_delete'] === 'true') {
                $sql = "UPDATE benevole SET statut = 'inactif' WHERE id_benevole = ?";
                $redirect_url = 'listeAdherent.php?status=disabled&id=' . $benevole_id . '&nom=' . urlencode($benevole['prenom'] . ' ' . $benevole['nom']);
            }
            // OPTION 2 : Suppression physique (définitif)
            else {
                $sql = "DELETE FROM benevole WHERE id_benevole = ?";
                $redirect_url = 'listeAdherent.php?status=deleted&id=' . $benevole_id . '&nom=' . urlencode($benevole['prenom'] . ' ' . $benevole['nom']);
            }

            // Exécuter la suppression
            $stmt = $conn->prepare($sql);
            $stmt->execute([$benevole_id]);

            // Redirection après succès
            header('Location: ' . $redirect_url);
            exit();

        } catch (Exception $e) {
            $error = "Erreur lors de la suppression : " . $e->getMessage();
        }
    } else {
        // Annulation - retour à la fiche
        header('Location: voirDetails.php?id=' . $benevole_id . '&error=cancelled');
        exit();
    }
}
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
        .required-field::after {
            content: " *";
            color: #dc3545;
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
            <!-- Message d'erreur global -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show mb-4">
                    <h5><i class="bi bi-x-circle"></i> Erreur</h5>
                    <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Carte d'avertissement -->
            <div class="card warning-card shadow-lg">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        CONFIRMATION DE SUPPRESSION
                    </h4>
                </div>

                <div class="card-body">
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
                                <li>Les cotisations associées seront supprimées</li>
                                <li>Cette action ne peut pas être annulée</li>
                            </ul>
                        </div>

                        <!-- Options de suppression -->
                        <div class="mb-4">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="delete_type"
                                       id="soft_delete" value="soft" checked>
                                <label class="form-check-label" for="soft_delete">
                                    <strong class="text-warning">Désactivation (recommandé)</strong>
                                </label>
                                <div class="form-text small">
                                    <i class="bi bi-info-circle"></i> Le bénévole sera marqué comme "inactif" mais conservé dans la base.
                                    Vous pourrez le réactiver en modifiant sa fiche.
                                </div>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="delete_type"
                                       id="hard_delete" value="hard">
                                <label class="form-check-label" for="hard_delete">
                                    <strong class="text-danger">Suppression définitive</strong>
                                </label>
                                <div class="form-text small text-danger">
                                    <i class="bi bi-exclamation-triangle"></i> Le bénévole sera complètement supprimé de la base de données
                                    ainsi que toutes ses cotisations. Cette action est définitive et irréversible.
                                </div>
                            </div>
                        </div>

                        <!-- Formulaire de confirmation -->
                        <form method="POST" action="" id="deleteForm">
                            <!-- Champ caché pour le type de suppression -->
                            <input type="hidden" name="soft_delete" id="soft_delete_input" value="true">

                            <div class="mb-4">
                                <label for="confirm" class="form-label fw-bold required-field">
                                    <i class="bi bi-question-circle"></i>
                                    Tapez "OUI" (en majuscules) pour confirmer :
                                </label>
                                <input type="text"
                                       id="confirm"
                                       name="confirm"
                                       class="form-control form-control-lg text-center"
                                       placeholder="OUI"
                                       required
                                       autocomplete="off"
                                       style="font-size: 1.5rem; letter-spacing: 2px;">
                                <div class="form-text text-center mt-2">
                                    Cette mesure de sécurité empêche les suppressions accidentelles.
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <div>
                                    <a href="voirDetails.php?id=<?php echo $benevole_id; ?>"
                                       class="btn btn-success">
                                        <i class="bi bi-arrow-left"></i> Annuler et retourner
                                    </a>
                                    <a href="listeAdherent.php" class="btn btn-outline-secondary ms-2">
                                        <i class="bi bi-list"></i> Liste complète
                                    </a>
                                </div>

                                <div>
                                    <button type="submit"
                                            class="btn btn-danger btn-lg px-4"
                                            id="confirmBtn">
                                        <i class="bi bi-trash-fill"></i> CONFIRMER
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Pied de carte -->
                <div class="card-footer bg-light">
                    <div class="row">
                        <div class="col-md-8">
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i>
                                Pour plus de sécurité, la désactivation est recommandée.
                            </small>
                        </div>
                        <div class="col-md-4 text-end">
                            <small class="text-muted">
                                ID: #<?php echo $benevole_id; ?>
                            </small>
                        </div>
                    </div>
                </div>
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
        const confirmBtn = document.getElementById('confirmBtn');

        function updateDeleteType() {
            if (hardDeleteRadio.checked) {
                softDeleteInput.value = 'false';
                confirmBtn.innerHTML = '<i class="bi bi-trash-fill"></i> SUPPRIMER DÉFINITIVEMENT';
                confirmBtn.classList.remove('btn-danger');
                confirmBtn.classList.add('btn-dark');
            } else {
                softDeleteInput.value = 'true';
                confirmBtn.innerHTML = '<i class="bi bi-trash-fill"></i> DÉSACTIVER LE BÉNÉVOLE';
                confirmBtn.classList.remove('btn-dark');
                confirmBtn.classList.add('btn-danger');
            }
        }

        softDeleteRadio.addEventListener('change', updateDeleteType);
        hardDeleteRadio.addEventListener('change', updateDeleteType);

        // Validation du formulaire
        document.getElementById('deleteForm').addEventListener('submit', function(e) {
            const confirmInput = document.getElementById('confirm');
            const isHardDelete = hardDeleteRadio.checked;

            // Vérification "OUI"
            if (confirmInput.value.trim().toUpperCase() !== 'OUI') {
                e.preventDefault();
                alert('❌ Vous devez taper "OUI" (en majuscules) pour confirmer.');
                confirmInput.focus();
                confirmInput.select();
                return false;
            }

            // Dernière confirmation
            const nomBenevole = "<?php echo addslashes($benevole['prenom'] . ' ' . $benevole['nom']); ?>";
            let message;

            if (isHardDelete) {
                message = `⚠️ CONFIRMATION FINALE\n\n` +
                    `Êtes-vous ABSOLUMENT CERTAIN de vouloir SUPPRIMER DÉFINITIVEMENT :\n` +
                    `"${nomBenevole}" ?\n\n` +
                    `✅ Cette action supprimera :\n` +
                    `• Le bénévole et toutes ses informations\n` +
                    `• Toutes ses cotisations associées\n\n` +
                    `❌ Cette action est IRRÉVERSIBLE !`;
            } else {
                message = `⚠️ CONFIRMATION FINALE\n\n` +
                    `Confirmez-vous la DÉSACTIVATION de :\n` +
                    `"${nomBenevole}" ?\n\n` +
                    `✅ Cette action :\n` +
                    `• Marquera le bénévole comme "inactif"\n` +
                    `• Conservera toutes ses données\n` +
                    `• Pourra être annulée en modifiant sa fiche`;
            }

            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }

            // Petit délai pour montrer le traitement
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> TRAITEMENT EN COURS...';

            return true;
        });

        // Touche Échap pour annuler
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.location.href = 'voirDetails.php?id=<?php echo $benevole_id; ?>';
            }
        });
    });
</script>
</body>
</html>