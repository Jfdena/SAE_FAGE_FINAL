<?php
# ⭐ PRIORITAIRE - Tableau de bord principal
// Backend/views/admin/dashboard.php
$page_title = "Tableau de bord - Backoffice FAGE";

require_once '../partials/header.php';
require_once '../partials/sidebar.php';

// Inclure les modèles nécessaires
require_once '../../models/MembreBureau.php';
require_once '../../models/Benevol.php';
require_once '../../models/Mission.php';

$database = new Database();
$db = $database->getConnection();

// Récupérer les statistiques
$adherentModel = new Benevol($db);
$missionModel = new Mission($db);

$totalAdherents = $adherentModel->countTotal();
$adherentsActifs = $adherentModel->countActifs();
$missionsEnCours = $missionModel->countEnCours();
$missionsTerminees = $missionModel->countTerminees();

// Derniers bénévoles inscrits
$derniersAdherents = $adherentModel->getLatest(5);

// Prochains événements
$prochainsEvents = $missionModel->getUpcoming(5);
?>

    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <h1 class="h3 mb-0">Tableau de bord</h1>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo substr($_SESSION['user_name'], 0, 1); ?>
                </div>
                <div>
                    <div class="fw-bold"><?php echo $_SESSION['user_name']; ?></div>
                    <small class="text-muted">Membre du bureau</small>
                </div>
            </div>
        </div>

        <!-- Statistiques en temps réel -->
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="stat-value"><?php echo $totalAdherents; ?></div>
                    <div class="stat-label">Bénévoles total</div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <div class="stat-value"><?php echo $adherentsActifs; ?></div>
                    <div class="stat-label">Bénévoles actifs</div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    <div class="stat-value"><?php echo $missionsEnCours; ?></div>
                    <div class="stat-label">Événements en cours</div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="stat-value"><?php echo $missionsTerminees; ?></div>
                    <div class="stat-label">Événements terminés</div>
                </div>
            </div>
        </div>

        <!-- Deux colonnes principales -->
        <div class="row mt-4">
            <!-- Derniers bénévoles -->
            <div class="col-lg-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-person-plus me-2"></i>
                            Derniers bénévoles inscrits
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Date d'inscription</th>
                                    <th>Statut</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if (empty($derniersAdherents)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">
                                            Aucun bénévole inscrit
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($derniersAdherents as $adherent): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($adherent['prenom'] . ' ' . $adherent['nom']); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($adherent['email']); ?></small>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($adherent['date_inscription'])); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo ($adherent['statut'] == 'actif') ? 'success' : 'secondary'; ?>">
                                                    <?php echo $adherent['statut']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end">
                            <a href="adherents/list.php" class="btn btn-sm btn-outline-primary">
                                Voir tous les bénévoles →
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Prochains événements -->
            <div class="col-lg-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar-week me-2"></i>
                            Prochains événements
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th>Événement</th>
                                    <th>Date</th>
                                    <th>Lieu</th>
                                    <th>Statut</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if (empty($prochainsEvents)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            Aucun événement à venir
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($prochainsEvents as $event): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($event['nom']); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($event['type']); ?></small>
                                            </td>
                                            <td>
                                                <?php echo date('d/m/Y', strtotime($event['date_debut'])); ?>
                                                <br>
                                                <small><?php echo date('H:i', strtotime($event['date_debut'])); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($event['lieu']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php
                                                switch($event['statut']) {
                                                    case 'planifié': echo 'warning'; break;
                                                    case 'en cours': echo 'info'; break;
                                                    case 'terminé': echo 'success'; break;
                                                    default: echo 'secondary';
                                                }
                                                ?>">
                                                    <?php echo $event['statut']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end">
                            <a href="missions/calendar.php" class="btn btn-sm btn-outline-primary">
                                Voir le calendrier →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphique d'activité (simplifié) -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-bar-chart me-2"></i>
                            Activité mensuelle
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="activityChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Graphique d'activité simplifié
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('activityChart').getContext('2d');
            const activityChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
                    datasets: [{
                        label: 'Inscriptions bénévoles',
                        data: [12, 19, 8, 15, 22, 18, 25, 30, 22, 28, 35, 40],
                        borderColor: '#183146',
                        backgroundColor: 'rgba(24, 49, 70, 0.1)',
                        fill: true,
                        tension: 0.4
                    }, {
                        label: 'Événements organisés',
                        data: [3, 5, 2, 6, 4, 8, 7, 10, 8, 9, 12, 15],
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Nombre'
                            }
                        }
                    }
                }
            });
        });
    </script>

<?php require_once '../partials/footer.php'; ?>