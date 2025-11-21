<?php
date_default_timezone_set('UTC');
require '../database/database.php';


$json = file_get_contents('php://input');
 
     // decoding the received JSON and store into $obj variable.
     $obj = json_decode($json,true);
     
     // variable
    $matricule = $obj['matricule'];   
    $login = $obj['login'];
    $mdp = $obj['mdp'];
    $email = $obj['email'];
    $telephone = $obj['telephone'];
    $photo = base64_decode($obj['photo']);
    $TypePhoto = $obj['TypePhoto'];

		// recherche pour voir si le login existe 
	$req = $con->prepare('SELECT * FROM utilisateur WHERE matricule=:matricule');
	$req->bindParam(':matricule', $matricule);
	$req->execute();
	$verifLogin = $req->fetchAll();

	if(empty($verifLogin)){
		$message="Aucune donnée pour ce profil.";
	}else{
		$req = $con->prepare('UPDATE utilisateur SET login=:login,mdp=:mdp,email=:email,telephone=:telephone,photo=:photo,type=:type WHERE matricule=:matricule');
		$req->bindParam(':login', $login);
		$req->bindParam(':mdp', $mdp);
		$req->bindParam(':email', $email);
		$req->bindParam(':telephone', $telephone);
		$req->bindParam(':matricule', $matricule);
		$req->bindParam(':photo', $photo);
		$req->bindParam(':type', $TypePhoto);
		$sol = $req->execute();

		$message="Félicitation ! Votre profil a été mis à jour avec succès.";
	}


	
	print_r(json_encode($message));


?>
