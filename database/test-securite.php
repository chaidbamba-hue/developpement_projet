<?php
define('APP_ENTRY', true);
define('APP_ENV', 'DEV'); // On force DEV pour voir les erreurs
require __DIR__ . '/database.php';

echo "<h2>🚀 Diagnostic Sécurité PHP</h2>";

// 1) Test session
echo "<strong>Session :</strong> ";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✅ Session démarrée<br>";
} else {
    echo "❌ Session NON démarrée<br>";
}

// 2) Test token CSRF
echo "<strong>Token CSRF :</strong> ";
if (!empty($_SESSION['csrf_token'])) {
    echo "✅ " . substr($_SESSION['csrf_token'], 0, 10) . "...<br>";
} else {
    echo "❌ Token CSRF absent<br>";
}

// 3) Test connexion PDO
echo "<strong>Connexion Base de Données :</strong> ";
try {
    $stmt = $pdo->query("SELECT NOW() AS date_test");
    $res = $stmt->fetch();
    echo "✅ Connexion OK, heure DB : " . $res['date_test'] . "<br>";
} catch (Exception $e) {
    echo "❌ Erreur PDO : " . safe_output($e->getMessage()) . "<br>";
}

// 4) Test écriture fichier logs
echo "<strong>Écriture logs :</strong> ";
$logTest = LOG_DIR . '/test.log';
if (file_put_contents($logTest, "Test log: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND) !== false) {
    echo "✅ Écriture OK dans " . safe_output($logTest) . "<br>";
} else {
    echo "❌ Impossible d'écrire dans " . safe_output(LOG_DIR) . "<br>";
}

// 5) Test en-têtes HTTP
echo "<strong>En-têtes HTTP :</strong><br>";
$headers = headers_list();
foreach ($headers as $h) {
    echo " - " . safe_output($h) . "<br>";
}

// 6) Test journalisation sécurité
security_log("Test journalisation depuis diagnostic.php");
echo "<br>📜 Une entrée de test a été ajoutée dans security.log";
