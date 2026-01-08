<?php
// Backend/views/admin/partenaires/HistoContribution.php

// Protection
require_once '../../../test/session_check.php';

// Connexion BDD
require_once '../../../config/Database.php';
$db = new Database();
$conn = $db->getConnection();

// Paramètres de filtrage
$partenaire_id = $_GET['partenaire_id'] ?? '';
$annee = $_GET['annee'] ?? date('Y');
$type = $_GET['type'] ?? '';
$mois = $_GET['mois'] ?? '';

// Récupérer tous les partenaires pour le filtre
$partenaires = [];
try {
    $stmt = $conn->query("SELECT id_partenaire, nom FROM Partenaire ORDER BY nom");
    $partenaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Erreur récupération partenaires: " . $e->getMessage());
}

// Construction de la requête pour l'historique
$sql = "SELECT 
            p.id_partenaire,
            p.nom,
            p.type,
            p.montant,
            p.date_contribution,
            p.statut,
            YEAR(p.date_contribution) as annee_contribution,
            MONTH(p.date_contribution) as mois_contribution
        FROM Partenaire p
        WHERE p.montant IS NOT NULL AND p.date_contribution IS NOT NULL";
$params = [];

if (!empty($partenaire_id)) {
    $sql .= " AND p.id_partenaire = ?";
    $params[] = $partenaire_id;
}

if (!empty($type)) {
    $sql .= " AND p.type = ?";
    $params[] = $type;
}

if (!empty($annee)) {
    $sql .= " AND YEAR(p.date_contribution) = ?";
    $params[] = $annee;
}

if (!empty($mois)) {
    $sql .= " AND MONTH(p.date_contribution) = ?";
    $params[] = $mois;
}

$sql .= " ORDER BY p.date_contribution DESC";

// Exécuter la requête
try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $contributions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $contributions = [];
    error_log("Erreur récupération contributions: " . $e->getMessage());
}

// Calcul des statistiques
$stats = [
    'total_contributions' => count($contributions),
    'total_montant' => 0,
    'moyenne_montant' => 0,
    'max_montant' => 0,
    'min_montant' => PHP_INT_MAX,
    'par_type' => ['donateur' => 0, 'subvention' => 0],
    'par_mois' => array_fill(1, 12, 0)
];

foreach ($contributions as $contribution) {
    $montant = (float)$contribution['montant'];
    $stats['total_montant'] += $montant;

    if ($montant > $stats['max_montant']) {
        $stats['max_montant'] = $montant;
    }

    if ($montant < $stats['min_montant']) {
        $stats['min_montant'] = $montant;
    }

    $stats['par_type'][$contribution['type']] += $montant;

    if (!empty($contribution['mois_contribution'])) {
        $stats['par_mois'][(int)$contribution['mois_contribution']] += $montant;
    }
}

if ($stats['total_contributions'] > 0) {
    $stats['moyenne_montant'] = $stats['total_montant'] / $stats['total_contributions'];
    if ($stats['min_montant'] == PHP_INT_MAX) {
        $stats['min_montant'] = 0;
    }
}

// Années disponibles pour le filtre
$annees = [];
for ($i = date('Y'); $i >= 2020; $i--) {
    $annees[] = $i;
}

// Mois
$mois_noms = [
    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
    5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
    9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique des contributions - FAGE</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .stat-card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-3px);
        }
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }
        .montant-cell {
            font-weight: 600;
        }
        .montant-donateur {
            color: #0d6efd;
        }
        .montant-subvention {
            color: #fd7e14;
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 20px;
        }
        .badge-contribution {
            font-size: 0.8rem;
            padding: 4px 8px;
        }
        .filters-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
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
            <i class="bi bi-cash-stack"></i> Historique des contributions
        </div>
    </div>
</nav>

<div class="container-fluid py-4">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="bi bi-cash-stack"></i> Historique des contributions</h2>
            <p class="text-muted mb-0">
                Suivi des dons et subventions des partenaires FAGE
            </p>
        </div>
        <div class="btn-group">
            <a href="listePartenaires.php" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Liste des partenaires
            </a>
            <button class="btn btn-success" onclick="exportToExcel()">
                <i class="bi bi-file-earmark-excel"></i> Exporter
            </button>
        </div>
    </div>

    <!-- Statistiques globales -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted">Total contributions</h6>
                            <h2 class="mb-0"><?php echo $stats['total_contributions']; ?></h2>
                        </div>
                        <div class="bg-primary text-white rounded-circle p-3">
                            <i class="bi bi-cash-coin fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted">Montant total</h6>
                            <h3 class="mb-0"><?php echo number_format($stats['total_montant'], 0, ',', ' '); ?> €</h3>
                        </div>
                        <div class="bg-success text-white rounded-circle p-3">
                            <i class="bi bi-bar-chart fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted">Moyenne</h6>
                            <h3 class="mb-0"><?php echo number_format($stats['moyenne_montant'], 0, ',', ' '); ?> €</h3>
                        </div>
                        <div class="bg-info text-white rounded-circle p-3">
                            <i class="bi bi-calculator fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted">Plus élevée</h6>
                            <h3 class="mb-0"><?php echo number_format($stats['max_montant'], 0, ',', ' '); ?> €</h3>
                        </div>
                        <div class="bg-warning text-white rounded-circle p-3">
                            <i class="bi bi-arrow-up fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card shadow mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtres</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-4">
                    <label for="partenaire_id" class="form-label">Partenaire</label>
                    <select id="partenaire_id" name="partenaire_id" class="form-select">
                        <option value="">Tous les partenaires</option>
                        <?php foreach ($partenaires as $p): ?>
                            <option value="<?php echo $p['id_partenaire']; ?>"
                                <?php echo $partenaire_id == $p['id_partenaire'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($p['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="type" class="form-label">Type</label>
                    <select id="type" name="type" class="form-select">
                        <option value="">Tous types</option>
                        <option value="donateur" <?php echo $type == 'donateur' ? 'selected' : ''; ?>>Donateur</option>
                        <option value="subvention" <?php echo $type == 'subvention' ? 'selected' : ''; ?>>Subvention</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="annee" class="form-label">Année</label>
                    <select id="annee" name="annee" class="form-select">
                        <?php foreach ($annees as $a): ?>
                            <option value="<?php echo $a; ?>" <?php echo $annee == $a ? 'selected' : ''; ?>>
                                <?php echo $a; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="mois" class="form-label">Mois</label>
                    <select id="mois" name="mois" class="form-select">
                        <option value="">Tous les mois</option>
                        <?php foreach ($mois_noms as $num => $nom): ?>
                            <option value="<?php echo $num; ?>" <?php echo $mois == $num ? 'selected' : ''; ?>>
                                <?php echo $nom; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-filter"></i> Appliquer les filtres
                        </button>
                        <a href="HistoContribution.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Réinitialiser
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Répartition par type</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="typeChart"></canvas>
                    </div>
                    <div class="text-center">
                        <span class="badge bg-primary me-3">
                            <i class="bi bi-circle-fill"></i> Donateurs : <?php echo number_format($stats['par_type']['donateur'], 0, ',', ' '); ?> €
                        </span>
                        <span class="badge bg-warning text-dark">
                            <i class="bi bi-circle-fill"></i> Subventions : <?php echo number_format($stats['par_type']['subvention'], 0, ',', ' '); ?> €
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Évolution mensuelle (<?php echo $annee; ?>)</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau des contributions -->
    <div class="card shadow">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-table"></i> Liste des contributions
                <span class="badge bg-primary ms-2"><?php echo $stats['total_contributions']; ?></span>
            </h5>
            <div class="text-muted">
                Total filtré : <strong><?php echo number_format($stats['total_montant'], 2, ',', ' '); ?> €</strong>
            </div>
        </div>

        <div class="card-body">
            <?php if (empty($contributions)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-cash-stack" style="font-size: 4rem; color: #6c757d;"></i>
                    <h4 class="text-muted mt-3">Aucune contribution trouvée</h4>
                    <p class="text-muted">
                        <?php echo !empty($partenaire_id) || !empty($type) || !empty($mois)
                            ? 'Aucune contribution ne correspond à vos critères de recherche.'
                            : 'Aucun partenaire n\'a encore effectué de contribution.'; ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>Date</th>
                            <th>Partenaire</th>
                            <th>Type</th>
                            <th>Montant</th>
                            <th>Année</th>
                            <th>Mois</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($contributions as $contribution):
                            $date_formatted = date('d/m/Y', strtotime($contribution['date_contribution']));
                            $montant_formatted = number_format($contribution['montant'], 2, ',', ' ') . ' €';
                            $mois_nom = $mois_noms[$contribution['mois_contribution']] ?? 'N/A';
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo $date_formatted; ?></strong>
                                </td>
                                <td>
                                    <a href="voirDetailsPartenaire.php?id=<?php echo $contribution['id_partenaire']; ?>"
                                       class="text-decoration-none">
                                        <?php echo htmlspecialchars($contribution['nom']); ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge <?php echo $contribution['type'] === 'donateur' ? 'bg-primary' : 'bg-warning text-dark'; ?>">
                                        <?php echo $contribution['type'] === 'donateur' ? 'Donateur' : 'Subvention'; ?>
                                    </span>
                                </td>
                                <td class="montant-cell <?php echo $contribution['type'] === 'donateur' ? 'montant-donateur' : 'montant-subvention'; ?>">
                                    <strong><?php echo $montant_formatted; ?></strong>
                                </td>
                                <td>
                                    <?php echo $contribution['annee_contribution']; ?>
                                </td>
                                <td>
                                    <?php echo $mois_nom; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $contribution['statut'] === 'actif' ? 'success' : 'secondary'; ?>">
                                        <?php echo $contribution['statut'] === 'actif' ? 'Actif' : 'Inactif'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="voirDetailsPartenaire.php?id=<?php echo $contribution['id_partenaire']; ?>"
                                           class="btn btn-outline-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <button class="btn btn-outline-secondary" onclick="showContributionDetails(<?php echo $contribution['id_partenaire']; ?>, '<?php echo addslashes($contribution['nom']); ?>', '<?php echo $date_formatted; ?>', '<?php echo $montant_formatted; ?>')">
                                            <i class="bi bi-info-circle"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                        <tr class="table-active">
                            <td colspan="3" class="text-end"><strong>Total :</strong></td>
                            <td class="montant-cell">
                                <strong><?php echo number_format($stats['total_montant'], 2, ',', ' '); ?> €</strong>
                            </td>
                            <td colspan="4"></td>
                        </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Résumé statistique -->
                <div class="alert alert-info mt-3">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <small>Contribution moyenne</small><br>
                            <strong><?php echo number_format($stats['moyenne_montant'], 2, ',', ' '); ?> €</strong>
                        </div>
                        <div class="col-md-3 text-center">
                            <small>Plus élevée</small><br>
                            <strong><?php echo number_format($stats['max_montant'], 2, ',', ' '); ?> €</strong>
                        </div>
                        <div class="col-md-3 text-center">
                            <small>Plus basse</small><br>
                            <strong><?php echo number_format($stats['min_montant'], 2, ',', ' '); ?> €</strong>
                        </div>
                        <div class="col-md-3 text-center">
                            <small>Contributions/partenaire</small><br>
                            <strong>
                                <?php echo $partenaires ? number_format($stats['total_contributions'] / count($partenaires), 1) : '0'; ?>
                            </strong>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="card-footer bg-light">
            <div class="row">
                <div class="col-md-6">
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i>
                        Les contributions sans date ou montant ne sont pas affichées.
                    </small>
                </div>
                <div class="col-md-6 text-end">
                    <small class="text-muted">
                        <?php echo date('d/m/Y H:i'); ?> •
                        Données mises à jour en temps réel
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour détails contribution -->
<div class="modal fade" id="contributionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Détails de la contribution</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="contributionDetails"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Données pour les graphiques
    const typeData = {
        labels: ['Donateurs', 'Subventions'],
        datasets: [{
            data: [
                <?php echo $stats['par_type']['donateur']; ?>,
                <?php echo $stats['par_type']['subvention']; ?>
            ],
            backgroundColor: ['#0d6efd', '#fd7e14'],
            borderWidth: 1
        }]
    };

    const monthlyData = {
        labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'],
        datasets: [{
            label: 'Contributions (€)',
            data: [
                <?php echo $stats['par_mois'][1]; ?>,
                <?php echo $stats['par_mois'][2]; ?>,
                <?php echo $stats['par_mois'][3]; ?>,
                <?php echo $stats['par_mois'][4]; ?>,
                <?php echo $stats['par_mois'][5]; ?>,
                <?php echo $stats['par_mois'][6]; ?>,
                <?php echo $stats['par_mois'][7]; ?>,
                <?php echo $stats['par_mois'][8]; ?>,
                <?php echo $stats['par_mois'][9]; ?>,
                <?php echo $stats['par_mois'][10]; ?>,
                <?php echo $stats['par_mois'][11]; ?>,
                <?php echo $stats['par_mois'][12]; ?>
            ],
            backgroundColor: 'rgba(13, 110, 253, 0.2)',
            borderColor: 'rgba(13, 110, 253, 1)',
            borderWidth: 2,
            tension: 0.4
        }]
    };

    // Initialiser les graphiques
    document.addEventListener('DOMContentLoaded', function() {
        // Graphique circulaire (type)
        const typeCtx = document.getElementById('typeChart').getContext('2d');
        new Chart(typeCtx, {
            type: 'pie',
            data: typeData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += new Intl.NumberFormat('fr-FR', {
                                    style: 'currency',
                                    currency: 'EUR'
                                }).format(context.parsed);
                                return label;
                            }
                        }
                    }
                }
            }
        });

        // Graphique mensuel
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'line',
            data: monthlyData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('fr-FR', {
                                    style: 'currency',
                                    currency: 'EUR',
                                    minimumFractionDigits: 0
                                }).format(value);
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return new Intl.NumberFormat('fr-FR', {
                                    style: 'currency',
                                    currency: 'EUR'
                                }).format(context.parsed.y);
                            }
                        }
                    }
                }
            }
        });

        // Initialiser le modal
        const contributionModal = new bootstrap.Modal(document.getElementById('contributionModal'));
    });

    // Afficher les détails d'une contribution
    function showContributionDetails(partenaireId, partenaireNom, date, montant) {
        const details = `
            <div class="mb-3">
                <h6>Partenaire :</h6>
                <p class="mb-0">${partenaireNom}</p>
                <a href="voirDetailsPartenaire.php?id=${partenaireId}" class="btn btn-sm btn-outline-primary mt-1">
                    <i class="bi bi-eye"></i> Voir fiche complète
                </a>
            </div>
            <div class="mb-3">
                <h6>Date de contribution :</h6>
                <p class="mb-0">${date}</p>
            </div>
            <div class="mb-3">
                <h6>Montant :</h6>
                <p class="mb-0 fs-5 text-success">${montant}</p>
            </div>
            <div class="alert alert-info">
                <small>
                    <i class="bi bi-info-circle"></i>
                    Ces informations sont extraites de la fiche du partenaire.
                    Pour modifier ces données, éditez la fiche du partenaire.
                </small>
            </div>
        `;

        document.getElementById('contributionDetails').innerHTML = details;

        const modal = new bootstrap.Modal(document.getElementById('contributionModal'));
        modal.show();
    }

    // Export Excel (basique)
    function exportToExcel() {
        const table = document.querySelector('table');
        if (!table) {
            alert('Aucune donnée à exporter.');
            return;
        }

        // Créer une chaîne CSV simple
        let csv = [];
        const rows = table.querySelectorAll('tr');

        for (let i = 0; i < rows.length; i++) {
            const row = [], cols = rows[i].querySelectorAll('td, th');

            for (let j = 0; j < cols.length; j++) {
                let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, '').replace(/(\s\s)/gm, ' ');
                data = data.replace(/"/g, '""');
                row.push('"' + data + '"');
            }

            csv.push(row.join(';'));
        }

        const csvString = csv.join('\n');
        const filename = `contributions_fage_${new Date().toISOString().slice(0,10)}.csv`;

        const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');

        if (navigator.msSaveBlob) {
            navigator.msSaveBlob(blob, filename);
        } else {
            link.href = URL.createObjectURL(blob);
            link.download = filename;
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        alert('Export terminé. Le fichier CSV a été téléchargé.');
    }

    // Navigation rapide avec clavier
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            window.location.href = 'listePartenaires.php';
        }
        if (e.key === 'r' && e.ctrlKey) {
            e.preventDefault();
            window.location.href = 'HistoContribution.php';
        }
    });
</script>
</body>
</html>