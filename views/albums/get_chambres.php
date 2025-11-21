<?php
header('Content-Type: application/json');
require "database/database.php";

$hotel = $_GET['hotel'] ?? '';
if (!$hotel) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT code_chambre, nom_chambre, type_chambre 
    FROM chambres 
    WHERE code_hotel = ? 
    ORDER BY nom_chambre
");
$stmt->execute([$hotel]);
$chambres = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($chambres);
?>