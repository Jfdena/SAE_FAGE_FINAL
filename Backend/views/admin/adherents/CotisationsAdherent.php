<?php
# ⭐ PRIORITAIRE - Suivi paiements (Sophie)
// Backend/views/admin/adherents/CotisationsAdherent.php

// Protection
require_once '../../../session_check.php';

// Connexion BDD
require_once '../../../config/Database.php';
$db = new Database();
$conn = $db->getConnection();
require_once '../../../config/Constraints.php';
$constraints = new Constraints($conn);

// Récupérer tous les bénévoles avec leurs cotisations (Correction mysqli directe)
$result = $conn->query("
        SELECT b.*, 
               (SELECT MAX(date_paiement) FROM cotisation WHERE id_benevole = b.id_benevole) as derniere_cotisation,
               (SELECT COUNT(*) FROM cotisation WHERE id_benevole = b.id_benevole) as nb_cotisations
        FROM benevole b
        WHERE b.statut = 'actif'
        ORDER BY b.nom, b.prenom
    ");
$benevoles = $result->fetch_all(MYSQLI_ASSOC);

// Traitement ajout cotisation
// Dans le traitement du formulaire d'ajout de cotisation :
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_cotisation'])) {

    // Validation avec Constraints
    $rules = [
        'id_benevole' => ['required' => true, 'numeric' => true],
        'montant' => ['required' => true, 'amount' => ['allow_zero' => false]],
        'date_paiement' => ['required' => true, 'date' => true, 'not_future' => true],
        'annee' => ['required' => true, 'year' => true, 'unique_cotisation' => true]
    ];

    // Pour unique_cotisation, on a besoin de l'ID bénévole et de l'année
    $_POST['id'] = $_POST['id_cotisation'] ?? null; // Pour exclusion si modification

    $errors = $constraints->validateFormData($_POST, $rules);

    if (empty($errors)) {
        // Nettoyer et formater
        $id_benevole = (int)$_POST['id_benevole'];
        $montant = $constraints->formatAmountForDB($_POST['montant']);
        $date_paiement = $constraints->formatDateForDB($_POST['date_paiement']);
        $mode_paiement = $_POST['mode_paiement'];
        $annee = (int)$_POST['annee'];

        try {
            $sql = "INSERT INTO cotisation (id_benevole, montant, date_paiement, mode_paiement, annee) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id_benevole, $montant, $date_paiement, $mode_paiement, $annee]);

            $success_message = "✅ Cotisation enregistrée avec succès !";
        } catch (Exception $e) {
            $error_message = "❌ Erreur : " . $e->getMessage();
        }
    } else {
        $error_message = "❌ " . implode("<br>", $errors);
    }
}

// Si la table cotisation n'existe pas, créons-la
try {
    $conn->query("SELECT 1 FROM cotisation LIMIT 1");
} catch (Exception $e) {
    // Créer la table
    $sql = "CREATE TABLE IF NOT EXISTS cotisation (
                id_cotisation INT AUTO_INCREMENT PRIMARY KEY,
                id_benevole INT NOT NULL,
                montant DECIMAL(10,2) NOT NULL,
                date_paiement DATE NOT NULL,
                mode_paiement ENUM('espèces', 'chèque', 'virement', 'autre') DEFAULT 'espèces',
                annee YEAR NOT NULL,
                date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (id_benevole) REFERENCES benevole(id_benevole) ON DELETE CASCADE
            )";
    $conn->exec($sql);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des cotisations - FAGE</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <style>
        .cotisation-card {
            border-left: 4px solid #28a745;
        }
        .retard-card {
            border-left: 4px solid #dc3545;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.8; }
            100% { opacity: 1; }
        }
        .stat-card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }
        .badge-cotise {
            background-color: #28a745;
        }
        .badge-retard {
            background-color: #dc3545;
        }
        .montant-input {
            max-width: 150px;
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
            <i class="bi bi-cash-coin"></i> Gestion des cotisations
        </div>
    </div>
</nav>

<div class="container-fluid py-4">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="bi bi-cash-coin"></i> Cotisations des bénévoles</h2>
            <p class="text-muted mb-0">
                Suivi des paiements et gestion des cotisations - <?php echo date('Y'); ?>
            </p>
        </div>
        <div class="btn-group">
            <a href="listeAdherent.php" class="btn btn-outline-primary">
                <i class="bi bi-people"></i> Liste bénévoles
            </a>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#ajoutModal">
                <i class="bi bi-plus-circle"></i> Nouvelle cotisation
            </button>
        </div>
    </div>

    <!-- Messages -->
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card border-primary">
                <div class="card-body text-center">
                    <h6 class="text-muted">Bénévoles actifs</h6>
                    <h2 class="mb-0"><?php echo count($benevoles); ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card border-success">
                <div class="card-body text-center">
                    <h6 class="text-muted">À jour</h6>
                    <h2 class="mb-0" id="countAjour">0</h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card border-warning">
                <div class="card-body text-center">
                    <h6 class="text-muted">En retard</h6>
                    <h2 class="mb-0" id="countRetard">0</h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card border-info">
                <div class="card-body text-center">
                    <h6 class="text-muted">Taux cotisation</h6>
                    <h3 class="mb-0" id="tauxCotisation">0%</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau des cotisations -->
    <div class="card shadow">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-table"></i> État des cotisations
                <span class="badge bg-primary ms-2"><?php echo count($benevoles); ?></span>
            </h5>
            <div class="text-muted">
                Année : <strong><?php echo date('Y'); ?></strong>
            </div>
        </div>

        <div class="card-body">
            <?php if (empty($benevoles)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-people" style="font-size: 4rem; color: #6c757d;"></i>
                    <h4 class="text-muted mt-3">Aucun bénévole actif</h4>
                    <p class="text-muted">
                        Ajoutez d'abord des bénévoles avant de gérer les cotisations.
                    </p>
                    <a href="addFiche.php" class="btn btn-primary">
                        <i class="bi bi-person-plus"></i> Ajouter un bénévole
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="cotisationsTable">
                        <thead>
                            <tr>
                                <th>Bénévole</th>
                                <th>Contact</th>
                                <th>Dernière cotisation</th>
                                <th>Nb cotisations</th>
                                <th>Statut <?php echo date('Y'); ?></th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($benevoles as $benevole):
                                // Vérifier statut cotisation
                                $derniere_annee = substr($benevole['derniere_cotisation'] ?? '', 0, 4);
                                $a_jour = ($derniere_annee == date('Y'));
                                $statut_class = $a_jour ? 'badge-cotise' : 'badge-retard';
                                $statut_text = $a_jour ? 'À jour' : 'En retard';
                            ?>
                            <tr class="<?php echo $a_jour ? 'cotisation-card' : 'retard-card'; ?>">
                                <td>
                                    <strong><?php echo htmlspecialchars($benevole['prenom'] . ' ' . $benevole['nom']); ?></strong>
                                    <br>
                                    <small class="text-muted">ID: #<?php echo $benevole['id_benevole']; ?></small>
                                </td>
                                <td>
                                    <?php if (!empty($benevole['email'])): ?>
                                        <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($benevole['email']); ?><br>
                                    <?php endif; ?>
                                    <?php if (!empty($benevole['telephone'])): ?>
                                        <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($benevole['telephone']); ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo $benevole['derniere_cotisation']
                                        ? date('d/m/Y', strtotime($benevole['derniere_cotisation']))
                                        : 'Jamais'; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?php echo $benevole['nb_cotisations'] ?: '0'; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo $statut_class; ?>">
                                        <?php echo $statut_text; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-success"
                                                onclick="ajouterCotisation(<?php echo $benevole['id_benevole']; ?>, '<?php echo addslashes($benevole['prenom'] . ' ' . $benevole['nom']); ?>')">
                                            <i class="bi bi-cash"></i> Payer
                                        </button>
                                        <a href="voirDetails.php?id=<?php echo $benevole['id_benevole']; ?>"
                                           class="btn btn-outline-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Résumé -->
                <div class="alert alert-info mt-3">
                    <div class="row">
                        <div class="col-md-4">
                            <small>Cotisation standard recommandée :</small><br>
                            <strong>15 € / an</strong>
                        </div>
                        <div class="col-md-4">
                            <small>Période de cotisation :</small><br>
                            <strong>Année civile (Jan-Dec)</strong>
                        </div>
                        <div class="col-md-4">
                            <small>Prochaine relance :</small><br>
                            <strong>15 Mars <?php echo date('Y') + 1; ?></strong>
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
                        Les bénévoles en rouge n'ont pas cotisé pour l'année en cours.
                    </small>
                </div>
                <div class="col-md-6 text-end">
                    <small class="text-muted">
                        <?php echo date('d/m/Y H:i'); ?> •
                        Mise à jour en temps réel
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal ajout cotisation -->
<div class="modal fade" id="ajoutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouvelle cotisation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="ajouter_cotisation" value="1">

                    <div class="mb-3">
                        <label for="id_benevole" class="form-label">Bénévole</label>
                        <select id="id_benevole" name="id_benevole" class="form-select" required>
                            <option value="">Sélectionner un bénévole</option>
                            <?php foreach ($benevoles as $benevole): ?>
                            <option value="<?php echo $benevole['id_benevole']; ?>">
                                <?php echo htmlspecialchars($benevole['prenom'] . ' ' . $benevole['nom']); ?>
                                <?php if (!empty($benevole['email'])): ?>
                                    (<?php echo htmlspecialchars($benevole['email']); ?>)
                                <?php endif; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="montant" class="form-label">Montant (€)</label>
                            <div class="input-group">
                                <input type="number"
                                       id="montant"
                                       name="montant"
                                       class="form-control montant-input"
                                       value="15"
                                       min="0"
                                       step="0.01"
                                       required>
                                <span class="input-group-text">€</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="annee" class="form-label">Année</label>
                            <select id="annee" name="annee" class="form-select" required>
                                <?php for ($i = date('Y') + 1; $i >= 2020; $i--): ?>
                                <option value="<?php echo $i; ?>" <?php echo $i == date('Y') ? 'selected' : ''; ?>>
                                    <?php echo $i; ?>
                                </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="date_paiement" class="form-label">Date paiement</label>
                            <input type="date"
                                   id="date_paiement"
                                   name="date_paiement"
                                   class="form-control"
                                   value="<?php echo date('Y-m-d'); ?>"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label for="mode_paiement" class="form-label">Mode paiement</label>
                            <select id="mode_paiement" name="mode_paiement" class="form-select">
                                <option value="espèces">Espèces</option>
                                <option value="chèque">Chèque</option>
                                <option value="virement">Virement</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Enregistrer la cotisation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Calculer les statistiques
    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('tbody tr');
        let countAjour = 0;
        let countRetard = 0;

        rows.forEach(row => {
            const statutBadge = row.querySelector('.badge-cotise, .badge-retard');
            if (statutBadge) {
                if (statutBadge.classList.contains('badge-cotise')) {
                    countAjour++;
                } else {
                    countRetard++;
                }
            }
        });

        document.getElementById('countAjour').textContent = countAjour;
        document.getElementById('countRetard').textContent = countRetard;

        const total = countAjour + countRetard;
        const taux = total > 0 ? Math.round((countAjour / total) * 100) : 0;
        document.getElementById('tauxCotisation').textContent = taux + '%';
    });

    // Fonction pour ajouter une cotisation pré-remplie
    function ajouterCotisation(id, nom) {
        const modal = new bootstrap.Modal(document.getElementById('ajoutModal'));
        document.getElementById('id_benevole').value = id;

        // Mettre à jour le titre
        document.querySelector('#ajoutModal .modal-title').textContent = 'Cotisation - ' + nom;

        modal.show();
    }

    // Export Excel simple
    function exportToExcel() {
        const table = document.getElementById('cotisationsTable');
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
        const filename = `cotisations_fage_${new Date().toISOString().slice(0,10)}.csv`;

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
</script>
</body>
</html>