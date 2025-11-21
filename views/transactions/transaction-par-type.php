<?php
//session_start();
require_once __DIR__ . '/../../database/database.php';   // Chemin correct

// Fonction formatMoney (indispensable)
function formatMoney($amount)
{
    return number_format((float)$amount, 0, ',', ' ') . ' FCFA';
}

// Récupération des filtres
$type  = $_GET['type']  ?? '';
$debut = $_GET['debut'] ?? '';
$fin   = $_GET['fin']   ?? '';

// Construction sécurisée de la requête
$where  = "WHERE 1=1";
$params = [];

if (!empty($type)) {
    $where   .= " AND t.type_transaction = ?";
    $params[] = $type;
}
if (!empty($debut)) {
    $where   .= " AND t.date_transaction >= ?";
    $params[] = $debut;
}
if (!empty($fin)) {
    $where   .= " AND t.date_transaction <= ?";
    $params[] = $fin;
}

// Requête principale (avec jointures pour avoir le nom du client et le titre de facture)
$sql = "
    SELECT t.*,
           COALESCE(c.nom_prenom_client, t.destinataire) AS nom_client,
           f.titre_facture
    FROM transactions t
    LEFT JOIN clients   c ON t.destinataire = c.code_client
    LEFT JOIN factures  f ON t.code_facture = f.code_facture
    $where
    ORDER BY t.date_transaction DESC, t.heure_transaction DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Liste des types existants pour le filtre
$types = $pdo->query("SELECT DISTINCT type_transaction FROM transactions WHERE type_transaction IS NOT NULL ORDER BY type_transaction")
             ->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Soutra+ | Transactions par Type</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .badge-success { background:#28a745 !important; }
        .badge-warning { background:#ffc107 !important; color:#212529 !important; }
        .badge-danger  { background:#dc3545 !important; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <?php include __DIR__ . '/../../config/dashboard.php'; ?>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6"><h1>Transactions par Type</h1></div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Accueil</a></li>
                            <li class="breadcrumb-item active">Transactions / Type</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <form method="get" class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label text-white fw-bold">Type de transaction</label>
                                <select name="type" class="form-select">
                                    <option value="">Toutes les transactions</option>
                                    <?php foreach ($types as $t): ?>
                                        <option value="<?= htmlspecialchars($t) ?>" <?= $type === $t ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($t) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-white fw-bold">Date début</label>
                                <input type="date" name="debut" class="form-control" value="<?= htmlspecialchars($debut) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-white fw-bold">Date fin</label>
                                <input type="date" name="fin" class="form-control" value="<?= htmlspecialchars($fin) ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-light w-100 fw-bold">
                                    Filtrer
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="card-body">
                        <?php if (empty($transactions)): ?>
                            <div class="alert alert-info text-center py-4">
                                Aucune transaction ne correspond à vos critères.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Date & Heure</th>
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
                                                <td><?= date('d/m/Y H:i', strtotime($t['date_transaction'] . ' ' . $t['heure_transaction'])) ?></td>
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
                                                    <span class="badge bg-<?= $t['etat_transaction'] === 'Succès' ? 'success' : ($t['etat_transaction'] === 'Echec' ? 'danger' : 'warning') ?>">
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

                            <div class="mt-3 text-muted">
                                <strong><?= count($transactions) ?></strong> transaction(s) affichée(s)
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