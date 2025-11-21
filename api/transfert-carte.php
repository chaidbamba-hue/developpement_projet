<?php
date_default_timezone_set('UTC'); 
require '../database/database.php';

$json = file_get_contents('php://input');
 
     // decoding the received JSON and store into $obj variable.
     $obj = json_decode($json,true);
     
     // variable
    $matricule = $obj['matricule'];   
    $montant = $obj['montant'];
    $destinataire = $obj['destinataire'];


      // generateur de code 
        $char = '0123456789';
        $code = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= $char[rand() % strlen($char)];
        }

        $numero = "TRANSFERT".$code.time();

 

  if($matricule!=$destinataire)
            {
            
            
            $req = $con->prepare('SELECT * FROM carte WHERE numero_carte=:numero_carte');
        $req->bindParam(':numero_carte', $matricule);
        $req->execute();
        $verifExp = $req->fetchAll();

            
            $req = $con->prepare('SELECT * FROM carte WHERE numero_carte=:numero_carte');
        $req->bindParam(':numero_carte', $destinataire);
        $req->execute();
        $verifDest = $req->fetchAll();

            $MontantSaisi = $montant;

            $dernier = substr($MontantSaisi, -1);
            if($dernier==0 OR $dernier==5){

                if(!empty($verifExp)){

                    if($verifExp[0]['etat_carte']=="Actif"){

                        if($verifExp[0]['solde_carte']>=$MontantSaisi){

                            if (!empty($verifDest)) {

                                if($verifDest[0]['etat_carte']=="Actif"){

                                // debut du code reel
     
                                    $numero = $numero;
                                    $date = date('Y-m-d');
                                    $heure = date('H:i:s');
                                    $montant = $MontantSaisi;
                                    $type = "Transfert";
                                    $objet = ""; 
                                    $frais= 0;
                                    $montant_total= $MontantSaisi;
                                    $carte_expediteur = $matricule;
                                    $carte_destinataire = $destinataire;
                                    $facture_id = "";
                                    $mode_reglement= "";
                                    $numero_reglement= "";
                                    $reference_reglement = ''; 
                                    $societe_id = '';
                                    $valider_par= $matricule;
                                    $etat = "Succes";

                //

                                    // ajout transaction
                                    $req = $con->prepare('INSERT INTO transaction VALUES(:numero,:date,:heure,:montant,:frais,:montant_total,:type,:objet,:carte_expediteur,:carte_destinataire,:facture_id,:mode_reglement,:numero_reglement,:reference_reglement,:valider_par,:societe_id,:etat)');
                                    $req->bindParam(':numero', $numero);
                                    $req->bindParam(':date', $date);
                                    $req->bindParam(':heure', $heure);
                                    $req->bindParam(':montant', $montant);
                                    $req->bindParam(':frais', $frais);
                                    $req->bindParam(':montant_total', $montant_total);
                                    $req->bindParam(':type', $type);
                                    $req->bindParam(':objet', $objet);
                                    $req->bindParam(':facture_id', $facture_id);
                                    $req->bindParam(':carte_expediteur', $carte_expediteur);
                                    $req->bindParam(':carte_destinataire', $carte_destinataire);
                                    $req->bindParam(':mode_reglement', $mode_reglement);
                                    $req->bindParam(':numero_reglement', $numero_reglement); 
                                    $req->bindParam(':reference_reglement', $reference_reglement);
                                    $req->bindParam(':societe_id', $societe_id);
                                    $req->bindParam(':valider_par', $valider_par);           
                                    $req->bindParam(':etat', $etat);
                                    $exec = $req->execute();

                                    if ($exec== true) {
                                        
                                        try {

                                            $con->beginTransaction();
                                            $req = $con->prepare('UPDATE carte SET solde_carte=solde_carte -:montant WHERE numero_carte=:expediteur');
                                            $req->bindParam(':expediteur', $carte_expediteur);
                                            $req->bindParam(':montant', $montant_total);
                                            $req->execute();

                                            $req = $con->prepare('UPDATE carte SET solde_carte=solde_carte + :montant WHERE numero_carte=:destinataire');
                                            $req->bindParam(':destinataire', $carte_destinataire);
                                            $req->bindParam(':montant', $montant_total);
                                            $req->execute();

                                            $exec = $con->commit();
                                        } catch (Exception $e) {

                                            $exec = $con->rollback();

                                            echo "Erreur" . $e->getMessage();
                                            echo "Erreur" . $e->getCode();
                                        }


                                        
                                        $message ="Transfert effectué avec succes ! ";
                                    } else {

                                        $message ="Echec du transfert. ! ";
                                    }

// fin du code reel


                                } else {
                                    $message ="Le carte du destinataire a été desactivée ! ";
                                    
                                }

                            } else {
                                $message ="Le carte du destinataire n'existe pas.! ";
                                
                            }

                        }else{
                            $message ="Le solde de votre carte est insuffisant. ! ";
                        }

                    }else{
                        $message ="Votre carte a été desactivée. ! ";
                    }

                }else{
                    $message ="Votre carte n'existe pas. ! ";
                }
            }else{
                $message="Le montant saisi ne respecte pas les normes car il se termine par  ".$dernier.". Veuillez ressaisir un autre montant.";
            }

            }else{
                $message ="La carte destinataire est la meme que votre carte. Veuillez saisir des cartes differents ! ";
            }


print_r(json_encode($message));



 ?>