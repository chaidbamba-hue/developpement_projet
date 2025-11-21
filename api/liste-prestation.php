<?php
date_default_timezone_set('UTC');
require '../database/database.php';


if(isset($_GET['societe']))
	{ 
$req = $con->prepare('SELECT * FROM prestation WHERE societe_id=:societe and etat="Actif"');
$req->bindParam(':societe', $_GET['societe']);
$req->execute();
$sol = $req->fetchAll();

 print_r(json_encode($sol));

 }     

?>