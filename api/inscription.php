<?php
date_default_timezone_set('UTC'); 
require '../database/database.php';

$json = file_get_contents('php://input');
 
     // decoding the received JSON and store into $obj variable.
     $obj = json_decode($json,true);

      
 // verification si le telephone a deja ete utilisé pour ouvrir un compte beneficiaire

            $req = $con->prepare('SELECT * FROM utilisateur WHERE telephone=:matricule');
        $req->bindParam(':matricule', $obj['telephone']);
        $req->execute();
        $VerifBene = $req->fetchAll();

              if (!empty($VerifBene)) {
                  $message = "Vous etes déjà inscrit avec ce numero de téléphone. En cas de difficulté, veuillez contacter par SMS ou WhatsApp le superviseur au +225 0709107849 ou par email à info@adores.tech";
              }else{

 
 $req = $con->prepare('SELECT MAX(id) AS nbre FROM utilisateur WHERE id REGEXP "^[0-9]+$" ');
        $req->execute();
        $nombre = $req->fetchAll();


        if(!empty($nombre)){
          if($nombre > 10000){
            $matricule = $nombre[0]['nbre'] + 1;
            }else{
            $matricule = 10000 + $nombre[0]['nbre'] + 1;
            }
        }else{
            $matricule = 10000 + 1;
        }




            	      // ajout de la table utilisateur
            $id = $matricule;
            $login = $obj['login'];
            $nom_prenom = $obj['nom_prenom'];
            $date_naissance = date('Y-m-d');
            $lieu_naissance = "";
            $sexe = "";
            $nationalite = "";
            $ville = "";
            $adresse = "";
            $raison_sociale = $obj['nom_prenom'];
            $email = "";
            $telephone = '225'.$obj['telephone'];
            $solde = 0;
            $profil = "Classic";
            $categorie = "";
            $mdp = $obj['mdp'];
            $role = "Professionnel";
            $matricule = $matricule;
            $profession = "";
            $entreprise = "";
            $description = "J'utilise Soutra+ le réseau social professionnel de stage en ligne. Je vous invite à faire autant...";
            $date_saisie = date('Y-m-d');
            $etat = "Actif";
            $photo = "";
            $type = "";

                // ajout de la table utilisateur
            $req = $con->prepare('INSERT INTO utilisateur VALUES (:id,:matricule, :nom_prenom, :date_naissance, :lieu_naissance, :sexe, :login,:mdp,:telephone,:email,:profession,:nationalite,:ville,:adresse,:profil,:categorie,:solde,:raison_sociale,:entreprise,:description,:role,:date_saisie,:photo,:type,:etat)');
        $req->bindParam(':id', $id);
        $req->bindParam(':login', $login);
        $req->bindParam(':nom_prenom', $nom_prenom);
        $req->bindParam(':date_naissance', $date_naissance);
        $req->bindParam(':lieu_naissance', $lieu_naissance);
        $req->bindParam(':sexe', $sexe);
        $req->bindParam(':nationalite', $nationalite);
        $req->bindParam(':ville', $ville);
        $req->bindParam(':adresse', $adresse);
        $req->bindParam(':description', $description);
        $req->bindParam(':profil', $profil);
        $req->bindParam(':categorie', $categorie);
        $req->bindParam(':solde', $solde);
        $req->bindParam(':email', $email);
        $req->bindParam(':mdp', $mdp);
        $req->bindParam(':telephone', $telephone);
        $req->bindParam(':role', $role);
        $req->bindParam(':profession', $profession);
        $req->bindParam(':entreprise', $entreprise);
        $req->bindParam(':raison_sociale', $raison_sociale);
        $req->bindParam(':matricule', $matricule);
        $req->bindParam(':date_saisie', $date_saisie);
        $req->bindParam(':photo', $photo);
        $req->bindParam(':type', $type);
        $req->bindParam(':etat', $etat);
        $ajouterUtilisateur  = $req->execute();



                 if($ajouterUtilisateur==true){

                      $message = "Félicitation ! Votre compte a été avec succès";


                 }else{
               
                $message = "Echec de la création de votre compte. Veuillez réessayez ou contacter notre équipe au +225 0709107849 ou info@adores.tech";
            }
            }

      


         

       

     print_r(json_encode($message));


 ?>


