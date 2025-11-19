<?php
date_default_timezone_set('UTC'); 
require '../database/database.php';

$req = $con->prepare('SELECT code,titre,etat,type,TO_BASE64(photo) AS photo64 FROM categorie');
        $req->execute();
        $sol = $req->fetchAll(PDO::FETCH_ASSOC);

        print_r(json_encode($sol));
     
?>