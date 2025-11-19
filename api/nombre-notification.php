<?php
date_default_timezone_set('UTC');
require '../database/database.php';


if(isset($_GET['id']))
	{ 
$req = $con->prepare('SELECT * FROM notification,utilisateur,vue WHERE utilisateur.id=vue.user AND vue.notification=notification.id AND vue.lecture="Non" and vue.affichage="Oui" AND vue.user=:u');
$req->bindParam(':u', $_GET['id']);
$req->execute();
$sol = $req->rowCount();

 print_r(json_encode($sol));

 }     

?>