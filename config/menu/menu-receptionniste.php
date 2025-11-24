<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soutra CI – Tableau de bord Réceptionniste</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root{
            --primary:#003580;--secondary:#0a3d78;--yellow:#ffcc00;--light:#f8f9fa;
        }
        body{font-family:'Roboto',sans-serif;background:var(--light);margin:0;height:100vh;overflow-x:hidden;}
        .sidebar{
            position:fixed;top:0;left:0;width:280px;height:100%;background:var(--primary);color:#fff;padding-top:20px;
            transition:all .3s;z-index:1000;box-shadow:2px 0 10px rgba(0,0,0,.1);
        }
        .sidebar .logo{
            font-size:1.8rem;font-weight:700;text-align:center;margin-bottom:30px;color:#fff;text-decoration:none;
        }
        .sidebar .nav-link{
            color:#ddd;padding:12px 20px;border-radius:8px;margin:5px 15px;transition:.3s;display:flex;align-items:center;
            font-weight:500;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active{
            background:rgba(255,255,255,.2);color:#fff;
        }
        .sidebar .nav-link i{width:30px;font-size:1.1rem;}
        .main-content{margin-left:280px;padding:20px;min-height:100vh;transition:all .3s;}
        .topbar{
            background:#fff;box-shadow:0 2px 10px rgba(0,0,0,.1);padding:15px 25px;border-radius:12px;
            display:flex;justify-content:space-between;align-items:center;margin-bottom:25px;
        }
        .welcome-msg h4{margin:0;color:var(--primary);}
        .user-avatar{
            width:45px;height:45px;border-radius:50%;background:var(--yellow);display:flex;align-items:center;
            justify-content:center;font-weight:bold;color:var(--primary);font-size:1.3rem;
        }
        .card-custom{
            border:none;border-radius:16px;box-shadow:0 6px 20px rgba(0,0,0,.1);overflow:hidden;
        }
        .card-header-custom{
{
            background:var(--primary);color:#fff;padding:15px 20px;font-weight:600;
        }
        @media(max-width:992px){
            .sidebar{width:80px;padding-top:15px;}
            .sidebar .logo, .sidebar .nav-link span{display:none;}
            .sidebar .nav-link{margin:10px auto;padding:15px;text-align:center;}
            .main-content{margin-left:80px;}
        }
    </style>
</head>
<body>

<!-- ==================== SIDEBAR RÉCEPTIONNISTE ==================== -->
<div class="sidebar">
    <a href="#" class="logo d-block text-center">Soutra CI</a>
    <nav class="nav flex-column">
        <a class="nav-link active" href="receptionniste.php"><i class="fas fa-tachometer-alt"></i><span> Tableau de bord</span></a>
        <a class="nav-link" href=""><i class="fas fa-calendar-check"></i><span> Réservations</span></a>
        <a class="nav-link" href=""><i class="fas fa-door-open"></i><span> État des chambres</span></a>
        <a class="nav-link" href=""><i class="fas fa-users"></i><span> Clients</span></a>
        <a class="nav-link" href=""><i class="fas fa-cash-register"></i><span> Caisse & Factures</span></a>
        <hr class="mx-3 border-secondary">
        <a class="nav-link text-danger" href="deconnexion.php"><i class="fas fa-power-off"></i><span> Déconnexion</span></a>
    </nav>
</div>


<!-- ==================== CONTENU PRINCIPAL ==================== -->
<div class="main-content">
    <!-- Topbar -->
    <div class="topbar">
        <div class="welcome-msg">
            <h4><i class="fas fa-sun text-warning"></i> Bonjour, Marie !</h4>
            <small class="text-muted">Mercredi 19 novembre 2025</small>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-success fs-6">En ligne</span>
            <div class="user-avatar">M</div>
        </div>
    </div>

    <!-- Cartes résumé -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card card-custom text-white" style="background: linear-gradient(135deg,#28a745,#20c997);">
                <div class="card-body p-4">
                    <h5 class="card-title"><i class="fas fa-sign-in-alt"></i> Arrivées</h5>
                    <h2 class="mb-0">12</h2>
                    <small>aujourd'hui</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-custom text-white" style="background: linear-gradient(135deg,#007bff,#0d6efd);">
                <div class="card-body p-4">
                    <h5 class="card-title"><i class="fas fa-bed"></i> Occupées</h5>
                    <h2 class="mb-0">48</h2>
                    <small>sur 62 chambres</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-custom text-white" style="background: linear-gradient(135deg,#ffc107,#ffb300);color:#212529;">
                <div class="card-body p-4">
                    <h5 class="card-title"><i class="fas fa-bell"></i> Demandes</h5>
                    <h2 class="mb-0">7</h2>
                    <small>en attente</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-custom text-white" style="background: linear-gradient(135deg,#dc3545,#c82333);">
                <div class="card-body p-4">
                    <h5 class="card-title"><i class="fas fa-sign-out-alt"></i> Départs</h5>
                    <h2 class="mb-0">9</h2>
                    <small>prévu aujourd'hui</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Dernières actions rapides -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-custom">
                <div class="card-header-custom">
                    <i class="fas fa-clock"></i> Dernières réservations confirmées
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Client</th>
                                    <th>Chambre</th>
                                    <th>Arrivée</th>
                                    <th>Départ</th>
                                    <th>Statut</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Kouassi Jean</td>
                                    <td>205 - Suite</td>
                                    <td>19/11/2025</td>
                                    <td>22/11/2025</td>
                                    <td><span class="badge bg-success">Confirmée</span></td>
                                    <td><button class="btn btn-sm btn-primary"><i class="fas fa-key"></i> Check-in</button></td>
                                </tr>
                                <tr>
                                    <td>Traoré Awa</td>
                                    <td>112 - Standard</td>
                                    <td>19/11/2025</td>
                                    <td>20/11/2025</td>
                                    <td><span class="badge bg-warning text-dark">En attente</span></td>
                                    <td><button class="btn btn-sm btn-outline-primary">Contacter</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat rapide avec les clients -->
        <div class="col-lg-4">
            <div class="card card-custom h-100">
                <div class="card-header-custom">
                    <i class="fas fa-comments"></i> Messagerie instantanée
                </div>
                <div class="card-body d-flex flex-column">
                    <div class="alert alert-info small mb-3">
                        <strong>Chambre 308</strong> – M. Konaté<br>
                        "Pourriez-vous m’envoyer des serviettes supplémentaires ?"
                    </div>
                    <div class="alert alert-info small mb-3">
                        <strong>Chambre 515</strong> – Mme Diarra<br>
                        "Le Wi-Fi ne fonctionne pas bien"
                    </div>
                    <div class="mt-auto">
                        <button class="btn btn-primary w-100"><i class="fas fa-comment-dots"></i> Ouvrir le chat</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>