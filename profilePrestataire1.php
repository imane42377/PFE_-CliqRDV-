<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit;
}
require_once 'config.php'; 
$conn = mysqli_connect($host, $user, $password, $dbname);
if (mysqli_connect_errno()) {
    die("Erreur de connexion MySQL : " . mysqli_connect_error());
}

    $email=$_SESSION['user_email'];
    $id=$_SESSION['user_id'];
    $req="select * from prestataire where prestataire_id='$id' or email='$email'";
    $res=mysqli_query($conn , $req);
    $rows = [];
    if ($res && mysqli_num_rows($res) > 0) {
    $rows =mysqli_fetch_assoc($res);
}
$sql1 = "
  SELECT id
    FROM prestataire
   WHERE  prestataire_id = $id
";
$res = mysqli_query($conn, $sql1);
if (! $res) {
    die("Erreur MySQL (récup prestataire_id) : " . mysqli_error($conn));
}
if (mysqli_num_rows($res) === 0) {
    die("Aucun prestataire trouvé pour l'utilisateur n°{$id}");
}

// on récupère le bon prestataire_id
$row           = mysqli_fetch_assoc($res);
$prestaId      = intval($row['id']);

// … your session_start, connect, fetch $rows …
if ($_SERVER['REQUEST_METHOD']==='POST' && !empty($_POST['prestataire_id'])) {
  $id = intval($_POST['prestataire_id']);
  
  if (isset($_POST['formation'], $_POST['experience'])) {
    $parcours   = mysqli_real_escape_string($conn, $_POST['formation']);
    $experience = mysqli_real_escape_string($conn, $_POST['experience']);
    $sql = "
      UPDATE prestataire
      SET parcours   = '$parcours',
          experience = '$experience'
      WHERE prestataire_id = $id
    ";
    mysqli_query($conn, $sql);
    header('Location: profilePrestataire1.php?modif=parcours_success');
    exit;
  }

  
  if (isset($_POST['nom_prenom'], $_POST['specialite'])) {
    $nom   = mysqli_real_escape_string($conn, $_POST['nom_prenom']);
    $spec  = mysqli_real_escape_string($conn, $_POST['specialite']);
    $loc   = mysqli_real_escape_string($conn, $_POST['localisation']);
    $tele  = mysqli_real_escape_string($conn, $_POST['tele']);
    $fax   = mysqli_real_escape_string($conn, $_POST['fax']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // handle photo upload if present…
    $photoSQL = '';
    if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error']===UPLOAD_ERR_OK) {
      $tmp      = $_FILES['photo_profil']['tmp_name'];
      $fn       = basename($_FILES['photo_profil']['name']);
      $dest     = "uploads/$fn";
      move_uploaded_file($tmp, __DIR__."/$dest");
      $photoSQL = ", photo_profil = '".mysqli_real_escape_string($conn,$dest)."'";
    }
  $id = intval($_SESSION['user_id']);
    $sql = "
      UPDATE prestataire
      SET nom_prenom   = '$nom',
          specialite   = '$spec',
          localisation = '$loc',
          tele         = '$tele',
          fax          = '$fax',
          email        = '$email'
          $photoSQL
      WHERE prestataire_id = $id
    ";
    mysqli_query($conn, $sql);
    header('Location: profilePrestataire1.php?modifE=profile_success');
    exit;
  }
  if ($_SERVER['REQUEST_METHOD']==='POST' && !empty($_POST['prestataire_id'])) {
  $id = intval($_POST['prestataire_id']);
  
  if (isset($_POST['formation'], $_POST['experience'])) {
    $parcours   = mysqli_real_escape_string($conn, $_POST['formation']);
    $experience = mysqli_real_escape_string($conn, $_POST['experience']);
    $sql = "
      UPDATE prestataire
      SET parcours   = '$parcours',
          experience = '$experience'
      WHERE prestataire_id = $id
    ";
    mysqli_query($conn, $sql);
    header('Location: profilePrestataire1.php?modif=parcours_success');
    exit;
  }

  
  if (isset($_POST['nom_prenom'], $_POST['specialite'])) {
    $nom   = mysqli_real_escape_string($conn, $_POST['nom_prenom']);
    $spec  = mysqli_real_escape_string($conn, $_POST['specialite']);
    $loc   = mysqli_real_escape_string($conn, $_POST['localisation']);
    $tele  = mysqli_real_escape_string($conn, $_POST['tele']);
    $fax   = mysqli_real_escape_string($conn, $_POST['fax']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // handle photo upload if present…
    $photoSQL = '';
    if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error']===UPLOAD_ERR_OK) {
      $tmp      = $_FILES['photo_profil']['tmp_name'];
      $fn       = basename($_FILES['photo_profil']['name']);
      $dest     = "uploads/$fn";
      move_uploaded_file($tmp, __DIR__."/$dest");
      $photoSQL = ", photo_profil = '".mysqli_real_escape_string($conn,$dest)."'";
    }

    $sql = "
      UPDATE prestataire
      SET nom_prenom   = '$nom',
          specialite   = '$spec',
          localisation = '$loc',
          tele         = '$tele',
          fax          = '$fax',
          email        = '$email'
          $photoSQL
      WHERE prestataire_id = $id
    ";
    
     if (mysqli_query($conn, $sql)) {
        header('Location: profilePrestataire1.php?modifE=success');
        exit;
    } else {
        die("Erreur MySQL durant la mise à jour : " . mysqli_error($conn));
    }
  
    
  }
}
// Modification des horaires de travail du prestataire
if (isset($_POST['save_schedule'])) {
    $jours = ['lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche'];
    foreach ($jours as $j) {
        $jour_sql = ucfirst($j); // Pour correspondre à la colonne en BDD
        $isOpen = isset($_POST["open_$j"]) ? 1 : 0;

        if ($isOpen) {
            $start = mysqli_real_escape_string($conn, $_POST["start_$j"]);
            $end   = mysqli_real_escape_string($conn, $_POST["end_$j"]);
            // Met à jour ou insère la plage horaire
            $sql = "UPDATE horairestravail
                    SET heure_debut = '$start',
                        heure_fin   = '$end'
                    WHERE prestataire = $prestaId AND jour_semaine = '$jour_sql'";
            mysqli_query($conn, $sql);
        } else {
            // Ferme la journée : heures nulles (ou tu peux aussi supprimer la ligne)
            $sql = "UPDATE horairestravail
                    SET heure_debut = NULL,
                        heure_fin   = NULL
                    WHERE prestataire = $prestaId AND jour_semaine = '$jour_sql'";
            mysqli_query($conn, $sql);
            
        }
        
    }
    header('Location: profilePrestataire1.php?modif=horaires_success');
    exit;
}
}

?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CliqRDV - Profil Docteur</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="./bootstrap/bootstrapajax.min.css" rel="stylesheet">
    <link href="./bootstrap/fontawesome/css/all.min.css" rel="stylesheet">
 <style>
        :root {
            --primary-color: #2563eb;
            --primary-light: #3b82f6;
            --secondary-color: #1e40af;
            --accent-color: #60a5fa;
            --text-dark: #1f2937;
            --text-medium: #4b5563;
            --text-light: #9ca3af;
            --bg-light: #f9fafb;
            --border-color: #e5e7eb;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --error-color: #ef4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            line-height: 1.6;
        }

        
        header {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(12px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        position: fixed;
        width: 100%;
        top: 0;
        z-index: 1000;
        padding: 0.5rem 2rem;
            }


        /* Logo */
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.8rem;
            font-weight: bold;
            color: #2563eb;
            font-family: 'Segoe UI', sans-serif;
        }

        .logo::before {
            content: "📅";
            font-size: 1.8rem;
        }

        /* Navigation */
        .navbar-nav .nav-link {
            color: #1e3a8a !important;
            font-weight: 500;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
            position: relative;
            font-size: 1rem;
        }

        .navbar-nav .nav-link:hover {
            color: #3b82f6 !important;
            transform: translateY(-1px);
        }

        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 50%;
            background: linear-gradient(90deg, #3b82f6, #1d4ed8);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .navbar-nav .nav-link:hover::after {
            width: 100%;
        }

        /* Responsive pour mobiles */
        @media (max-width: 768px) {
            .top-container {
                flex-direction: column;
                align-items: flex-start;
            }

            .navbar-collapse {
                width: 100%;
            }

            .navbar-nav {
                width: 100%;
            }

            .navbar-nav .nav-item {
                width: 100%;
            }

            .navbar-nav .nav-link {
                width: 100%;
            }
        }


        .top-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .profile-header {
            display: flex;
            gap: 2rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            padding: 2rem;
            margin-bottom: 2rem;
            position: relative;
        }

        .profile-picture {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .profile-info {
            flex: 1;
        }

        .profile-name {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .profile-specialty {
            font-size: 1.2rem;
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .profile-bio {
            color: var(--text-medium);
            margin-bottom: 1.5rem;
            max-width: 600px;
        }

        .profile-meta {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-medium);
        }

        .meta-item i {
            color: var(--primary-color);
        }

        .rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .stars {
            color: var(--warning-color);
        }

        .rating-count {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .btn {
            padding: 0.7rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }

        .btn-outline {
            background-color: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline:hover {
            background-color: rgba(37, 99, 235, 0.1);
        }

        .profile-content {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 2rem;
            width: auto;
        }

        .profile-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            padding: 2rem;
            margin-bottom: 2rem;
            width:  1170px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }

        .card-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        .edit-btn {
            color: var(--primary-color);
            background: none;
            border: none;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .schedule-container {
            margin-top: 1rem;
        }

        .schedule-day {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0;
            border-bottom: 1px solid var(--border-color);
        }

        .schedule-day:last-child {
            border-bottom: none;
        }

        .day-name {
            font-weight: 600;
            color: var(--text-dark);
        }

        .day-hours {
            color: var(--text-medium);
        }

        .closed {
            color: var(--text-light);
            font-style: italic;
        }

        .services-list {
            list-style: none;
        }

        .service-item {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border-color);
        }

        .service-item:last-child {
            border-bottom: none;
        }

        .service-name {
            font-weight: 600;
        }

        .service-price {
            color: var(--primary-color);
            font-weight: 700;
        }

        .review-card {
            display: flex;
            gap: 1.5rem;
            padding: 1.5rem 0;
            border-bottom: 1px solid var(--border-color);
        }

        .review-card:last-child {
            border-bottom: none;
        }

        .reviewer-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }

        .review-content {
            flex: 1;
        }

        .reviewer-name {
            font-weight: 600;
            margin-bottom: 0.3rem;
        }

        .review-date {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .review-text {
            color: var(--text-medium);
            margin-top: 0.5rem;
        }

        .sidebar-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .sidebar-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text-dark);
        }

        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .contact-icon {
            color: var(--primary-color);
            font-size: 1.2rem;
            margin-top: 0.2rem;
        }

        .contact-text {
            color: var(--text-medium);
        }

        .contact-text strong {
            color: var(--text-dark);
            display: block;
            margin-bottom: 0.2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-top: 1rem;
        }

        .stat-item {
            text-align: center;
            padding: 1rem;
            background: var(--bg-light);
            border-radius: 8px;
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.3rem;
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--text-medium);
        }

        .availability-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        .available {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }

        .unavailable {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--error-color);
        }

        .badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            background-color: var(--accent-color);
            color: white;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .languages-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        @media (max-width: 1024px) {
            .profile-content {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .profile-meta {
                justify-content: center;
            }

            .action-buttons {
                justify-content: center;
            }

            .profile-bio {
                margin-left: auto;
                margin-right: auto;
            }

            .nav-links {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .profile-meta {
                flex-direction: column;
                gap: 0.5rem;
                align-items: center;
            }

            .action-buttons {
                flex-direction: column;
                width: 100%;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
        .exit-button {
            position: fixed;
            top: 15px;
            right: 15px;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: bold;
            color: #666;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            z-index: 1000;
            backdrop-filter: blur(10px);
        }

        .exit-button:hover {
            background: #ff4757;
            color: white;
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(255, 71, 87, 0.3);
        }

        .exit-button:active {
            transform: scale(0.95);
        }

 </style>
   <title>Modifier le Profil</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            padding: 20px;
        }

        /* Overlay pour simuler le popup */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        .overlay.show { display: flex; }
        .popup {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            animation: popupSlide 0.3s ease-out;
        }

        @keyframes popupSlide {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .popup-header {
            background: #4285f4;
            color: white;
            padding: 20px 24px;
            border-radius: 12px 12px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .popup-title {
            font-size: 1.3em;
            font-weight: 600;
        }

        .close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 1.5em;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            transition: background 0.2s ease;
        }

        .close-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .popup-content {
            padding: 24px;
        }

        .form-section {
            margin-bottom: 24px;
        }

        .section-title {
            font-size: 1.1em;
            font-weight: 600;
            color: #333;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .photo-section {
            text-align: center;
            margin-bottom: 32px;
        }

        .photo-container {
            position: relative;
            display: inline-block;
            margin-bottom: 16px;
        }

        .profile-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: #8b5cf6;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5em;
            font-weight: bold;
            margin: 0 auto;
        }

        .photo-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s ease;
            cursor: pointer;
        }

        .photo-container:hover .photo-overlay {
            opacity: 1;
        }

        .camera-icon {
            color: white;
            font-size: 1.5em;
        }

        .change-photo-btn {
            background: #4285f4;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9em;
            margin-top: 8px;
            transition: background 0.2s ease;
        }

        .change-photo-btn:hover {
            background: #3367d6;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            font-weight: 500;
            color: #555;
            margin-bottom: 6px;
            font-size: 0.9em;
        }

        .form-input {
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.95em;
            transition: border-color 0.2s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #4285f4;
            box-shadow: 0 0 0 2px rgba(66, 133, 244, 0.1);
        }

        .status-toggle {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 8px;
        }

        .toggle-switch {
            position: relative;
            width: 50px;
            height: 24px;
            background: #ccc;
            border-radius: 12px;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .toggle-switch.active {
            background: #10b981;
        }

        .toggle-slider {
            position: absolute;
            top: 2px;
            left: 2px;
            width: 20px;
            height: 20px;
            background: white;
            border-radius: 50%;
            transition: transform 0.2s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .toggle-switch.active .toggle-slider {
            transform: translateX(26px);
        }

        .status-text {
            font-weight: 500;
            color: #10b981;
        }

        .popup-actions {
            padding: 0 24px 24px;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.95em;
        }

        .btn-cancel {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .btn-cancel:hover {
            background: #e9ecef;
        }

        .btn-save {
            background: #4285f4;
            color: white;
            border: none;
        }

        .btn-save:hover {
            background: #3367d6;
        }

        @media (max-width: 768px) {
            .popup {
                margin: 10px;
                max-width: none;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .popup-actions {
                flex-direction: column;
            }
        }

        .file-input {
            display: none;
        }
    </style>
    <style>
  /* --- Modal de base --- */
  .modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(8px);
    align-items: center;
    justify-content: center;
    z-index: 1000;
  }
  .modal.active { display: flex; }
  .modal-content {
    background: #fff;
    border-radius: 16px;
    width: 90%;
    max-width: 600px;
    max-height: 80vh;
    overflow-y: auto;
    padding: 32px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    animation: slideUp 0.3s ease;
  }

  /* Header */
  .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
  }
  .modal-header h3 {
    margin: 0;
    font-size: 20px;
  }
  .close-btn {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #86868b;
    transition: color .2s;
  }
  .close-btn:hover { color: #000; }

  /* Bloc par jour */
  .day-editor {
    padding: 16px;
    margin-bottom: 16px;
    background: #fafafa;
    border: 1px solid #eee;
    border-radius: 8px;
  }
  .day-editor-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
  }
  .day-editor-name {
    font-weight: 600;
  }

  /* Toggle */
  .toggle-switch {
    position: relative;
    width: 50px; height: 24px;
  }
  .toggle-switch input { display:none; }
  .toggle-switch .slider {
    position: absolute; inset: 0;
    background: #ccc; border-radius: 24px;
    transition: .4s;
  }
  .toggle-switch .slider:before {
    content: ""; position: absolute;
    width: 18px; height: 18px;
    left: 3px; bottom: 3px;
    background: #fff; border-radius: 50%;
    transition: .4s;
  }
  .toggle-switch input:checked + .slider {
    background: #007aff;
  }
  .toggle-switch input:checked + .slider:before {
    transform: translateX(26px);
  }

  /* Inputs horaires */
  .time-inputs {
    display: flex;
    gap: 12px;
    align-items: center;
  }
  .time-input-group {
    display: flex;
    flex-direction: column;
  }
  .time-input-group label {
    font-size: 12px;
    color: #666;
    margin-bottom: 4px;
  }
  .time-input {
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 6px;
    width: 80px;
  }

  /* Pied de modal */
  .modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 24px;
    border-top: 1px solid #eee;
    padding-top: 16px;
  }
  .btn {
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
  }
  .btn-cancel {
    background: #f2f2f7;
    border: none;
  }
  .btn-save {
    background: #007aff;
    color: #fff;
    border: none;
  }

  @keyframes slideUp {
    from { transform: translateY(20px); opacity: 0; }
    to   { transform: translateY(0); opacity: 1; }
  }
</style>
</head>
<body>
        <!--header>
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container" >
                <div class="logo">CliqRDV</div>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboardDoc.php"><i class="fas fa-tachometer-alt me-1"></i>Tableau de Bord</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="calendrier.php"><i class="fas fa-calendar me-1"></i>Calendrier</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="profilePrestataire.php"><i class="fas fa-user me-1"></i>Profil</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="notifications.php"><i class="fas fa-bell me-1"></i>Notifications</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i>Déconnexion</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header-->
     <button class="exit-button" id="exitBtn1" onclick="exitApplication()" title="Fermer">×</button>
<script>
  function exitApplication() {
            window.history.back();
        }
</script>

    <div class="top-container">
    <form method="post" action="" id="profileForm">
        <div class="profile-header">
            <?php
                     if (!empty($rows['photo_profil']) && file_exists(__DIR__ . '/' . $rows['photo_profil'])) {
                     echo '<img src="' . htmlspecialchars($rows['photo_profil'], ENT_QUOTES, 'UTF-8') . '" alt="Photo de profil" class="rounded-circle me-2" style="width: 90px; height: 90px; object-fit: cover;">';
                     } else {
                        echo '<i class="fas fa-user-md me-2" style="font-size: 90px;"></i>';
                     }?>
            <div class="profile-info">
                <h1 class="profile-name"><?php echo 'Dr '.$rows['nom_prenom'] ?></h1>
                <p class="profile-specialty"><?php echo $rows['specialite'] ?></p>
                <!--p class="profile-bio">Spécialiste en cardiologie avec plus de 15 ans d'expérience. Diplômé de l'Université de Paris et ancien chef de clinique à l'Hôpital Européen Georges Pompidou.</p-->
                
                <div class="profile-meta">
                    <div class="meta-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?php echo $rows['localisation'] ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Diplômé en 2005</span>
                    </div>
                </div>

                <div class="availability-badge available">
                    <i class="fas fa-check-circle"></i> Disponible aujourd'hui
                </div>

                <div class="action-buttons">
                    <button type="button" class="btn btn-primary" onclick='openProfilePopup()'>
                        <i class="fas fa-edit"></i> Modifier le profil
                    </button>
                </div>
            </div>

  <div class="sidebar">
                <div class="sidebar-card">
                    <h3 class="sidebar-title">Coordonnées</h3>
                    <div class="contact-info">
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt contact-icon"></i>
                            <div class="contact-text">
                                <strong>Adresse</strong>
                                <span><?php echo $rows['localisation'] ?></span>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone-alt contact-icon"></i>
                            <div class="contact-text">
                                <strong>Téléphone</strong>
                                <span><?php echo $rows['tele'] ?></span>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-fax contact-icon"></i>
                            <div class="contact-text">
                                <strong>Fax</strong>
                                <span><?php echo $rows['fax'] ?></span>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope contact-icon"></i>
                            <div class="contact-text">
                                <strong>Email</strong>
                                <span><?php echo $rows['email'] ?></span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            
        </div>
    </form>
        <!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le Profil</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f5f5f7;
            padding: 20px;
        }

        .profile-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 1200px;
            
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 600;
            color: #1d1d1f;
        }

        .modify-btn {
            background: #007aff;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: background-color 0.2s;
        }

        .modify-btn:hover {
            background: #0056b3;
        }

        .section {
            margin-bottom: 32px;
        }

        .section h2 {
            font-size: 20px;
            font-weight: 600;
            color: #1d1d1f;
            margin-bottom: 12px;
        }

        .section-content {
            color: #86868b;
            font-size: 16px;
            line-height: 1.5;
        }

        .section-content ul {
            list-style: none;
            padding-left: 16px;
        }

        .section-content li {
            position: relative;
            margin-bottom: 8px;
        }

        .section-content li::before {
            content: "•";
            position: absolute;
            left: -16px;
            color: #007aff;
            font-weight: bold;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(8px);
            z-index: 1000;
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            padding: 32px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            animation: slideUp 0.3s ease;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .modal-header h3 {
            font-size: 20px;
            font-weight: 600;
            color: #1d1d1f;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #86868b;
            padding: 4px;
            border-radius: 4px;
            transition: color 0.2s;
        }

        .close-btn:hover {
            color: #1d1d1f;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            color: #1d1d1f;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group textarea {
            width: 100%;
            min-height: 120px;
            padding: 12px;
            border: 1px solid #d2d2d7;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            resize: vertical;
            transition: border-color 0.2s;
        }

        .form-group textarea:focus {
            outline: none;
            border-color: #007aff;
            box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.1);
        }

        .modal-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 24px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-cancel {
            background: #f2f2f7;
            color: #1d1d1f;
        }

        .btn-cancel:hover {
            background: #e5e5ea;
        }

        .btn-save {
            background: #007aff;
            color: white;
        }

        .btn-save:hover {
            background: #0056b3;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { 
                opacity: 0;
                transform: translateY(20px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }

        .help-text {
            font-size: 12px;
            color: #86868b;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <div class="profile-card">
        <div class="header">
            <h1>À propos</h1>
            <button class="modify-btn" onclick="openModal()">
                 Modifier
            </button>
        </div>
<?php
  // split your newline-separated parcours & experience strings
  $parcoursList   = explode("\n", $rows['parcours']);
  $experienceList = explode("\n", $rows['experience']);
?>
<div class="section">
  <h2>*Parcours</h2>
  <div class="section-content">
    <ul>
      <?php foreach($parcoursList as $i => $p):
        $p = trim($p);
        if ($p === '') continue;
      ?>
        <li>
          <span id="parcours-display-<?= $i ?>">
            <?= htmlspecialchars($p, ENT_QUOTES) ?>
          </span>
          <input
            type="text"
            name="parcours[]"
            value="<?= htmlspecialchars($p, ENT_QUOTES) ?>"
            style="display:none;"
            id="parcours-input-<?= $i ?>"
          >
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>

<div class="section">
  <h2>*Expérience</h2>
  <div class="section-content">
    <ul>
      <?php foreach($experienceList as $j => $e):
        $e = trim($e);
        if ($e === '') continue;
      ?>
        <li>
          <span id="experience-display-<?= $j ?>">
            <?= htmlspecialchars($e, ENT_QUOTES) ?>
          </span>
          <input
            type="text"
            name="experience[]"
            value="<?= htmlspecialchars($e, ENT_QUOTES) ?>"
            style="display:none;"
            id="experience-input-<?= $j ?>"
          >
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>

    </div>

    <!-- Modal -->
<form id="editForm" method="post" action="">
    
  <input type="hidden" name="prestataire_id" value="<?= intval($_SESSION['user_id']) ?>">
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Modifier les informations</h3>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>

            <input type="hidden" name="prestataire_id" value="<?= intval($rows['prestataire_id']) ?>">

                <div class="form-group">
                    <label for="formation">*Parcours</label>
                    <textarea id="formation" name="formation" placeholder="Décrivez votre formation académique…" required ><?= htmlspecialchars($rows['parcours'], ENT_QUOTES) ?></textarea>
                    <div class="help-text">Vous pouvez utiliser plusieurs lignes pour lister différentes formations</div>
                </div>

                <div class="form-group">
                    <label for="experience">*Expérience</label>
                    <textarea id="experience" name="experience"  placeholder="Décrivez votre expérience professionnelle…"  required><?= htmlspecialchars($rows['experience'], ENT_QUOTES) ?></textarea>
                    <div class="help-text">Vous pouvez utiliser plusieurs lignes pour lister différentes expériences</div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-cancel" onclick="closeModal()">Annuler</button>
                    <button type="submit" class="btn btn-save">Enregistrer</button>
                </div>
        </div>
    </div>
</form>
    <script>
        // Store current data
      
    

        function closeModall() {
            const modal = document.getElementById('editModal');
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }


        function openModal() {
        document.getElementById('editModal').classList.add('active');
        document.body.style.overflow = 'hidden';
        }

        // no more preventDefault on submit!
        // the browser will post the form and reload the page on success.

    </script>
</body>
</html>
                <div class="profile-card">
                    <div class="card-header">
                        <h2 class="card-title">Horaires de consultation</h2>
                        <button class="edit-btn" onclick="openScheduleModal()">
                            <i class="fas fa-edit"></i> Modifier
                        </button>
                    </div>
<div class="schedule-container">
<?php

if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit;
}
    $host     = 'localhost';
    $dbname   = 'cliqrdv';
    $user     = 'root';
    $password = '';

    $conn = mysqli_connect($host, $user, $password, $dbname);
    if (mysqli_connect_errno()) {
        die("Erreur de connexion MySQL : " . mysqli_connect_error());
    }
  // use the session's user_id
  $id = intval($_SESSION['user_id']);

  $jours_semaines = [
    "Lundi","Mardi","Mercredi",
    "Jeudi","Vendredi","Samedi","Dimanche"
  ];

  // Simple query
  $sql = "
    SELECT jour_semaine, heure_debut, heure_fin
      FROM horairestravail
     WHERE prestataire = $prestaId
  ";
  $res = mysqli_query($conn, $sql);
  if (! $res) {
    die("MySQL error: " . mysqli_error($conn));
  }

  // build the array
  $horaires = [];
while ($row = mysqli_fetch_assoc($res)) {
    // ensure "lundi" → "Lundi", "MARDI" → "Mardi", etc.
    $key = ucfirst(strtolower($row['jour_semaine']));
    $horaires[$key][] = $row['heure_debut'] . " – " . $row['heure_fin'];
}


// On supprime toutes les plages "00:00:00 – 00:00:00"
foreach ($horaires as $jour => $plages) {
    $horaires[$jour] = array_filter($plages, function($plage) {
        return trim($plage) !== '00:00:00 – 00:00:00';
    });
}


 foreach ($jours_semaines as $jour) {
    echo '<div class="schedule-day">';
    echo   '<span class="day-name">'.htmlspecialchars($jour).'</span>';
    if (! empty($horaires[$jour])) {
        echo '<span class="day-hours">'
           . implode(' | ', $horaires[$jour])
           . '</span>';
    } else {
        echo '<span class="day-hours closed">Fermé</span>';
    }
    
    echo '</div>';
}

?>
</div>

    </div>

    <script>
        // Scripts pour la gestion interactive du profil
        document.addEventListener('DOMContentLoaded', function() {
            // Ici vous pourriez ajouter des fonctionnalités comme:
            // - Édition des informations
            // - Gestion des rendez-vous
            // - Affichage dynamique des statistiques
            console.log('Page de profil du docteur chargée');
        });

        // Exemple de fonction pour modifier le profil
        function editProfile(section) {
            console.log('Modification de la section: ' + section);
            // Implémentez la logique d'édition ici
        }
    </script>
    <form id="profileFormE" method="post" action="" enctype="multipart/form-data">
           <!-- 1) Hidden PK so PHP sees $_POST['prestataire_id'] -->
            <input 
                type="hidden" 
                name="prestataire_id" 
                value="<?= htmlspecialchars($rows['prestataire_id'], ENT_QUOTES) ?>"
            >

  <div class="overlay" id="me">
    <div class="popup">
      <div class="popup-header">
        <h2 class="popup-title">Modifier le profil</h2>
        <button class="close-btn" type="button" onclick="closeePopup()">×</button>
      </div>

      <div class="popup-content">
        <div class="photo-section">
                <div class="section-title">Photo de profil</div>

                <!-- This div shows the current photo as a background -->
                <div class="photo-container" onclick="changePhoto()">
                <div
                    class="profile-photo"
                    id="profilePhoto"
                    style="background-image:
                    url('<?= htmlspecialchars($rows['photo_profil'], ENT_QUOTES) ?>');
                        background-size: cover;
                        background-position: center;"
                ></div>
                <div class="photo-overlay">
                    <span class="camera-icon"></span>
                </div>
                </div>

                <!-- This is the actual file‐input: -->
                <input
                type="file"
                name="photo_profil"
                id="photoInput"
                class="file-input"
                accept="image/*"
                onchange="handlePhotoChange(event)"
                >

                <!-- And this button simply forwards the click to that hidden input -->
                <button type="button" class="change-photo-btn" onclick="changePhoto()">
                Changer la photo
                </button>
            
        </div>

        <!-- Informations personnelles -->
        <div class="form-section">
        <div class="section-title">👤 Informations personnelles</div>
        <div class="form-grid">
            <div class="form-group">
            <label class="form-label" for="firstName">Prénom</label>
            <input 
                type="text" 
                class="form-input" 
                id="firstName" 
                name="firstName"
                value="<?= 'Dr' ?>"  <!-- or pull from DB if you have a column -->
            >
            </div>
            <div class="form-group">
            <label class="form-label" for="lastName">Nom</label>
            <input 
                type="text" 
                class="form-input" 
                id="lastName" 
                name="nom_prenom"
                value="<?= htmlspecialchars($rows['nom_prenom'], ENT_QUOTES) ?>"
                required
            >
            </div>
            <div class="form-group full-width">
            <label class="form-label" for="specialty">Spécialité</label>
            <input 
                type="text" 
                class="form-input" 
                id="specialty" 
                name="specialite"
                value="<?= htmlspecialchars($rows['specialite'], ENT_QUOTES) ?>"
                required
            >
            </div>
        </div>
        </div>

        <!--  Coordonnées -->
        <div class="form-section">
        <div class="section-title">Coordonnées</div>
        <div class="form-grid">
            <div class="form-group full-width">
            <label class="form-label" for="address">Adresse</label>
            <input 
                type="text" 
                class="form-input" 
                id="address" 
                name="localisation"
                value="<?= htmlspecialchars($rows['localisation'], ENT_QUOTES) ?>"
            >
            </div>
            <div class="form-group">
            <label class="form-label" for="phone">Téléphone</label>
            <input 
                type="tel" 
                class="form-input" 
                id="phone" 
                name="tele"
                value="<?= htmlspecialchars($rows['tele'], ENT_QUOTES) ?>"
            >
            </div>
            <div class="form-group">
            <label class="form-label" for="fax">Fax</label>
            <input 
                type="text" 
                class="form-input" 
                id="fax" 
                name="fax"
                value="<?= htmlspecialchars($rows['fax'], ENT_QUOTES) ?>"
            >
            </div>
            <div class="form-group full-width">
            <label class="form-label" for="email">Email</label>
            <input 
                type="email" 
                class="form-input" 
                id="email" 
                name="email"
                value="<?= htmlspecialchars($rows['email'], ENT_QUOTES) ?>"
                required
            >
            </div>
        </div>
        </div>

    </div>
    </form method="post" action="">   
  <input type="hidden" name="prestataire_id" value="<?= intval($row['id']); ?>">
            <div class="popup-actions">
                <button class="btn btn-cancel" onclick="closeePopup()">Annuler</button>
                <button type="submit" class="btn btn-save">Enregistrer</button>

            </div>
        </div>
    </div>

<script>
        let isAvailable = true;
        function openPopup() {
        // 1) Grab the overlay and popup elements
        const overlay = document.querySelector('.overlay');
        const popup   = overlay.querySelector('.popup');

        // 2) Make sure the overlay is hidden by default in your CSS:
        //    .overlay { display: none; }
        //    .overlay.show { display: flex; }
        // 3) Show it
        overlay.classList.add('show');

        // 4) Play the slide‐in animation
        popup.style.animation = 'popupSlide 0.3s ease-out';
        }

        function closeePopup() {
            const overlay = document.querySelector('#me');
            const popup   = overlay.querySelector('.form-section');
            // play the reverse animation
            popup.style.animation = 'popupSlide 0.2s ease-in reverse';
            // when the animation finishes, hide the overlay
            popup.addEventListener('animationend', () => {
            overlay.style.display = 'none';
            // reset inline style so next open works
            popup.style.animation = '';
            }, { once: true });
        }

        function changePhoto() {
            document.getElementById('photoInput').click();
        }

        function handlePhotoChange(event) {
            const file = event.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(e) {
                const profilePhoto = document.getElementById('profilePhoto');
                // ← correct:
                profilePhoto.style.backgroundImage = `url("${e.target.result}")`;
                profilePhoto.style.backgroundSize = 'cover';
                profilePhoto.style.backgroundPosition = 'center';
                profilePhoto.textContent = '';
            };
            reader.readAsDataURL(file);
            }

        function toggleStatus() {
            const toggle = document.getElementById('statusToggle');
            const statusText = document.getElementById('statusText');
            
            isAvailable = !isAvailable;
            
            if (isAvailable) {
                toggle.classList.add('active');
                statusText.textContent = 'Disponible aujourd\'hui';
                statusText.style.color = '#10b981';
            } else {
                toggle.classList.remove('active');
                statusText.textContent = 'Non disponible';
                statusText.style.color = '#ef4444';
            }
        }
        function openProfilePopup(){
        // 1) Grab the overlay and popup elements
        const overlay = document.querySelector('.overlay');
        const popup   = overlay.querySelector('.popup');

        // 2) Make sure the overlay is hidden by default in your CSS:
        //    .overlay { display: none; }
        //    .overlay.show { display: flex; }
        // 3) Show it
        overlay.classList.add('show');

        // 4) Play the slide‐in animation
        popup.style.animation = 'popupSlide 0.3s ease-out';
        }

        function saveProfile() {
            // Récupérer toutes les valeurs du formulaire
            const formData = {
                firstName: document.getElementById('firstName').value,
                lastName: document.getElementById('lastName').value,
                specialty: document.getElementById('specialty').value,
                address: document.getElementById('address').value,
                phone: document.getElementById('phone').value,
                fax: document.getElementById('fax').value,
                email: document.getElementById('email').value,
                isAvailable: isAvailable
            };
            function closePopup() {
            const overlay = document.getElementById('profileForm');
            const popup   = overlay.querySelector('.popup');
            // play reverse animation
            popup.style.animation = 'popupSlide 0.2s ease-in reverse';
            // once that's done, hide the overlay
            popup.addEventListener('animationend', () => {
                overlay.classList.remove('show');
                popup.style.animation = '';
            }, { once: true });
            }
            // Simulation de sauvegarde
            console.log('Données sauvegardées:', formData);
            
            // Animation de succès
            const saveBtn = document.querySelector('.btn-save');
            const originalText = saveBtn.textContent;
            saveBtn.textContent = '✓ Enregistré';
            saveBtn.style.background = '#10b981';
            
            setTimeout(() => {
                saveBtn.textContent = originalText;
                saveBtn.style.background = '#4285f4';
                alert('Profil mis à jour avec succès !');
            }, 1500);
        }

        // Empêcher la fermeture du popup en cliquant sur le contenu
        document.querySelector('.popup').addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // Fermer en cliquant sur l'overlay
        document.querySelector('.overlay').addEventListener('click', closeePopup);

        // Fermer avec la touche Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeePopup();
            }
        });
</script>
<!-- 2️ Le conteneur de la popup -->
<div id="scheduleModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Modifier les horaires de consultation</h3>
      <button class="close-btn" onclick="closeScheduleModal()">&times;</button>
    </div>


<form id="scheduleForm" method="post" action="">
  <input type="hidden" name="save_schedule" value="1">
   <input type="hidden" name="prestataire_id" value="<?= intval($prestaId) ?>">
      <!-- 3️Ici on injectera dynamiquement un bloc <div class="day-editor"> par jour -->
      <div id="day-editors"></div>

      <div class="modal-actions">
        <button type="button" class="btn btn-cancel" onclick="closeScheduleModal()">Annuler</button>
        <button type="submit" class="btn btn-save">Enregistrer</button>
      </div>
    </form>
  </div>
</div>
<script>
  // Données d’exemple (à remplacer par tes vraies données)
  const scheduleData = {
    lundi:    { open: true,  start: '08:30', end: '13:30' },
    mardi:    { open: true,  start: '14:00', end: '20:00' },
    mercredi: { open: true,  start: '09:00', end: '23:00' },
    jeudi:    { open: true,  start: '09:00', end: '19:00' },
    vendredi: { open: false, start: '09:00', end: '17:00' },
    samedi:   { open: false, start: '09:00', end: '17:00' },
    dimanche: { open: false, start: '09:00', end: '17:00' }
  };
  const dayNames = {
    lundi: 'Lundi', mardi: 'Mardi', mercredi: 'Mercredi',
    jeudi: 'Jeudi', vendredi: 'Vendredi', samedi: 'Samedi',
    dimanche: 'Dimanche'
  };

  function createDayEditor(key) {
    const day = scheduleData[key];
    const div = document.createElement('div');
    div.className = 'day-editor';
    div.innerHTML = `
      <div class="day-editor-header">
        <div class="day-editor-name">${dayNames[key]}</div>
        <label class="toggle-switch">
          <input type="checkbox" ${day.open?'checked':''}  name="open_${key}" onchange="toggleDay('${key}',this.checked)">
          <span class="slider"></span>
        </label>
      </div>
      <div class="time-inputs" id="times-${key}"  style="${day.open?'':'display:none'}">
        <div class="time-input-group">
          <label>Ouverture</label>
          <input type="time" class="time-input" id="start-${key}"  name="start_${key}"  value="${day.start}">
        </div>
        <div class="time-input-group">
          <label>Fermeture</label>
          <input type="time" class="time-input" id="end-${key}" name="end_${key}" value="${day.end}">
        </div>
      </div>`;
      
    return div;
  }

  function toggleDay(key, open) {
    scheduleData[key].open = open;
    document.getElementById(`times-${key}`).style.display = open?'flex':'none';
  }

  function openScheduleModal() {
    const modal = document.getElementById('scheduleModal');
    const container = document.getElementById('day-editors');
    container.innerHTML = '';
    Object.keys(scheduleData).forEach(k => container.appendChild(createDayEditor(k)));
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
  }
  function closeScheduleModal() {
    document.getElementById('scheduleModal').classList.remove('active');
    document.body.style.overflow = '';
  }

  // Soumission du formulaire
  document.getElementById('scheduleForm').addEventListener('submit', e => {
    
    // Récupère les valeurs
    Object.keys(scheduleData).forEach(k => {
      if (scheduleData[k].open) {
        scheduleData[k].start = document.getElementById(`start-${k}`).value;
        scheduleData[k].end   = document.getElementById(`end-${k}`).value;
      }
    });
    closeScheduleModal();
    console.log('Horaires mis à jour :', scheduleData);
    // Ici tu peux faire ton AJAX / submit réel
  });

  // Fermer au clic extérieur ou à Échap
  document.getElementById('scheduleModal').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeScheduleModal();
  });
  document.addEventListener('keydown', e => e.key==='Escape' && closeScheduleModal());
</script>

</body>
</html>