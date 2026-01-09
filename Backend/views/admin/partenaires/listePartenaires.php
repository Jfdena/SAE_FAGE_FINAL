    <?php
    // Backend/views/admin/partenaires/listePartenaires.php

    // Protection
    require_once '../../../session_check.php';

    // Connexion BDD
    require_once '../../../config/Database.php';
    $db = new Database();
    $conn = $db->getConnection();

    // Variables pour la recherche et le filtrage
    $search = $_GET['search'] ?? '';
    $type = $_GET['type'] ?? '';
    $statut = $_GET['statut'] ?? '';
    $date_debut = $_GET['date_debut'] ?? '';
    $date_fin = $_GET['date_fin'] ?? '';

    // Construction de la requête
    $sql = "SELECT * FROM Partenaire WHERE 1=1";
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (nom LIKE ? OR contact_email LIKE ? OR description LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    if (!empty($type)) {
        $sql .= " AND type = ?";
        $params[] = $type;
    }

    if (!empty($statut)) {
        $sql .= " AND statut = ?";
        $params[] = $statut;
    }

    if (!empty($date_debut)) {
        $sql .= " AND date_contribution >= ?";
        $params[] = $date_debut;
    }

    if (!empty($date_fin)) {
        $sql .= " AND date_contribution <= ?";
        $params[] = $date_fin;
    }

    $sql .= " ORDER BY date_creation DESC";

    // Exécuter la requête
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->get_result();
        $partenaires = $result->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        $partenaires = [];
        error_log("Erreur lors de la récupération des partenaires: " . $e->getMessage());
    }

    // Calcul des statistiques
    $stats = [
        'total' => count($partenaires),
        'donateurs' => 0,
        'subventions' => 0,
        'actifs' => 0,
        'total_montant' => 0
    ];

    foreach ($partenaires as $p) {
        if ($p['type'] === 'donateur') $stats['donateurs']++;
        if ($p['type'] === 'subvention') $stats['subventions']++;
        if ($p['statut'] === 'actif') $stats['actifs']++;
        if ($p['montant']) $stats['total_montant'] += (float)$p['montant'];
    }
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Liste des partenaires - FAGE</title>

        <!-- Bootstrap -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

        <!-- DataTables CSS -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

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
            .badge-donateur {
                background-color: #0d6efd;
            }
            .badge-subvention {
                background-color: #fd7e14;
            }
            .badge-actif {
                background-color: #198754;
            }
            .badge-inactif {
                background-color: #6c757d;
            }
            .table-hover tbody tr:hover {
                background-color: #f8f9fa;
                cursor: pointer;
            }
            .search-box {
                background: #f8f9fa;
                border-radius: 10px;
                padding: 20px;
                margin-bottom: 20px;
            }
            .montant-cell {
                font-weight: 600;
                color: #198754;
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
                <i class="bi bi-handshake"></i> Liste des partenaires
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- En-tête avec actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="bi bi-handshake"></i> Partenaires & donateurs</h2>
                <p class="text-muted mb-0">
                    Gérez les partenaires, donateurs et subventions de la FAGE
                </p>
            </div>
            <div class="btn-group">
                <a href="addPartenaire.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Nouveau partenaire
                </a>
                <a href="../dashboard.php" class="btn btn-outline-secondary">
                    <i class="bi bi-house"></i> Dashboard
                </a>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card border-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted">Total partenaires</h6>
                                <h2 class="mb-0"><?php echo $stats['total']; ?></h2>
                            </div>
                            <div class="bg-primary text-white rounded-circle p-3">
                                <i class="bi bi-people fs-4"></i>
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
                                <h6 class="text-muted">Donateurs</h6>
                                <h2 class="mb-0"><?php echo $stats['donateurs']; ?></h2>
                            </div>
                            <div class="bg-success text-white rounded-circle p-3">
                                <i class="bi bi-cash-coin fs-4"></i>
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
                                <h6 class="text-muted">Subventions</h6>
                                <h2 class="mb-0"><?php echo $stats['subventions']; ?></h2>
                            </div>
                            <div class="bg-warning text-white rounded-circle p-3">
                                <i class="bi bi-building fs-4"></i>
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
                                <h6 class="text-muted">Montant total</h6>
                                <h3 class="mb-0"><?php echo number_format($stats['total_montant'], 2, ',', ' '); ?> €</h3>
                            </div>
                            <div class="bg-info text-white rounded-circle p-3">
                                <i class="bi bi-bar-chart fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Boîte de recherche et filtres -->
        <div class="card shadow mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-search"></i> Recherche et filtres</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Recherche</label>
                        <input type="text"
                               id="search"
                               name="search"
                               class="form-control"
                               value="<?php echo htmlspecialchars($search); ?>"
                               placeholder="Nom, email, description...">
                    </div>

                    <div class="col-md-2">
                        <label for="type" class="form-label">Type</label>
                        <select id="type" name="type" class="form-select">
                            <option value="">Tous les types</option>
                            <option value="donateur" <?php echo $type == 'donateur' ? 'selected' : ''; ?>>Donateur</option>
                            <option value="subvention" <?php echo $type == 'subvention' ? 'selected' : ''; ?>>Subvention</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="statut" class="form-label">Statut</label>
                        <select id="statut" name="statut" class="form-select">
                            <option value="">Tous statuts</option>
                            <option value="actif" <?php echo $statut == 'actif' ? 'selected' : ''; ?>>Actif</option>
                            <option value="inactif" <?php echo $statut == 'inactif' ? 'selected' : ''; ?>>Inactif</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="date_debut" class="form-label">Date début</label>
                        <input type="date"
                               id="date_debut"
                               name="date_debut"
                               class="form-control"
                               value="<?php echo htmlspecialchars($date_debut); ?>">
                    </div>

                    <div class="col-md-2">
                        <label for="date_fin" class="form-label">Date fin</label>
                        <input type="date"
                               id="date_fin"
                               name="date_fin"
                               class="form-control"
                               value="<?php echo htmlspecialchars($date_fin); ?>">
                    </div>

                    <div class="col-12">
                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-filter"></i> Appliquer les filtres
                            </button>
                            <a href="listePartenaires.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Réinitialiser
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tableau des partenaires -->
        <div class="card shadow">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-table"></i> Liste des partenaires
                    <span class="badge bg-primary ms-2"><?php echo $stats['total']; ?></span>
                </h5>
                <div>
                    <button class="btn btn-sm btn-outline-success" onclick="exportToExcel()">
                        <i class="bi bi-file-earmark-excel"></i> Exporter
                    </button>
                </div>
            </div>

            <div class="card-body">
                <?php if (empty($partenaires)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-people" style="font-size: 4rem; color: #6c757d;"></i>
                        <h4 class="text-muted mt-3">Aucun partenaire trouvé</h4>
                        <p class="text-muted">
                            <?php echo !empty($search) || !empty($type) || !empty($statut)
                                ? 'Aucun partenaire ne correspond à vos critères de recherche.'
                                : 'Commencez par ajouter votre premier partenaire.'; ?>
                        </p>
                        <a href="addPartenaire.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Ajouter un partenaire
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="partenairesTable">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Type</th>
                                <th>Montant</th>
                                <th>Date contribution</th>
                                <th>Contact</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($partenaires as $partenaire):
                                $montant = $partenaire['montant'] ? number_format($partenaire['montant'], 2, ',', ' ') . ' €' : '-';
                                ?>
                                <tr onclick="window.location.href='#'" style="cursor: pointer;">
                                    <td>#<?php echo $partenaire['id_partenaire']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($partenaire['nom']); ?></strong>
                                        <?php if (!empty($partenaire['description'])): ?>
                                            <br><small class="text-muted"><?php echo substr(htmlspecialchars($partenaire['description']), 0, 50); ?>...</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $partenaire['type']; ?>">
                                            <?php echo $partenaire['type'] === 'donateur' ? 'Donateur' : 'Subvention'; ?>
                                        </span>
                                    </td>
                                    <td class="montant-cell"><?php echo $montant; ?></td>
                                    <td>
                                        <?php echo $partenaire['date_contribution']
                                            ? date('d/m/Y', strtotime($partenaire['date_contribution']))
                                            : '-'; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($partenaire['contact_email'])): ?>
                                            <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($partenaire['contact_email']); ?><br>
                                        <?php endif; ?>
                                        <?php if (!empty($partenaire['contact_telephone'])): ?>
                                            <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($partenaire['contact_telephone']); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $partenaire['statut']; ?>">
                                            <?php echo $partenaire['statut'] === 'actif' ? 'Actif' : 'Inactif'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-info"
                                                    onclick="event.stopPropagation(); window.location.href='voirDetailsPartenaire.php?id=<?php echo $partenaire['id_partenaire']; ?>'">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-warning"
                                                    onclick="event.stopPropagation(); window.location.href='editPartenaire.php?id=<?php echo $partenaire['id_partenaire']; ?>'">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-outline-danger"
                                                    onclick="event.stopPropagation(); if(confirm('Supprimer <?php echo addslashes($partenaire['nom']); ?> ?')) window.location.href='deletePartenaire.php?id=<?php echo $partenaire['id_partenaire']; ?>'">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="8" class="text-end">
                                    <strong>Total : <?php echo $stats['total']; ?> partenaires</strong>
                                    <?php if ($stats['total_montant'] > 0): ?>
                                        | <strong class="text-success"><?php echo number_format($stats['total_montant'], 2, ',', ' '); ?> €</strong>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card-footer bg-light">
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i>
                            Cliquez sur une ligne pour voir les détails du partenaire.
                        </small>
                    </div>
                    <div class="col-md-6 text-end">
                        <small class="text-muted">
                            <?php echo date('d/m/Y H:i'); ?> •
                            <?php echo $stats['actifs']; ?> partenaires actifs
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        // Initialiser DataTables
        document.addEventListener('DOMContentLoaded', function() {
            // Initialiser les badges avec les bonnes classes
            document.querySelectorAll('.badge-donateur').forEach(badge => {
                badge.classList.add('bg-primary');
            });
            document.querySelectorAll('.badge-subvention').forEach(badge => {
                badge.classList.add('bg-warning', 'text-dark');
            });
            document.querySelectorAll('.badge-actif').forEach(badge => {
                badge.classList.add('bg-success');
            });
            document.querySelectorAll('.badge-inactif').forEach(badge => {
                badge.classList.add('bg-secondary');
            });

            // Initialiser DataTable si le tableau existe
            const table = document.getElementById('partenairesTable');
            if (table) {
                // Simple DataTable avec options de base
                const dataTable = new simpleDatatables.DataTable(table, {
                    searchable: true,
                    fixedHeight: false,
                    perPage: 10,
                    labels: {
                        placeholder: "Rechercher...",
                        perPage: "Lignes par page",
                        noRows: "Aucun partenaire trouvé",
                        info: "Affichage de {start} à {end} sur {rows} partenaires"
                    }
                });
            }

            // Clic sur une ligne pour voir les détails
            document.querySelectorAll('tbody tr').forEach(row => {
                row.addEventListener('click', function() {
                    const id = this.cells[0].textContent.replace('#', '');
                    window.location.href = `voirDetailsPartenaire.php?id=${id}`;

                });
            });
        });

        // Fonction d'export Excel (basique)
        function exportToExcel() {
            alert('Fonction d\'export à implémenter. Pour l\'instant, utilisez Ctrl+P pour imprimer.');
            window.print();
        }

        // Confirmation avant suppression
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-outline-danger')) {
                const row = e.target.closest('tr');
                const nom = row.cells[1].textContent.trim();
                const id = row.cells[0].textContent.replace('#', '');

                if (confirm(`Voulez-vous vraiment supprimer le partenaire "${nom}" ?`)) {
                    window.location.href = `deletePartenaire.php?id=${id}`;
                }
            }
        });
    </script>
    </body>
    </html>