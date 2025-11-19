<?php
date_default_timezone_set('UTC');
require '../database/database.php';


$out = "";

     $json = file_get_contents('php://input');
     $obj = json_decode($json,true);

 

     $login = $obj['login'];   
     $mdp = $obj['mdp'];

$req = $con->prepare('SELECT id,email,telephone,nom_prenom,login,mdp,matricule,etat,role,societe_id,type,date_saisie,TO_BASE64(photo) AS photo64 FROM utilisateur WHERE (login=:login OR telephone=:login OR matricule=:login) AND mdp=:mdp AND etat="Actif"');
        $req->bindParam(':login', $login);
        $req->bindParam(':mdp', $mdp);
        $req->execute();
        $sol = $req->fetchAll();
        if(!empty($sol))
        {
           $out = $sol;
        }else{
            $out = "Nom d'utilisateur et mot de passe incorrectes";
        }

   print_r(json_encode($out));
       


?>