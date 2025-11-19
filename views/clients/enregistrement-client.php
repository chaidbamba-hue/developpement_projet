<?php
//session_start();
require "database/database.php";

// ==================== SUPPRESSION ====================
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM clients WHERE code_client = ?");
        $stmt->execute([$_GET['delete']]);
        $_SESSION['message'] = "Client supprimé avec succès.";
    } catch (Exception $e) {
        $_SESSION['message'] = "Erreur lors de la suppression : " . $e->getMessage();
    }
   
}

// ==================== AJOUT / MODIFICATION ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $code = trim($_POST['code_client']);
    $nom = trim($_POST['nom_prenom_client']);
    $date_naiss = $_POST['date_naissance_client'];
    $lieu = trim($_POST['lieu_naissance_client']);
    $sexe = $_POST['sexe_client'];
    $nationalite = trim($_POST['nationalite_client']);
    $situation = trim($_POST['situation_matrimoniale_client']);
    $enfants = (int)$_POST['nombre_enfant_client'];
    $tel = trim($_POST['telephone_client']);
    $email = trim($_POST['email_client']);
    $pays = trim($_POST['pays_client']);
    $ville = trim($_POST['ville_client']);
    $adresse = trim($_POST['adresse_client']);
    $quartier = trim($_POST['quartier_client']);
    $type = $_POST['type_client'];
    $etat = $_POST['etat_client'];

    try {
        if ($action === 'add') {
            $sql = "INSERT INTO clients VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $code, $nom, $date_naiss, $lieu, $sexe, $nationalite,
                $situation, $enfants, $tel, $email, $pays, $ville,
                $adresse, $quartier, $type, $etat
            ]);
            $_SESSION['message'] = "Client ajouté avec succès.";
        }

        if ($action === 'update') {
            $sql = "UPDATE clients SET 
                    nom_prenom_client=?, date_naissance_client=?, lieu_naissance_client=?,
                    sexe_client=?, nationalite_client=?, situation_matrimoniale_client=?,
                    nombre_enfant_client=?, telephone_client=?, email_client=?,
                    pays_client=?, ville_client=?, adresse_client=?, quartier_client=?,
                    type_client=?, etat_client=? 
                    WHERE code_client=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $nom, $date_naiss, $lieu, $sexe, $nationalite,
                $situation, $enfants, $tel, $email, $pays, $ville,
                $adresse, $quartier, $type, $etat, $code
            ]);
            $_SESSION['message'] = "Client modifié avec succès.";
        }
    } catch (Exception $e) {
        $_SESSION['message'] = "Erreur : " . $e->getMessage();
    }
   
}

// ==================== RECHERCHE + PAGINATION ====================
$recherche = $_GET['recherche'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$where = $recherche ? "WHERE nom_prenom_client LIKE :search OR code_client LIKE :search OR telephone_client LIKE :search" : "";
$search = "%$recherche%";

$countSql = "SELECT COUNT(*) FROM clients $where";
$countStmt = $pdo->prepare($countSql);
if ($recherche) $countStmt->bindParam(':search', $search);
$countStmt->execute();
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $perPage);

// ==================== LISTE CLIENTS + RÉSERVATIONS ====================
$sql = "SELECT c.*, COUNT(r.numero_reservation) as nb_reservations
        FROM clients c
        LEFT JOIN reservations r ON c.code_client = r.code_client
        $where
        GROUP BY c.code_client
        LIMIT ? OFFSET ?";
$stmt = $pdo->prepare($sql);
if ($recherche) $stmt->bindParam(':search', $search);
$stmt->bindValue(1, $perPage, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$clients = $stmt->fetchAll();

// ==================== TOP CLIENT ====================
$topSql = "SELECT c.nom_prenom_client, COUNT(r.numero_reservation) as total
           FROM clients c
           LEFT JOIN reservations r ON c.code_client = r.code_client
           GROUP BY c.code_client
           ORDER BY total DESC
           LIMIT 1";
$topStmt = $pdo->query($topSql);
$topClient = $topStmt->fetch() ?: ['nom_prenom_client' => 'Aucun', 'total' => 0];

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
    <title>Soutra+ | Gestion des Clients</title>
    <!-- AdminLTE + FontAwesome + Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .user-panel img { width: 2.1rem; height: 2.1rem; object-fit: cover; }
        .nav-sidebar .nav-link { border-radius: 0.25rem; }
        .nav-treeview .nav-link { padding-left: 2.5rem; }
        .badge { font-size: 0.85em; }
        .top-client-card { 
            border-left: 5px solid #ffc107; 
            background: #fff8e1; 
            border-radius: 0.375rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
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
                        <h1>Gestion des Clients</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Accueil</a></li>
                            <li class="breadcrumb-item active">Clients</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">

                <!-- Messages flash -->
               

                <!-- Top Client -->
                <div class="card shadow-sm mb-3 top-client-card">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">
                                <i class="fas fa-trophy text-warning"></i> Meilleur Client
                            </h5>
                            <p class="mb-0">
                                <strong><?= htmlspecialchars($topClient['nom_prenom_client']) ?></strong>
                            </p>
                            <small class="text-muted">
                                <?= $topClient['total'] ?> réservation(s)
                            </small>
                        </div>
                        <div class="text-warning">
                            <i class="fas fa-trophy fa-2x"></i>
                        </div>
                    </div>
                </div>

                <!-- Bouton Ajouter + Recherche -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <button class="btn btn-primary" id="addBtn">
                        <i class="fas fa-plus"></i> Ajouter un client
                    </button>
                    <form method="get" class="d-flex">
                        <div class="input-group w-auto">
                            <input type="text" name="recherche" class="form-control" placeholder="Nom, code, tel..." 
                                   value="<?= htmlspecialchars($recherche) ?>">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Liste des clients -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Liste des clients</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Code</th>
                                        <th>Nom & Prénom</th>
                                        <th>Téléphone</th>
                                        <th>Email</th>
                                        <th>Nationalité</th>
                                        <th>Réservations</th>
                                        <th>État</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($clients as $c): ?>
                                        <tr>
                                            <td><strong><code><?= htmlspecialchars($c['code_client']) ?></code></strong></td>
                                            <td><?= htmlspecialchars($c['nom_prenom_client']) ?></td>
                                            <td><?= htmlspecialchars($c['telephone_client']) ?></td>
                                            <td><?= htmlspecialchars($c['email_client']) ?></td>
                                            <td><?= htmlspecialchars($c['nationalite_client']) ?></td>
                                            <td>
                                                <span class="badge <?= $c['nb_reservations'] > 0 ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= $c['nb_reservations'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?= $c['etat_client'] === 'Actif' ? 'bg-success' : 'bg-danger' ?>">
                                                    <?= htmlspecialchars($c['etat_client']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-warning btn-sm edit-btn"
                                                    data-bs-code="<?= htmlspecialchars($c['code_client']) ?>"
                                                    data-bs-nom="<?= htmlspecialchars($c['nom_prenom_client']) ?>"
                                                    data-bs-date="<?= $c['date_naissance_client'] ?>"
                                                    data-bs-lieu="<?= htmlspecialchars($c['lieu_naissance_client']) ?>"
                                                    data-bs-sexe="<?= htmlspecialchars($c['sexe_client']) ?>"
                                                    data-bs-nationalite="<?= htmlspecialchars($c['nationalite_client']) ?>"
                                                    data-bs-situation="<?= htmlspecialchars($c['situation_matrimoniale_client']) ?>"
                                                    data-bs-enfants="<?= htmlspecialchars($c['nombre_enfant_client']) ?>"
                                                    data-bs-tel="<?= htmlspecialchars($c['telephone_client']) ?>"
                                                    data-bs-email="<?= htmlspecialchars($c['email_client']) ?>"
                                                    data-bs-pays="<?= htmlspecialchars($c['pays_client']) ?>"
                                                    data-bs-ville="<?= htmlspecialchars($c['ville_client']) ?>"
                                                    data-bs-adresse="<?= htmlspecialchars($c['adresse_client']) ?>"
                                                    data-bs-quartier="<?= htmlspecialchars($c['quartier_client']) ?>"
                                                    data-bs-type="<?= htmlspecialchars($c['type_client']) ?>"
                                                    data-bs-etat="<?= htmlspecialchars($c['etat_client']) ?>">
                                                    <i class="fas fa-edit"></i> Modifier
                                                </button>
                                                <a href="?delete=<?= urlencode($c['code_client']) ?>"
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Supprimer ce client ?');">
                                                    <i class="fas fa-trash"></i> Supprimer
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <nav aria-label="Pagination">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $page - 1 ?>&recherche=<?= urlencode($recherche) ?>">Précédent</a>
                                    </li>
                                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>&recherche=<?= urlencode($recherche) ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $page + 1 ?>&recherche=<?= urlencode($recherche) ?>">Suivant</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
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

<!-- ==================== MODAL CLIENT ==================== -->
<div class="modal fade" id="clientModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Ajouter un client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="clientForm" method="post">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <div class="row g-3">
                        <!-- Colonne 1 -->
                        <div class="col-md-6">
                            <label class="form-label">Code Client <span class="text-danger">*</span></label>
                            <input type="text" name="code_client" id="code_client" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nom & Prénom <span class="text-danger">*</span></label>
                            <input type="text" name="nom_prenom_client" id="nom_prenom_client" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date Naissance <span class="text-danger">*</span></label>
                            <input type="date" name="date_naissance_client" id="date_naissance_client" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Lieu Naissance <span class="text-danger">*</span></label>
                            <input type="text" name="lieu_naissance_client" id="lieu_naissance_client" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sexe <span class="text-danger">*</span></label>
                            <select name="sexe_client" id="sexe_client" class="form-select" required>
                                <option value="Masculin">Masculin</option>
                                <option value="Féminin">Féminin</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nationalité <span class="text-danger">*</span></label>
                            <input type="text" name="nationalite_client" id="nationalite_client" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Situation Matrimoniale</label>
                            <select name="situation_matrimoniale_client" id="situation_matrimoniale_client" class="form-select">
                                <option value="Célibataire">Célibataire</option>
                                <option value="Marié(e)">Marié(e)</option>
                                <option value="Divorcé(e)">Divorcé(e)</option>
                                <option value="Veuf(ve)">Veuf(ve)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nombre d'enfants</label>
                            <input type="number" name="nombre_enfant_client" id="nombre_enfant_client" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Téléphone <span class="text-danger">*</span></label>
                            <input type="text" name="telephone_client" id="telephone_client" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email_client" id="email_client" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Pays <span class="text-danger">*</span></label>
                            <input type="text" name="pays_client" id="pays_client" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ville <span class="text-danger">*</span></label>
                            <input type="text" name="ville_client" id="ville_client" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Adresse <span class="text-danger">*</span></label>
                            <input type="text" name="adresse_client" id="adresse_client" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Quartier <span class="text-danger">*</span></label>
                            <input type="text" name="quartier_client" id="quartier_client" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Type Client</label>
                            <select name="type_client" id="type_client" class="form-select">
                                <option value="Particulier">Particulier</option>
                                <option value="Entreprise">Entreprise</option>
                                <option value="VIP">VIP</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">État</label>
                            <select name="etat_client" id="etat_client" class="form-select">
                                <option value="Actif">Actif</option>
                                <option value="Inactif">Inactif</option>
                            </select>
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
    const modal = new bootstrap.Modal('#clientModal');

    // Ajouter
    document.getElementById('addBtn').addEventListener('click', () => {
        document.getElementById('clientForm').reset();
        document.getElementById('modalTitle').innerText = 'Ajouter un client';
        document.getElementById('formAction').value = 'add';
        document.getElementById('code_client').readOnly = false;
        modal.show();
    });

    // Modifier
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('modalTitle').innerText = 'Modifier un client';
            document.getElementById('formAction').value = 'update';
            document.getElementById('code_client').value = this.dataset.bsCode;
            document.getElementById('code_client').readOnly = true;
            document.getElementById('nom_prenom_client').value = this.dataset.bsNom;
            document.getElementById('date_naissance_client').value = this.dataset.bsDate;
            document.getElementById('lieu_naissance_client').value = this.dataset.bsLieu;
            document.getElementById('sexe_client').value = this.dataset.bsSexe;
            document.getElementById('nationalite_client').value = this.dataset.bsNationalite;
            document.getElementById('situation_matrimoniale_client').value = this.dataset.bsSituation;
            document.getElementById('nombre_enfant_client').value = this.dataset.bsEnfants;
            document.getElementById('telephone_client').value = this.dataset.bsTel;
            document.getElementById('email_client').value = this.dataset.bsEmail;
            document.getElementById('pays_client').value = this.dataset.bsPays;
            document.getElementById('ville_client').value = this.dataset.bsVille;
            document.getElementById('adresse_client').value = this.dataset.bsAdresse;
            document.getElementById('quartier_client').value = this.dataset.bsQuartier;
            document.getElementById('type_client').value = this.dataset.bsType;
            document.getElementById('etat_client').value = this.dataset.bsEtat;
            modal.show();
        });
    });
</script>
</body>
</html>