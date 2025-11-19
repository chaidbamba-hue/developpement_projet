 <?php
date_default_timezone_set('UTC');
require '../database/database.php';

     // decoding the received JSON and store into $obj variable.
     $json = file_get_contents('php://input');
     $obj = json_decode($json,true);

     // variable
     $matricule = $obj['matricule'];
     $MontantEnvoye = $obj['MontantEnvoye'];
     $MontantRecu = $obj['MontantRecu'];
     $utilisateur = $obj['utilisateur'];


            // Recherche du carte

        $req = $con->prepare('SELECT * FROM carte WHERE numero_carte=:matricule');
        $req->bindParam(':matricule', $obj['matricule']);
        $req->execute();
        $sol = $req->fetchAll();
            
            if (!empty($sol)) 
            {
         
         // Recherche de la carte si elle est active
         if ($sol[0]['etat_carte']=="Actif") 
            {
                        
                                    // Rechargement de la carte du beneficiaire
                                    $numero = "RECHARGEMENT".date("dmYHis");
                                    $date = date('Y-m-d');
                                    $heure = date('H:i:s');
                                    $montant = $MontantEnvoye;
                                    $frais = 5;
                                    $montant_total = $MontantRecu;
                                    $type = "Rechargement";
                                    $objet = ""; 
                                    $carte_expediteur = $matricule;
                                    $carte_destinataire = $matricule;
                                    $mode_reglement = "Wave";
                                    $numero_reglement = "";
                                    $reference_reglement = ''; 
                                    $societe_id = ''; 
                                    $facture_id= '';
                                    $valider_par = $obj['matricule'];
                                    $etat = "En cours";

                                  // insertion de la transaction
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

                                    if($exec ==true)
                                    {
                                         // API WAVE
                                         $curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_URL => "https://api.wave.com/v1/checkout/sessions",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => json_encode([
    "amount" => $sol[0]['montant'],
    "currency" => "XOF",
    "error_url" => "https://epencia.net/soutra/api/echec.php/$numero",
    "success_url" => "https://epencia.net/soutra/api/succes.php/$numero"
    //"error_url" => "https://epencia.net/soutra/transaction/echec/$numero",
    //"success_url" => "https://epencia.net/soutra/transaction/succes/$numero"
  ]),
  CURLOPT_HTTPHEADER => [
    "Authorization: Bearer wave_ci_prod_KeFGhwmG2xBwU0lKNvmfsaf24tJ8vuqn2GNFhwQv-zPMSW8N76v8aKxrWi7ccwpvWg6bzwNzekhwC0e-6qDY6jVW20w1RgVojg",
    "Content-Type: application/json"
  ],
]);
$response = curl_exec($curl);

$responseData = json_decode($response, true);

if (!empty($responseData['wave_launch_url'])) {
   
    ?>
            <script type='text/javascript'>document.location.replace('<?php echo $responseData['wave_launch_url']; ?>');</script>
            <?php
    exit();
}else{
            ?>
            <script type='text/javascript'>alert('Echec 1');</script>
            <?php 
}
curl_close($curl);
                                         // message de succes du rechargement
                                         $message = "Votre demande de rechargement de " . " " . $montant . " F CFA est en cours de traitement. Veuillez patienter au maximum 24 H";
                                    }else 
                                    {
                                        $message ="Echec de la transaction du rechargement du carte.";
                                    }


            } else {
                $message = "Ce carte a été desactivé. Veuillez le signaler au superviseur au +225 0709107849 ! Merci.";
            }

            } else {
                $message = "Ce carte n'existe. Veuillez saisir un carte existant.";
            }

    print_r(json_encode($message));     
        
 ?>