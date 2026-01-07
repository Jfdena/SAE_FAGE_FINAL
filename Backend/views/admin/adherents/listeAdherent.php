<?php
// Backend/views/admin/adherents/listeAdherent.php

// Protection de la page
require_once '../../../test/session_check.php';

// Connexion BDD
require_once '../../../config/Database.php';
$db = new Database();
$conn = $db->getConnection();

// Récupérer les bénévoles
$search = $_GET['search'] ?? '';
$statut = $_GET['statut'] ?? '';

$sql = "SELECT * FROM benevole WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (nom LIKE ? OR prenom LIKE ? OR email LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if (!empty($statut)) {
    $sql .= " AND statut = ?";
    $params[] = $statut;
}

$sql .= " ORDER BY date_inscription DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$benevoles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Compter le total
$total = count($benevoles);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des bénévoles - FAGE</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <style>
        .table-hover tbody tr:hover {
            background-color: #f5f9ff;
            cursor: pointer;
        }
        .badge-actif {
            background-color: #28a745;
        }
        .badge-inactif {
            background-color: #6c757d;
        }
        .search-box {
            max-width: 400px;
        }
    </style>
</head>
<body>
<!-- Header simple -->
<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="../dashboard.php">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
        <span class="navbar-text text-white">
                <i class="bi bi-people"></i> Gestion des bénévoles
            </span>
    </div>
</nav>

<div class="container-fluid mt-4">
    <!-- En-tête avec actions -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="bi bi-people"></i> Liste des bénévoles
            <span class="badge bg-primary"><?php echo $total; ?></span>
        </h2>

        <div>
            <a href="addFiche.php" class="btn btn-primary">
                <i class="bi bi-person-plus"></i> Nouveau bénévole
            </a>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                        <input type="text"
                               name="search"
                               class="form-control"
                               placeholder="Rechercher par nom, prénom ou email..."
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>

                <div class="col-md-4">
                    <select name="statut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="actif" <?php echo $statut == 'actif' ? 'selected' : ''; ?>>Actif</option>
                        <option value="inactif" <?php echo $statut == 'inactif' ? 'selected' : ''; ?>>Inactif</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="bi bi-funnel"></i> Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau -->
    <?php if ($total > 0): ?>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Inscription</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($benevoles as $benevole): ?>
                            <tr>
                                <td><?php echo $benevole['id_benevole']; ?></td>
                                <td><strong><?php echo htmlspecialchars($benevole['nom']); ?></strong></td>
                                <td><?php echo htmlspecialchars($benevole['prenom']); ?></td>
                                <td>
                                    <?php if (!empty($benevole['email'])): ?>
                                        <a href="mailto:<?php echo htmlspecialchars($benevole['email']); ?>">
                                            <?php echo htmlspecialchars($benevole['email']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Non renseigné</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($benevole['telephone'])): ?>
                                        <?php echo htmlspecialchars($benevole['telephone']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $date = new DateTime($benevole['date_inscription']);
                                    echo $date->format('d/m/Y');
                                    ?>
                                </td>
                                <td>
                                    <span class="badge rounded-pill <?php echo $benevole['statut'] == 'actif' ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo ucfirst($benevole['statut']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="voirDetails.php?id=<?php echo $benevole['id_benevole']; ?>"
                                           class="btn btn-outline-info"
                                           title="Voir détails">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="editFiche.php?id=<?php echo $benevole['id_benevole']; ?>"
                                           class="btn btn-outline-warning"
                                           title="Modifier">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button class="btn btn-outline-danger"
                                                title="Supprimer"
                                                onclick="confirmDelete(<?php echo $benevole['id_benevole']; ?>, '<?php echo htmlspecialchars($benevole['prenom'] . ' ' . $benevole['nom']); ?>')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Résumé -->
        <div class="alert alert-info mt-3">
            <i class="bi bi-info-circle"></i>
            Affichage de <strong><?php echo $total; ?></strong> bénévole(s)
            <?php if (!empty($search)): ?>
                pour la recherche : "<strong><?php echo htmlspecialchars($search); ?></strong>"
            <?php endif; ?>
        </div>

    <?php else: ?>
        <!-- Aucun résultat -->
        <div class="text-center py-5">
            <div class="mb-3">
                <i class="bi bi-people" style="font-size: 3rem; color: #6c757d;"></i>
            </div>
            <h4 class="text-muted">Aucun bénévole trouvé</h4>
            <p class="text-muted">
                <?php if (!empty($search)): ?>
                    Aucun résultat pour "<?php echo htmlspecialchars($search); ?>"
                <?php else: ?>
                    La liste des bénévoles est vide pour le moment.
                <?php endif; ?>
            </p>
            <a href="addFiche.php" class="btn btn-primary mt-2">
                <i class="bi bi-person-plus"></i> Ajouter le premier bénévole
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Footer -->
<footer class="mt-5 py-3 border-top text-center">
    <div class="container">
        <small class="text-muted">
            FAGE Backoffice • Gestion des bénévoles •
            <?php echo date('Y'); ?> • <?php echo $total; ?> bénévole(s) enregistré(s)
        </small>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Confirmation suppression
    function confirmDelete(id, nom) {
        if (confirm(`Voulez-vous vraiment supprimer le bénévole "${nom}" ?`)) {
            window.location.href = 'deleteBenevole.php?id=' + id;
        }
    }

    // Clic sur une ligne = voir détails
    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const detailLink = row.querySelector('a.btn-outline-info');
            row.addEventListener('click', function(e) {
                // Ne pas déclencher si on clique sur les boutons d'action
                if (!e.target.closest('.btn-group')) {
                    detailLink.click();
                }
            });
        });
    });
</script>
</body>
</html>