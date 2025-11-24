<?php
session_start(); // OBLIGATOIRE !

// ==================== CONNEXION BDD ====================
$host = 'localhost';
$dbname = 'u738064605_soutra';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(Exception $e) {
    die('<div style="text-align:center;padding:100px;background:#000;color:#fff;font-size:24px;">Erreur de connexion à la base de données.</div>');
}

// ==================== DONNÉES SIMULÉES (remplace la table 'chambres') ====================
// Nous allons simuler les détails des chambres pour un hôtel donné
function getRoomDetails($code_hotel) {
    // Les types de chambres disponibles par défaut (comme dans le select d'origine)
    $baseTypes = [
        'Chambre Standard', 
        'Chambre Deluxe', 
        'Chambre De passage', 
        'Suite Junior', 
        'Suite Présidentielle'
    ];
    $chambres = [];
    $basePrice = rand(45, 180) * 1000;

    foreach ($baseTypes as $i => $type) {
        $price = $basePrice + (rand(0, 5) * 20000); // Prix aléatoire basé sur le type
        
        $details = [
            'type_chambre' => $type,
            'code_chambre_simule' => $code_hotel . '-' . ($i + 1), // Clé unique simulée
            'prix_nuit' => $price,
            'capacite' => rand(1, 4),
            'description_chambre' => "Description pour la chambre de type '$type' de l'hôtel. Elle offre un confort optimal et des services exclusifs.",
            'services' => ['WiFi gratuit', 'Climatisation'],
        ];

        if (str_contains($type, 'Suite')) {
            $details['capacite'] = rand(3, 5);
            $details['services'][] = 'Mini-bar inclus';
        } elseif (str_contains($type, 'passage')) {
             $details['prix_nuit'] = rand(25, 40) * 1000;
        }

        $chambres[] = $details;
    }
    return $chambres;
}

// Récupérer une chambre spécifique (utilisé pour la page de détail d'une chambre)
function getRoomBySimulatedCode($code_hotel, $simulated_code) {
    $allRooms = getRoomDetails($code_hotel);
    foreach ($allRooms as $room) {
        if ($room['code_chambre_simule'] === $simulated_code) {
            return $room;
        }
    }
    return false;
}

// ==================== LISTE DE PHOTOS ====================
$hotelPhotos = [
    'https://images.unsplash.com/photo-1611892441792-ae6af9366e2c?w=1200&q=90',
    'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1200&q=90',
    'https://images.unsplash.com/photo-1578683015141-0b5744e7c4cb?w=1200&q=90',
    'https://images.unsplash.com/photo-1542314831-0682f0080b97?w=1200&q=90',
    'https://images.unsplash.com/photo-1520250497591-357d63e2b943?w=1200&q=90',
    'https://images.unsplash.com/photo-1582719478250-c89ccc601aa3?w=1200&q=90',
    'https://images.unsplash.com/photo-1564507592333-c60657eea523?w=1200&q=90',
    'https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?w=1200&q=90',
    'https://images.unsplash.com/photo-1549298916-b787d2e11f14?w=1200&q=90',
    'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?w=1200&q=90',
    'https://images.unsplash.com/photo-1618776185822-571e12d4a16e?w=1200&q=90',
    'https://images.unsplash.com/photo-1584132961644-958a3978f4d4?w=1200&q=90',
    'https://images.unsplash.com/photo-1562790351-d273d5f5a5c2?w=1200&q=90',
    'https://images.unsplash.com/photo-1549638441-b787d2e11f14?w=1200&q=90',
    'https://images.unsplash.com/photo-1522708323590-d24dbb6b03e7?w=1200&q=90',
    'https://images.unsplash.com/photo-1576354302919-96748cb9899e?w=1200&q=90',
    'https://images.unsplash.com/photo-1586105251261-72a1f4a4d84e?w=1200&q=90',
    'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=1200&q=90',
    'https://images.unsplash.com/photo-1512918728675-ed5a9ecdebfd?w=1200&q=90',
    'https://images.unsplash.com/photo-1541123356219-284ebe98ae48?w=1200&q=90',
    'https://images.unsplash.com/photo-1571896349842-33c8940572a9?w=1200&q=90',
    'https://images.unsplash.com/photo-1587982710102-7e39166fc6d9?w=1200&q=90',
    'https://images.unsplash.com/photo-1576678927484-2088f4d6c6f8?w=1200&q=90',
    'https://images.unsplash.com/photo-1568082359232-4d4cd8e3d7a9?w=1200&q=90',
    'https://images.unsplash.com/photo-1541971875074-3c06af9da6d2?w=1200&q=90',
    'https://images.unsplash.com/photo-1561505457-7f9d5d5e0e9b?w=1200&q=90',
    'https://images.unsplash.com/photo-1587874520646-9e46c146a9f9?w=1200&q=90',
    'https://images.unsplash.com/photo-1570214470622-d5b7d6a1b7bb?w=1200&q=90',
    'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=1200&q=90',
    'https://images.unsplash.com/photo-1590490360182-c33d577abc0b?w=1200&q=90'
];

$bg = $hotelPhotos[array_rand($hotelPhotos)];
$message = '';
$searchResults = [];
$isSearching = false;

// ==================== TRAITEMENT RÉSERVATION (Utilise le type_chambre) ====================
if (isset($_POST['reserver'])) {
    $code_hotel = $_POST['code_hotel'] ?? '';
    // Nous utilisons le type_chambre dans le formulaire, pas un code chambre séparé
    $type_chambre = $_POST['type_chambre_reservee'] ?? ''; 
    $date_debut = $_POST['date_debut'] ?? '';
    $date_fin = $_POST['date_fin'] ?? '';

    if ($code_hotel && $date_debut && $date_fin && $type_chambre) {
        try {
            // Utilisation de type_chambre dans la requête INSERT pour coller à la BDD existante
            $stmt = $pdo->prepare("INSERT INTO reservations (code_hotel, date_debut, date_fin, type_chambre, date_reservation) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$code_hotel, $date_debut, $date_fin, $type_chambre]);
            
            $message = "<div class='success-box'>
                <i class='fas fa-check-circle'></i>
                <h2>Réservation confirmée !</h2>
                <p>Votre chambre de type **".htmlspecialchars($type_chambre)."** a été réservée. Un email de confirmation vous a été envoyé.</p>
                <a href='.' class='btn-back'>Retour à l'accueil</a>
            </div>";
        } catch(Exception $e) {
             $message = "<div class='alert alert-danger'>Erreur : réservation non enregistrée. Veuillez réessayer.</div>";
        }
    } else {
         $message = "<div class='alert alert-danger'>Erreur : Tous les champs de réservation sont requis.</div>";
    }
}

// ==================== RECHERCHE ====================
if (isset($_POST['search']) && !empty(trim($_POST['query'] ?? ''))) {
    $isSearching = true;
    $query = trim($_POST['query']);
    $q = '%' . $query . '%';
    $stmt = $pdo->prepare("SELECT * FROM hotels WHERE etat_hotel = 'actif' AND (ville_hotel LIKE ? OR quartier_hotel LIKE ? OR nom_hotel LIKE ?) ORDER BY nom_hotel LIMIT 30");
    $stmt->execute([$q, $q, $q]);
    $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ==================== PAGE DÉTAIL CHAMBRE SIMULÉE ====================
elseif (isset($_GET['chambre_simulee'])) {
    list($code_hotel_ref, $code_chambre_simule) = explode('-', $_GET['chambre_simulee']);
    
    // 1. Récupérer les infos de l'hôtel
    $stmt = $pdo->prepare("SELECT * FROM hotels WHERE code_hotel = ? AND etat_hotel = 'actif'");
    $stmt->execute([$code_hotel_ref]);
    $hotel = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Récupérer les détails de la chambre simulée
    $chambre = getRoomBySimulatedCode($code_hotel_ref, $_GET['chambre_simulee']);

    if (!$hotel || !$chambre) {
        die("<div style='text-align:center;padding:200px;background:#000;color:#fff;font-size:32px;'>Chambre ou Hôtel non trouvé.</div>");
    }
    
    // Données d'affichage
    $photos = $hotelPhotos;
    shuffle($photos);
    $chambrePhotos = array_slice($photos, 0, 8);
    $note = number_format(8.1 + mt_rand(0, 18) / 10, 1);
    $avis = rand(200, 2800);

}

// ==================== PAGE DÉTAIL HÔTEL (Affichage des chambres simulées) ====================
elseif (isset($_GET['hotel'])) {
    $code = $_GET['hotel'];
    $stmt = $pdo->prepare("SELECT * FROM hotels WHERE code_hotel = ? AND etat_hotel = 'actif'");
    $stmt->execute([$code]);
    $hotel = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$hotel) {
        die("<div style='text-align:center;padding:200px;background:#000;color:#fff;font-size:32px;'>Hôtel non trouvé ou désactivé.</div>");
    }

    // Récupérer les chambres SIMULÉES de cet hôtel
    $chambres = getRoomDetails($code);

    $photos = $hotelPhotos;
    shuffle($photos);
    $photos = array_slice($photos, 0, 8);
    $note = number_format(8.1 + mt_rand(0, 18) / 10, 1);
    $avis = rand(200, 2800);
}

// ==================== ACCUEIL ====================
else {
    $villes = $pdo->query("SELECT ville_hotel, COUNT(*) as nb FROM hotels WHERE etat_hotel='actif' GROUP BY ville_hotel ORDER BY nb DESC LIMIT 12")->fetchAll(PDO::FETCH_ASSOC);
    $offres = $pdo->query("SELECT * FROM hotels WHERE etat_hotel='actif' ORDER BY RAND() LIMIT 12")->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>soutra – 
        <?php if (isset($chambre)): ?>
            <?=htmlspecialchars($chambre['type_chambre'])?> - <?=htmlspecialchars($hotel['nom_hotel'])?>
        <?php elseif (isset($hotel)): ?>
            <?=htmlspecialchars($hotel['nom_hotel'])?>
        <?php else: ?>
            Réservez votre hôtel de rêve
        <?php endif; ?>
    </title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght=700;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <style>
        :root{--orange:#ff6b35;--bleu:#0a2647;--gris:#f8f9fa;}
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Inter',sans-serif;background:var(--gris);color:#222;overflow-x:hidden;}
        .header{position:fixed;top:0;left:0;right:0;height:90px;background:rgba(10,38,71,0.95);backdrop-filter:blur(12px);display:flex;align-items:center;justify-content:space-between;padding:0 5%;z-index:1000;box-shadow:0 10px 30px rgba(0,0,0,0.2);}
        .logo-header{font-family:'Playfair Display',serif;font-size:42px;color:white;font-weight:900;letter-spacing:4px;}
        .hero{background:linear-gradient(rgba(10,38,71,0.82),rgba(10,38,71,0.95)),url('<?=$bg?>') center/cover no-repeat fixed;min-height:100vh;display:flex;align-items:center;justify-content:center;padding-top:90px;position:relative;}
        .logo{font-family:'Playfair Display',serif;font-size:140px;color:white;text-shadow:0 20px 40px rgba(0,0,0,0.6);animation:float 6s infinite;}
        @keyframes float{0%,100%{transform:translateY(0);}50%{transform:translateY(-20px);}}
        .search-box{max-width:960px;width:90%;background:white;border-radius:80px;box-shadow:0 30px 80px rgba(0,0,0,0.25);overflow:hidden;display:flex;transition:0.4s;margin:50px auto;}
        .search-box:focus-within{box-shadow:0 40px 100px rgba(255,107,53,0.3);transform:scale(1.02);}
        .search-input{flex:1;padding:28px 40px;font-size:24px;border:none;outline:none;}
        .search-btn{background:var(--orange);color:white;border:none;width:80px;height:80px;border-radius:50%;font-size:28px;cursor:pointer;transition:0.3s;}
        .search-btn:hover{background:#e55a2b;transform:scale(1.1);}
        .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:40px;margin-top:40px;}
        .card,.offer-card{border-radius:32px;overflow:hidden;box-shadow:0 25px 70px rgba(0,0,0,0.15);transition:0.4s;cursor:pointer;background:white;}
        .card:hover,.offer-card:hover{transform:translateY(-20px);box-shadow:0 50px 100px rgba(0,0,0,0.25);}
        .card-img,.offer-img{height:360px;background:center/cover no-repeat;position:relative;}
        .card-overlay{position:absolute;inset:0;background:linear-gradient(transparent 40%,rgba(0,0,0,0.9));display:flex;flex-direction:column;justify-content:end;padding:40px;color:white;}
        .card-overlay h3{font-size:32px;font-weight:700;}
        .offer-info{padding:32px;}
        .offer-info h3{font-size:26px;color:var(--bleu);margin-bottom:8px;}
        .heart{position:absolute;top:20px;right:20px;width:56px;height:56px;background:rgba(255,255,255,0.95);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:26px;transition:0.3s;color:#ccc;}
        .heart:hover{color:var(--orange);}
        .back-btn{position:fixed;top:120px;left:40px;z-index:100;background:rgba(0,0,0,0.6);color:white;width:60px;height:60px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:32px;transition:0.3s;}
        .back-btn:hover{background:var(--orange);}
        .gallery{max-width:1500px;margin:40px auto;padding:0 5%;display:grid;grid-template-columns:2fr 1fr;gap:20px;}
        .main-photo{height:620px;background:center/cover;border-radius:32px;box-shadow:0 40px 100px rgba(0,0,0,0.3);}
        .side-photos{display:grid;gap:20px;}
        .side-photos div{height:148px;background:center/cover;border-radius:24px;}
        .hotel-body, .chambre-body{max-width:1400px;margin:60px auto;padding:0 5%;display:grid;grid-template-columns:1fr 440px;gap:60px;}
        .chambre-body{grid-template-columns:1fr;} /* La chambre occupe toute la largeur pour le moment */
        .booking-box{background:white;border-radius:32px;padding:40px;box-shadow:0 30px 100px rgba(0,0,0,0.15);position:sticky;top:110px;}
        .price{font-size:52px;font-weight:800;color:var(--orange);text-align:center;margin-bottom:20px;}
        .reserve-btn{width:100%;background:var(--orange);color:white;border:none;padding:22px;font-size:24px;border-radius:16px;cursor:pointer;font-weight:600;transition:0.3s;}
        .reserve-btn:hover{background:#e55a2b;transform:translateY(-4px);}
        .success-box{background:#d4edda;color:#155724;padding:120px 40px;border-radius:32px;text-align:center;max-width:700px;margin:100px auto;box-shadow:0 30px 80px rgba(0,0,0,0.1);}
        .success-box i{font-size:90px;color:#28a745;margin-bottom:20px;display:block;}
        .btn-back{padding:18px 60px;background:var(--orange);color:white;border-radius:50px;text-decoration:none;font-weight:600;font-size:18px;display:inline-block;margin-top:20px;}

        /* Style pour l'affichage des chambres */
        .chambre-list{margin-top:50px;}
        .chambre-item{background:white;border-radius:20px;padding:25px;margin-bottom:20px;box-shadow:0 10px 30px rgba(0,0,0,0.08);display:flex;gap:30px;align-items:center;transition:0.3s;cursor:pointer;}
        .chambre-item:hover{box-shadow:0 20px 50px rgba(0,0,0,0.15);transform:translateY(-5px);}
        .chambre-img{width:200px;height:140px;background:center/cover;border-radius:15px;flex-shrink:0;}
        .chambre-details{flex-grow:1;}
        .chambre-details h4{font-size:24px;color:var(--bleu);margin-bottom:5px;}
        .chambre-price{font-size:36px;font-weight:800;color:var(--orange);flex-shrink:0;}

        /* Style pour la page de détail d'une chambre */
        .chambre-detail-content{max-width:1200px;margin:140px auto;padding:0 5%;}
                /* FOOTER */
        footer{background:var(--bleu);color:white;padding:100px 5% 50px;margin-top:150px;}
        .footer-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:50px;}
        .footer-col h3{font-family:'Playfair Display',serif;font-size:32px;margin-bottom:30px;}
        .footer-col ul{list-style:none;}
        .footer-col ul li{margin-bottom:14px;}
        .footer-col ul li a{color:#ccc;text-decoration:none;transition:0.3s;}
        .footer-col ul li a:hover{color:var(--orange);padding-left:8px;}
        .social-links{margin-top:30px;display:flex;gap:16px;}
        .social-links a{width:50px;height:50px;background:rgba(255,255,255,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:20px;transition:0.3s;}
        .social-links a:hover{background:var(--orange);transform:translateY(-5px);}
        .copyright{text-align:center;padding-top:60px;border-top:1px solid rgba(255,255,255,0.1);margin-top:60px;font-size:15px;color:#aaa;}
    </style>
</head>
<body>

<div class="header">
    <a href="." class="logo-header" style="text-decoration:none;">soutra</a>
    <div style="display:flex;gap:18px;">
        <a href="connexion.php" style="padding:12px 32px;border:2px solid white;border-radius:50px;color:white;text-decoration:none;font-weight:600;transition:0.3s;">Connexion</a>
        <a href="inscription.php" style="padding:12px 32px;background:var(--orange);color:white;border-radius:50px;text-decoration:none;font-weight:600;transition:0.3s;">Inscription</a>
    </div>
</div>

<?php if ($message): ?>
    <?= $message ?>

<?php elseif (isset($chambre)): ?>
    <a href="?hotel=<?=htmlspecialchars($hotel['code_hotel'])?>" class="back-btn"><i class="fas fa-arrow-left"></i></a>

    <div class="chambre-detail-content">
        <h1 style="font-size:48px;color:var(--bleu);margin-bottom:10px;"><?=htmlspecialchars($chambre['type_chambre'])?></h1>
        <p style="font-size:22px;color:#555;margin-bottom:40px;">
            Hôtel: <a href="?hotel=<?=htmlspecialchars($hotel['code_hotel'])?>" style="color:var(--orange);text-decoration:none;font-weight:600;"><?=htmlspecialchars($hotel['nom_hotel'])?></a>, <?=htmlspecialchars($hotel['ville_hotel'])?>
        </p>

        <div class="gallery" style="grid-template-columns:1fr;">
            <div class="main-photo" style="height:500px;background-image:url('<?=$chambrePhotos[0]?>')"></div>
        </div>

        <div class="hotel-body" style="grid-template-columns:1fr 400px;margin-top:60px;">
            <div>
                <h2 style="font-size:32px;margin-bottom:20px;">Détails de la Chambre</h2>
                <p style="font-size:18px;line-height:1.8;color:#444;margin-bottom:30px;">
                    <?=nl2br(htmlspecialchars($chambre['description_chambre']))?>
                </p>
                <div style="display:flex;gap:40px;border-top:1px solid #eee;padding-top:20px;">
                    <div><i class="fas fa-bed" style="color:var(--orange);margin-right:8px;"></i> Capacité: <strong><?=$chambre['capacite']?></strong> personnes</div>
                    <div><i class="fas fa-wifi" style="color:var(--orange);margin-right:8px;"></i> Services inclus: <strong><?=implode(', ', $chambre['services'])?></strong></div>
                </div>
            </div>

            <div class="booking-box">
                <div class="price"><?=number_format($chambre['prix_nuit'])?> FCFA <small style="font-size:19px;color:#666;">/ nuit</small></div>
                <form method="post">
                    <input type="hidden" name="code_hotel" value="<?=htmlspecialchars($hotel['code_hotel'])?>">
                    <input type="hidden" name="type_chambre_reservee" value="<?=htmlspecialchars($chambre['type_chambre'])?>"> 
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin:25px 0;">
                        <div>
                            <label style="display:block;margin-bottom:8px;font-weight:600;">Arrivée</label>
                            <input type="date" name="date_debut" required style="width:100%;padding:16px;border-radius:12px;border:1px solid #ddd;font-size:16px;">
                        </div>
                        <div>
                            <label style="display:block;margin-bottom:8px;font-weight:600;">Départ</label>
                            <input type="date" name="date_fin" required style="width:100%;padding:16px;border-radius:12px;border:1px solid #ddd;font-size:16px;">
                        </div>
                    </div>
                    <button type="submit" name="reserver" class="reserve-btn">Réserver cette Chambre</button>
                </form>
            </div>
        </div>
    </div>


<?php elseif (isset($hotel)): ?>
    <div class="hero" style="height:70vh;background-image:url('<?=$photos[0]?>')"></div>
    <a href="." class="back-btn"><i class="fas fa-arrow-left"></i></a>

    <div class="gallery">
        <div class="main-photo" style="background-image:url('<?=$photos[0]?>')"></div>
        <div class="side-photos">
            <?php foreach (array_slice($photos, 1, 4) as $p): ?>
                <div style="background-image:url('<?=$p?>')"></div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="hotel-body" style="grid-template-columns:1fr;"> 
        <div>
            <h1><?=htmlspecialchars($hotel['nom_hotel'])?></h1>
            <p style="font-size:19px;color:#555;margin:12px 0;">
                <i class="fas fa-map-marker-alt" style="color:var(--orange);"></i>
                <?=htmlspecialchars($hotel['quartier_hotel'].', '.$hotel['ville_hotel'])?>
            </p>
            <div style="font-size:58px;color:var(--orange);font-weight:800;"><?=$note?> / 10</div>
            <p style="color:#666;font-size:17px;"><?=$avis?> avis vérifiés</p>

            <div style="margin:50px 0;display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:16px;">
                <?php $services = ['WiFi gratuit','Piscine','Parking','Restaurant','Spa','Climatisation','Room Service','Réception 24h/24']; ?>
                <?php foreach($services as $s): ?>
                    <span style="background:#f0f8ff;padding:16px 20px;border-radius:16px;text-align:center;font-weight:500;font-size:15px;"><?=$s?></span>
                <?php endforeach; ?>
            </div>

            <h2 style="font-family:'Playfair Display',serif;font-size:42px;color:var(--bleu);margin-top:60px;border-bottom:3px solid var(--orange);display:inline-block;padding-bottom:10px;">Types de Chambres disponibles</h2>

            <div class="chambre-list">
                <?php 
                // Récupération des chambres SIMULÉES (car nous ne touchons pas à la BDD)
                $chambres = getRoomDetails($code);
                if (empty($chambres)): ?>
                     <p style="font-size:20px;color:#666;padding:50px 0;">Aucun type de chambre n'est disponible pour cet hôtel.</p>
                <?php else: ?>
                    <?php foreach ($chambres as $c): $chambreImg = $hotelPhotos[array_rand($hotelPhotos)]; ?>
                        <div class="chambre-item" onclick="location.href='?hotel=<?=htmlspecialchars($code)?>&chambre_simulee=<?=htmlspecialchars($c['code_chambre_simule'])?>'">
                            <div class="chambre-img" style="background-image:url('<?=$chambreImg?>')"></div>
                            <div class="chambre-details">
                                <h4><?=htmlspecialchars($c['type_chambre'])?></h4>
                                <p style="color:#666;margin-bottom:10px;"><i class="fas fa-user-friends"></i> Capacité: <?=$c['capacite']?> personnes</p>
                                <p style="color:#888;font-size:15px;"><?=substr(htmlspecialchars($c['description_chambre']), 0, 100)?>...</p>
                            </div>
                            <div class="chambre-price"><?=number_format($c['prix_nuit'])?> FCFA</div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </div>

<?php elseif ($isSearching): ?>
    <div style="padding:140px 5% 100px;background:#fff;">
        <h2 style="font-family:'Playfair Display',serif;font-size:52px;color:var(--bleu);text-align:center;margin-bottom:60px;">
            Résultats pour "<?=htmlspecialchars($_POST['query'])?>"
        </h2>
        <?php if (empty($searchResults)): ?>
            <p style="text-align:center;font-size:24px;color:#666;padding:100px;">Aucun hôtel trouvé.</p>
        <?php else: ?>
            <div class="grid">
                <?php foreach ($searchResults as $h): $img = $hotelPhotos[array_rand($hotelPhotos)]; ?>
                    <div class="offer-card" onclick="location.href='?hotel=<?=htmlspecialchars($h['code_hotel'])?>'">
                        <div class="offer-img" style="background-image:url('<?=$img?>')">
                            <div class="heart"><i class="far fa-heart"></i></div>
                        </div>
                        <div class="offer-info">
                            <h3><?=htmlspecialchars($h['nom_hotel'])?></h3>
                            <p style="color:#666;margin:8px 0;"><?=htmlspecialchars($h['ville_hotel'])?></p>
                            <span style="background:#007bff;color:white;padding:8px 16px;border-radius:10px;font-weight:600;"><?=number_format(8.2 + mt_rand(0,16)/10, 1)?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

<?php else: ?>
    <div class="hero">
        <div style="text-align:center;">
            <div class="logo">soutra</div>
            <form method="post" class="search-box">
                <input type="text" name="query" class="search-input" placeholder="Rechercher..." required autofocus>
                <button type="submit" name="search" class="search-btn"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>


    <div style="padding:140px 5%;background:#fff;max-width:1600px;margin:auto;">
        <h2 style="font-family:'Playfair Display',serif;font-size:56px;color:var(--bleu);text-align:center;margin-bottom:80px;position:relative;">
            Offres exceptionnelles
            <div style="width:140px;height:6px;background:var(--orange);position:absolute;bottom:-20px;left:50%;transform:translateX(-50%);border-radius:3px;"></div>
        </h2>
        <div class="grid">
            <?php foreach($offres as $h): $img = $hotelPhotos[array_rand($hotelPhotos)]; ?>
                <div class="offer-card" onclick="location.href='?hotel=<?=htmlspecialchars($h['code_hotel'])?>'">
                    <div class="offer-img" style="background-image:url('<?=$img?>')">
                        <div class="heart"><i class="far fa-heart"></i></div>
                    </div>
                    <div class="offer-info">
                        <h3><?=htmlspecialchars($h['nom_hotel'])?></h3>
                        <p style="color:#666;"><?=htmlspecialchars($h['ville_hotel'])?></p>
                        <span style="background:#007bff;color:white;padding:10px 18px;border-radius:12px;font-weight:700;"><?=number_format(8.3 + mt_rand(0,15)/10, 1)?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
                <div class="grid">
            <?php foreach($offres as $h): $img = $hotelPhotos[array_rand($hotelPhotos)]; ?>
                <div class="offer-card" onclick="location.href='?hotel=<?=htmlspecialchars($h['code_hotel'])?>'">
                    <div class="offer-img" style="background-image:url('<?=$img?>')">
                        <div class="heart"><i class="far fa-heart"></i></div>
                    </div>
                    <div class="offer-info">
                        <h3><?=htmlspecialchars($h['nom_hotel'])?></h3>
                        <p style="color:#666;"><?=htmlspecialchars($h['ville_hotel'])?></p>
                        <span style="background:#007bff;color:white;padding:10px 18px;border-radius:12px;font-weight:700;"><?=number_format(8.3 + mt_rand(0,15)/10, 1)?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
<!-- FOOTER MAGNIFIQUE -->
<footer>
    <div class="footer-grid">
        <div class="footer-col">
            <h3>soutra</h3>
            <p>Réservez les meilleurs hôtels en un clic. Luxe, confort et simplicité.</p>
            <div class="social-links">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>
        <div class="footer-col">
            <h3>Liens rapides</h3>
            <ul>
                <li><a href="#">À propos</a></li>
                <li><a href="#">Contact</a></li>
                <li><a href="#">Conditions d'utilisation</a></li>
                <li><a href="#">Politique de confidentialité</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h3>Support</h3>
            <ul>
                <li><a href="#">Centre d'aide</a></li>
                <li><a href="#">Nous contacter</a></li>
                <li><a href="#">FAQ</a></li>
                <li><a href="#">Annulation</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h3>Contact</h3>
            <ul>
                <li><a href="#">+225 07 77 77 77 77</a></li>
                <li><a href="#">contact@soutra.ci</a></li>
                <li><a href="#">Abidjan, Côte d'Ivoire</a></li>
            </ul>
        </div>
    </div>
    <div class="copyright">
        © 2025 soutra. Tous droits réservés. Créé avec ❤️ en Côte d'Ivoire
    </div>
</footer>
</body>
</html>