<?php
// session_start();
require "database/database.php";

$message = '';
$alert_type = 'danger';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['final_submit'])) {
    $id        = trim($_POST['utilisateur_id']);
    $nom       = trim($_POST['nom_prenom']);
    $login     = trim($_POST['login']);
    $mdp       = $_POST['mdp'];
    $telephone = trim($_POST['telephone'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $role      = 'Superviseur';
    $etat      = 'actif';

    $photo = null;
    $type_photo = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $allowed = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['photo']['type'], $allowed) && $_FILES['photo']['size'] <= 2 * 1024 * 1024) {
            $photo = file_get_contents($_FILES['photo']['tmp_name']);
            $type_photo = $_FILES['photo']['type'];
        }
    }

    try {
        $check = $pdo->prepare("SELECT 1 FROM utilisateurs WHERE login = ?");
        $check->execute([$login]);
        if ($check->fetch()) {
            $message = "Ce login existe déjà !";
        } else {
            $hash = password_hash($mdp, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO utilisateurs 
                (utilisateur_id, nom_prenom, login, mdp, telephone, email, role, photo, type_photo, etat)
                VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$id, $nom, $login, $hash, $telephone, $email, $role, $photo, $type_photo, $etat]);

            $message = "Inscription réussie ! vous pouvez vous connectez";
            $alert_type = 'success';
        }
    } catch (Exception $e) {
        $message = "Erreur : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Soutra+ | Inscription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
            font-family: 'Segoe UI', sans-serif;
        }
        .card {
            max-width: 380px;
            width: 100%;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            border: 1px solid #e0e0e0;
        }
        .card-header {
            background: #007bff;
            color: white;
            text-align: center;
            padding: 1.2rem;
        }
        .card-header h3 { margin: 0; font-size: 1.6rem; font-weight: 700; }
        .card-header p { margin: 4px 0 0; font-size: 0.9rem; opacity: 0.9; }
        .step-indicator {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin: 18px 0 12px;
        }
        .step {
            width: 30px; height: 30px;
            background: #e0e0e0; color: #888;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.9rem; font-weight: bold;
        }
        .step.active { background: #007bff; color: white; transform: scale(1.1); }
        .step.completed { background: #0056b3; color: white; }
        .form-label { font-size: 0.88rem; font-weight: 600; margin-bottom: 4px; }
        .form-control {
            border-radius: 12px;
            padding: 10px 12px;
            font-size: 0.95rem;
            height: 44px;
        }
        .btn {
            border-radius: 50px;
            padding: 10px 24px;
            font-size: 0.95rem;
            font-weight: 600;
        }
        .btn-primary { background: #007bff; border: none; }
        .btn-success { background: #28a745; border: none; }
        .btn-secondary { background: #6c757d; }
        .preview-img {
            width: 95px; height: 95px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #007bff;
        }
        .step-content { display: none; }
        .step-content.active { display: block; animation: fade 0.3s; }
        @keyframes fade { from { opacity: 0; } to { opacity: 1; } }
        .mb-4 { margin-bottom: 0.7rem !important; }
        small { font-size: 0.78rem; }
        .alert { padding: 0.6rem 1rem; font-size: 0.85rem; }
    </style>
</head>
<body>

<div class="card">
    <div class="card-header">
        <h3>Soutra+</h3>
        <p>Créer votre compte</p>
    </div>

    <div class="card-body p-3 px-4">

        <?php if ($message): ?>
            <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show rounded-3 mb-3">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- 4 étapes -->
        <div class="step-indicator">
            <div class="step active" data-step="1">1</div>
            <div class="step" data-step="2">2</div>
            <div class="step" data-step="3">3</div>
            <div class="step" data-step="4">4</div>
        </div>

        <form id="wizardForm" method="post" enctype="multipart/form-data">
            <input type="hidden" name="final_submit" value="1">

            <!-- ÉTAPE 1 : Identité -->
            <div class="step-content active" data-step="1">
                <div class="mb-4">
                    <label class="form-label">ID utilisateur <span class="text-danger">*</span></label>
                    <input type="text" name="utilisateur_id" class="form-control" required placeholder="SUP001">
                </div>
                <div class="mb-4">
                    <label class="form-label">Nom complet <span class="text-danger">*</span></label>
                    <input type="text" name="nom_prenom" class="form-control" required placeholder="Jean Dupont">
                </div>
            </div>

            <!-- ÉTAPE 2 : Contact -->
            <div class="step-content" data-step="2">
                <div class="mb-4">
                    <label class="form-label">Téléphone</label>
                    <input type="tel" name="telephone" class="form-control" placeholder="+221 77 123 45 67">
                </div>
                <div class="mb-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="jean@example.com">
                </div>
            </div>

            <!-- ÉTAPE 3 : Accès -->
            <div class="step-content" data-step="3">
                <div class="mb-4">
                    <label class="form-label">Login <span class="text-danger">*</span></label>
                    <input type="text" name="login" class="form-control" required placeholder="jeandupont">
                </div>
                <div class="mb-4">
                    <label class="form-label">Mot de passe <span class="text-danger">*</span></label>
                    <input type="password" name="mdp" class="form-control" minlength="6" required placeholder="••••••••">
                    <small class="text-muted">Minimum 6 caractères</small>
                </div>
            </div>

            <!-- ÉTAPE 4 : Photo -->
            <div class="step-content" data-step="4">
                <div class="text-center">
                    <img id="previewPhoto" src="https://via.placeholder.com/95/007bff/ffffff?text=Photo"
                         class="preview-img mb-3" alt="Photo">
                    <br>
                    <label class="btn btn-primary">
                        Choisir une photo
                        <input type="file" name="photo" accept="image/*" onchange="preview(event)" style="display:none;">
                    </label>
                    <p class="text-muted small mt-2">Optionnel • Max 2 Mo</p>
                </div>
            </div>

            <!-- Boutons -->
            <div class="d-flex justify-content-between mt-3">
                <button type="button" class="btn btn-secondary" id="prevBtn" style="display:none;">Précédent</button>
                <div>
                    <button type="button" class="btn btn-primary" id="nextBtn">Suivant</button>
                    <button type="submit" class="btn btn-success" id="finishBtn" style="display:none;">Terminé</button>
                </div>
            </div>
        </form>

        <div class="text-center mt-3">
            <small class="text-muted">
                Déjà un compte ? <a href="<?php if(substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])),-1) =="/"){ echo (substr(((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"])), 0,-1)); }else{ echo ((isset($_SERVER["HTTPS"]) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER["PHP_SELF"]));} ?>/utilisateur/connexion" class="text-primary fw-bold">Se connecter</a>
            </small>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let step = 1;
    const total = 4;

    function update() {
        document.querySelectorAll('.step').forEach((s, i) => {
            s.classList.remove('active', 'completed');
            if (i + 1 < step) s.classList.add('completed');
            if (i + 1 === step) s.classList.add('active');
        });
        document.querySelectorAll('.step-content').forEach(c => c.classList.remove('active'));
        document.querySelector(`.step-content[data-step="${step}"]`).classList.add('active');

        document.getElementById('prevBtn').style.display = step === 1 ? 'none' : 'block';
        document.getElementById('nextBtn').style.display = step === total ? 'none' : 'block';
        document.getElementById('finishBtn').style.display = step === total ? 'block' : 'none';
    }

    document.getElementById('nextBtn').onclick = () => { if (step < total) { step++; update(); } };
    document.getElementById('prevBtn').onclick = () => { if (step > 1) { step--; update(); } };

    function preview(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = r => document.getElementById('previewPhoto').src = r.target.result;
            reader.readAsDataURL(file);
        }
    }

    update();
</script>
</body>
</html>