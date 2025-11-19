<?php
ob_start(); // Protection contre les erreurs de sortie avant PDF
//session_start();
require_once __DIR__ . '/../../database/database.php';
require_once __DIR__ . '/../../libraries/fpdf/fpdf.php';

function formatMoney($amount)
{
    return number_format((float)$amount, 0, ',', ' ') . ' FCFA';
}

// === FILTRES ===
$numero   = trim($_GET['numero'] ?? '');
$type     = $_GET['type'] ?? '';
$caissier = $_GET['caissier'] ?? '';
$debut    = $_GET['debut'] ?? '';
$fin      = $_GET['fin'] ?? '';

$where  = "WHERE t.etat_transaction = 'Succès'";
$params = [];

if ($numero) {
    $where .= " AND t.numero_transaction LIKE ?";
    $params[] = "%$numero%";
}
if ($type) {
    $where .= " AND t.type_transaction = ?";
    $params[] = $type;
}
if ($caissier) {
    $where .= " AND u.utilisateur_id = ?";
    $params[] = $caissier;
}
if ($debut) {
    $where .= " AND t.date_transaction >= ?";
    $params[] = $debut;
}
if ($fin) {
    $where .= " AND t.date_transaction <= ?";
    $params[] = $fin;
}

// Requête principale
$sql = "
    SELECT t.*, 
           COALESCE(c.nom_prenom_client, t.destinataire) AS client,
           u.nom_prenom AS caissier
    FROM transactions t
    LEFT JOIN clients c ON t.destinataire = c.code_client
    LEFT JOIN utilisateurs u ON t.utilisateur_id = u.utilisateur_id
    $where
    ORDER BY t.date_transaction DESC, t.heure_transaction DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$trans = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = array_sum(array_column($trans, 'montant_total'));

// Liste pour les filtres
$types = $pdo->query("SELECT DISTINCT type_transaction FROM transactions WHERE type_transaction IS NOT NULL ORDER BY type_transaction")->fetchAll(PDO::FETCH_COLUMN);
$caissiers = $pdo->query("SELECT utilisateur_id, nom_prenom FROM utilisateurs ORDER BY nom_prenom")->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Soutra+ | Rapport des Transactions</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .btn-pdf { background: #dc3545; color: white; }
        .btn-pdf:hover { background: #c82333; }
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
                        <h1>Rapport des Transactions</h1>
                    </div>
                    <div class="col-sm-6 text-end">
                        <button onclick="window.print()" class="btn btn-secondary">
                            Imprimer
                        </button>
                        <a href="<?= $_SERVER['PHP_SELF'] . '?' . http_build_query($_GET) ?>" 
                           class="btn btn-pdf" target="_blank">
                            Générer le PDF
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <!-- FILTRES -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">Filtres avancés</h3>
                    </div>
                    <div class="card-body">
                        <form method="get" class="row g-3">
                            <div class="col-md-3">
                                <input type="text" name="numero" class="form-control" placeholder="N° transaction" value="<?= htmlspecialchars($numero) ?>">
                            </div>
                            <div class="col-md-3">
                                <select name="type" class="form-select">
                                    <option value="">Tous les types</option>
                                    <?php foreach ($types as $t): ?>
                                        <option value="<?= htmlspecialchars($t) ?>" <?= $type === $t ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($t) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="caissier" class="form-select">
                                    <option value="">Tous les caissiers</option>
                                    <?php foreach ($caissiers as $id => $nom): ?>
                                        <option value="<?= $id ?>" <?= $caissier == $id ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($nom) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="debut" class="form-control" value="<?= htmlspecialchars($debut) ?>">
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="fin" class="form-control" value="<?= htmlspecialchars($fin) ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-success w-100">Filtrer</button>
                            </div>
                        </form>

                        <?php if (!empty($debut) || !empty($fin) || $numero || $type || $caissier): ?>
                            <div class="alert alert-info mt-3">
                                <strong><?= count($trans) ?></strong> transaction(s) trouvée(s) 
                                → Total : <strong class="text-success fs-5"><?= formatMoney($total) ?></strong>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- TABLEAU -->
                <div class="card shadow">
                    <div class="card-header bg-dark text-white">
                        <h3 class="card-title">
                            Transactions (<?= count($trans) ?>)
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Date</th>
                                        <th>N° Transaction</th>
                                        <th>Client</th>
                                        <th>Type</th>
                                        <th>Caissier</th>
                                        <th class="text-end">Montant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($trans as $t): ?>
                                        <tr>
                                            <td><?= date('d/m/Y H:i', strtotime($t['date_transaction'] . ' ' . $t['heure_transaction'])) ?></td>
                                            <td><strong><?= htmlspecialchars($t['numero_transaction']) ?></strong></td>
                                            <td><?= htmlspecialchars($t['client']) ?></td>
                                            <td><span class="badge bg-info"><?= htmlspecialchars($t['type_transaction']) ?></span></td>
                                            <td><?= htmlspecialchars($t['caissier'] ?? 'Système') ?></td>
                                            <td class="text-end fw-bold text-success">
                                                <?= formatMoney($t['montant_total']) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-dark">
                                    <tr>
                                        <th colspan="5" class="text-end">TOTAL GÉNÉRAL</th>
                                        <th class="text-end text-white fs-5"><?= formatMoney($total) ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- GÉNÉRATION DU PDF SI ON CLIQUE SUR LE BOUTON -->
<?php
if (isset($_GET['pdf']) || (isset($_SERVER['HTTP_SEC_FETCH_DEST']) && $_SERVER['HTTP_SEC_FETCH_DEST'] === 'document')) {
    ob_end_clean();

    class PDF extends FPDF {
        function Header() {
            $this->SetFont('Arial', 'B', 18);
            $this->Cell(0, 12, 'RAPPORT DES TRANSACTIONS', 0, 1, 'C');
            $this->Ln(5);
            $this->SetFont('Arial', 'B', 14);
            $this->Cell(0, 10, 'HÔTEL SOUTRA+', 0, 1, 'C');
            $this->SetFont('Arial', '', 10);
            $this->Cell(0, 6, 'Généré le ' . date('d/m/Y à H:i'), 0, 1, 'C');
            if (!empty($debut) || !empty($fin)) {
                $this->Cell(0, 6, 'Période : ' . ($debut ? 'Du ' . date('d/m/Y', strtotime($debut)) : '') . ($fin ? ' au ' . date('d/m/Y', strtotime($fin)) : ''), 0, 1, 'C');
            }
            $this->Ln(10);
        }
        function Footer() {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 8);
            $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
        }
    }

    $pdf = new PDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetFillColor(230,230,230);

    // En-tête tableau
    $pdf->Cell(25, 8, 'N°', 1, 0, 'C', true);
    $pdf->Cell(30, 8, 'Date', 1, 0, 'C', true);
    $pdf->Cell(50, 8, 'Client', 1, 0, 'C', true);
    $pdf->Cell(40, 8, 'Type', 1, 0, 'C', true);
    $pdf->Cell(30, 8, 'Montant', 1, 1, 'R', true);

    foreach ($trans as $t) {
        $pdf->Cell(25, 7, $t['numero_transaction'], 1);
        $pdf->Cell(30, 7, date('d/m/Y', strtotime($t['date_transaction'])), 1);
        $pdf->Cell(50, 7, substr($t['client'], 0, 25), 1);
        $pdf->Cell(40, 7, $t['type_transaction'], 1);
        $pdf->Cell(30, 7, formatMoney($t['montant_total']), 1, 1, 'R');
    }

    $pdf->Ln(8);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(200,220,255);
    $pdf->Cell(145, 10, 'TOTAL GENERAL', 1, 0, 'R', true);
    $pdf->Cell(30, 10, formatMoney($total), 1, 1, 'R', true);

    $filename = 'Rapport_Transactions_' . date('Y-m-d') . '.pdf';
    $pdf->Output('D', $filename);
    exit;
}
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>