<?php
//session_start(); // Réactivé (nécessaire si tu utilises des messages flash ailleurs)
require_once __DIR__ . '/../../database/database.php'; // Chemin correct

// Fonction formatMoney (indispensable ici)
function formatMoney($amount) {
    return number_format((float)$amount, 0, ',', ' ') . ' FCFA';
}

// Récupération des filtres
$facture = $_GET['facture'] ?? '';
$debut   = $_GET['debut'] ?? '';
$fin     = $_GET['fin'] ?? '';

// Construction sécurisée de la requête
$where = "WHERE 1=1";
$params = [];

if (!empty($facture)) {
    $where .= " AND t.code_facture = ?";
    $params[] = $facture;
}
if (!empty($debut)) {
    $where .= " AND t.date_transaction >= ?";
    $params[] = $debut;
}
if (!empty($fin)) {
    $where .= " AND t.date_transaction <= ?";
    $params[] = $fin;
}

// Requête principale
$sql = "
    SELECT t.*, 
           COALESCE(c.nom_prenom_client, t.destinataire) AS nom_client,
           f.titre_facture
    FROM transactions t
    LEFT JOIN clients c ON t.destinataire = c.code_client
    LEFT JOIN factures f ON t.code_facture = f.code_facture
    $where
    ORDER BY t.date_transaction DESC, t.heure_transaction DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Liste des factures pour le filtre
$factures = $pdo->query("SELECT code_facture, titre_facture FROM factures ORDER BY titre_facture")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Soutra+ | Transactions par Facture</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card-header { background: #f8f9fa; }
        .badge-success { background-color: #28a745 !important; }
        .badge-warning { background-color: #ffc107 !important; color: #212529 !important; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <?php include __DIR__ . '/../../config/dashboard.php'; // Chemin correct ?>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1><i class="fas fa-receipt"></i> Transactions par Facture</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Accueil</a></li>
                            <li class="breadcrumb-item active">Transactions / Factures</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="card shadow">
                    <div class="card-header">
                        <form method="get" class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Facture</label>
                                <select name="facture" class="form-select">
                                    <option value="">Toutes les factures</option>
                                    <?php foreach ($factures as $f): ?>
                                        <option value="<?= htmlspecialchars($f['code_facture']) ?>" 
                                            <?= $facture === $f['code_facture'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($f['titre_facture']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Date début</label>
                                <input type="date" name="debut" class="form-control" value="<?= htmlspecialchars($debut) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Date fin</label>
                                <input type="date" name="fin" class="form-control" value="<?= htmlspecialchars($fin) ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter"></i> Filtrer
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="card-body">
                        <?php if (empty($transactions)): ?>
                            <div class="alert alert-info text-center">
                                Aucune transaction trouvée avec ces critères.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Date</th>
                                            <th>N° Transaction</th>
                                            <th>Montant Total</th>
                                            <th>Type</th>
                                            <th>Client</th>
                                            <th>Facture</th>
                                            <th>État</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($transactions as $t): ?>
                                            <tr>
                                                <td><?= date('d/m/Y', strtotime($t['date_transaction'])) ?></td>
                                                <td><strong><?= htmlspecialchars($t['numero_transaction']) ?></strong></td>
                                                <td class="text-end fw-bold text-success">
                                                    <?= formatMoney($t['montant_total']) ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?= htmlspecialchars($t['type_transaction']) ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($t['nom_client'] ?? $t['destinataire']) ?></td>
                                                <td><?= htmlspecialchars($t['titre_facture'] ?? $t['code_facture']) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $t['etat_transaction'] === 'Succès' ? 'success' : 'warning' ?>">
                                                        <?= htmlspecialchars($t['etat_transaction']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="http://localhost/soutra/transaction/impression?numero=<?= urlencode($t['numero_transaction']) ?>" 
                                                       class="btn btn-sm btn-success" target="_blank" title="Imprimer le reçu">
                                                        Reçu
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3 text-muted small">
                                <strong><?= count($transactions) ?></strong> transaction(s) trouvée(s)
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