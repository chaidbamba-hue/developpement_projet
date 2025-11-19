<?php
date_default_timezone_set('UTC');
require '../database/database.php';


if(isset($_GET['matricule']) and isset($_GET['dd']) and isset($_GET['df']))
    { 
$req = $con->prepare('SELECT facture.*,
    COALESCE(facture.montant,0) AS montant,
    COALESCE(SUM(transaction.montant_transaction), 0) AS avance,
    COALESCE(facture.montant - SUM(transaction.montant_total), 0) AS reste,
    COALESCE(SUM(transaction.montant_total), 0) AS encaisse
FROM 
    facture
LEFT JOIN 
    transaction ON facture.numero_facture = transaction.facture_id
WHERE
    facture.carte_id=:matricule
    AND facture.date_facture BETWEEN :dd and :df
GROUP BY 
    facture.numero_facture
ORDER BY facture.date_facture ASC');
$req->bindParam(':matricule', $_GET['matricule']);
$req->bindParam(':dd', $_GET['dd']);
$req->bindParam(':df', $_GET['df']);
$req->execute();
$sol = $req->fetchAll();

 print_r(json_encode($sol));

 }     

?>