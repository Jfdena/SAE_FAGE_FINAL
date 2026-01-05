<?php
require_once __DIR__ . '/../database.php';
$db = Database::getInstance()->getConnection();

$query = "SELECT * FROM evenement WHERE date_debut >= CURDATE() ORDER BY date_debut LIMIT 1";

$result = $db->query($query);
$row = $result->fetch_assoc();

$nomEvent = $row['nom'] ?? 'Aucun évènement prévu';
$typeEvent = $row['type'] ?? 'N/A';
$lieuEvent = $row['lieu'] ?? 'N/A';
$dateDebut = $row['date_debut'] ?? 'N/A';
$dateFin = $row['date_fin'] ?? 'N/A';
$nbParticipants = $row['nb_participants_prevus'] ?? 'N/A';
$budget = $row['budget'] ?? 'N/A';
$statut = $row['statut'] ?? 'N/A';

?>

<div class="bg-primary">
    <h1 class="text-center"> Prochain évènement </h1>
    <h2><?php echo $nomEvent?></h2>
</div>
