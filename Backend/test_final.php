<?php
session_start();

echo "<h1>Test Configuration PHP/MySQL</h1>";

// 1. Test extension_dir
echo "<h2>1. Configuration :</h2>";
echo "extension_dir: " . ini_get('extension_dir') . "<br>";

// 2. Test extensions
echo "<h2>2. Extensions MySQL :</h2>";
$mysql_extensions = ['mysqli', 'pdo_mysql', 'mbstring', 'openssl'];
foreach ($mysql_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<span style='color: green;'>✅ $ext chargée</span><br>";
    } else {
        echo "<span style='color: red;'>❌ $ext NON chargée</span><br>";
    }
}

// 3. Test connexion MySQL
echo "<h2>3. Test Connexion MySQL :</h2>";
try {
    // Essaie plusieurs combinaisons courantes
    $configs = [
        ['host' => 'localhost', 'user' => 'root', 'pass' => ''],
        ['host' => '127.0.0.1', 'user' => 'root', 'pass' => 'root'],
        ['host' => 'localhost', 'user' => 'root', 'pass' => 'root']
    ];

    $connected = false;
    foreach ($configs as $config) {
        try {
            $pdo = new PDO(
                "mysql:host={$config['host']}",
                $config['user'],
                $config['pass']
            );
            echo "<span style='color: green;'>✅ Connexion réussie avec :</span><br>";
            echo "Host: {$config['host']}<br>";
            echo "User: {$config['user']}<br>";
            echo "Password: " . (empty($config['pass']) ? '(vide)' : '********') . "<br>";

            // Créer la base si elle n'existe pas
            $pdo->exec("CREATE DATABASE IF NOT EXISTS fage_backoffice");
            echo "Base 'fage_backoffice' créée/accessible<br>";

            $connected = true;
            break;

        } catch (PDOException $e) {
            continue; // Essaie la configuration suivante
        }
    }

    if (!$connected) {
        echo "<span style='color: orange;'>⚠ Aucune connexion MySQL trouvée</span><br>";
        echo "Installe/configure :<br>";
        echo "1. <a href='https://www.apachefriends.org/'>XAMPP</a> (recommandé)<br>";
        echo "2. Ou <a href='https://www.wampserver.com/'>WAMP</a><br>";
        echo "3. Ou MySQL standalone";
    }

} catch (PDOException $e) {
    echo "<span style='color: red;'>❌ Erreur PDO : " . $e->getMessage() . "</span>";
}

// 4. Test sessions
echo "<h2>4. Sessions :</h2>";
$_SESSION['test'] = 'OK';
echo "Sessions : " . (isset($_SESSION['test']) ? '✅ Fonctionne' : '❌ Échec');
?>