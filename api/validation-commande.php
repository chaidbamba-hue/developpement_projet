<?php
date_default_timezone_set('UTC'); 
require '../database/database.php';

$json = file_get_contents('php://input');
 
     // decoding the received JSON and store into $obj variable.
     $obj = json_decode($json,true);

 // variable
    $matricule = $obj['matricule'];   
    $reference = $obj['reference'];

            // code generation
             $char = '0123456789';
        $code = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= $char[rand() % strlen($char)];
        }

        $CodeOrd = "COMMANDE".$code.time();
        

        // verification de la carte
        // Carte de l'Beneficiaire

            $req = $con->prepare('SELECT * FROM utilisateur WHERE matricule =:matricule');
        $req->bindParam(':matricule', $matricule);
        $req->execute();
        $verifCarte = $req->fetchAll();

         $req = $con->prepare('SELECT * FROM produit WHERE code=:code');
        $req->bindParam(':code', $reference);
        $req->execute();
        $rechPrixMed = $req->fetchAll();



         if(!empty($verifCarte) AND !empty($rechPrixMed))
         {
             if($verifCarte[0]['etat']!="Actif")
             {
                 $message = "Votre compte a été desactivé. Contactez le superviseur au +225 0709107849 ou info@adores.tech ! Merci.";
             }else{
                if($verifCarte[0]['solde'] < $rechPrixMed[0]['prix'])
                {
                    $message = "Le solde de votre compte est inférieur au prix du produit/prestation donc vous ne pouvez pas effectuer de commandes. Veuillez recharger votre compte.";

                }else{
                     
                        // verification de l'existance du produit dans la liste
            $req = $con->prepare('SELECT * FROM commande WHERE code=:code and beneficiaire=:beneficiaire and (etat="Commander" OR etat="En cours")');
        $req->bindParam(':code', $reference);
        $req->bindParam(':beneficiaire', $matricule);
        $req->execute();
        $VerificationCommande = $req->fetchAll();

            if(!empty($VerificationCommande)){
               
               $message = "Vous avez déjà commandé ce produit mais n'a pas encore été validé par notre service alors veuillez patienter pour la validation ou contactez le service client au +225 0709107849 (SMS, Appel, WhatsApp) ou info@adores.tech.";

            }else{
                

                    // ajout d'une commande
                     $DateCommande = date('Y-m-d');
                     $HeureCommande = date('H:i:s');
              $req = $con->prepare('INSERT INTO commande VALUES (:numero,:code,:libelle,:prix,"1",:prix,:date,:heure,:beneficiaire,:agent,"","Commander")');
        $req->bindParam(':numero', $CodeOrd);
        $req->bindParam(':code', $rechPrixMed[0]['code']);
        $req->bindParam(':libelle', $rechPrixMed[0]['libelle']);
        $req->bindParam(':prix', $rechPrixMed[0]['prix']);
        $req->bindParam(':date', $DateCommande);
        $req->bindParam(':heure', $HeureCommande);
        $req->bindParam(':beneficiaire', $matricule);
        $req->bindParam(':agent', $rechPrixMed[0]['structure']);

        $AjouterCommande = $req->execute();

                if($AjouterCommande==true){
            

                    $message = "Commande effectuée avec succès. Vous serez contacté par notre service client pour la validation officielle de votre commande dans un delai maximum de 72 Heures.";

                }else{
                    $message = "Echec de la commande. Veuillez reessayer !";
                }
                
            }

                    }
               

         }
         }else{
             $message = "Impossible de mener cette operation sans certaines données. Veuillez reessayer !";
         }

          
           
 print_r(json_encode($message));
            
         
       

 ?>