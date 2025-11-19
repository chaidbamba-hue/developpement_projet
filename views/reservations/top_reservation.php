<?php
//session_start();
require "database/database.php";

// ==================== LISTE ====================
$stmt = $pdo->query("
    SELECT r.*, cl.nom_prenom_client, ch.nom_chambre, h.nom_hotel 
    FROM reservations r 
    LEFT JOIN clients cl ON r.code_client = cl.code_client 
    LEFT JOIN chambres ch ON r.code_chambre = ch.code_chambre 
    LEFT JOIN hotels h ON ch.code_hotel = h.code_hotel 
    ORDER BY r.montant_reservation ASC
");
$reservations = $stmt->fetchAll();

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
    <title>Soutra+ | Top des Réservations (du plus petit au plus grand montant)</title>
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
                        <h1>Top des Réservations (du plus petit au plus grand montant)</h1>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Numéro</th>
                                        <th>Date Rés.</th>
                                        <th>Client</th>
                                        <th>Chambre</th>
                                        <th>Hôtel</th>
                                        <th>Statut</th>
                                        <th>Montant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reservations as $r): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($r['numero_reservation']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($r['date_reservation'])) ?></td>
                                            <td><?= htmlspecialchars($r['nom_prenom_client'] ?? '—') ?></td>
                                            <td><?= htmlspecialchars($r['nom_chambre'] ?? '—') ?></td>
                                            <td><?= htmlspecialchars($r['nom_hotel'] ?? '—') ?></td>
                                            <td><span class="badge bg-<?= $r['statut_reservation'] === 'libre' ? 'success' : ($r['statut_reservation'] === 'occupé' ? 'danger' : 'warning') ?>"><?= ucfirst($r['statut_reservation']) ?></span></td>
                                            <td><?= htmlspecialchars($r['montant_reservation']) ?> FCFA</td>
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