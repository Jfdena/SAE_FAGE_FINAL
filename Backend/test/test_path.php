<?php
echo "<h3>Test du chemin pour deleteMissions.php</h3>";
echo "<p>URL actuelle : " . $_SERVER['PHP_SELF'] . "</p>";
echo "<p>Répertoire courant : " . __DIR__ . "</p>";

// Testons différents chemins
echo "<h4>Liens de test :</h4>";
echo '<a href="../views/admin/missions/deleteMissions.php?id=1">Test deleteMissions.php (chemin relatif)</a><br>';
echo '<a href="../views/admin/missions/deleteMissions.php?id=1">Test ./deleteMissions.php</a><br>';
echo '<a href="../views/admin/missions/deleteMissions.php?id=1">Test chemin absolu</a><br>';

// Vérifions si le fichier existe
$files = [
    '../views/admin/missions/deleteMissions.php',
    '../views/admin/missions/deleteMissions.php',
    __DIR__ . '../views/admin/missions/deleteMissions.php'
];

echo "<h4>Vérification des fichiers :</h4>";
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✓ Fichier existe : $file<br>";
    } else {
        echo "✗ Fichier NON trouvé : $file<br>";
    }
}
