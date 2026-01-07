<?php
# ⭐ PRIORITAIRE - Détails + missions
// Backend/views/admin/adherents/voirDetails.php

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

// Récupérer le bénévole
$stmt = $conn->prepare("SELECT * FROM benevole WHERE id_benevole = ?");
$stmt->execute([$benevole_id]);
$benevole = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier si le bénévole existe
if (!$benevole) {
    header('Location: listeAdherent.php?error=not_found');
    exit();
}

// Récupérer les éventuelles participations aux événements
// (À adapter selon tes tables)
$participations = [];
/*
try {
    $stmt = $conn->prepare("
        SELECT e.nom, e.date_debut, p.role, p.date_participation
        FROM PARTICIPATION p
        JOIN EVENEMENT e ON p.id_evenement = e.id_evenement
        WHERE p.id_benevole = ?
        ORDER BY e.date_debut DESC
    ");
    $stmt->execute([$benevole_id]);
    $participations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Table non existante ou autre erreur
}
*/

// Calculer l'âge si date de naissance
$age = null;
if (!empty($benevole['date_naissance'])) {
    $birthDate = new DateTime($benevole['date_naissance']);
    $today = new DateTime();
    $age = $today->diff($birthDate)->y;
}

// Formater les dates
$date_naissance = !empty($benevole['date_naissance'])
    ? (new DateTime($benevole['date_naissance']))->format('d/m/Y')
    : 'Non renseignée';

$date_inscription = !empty($benevole['date_inscription'])
    ? (new DateTime($benevole['date_inscription']))->format('d/m/Y')
    : 'Inconnue';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails bénévole - FAGE</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <style>
        .profile-header {
            background: linear-gradient(135deg, #183146 0%, #0d6efd 100%);
            color: white;
            border-radius: 10px 10px 0 0;
            padding: 30px;
        }
        .stat-badge {
            font-size: 0.9rem;
            padding: 5px 10px;
            border-radius: 20px;
        }
        .info-card {
            border-left: 4px solid #183146;
            transition: transform 0.2s;
        }
        .info-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .contact-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-right: 10px;
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
                <i class="bi bi-person-badge"></i> Fiche bénévole
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- En-tête avec actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="listeAdherent.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Retour à la liste
                </a>
            </div>

            <div class="btn-group">
                <a href="editFiche.php?id=<?php echo $benevole_id; ?>"
                   class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Modifier
                </a>
                <button class="btn btn-outline-danger"
                        onclick="confirmDelete(<?php echo $benevole_id; ?>, '<?php echo htmlspecialchars($benevole['prenom'] . ' ' . $benevole['nom']); ?>')">
                    <i class="bi bi-trash"></i> Supprimer
                </button>
            </div>
        </div>

        <!-- Carte principale -->
        <div class="card shadow mb-4">
            <!-- En-tête profil -->
            <div class="profile-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="h3 mb-2">
                            <?php echo htmlspecialchars($benevole['prenom'] . ' ' . $benevole['nom']); ?>
                        </h1>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="stat-badge <?php echo $benevole['statut'] == 'actif' ? 'bg-success' : 'bg-secondary'; ?>">
                                <i class="bi bi-circle-fill"></i>
                                <?php echo ucfirst($benevole['statut']); ?>
                            </span>
                            <span class="stat-badge bg-info">
                                ID : #<?php echo $benevole['id_benevole']; ?>
                            </span>
                            <?php if ($age): ?>
                            <span class="stat-badge bg-warning text-dark">
                                <i class="bi bi-balloon"></i> <?php echo $age; ?> ans
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="bg-white text-dark rounded-circle d-inline-flex align-items-center justify-content-center"
                             style="width: 80px; height: 80px;">
                            <i class="bi bi-person" style="font-size: 2.5rem; color: #183146;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Corps -->
            <div class="card-body">
                <div class="row g-4">
                    <!-- Colonne 1 : Informations personnelles -->
                    <div class="col-lg-6">
                        <h5 class="border-bottom pb-2 mb-3">
                            <i class="bi bi-person-lines-fill"></i> Informations personnelles
                        </h5>

                        <div class="info-card card mb-3">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted small">Nom complet</label>
                                        <p class="mb-0 fw-bold">
                                            <?php echo htmlspecialchars($benevole['prenom'] . ' ' . $benevole['nom']); ?>
                                        </p>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted small">Date de naissance</label>
                                        <p class="mb-0">
                                            <?php echo $date_naissance; ?>
                                            <?php if ($age): ?>
                                            <br><small class="text-muted">(<?php echo $age; ?> ans)</small>
                                            <?php endif; ?>
                                        </p>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label text-muted small">Statut</label>
                                        <p class="mb-0">
                                            <span class="badge rounded-pill <?php echo $benevole['statut'] == 'actif' ? 'bg-success' : 'bg-secondary'; ?>">
                                                <?php echo ucfirst($benevole['statut']); ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informations association -->
                        <h5 class="border-bottom pb-2 mb-3 mt-4">
                            <i class="bi bi-building"></i> Informations association
                        </h5>

                        <div class="info-card card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted small">Date d'inscription</label>
                                        <p class="mb-0 fw-bold">
                                            <?php echo $date_inscription; ?>
                                        </p>
                                        <small class="text-muted">
                                            Membre depuis
                                            <?php
                                            $inscription = new DateTime($benevole['date_inscription']);
                                            $today = new DateTime();
                                            $diff = $today->diff($inscription);
                                            echo $diff->y > 0 ? $diff->y . ' an(s)' : $diff->m . ' mois';
                                            ?>
                                        </small>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted small">Identifiant</label>
                                        <p class="mb-0">
                                            <code>#B<?php echo str_pad($benevole['id_benevole'], 4, '0', STR_PAD_LEFT); ?></code>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Colonne 2 : Contact -->
                    <div class="col-lg-6">
                        <h5 class="border-bottom pb-2 mb-3">
                            <i class="bi bi-telephone"></i> Coordonnées
                        </h5>

                        <div class="info-card card mb-4">
                            <div class="card-body">
                                <!-- Email -->
                                <div class="d-flex align-items-center mb-3">
                                    <div class="contact-icon bg-primary text-white">
                                        <i class="bi bi-envelope"></i>
                                    </div>
                                    <div>
                                        <label class="form-label text-muted small">Email</label>
                                        <?php if (!empty($benevole['email'])): ?>
                                        <p class="mb-0">
                                            <a href="mailto:<?php echo htmlspecialchars($benevole['email']); ?>"
                                               class="text-decoration-none">
                                                <?php echo htmlspecialchars($benevole['email']); ?>
                                            </a>
                                        </p>
                                        <?php else: ?>
                                        <p class="mb-0 text-muted">Non renseigné</p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Téléphone -->
                                <div class="d-flex align-items-center">
                                    <div class="contact-icon bg-success text-white">
                                        <i class="bi bi-phone"></i>
                                    </div>
                                    <div>
                                        <label class="form-label text-muted small">Téléphone</label>
                                        <?php if (!empty($benevole['telephone'])): ?>
                                        <p class="mb-0">
                                            <a href="tel:<?php echo htmlspecialchars($benevole['telephone']); ?>"
                                               class="text-decoration-none">
                                                <?php echo htmlspecialchars($benevole['telephone']); ?>
                                            </a>
                                        </p>
                                        <?php else: ?>
                                        <p class="mb-0 text-muted">Non renseigné</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <h5 class="border-bottom pb-2 mb-3">
                            <i class="bi bi-lightning"></i> Actions rapides
                        </h5>

                        <div class="row g-2">
                            <div class="col-md-6">
                                <a href="mailto:<?php echo htmlspecialchars($benevole['email']); ?>"
                                   class="btn btn-outline-primary w-100 mb-2 <?php echo empty($benevole['email']) ? 'disabled' : ''; ?>">
                                    <i class="bi bi-envelope"></i> Envoyer email
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="#" class="btn btn-outline-success w-100 mb-2">
                                    <i class="bi bi-calendar-plus"></i> Ajouter à un événement
                                </a>
                            </div>
                            <div class="col-md-12">
                                <button class="btn btn-outline-info w-100" onclick="printFiche()">
                                    <i class="bi bi-printer"></i> Imprimer cette fiche
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Historique (si données disponibles) -->
                <?php if (!empty($participations)): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <h5 class="border-bottom pb-2 mb-3">
                            <i class="bi bi-clock-history"></i> Historique des participations
                        </h5>

                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Événement</th>
                                        <th>Date</th>
                                        <th>Rôle</th>
                                        <th>Date participation</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($participations as $part): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($part['nom']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($part['date_debut'])); ?></td>
                                        <td><span class="badge bg-info"><?php echo htmlspecialchars($part['role']); ?></span></td>
                                        <td><?php echo date('d/m/Y', strtotime($part['date_participation'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Pied de carte -->
            <div class="card-footer bg-light">
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i>
                            Fiche créée le <?php echo $date_inscription; ?>
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

        <!-- Navigation basse -->
        <div class="d-flex justify-content-between mt-3">
            <?php if ($benevole_id > 1): ?>
            <a href="voirDetails.php?id=<?php echo $benevole_id - 1; ?>"
               class="btn btn-outline-secondary">
                <i class="bi bi-chevron-left"></i> Bénévole précédent
            </a>
            <?php else: ?>
            <div></div>
            <?php endif; ?>

            <?php
            // Chercher le prochain ID
            $stmt = $conn->prepare("SELECT id_benevole FROM benevole WHERE id_benevole > ? ORDER BY id_benevole ASC LIMIT 1");
            $stmt->execute([$benevole_id]);
            $next = $stmt->fetchColumn();
            ?>

            <?php if ($next): ?>
            <a href="voirDetails.php?id=<?php echo $next; ?>"
               class="btn btn-outline-secondary">
                Bénévole suivant <i class="bi bi-chevron-right"></i>
            </a>
            <?php else: ?>
            <div></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // Confirmation suppression
    function confirmDelete(id, nom) {
        if (confirm(`Voulez-vous vraiment supprimer le bénévole "${nom}" ?\n\nCette action est irréversible.`)) {
            window.location.href = 'deleteBenevole.php?id=' + id;
        }
    }

    // Impression de la fiche
    function printFiche() {
        window.print();
    }

    // Calcul âge dynamique
    function calculateAge(birthDate) {
        const today = new Date();
        const birth = new Date(birthDate);
        let age = today.getFullYear() - birth.getFullYear();
        const m = today.getMonth() - birth.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) {
            age--;
        }
        return age;
    }

    // Message si email manquant
    document.addEventListener('DOMContentLoaded', function() {
        const emailBtn = document.querySelector('a[href^="mailto:"]');
        if (emailBtn && emailBtn.classList.contains('disabled')) {
            emailBtn.addEventListener('click', function(e) {
                e.preventDefault();
                alert("Email non renseigné pour ce bénévole");
            });
        }
    });
    </script>
</body>
</html>