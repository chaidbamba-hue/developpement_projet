<?php
require "database/database.php";

// Inclusion FPDF seulement si on exporte en PDF
if (isset($_POST['export']) && $_POST['export'] === 'pdf') {
    require_once 'librairiesfpdf/fpdf/fpdf.php'; 
}

// ==================== EXPORT EXCEL / CSV / PDF ====================
if (isset($_POST['export']) && in_array($_POST['export'], ['excel', 'csv', 'pdf'])) {

    $stmt = $pdo->query("
        SELECT h.*, 
               COUNT(c.code_chambre) as nb_chambres
        FROM hotels h
        LEFT JOIN chambres c ON h.code_hotel = c.code_hotel
        GROUP BY h.code_hotel
        ORDER BY h.nom_hotel ASC
    ");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (ob_get_level()) ob_end_clean();

    // ====================== CSV ======================
    if ($_POST['export'] === 'csv') {
        $filename = 'Hotels_' . date('d-m-Y_H-i') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Nom Hôtel','Code','Type','Ville','Pays','Quartier','Adresse','Téléphone','Email','Nb Chambres','État'], ';');

        foreach ($data as $row) {
            fputcsv($output, [
                $row['nom_hotel'],
                $row['code_hotel'],
                $row['type_hotel'],
                $row['ville_hotel'],
                $row['pays_hotel'],
                $row['quartier_hotel'],
                $row['adresse_hotel'],
                $row['telephone_hotel'],
                $row['email_hotel'] ?? '',
                $row['nb_chambres'],
                ucfirst($row['etat_hotel'] ?? 'inconnu')
            ], ';');
        }
        exit;
    }

    // ====================== EXCEL ======================
    if ($_POST['export'] === 'excel') {
        $filename = 'Hotels_' . date('d-m-Y_H-i') . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo "\xEF\xBB\xBF";
        ?>
        <table border="1">
            <tr style="background:#007bff;color:white;font-weight:bold;">
                <th>Nom Hôtel</th><th>Code</th><th>Type</th><th>Ville</th><th>Pays</th><th>Quartier</th>
                <th>Adresse</th><th>Téléphone</th><th>Email</th><th>Chambres</th><th>État</th>
            </tr>
            <?php foreach ($data as $row): ?>
            <tr align="center">
                <td><?= htmlspecialchars($row['nom_hotel']) ?></td>
                <td><?= htmlspecialchars($row['code_hotel']) ?></td>
                <td><?= htmlspecialchars($row['type_hotel']) ?></td>
                <td><?= htmlspecialchars($row['ville_hotel']) ?></td>
                <td><?= htmlspecialchars($row['pays_hotel']) ?></td>
                <td><?= htmlspecialchars($row['quartier_hotel']) ?></td>
                <td><?= htmlspecialchars($row['adresse_hotel']) ?></td>
                <td><?= htmlspecialchars($row['telephone_hotel']) ?></td>
                <td><?= htmlspecialchars($row['email_hotel'] ?? '') ?></td>
                <td><?= $row['nb_chambres'] ?></td>
                <td><?= ucfirst($row['etat_hotel'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php
        exit;
    }

    // ====================== PDF ======================
    if ($_POST['export'] === 'pdf') {
        $pdf = new FPDF('L', 'mm', 'A3');
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->SetFillColor(0,123,255);
        $pdf->SetTextColor(255,255,255);
        $pdf->Cell(0,15, ('Liste des Hotels'), 0,1,'C',true);
        $pdf->Ln(5);
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(0,8, ('Genere le ') . date('d/m/Y à H:i'),0,1,'R');
        $pdf->Ln(8);

        // En-tête
        $pdf->SetFont('Arial','B',10);
        $pdf->SetFillColor(230,230,230);
        $pdf->Cell(10,10,'N°',1,0,'C',true);
        $pdf->Cell(55,10,('Nom Hotel'),1,0,'C',true);
        $pdf->Cell(25,10,'Code',1,0,'C',true);
        $pdf->Cell(35,10,'Type',1,0,'C',true);
        $pdf->Cell(40,10,'Ville / Pays',1,0,'C',true);
        $pdf->Cell(55,10,'Adresse',1,0,'C',true);
        $pdf->Cell(30,10,'Telephone',1,0,'C',true);
        $pdf->Cell(20,10,'Chambres',1,0,'C',true);
        $pdf->Cell(25,10,'Etat',1,1,'C',true);

        $pdf->SetFont('Arial','',9);
        $i = 1;
        foreach ($data as $row) {
            $villePays = $row['ville_hotel'] . ' / ' . $row['pays_hotel'];
            $pdf->Cell(10,9,$i++,1,0,'C');
            $pdf->Cell(55,9,($row['nom_hotel']),1,0,'L');
            $pdf->Cell(25,9,$row['code_hotel'],1,0,'C');
            $pdf->Cell(35,9,($row['type_hotel']),1,0,'L');
            $pdf->Cell(40,9,($villePays),1,0,'L');
            $pdf->Cell(55,9,($row['adresse_hotel']),1,0,'L');
            $pdf->Cell(30,9,$row['telephone_hotel'],1,0,'C');
            $pdf->Cell(20,9,$row['nb_chambres'],1,0,'C');
            $pdf->Cell(25,9,ucfirst($row['etat_hotel'] ?? ''),1,1,'C');
        }

        $pdf->Output('D', 'Hotels_' . date('d-m-Y_H-i') . '.pdf');
        exit;
    }
}

// ==================== AFFICHAGE NORMAL ====================
$stmt = $pdo->query("
    SELECT h.*, COUNT(c.code_chambre) as nb_chambres
    FROM hotels h
    LEFT JOIN chambres c ON h.code_hotel = c.code_hotel
    GROUP BY h.code_hotel
    ORDER BY h.nom_hotel ASC
");
$hotels = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hotelio | Hôtels</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6/css/all.min.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <?php include './config/dashboard.php'; ?>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <h1 class="mb-0">Hôtels (Triés par ordre alphabétique)</h1>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">

                <!-- Boutons Export -->
                <form method="post" class="d-flex justify-content-end mb-4 gap-2 flex-wrap">
                    <button type="submit" name="export" value="excel" class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>&nbsp &nbsp
                    <button type="submit" name="export" value="csv" class="btn btn-info text-white">
                        <i class="fas fa-file-csv"></i> CSV
                    </button>&nbsp &nbsp
                    <button type="submit" name="export" value="pdf" class="btn btn-danger">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>&nbsp &nbsp
                </form>

                <!-- Liste des hôtels (même style que les chambres) -->
                <?php foreach ($hotels as $h): ?>
                <div class="card mb-4 shadow-sm border-start border-primary border-5">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <?= htmlspecialchars($h['nom_hotel']) ?> 
                            <small>(<?= htmlspecialchars($h['code_hotel']) ?>)</small>
                        </h5>
                        <span class="badge bg-light text-dark">
                            <?= $h['nb_chambres'] ?> chambre<?= $h['nb_chambres'] > 1 ? 's' : '' ?>
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Code</th>
                                        <th>Nom</th>
                                        <th>Type</th>
                                        <th>Ville / Pays</th>
                                        <th>Adresse Complète</th>
                                        <th>Téléphone</th>
                                        <th>Email</th>
                                        <th>Chambres</th>
                                        <th>État</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="text-center">
                                        <td><strong><?= htmlspecialchars($h['code_hotel']) ?></strong></td>
                                        <td><?= htmlspecialchars($h['nom_hotel']) ?></td>
                                        <td><span class="badge bg-info text-dark"><?= htmlspecialchars($h['type_hotel']) ?></span></td>
                                        <td>
                                            <?= htmlspecialchars($h['ville_hotel']) ?><br>
                                            <small class="text-muted"><?= htmlspecialchars($h['pays_hotel']) ?></small>
                                        </td>
                                        <td class="text-start">
                                            <?= nl2br(htmlspecialchars($h['adresse_hotel'])) ?><br>
                                            <small class="text-muted">Quartier : <?= htmlspecialchars($h['quartier_hotel']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($h['telephone_hotel']) ?></td>
                                        <td><?= htmlspecialchars($h['email_hotel'] ?? '-') ?></td>
                                        <td><strong class="text-primary"><?= $h['nb_chambres'] ?></strong></td>
                                        <td>
                                            <span class="badge bg-<?= ($h['etat_hotel'] ?? '') == 'actif' ? 'success' : 'danger' ?>">
                                                <?= ucfirst($h['etat_hotel'] ?? 'inconnu') ?>
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                </div>
                <?php endforeach; ?>

                <?php if (empty($hotels)): ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle fa-2x mb-3"></i><br>
                        Aucun hôtel enregistré pour le moment.
                    </div>
                <?php endif; ?>

            </div>
        </section>
    </div>
</div>
</body>
</html>