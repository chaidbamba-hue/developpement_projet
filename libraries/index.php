<?php 
require "phpqrcode/phpqrcode.php";
// créer un fichier
QRcode::png('http://localhost/adores_pro/badge/profil/003', 'test.png');
// Afficher directement le qr code (dans le navigateur)
QRcode::png('eric');

 ?>