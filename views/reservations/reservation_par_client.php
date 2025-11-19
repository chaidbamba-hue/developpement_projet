<?php
//session_start();
require "database/database.php";

// ==================== FILTRE ====================
$code_client = $_GET['code_client'] ?? '';
$date_debut = $_GET['date_debut'] ?? '';
$date_fin = $_GET['date_fin'] ?? '';
$where = "WHERE 1=1";
$params = [];
if ($code_client) {
    $where .= " AND r.code_client = ?";
    $params[] = $code_client;
}
if ($date_debut && $date_fin) {
    $where .= " AND r.date_reservation BETWEEN ? AND ?";
    $params[] = $date_debut;
    $params[] = $date_fin;
}

// ==================== LISTE ====================
$stmt = $pdo->prepare("
    SELECT r.*, cl.nom_prenom_client, ch.nom_chambre, h.nom_hotel 
    FROM reservations r 
    LEFT JOIN clients cl ON r.code_client = cl.code_client 
    LEFT JOIN chambres ch ON r.code_chambre = ch.code_chambre 
    LEFT JOIN hotels h ON ch.code_hotel = h.code_hotel 
    $where 
    ORDER BY r.date_reservation DESC
");
$stmt->execute($params);
$reservations = $stmt->fetchAll();

$clients = $pdo->query("SELECT code_client, nom_prenom_client FROM clients ORDER BY nom_prenom_client")->fetchAll();

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
    <title>Soutra+ | Réservations par Client</title>
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
                        <h1>Réservations par Client</h1>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <form method="get" class="row g-3">
                            <div class="col-md-4">
                                <select name="code_client" class="form-control">
                                    <option value="">-- Sélectionner un client --</option>
                                    <?php foreach ($clients as $cl): ?>
                                        <option value="<?= $cl['code_client'] ?>" <?= $code_client == $cl['code_client'] ? 'selected' : '' ?>><?= htmlspecialchars($cl['nom_prenom_client']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="date" name="date_debut" class="form-control" value="<?= htmlspecialchars($date_debut) ?>">
                            </div>
                            <div class="col-md-3">
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