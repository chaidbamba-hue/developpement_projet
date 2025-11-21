<?php
date_default_timezone_set('UTC');
require '../database/database.php';


if(isset($_GET['id']) and isset($_GET['user']))
   { 


   $req = $con->prepare('UPDATE vue SET lecture="Oui" WHERE user=:user AND notification=:id');
$req->bindParam(':user', $_GET['user']);
$req->bindParam(':id', $_GET['id']);
$sol = $req->execute();
   if($sol==true) {
            $message="Félicitation ! Vous avez supprimé cette notification";
         }else{
            $message="Désolé ! La suppression de la notification a échouée";
   }




 print_r(json_encode($message));

 }     

?>