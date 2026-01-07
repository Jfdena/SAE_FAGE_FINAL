<?php
// Backend/views/admin/missions/calendrier.php

// Protection
require_once '../../../test/session_check.php';

// Connexion BDD
require_once '../../../config/Database.php';
// Afficher les messages de succès
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'annule') {
        echo '<div class="alert alert-warning alert-dismissible fade show">
                <i class="bi bi-info-circle"></i> Événement marqué comme annulé.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>';
    } elseif ($_GET['status'] == 'deleted') {
        echo '<div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle"></i> Événement supprimé avec succès.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>';
    }
}
$db = new Database();
$conn = $db->getConnection();

// Récupérer le mois/année courante ou spécifiée
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Validation
if ($month < 1 || $month > 12) $month = date('n');
if ($year < 2020 || $year > 2030) $year = date('Y');

// Récupérer les événements du mois
$start_date = "$year-$month-01";
$end_date = date('Y-m-t', strtotime($start_date));

try {
    $stmt = $conn->prepare("
        SELECT * FROM Evenement 
        WHERE date_debut BETWEEN ? AND ?
           OR date_fin BETWEEN ? AND ?
           OR (? BETWEEN date_debut AND date_fin)
           OR (? BETWEEN date_debut AND date_fin)
        ORDER BY date_debut
    ");
    $stmt->execute([$start_date, $end_date, $start_date, $end_date, $start_date, $end_date]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Si la table n'existe pas encore
    $events = [];
}

// Organiser les événements par jour
$events_by_day = [];
foreach ($events as $event) {
    $event_start = new DateTime($event['date_debut']);
    $event_end = new DateTime($event['date_fin']);

    // Pour chaque jour entre début et fin
    $interval = DateInterval::createFromDateString('1 day');
    $period = new DatePeriod($event_start, $interval, $event_end->modify('+1 day'));

    foreach ($period as $day) {
        $day_str = $day->format('Y-m-d');
        if (!isset($events_by_day[$day_str])) {
            $events_by_day[$day_str] = [];
        }
        $events_by_day[$day_str][] = $event;
    }
}

// Calculs pour le calendrier
$first_day = date('N', strtotime("$year-$month-01")); // 1=lundi, 7=dimanche
$days_in_month = date('t', strtotime("$year-$month-01"));
$today = date('Y-m-d');

// Mois précédent/suivant
$prev_month = $month - 1;
$prev_year = $year;
if ($prev_month < 1) {
    $prev_month = 12;
    $prev_year--;
}

$next_month = $month + 1;
$next_year = $year;
if ($next_month > 12) {
    $next_month = 1;
    $next_year++;
}

// Noms des mois
$month_names = [
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
    <title>Calendrier événements - FAGE</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />

    <style>
        .calendar-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .calendar-header {
            background: linear-gradient(135deg, #183146 0%, #0d6efd 100%);
            color: white;
            padding: 20px;
        }
        .calendar-day {
            min-height: 120px;
            border: 1px solid #e9ecef;
            padding: 8px;
            transition: all 0.2s;
            position: relative;
        }
        .calendar-day:hover {
            background-color: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .calendar-day.today {
            background-color: #e3f2fd;
            border: 2px solid #183146;
        }
        .calendar-day.other-month {
            background-color: #f8f9fa;
            color: #adb5bd;
        }
        .event-badge {
            display: block;
            font-size: 0.75rem;
            padding: 3px 6px;
            margin-bottom: 3px;
            border-radius: 4px;
            cursor: pointer;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .event-planifie { background-color: #cce5ff; color: #004085; }
        .event-cours { background-color: #d4edda; color: #155724; }
        .event-termine { background-color: #f8d7da; color: #721c24; }
        .calendar-nav-btn {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        .calendar-nav-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: scale(1.1);
        }
        .calendar-cell-number {
            position: absolute;
            top: 5px;
            right: 5px;
            font-weight: bold;
            color: #495057;
        }
        .calendar-day.today .calendar-cell-number {
            background: #183146;
            color: white;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
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
            <i class="bi bi-calendar-event"></i> Calendrier des événements
        </div>
    </div>
</nav>

<div class="container-fluid py-4">
    <!-- En-tête avec actions -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>
                <i class="bi bi-calendar-month"></i> Calendrier
            </h2>
            <p class="text-muted mb-0">
                Visualisez et gérez les événements et missions de la FAGE
            </p>
        </div>

        <div class="btn-group">
            <a href="addMissions.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nouvel événement
            </a>
            <a href="listeMissions.php" class="btn btn-outline-primary">
                <i class="bi bi-list"></i> Liste
            </a>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <?php if (!empty($events)): ?>
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="card-title">Événements ce mois</h6>
                        <h3 class="mb-0"><?php echo count($events); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="card-title">En cours</h6>
                        <h3 class="mb-0">
                            <?php
                            $in_progress = array_filter($events, function($e) {
                                return $e['statut'] === 'en cours';
                            });
                            echo count($in_progress);
                            ?>
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <h6 class="card-title">Planifiés</h6>
                        <h3 class="mb-0">
                            <?php
                            $planned = array_filter($events, function($e) {
                                return $e['statut'] === 'planifié';
                            });
                            echo count($planned);
                            ?>
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6 class="card-title">Aujourd'hui</h6>
                        <h3 class="mb-0">
                            <?php
                            $today_events = isset($events_by_day[$today]) ? count($events_by_day[$today]) : 0;
                            echo $today_events;
                            ?>
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Calendrier -->
    <div class="calendar-container">
        <!-- En-tête calendrier -->
        <div class="calendar-header">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <a href="?month=<?php echo date('n'); ?>&year=<?php echo date('Y'); ?>"
                       class="btn btn-outline-light btn-sm">
                        <i class="bi bi-calendar-check"></i> Aujourd'hui
                    </a>
                </div>

                <div class="col-md-4 text-center">
                    <div class="d-flex justify-content-center align-items-center">
                        <a href="?month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>"
                           class="calendar-nav-btn me-3">
                            <i class="bi bi-chevron-left"></i>
                        </a>

                        <h3 class="mb-0 mx-3">
                            <?php echo $month_names[$month]; ?> <?php echo $year; ?>
                        </h3>

                        <a href="?month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?>"
                           class="calendar-nav-btn ms-3">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-md-4 text-end">
                    <div class="btn-group">
                        <a href="calendrier.php" class="btn btn-outline-light btn-sm active">
                            Mois
                        </a>
                        <a href="#" class="btn btn-outline-light btn-sm">
                            Semaine
                        </a>
                        <a href="#" class="btn btn-outline-light btn-sm">
                            Jour
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Jours de la semaine -->
        <div class="row g-0 text-center bg-light">
            <?php
            $week_days = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
            foreach ($week_days as $day):
                ?>
                <div class="col p-3 border-end">
                    <strong><?php echo $day; ?></strong>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Grille du calendrier -->
        <div class="row g-0">
            <?php
            // Jours du mois précédent
            for ($i = 1; $i < $first_day; $i++):
                $day_num = date('t', strtotime("$prev_year-$prev_month-01")) - ($first_day - $i) + 1;
                ?>
                <div class="col calendar-day other-month">
                    <div class="calendar-cell-number"><?php echo $day_num; ?></div>
                </div>
            <?php endfor; ?>

            <!-- Jours du mois courant -->
            <?php for ($day = 1; $day <= $days_in_month; $day++):
            $current_date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $is_today = ($current_date === $today);
            $day_events = isset($events_by_day[$current_date]) ? $events_by_day[$current_date] : [];
            ?>
            <div class="col calendar-day <?php echo $is_today ? 'today' : ''; ?>">
                <div class="calendar-cell-number"><?php echo $day; ?></div>

                <!-- Événements du jour -->
                <?php foreach ($day_events as $event):
                    $event_class = 'event-planifie';
                    if ($event['statut'] === 'en cours') $event_class = 'event-cours';
                    if ($event['statut'] === 'termine') $event_class = 'event-termine';
                    ?>
                    <div class="event-badge <?php echo $event_class; ?>"
                         data-bs-toggle="tooltip"
                         title="<?php echo htmlspecialchars($event['nom']); ?> - <?php echo $event['type']; ?>"
                         onclick="window.location.href='voirDetailsMissions.php?id=<?php echo $event['id_evenement']; ?>'">
                        <i class="bi bi-calendar-event"></i>
                        <?php echo htmlspecialchars(substr($event['nom'], 0, 15)); ?>
                        <?php if (strlen($event['nom']) > 15) echo '...'; ?>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($day_events)): ?>
                    <div class="text-center text-muted mt-4">
                        <small>Aucun événement</small>
                    </div>
                <?php endif; ?>

                <!-- Bouton ajout rapide -->
                <?php if (count($day_events) < 3): ?>
                    <div class="position-absolute bottom-0 start-0 end-0 p-2">
                        <a href="addMissions.php?date=<?php echo $current_date; ?>"
                           class="btn btn-sm btn-outline-success w-100">
                            <i class="bi bi-plus"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Nouvelle ligne après 7 jours -->
            <?php if (($first_day + $day - 1) % 7 == 0 && $day != $days_in_month): ?>
        </div><div class="row g-0">
            <?php endif; ?>

            <?php endfor; ?>

            <!-- Jours du mois suivant -->
            <?php
            $total_cells = 42; // 6 lignes de 7 jours
            $used_cells = ($first_day - 1) + $days_in_month;
            $remaining_cells = $total_cells - $used_cells;

            for ($i = 1; $i <= $remaining_cells; $i++):
                ?>
                <div class="col calendar-day other-month">
                    <div class="calendar-cell-number"><?php echo $i; ?></div>
                </div>
            <?php endfor; ?>
        </div>
    </div>

    <!-- Légende -->
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h6><i class="bi bi-info-circle"></i> Légende</h6>
                    <div class="d-flex flex-wrap gap-3">
                        <div>
                            <span class="event-badge event-planifie d-inline-block">Événement planifié</span>
                        </div>
                        <div>
                            <span class="event-badge event-cours d-inline-block">En cours</span>
                        </div>
                        <div>
                            <span class="event-badge event-termine d-inline-block">Terminé</span>
                        </div>
                        <div>
                            <div class="calendar-day today d-inline-block p-2" style="min-height: auto;">
                                Jour courant
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6><i class="bi bi-lightning"></i> Actions rapides</h6>
                    <div class="d-grid gap-2">
                        <a href="addMissions.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Créer un événement
                        </a>
                        <button class="btn btn-outline-secondary" onclick="window.print()">
                            <i class="bi bi-printer"></i> Imprimer le calendrier
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des événements du mois -->
    <?php if (!empty($events)): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-list-check"></i> Événements de <?php echo $month_names[$month]; ?> <?php echo $year; ?>
                    <span class="badge bg-primary"><?php echo count($events); ?></span>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Type</th>
                            <th>Dates</th>
                            <th>Lieu</th>
                            <th>Statut</th>
                            <th>Participants</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($events as $event):
                            $date_debut = date('d/m/Y', strtotime($event['date_debut']));
                            $date_fin = date('d/m/Y', strtotime($event['date_fin']));
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($event['nom']); ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?php echo htmlspecialchars($event['type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo $date_debut; ?>
                                    <?php if ($date_debut !== $date_fin): ?>
                                        <br><small class="text-muted">au <?php echo $date_fin; ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo !empty($event['lieu']) ? htmlspecialchars($event['lieu']) : 'Non défini'; ?></td>
                                <td>
                                    <?php
                                    $status_badge = [
                                        'planifié' => 'warning',
                                        'en cours' => 'success',
                                        'termine' => 'secondary',
                                        'annule' => 'danger'
                                    ];
                                    $badge_class = $status_badge[$event['statut']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo $badge_class; ?>">
                                        <?php echo ucfirst($event['statut']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo $event['nb_participants_prevus'] ?? '0'; ?> prévus
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="voirDetailsMissions.php?id=<?php echo $event['id_evenement']; ?>"
                                           class="btn btn-outline-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="editMissions.php?id=<?php echo $event['id_evenement']; ?>"
                                           class="btn btn-outline-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Aucun événement -->
        <div class="text-center py-5">
            <div class="mb-3">
                <i class="bi bi-calendar-x" style="font-size: 4rem; color: #6c757d;"></i>
            </div>
            <h4 class="text-muted">Aucun événement ce mois-ci</h4>
            <p class="text-muted">
                Commencez par planifier vos premiers événements et missions.
            </p>
            <a href="addMissions.php" class="btn btn-primary btn-lg">
                <i class="bi bi-plus-circle"></i> Créer le premier événement
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- FullCalendar JS (optionnel) -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/fr.js'></script>

<script>
    // Initialiser les tooltips Bootstrap
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Navigation clavier
        document.addEventListener('keydown', function(e) {
            switch(e.key) {
                case 'ArrowLeft':
                    window.location.href = '?month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>';
                    break;
                case 'ArrowRight':
                    window.location.href = '?month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?>';
                    break;
                case 'Home':
                    window.location.href = '?month=<?php echo date("n"); ?>&year=<?php echo date("Y"); ?>';
                    break;
            }
        });

        // Clic sur jour vide = créer événement
        document.querySelectorAll('.calendar-day').forEach(function(day) {
            day.addEventListener('click', function(e) {
                // Ne déclencher que si on clique sur le jour lui-même, pas sur un événement
                if (e.target === this || e.target.classList.contains('calendar-cell-number')) {
                    const dayNum = this.querySelector('.calendar-cell-number').textContent;
                    const date = '<?php echo $year . "-" . str_pad($month, 2, "0", STR_PAD_LEFT); ?>-' +
                        dayNum.toString().padStart(2, '0');

                    if (confirm('Créer un événement le ' + dayNum + '/<?php echo str_pad($month, 2, "0", STR_PAD_LEFT); ?>/<?php echo $year; ?> ?')) {
                        window.location.href = 'addMissions.php?date=' + date;
                    }
                }
            });
        });
    });

    // Version FullCalendar (optionnelle - décommenter si voulu)
    /*
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'fr',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: [
                // Les événements seraient chargés via AJAX
            ]
        });
        calendar.render();
    });
    */
</script>
</body>
</html>