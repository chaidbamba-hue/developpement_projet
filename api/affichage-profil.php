<?php
date_default_timezone_set('UTC');
require '../database/database.php';

if(isset($_GET['matricule']))
	{ 

$req = $con->prepare('SELECT id,email,telephone,nom_prenom,login,mdp,matricule,etat,role,societe_id,type,date_saisie,TO_BASE64(photo) AS photo64 FROM utilisateur WHERE matricule=:matricule');
        $req->bindParam(':matricule', $_GET['matricule']);
        $req->execute();
        $sol = $req->fetchAll();

        print_r(json_encode($sol));
      
}

 ?>
