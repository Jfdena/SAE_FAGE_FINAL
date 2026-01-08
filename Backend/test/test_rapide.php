<?php
// Backend/test/test_rapide.php
echo "<h1>⚡ TEST RAPIDE MODULE ADHÉRENTS</h1>";

// Liste des pages à tester
$pages = [
    'auth' => [
        'name' => 'Login',
        'url' => 'views/auth/login.php',
        'desc' => 'Page de connexion'
    ],
    'dashboard' => [
        'name' => 'Dashboard',
        'url' => 'views/admin/dashboard.php',
        'desc' => 'Tableau de bord'
    ],
    'liste' => [
        'name' => 'Liste bénévoles',
        'url' => 'views/admin/adherents/listeAdherent.php',
        'desc' => 'Liste avec filtres'
    ],
    'add' => [
        'name' => 'Ajouter bénévole',
        'url' => 'views/admin/adherents/addFiche.php',
        'desc' => 'Formulaire ajout'
    ],
    'cotisations' => [
        'name' => 'Cotisations',
        'url' => 'views/admin/adherents/CotisationsAdherent.php',
        'desc' => 'Gestion paiements'
    ]
];

echo "<h2>📁 Vérification des pages</h2>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>Page</th><th>Description</th><th>Fichier</th><th>Statut</th><th>Test</th></tr>";

foreach ($pages as $page) {
    $exists = file_exists($page['url']);
    $status = $exists ? '✅ Existe' : '❌ Introuvable';
    $color = $exists ? 'green' : 'red';

    echo "<tr>";
    echo "<td><strong>" . $page['name'] . "</strong></td>";
    echo "<td>" . $page['desc'] . "</td>";
    echo "<td><code>" . $page['url'] . "</code></td>";
    echo "<td style='color:$color'>" . $status . "</td>";
    echo "<td>";
    if ($exists) {
        echo "<a href='" . $page['url'] . "' target='_blank' style='padding: 5px 10px; background: #007bff; color: white; text-decoration: none; border-radius: 3px;'>Tester</a>";
    } else {
        echo "N/A";
    }
    echo "</td>";
    echo "</tr>";
}

echo "</table>";

// Test BDD
echo "<h2>🗃️ Test base de données</h2>";
try {
    require_once '../config/Database.php';
    $db = new Database();
    $conn = $db->getConnection();

    echo "<p style='color:green'>✅ Connexion BDD OK</p>";

    // Compter les bénévoles
    $stmt = $conn->query("SELECT COUNT(*) as total, SUM(CASE WHEN statut = 'actif' THEN 1 ELSE 0 END) as actifs FROM benevole");
    $stats = $stmt->fetch();

    echo "<p>Bénévoles total : <strong>" . ($stats['total'] ?? 0) . "</strong></p>";
    echo "<p>Bénévoles actifs : <strong>" . ($stats['actifs'] ?? 0) . "</strong></p>";

} catch (Exception $e) {
    echo "<p style='color:red'>❌ Erreur BDD : " . $e->getMessage() . "</p>";
}

// Instructions
echo "<h2>📋 Procédure de test</h2>";
echo "<ol>";
echo "<li>Testez la page <a href='../views/auth/login.php' target='_blank'>Login</a></li>";
echo "<li>Connectez-vous avec : admin@fage.fr / password</li>";
echo "<li>Accédez au <a href='../views/admin/dashboard.php' target='_blank'>Dashboard</a></li>";
echo "<li>Cliquez sur 'Bénévoles' dans le menu</li>";
echo "<li>Testez toutes les fonctionnalités : Ajouter, Modifier, Voir détails, Cotisations</li>";
echo "</ol>";
?>