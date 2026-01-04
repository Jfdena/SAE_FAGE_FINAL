<?php
require_once __DIR__ . '/../database.php';
$db = Database::getInstance()->getConnection();

$query = "SELECT 
            (SELECT count(*) FROM association) as totalAssos, 
            (SELECT count(*) FROM benevole) as totalBene,
            (SELECT SUM(montant) FROM DON WHERE MONTH(date_don) = MONTH(CURRENT_DATE()) AND YEAR(date_don) = YEAR(CURRENT_DATE())) as totalDonsMois,
            (SELECT count(*) FROM evenement WHERE date_debut > CURRENT_DATE()) as upcomingEvents,
            (SELECT count(*) FROM projet_national WHERE date_debut > CURRENT_DATE()) as upcomingNationalEvents";

$result = $db->query($query);
$row = $result->fetch_assoc();

$countAssociation = $row['totalAssos'];
$countBenevole = $row['totalBene'];
$donsMois = $row['totalDonsMois'] ?? 0;
$countUpcomingEvents = $row['upcomingEvents'];
$countUpcomingNational = $row['upcomingNationalEvents'];
?>

<div>
    <div class="row">
        <div class="rounded rounded-3 bg-primary col text-white text-center m-3 p-3">
            <h3><?php echo $countAssociation ?></h3>
            <p>Associations</p>
        </div>
        <div class="rounded rounded-3 bg-success col text-white text-center m-3 p-3">
            <h3><?php echo $countBenevole?></h3>
            <p>Bénévoles</p>
        </div>
    </div>
    <div class="row">
        <div class="rounded rounded-3 bg-warning col text-white text-center m-3 p-3">
            <h3><?php echo $donsMois?>€</h3>
            <p>de dons collectés</p>
        </div>
        <div class="rounded rounded-3 bg-info col text-white text-center m-3 p-3">
            <h3><?php echo $countUpcomingEvents?></h3>
            <p>évènements à venir</p>
        </div>
        <div class="rounded rounded-3 bg-danger col text-white text-center m-3 p-3">
            <h3><?php echo $countUpcomingNational?></h3>
            <p>évènements à venir</p>
        </div>
    </div>

</div>
