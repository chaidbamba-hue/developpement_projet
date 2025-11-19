<?php
date_default_timezone_set('UTC'); 
require '../database/database.php';

$json = file_get_contents('php://input');
 
     // decoding the received JSON and store into $obj variable.
     $obj = json_decode($json,true);
     
     // variable
    $code = $obj['code'];
    $matricule = $obj['matricule']; 
    $titre = $obj['titre'];  
    $montant = $obj['montant'];
    $telephone = $obj['telephone'];
    $client = $obj['client'];
    $utilisateur = $obj['utilisateur'];

      // generateur de code 

 
        $req = $con->prepare('SELECT * FROM carte WHERE numero_carte=:numero_carte');
        $req->bindParam(':numero_carte', $matricule);
        $req->execute();
        $verifCarte = $req->fetchAll();

        if(!empty($verifCarte)){

            if($verifCarte[0]['etat_carte']=="Actif"){

                $req = $con->prepare('SELECT * FROM facture WHERE numero_facture=:numero_facture');
        $req->bindParam(':numero_facture', $code);
        $req->execute();
        $verifFacture = $req->fetchAll();

                if(empty($verifFacture)){

            $MontantSaisi = $montant;

            $dernier = substr($MontantSaisi, -1);
            if($dernier==0 OR $dernier==5){

     
                $numero_facture = $code;
                $titre_facture = $titre;
                $date_facture = date("Y-m-d");
                $montant = $MontantSaisi;
                $commande_json = "";
                $client_identite = $client;
                $client_telephone = $telephone;
                $carte_id = $matricule;
                $utilisateur_id = $utilisateur;
                $societe_id = "";
                $etat_facture = "Actif";

        $req = $con->prepare('INSERT INTO facture VALUES (:numero_facture,:titre_facture, :date_facture, :montant, :commande_json,:client_identite,:client_telephone, :carte_id,:utilisateur_id,:societe_id, :etat_facture)');
        $req->bindParam(':numero_facture', $numero_facture);
        $req->bindParam(':titre_facture', $titre_facture);
        $req->bindParam(':date_facture', $date_facture);
        $req->bindParam(':montant', $montant);
        $req->bindParam(':commande_json', $commande_json);
        $req->bindParam(':client_identite', $client_identite);
        $req->bindParam(':client_telephone', $client_telephone);
        $req->bindParam(':carte_id', $carte_id);
        $req->bindParam(':utilisateur_id', $utilisateur_id);
        $req->bindParam(':societe_id', $societe_id);
        $req->bindParam(':etat_facture', $etat_facture);
        $exec = $req->execute();

                           if ($exec== true) {
                                        
                                $message ="Facture créee avec succès ! ";
                            } else {

                                $message ="Echec de la création de la facture. ! ";
                            }

                    
            }else{
                $message="Le montant saisi ne respecte pas les normes car il se termine par  ".$dernier.". Veuillez ressaisir un autre montant.";
            }
            }else{
                $numero_facture = $code;
                $titre_facture = $titre;
                $date_facture = $verifFacture[0]['date_facture'];
                $montant = $obj['montant'];
                $commande_json = $verifFacture[0]['commande_json'];
                $client_identite = $client;
                $client_telephone = $telephone;
                $carte_id = $matricule;
                $utilisateur_id = $utilisateur;
                $societe_id = "";
                $etat_facture = "Actif";

        $req = $con->prepare('UPDATE facture SET titre_facture=:titre_facture, date_facture=:date_facture, montant=:montant, commande_json=:commande_json,client_identite=:client_identite,client_telephone=:client_telephone, carte_id=:carte_id,utilisateur_id=:utilisateur_id,societe_id=:societe_id, etat_facture=:etat_facture WHERE numero_facture=:numero_facture');
        $req->bindParam(':numero_facture', $numero_facture);
        $req->bindParam(':titre_facture', $titre_facture);
        $req->bindParam(':date_facture', $date_facture);
        $req->bindParam(':montant', $montant);
        $req->bindParam(':commande_json', $commande_json);
        $req->bindParam(':client_identite', $client_identite);
        $req->bindParam(':client_telephone', $client_telephone);
        $req->bindParam(':carte_id', $carte_id);
        $req->bindParam(':utilisateur_id', $utilisateur_id);
        $req->bindParam(':societe_id', $societe_id);
        $req->bindParam(':etat_facture', $etat_facture);
        $exec = $req->execute();

                           if ($exec== true) {
                                        
                                $message ="Facture modifiée avec succès ! ";
                            } else {

                                $message ="Echec de la modification de la facture. ! ";
                            }
             }
            }else{
                        $message ="Votre carte a été desactivée. ! ";
                    }
            }else{
                    $message ="Votre carte n'existe pas. ! ";
             }




print_r(json_encode($message));



 ?>