<?php
// Backend/views/admin/partenaires/deletePartenaire.php

// Protection
require_once '../../../session_check.php';

// Vérifier l'ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: listePartenaires.php?error=no_id');
    exit();
}

$partenaire_id = (int)$_GET['id'];

// Connexion BDD
require_once '../../../config/Database.php';
$db = new Database();

try {
    $conn = $db->getConnection();
} catch (Exception $e) {
    die("<div class='alert alert-danger'>Erreur connexion BDD : " . $e->getMessage() . "</div>");
}

// Récupérer le partenaire
try {
    $stmt = $conn->prepare("SELECT * FROM Partenaire WHERE id_partenaire = ?");
    $stmt->execute([$partenaire_id]);
    $partenaire = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("<div class='alert alert-danger'>Erreur SELECT : " . $e->getMessage() . "</div>");
}

if (!$partenaire) {
    header('Location: listePartenaires.php?error=not_found');
    exit();
}

// Variable pour les erreurs
$error = '';
$success = false;
$deleted = false;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier la confirmation
    $confirm = trim($_POST['confirm'] ?? '');
    $action = $_POST['action'] ?? 'desactiver';

    if (strtoupper($confirm) === 'OUI') {
        try {
            if ($action === 'desactiver') {
                // Désactivation
                $sql = "UPDATE Partenaire SET statut = 'inactif' WHERE id_partenaire = ?";
                $message = "désactivé";
                $success = true;

                $stmt = $conn->prepare($sql);
                $stmt->execute([$partenaire_id]);

                // Recharger les données
                $stmt = $conn->prepare("SELECT * FROM Partenaire WHERE id_partenaire = ?");
                $stmt->execute([$partenaire_id]);
                $partenaire = $stmt->fetch(PDO::FETCH_ASSOC);

            } else {
                // Suppression définitive
                $sql = "DELETE FROM Partenaire WHERE id_partenaire = ?";
                $message = "supprimé définitivement";
                $deleted = true;

                $stmt = $conn->prepare($sql);
                $stmt->execute([$partenaire_id]);
            }

        } catch (Exception $e) {
            $error = "Erreur : " . $e->getMessage();
            error_log("Erreur delete partenaire: " . $e->getMessage());
        }
    } else {
        $error = "Vous devez taper 'OUI' pour confirmer.";
    }
}

// Si supprimé, rediriger immédiatement
if ($deleted) {
    header('Location: listePartenaires.php?success=supprime&nom=' . urlencode($partenaire['nom']));
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer partenaire - FAGE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .option-card {
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .option-card:hover {
            background-color: #f8f9fa;
        }
        .option-card.selected {
            border-color: #0d6efd;
            background-color: #e7f1ff;
        }
        .option-warning {
            border-color: #ffc107;
            background-color: #fff3cd;
        }
        .option-danger {
            border-color: #dc3545;
            background-color: #f8d7da;
        }
        .confirmation-input {
            font-size: 1.2rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
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
            <?php echo $success ? 'Partenaire désactivé' : 'Supprimer partenaire'; ?>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Messages -->
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <h5><i class="bi bi-x-circle"></i> Erreur</h5>
                    <p><?php echo htmlspecialchars($error); ?></p>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <h5><i class="bi bi-check-circle"></i> Succès !</h5>
                    <p>Le partenaire <strong><?php echo htmlspecialchars($partenaire['nom']); ?></strong> a été désactivé avec succès.</p>
                    <div class="mt-2">
                        <a href="listePartenaires.php" class="btn btn-success btn-sm">
                            <i class="bi bi-list"></i> Retour à la liste
                        </a>
                        <a href="voirDetailsPartenaire.php?id=<?php echo $partenaire_id; ?>"
                           class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-eye"></i> Voir la fiche
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!$success && !$deleted): ?>
                <!-- Carte principale -->
                <div class="card border-danger shadow">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-exclamation-triangle"></i>
                            Confirmer la suppression
                        </h4>
                    </div>

                    <div class="card-body">
                        <!-- Info partenaire -->
                        <div class="alert alert-info">
                            <h5>
                                <i class="bi bi-handshake"></i>
                                <?php echo htmlspecialchars($partenaire['nom']); ?>
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>ID :</strong> #<?php echo $partenaire_id; ?><br>
                                    <strong>Type :</strong>
                                    <span class="badge bg-<?php echo $partenaire['type'] === 'donateur' ? 'primary' : 'warning'; ?>">
                                        <?php echo $partenaire['type'] === 'donateur' ? 'Donateur' : 'Subvention'; ?>
                                    </span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Statut actuel :</strong>
                                    <span class="badge bg-<?php echo $partenaire['statut'] === 'actif' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($partenaire['statut']); ?>
                                    </span><br>
                                    <?php if (!empty($partenaire['montant'])): ?>
                                        <strong>Montant :</strong>
                                        <?php echo number_format($partenaire['montant'], 2, ',', ' '); ?> €
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Options -->
                        <h5 class="mt-4 mb-3">Choisissez une action :</h5>

                        <div class="mb-4">
                            <!-- Option 1 : Désactiver -->
                            <div class="option-card option-warning" id="option-desactiver" onclick="selectOption('desactiver')">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="action"
                                           id="action-desactiver" value="desactiver" checked>
                                    <label class="form-check-label" for="action-desactiver">
                                        <h6 class="text-warning">
                                            <i class="bi bi-toggle-off"></i> Désactiver le partenaire
                                        </h6>
                                    </label>
                                </div>
                                <p class="mb-0 small">
                                    Le partenaire sera marqué comme "inactif" mais conservé dans la base de données.
                                    <br><strong>Recommandé</strong> - Vous pourrez le réactiver plus tard.
                                </p>
                            </div>

                            <!-- Option 2 : Supprimer définitivement -->
                            <div class="option-card option-danger" id="option-supprimer" onclick="selectOption('supprimer')">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="action"
                                           id="action-supprimer" value="supprimer">
                                    <label class="form-check-label" for="action-supprimer">
                                        <h6 class="text-danger">
                                            <i class="bi bi-trash"></i> Supprimer définitivement
                                        </h6>
                                    </label>
                                </div>
                                <p class="mb-0 small text-danger">
                                    <strong>ATTENTION :</strong> Le partenaire sera complètement supprimé de la base de données.
                                    <br>Cette action est irréversible et les données seront perdues.
                                </p>
                            </div>
                        </div>

                        <!-- Formulaire -->
                        <form method="POST" id="deleteForm">
                            <div class="mb-4">
                                <label for="confirm" class="form-label fw-bold">
                                    <i class="bi bi-shield-exclamation"></i>
                                    Confirmation finale :
                                </label>
                                <p class="text-muted small">
                                    Pour confirmer, tapez <strong>OUI</strong> en majuscules dans le champ ci-dessous.
                                </p>
                                <input type="text"
                                       id="confirm"
                                       name="confirm"
                                       class="form-control form-control-lg confirmation-input text-center"
                                       placeholder="OUI"
                                       required
                                       autocomplete="off"
                                       maxlength="3">
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <div>
                                    <a href="listePartenaires.php" class="btn btn-secondary">
                                        <i class="bi bi-x-circle"></i> Annuler
                                    </a>
                                    <a href="voirDetailsPartenaire.php?id=<?php echo $partenaire_id; ?>"
                                       class="btn btn-outline-primary ms-2">
                                        <i class="bi bi-eye"></i> Voir la fiche
                                    </a>
                                </div>

                                <div>
                                    <button type="submit" class="btn btn-danger btn-lg px-4" id="submitBtn">
                                        <i class="bi bi-check-circle"></i> Confirmer l'action
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="card-footer bg-light">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i>
                            ID: #<?php echo $partenaire_id; ?> •
                            Date création: <?php echo date('d/m/Y', strtotime($partenaire['date_creation'])); ?>
                        </small>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Gestion des options
    function selectOption(option) {
        // Désélectionner toutes les cartes
        document.querySelectorAll('.option-card').forEach(card => {
            card.classList.remove('selected');
        });

        // Sélectionner la carte cliquée
        document.getElementById('option-' + option).classList.add('selected');

        // Cochez le radio correspondant
        document.getElementById('action-' + option).checked = true;

        // Mettre à jour le texte du bouton
        const btn = document.getElementById('submitBtn');
        if (option === 'desactiver') {
            btn.innerHTML = '<i class="bi bi-toggle-off"></i> Confirmer la désactivation';
            btn.className = 'btn btn-warning btn-lg px-4';
        } else {
            btn.innerHTML = '<i class="bi bi-trash"></i> Confirmer la suppression définitive';
            btn.className = 'btn btn-danger btn-lg px-4';
        }
    }

    // Sélectionner l'option par défaut
    document.addEventListener('DOMContentLoaded', function() {
        selectOption('desactiver');
        document.getElementById('confirm').focus();

        // Validation du formulaire
        document.getElementById('deleteForm').addEventListener('submit', function(e) {
            const confirmInput = document.getElementById('confirm');
            const action = document.querySelector('input[name="action"]:checked').value;
            const partenaireNom = "<?php echo addslashes($partenaire['nom']); ?>";

            // Vérifier "OUI"
            if (confirmInput.value.trim().toUpperCase() !== 'OUI') {
                e.preventDefault();
                alert('❌ Vous devez taper "OUI" pour confirmer.');
                confirmInput.focus();
                confirmInput.select();
                return false;
            }

            // Dernière confirmation
            let message;
            if (action === 'desactiver') {
                message = `Confirmez-vous la DÉSACTIVATION du partenaire :\n"${partenaireNom}" ?\n\n` +
                    `✅ Le partenaire sera marqué comme "inactif".\n` +
                    `✅ Vous pourrez le réactiver plus tard.`;
            } else {
                message = `⚠️ CONFIRMATION FINALE ⚠️\n\n` +
                    `Êtes-vous ABSOLUMENT CERTAIN de vouloir SUPPRIMER DÉFINITIVEMENT :\n` +
                    `"${partenaireNom}" ?\n\n` +
                    `❌ Cette action est IRRÉVERSIBLE !\n` +
                    `❌ Toutes les données seront PERDUES.\n` +
                    `❌ Vous ne pourrez pas revenir en arrière.`;
            }

            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }

            // Désactiver le bouton pendant le traitement
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Traitement en cours...';

            return true;
        });

        // Limiter à 3 caractères et convertir en majuscules
        document.getElementById('confirm').addEventListener('input', function(e) {
            this.value = this.value.toUpperCase().substring(0, 3);
        });

        // Navigation avec clavier
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.location.href = 'listePartenaires.php';
            }
        });
    });
</script>
</body>
</html>