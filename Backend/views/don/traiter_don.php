<?php
// Backend/views/don/traiter_don.php

// Handler minimal pour restaurer l'accès depuis HTML/Dons_Engagement.html
// Il valide le POST, logue l'entrée dans Backend/logs/donations.log et redirige.

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../HTML/Dons_Engagement.html');
    exit;
}

$prenom   = trim($_POST['prenom'] ?? '');
$nom      = trim($_POST['nom'] ?? '');
$email    = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL) ? trim($_POST['email']) : '';
$telephone= trim($_POST['telephone'] ?? '');
$montant  = floatval($_POST['montant'] ?? 0);
$frequence= trim($_POST['frequence'] ?? '');
$paiement = trim($_POST['paiement'] ?? '');
$engagement = isset($_POST['engagement']) ? 'oui' : 'non';
$receipt  = isset($_POST['receipt']) ? 'oui' : 'non';
$rgpd     = isset($_POST['rgpd']);

if (!$rgpd || $montant < 1 || !$email || !$prenom || !$nom) {
    header('Location: ../../HTML/Dons_Engagement.html?error=1');
    exit;
}

$logDir = __DIR__ . '/../../logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}
$logFile = $logDir . '/donations.log';
$entry = sprintf("%s | %s | %s | %s | %s | %.2f | %s | %s | %s\n",
    date('c'), $prenom, $nom, $email, $telephone, $montant, $frequence, $paiement, $engagement
);
@file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);

header('Location: ../../HTML/Dons_Engagement.html?success=1');
exit;
