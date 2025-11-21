<?php
date_default_timezone_set('UTC');
require '../database/database.php';


if(isset($_GET['matricule']))
    { 
$req = $con->prepare('SELECT facture.*,
    COALESCE(facture.montant_ttc,0) AS montant,
    COALESCE(SUM(transaction.montant_transaction), 0) AS avance,
    COALESCE(facture.montant_ttc - SUM(transaction.montant_total), 0) AS reste,
    COALESCE(SUM(transaction.montant_total), 0) AS encaisse
FROM 
    facture
LEFT JOIN 
    transaction ON facture.numero_facture = transaction.facture_id
WHERE
    facture.utilisateur_id=:matricule
GROUP BY 
    facture.numero_facture');
$req->bindParam(':matricule', $_GET['matricule']);
$req->execute();
$sol = $req->fetchAll();

 print_r(json_encode($sol));

 }     

?>