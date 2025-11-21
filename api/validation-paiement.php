<?php
date_default_timezone_set('UTC');
require '../database/database.php';

     // decoding the received JSON and store into $obj variable.
     $json = file_get_contents('php://input');
     $obj = json_decode($json,true);
     
     // variable
    $matricule = $obj['matricule'];  
    $numero = $obj['numero'];



    	 // verification de l'existence de la carte receptrice du partenaire
                     $req = $con->prepare('SELECT * FROM transaction WHERE numero=:numero and etat="En cours"');
        $req->bindParam(':numero', $obj['numero']);
        $req->execute();
        $verifTransaction = $req->fetchAll();

                     if (!empty($verifTransaction)) {

              // le solde de la carte

            $req = $con->prepare('SELECT * FROM utilisateur WHERE matricule=:matricule');
        $req->bindParam(':matricule', $matricule);
        $req->execute();
        $solde  = $req->fetchAll();

            if(!empty($solde)){

                if($solde[0]['etat']=="Actif"){

                      if($solde[0]['solde'] >= $verifTransaction[0]['montant_total'])
                {
                         
                // insertion de la transaction

                    $req = $con->prepare('UPDATE transaction SET etat="Succes" WHERE numero=:numero');
                    $req->bindParam(':numero', $numero);
                    $trans = $req->execute();

                    if($trans ==true) {

                        	$req = $con->prepare('UPDATE utilisateur SET solde=solde + :montant WHERE matricule=:destinataire');
                        	$req->bindParam(':destinataire', $obj['matricule']);
                        	$req->bindParam(':montant', $verifTransaction[0]['montant_total']);
                        	$req->execute();

                        if($RetraitSoldeCarte==true){ 

                        $message ="Paiement effectué avec succes.";
                
                           }else{
       
                   $message ="Echec du debit des soldes.";

                    }
                    }else{
       
                       $message ="Echec de la transaction.";

                    }

                    }else{
                        $message = "Le solde de votre compte est insuffisant pour effectuer le paiement. Merci";
                     }

                }else{

                
                $message = "Ce compte a été desactivé. Veuillez le signaler au superviseur au +225 0709107849 ! Merci.";

                
                }


                }else{

               $message = "Ce compte n'existe pas.";
                }
                }else{

                $message = "Cette transaction n'existe pas.";

            }

            

	
	print_r(json_encode($message));

?>