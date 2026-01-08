<?php
// Backend/test_session.php
session_start();

echo "<h1>Test de session</h1>";
echo "<p>Session ID : " . session_id() . "</p>";
echo "<p>Fichier session : " . session_save_path() . "</p>";

// Tester la redirection
echo "<h2>Test de redirection :</h2>";
echo "<p><a href='views/auth/login.php'>1. Vers login.php</a></p>";
echo "<p><a href='admin/dashboard.php'>2. Vers dashboard.php (devrait rediriger vers login)</a></p>";

// Tester session_check.php directement
echo "<h2>Test session_check.php :</h2>";
echo "<pre>";
include 'test/session_check.php';
echo "</pre>";
?>