<?php
//session_start();
require "database/database.php";

// ==================== RÉCUPÉRER TOUTES LES CHAMBRES + HÔTELS ====================
$stmt = $pdo->query("
    SELECT c.*, h.nom_hotel, h.code_hotel 
    FROM chambres c 
    LEFT JOIN hotels h ON c.code_hotel = h.code_hotel 
    ORDER BY h.code_hotel, c.prix_chambre + 0
");
$all_chambres = $stmt->fetchAll();

// Regrouper par CODE_HOTEL (fiable)
$chambres_par_hotel = [];
foreach ($all_chambres as $c) {
    $code_hotel = $c['code_hotel'] ?? 'INCONNU';
    $nom_hotel  = $c['nom_hotel'] ?? 'Hôtel non défini';

    // Clé unique = code_hotel
    if (!isset($chambres_par_hotel[$code_hotel])) {
        $chambres_par_hotel[$code_hotel] = [
            'nom' => $nom_hotel,
            'chambres' => []
        ];
    }
    $chambres_par_hotel[$code_hotel]['chambres'][] = $c;
}

// Trier chaque hôtel par prix croissant
foreach ($chambres_par_hotel as $code => &$data) {
    usort($data['chambres'], function($a, $b) {
        $prixA = is_numeric($a['prix_chambre']) ? (float)$a['prix_chambre'] : PHP_INT_MAX;
        $prixB = is_numeric($b['prix_chambre']) ? (float)$b['prix_chambre'] : PHP_INT_MAX;
        return $prixA <=> $prixB;
    });
}
unset($data); // bonne pratique

$user_name = "Jean Dupont";
$user_role = "Administrateur";
$user_photo = "https://via.placeholder.com/160x160/007bff/ffffff?text=JD";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hotelio | Chambres par Hôtel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .user-panel img { width: 2.1rem; height: 2.1rem; object-fit: cover; }
        .nav-sidebar .nav-link { border-radius: 0.25rem; }
        .card { transition: transform 0.2s; }
        .card:hover { transform: translateY(-2px); }
        .badge-type { font-size: 0.8rem; }
        .hotel-card { border-left: 4px solid #007bff; }
        .price-rank { width: 28px; height: 28px; font-size: 0.75rem; }
        .top-chambre { background-color: #f8f9fa; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <?php include 'config/dashboard.php'; ?>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Chambres par Hôtel (Triées par prix)</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Accueil</a></li>
                            <li class="breadcrumb-item active">Chambres</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="mb-3">
                    <a href="enregistrement" class="btn btn-secondary">
                        Gestion des chambres
                    </a>
                </div>

                <?php if (empty($chambres_par_hotel)): ?>
                    <div class="alert alert-info text-center">
                        Aucune chambre enregistrée.
                    </div>
                <?php else: ?>
                    <?php foreach ($chambres_par_hotel as $code_hotel => $data): ?>
                        <?php $hotel = $data['nom']; $chambres = $data['chambres']; ?>
                        <div class="card hotel-card shadow-sm mb-4">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-hotel"></i> <?= htmlspecialchars($hotel) ?>
                                    <small class="text-light ms-2">(<?= $code_hotel ?>)</small>
                                </h5>
                                <span class="badge bg-light text-dark">
                                    <?= count($chambres) ?> chambre<?= count($chambres) > 1 ? 's' : '' ?>
                                </span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Code</th>
                                                <th>Nom</th>
                                                <th>Type</th>
                                                <th>Prix</th>
                                                <th>État</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($chambres as $index => $c): ?>
                                                <?php 
                                                $isTop = $index < 3;
                                                $rankBadge = match($index) {
                                                    0 => 'bg-warning text-dark',
                                                    1 => 'bg-secondary text-white',
                                                    2 => 'bg-danger text-white',
                                                    default => 'bg-light text-dark'
                                                };
                                                ?>
                                                <tr <?= $isTop ? 'class="top-chambre"' : '' ?>>
                                                    <td>
                                                        <span class="badge rounded-pill <?= $rankBadge ?> price-rank d-flex align-items-center justify-content-center">
                                                            <?= $index + 1 ?>
                                                        </span>
                                                    </td>
                                                    <td><strong><?= htmlspecialchars($c['code_chambre']) ?></strong></td>
                                                    <td><?= htmlspecialchars($c['nom_chambre']) ?></td>
                                                    <td>
                                                        <span class="badge bg-info text-dark badge-type">
                                                            <?= ucwords(str_replace('chambre ', '', $c['type_chambre'])) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <strong class="text-success">
                                                            <?php
                                                            $prix = $c['prix_chambre'];
                                                            echo is_numeric($prix) ? number_format((float)$prix) . ' FCFA' : htmlspecialchars($prix);
                                                            ?>
                                                        </strong>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $etat = $c['etat_chambre'];
                                                        $badge = match($etat) {
                                                            'disponible' => 'success',
                                                            'occupée' => 'danger',
                                                            'réservée' => 'warning',
                                                            'maintenance' => 'secondary',
                                                            default => 'dark'
                                                        };
                                                        ?>
                                                        <span class="badge bg-<?= $badge ?>">
                                                            <?= ucfirst($etat) ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>© 2025 <a href="#">Soutra+</a>.</strong> Tous droits réservés.
        <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
    </footer>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>