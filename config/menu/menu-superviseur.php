<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soutra CI – Tableau de bord Superviseur</title>
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
            background:var(--primary);color:#fff;padding:15px 20px;font-weight:600;
        }
        .status-vacant {background:#28a745;color:#fff;}
        .status-occupied {background:#dc3545;color:#fff;}
        .status-dirty {background:#6c757d;color:#fff;}
        .status-cleaning {background:#ffc107;color:#212529;}
        @media(max-width:992px){
            .sidebar{width:80px;padding-top:15px;}
            .sidebar .logo, .sidebar .nav-link span{display:none;}
            .sidebar .nav-link{margin:10px auto;padding:15px;text-align:center;}
            .main-content{margin-left:80px;}
        }
    </style>
</head>
<body>

<!-- ==================== SIDEBAR SUPERVISEUR ==================== -->
<div class="sidebar">
    <a href="#" class="logo d-block text-center">Soutra CI</a>
    <nav class="nav flex-column">
        <a class="nav-link active" href="superviseur.php"><i class="fas fa-tachometer-alt"></i><span> Tableau de bord</span></a>
        <a class="nav-link" href=""><i class="fas fa-bed"></i><span> État des chambres</span></a>
        <a class="nav-link" href=""><i class="fas fa-broom"></i><span> Planning ménage</span></a>
        <a class="nav-link" href=""><i class="fas fa-users-cog"></i><span> Équipe & Pointage</span></a>
        <a class="nav-link" href=""><i class="fas fa-clipboard-list"></i><span> Contrôle qualité</span></a>
        <a class="nav-link" href=""><i class="fas fa-box-open"></i><span> Stocks & Fournitures</span></a>
        <a class="nav-link" href=""><i class="fas fa-exclamation-triangle"></i><span> Maintenance & Incidents</span></a>
        <hr class="mx-3 border-secondary">
        <a class="nav-link text-danger" href="deconnexion.php"><i class="fas fa-power-off"></i><span> Déconnexion</span></a>
    </nav>
</div>

<!-- ==================== CONTENU PRINCIPAL ==================== -->
<div class="main-content">
    <!-- Topbar -->
    <div class="topbar">
        <div class="welcome-msg">
            <h4><i class="fas fa-sun text-warning"></i> Bonjour, Fatoumata !</h4>
            <small class="text-muted">Samedi 22 novembre 2025</small>
        </div>
        <div class="d-flex align-items:center gap-3">
            <span class="badge bg-success fs-6">En ligne</span>
            <div class="user-avatar">F</div>
        </div>
    </div>

    <!-- Cartes résumé supervieur -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card card-custom text-white" style="background: linear-gradient(135deg,#17a2b8,#0dcaf0);">
                <div class="card-body p-4">
                    <h5 class="card-title"><i class="fas fa-broom"></i> Chambres à faire</h5>
                    <h2 class="mb-0">23</h2>
                    <small>départs + séjour</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-custom text-white" style="background: linear-gradient(135deg,#28a745,#20c997);">
                <div class="card-body p-4">
                    <h5 class="card-title"><i class="fas fa-check-double"></i> Chambres prêtes</h5>
                    <h2 class="mb-0">39</h2>
                    <small>propres & contrôlées</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-custom text-white" style="background: linear-gradient(135deg,#fd7e14,#f39c12);">
                <div class="card-body p-4">
                    <h5 class="card-title"><i class="fas fa-users"></i> Gouvernants présents</h5>
                    <h2 class="mb-0">14</h2>
                    <small>sur 18 prévus</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-custom text-white" style="background: linear-gradient(135deg,#6f42c1,#5a2d91);">
                <div class="card-body p-4">
                    <h5 class="card-title"><i class="fas fa-bell"></i> Alertes actives</h5>
                    <h2 class="mb-0">5</h2>
                    <small>maintenance + demandes</small>
                </div>
            </div>
        </div>
    </div>
    

    <!-- État des chambres en temps réel + Tâches urgentes -->
    <div class="row">
        <!-- État détaillé des chambres -->
        <div class="col-lg-8">
            <div class="card card-custom">
                <div class="card-header-custom">
                    <i class="fas fa-bed"></i> État des chambres en temps réel
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Chambre</th>
                                    <th>Type</th>
                                    <th>Statut actuel</th>
                                    <th>Assignée à</th>
                                    <th>Prochaine action</th>
                                    <th>Priorité</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>108</strong></td>
                                    <td>Standard</td>
                                    <td><span class="badge status-occupied">Occupée</span></td>
                                    <td>Départ 11h</td>
                                    <td>Ménage départ</td>
                                    <td><span class="badge bg-danger">Urgent</span></td>
                                </tr>
                                <tr>
                                    <td><strong>215</strong></td>
                                    <td>Suite</td>
                                    <td><span class="badge status-cleaning">En cours ménage</span></td>
                                    <td>Aïcha K.</td>
                                    <td>Contrôle qualité</td>
                                    <td><span class="badge bg-warning text-dark">Haute</span></td>
                                </tr>
                                <tr>
                                    <td><strong>312</strong></td>
                                    <td>Deluxe</td>
                                    <td><span class="badge status-vacant">Propre</span></td>
                                    <td>Contrôlée</td>
                                    <td>Prête arrivée VIP 14h</td>
                                    <td><span class="badge bg-info">VIP</span></td>
                                </tr>
                                <tr>
                                    <td><strong>407</strong></td>
                                    <td>Standard</td>
                                    <td><span class="badge status-dirty">Sale</span></td>
                                    <td>—</td>
                                    <td>À assigner</td>
                                    <td><span class="badge bg-secondary">Normal</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Demandes clients + Alertes rapides -->
        <div class="col-lg-4">
            <div class="card card-custom h-100">
                <div class="card-header-custom">
                    <i class="fas fa-bell"></i> Demandes & Alertes urgentes
                </div>
                <div class="card-body d-flex flex-column gap-3">
                    <div class="alert alert-warning small mb-2 p-2">
                        <strong>Chambre 505</strong> → Fuite d’eau signalée (maintenance appelée)
                    </div>
                    <div class="alert alert-info small mb-2 p-2">
                        <strong>Chambre 302</strong> → Demande lit bébé + planche à repasser
                    </div>
                    <div class="alert alert-danger small mb-2 p-2">
                        <strong>Chambre 110</strong> → Réclamation bruit – Client très mécontent
                    </div>
                    <div class="alert alert-primary small mb-2 p-2">
                        <strong>Objets trouvés</strong> → Portefeuille noir dans 208
                    </div>
                    <div class="mt-auto">
                        <button class="btn btn-danger w-100"><i class="fas fa-exclamation-circle"></i> Voir toutes les alertes</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ligne du bas : Pointage équipe du jour -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card card-custom">
                <div class="card-header-custom">
                    <i class="fas fa-users"></i> Pointage équipe ménage – Aujourd'hui
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 col-md-3 col-lg-2 mb-3">
                            <div class="bg-success text-white rounded-circle mx-auto mb-2" style="width:60px;height:60px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;">
                                A
                            </div>
                            <small>Aïcha</small><br><span class="text-success fw-bold">Présente</span>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2 mb-3">
                            <div class="bg-success text-white rounded-circle mx-auto mb-2" style="width:60px;height:60px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;">
                                M
                            </div>
                            <small>Marie</small><br><span class="text-success fw-bold">Présente</span>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2 mb-3">
                            <div class="bg-danger text-white rounded-circle mx-auto mb-2" style="width:60px;height:60px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;">
                                S
                            </div>
                            <small>Saran</small><br><span class="text-danger fw-bold">Absente</span>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2 mb-3">
                            <div class="bg-warning text-dark rounded-circle mx-auto mb-2" style="width:60px;height:60px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;">
                                F
                            </div>
                            <small>Fatou</small><br><span class="text-warning fw-bold">En retard</span>
                        </div>
                        <!-- Ajouter les autres membres... -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>