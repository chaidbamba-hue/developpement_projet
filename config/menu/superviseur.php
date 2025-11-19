  <!-- ==================== NAVBAR ==================== -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="#" class="nav-link">Accueil</a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <img src="<?= $user_photo ?>" class="user-image img-circle elevation-2" alt="User Image">
                    <span class="d-none d-md-inline"><?= $user_name ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <li class="user-header bg-primary">
                        <img src="<?= $user_photo ?>" class="img-circle elevation-2" alt="User Image">
                        <p>
                            <?= $user_name ?> - <?= $user_role ?>
                            <small>Membre depuis 2024</small>
                        </p>
                    </li>
                    <li class="user-body">
                        <div class="row">
                            <div class="col-6 text-center">
                                <a href="#" class="btn btn-default btn-flat">Profil</a>
                            </div>
                            <div class="col-6 text-center">
                                <a href="#" class="btn btn-default btn-flat">Déconnexion</a>
                            </div>
                        </div>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>

    <!-- ==================== SIDEBAR ==================== -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="#" class="brand-link text-center py-3">
            <span class="brand-text font-weight-light fw-bold">Soutra+</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar user panel -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="<?= $user_photo ?>" class="img-circle elevation-2" alt="User Image">
                </div>
                <div class="info">
                    <a href="#" class="d-block text-white"><?= $user_name ?></a>
                    <span class="badge badge-success"><?= $user_role ?></span>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Tableau de bord</p>
                        </a>
                    </li>

                    <!-- Gestion Hôtelière -->
                    <li class="nav-item menu-open">
                        <a href="#" class="nav-link active">
                            <i class="nav-icon fas fa-hotel"></i>
                            <p>Gestion Hôtelière <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="crud_hotels.php" class="nav-link active">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Hôtels</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="crud_chambres.php" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Chambres</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="crud_clients.php" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Clients</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="crud_reservations.php" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Réservations</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Gestion Utilisateurs -->
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Utilisateurs <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="crud_utilisateurs.php" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Liste des utilisateurs</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Rôles & Permissions</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Facturation -->
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-file-invoice-dollar"></i>
                            <p>Facturation <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="crud_factures.php" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Factures</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Paiements</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Paramètres -->
                    <li class="nav-header">PARAMÈTRES</li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-cog"></i>
                            <p>Configuration</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>