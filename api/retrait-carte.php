<?php
date_default_timezone_set('UTC'); 
require '../database/database.php';

$json = file_get_contents('php://input');

// Décoder le JSON reçu et le stocker dans la variable $obj.
$obj = json_decode($json, true);

// Variables
$matricule = $obj['matricule'];   
$montant = $obj['montant'];
$telephone = $obj['telephone'];

$utilisateur = $obj['utilisateur'];


// Générateur de code 
$char = '0123456789';
$code = '';
for ($i = 0; $i < 12; $i++) {
    $code .= $char[rand() % strlen($char)];
}

$IDTransaction = "RETRAIT".$code.time();
// Vérification de la carte
$req = $con->prepare('SELECT * FROM carte WHERE numero_carte = :matricule');
$req->bindParam(':matricule', $matricule);
$req->execute();
$verifDemandeur = $req->fetchAll();

// Assurez-vous que $montant est une chaîne pour pouvoir utiliser substr
$MontantSaisi = (string)$montant;

// Obtenir le dernier caractère
$dernier = substr($MontantSaisi, -1);

if ($dernier == '0' || $dernier == '5') {
    if (!empty($verifDemandeur)) {
        if ($verifDemandeur[0]['etat_carte'] == "Actif") {
            if ($verifDemandeur[0]['solde_carte'] >= $MontantSaisi) {
                // Si le montant est supérieur ou égal à 1000 F CFA
                if ($montant >= 1000) {

                    $numero = $IDTransaction;
                    $date = date('Y-m-d');
                    $heure = date('H:i:s');
                    $montant = $MontantSaisi;
                    $type = "Retrait";
                    $objet = ""; 
                    $frais= 0;
                    $montant_total= $MontantSaisi;
                    $carte_expediteur = $matricule;
                    $carte_destinataire = "";
                    $facture_id = "";
                    $mode_reglement= "Wave";
                    $numero_reglement= "+225".$telephone;
                    $reference_reglement = ''; 
                    $societe_id = '';
                    $valider_par= $utilisateur;
                    $etat = "Succes";

                    // Ajouter la transaction
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

                    if ($exec==true) {

                        $req = $con->prepare('UPDATE carte SET solde_carte = solde_carte - :montant WHERE numero_carte = :matricule');
                        $req->bindParam(':matricule', $matricule);
                        $req->bindParam(':montant', $montant_total);
                        $RetraitSoldeCarte = $req->execute();

                        if ($RetraitSoldeCarte==true) {
                            
                            // API Payout
                            $curl = curl_init();

                            $idempotencyKey = uniqid('idem_', true); 

                            curl_setopt_array($curl, [
                              CURLOPT_URL => "https://api.wave.com/v1/payout",
                              CURLOPT_RETURNTRANSFER => true,
                              CURLOPT_ENCODING => "",
                              CURLOPT_MAXREDIRS => 10,
                              CURLOPT_TIMEOUT => 0,
                              CURLOPT_FOLLOWLOCATION => true,
                              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                              CURLOPT_CUSTOMREQUEST => "POST",
                              CURLOPT_POSTFIELDS => json_encode([
                                "client_reference"=> $matricule,
                                "currency" => "XOF",
                                "receive_amount" => $MontantSaisi,
                                "national_id"=> "225",
                                "mobile" => "+225".$telephone,
                                "name" => isset($verifDemandeur[0]['nom_prenom_carte']) && !empty($verifDemandeur[0]['nom_prenom_carte'])
                                ? $verifDemandeur[0]['nom_prenom_carte']
                                : "Client non identifié",
                                "payment_reason"=> "Retrait"
                            ]),
                              CURLOPT_HTTPHEADER => [
                                "Authorization: Bearer wave_ci_prod_KeFGhwmG2xBwU0lKNvmfsaf24tJ8vuqn2GNFhwQv-zPMSW8N76v8aKxrWi7ccwpvWg6bzwNzekhwC0e-6qDY6jVW20w1RgVojg",
                                "Content-Type: application/json",
                                "Idempotency-Key: $idempotencyKey"
                            ],
                        ]);
                            $response = curl_exec($curl);

                            $responseData = json_decode($response, true);

                            if ($responseData['error']==200) {

                                $message = "Succès. Votre demande de retrait de ".$montant." F CFA a reussi. Vous recevrez le montant dans votre compte Wave. Nous vous remercions.";

                            }else{
                                // Error message
                                $message = "Echec. Désolé votre transaction a rencontré un problème interne et sera traité manuellement par notre equipe";

                                 // Modifier la transaction
                                $NewObjet = $responseData['message'];
                    $req = $con->prepare('UPDATE transaction SET objet_transaction=:objet,reference_reglement=:reglement, etat_transaction="En cours" WHERE numero_transaction=:numero');
                    $req->bindParam(':numero', $IDTransaction);
                    $req->bindParam(':objet', $NewObjet);           
                    $req->bindParam(':reglement', $idempotencyKey);
                    $exec = $req->execute();

                            }
                            curl_close($curl);
                            // API WAVE --fin--
                        } else {
                            $message = "Échec du retrait du solde de votre carte.";
                        }
                    } else {
                        $message = "Échec de la demande de retrait. Veuillez réessayer. Nous vous remercions.";
                    }
                } else {
                    $message = "Impossible car vous devez saisir un montant supérieur ou égal à 1 000 F CFA.";
                }
            } else {
                $message = "Ce montant n'est pas disponible sur votre carte.";
            }
        } else {
            $message = "Votre carte a été desactivée.";
        }
    } 
} else {
    $message = "Le montant saisi ne respecte pas les normes car il se termine par ".$dernier.". Veuillez ressaisir un autre montant.";
}

print_r(json_encode($message));
?>
