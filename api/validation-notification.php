<?php
date_default_timezone_set('UTC');
require '../database/database.php';


if(isset($_GET['id']) and isset($_GET['user']))
	{ 
		//voir si on dea lu
		$req = $con->prepare('SELECT * FROM vue WHERE user=:user AND notification=:id AND lecture=1');
$req->bindParam(':user', $_GET['user']);
$req->bindParam(':id', $_GET['id']);
$req->execute();
$voirNot = $req->fetchAll();
 if (!empty($voirNot)) {
 	$message="Désolé ! Vous avez déjà lu cette notification";
 }else{

 	$req = $con->prepare('UPDATE vue SET lecture=1 WHERE user=:user AND notification=:id');
$req->bindParam(':user', $_GET['user']);
$req->bindParam(':id', $_GET['id']);
$sol = $req->execute();
   if($sol==true) {
         	$message="Félicitation ! Vous avez lu la notification";
         }else{
         	$message="Désolé ! La lecture de la notification a été rejétée";
   }

 }



 print_r(json_encode($message));

 }     

?>