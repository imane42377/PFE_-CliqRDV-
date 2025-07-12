<?php
session_start();

require_once 'config.php'; 

$conn = mysqli_connect($host, $user, $password, $dbname);
if (mysqli_connect_errno()) {
    die("Erreur de connexion MySQL : " . mysqli_connect_error());
}

if ((isset($_GET['id']))   && (isset($_GET['rdv'])) ){
    $id=$_GET['id'];
    $rdv=$_GET['rdv'];
    $pres=$_GET['pre'];
    $rdv="update rendezvous set etat='annulé' where client='$id' and id='$rdv'";
   $res=mysqli_query($conn, $rdv);

   $req="select * from client where client_id='$id'";
   $ress = mysqli_query($conn, $req);

    if ($ress && mysqli_num_rows($ress) > 0) {
        $row = mysqli_fetch_assoc($ress);
    }
   $email_client = $row['email'];
    //$date_rdv = $row['date_rdv'];
   // $heure_rdv = $row['heure_rdv'];
    $subject = "Annulation de votre rendez-vous";
    $message = "Bonjour,\n\nVotre rendez-vous prévu  a été annulé.\n\nCordialement,\nVotre service";
    if(mail($email_client, $subject, $message)){
        echo "<script> alert('oookkk') ;</script>";
    }
   header("location:calendrier.php?x=$pres");
}