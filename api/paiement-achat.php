<?php
date_default_timezone_set('UTC');
require '../database/database.php';

     // decoding the received JSON and store into $obj variable.
     $json = file_get_contents('php://input');
     $obj = json_decode($json,true);
     
     // variable
    $matricule = $obj['matricule'];  
    $carte = $obj['carte'];
    $boutique = $obj['boutique'];
    $montant = $obj['montant'];


 // Assurez-vous que $montant est une chaîne pour pouvoir utiliser substr
    $MontantSaisi = (string)$montant;

    // Obtenir le dernier caractère
    $dernier = substr($MontantSaisi, -1);

    if ($dernier == '0' || $dernier == '5') {
           // le solde de la carte

            $req = $con->prepare('SELECT * FROM carte WHERE numero_carte=:matricule');
        $req->bindParam(':matricule', $carte);
        $req->execute();
        $solde  = $req->fetchAll();

            if(!empty($solde)){

                if($solde[0]['etat_carte']=="Actif"){

                      if($solde[0]['solde_carte'] >= $obj['montant'])
                {

                    // verification de l'existence de la carte receptrice du partenaire
                     $req = $con->prepare('SELECT * FROM utilisateur WHERE matricule=:numero_carte');
        $req->bindParam(':numero_carte', $boutique);
        $req->execute();
        $verifPartenaire = $req->fetchAll();

                     if (!empty($verifPartenaire)) {
                         
                    $numero = 'TRANSACTION'.date('dmYHis');
                    $date = date('Y-m-d');
                    $heure = date('H:i:s');
                    $montant = $obj['montant'];
                    $type = "Paiement";
                    $objet = "Acompte";
                    $periode = "";
                    $frais = 0;
                    $montant_total = $obj['montant']; 
                    $expediteur = $obj['carte'];
                    $destinataire = $obj['boutique'];
                    $mode_reglement = "Carte";
                    $numero_reglement = "";
                    $reference_reglement = ''; 
                    $motif = ''; 
                    $valider_par = $obj['boutique'];
                    $etat = "Succes";

                // insertion de la transaction

                    $req = $con->prepare('INSERT INTO transaction VALUES (:numero, :date,:heure,:montant,:frais,:montant_total,:type,:objet,:periode,:expediteur,:destinataire,:mode_reglement,:numero_reglement,:reference_reglement,:motif,:valider_par,:etat)');
                    $req->bindParam(':numero', $numero);
                    $req->bindParam(':date', $date);
                    $req->bindParam(':heure', $heure);
                    $req->bindParam(':montant', $montant);
                    $req->bindParam(':frais', $frais);
                    $req->bindParam(':montant_total', $montant_total);
                    $req->bindParam(':type', $type);
                    $req->bindParam(':objet', $objet);
                    $req->bindParam(':periode', $periode);
                    $req->bindParam(':expediteur', $expediteur);
                    $req->bindParam(':destinataire', $destinataire);
                    $req->bindParam(':mode_reglement', $mode_reglement);
                    $req->bindParam(':numero_reglement', $numero_reglement);
                    $req->bindParam(':reference_reglement', $reference_reglement);
                    $req->bindParam(':motif', $motif);
                    $req->bindParam(':valider_par', $valider_par);
                    $req->bindParam(':etat', $etat);
                    $trans = $req->execute();

                    if($trans ==true) {

                        // retrait
                        try {

                        	$con->beginTransaction();
                        	$req = $con->prepare('UPDATE carte SET solde_carte=solde_carte -:montant WHERE numero_carte=:expediteur');
                        	$req->bindParam(':expediteur', $carte);
                        	$req->bindParam(':montant', $montant_total);
                        	$req->execute();

                        	$req = $con->prepare('UPDATE utilisateur SET solde=solde + :montant WHERE matricule=:destinataire');
                        	$req->bindParam(':destinataire', $boutique);
                        	$req->bindParam(':montant', $montant_total);
                        	$req->execute();

                        	$RetraitSoldeCarte = $con->commit();

                        } catch (Exception $e) {

                        	$RetraitSoldeCarte = $con->rollback();

                        	echo "Erreur" . $e->getMessage();
                        	echo "Erreur" . $e->getCode();
                        }


                        if($RetraitSoldeCarte==true){ 

                        $message ="Paiement effectué avec succes.";
                
                           }else{
       
                   $message ="Echec du debit des soldes.";

                    }
                    }else{
       
                       $message ="Echec de la transaction.";

                    }

                    }else{
                        $message ="La boutique ne possède pas de compte donc elle ne peut recevoir le montant de la commande !";
                     }

                }else{

                $message = "Le solde de votre compte est insuffisant pour effectuer le paiement. Merci";
                }


                }else{

               $message = "Ce compte a été desactivé. Veuillez le signaler au superviseur au +225 0709107849 ! Merci.";
                }
                }else{

                $message = "Operation de paiement impossible sans le solde et le total. Merci";

            }
             } else {
        $message = "Le montant saisi ne respecte pas les normes car il se termine par " . $dernier . ". Veuillez ressaisir un autre montant.";
    }

            

	
	print_r(json_encode($message));

?>