<?php
// //session_start();
require "database/database.php";

// ==================== SUPPRESSION ====================
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM hotels WHERE code_hotel = ?");
        $stmt->execute([$_GET['delete']]);
        $_SESSION['message'] = "Hôtel supprimé avec succès.";
    } catch (Exception $e) {
        $_SESSION['message'] = "Erreur lors de la suppression : " . $e->getMessage();
    }
}

// ==================== AJOUT / MODIFICATION ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $code = trim($_POST['code_hotel']);
    $nom = trim($_POST['nom_hotel']);
    $type = trim($_POST['type_hotel']);
    $latitude = trim($_POST['latitude_hotel']);
    $longitude = trim($_POST['longitude_hotel']);
    $pays = trim($_POST['pays_hotel']);
    $ville = trim($_POST['ville_hotel']);
    $quartier = trim($_POST['quartier_hotel']);
    $adresse = trim($_POST['adresse_hotel']);
    $telephone = trim($_POST['telephone_hotel']);
    $email = trim($_POST['email_hotel']);
    $observation = $_POST['observation_hotel'];
    $etat = trim($_POST['etat_hotel']);

    try {
        if ($action === 'add') {
            $sql = "INSERT INTO hotels 
                    (code_hotel, nom_hotel, type_hotel, latitude_hotel, longitude_hotel, 
                     pays_hotel, ville_hotel, quartier_hotel, adresse_hotel, 
                     telephone_hotel, email_hotel, observation_hotel, etat_hotel)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$code, $nom, $type, $latitude, $longitude, $pays, $ville, $quartier, $adresse, $telephone, $email, $observation, $etat]);
            $_SESSION['message'] = "Hôtel ajouté avec succès.";
        }

        if ($action === 'update') {
            $sql = "UPDATE hotels SET
                    nom_hotel = ?, type_hotel = ?, latitude_hotel = ?, longitude_hotel = ?,
                    pays_hotel = ?, ville_hotel = ?, quartier_hotel = ?, adresse_hotel = ?,
                    telephone_hotel = ?, email_hotel = ?, observation_hotel = ?, etat_hotel = ?
                    WHERE code_hotel = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nom, $type, $latitude, $longitude, $pays, $ville, $quartier, $adresse, $telephone, $email, $observation, $etat, $code]);
            $_SESSION['message'] = "Hôtel modifié avec succès.";
        }
    } catch (Exception $e) {
        $_SESSION['message'] = "Erreur : " . $e->getMessage();
    }
}

// ==================== LISTE HÔTELS ====================
$stmt = $pdo->query("
    SELECT * FROM hotels 
    ORDER BY nom_hotel
");
$hotels = $stmt->fetchAll();

// ==================== MESSAGE FLASH ====================
$message = $_SESSION['message'] ?? '';
// $alert_type = str_starts_with($message, 'Erreur') ? 'danger' : 'success';
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
    <title>Soutra+ | Gestion des Hôtels</title>
    <!-- AdminLTE + FontAwesome + Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .user-panel img { width: 2.1rem; height: 2.1rem; object-fit: cover; }
        .nav-sidebar .nav-link { border-radius: 0.25rem; }
        .nav-treeview .nav-link { padding-left: 2.5rem; }
        .badge { font-size: 0.85em; }
        .table td { vertical-align: middle; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <?php include 'config/dashboard.php'; ?>

    <!-- ==================== CONTENT ==================== -->
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Gestion des Hôtels</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Accueil</a></li>
                            <li class="breadcrumb-item active">Hôtels</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <!-- Message Flash -->
                <?php if ($message): ?>
                    <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show">
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Liste des hôtels</h3>
                        <button class="btn btn-primary" id="addBtn">
                            <i class="fas fa-plus"></i> Ajouter un hôtel
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Code</th>
                                        <th>Nom</th>
                                        <th>Type</th>
                                        <th>Ville</th>
                                        <th>Téléphone</th>
                                        <th>Email</th>
                                        <th>État</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($hotels as $h): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($h['code_hotel']) ?></strong></td>
                                            <td><?= htmlspecialchars($h['nom_hotel']) ?></td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    <?= ucfirst($h['type_hotel']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($h['ville_hotel']) ?></td>
                                            <td><?= htmlspecialchars($h['telephone_hotel']) ?></td>
                                            <td><?= htmlspecialchars($h['email_hotel']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $h['etat_hotel'] === 'actif' ? 'success' : 'secondary' ?>">
                                                    <?= ucfirst($h['etat_hotel']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-warning btn-sm edit-btn"
                                                    data-bs-code="<?= htmlspecialchars($h['code_hotel']) ?>"
                                                    data-bs-nom="<?= htmlspecialchars($h['nom_hotel']) ?>"
                                                    data-bs-type="<?= htmlspecialchars($h['type_hotel']) ?>"
                                                    data-bs-lat="<?= htmlspecialchars($h['latitude_hotel']) ?>"
                                                    data-bs-lng="<?= htmlspecialchars($h['longitude_hotel']) ?>"
                                                    data-bs-pays="<?= htmlspecialchars($h['pays_hotel']) ?>"
                                                    data-bs-ville="<?= htmlspecialchars($h['ville_hotel']) ?>"
                                                    data-bs-quartier="<?= htmlspecialchars($h['quartier_hotel']) ?>"
                                                    data-bs-adresse="<?= htmlspecialchars($h['adresse_hotel']) ?>"
                                                    data-bs-tel="<?= htmlspecialchars($h['telephone_hotel']) ?>"
                                                    data-bs-email="<?= htmlspecialchars($h['email_hotel']) ?>"
                                                    data-bs-obs="<?= htmlspecialchars($h['observation_hotel']) ?>"
                                                    data-bs-etat="<?= htmlspecialchars($h['etat_hotel']) ?>">
                                                    <i class="fas fa-edit"></i> Modifier
                                                </button>
                                                <a href="?delete=<?= urlencode($h['code_hotel']) ?>"
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Supprimer cet hôtel ? Toutes les chambres associées seront affectées !');">
                                                    <i class="fas fa-trash"></i> Supprimer
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

    <!-- ==================== FOOTER ==================== -->
    <footer class="main-footer">
        <strong>© 2025 <a href="#">Soutra+</a>.</strong> Tous droits réservés.
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 1.0
        </div>
    </footer>
</div>

<!-- ==================== MODAL HÔTEL ==================== -->
<div class="modal fade" id="hotelModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Ajouter un hôtel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="hotelForm" method="post">
                    <input type="hidden" name="action" id="formAction" value="add">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Code hôtel <span class="text-danger">*</span></label>
                            <input type="text" name="code_hotel" id="code_hotel" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nom hôtel <span class="text-danger">*</span></label>
                            <input type="text" name="nom_hotel" id="nom_hotel" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Type hôtel</label>
                            <input type="text" name="type_hotel" id="type_hotel" class="form-control" placeholder="Ex: 3 étoiles, Résidence, etc.">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">État <span class="text-danger">*</span></label>
                            <select name="etat_hotel" id="etat_hotel" class="form-control" required>
                                <option value="actif">Actif</option>
                                <option value="inactif">Inactif</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Latitude</label>
                            <input type="text" name="latitude_hotel" id="latitude_hotel" class="form-control" placeholder="Ex: 6.1654">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Longitude</label>
                            <input type="text" name="longitude_hotel" id="longitude_hotel" class="form-control" placeholder="Ex: 1.2315">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Pays <span class="text-danger">*</span></label>
                            <input type="text" name="pays_hotel" id="pays_hotel" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ville <span class="text-danger">*</span></label>
                            <input type="text" name="ville_hotel" id="ville_hotel" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Quartier</label>
                            <input type="text" name="quartier_hotel" id="quartier_hotel" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Adresse complète</label>
                            <input type="text" name="adresse_hotel" id="adresse_hotel" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Téléphone <span class="text-danger">*</span></label>
                            <input type="text" name="telephone_hotel" id="telephone_hotel" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email_hotel" id="email_hotel" class="form-control" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Observation</label>
                            <textarea name="observation_hotel" id="observation_hotel" class="form-control" rows="3" placeholder="Services, particularités, etc."></textarea>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Sauvegarder
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script>
    const modal = new bootstrap.Modal('#hotelModal');

    // Ajouter
    document.getElementById('addBtn').addEventListener('click', () => {
        document.getElementById('hotelForm').reset();
        document.getElementById('modalTitle').innerText = 'Ajouter un hôtel';
        document.getElementById('formAction').value = 'add';
        document.getElementById('code_hotel').readOnly = false;
        modal.show();
    });

    // Modifier
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('modalTitle').innerText = 'Modifier un hôtel';
            document.getElementById('formAction').value = 'update';
            document.getElementById('code_hotel').value = this.dataset.bsCode;
            document.getElementById('code_hotel').readOnly = true;
            document.getElementById('nom_hotel').value = this.dataset.bsNom;
            document.getElementById('type_hotel').value = this.dataset.bsType;
            document.getElementById('latitude_hotel').value = this.dataset.bsLat;
            document.getElementById('longitude_hotel').value = this.dataset.bsLng;
            document.getElementById('pays_hotel').value = this.dataset.bsPays;
            document.getElementById('ville_hotel').value = this.dataset.bsVille;
            document.getElementById('quartier_hotel').value = this.dataset.bsQuartier;
            document.getElementById('adresse_hotel').value = this.dataset.bsAdresse;
            document.getElementById('telephone_hotel').value = this.dataset.bsTel;
            document.getElementById('email_hotel').value = this.dataset.bsEmail;
            document.getElementById('observation_hotel').value = this.dataset.bsObs;
            document.getElementById('etat_hotel').value = this.dataset.bsEtat;
            modal.show();
        });
    });
</script>
</body>
</html>