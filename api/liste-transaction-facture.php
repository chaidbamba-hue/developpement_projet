<?php
date_default_timezone_set('UTC');
require '../database/database.php';

if(isset($_GET['matricule'] ))
	{ 

$req = $con->prepare('SELECT * FROM transaction WHERE facture_id=:matricule ORDER BY date_transaction ASC');
        $req->bindParam(':matricule', $_GET['matricule']);
        $req->execute();
        $sol = $req->fetchAll();

        print_r(json_encode($sol));
      
}

?>
