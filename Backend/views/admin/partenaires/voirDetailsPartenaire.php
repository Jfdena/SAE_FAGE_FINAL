<?php
// Backend/views/admin/partenaires/voirDetailsPartenaire.php

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
$conn = $db->getConnection();

// Récupérer le partenaire
$stmt = $conn->prepare("SELECT * FROM Partenaire WHERE id_partenaire = ?");
$stmt->execute([$partenaire_id]);
$partenaire = $stmt->fetch();

// Vérifier si le partenaire existe
if (!$partenaire) {
    header('Location: listePartenaires.php?error=not_found');
    exit();
}

// Formater les dates
$date_contribution = !empty($partenaire['date_contribution'])
    ? date('d/m/Y', strtotime($partenaire['date_contribution']))
    : 'Non spécifiée';

$date_creation = !empty($partenaire['date_creation'])
    ? date('d/m/Y H:i', strtotime($partenaire['date_creation']))
    : 'Non spécifiée';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails partenaire - FAGE</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <style>
        .partner-header {
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
        .contact-card {
            border-left: 4px solid #183146;
        }
        .montant-big {
            font-size: 2.5rem;
            font-weight: 700;
            color: #198754;
        }
        .type-badge {
            font-size: 0.9rem;
            padding: 8px 15px;
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
            <i class="bi bi-handshake"></i> Détails partenaire
        </div>
    </div>
</nav>

<div class="container py-4">
    <!-- En-tête avec actions -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="listePartenaires.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Retour à la liste
            </a>
        </div>

        <div class="btn-group">
            <button class="btn btn-warning" onclick="window.location.href='editPartenaire.php?id=<?php echo $partenaire_id; ?>'">
                <i class="bi bi-pencil"></i> Modifier
            </button>
            <a href="addPartenaire.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nouveau partenaire
            </a>
        </div>
    </div>

    <!-- Carte principale -->
    <div class="card shadow mb-4">
        <!-- En-tête partenaire -->
        <div class="partner-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="h3 mb-2">
                        <?php echo htmlspecialchars($partenaire['nom']); ?>
                    </h1>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="badge <?php echo $partenaire['type'] === 'donateur' ? 'bg-primary' : 'bg-warning text-dark'; ?> type-badge">
                            <i class="bi bi-<?php echo $partenaire['type'] === 'donateur' ? 'cash-coin' : 'building'; ?>"></i>
                            <?php echo $partenaire['type'] === 'donateur' ? 'Donateur' : 'Subvention'; ?>
                        </span>
                        <span class="badge <?php echo $partenaire['statut'] === 'actif' ? 'bg-success' : 'bg-secondary'; ?> type-badge">
                            <i class="bi bi-circle-fill"></i>
                            <?php echo $partenaire['statut'] === 'actif' ? 'Actif' : 'Inactif'; ?>
                        </span>
                        <span class="badge bg-light text-dark type-badge">
                            ID : #<?php echo $partenaire_id; ?>
                        </span>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="bg-white text-dark rounded-circle d-inline-flex align-items-center justify-content-center"
                         style="width: 80px; height: 80px;">
                        <i class="bi bi-handshake" style="font-size: 2.5rem; color: #183146;"></i>
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
                    <?php if (!empty($partenaire['description'])): ?>
                        <div class="info-box mb-4">
                            <h5><i class="bi bi-card-text"></i> Description</h5>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($partenaire['description'])); ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Contact -->
                    <div class="info-box contact-card mb-4">
                        <h5><i class="bi bi-person-lines-fill"></i> Contact</h5>
                        <div class="row">
                            <?php if (!empty($partenaire['contact_email'])): ?>
                                <div class="col-md-6 mb-3">
                                    <strong><i class="bi bi-envelope"></i> Email :</strong><br>
                                    <a href="mailto:<?php echo htmlspecialchars($partenaire['contact_email']); ?>">
                                        <?php echo htmlspecialchars($partenaire['contact_email']); ?>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($partenaire['contact_telephone'])): ?>
                                <div class="col-md-6 mb-3">
                                    <strong><i class="bi bi-telephone"></i> Téléphone :</strong><br>
                                    <a href="tel:<?php echo htmlspecialchars($partenaire['contact_telephone']); ?>">
                                        <?php echo htmlspecialchars($partenaire['contact_telephone']); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (empty($partenaire['contact_email']) && empty($partenaire['contact_telephone'])): ?>
                            <p class="text-muted mb-0">
                                <i class="bi bi-info-circle"></i> Aucune information de contact renseignée
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Historique (à compléter plus tard) -->
                    <div class="info-box">
                        <h5><i class="bi bi-clock-history"></i> Historique</h5>
                        <p class="text-muted mb-0">
                            <i>Fonctionnalité à venir : suivi des contributions et interactions.</i>
                        </p>
                    </div>
                </div>

                <!-- Colonne 2 : Statistiques et informations financières -->
                <div class="col-lg-4">
                    <!-- Montant -->
                    <div class="info-box mb-4 text-center">
                        <h5><i class="bi bi-cash-coin"></i> Contribution</h5>
                        <?php if (!empty($partenaire['montant'])): ?>
                            <div class="montant-big">
                                <?php echo number_format($partenaire['montant'], 2, ',', ' '); ?> €
                            </div>
                            <?php if (!empty($partenaire['date_contribution'])): ?>
                                <p class="text-muted mb-0">
                                    Versé le <?php echo $date_contribution; ?>
                                </p>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="text-muted">
                                <i class="bi bi-dash-circle" style="font-size: 2rem;"></i><br>
                                Montant non spécifié
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Dates -->
                    <div class="info-box mb-4">
                        <h5><i class="bi bi-calendar"></i> Dates</h5>
                        <div class="mb-2">
                            <strong>Date de création :</strong><br>
                            <?php echo $date_creation; ?>
                        </div>
                        <?php if (!empty($partenaire['date_contribution'])): ?>
                            <div class="mb-0">
                                <strong>Date de contribution :</strong><br>
                                <?php echo $date_contribution; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Actions rapides -->
                    <div class="info-box">
                        <h5><i class="bi bi-lightning"></i> Actions rapides</h5>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary" onclick="window.location.href='mailto:<?php echo htmlspecialchars($partenaire['contact_email'] ?? ''); ?>'">
                                <i class="bi bi-envelope"></i> Envoyer un email
                            </button>
                            <button class="btn btn-outline-success" onclick="window.print()">
                                <i class="bi bi-printer"></i> Imprimer cette fiche
                            </button>
                            <button class="btn btn-outline-info" onclick="copyPartnerInfo()">
                                <i class="bi bi-clipboard"></i> Copier les infos
                            </button>
                            <button class="btn btn-outline-danger" onclick="confirmDelete()">
                                <i class="bi bi-trash"></i> Supprimer
                            </button>
                        </div>
                    </div>

                    <!-- Métriques système -->
                    <div class="info-box mt-4">
                        <h5><i class="bi bi-bar-chart"></i> Métriques</h5>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="mb-1">
                                    <i class="bi bi-calendar-check" style="font-size: 1.5rem; color: #0d6efd;"></i>
                                </div>
                                <div class="small">Créé il y a</div>
                                <div class="fw-bold">
                                    <?php
                                    if (!empty($partenaire['date_creation'])) {
                                        $date1 = new DateTime($partenaire['date_creation']);
                                        $date2 = new DateTime();
                                        $interval = $date1->diff($date2);
                                        echo $interval->days . ' jours';
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-1">
                                    <i class="bi bi-arrow-repeat" style="font-size: 1.5rem; color: #198754;"></i>
                                </div>
                                <div class="small">Statut</div>
                                <div class="fw-bold">
                                    <?php echo $partenaire['statut'] === 'actif' ? 'Actif' : 'Inactif'; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes additionnelles (section extensible) -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="info-box">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">
                                <i class="bi bi-sticky"></i> Notes additionnelles
                            </h5>
                            <button class="btn btn-sm btn-outline-primary" onclick="addNote()">
                                <i class="bi bi-plus"></i> Ajouter une note
                            </button>
                        </div>
                        <div class="text-center py-3">
                            <i class="bi bi-journal-text" style="font-size: 2rem; color: #6c757d;"></i>
                            <p class="text-muted mb-0 mt-2">Aucune note pour le moment</p>
                            <small class="text-muted">Cette fonctionnalité sera disponible prochainement</small>
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
                        Fiche partenaire générée automatiquement
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

    <!-- Navigation entre partenaires -->
    <div class="d-flex justify-content-between">
        <?php
        // Chercher le partenaire précédent
        $stmt = $conn->prepare("
            SELECT id_partenaire, nom 
            FROM Partenaire 
            WHERE id_partenaire < ? 
            ORDER BY id_partenaire DESC 
            LIMIT 1
        ");
        $stmt->execute([$partenaire_id]);
        $prev = $stmt->fetch(PDO::FETCH_ASSOC);
        ?>

        <?php if ($prev): ?>
            <a href="voirDetailsPartenaire.php?id=<?php echo $prev['id_partenaire']; ?>"
               class="btn btn-outline-secondary">
                <i class="bi bi-chevron-left"></i>
                <?php echo htmlspecialchars(substr($prev['nom'], 0, 20)); ?>
                <?php if (strlen($prev['nom']) > 20) echo '...'; ?>
            </a>
        <?php else: ?>
            <div></div>
        <?php endif; ?>

        <?php
        // Chercher le partenaire suivant
        $stmt = $conn->prepare("
            SELECT id_partenaire, nom 
            FROM Partenaire 
            WHERE id_partenaire > ? 
            ORDER BY id_partenaire ASC 
            LIMIT 1
        ");
        $stmt->execute([$partenaire_id]);
        $next = $stmt->fetch(PDO::FETCH_ASSOC);
        ?>

        <?php if ($next): ?>
            <a href="voirDetailsPartenaire.php?id=<?php echo $next['id_partenaire']; ?>"
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
        // Gestionnaire d'impression
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
    });

    // Copier les informations du partenaire
    function copyPartnerInfo() {
        const nom = '<?php echo addslashes($partenaire["nom"]); ?>';
        const type = '<?php echo $partenaire["type"] === "donateur" ? "Donateur" : "Subvention"; ?>';
        const montant = '<?php echo $partenaire["montant"] ? number_format($partenaire["montant"], 2, ",", " ") . " €" : "Non spécifié"; ?>';
        const email = '<?php echo addslashes($partenaire["contact_email"] ?? ""); ?>';
        const telephone = '<?php echo addslashes($partenaire["contact_telephone"] ?? ""); ?>';

        const text = `Partenaire FAGE : ${nom}\nType : ${type}\nMontant : ${montant}\nEmail : ${email}\nTéléphone : ${telephone}`;

        navigator.clipboard.writeText(text).then(function() {
            alert('Informations copiées dans le presse-papier !');
        }, function() {
            // Fallback pour anciens navigateurs
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            alert('Informations copiées dans le presse-papier !');
        });
    }

    // Confirmation de suppression
    function confirmDelete() {
        const nom = '<?php echo addslashes($partenaire["nom"]); ?>';
        const id = <?php echo $partenaire_id; ?>;

        if (confirm(`Voulez-vous vraiment supprimer le partenaire "${nom}" ?\n\nCette action ouvrira une page de confirmation.`)) {
            window.location.href = `deletePartenaire.php?id=${id}`;
        }
    }

    // Ajouter une note (placeholder)
    function addNote() {
        alert('Cette fonctionnalité sera disponible dans une version future.');
    }

    // Navigation clavier
    document.addEventListener('keydown', function(e) {
        switch(e.key) {
            case 'ArrowLeft':
            <?php if ($prev): ?>
                window.location.href = 'voirDetailsPartenaire.php?id=<?php echo $prev["id_partenaire"]; ?>';
            <?php endif; ?>
                break;
            case 'ArrowRight':
            <?php if ($next): ?>
                window.location.href = 'voirDetailsPartenaire.php?id=<?php echo $next["id_partenaire"]; ?>';
            <?php endif; ?>
                break;
            case 'Escape':
                window.location.href = 'listePartenaires.php';
                break;
            case 'e':
                if (e.ctrlKey) {
                    window.location.href = 'editPartenaire.php?id=<?php echo $partenaire_id; ?>';
                }
                break;
        }
    });
</script>
</body>
</html>