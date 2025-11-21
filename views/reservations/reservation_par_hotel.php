<?php
require "database/database.php";

// Inclusion FPDF seulement pour l'export PDF
if (isset($_POST['export']) && $_POST['export'] === 'pdf') {
    require_once 'librairiesfpdf/fpdf/fpdf.php';
}

// ==================== FILTRES ====================
$code_hotel = $_GET['code_hotel'] ?? '';
$date_debut = $_GET['date_debut'] ?? '';
$date_fin   = $_GET['date_fin'] ?? '';

$where = "WHERE 1=1";
$params = [];

if ($code_hotel !== '') {
    $where .= " AND ch.code_hotel = ?";
    $params[] = $code_hotel;
}
if ($date_debut !== '' && $date_fin !== '') {
    $where .= " AND r.date_reservation BETWEEN ? AND ?";
    $params[] = $date_debut;
    $params[] = $date_fin;
}

// ==================== EXPORT (CSV / EXCEL / PDF) ====================
if (isset($_POST['export']) && in_array($_POST['export'], ['excel', 'csv', 'pdf'])) {

    $sql = "
        SELECT 
            r.*,
            cl.nom_prenom_client,
            ch.nom_chambre,
            ch.code_chambre,
            h.nom_hotel,
            h.code_hotel
        FROM reservations r
        LEFT JOIN clients cl ON r.code_client = cl.code_client
        LEFT JOIN chambres ch ON r.code_chambre = ch.code_chambre
        LEFT JOIN hotels h ON ch.code_hotel = h.code_hotel
        $where
        ORDER BY h.code_hotel, r.date_reservation DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Nettoyage buffer
    if (ob_get_level()) ob_end_clean();

    // ====================== CSV ======================
    if ($_POST['export'] === 'csv') {
        $filename = 'Reservations_' . date('d-m-Y_H-i') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo "\xEF\xBB\xBF"; // BOM UTF-8
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Hôtel','Code Hôtel','N° Réserv.','Date','Client','Chambre','Début','Fin','Durée','Montant','Statut'], ';');

        foreach ($data as $row) {
            fputcsv($output, [
                $row['nom_hotel'] ?? 'Inconnu',
                $row['code_hotel'] ?? '',
                $row['numero_reservation'],
                date('d/m/Y', strtotime($row['date_reservation'])),
                $row['nom_prenom_client'] ?? '—',
                $row['nom_chambre'] ?? $row['code_chambre'],
                date('d/m/Y', strtotime($row['date_debut'])),
                date('d/m/Y', strtotime($row['date_fin'])),
                $row['duree_jours'],
                number_format($row['montant_reservation']) . ' FCFA',
                ucfirst($row['statut_reservation'] ?? '')
            ], ';');
        }
        exit;
    }

    // ====================== EXCEL ======================
    if ($_POST['export'] === 'excel') {
        $filename = 'Reservations_' . date('d-m-Y_H-i') . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo "\xEF\xBB\xBF";
        ?>
        <table border="1">
            <tr style="background:#007bff;color:white;font-weight:bold;">
                <th>Hôtel</th><th>N° Réserv.</th><th>Date</th><th>Client</th><th>Chambre</th><th>Début</th><th>Fin</th><th>Durée</th><th>Montant</th><th>Statut</th>
            </tr>
            <?php foreach ($data as $row): ?>
            <tr align="center">
                <td><?= htmlspecialchars($row['nom_hotel'] ?? 'Inconnu') ?></td>
                <td><?= htmlspecialchars($row['numero_reservation']) ?></td>
                <td><?= date('d/m/Y', strtotime($row['date_reservation'])) ?></td>
                <td><?= htmlspecialchars($row['nom_prenom_client'] ?? '—') ?></td>
                <td><?= htmlspecialchars($row['nom_chambre'] ?? $row['code_chambre']) ?></td>
                <td><?= date('d/m/Y', strtotime($row['date_debut'])) ?></td>
                <td><?= date('d/m/Y', strtotime($row['date_fin'])) ?></td>
                <td><?= $row['duree_jours'] ?></td>
                <td><?= number_format($row['montant_reservation']) ?> FCFA</td>
                <td><?= ucfirst($row['statut_reservation'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php
        exit;
    }

    // ====================== PDF ======================
    if ($_POST['export'] === 'pdf') {
        $pdf = new FPDF('L', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetFillColor(0, 123, 255);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 12, 'Liste des Réservations (Filtrées)', 0, 1, 'C', true);
        $pdf->Ln(5);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 8, 'Généré le ' . date('d/m/Y à H:i'), 0, 1, 'R');
        $pdf->Ln(5);

        $currentHotel = '';
        foreach ($data as $row) {
            $hotel = ($row['nom_hotel'] ?? 'Inconnu') . ' (' . ($row['code_hotel'] ?? '') . ')';
            if ($currentHotel !== $hotel) {
                if ($currentHotel !== '') $pdf->Ln(8);
                $currentHotel = $hotel;
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->SetFillColor(0, 123, 255);
                $pdf->SetTextColor(255, 255, 255);
                $pdf->Cell(0, 10, $hotel, 0, 1, 'L', true);
                $pdf->Ln(3);

                $pdf->SetFillColor(220, 220, 220);
                $pdf->SetTextColor(0);
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell(20, 8, 'N°', 1, 0, 'C', true);
                $pdf->Cell(30, 8, 'Réserv.', 1, 0, 'C', true);
                $pdf->Cell(30, 8, 'Date', 1, 0, 'C', true);
                $pdf->Cell(60, 8, 'Client', 1, 0, 'C', true);
                $pdf->Cell(40, 8, 'Chambre', 1, 0, 'C', true);
                $pdf->Cell(25, 8, 'Début', 1, 0, 'C', true);
                $pdf->Cell(25, 8, 'Fin', 1, 0, 'C', true);
                $pdf->Cell(20, 8, 'Jours', 1, 0, 'C', true);
                $pdf->Cell(30, 8, 'Montant', 1, 1, 'C', true);
            }
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(20, 8, '', 1, 0, 'C');
            $pdf->Cell(30, 8, $row['numero_reservation'], 1, 0, 'C');
            $pdf->Cell(30, 8, date('d/m/Y', strtotime($row['date_reservation'])), 1, 0, 'C');
            $pdf->Cell(60, 8, mb_substr($row['nom_prenom_client'] ?? '—', 0, 30), 1, 0, 'L');
            $pdf->Cell(40, 8, $row['nom_chambre'] ?? $row['code_chambre'], 1, 0, 'L');
            $pdf->Cell(25, 8, date('d/m/Y', strtotime($row['date_debut'])), 1, 0, 'C');
            $pdf->Cell(25, 8, date('d/m/Y', strtotime($row['date_fin'])), 1, 0, 'C');
            $pdf->Cell(20, 8, $row['duree_jours'], 1, 0, 'C');
            $pdf->Cell(30, 8, number_format($row['montant_reservation']) . ' FCFA', 1, 1, 'C');
        }
        $pdf->Output('D', 'Reservations_' . date('d-m-Y_H-i') . '.pdf');
        exit;
    }
}

// ==================== CHARGEMENT DONNÉES AVEC FILTRES ====================
$sql = "
    SELECT 
        r.*,
        cl.nom_prenom_client,
        ch.nom_chambre,
        ch.code_chambre,
        h.nom_hotel,
        h.code_hotel
    FROM reservations r
    LEFT JOIN clients cl ON r.code_client = cl.code_client
    LEFT JOIN chambres ch ON r.code_chambre = ch.code_chambre
    LEFT JOIN hotels h ON ch.code_hotel = h.code_hotel
    $where
    ORDER BY h.code_hotel, r.date_reservation DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$all = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Regroupement par hôtel
$group = [];
foreach ($all as $r) {
    $code = $r['code_hotel'] ?? 'INCONNU';
    $group[$code]['nom'] = $r['nom_hotel'] ?? 'Hôtel inconnu';
    $group[$code]['list'][] = $r;
}

$hotels = $pdo->query("SELECT code_hotel, nom_hotel FROM hotels ORDER BY nom_hotel")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hotelio | Réservations (Filtrées par Hôtel & Date)</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6/css/all.min.css">
    <style>
        .badge-reservé { background:#ffc107; color:#000; }
        .badge-occupé { background:#dc3545; color:#fff; }
        .badge-libre { background:#28a745; color:#fff; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <?php include 'config/dashboard.php'; ?>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <h1 class="mb-0">Réservations par Hôtel <?= $code_hotel ? '— ' . ($group[$code_hotel]['nom'] ?? '') : '' ?></h1>
                <?php if ($date_debut && $date_fin): ?>
                    <small class="text-muted">Du <?= date('d/m/Y', strtotime($date_debut)) ?> au <?= date('d/m/Y', strtotime($date_fin)) ?></small>
                <?php endif; ?>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">

                <!-- Filtres + Exports -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="get" class="row g-3 align-items-end mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Hôtel</label>
                                <select name="code_hotel" class="form-select">
                                    <option value="">Tous les hôtels</option>
                                    <?php foreach ($hotels as $h): ?>
                                        <option value="<?= $h['code_hotel'] ?>" <?= $code_hotel === $h['code_hotel'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($h['nom_hotel']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date début</label>
                                <input type="date" name="date_debut" class="form-control" value="<?= htmlspecialchars($date_debut) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date fin</label>
                                <input type="date" name="date_fin" class="form-control" value="<?= htmlspecialchars($date_fin) ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                            </div>
                        </form>

                        <form method="post" class="d-flex justify-content-end gap-2 flex-wrap">
                            <button type="submit" name="export" value="excel" class="btn btn-success">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                            <button type="submit" name="export" value="csv" class="btn btn-info text-white">
                                <i class="fas fa-file-csv"></i> CSV
                            </button>
                            <button type="submit" name="export" value="pdf" class="btn btn-danger">
                                <i class="fas fa-file-pdf"></i> PDF
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Réservations groupées par hôtel -->
                <?php foreach ($group as $code => $h): ?>
                <div class="card mb-4 shadow-sm border-start border-primary border-5">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?= htmlspecialchars($h['nom']) ?> <small>(<?= $code ?>)</small></h5>
                        <span class="badge bg-light text-dark"><?= count($h['list']) ?> réservation<?= count($h['list']) > 1 ? 's' : '' ?></span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>N° Réserv.</th>
                                        <th>Date</th>
                                        <th>Client</th>
                                        <th>Chambre</th>
                                        <th>Période</th>
                                        <th>Durée</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($h['list'] as $i => $r): ?>
                                    <tr>
                                        <td><span class="badge rounded-pill <?= $i==0?'bg-warning':($i==1?'bg-secondary':'bg-dark') ?> text-white"><?= $i+1 ?></span></td>
                                        <td><strong><?= htmlspecialchars($r['numero_reservation']) ?></strong></td>
                                        <td><?= date('d/m/Y', strtotime($r['date_reservation'])) ?></td>
                                        <td><?= htmlspecialchars($r['nom_prenom_client'] ?? '—') ?></td>
                                        <td><?= htmlspecialchars($r['nom_chambre'] ?? $r['code_chambre']) ?></td>
                                        <td><?= date('d/m', strtotime($r['date_debut'])) ?> → <?= date('d/m/Y', strtotime($r['date_fin'])) ?></td>
                                        <td><span class="badge bg-info"><?= $r['duree_jours'] ?> j</span></td>
                                        <td class="text-success fw-bold"><?= number_format($r['montant_reservation']) ?> FCFA</td>
                                        <td>
                                            <span class="badge <?= $r['statut_reservation']==='libre'?'bg-success':($r['statut_reservation']==='occupé'?'bg-danger':'bg-warning') ?>">
                                                <?= ucfirst($r['statut_reservation'] ?? '') ?>
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

                <?php if (empty($group)): ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i> Aucune réservation trouvée avec les filtres actuels.
                </div>
                <?php endif; ?>

            </div>
        </section>
    </div>
</div>
</body>
</html>