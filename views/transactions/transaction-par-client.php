<?php
//session_start();
require "database/database.php";
// require "config/fonctions.php";

$client = $_GET['client'] ?? '';
$debut = $_GET['debut'] ?? '';
$fin = $_GET['fin'] ?? '';

$where = "WHERE 1=1";
$params = [];
if ($client) { $where .= " AND t.destinataire = ?"; $params[] = $client; }
if ($debut) { $where .= " AND t.date_transaction >= ?"; $params[] = $debut; }
if ($fin) { $where .= " AND t.date_transaction <= ?"; $params[] = $fin; }

$stmt = $pdo->prepare("
    SELECT t.*, c.nom_prenom_client, f.titre_facture 
    FROM transactions t 
    LEFT JOIN clients c ON t.destinataire = c.code_client 
    LEFT JOIN factures f ON t.code_facture = f.code_facture 
    $where 
    ORDER BY t.date_transaction DESC
");
$stmt->execute($params);
$transactions = $stmt->fetchAll();

$clients = $pdo->query("SELECT code_client, nom_prenom_client FROM clients ORDER BY nom_prenom_client")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Soutra+ | Transactions par Client</title>
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
                <h1>Transactions par Client</h1>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <form method="get" class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label>Client</label>
                                <select name="client" class="form-control">
                                    <option value="">-- Tous --</option>
                                    <?php foreach ($clients as $c): ?>
                                        <option value="<?= $c['code_client'] ?>" <?= $client == $c['code_client'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($c['nom_prenom_client']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Début</label>
                                <input type="date" name="debut" class="form-control" value="<?= $debut ?>">
                            </div>
                            <div class="col-md-3">
                                <label>Fin</label>
                                <input type="date" name="fin" class="form-control" value="<?= $fin ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">
                        <?php if (empty($transactions)): ?>
                            <div class="alert alert-info">Aucune transaction.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Date</th>
                                            <th>N° Trans</th>
                                            <th>Montant</th>
                                            <th>Type</th>
                                            <th>Facture</th>
                                            <th>État</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($transactions as $t): ?>
                                            <tr>
                                                <td><?= date('d/m/Y H:i', strtotime($t['date_transaction'] . ' ' . $t['heure_transaction'])) ?></td>
                                                <td><?= htmlspecialchars($t['numero_transaction']) ?></td>
                                                <td><?= formatMoney($t['montant_total']) ?></td>
                                                <td><?= htmlspecialchars($t['type_transaction']) ?></td>
                                                <td><?= htmlspecialchars($t['titre_facture'] ?? $t['code_facture']) ?></td>
                                                <td><span class="badge bg-<?= $t['etat_transaction'] === 'Succès' ? 'success' : 'warning' ?>"><?= $t['etat_transaction'] ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
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