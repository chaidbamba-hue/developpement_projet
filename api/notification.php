<?php
date_default_timezone_set('UTC');
require '../database/database.php';


if(isset($_GET['id']))
    { 
$req = $con->prepare('SELECT utilisateur.id,utilisateur.nom_prenom,utilisateur.type,TO_BASE64(utilisateur.photo) AS photo64,notification.*,vue.* FROM notification,utilisateur,vue WHERE notification.user=utilisateur.id and vue.notification=notification.id AND vue.user=:u AND vue.affichage="Oui" and vue.lecture="Non" ORDER BY notification.id DESC');
$req->bindParam(':u', $_GET['id']);
$req->execute();
$sol = $req->fetchAll();

 print_r(json_encode($sol));

 }     

?>