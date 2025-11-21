<?php
//session_start();
require "database/database.php";

// ==================== FILTRE ====================
$date_debut = $_GET['date_debut'] ?? '';
$date_fin = $_GET['date_fin'] ?? '';
$subquery = "";
$params = [];
if ($date_debut && $date_fin) {
    $subquery = "WHERE code_chambre NOT IN (
        SELECT code_chambre FROM reservations 
        WHERE statut_reservation IN ('occupé', 'réservé') 
        AND date_debut_entre <= ? AND date_fin_entre >= ?
    )";
    $params = [$date_fin, $date_debut];
}

// ==================== LISTE ====================
$stmt = $pdo->prepare("
    SELECT ch.*, h.nom_hotel 
    FROM chambres ch 
    LEFT JOIN hotels h ON ch.code_hotel = h.code_hotel 
    $subquery 
    ORDER BY ch.nom_chambre
");
$stmt->execute($params);
$chambres = $stmt->fetchAll();

// ==================== UTILISATEUR CONNECTÉ ====================
$user_name = "Jean Dupont";
$user_role = "Administrateur";
$user_photo = "https://via.placeholder.com/160x160/007bff/ffffff?text=JD";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Soutra+ | Chambres Libres sur Période</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <?php include 'config/dashboard.php'; ?>
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Chambres Libres sur Période</h1>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <form method="get" class="row g-3">
                            <div class="col-md-5">
                                <input type="date" name="date_debut" class="form-control" value="<?= htmlspecialchars($date_debut) ?>">
                            </div>
                            <div class="col-md-5">
                                <input type="date" name="date_fin" class="form-control" value="<?= htmlspecialchars($date_fin) ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">Filtrer</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Code</th>
                                        <th>Nom</th>
                                        <th>Type</th>
                                        <th>Prix</th>
                                        <th>Hôtel</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($chambres as $ch): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($ch['code_chambre']) ?></td>
                                            <td><?= htmlspecialchars($ch['nom_chambre']) ?></td>
                                            <td><?= ucfirst(str_replace('chambre ', '', $ch['type_chambre'])) ?></td>
                                            <td><?= htmlspecialchars($ch['prix_chambre']) ?> FCFA</td>
                                            <td><?= htmlspecialchars($ch['nom_hotel'] ?? '—') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <footer class="main-footer">
        <strong>© 2025 <a href="#">Soutra+</a>.</strong> Tous droits réservés.
    </footer>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>