<?php
//session_start();
require_once __DIR__ . '/../../database/database.php';

// Fonction formatMoney
function formatMoney($amount)
{
    return number_format((float)$amount, 0, ',', ' ') . ' FCFA';
}

// Récupération des dates
$debut = $_GET['debut'] ?? '';
$fin   = $_GET['fin']   ?? '';

// Construction de la clause WHERE + paramètres
$where  = [];
$params = [];

if (!empty($debut)) {
    $where[]  = "date_transaction >= ?";
    $params[] = $debut;
}
if (!empty($fin)) {
    $where[]  = "date_transaction <= ?";
    $params[] = $fin;
}

// Toujours ne compter que les transactions réussies
$where[] = "etat_transaction = 'Succès'";

$whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : 'WHERE etat_transaction = "Succès"';

// Requête finale
$sql = "SELECT COALESCE(SUM(montant_total), 0) AS total 
        FROM transactions 
        $whereSql";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$total = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Soutra+ | Solde Caisse</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card-solde {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .display-2 {
            font-size: 4.5rem;
            font-weight: 800;
        }
        .periode {
            background: rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <?php include __DIR__ . '/../../config/dashboard.php'; ?>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Solde de Caisse</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Accueil</a></li>
                            <li class="breadcrumb-item active">Solde Caisse</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <!-- FILTRE -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">Période de calcul</h3>
                    </div>
                    <div class="card-body">
                        <form method="get" class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label text-muted">Date de début</label>
                                <input type="date" name="debut" class="form-control" value="<?= htmlspecialchars($debut) ?>">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label text-muted">Date de fin</label>
                                <input type="date" name="fin" class="form-control" value="<?= htmlspecialchars($fin) ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    Actualiser
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- SOLDE PRINCIPAL -->
                <div class="card card-solde text-center">
                    <div class="card-body py-5">
                        <?php if (!empty($debut) || !empty($fin)): ?>
                            <div class="periode mb-4">
                                <?php if ($debut && $fin): ?>
                                    Du <?= date('d/m/Y', strtotime($debut)) ?> au <?= date('d/m/Y', strtotime($fin)) ?>
                                <?php elseif ($debut): ?>
                                    Depuis le <?= date('d/m/Y', strtotime($debut)) ?>
                                <?php elseif ($fin): ?>
                                    Jusqu'au <?= date('d/m/Y', strtotime($fin)) ?>
                                <?php else: ?>
                                    Toute la période
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <h2 class="display-2 mb-3">
                            <?= formatMoney($total) ?>
                        </h2>
                        <p class="lead opacity-90">
                            Total encaissé (transactions réussies)
                        </p>

                        <div class="mt-4">
                            <span class="badge bg-light text-dark fs-6 px-4 py-2">
                                Mise à jour : <?= date('d/m/Y à H:i') ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Infobox -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="info-box bg-success">
                            <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Paiements réussis</span>
                                <span class="info-box-number"><?= formatMoney($total) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box bg-warning">
                            <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">En attente / Échecs</span>
                                <span class="info-box-number">Non comptabilisés</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>