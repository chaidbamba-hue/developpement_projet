<?php
//session_start();
require_once __DIR__ . '/../../database/database.php';

function formatMoney($amount)
{
    return number_format((float)$amount, 0, ',', ' ') . ' FCFA';
}

// Récupération des dates
$debut = $_GET['debut'] ?? '';
$fin   = $_GET['fin']   ?? '';

// Construction sécurisée de la clause WHERE
$where  = "WHERE 1=1";
$params = [];

if (!empty($debut)) {
    $where   .= " AND t.date_transaction >= ?";
    $params[] = $debut;
}
if (!empty($fin)) {
    $where   .= " AND t.date_transaction <= ?";
    $params[] = $fin;
}

// Requête principale : solde par client (paiements reçus vs dépenses/factures)
$sql = "
    SELECT 
        c.code_client,
        c.nom_prenom_client,
        c.telephone_client,
        COALESCE(SUM(CASE 
            WHEN t.type_transaction IN ('Paiement Réservation', 'Paiement Facture', 'paiement', 'Règlement') 
            THEN t.montant_total ELSE 0 END), 0) AS total_paye,
        COALESCE(SUM(CASE 
            WHEN t.type_transaction NOT IN ('Paiement Réservation', 'Paiement Facture', 'paiement', 'Règlement')
            AND t.type_transaction IS NOT NULL
            THEN t.montant_total ELSE 0 END), 0) AS total_du
    FROM clients c
    LEFT JOIN transactions t ON c.code_client = t.destinataire
        $where
        AND t.etat_transaction = 'Succès'
    GROUP BY c.code_client, c.nom_prenom_client, c.telephone_client
    HAVING total_paye > 0 OR total_du > 0
    ORDER BY c.nom_prenom_client ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$soldes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcul des totaux généraux
$total_paye_global = 0;
$total_du_global   = 0;
foreach ($soldes as $s) {
    $total_paye_global += $s['total_paye'];
    $total_du_global   += $s['total_du'];
}
$solde_general = $total_paye_global - $total_du_global;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Soutra+ | Solde des Clients</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .solde-credit  { color: #27ae60; font-weight: 800; }
        .solde-debit   { color: #e74c3c; font-weight: 800; }
        .card-header   { background: linear-gradient(135deg, #2c3e50, #34495e); color: white; }
        .table tr:hover { background-color: #f8f9fa; }
        .badge-paye    { background: #27ae60; }
        .badge-du      { background: #e67e22; }
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
                        <h1>Solde des Clients</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Accueil</a></li>
                            <li class="breadcrumb-item active">Solde Clients</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <!-- FILTRE PAR DATE -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">Période de référence</h3>
                    </div>
                    <div class="card-body">
                        <form method="get" class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label">Date début</label>
                                <input type="date" name="debut" class="form-control" value="<?= htmlspecialchars($debut) ?>">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Date fin</label>
                                <input type="date" name="fin" class="form-control" value="<?= htmlspecialchars($fin) ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    Filtrer
                                </button>
                            </div>
                        </form>
                        <?php if (!empty($debut) || !empty($fin)): ?>
                            <div class="mt-3 alert alert-info">
                                Période : 
                                <strong>
                                    <?= $debut ? 'Du ' . date('d/m/Y', strtotime($debut)) : 'Début' ?>
                                    <?= $fin ? ' au ' . date('d/m/Y', strtotime($fin)) : '' ?>
                                </strong>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- RÉSUMÉ GÉNÉRAL -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="info-box bg-success">
                            <span class="info-box-icon"><i class="fas fa-arrow-down"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total encaissé</span>
                                <span class="info-box-number"><?= formatMoney($total_paye_global) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-warning">
                            <span class="info-box-icon"><i class="fas fa-arrow-up"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total dû / dépensé</span>
                                <span class="info-box-number"><?= formatMoney($total_du_global) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-<?= $solde_general >= 0 ? 'primary' : 'danger' ?>">
                            <span class="info-box-icon"><i class="fas fa-balance-scale"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Solde général</span>
                                <span class="info-box-number <?= $solde_general >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <strong><?= formatMoney(abs($solde_general)) ?></strong>
                                    <small><?= $solde_general >= 0 ? 'CRÉDIT' : 'DÉBIT' ?></small>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABLEAU DES SOLDES -->
                <div class="card shadow">
                    <div class="card-header bg-gradient-dark text-white">
                        <h3 class="card-title">
                            Détail par client (<?= count($soldes) ?> client<?= count($soldes)>1?'s':'' ?>)
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Client</th>
                                        <th class="text-end">Encaissé</th>
                                        <th class="text-end">Dû / Dépensé</th>
                                        <th class="text-center">Solde Final</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($soldes as $s):
                                        $solde = $s['total_paye'] - $s['total_du'];
                                    ?>
                                        <tr>
                                            <td class="fw-bold">
                                                <?= htmlspecialchars($s['nom_prenom_client']) ?>
                                            </td>
                                            <td class="text-end text-success fw-bold">
                                                <?= formatMoney($s['total_paye']) ?>
                                            </td>
                                            <td class="text-end text-warning fw-bold">
                                                <?= formatMoney($s['total_du']) ?>
                                            </td>
                                            <td class="text-center fs-5">
                                                <?php if ($solde > 0): ?>
                                                    <span class="solde-credit">
                                                        + <?= formatMoney($solde) ?> <small>(crédit)</small>
                                                    </span>
                                                <?php elseif ($solde < 0): ?>
                                                    <span class="solde-debit">
                                                        - <?= formatMoney(abs($solde)) ?> <small>(débit)</small>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">Équilibré</span>
                                                <?php endif; ?>
                                            </td>
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
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>