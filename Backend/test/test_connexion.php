<?php

require_once '../config/Database.php';
$db = new Database();
$conn = $db->getConnection();

echo "<h3>Test de connexion à la table Evenement</h3>";

// 1. Vérifier les tables
$tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "<h4>Tables disponibles :</h4>";
echo "<ul>";
foreach ($tables as $table) {
    echo "<li>" . htmlspecialchars($table) . "</li>";
}
echo "</ul>";

// 2. Vérifier le contenu de la table Evenement (si elle existe)
if (in_array('Evenement', $tables) || in_array('evenement', $tables)) {
    $table_name = in_array('Evenement', $tables) ? 'Evenement' : 'evenement';

    echo "<h4>Contenu de la table $table_name :</h4>";
    $stmt = $conn->query("SELECT COUNT(*) as count FROM $table_name");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Nombre d'événements : " . $count['count'] . "<br>";

    $stmt = $conn->query("SELECT * FROM $table_name LIMIT 5");
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($events) {
        echo "<table border='1'><tr>";
        foreach (array_keys($events[0]) as $column) {
            echo "<th>" . htmlspecialchars($column) . "</th>";
        }
        echo "</tr>";

        foreach ($events as $event) {
            echo "<tr>";
            foreach ($event as $value) {
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "Aucun événement trouvé.";
    }
} else {
    echo "ERREUR : Table Evenement/evenement non trouvée !";
}

// 3. Vérifier le chemin du fichier calendrier.php
echo "<h4>Liens :</h4>";
echo '<a href="calendrier.php">Accéder au calendrier</a><br>';
echo '<a href="../dashboard.php">Retour au dashboard</a>';
?><?php
