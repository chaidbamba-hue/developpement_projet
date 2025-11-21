<?php
//session_start();
require "database/database.php";

// ==================== SUPPRESSION ====================
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM factures WHERE code_facture = ?");
        $stmt->execute([$_GET['delete']]);
        $_SESSION['message'] = "Facture supprimée avec succès.";
    } catch (Exception $e) {
        $_SESSION['message'] = "Erreur lors de la suppression : " . $e->getMessage();
    }
    header("Location: http://localhost/soutra/facture/enregistrement");
    exit;
}

// ==================== AJOUT / MODIFICATION ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['print'])) {
    $action = $_POST['action'] ?? '';
    $code_facture = trim($_POST['code_facture']);
    $titre_facture = trim($_POST['titre_facture']);
    $date_facture = $_POST['date_facture'];
    $montant_ht = $_POST['montant_ht'];
    $montant_ttc = $_POST['montant_ttc'];
    $taux_taxes = $_POST['taux_taxes'];
    $type_taxes = trim($_POST['type_taxes']);
    $etat_facture = $_POST['etat_facture'];
    $code_client = trim($_POST['code_client']);

    try {
        if ($action === 'add') {
            $sql = "INSERT INTO factures
                    (code_facture, titre_facture, date_facture, montant_ht, montant_ttc, taux_taxes, type_taxes, etat_facture, code_client)
                    VALUES (?,?,?,?,?,?,?,?,?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$code_facture, $titre_facture, $date_facture, $montant_ht, $montant_ttc, $taux_taxes, $type_taxes, $etat_facture, $code_client]);
            $_SESSION['message'] = "Facture créée avec succès.";
        }
        if ($action === 'update') {
            $sql = "UPDATE factures SET
                    code_facture = ?, titre_facture = ?, date_facture = ?, montant_ht = ?, montant_ttc = ?,
                    taux_taxes = ?, type_taxes = ?, etat_facture = ?, code_client = ?
                    WHERE code_facture = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$code_facture, $titre_facture, $date_facture, $montant_ht, $montant_ttc, $taux_taxes, $type_taxes, $etat_facture, $code_client, $_POST['old_code']]);
            $_SESSION['message'] = "Facture modifiée avec succès.";
        }
    } catch (Exception $e) {
        $_SESSION['message'] = "Erreur : " . $e->getMessage();
    }
    header("Location: http://localhost/soutra/facture/enregistrement");
    exit;
}

// ==================== GÉNÉRATION PDF (REÇU 80mm) ====================
if (isset($_GET['print'])) {
    $code = $_GET['print'];
    $stmt = $pdo->prepare("SELECT * FROM factures WHERE code_facture = ?");
    $stmt->execute([$code]);
    $f = $stmt->fetch();
    if (!$f) die("Facture non trouvée");

    require('libraries/fpdf/fpdf.php');

    // Classe PDF personnalisée
    class PDF extends FPDF {
        function Header() {
            $this->SetFont('Courier','B',12);
            $this->Cell(0,6,'SOUTRA +',0,1,'C');
            $this->SetFont('Courier','',8);
            $this->Cell(0,4,'Hotel de luxe - Abidjan',0,1,'C');
            $this->Cell(0,4,'Tel: +225 07 00 00 00 00',0,1,'C');
            $this->Cell(0,4,'contact@soutraplus.com',0,1,'C');
            $this->Ln(2);

            // Ligne horizontale
            $this->Line(5, $this->GetY(), 75, $this->GetY());
            $this->Ln(3);
        }

        function Footer() {
            $this->SetY(-15);
            $this->SetFont('Courier','I',7);
            $this->Cell(0,4,'Merci de votre visite !',0,0,'C');
        }
    }

    // Format 80mm
    $pdf = new PDF('P', 'mm', [80, 200]); // Largeur 80mm, hauteur max 200mm
    $pdf->AddPage();
    $pdf->SetAutoPageBreak(true, 10);
    $pdf->SetMargins(5, 5, 5);

    // === EN-TÊTE REÇU ===
    $pdf->SetFont('Courier','B',10);
    $pdf->Cell(0,5,'RECU N: '.strtoupper($f['code_facture']),0,1,'C');
    $pdf->SetFont('Courier','',8);
    $pdf->Cell(0,4,'Date: '.date('d/m/Y H:i', strtotime($f['date_facture'])),0,1,'C');
    $pdf->Cell(0,4,'Client: '.strtoupper($f['code_client']),0,1,'C');
    $pdf->Ln(2);
    $pdf->Line(5, $pdf->GetY(), 75, $pdf->GetY()); // CORRIGÉ : $pdf->GetY()
    $pdf->Ln(3);

    // === DÉTAIL ===
    $pdf->SetFont('Courier','',9);
    $pdf->Cell(50,5,'Prestation',0);
    $pdf->Cell(20,5,number_format($f['montant_ht'],0,',',' ').' FCFA',0,1,'R');

    $taxe = $f['montant_ttc'] - $f['montant_ht'];
    $pdf->SetFont('Courier','',8);
    $pdf->Cell(50,4,'TVA ('.$f['taux_taxes'].'% '.$f['type_taxes'].')',0);
    $pdf->Cell(20,4,number_format($taxe,0,',',' ').' FCFA',0,1,'R');

    $pdf->Ln(2);
    $pdf->Line(5, $pdf->GetY(), 75, $pdf->GetY()); // CORRIGÉ
    $pdf->Ln(3);

    // === TOTAL ===
    $pdf->SetFont('Courier','B',11);
    $pdf->Cell(50,6,'TOTAL',0);
    $pdf->Cell(20,6,number_format($f['montant_ttc'],0,',',' ').' FCFA',0,1,'R');

    $pdf->Ln(3);
    $pdf->SetFont('Courier','',8);
    $pdf->Cell(0,4,'Etat: '.strtoupper($f['etat_facture']),0,1,'C');

    $pdf->Ln(5);
    $pdf->SetFont('Courier','I',7);
    $pdf->MultiCell(0,3,"Conditions: 30 jours nets\nPenalites: 1.5% / mois",0,'C');

    $pdf->Output('I', 'Recu_'.$f['code_facture'].'.pdf');
    exit;
}

// ==================== LISTE TOUTES LES FACTURES ====================
$stmt = $pdo->query("SELECT * FROM factures ORDER BY date_facture DESC");
$factures = $stmt->fetchAll();

// ==================== MESSAGE FLASH ====================
$message = $_SESSION['message'] ?? '';
$alert_type = str_starts_with($message, 'Erreur') ? 'danger' : 'success';
if ($message) unset($_SESSION['message']);

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
    <title>Soutra+ | Gestion des Factures</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .user-panel img { width: 2.1rem; height: 2.1rem; object-fit: cover; }
        .nav-sidebar .nav-link { border-radius: 0.25rem; }
        .nav-treeview .nav-link { padding-left: 2.5rem; }
        .badge { font-size: 0.85em; }
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
                        <h1>Gestion des Factures</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Accueil</a></li>
                            <li class="breadcrumb-item active">Factures</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <?php if ($message): ?>
                    <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show">
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title mb-0">
                                Liste des factures 
                                <span id="filter-info" class="badge bg-info ms-2" style="display:none;"></span>
                            </h3>
                        </div>
                        <div>
                            <button type="button" class="btn btn-outline-danger me-2" id="showUnpaid">
                                Factures impayées 
                                <span class="badge bg-danger" id="unpaidCount">0</span>
                            </button>
                            <button type="button" class="btn btn-secondary me-2" id="showAll">
                                Toutes les factures
                            </button>
                            <button class="btn btn-primary" id="addBtn">
                                Ajouter une facture
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Code</th>
                                        <th>Titre</th>
                                        <th>Date</th>
                                        <th>Montant HT</th>
                                        <th>Montant TTC</th>
                                        <th>État</th>
                                        <th>Client</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($factures as $f): 
                                        $status = strtolower(trim($f['etat_facture']));
                                    ?>
                                    <tr data-status="<?= $status ?>">
                                        <td><strong><?= htmlspecialchars($f['code_facture']) ?></strong></td>
                                        <td><?= htmlspecialchars($f['titre_facture']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($f['date_facture'])) ?></td>
                                        <td><?= number_format($f['montant_ht'], 0, ',', ' ') ?> FCFA</td>
                                        <td><strong><?= number_format($f['montant_ttc'], 0, ',', ' ') ?> FCFA</strong></td>
                                        <td>
                                            <span class="badge bg-<?= $f['etat_facture']=='Payée'?'success':($f['etat_facture']=='Annulée'?'danger':'warning') ?>">
                                                <?= htmlspecialchars($f['etat_facture']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($f['code_client']) ?></td>
                                        <td>
                                            <button class="btn btn-warning btn-sm edit-btn"
                                                data-bs-code="<?= htmlspecialchars($f['code_facture']) ?>"
                                                data-bs-titre="<?= htmlspecialchars($f['titre_facture']) ?>"
                                                data-bs-date="<?= $f['date_facture'] ?>"
                                                data-bs-ht="<?= $f['montant_ht'] ?>"
                                                data-bs-ttc="<?= $f['montant_ttc'] ?>"
                                                data-bs-taux="<?= $f['taux_taxes'] ?>"
                                                data-bs-type="<?= htmlspecialchars($f['type_taxes']) ?>"
                                                data-bs-etat="<?= $f['etat_facture'] ?>"
                                                data-bs-client="<?= htmlspecialchars($f['code_client']) ?>">
                                                Modifier
                                            </button>
                                            <a href="?print=<?= urlencode($f['code_facture']) ?>" target="_blank"
                                               class="btn btn-info btn-sm text-white">Imprimer PDF</a>
                                            <a href="?delete=<?= urlencode($f['code_facture']) ?>"
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Supprimer cette facture ?');">
                                                Supprimer
                                            </a>
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

<!-- ==================== MODAL FACTURE ==================== -->
<div class="modal fade" id="factureModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTitle">Ajouter une facture</h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="factureForm" method="post">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="old_code" id="old_code">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label>Code Facture <span class="text-danger">*</span></label>
                            <input type="text" name="code_facture" id="code_facture" class="form-control" required>
                        </div>
                        <div class="col-md-8">
                            <label>Titre facture</label>
                            <input type="text" name="titre_facture" id="titre_facture" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label>Date facture</label>
                            <input type="date" name="date_facture" id="date_facture" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label>Montant HT</label>
                            <input type="number" step="0.01" name="montant_ht" id="montant_ht" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label>Montant TTC</label>
                            <input type="number" step="0.01" name="montant_ttc" id="montant_ttc" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label>Taux taxes (%)</label>
                            <input type="number" step="0.01" name="taux_taxes" id="taux_taxes" class="form-control" value="18" required>
                        </div>
                        <div class="col-md-3">
                            <label>Type taxes</label>
                            <input type="text" name="type_taxes" id="type_taxes" class="form-control" value="TVA" required>
                        </div>
                        <div class="col-md-3">
                            <label>État</label>
                            <select name="etat_facture" id="etat_facture" class="form-control">
                                <option value="en attente">en attente</option>
                                <option value="Payée">Payée</option>
                                <option value="non payer">non payer</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Code Client</label>
                            <input type="text" name="code_client" id="code_client" class="form-control" required>
                        </div>
                    </div>
                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-success px-4">Sauvegarder</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
    const modal = new bootstrap.Modal('#factureModal');

    // Compter les impayées au chargement
    function updateUnpaidCount() {
        const count = document.querySelectorAll('tr[data-status="non payer"], tr[data-status="en attente"]').length;
        document.getElementById('unpaidCount').textContent = count;
    }

    // Initialisation
    updateUnpaidCount();

    // Bouton Ajouter
    document.getElementById('addBtn').addEventListener('click', () => {
        document.getElementById('factureForm').reset();
        document.getElementById('modalTitle').innerText = 'Ajouter une facture';
        document.getElementById('formAction').value = 'add';
        document.getElementById('code_facture').readOnly = false;
        document.getElementById('date_facture').value = '<?= date('Y-m-d') ?>';
        modal.show();
    });

    // Bouton Modifier
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('modalTitle').innerText = 'Modifier une facture';
            document.getElementById('formAction').value = 'update';
            document.getElementById('old_code').value = this.dataset.bsCode;
            document.getElementById('code_facture').value = this.dataset.bsCode;
            document.getElementById('code_facture').readOnly = true;
            document.getElementById('titre_facture').value = this.dataset.bsTitre;
            document.getElementById('date_facture').value = this.dataset.bsDate;
            document.getElementById('montant_ht').value = this.dataset.bsHt;
            document.getElementById('montant_ttc').value = this.dataset.bsTtc;
            document.getElementById('taux_taxes').value = this.dataset.bsTaux;
            document.getElementById('type_taxes').value = this.dataset.bsType;
            document.getElementById('etat_facture').value = this.dataset.bsEtat;
            document.getElementById('code_client').value = this.dataset.bsClient;
            modal.show();
        });
    });

    // Bouton : Afficher seulement les impayées
    document.getElementById('showUnpaid').addEventListener('click', function() {
        document.querySelectorAll('tbody tr').forEach(row => {
            const status = row.dataset.status;
            row.style.display = (status === 'non payer' || status === 'en attente') ? '' : 'none';
        });
        document.getElementById('filter-info').textContent = 'Filtre : Factures impayées uniquement';
        document.getElementById('filter-info').style.display = 'inline';
    });

    // Bouton : Afficher toutes les factures
    document.getElementById('showAll').addEventListener('click', function() {
        document.querySelectorAll('tbody tr').forEach(row => {
            row.style.display = '';
        });
        document.getElementById('filter-info').style.display = 'none';
    });
</script>
</body>
</html>