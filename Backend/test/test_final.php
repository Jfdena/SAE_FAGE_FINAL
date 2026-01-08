<?php
// Backend/test_sae_finale.php
echo "<h1>🧪 TEST FINAL SAE - FAGE Backoffice</h1>";
echo "<h3>📅 Date du test : " . date('d/m/Y H:i') . "</h3>";

echo "<h2>📋 Modules à tester :</h2>";

$modules = [
    'auth' => [
        'Connexion' => '../views/auth/login.php',
        'Déconnexion' => '../views/auth/logout.php',
    ],
    'benevoles' => [
        'Dashboard' => '../views/admin/dashboard.php',
        'Liste bénévoles' => '../views/admin/adherents/listeAdherent.php',
        'Ajouter bénévole' => '../views/admin/adherents/addFiche.php',
        'Cotisations' => '../views/admin/adherents/CotisationsAdherent.php',
    ],
    'evenements' => [
        'Calendrier' => '../views/admin/missions/calendrier.php',
        'Liste événements' => '../views/admin/missions/listeMissions.php',
        'Ajouter événement' => '../views/admin/missions/addMissions.php',
    ],
    'partenaires' => [
        'Liste partenaires' => '../views/admin/partenaires/listePartenaires.php',
        'Ajouter partenaire' => '../views/admin/partenaires/addPartenaire.php',
        'Historique contributions' => '../views/admin/partenaires/HistoContribution.php',
    ]
];

foreach ($modules as $module => $pages) {
    echo "<div class='card mb-3'>";
    echo "<div class='card-header'><h4>" . ucfirst($module) . "</h4></div>";
    echo "<div class='card-body'>";

    foreach ($pages as $nom => $fichier) {
        $existe = file_exists($fichier);
        $couleur = $existe ? 'green' : 'red';
        $icone = $existe ? '✅' : '❌';

        echo "<p>";
        echo "<span style='color:$couleur'>$icone</span> ";
        echo "<strong>$nom</strong> : ";
        echo "<code>$fichier</code> ";

        if ($existe) {
            echo "<a href='$fichier' target='_blank' class='btn btn-sm btn-outline-primary'>Tester</a>";
        }
        echo "</p>";
    }

    echo "</div></div>";
}

// Test BDD
echo "<h2>🗃️ Test base de données</h2>";
try {
    require_once '../config/Database.php';
    $db = new Database();
    $conn = $db->getConnection();

    echo "<p style='color:green'>✅ Connexion MySQL OK</p>";

    // Compter par table
    $tables = ['benevole', 'cotisation', 'evenement', 'partenaire', 'membreBureau'];

    echo "<table class='table table-sm'>";
    echo "<tr><th>Table</th><th>Nombre d'enregistrements</th></tr>";

    foreach ($tables as $table) {
        try {
            $stmt = $conn->query("SELECT COUNT(*) as total FROM $table");
            $result = $stmt->fetch();
            echo "<tr><td>$table</td><td><strong>" . $result['total'] . "</strong></td></tr>";
        } catch (Exception $e) {
            echo "<tr><td>$table</td><td style='color:orange'>⚠️ Table non trouvée</td></tr>";
        }
    }

    echo "</table>";

} catch (Exception $e) {
    echo "<p style='color:red'>❌ Erreur BDD : " . $e->getMessage() . "</p>";
}
