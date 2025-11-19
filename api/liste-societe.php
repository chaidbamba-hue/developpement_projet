<?php
date_default_timezone_set('UTC');
require '../database/database.php';


$req = $con->prepare('
    SELECT 
        societe.code_societe,
        societe.nom_societe,
        societe.sigle_societe,
        societe.telephone_societe,
        societe.email_societe,
        societe.adresse_societe,
        societe.categorie_societe,
        TO_BASE64(societe.photo_logo) AS photo64,
        societe.type_logo AS type,
        societe.latitude_societe,
        societe.longitude_societe,
        categorie.titre 
    FROM societe 
    LEFT JOIN categorie ON societe.categorie_societe = categorie.code 
    WHERE societe.etat_societe = "Actif"');
$req->execute();
$sol = $req->fetchAll();
print_r(json_encode($sol));

 
?>