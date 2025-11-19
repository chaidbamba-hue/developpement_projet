<?php
date_default_timezone_set('UTC'); 
require '../database/database.php';

$req = $con->prepare('SELECT * FROM commune');
        $req->execute();
        $sol = $req->fetchAll(PDO::FETCH_ASSOC);

        print_r(json_encode($sol));
     
?>