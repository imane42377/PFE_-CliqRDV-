<?php
session_start();

require_once 'config.php'; 
$conn = mysqli_connect($host, $user, $password, $dbname);
if (mysqli_connect_errno()) {
    die("Erreur de connexion MySQL : " . mysqli_connect_error());
}

if (isset($_SESSION['user_email'])) {
    $email=$_SESSION['user_email'];
    $id=$_SESSION['user_id'];
    $req="select * from client where client_id='$id' or email='$email'";
    $res=mysqli_query($conn , $req);
      if ($res && mysqli_num_rows($res) > 0) {
    $row=mysqli_fetch_assoc($res);
    
}

  $rdv_pass = "SELECT COUNT(*) as total FROM rendezvous WHERE date_rdv < NOW() AND client = '$id'";
$res_pass  = mysqli_query($conn, $rdv_pass);
if ($res_pass && mysqli_num_rows($res_pass ) > 0) {
    $row_pass  = mysqli_fetch_assoc($res_pass );
}

    $rdv_venir = "SELECT COUNT(*) as total FROM rendezvous WHERE date_rdv > NOW() AND client = '$id'";
    $res_venir = mysqli_query($conn, $rdv_venir);
    if ($res_venir && mysqli_num_rows($res_venir ) > 0) {
        $row_venir  = mysqli_fetch_assoc($res_venir);
        
    }
 $rdv_hist="select * from prestataire , rendezvous where rendezvous.client='$id' and rendezvous.presta = prestataire.id and date_rdv < NOW()  ";
  $res_hist=mysqli_query($conn, $rdv_hist);

  $rdv_proch="select * from prestataire , rendezvous where rendezvous.client='$id' and rendezvous.presta = prestataire.id and date_rdv > NOW()  ";
  $res_proch=mysqli_query($conn, $rdv_proch);


// … your mysqli_connect() here …

//  ––––– Delete logic –––––
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delId = intval($_POST['delete_id']);
    $sql   = "DELETE FROM rendezvous WHERE id = $delId";
    if (mysqli_query($conn, $sql)) {
        header('Location: ' . $_SERVER['PHP_SELF'] . '?deleted=1');
        exit;
    } else {
        $errorMsg = "Erreur lors de la suppression : " . mysqli_error($conn);
    }
}

// … then your existing POST-update block and SELECTs …



if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id'])) {
    // Sanitize inputs
    $id   = intval($_POST['id']);
    $date = mysqli_real_escape_string($conn, $_POST['date_rdv']);
    $time = mysqli_real_escape_string($conn, $_POST['heure_rdv']);

    // Perform the update
    $sql = "
      UPDATE rendezvous
      SET date_rdv  = '$date',
          heure_rdv = '$time'
      WHERE id   = $id
    ";

    if (mysqli_query($conn, $sql)) {
        // Redirect to avoid resubmission on refresh
        header('Location: ' . $_SERVER['PHP_SELF'] . '?modif=success');
        exit;
    } else {
        $errorMsg = "Erreur de mise à jour : " . mysqli_error($conn);
    }
}



}

// Simulation des données utilisateur (en production, récupérer depuis la base de données)
$user = [
    'nom' => 'Martin Dubois',
    'email' => 'martin.dubois@email.com',
    'telephone' => '06 12 34 56 78'
];

// Simulation des rendez-vous
$rendez_vous_a_venir = [
    [
        'id' => 1,
        'date' => '2025-06-15',
        'heure' => '14:30',
        'service' => 'Consultation médicale',
        'prestataire' => 'Dr. Sophie Martin',
        'statut' => 'confirmé',
        'adresse' => '15 Rue de la Santé, Paris'
    ],
    [
        'id' => 2,
        'date' => '2025-06-20',
        'heure' => '10:00',
        'service' => 'Coiffure',
        'prestataire' => 'Salon Beauté',
        'statut' => 'en_attente',
        'adresse' => '23 Avenue des Champs, Paris'
    ]
];

$historique_rdv = [
    [
        'date' => '2025-05-15',
        'heure' => '16:00',
        'service' => 'Dentiste',
        'prestataire' => 'Dr. Pierre Blanc',
        'statut' => 'terminé'
    ],
    [
        'date' => '2025-04-20',
        'heure' => '09:30',
        'service' => 'Kinésithérapeute',
        'prestataire' => 'Cabinet Kiné Plus',
        'statut' => 'terminé'
    ]
];

function getStatutBadge($statut) {
    switch($statut) {
        case 'confirmé':
            return '<span class="badge bg-success">Confirmé</span>';
        case 'en_attente':
            return '<span class="badge bg-warning">En attente</span>';
        case 'annulé':
            return '<span class="badge bg-danger">Annulé</span>';
        case 'terminé':
            return '<span class="badge bg-secondary">Terminé</span>';
        default:
            return '<span class="badge bg-light">Inconnu</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Tableau de Bord - RDV en Ligne</title>
    <link href="./bootstrap/bootstrapajax.min.css" rel="stylesheet">
    <link href="./bootstrap/fontawesome/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #1e3a8a;
            background: linear-gradient(135deg, rgb(100, 184, 253) 0%, rgb(66, 168, 252) 100%);
            min-height: 100vh;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(30, 58, 138, 0.15);
        }


        .navbar-nav .nav-link {
            color: #1e3a8a !important;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .navbar-nav .nav-link:hover {
            color: #3b82f6 !important;
            transform: translateY(-2px);
        }

        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 50%;
            background: linear-gradient(90deg, #3b82f6, #1d4ed8);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .navbar-nav .nav-link:hover::after {
            width: 100%;
        }

        .main-content {
            margin-top: 2rem;
        }

        .welcome-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            color: #1e3a8a;
            border-radius: 20px;
            padding: 2rem;
            margin-top: 120px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(30, 58, 138, 0.15);
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .stats-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 10px 40px rgba(30, 58, 138, 0.1);
            transition: all 0.3s ease;
            border: 1px solid rgba(59, 130, 246, 0.1);
            position: relative;
            overflow: hidden;
        }

        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: auto;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.1), transparent);
            transition: all 0.5s ease;
        }

        .stats-card:hover::before {
            left: 100%;
        }

        .stats-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(30, 58, 138, 0.15);
            border-color: rgba(59, 130, 246, 0.3);
        }

        .rdv-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 10px 40px rgba(30, 58, 138, 0.1);
            border-left: 4px solid #3b82f6;
            border: 1px solid rgba(59, 130, 246, 0.1);
            transition: all 0.3s ease;
              max-width: 1200px;
        }

        .rdv-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(30, 58, 138, 0.15);
        }

        .btn-primary-custom {
            background: linear-gradient(45deg, #3b82f6, #1d4ed8);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
            background: linear-gradient(45deg, #1d4ed8, #1e40af);
            color: white;
        }

        .section-title {
            color: #0f172a;
            font-weight: 600;
            margin-bottom: 1.5rem;
            position: relative;
            text-shadow: 0 2px 4px rgba(255, 255, 255, 0.3);
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(90deg, #3b82f6, #1d4ed8);
            border-radius: 2px;
        }

        .notification-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 10px 40px rgba(30, 58, 138, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.1);
        }

        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .table-responsive {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 1rem;
            box-shadow: 0 10px 40px rgba(30, 58, 138, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.1);
        }

        .table {
            color: #1e3a8a;
        }

        .table-light {
            background-color: rgba(240, 248, 255, 0.8);
        }

        .btn-outline-primary {
            border-color: #3b82f6;
            color: #3b82f6;
        }

        .btn-outline-primary:hover {
            background-color: #3b82f6;
            border-color: #3b82f6;
            transform: translateY(-1px);
        }

        .btn-outline-danger {
            border-color: #ef4444;
            color: #ef4444;
        }

        .btn-outline-danger:hover {
            background-color: #ef4444;
            border-color: #ef4444;
            transform: translateY(-1px);
        }

        .btn-outline-success {
            border-color: #10b981;
            color: #10b981;
        }

        .btn-outline-success:hover {
            background-color: #10b981;
            border-color: #10b981;
            transform: translateY(-1px);
        }

        @media (max-width: 768px) {
            .main-content {
                margin-top: 1rem;
            }
            
            .welcome-card {
                padding: 1.5rem;
            }
        }

        .logo {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    font-size: 1.8rem;
                    font-weight: bold;
                    color: #2563eb;
                }

                .logo::before {
                    content: "📅";
                    font-size: 2rem;
                }


        header {
                    background: rgba(255, 255, 255, 0.95);
                    backdrop-filter: blur(10px);
                    box-shadow: 0 2px 20px rgba(30, 58, 138, 0.15);
                    position: fixed;
                    width: 100%;
                    top: 0;
                    z-index: 1000;
                    transition: all 0.3s ease;
                }


    </style>
</head>
<body>
    <!-- Navigation -->
     <header>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <!--a class="navbar-brand" href="#"-->
                <div class="logo">CliqRDV</div>
            <!--/a-->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php"><i class="fas fa-tachometer-alt me-1"></i>Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profileClient.php"><i class="fas fa-user me-1"></i>Mon Profil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-bell me-1"></i>Notifications</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i>Déconnexion</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
     <title>Modifier le Rendez-vous</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .popup-overlay.show {
            display: flex;
        }

        .popup-container {
            background: white;
            border-radius: 12px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .popup-header {
            background: linear-gradient(135deg, #4a90e2, #357abd);
            color: white;
            padding: 20px 25px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .popup-title {
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }

        .close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 28px;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background-color 0.2s;
        }

        .close-btn:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .popup-content {
            padding: 30px 25px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: white;
        }

        .form-input:focus {
            outline: none;
            border-color: #4a90e2;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .popup-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 30px;
        }

        .popup-actions .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            min-width: 120px;
        }

        .btn-cancel {
            background-color: #6c757d;
            color: white;
        }

        .btn-cancel:hover {
            background-color: #5a6268;
            transform: translateY(-1px);
        }

        .btn-save {
            background-color: #28a745;
            color: white;
        }

        .btn-save:hover {
            background-color: #218838;
            transform: translateY(-1px);
        }

        .input-icon {
            position: relative;
        }

        .input-icon::after {
            content: '';
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            background-size: contain;
            background-repeat: no-repeat;
            pointer-events: none;
        }

        .date-input::after {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23666'%3E%3Cpath d='M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z'/%3E%3C/svg%3E");
        }

        .time-input::after {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23666'%3E%3Cpath d='M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M16.2,16.2L11,13V7H12.5V12.2L17,14.7L16.2,16.2Z'/%3E%3C/svg%3E");
        }

        @media (max-width: 600px) {
            .popup-container {
                margin: 20px;
                max-width: none;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .popup-actions {
                flex-direction: column;
            }
            
            .popup-actions .btn {
                width: 100%;
            }
        }
    </style>
</header>
    <div class="container main-content">
        <!-- Carte de bienvenue -->
        <div class="welcome-card">
            <div class="row align-items-center">
                <div class="col-md-8">
            <h2 style="font-weight: bolder">
                        
        <?php
        // Vérifie si une photo existe et si le fichier est accessible
        if (!empty($row['photo']) && file_exists(__DIR__ . '/' . $row['photo'])) {
            echo '<img src="' . htmlspecialchars($row['photo'], ENT_QUOTES, 'UTF-8') . '" alt="Photo de profil" class="rounded-circle me-2" style="width: 90px; height: 90px; object-fit: cover;">';
        } else {
            echo '<i class="fas fa-user-circle me-2"></i>';
        }

        // Détermine la salutation en fonction de l'heure
        $heure = date("H"); // l'heure en format 24h
        if ($heure >= 5 && $heure < 12) {
            $salutation = "Bonjour";
        } elseif ($heure >= 12 && $heure < 18) {
            $salutation = "Bon après-midi";
        } else {
            $salutation = "Bonsoir";
        }

        echo "\t".$salutation . ", \n" . htmlspecialchars($row['nom_complet'], ENT_QUOTES, 'UTF-8') . ' !';
        ?>
                    </h2>
                    <p class="mb-0" >Bienvenue sur votre tableau de bord. Gérez facilement tous vos rendez-vous.</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="rendez-vousClient.php" class="btn btn-light btn-lg btn-primary-custom">
                        <i class="fas fa-plus me-2"></i>Nouveau RDV
                    </a>
                </div>
            </div>
        </div>
                <!-- Statistiques rapides -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stats-card text-center">
                            <i class="fas fa-calendar-check text-success" style="font-size: 2rem;"></i>
                            <h4 class="mt-2"><?php echo $row_venir['total']; ?></h4>
                            <p class="text-muted mb-0">RDV à venir</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card text-center">
                            <i class="fas fa-history text-info" style="font-size: 2rem;"></i>
                            <h4 class="mt-2"><?php echo  $row_pass ['total']; ?></h4>
                            <p class="text-muted mb-0">RDV passés</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card text-center">
                            <i class="fas fa-bell text-warning" style="font-size: 2rem;"></i>
                            <h4 class="mt-2">2</h4>
                            <p class="text-muted mb-0">Notifications</p>
                        </div>
                    </div>
                </div>

                <!-- Rendez-vous à venir -->
                <h3 class="section-title">
                    <i class="fas fa-calendar-alt me-2"></i>Mes prochains rendez-vous
                </h3>
                
                <?php
                if($res_proch && mysqli_num_rows($res_proch)>0) 
                {
                foreach($res_proch as $rdv): ?>
        
                    <div class="row align-items-center rdv-card" style="margin:50px;">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h5 text-primary mb-0"><?php echo date('d', strtotime($rdv['date_rdv'])); ?></div>
                                <small class="text-muted"><?php echo strftime('%B %Y', strtotime($rdv['date_rdv'])); ?></small>
                                <div class="mt-1">
                                    <strong><?php echo $rdv['heure_rdv']; ?></strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-1"><?php echo $rdv['specialite']; ?></h5>
                            <p class="mb-1"><i class="fas fa-user-md me-1"></i><?php echo $rdv['nom_prenom']; ?></p>
                            <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i><?php echo $rdv['localisation']; ?></small>
                        </div>
                        <div class="col-md-3 text-end">
                            <?php// echo getStatutBadge($rdv['statut']); ?>
                            <div class="mt-2">
                                                            
                                <button
                                    class="btn btn-sm btn-outline-primary me-1"
                                    title="Modifier"
                                    onclick="openPopup(this)"
                                    data-id='<?php echo $rdv['id']; ?>'
                                    data-date='<?php echo $rdv['date_rdv']; ?>'
                                    data-time='<?php echo $rdv['heure_rdv']; ?>'>
                                    
                                    <i class="fas fa-edit"></i>
                                </button>
                                 <form 
                                    method="post" 
                                    action="" 
                                    style="display:inline;" 
                                    onsubmit="return confirm('Êtes-vous sûr de vouloir annuler ce rendez-vous ?');"
                                >
                                    <input type="hidden" name="delete_id" value="<?= $rdv['id']; ?>">
                                    <button 
                                    type="submit" 
                                    class="btn btn-sm btn-outline-danger" 
                                    title="Annuler"
                                    >
                                    <i class="fas fa-times"></i>
                                    </button>
                                </form>
                                </div>
                            </div>
                        </div>
                    </div>
              
                <?php endforeach; 
                }else {
                    ?>
                    <div class="row align-items-center rdv-card" style = "font-weight= bold ; margin : 50px ;" >
                        <?php echo "aucun rendez-vous !"; ?>
                    </div>
                    <?php
                }
                ?>

                <!-- Historique -->
                <h3 class="section-title mt-4">
                    <i class="fas fa-history me-2"></i>Historique des rendez-vous
                </h3>
                <?php if($res_hist  && mysqli_num_rows($res_hist )) {?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Service</th>
                                <th>Prestataire</th>
                                <!--th>Statut</th-->
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                             foreach($res_hist as $rdv): ?>
                            <tr>
                                <td><?php echo date('d/m/Y à H:i', strtotime($rdv['date_rdv'] . ' ' . $rdv['heure_rdv'])); ?></td>
                                <td><?php echo $rdv['specialite']; ?></td>
                                <td><?php echo $rdv['nom_prenom']; ?></td>
                                <!--td><?php// echo getStatutBadge($rdv['specialite']); ?></td-->
                                <td>
                                    <form 
                                    method="post" 
                                    action="" 
                                    style="display:inline;" 
                                    onsubmit="return confirm('Êtes-vous sûr de vouloir annuler ce rendez-vous ?');"
                                >
                                    <input type="hidden" name="delete_id" value="<?= $rdv['id']; ?>">
                                    <button 
                                    type="submit" 
                                    class="btn btn-sm btn-outline-danger" 
                                    title="Annuler"
                                    >
                                    <i class="fas fa-times"></i>
                                    </button>
                                </form>
                                </td>
                            </tr>
                            <?php endforeach;
                            ?>

                        </tbody>
                    </table>
                </div>
                 <?php }else{
                                  ?>
                    <div class="row align-items-center rdv-card" style="font-weight= bold ; margin:50px;">
                        <?php echo "aucun historique !"; ?>
                    </div>
                    <?php
                            } ?>


            <!-- Sidebar 
            <div class="col-lg-4">
                <div class="notification-card">
                    <h5 class="section-title">
                        Notifications récentes
                        <span class="notification-badge">2</span>
                    </h5>
                    <div class="alert alert-info py-2">
                        <small><i class="fas fa-info-circle me-1"></i>Rappel: RDV demain à 14h30</small>
                    </div>
                    <div class="alert alert-warning py-2">
                        <small><i class="fas fa-exclamation-triangle me-1"></i>RDV en attente de confirmation</small>
                    </div>
                </div>
            </div>-->

    </div>

    <!--script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script-->
        <script src="./bootstrap/bootstrap.bundle.min.js"></script>

    <script>
        // Animation au chargement
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.stats-card, .rdv-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });

        // Confirmation d'annulation
        document.querySelectorAll('button[title="Annuler"]').forEach(btn => {
            btn.addEventListener('click', function() {
                if(confirm('Êtes-vous sûr de vouloir annuler ce rendez-vous ?')) {
                    // Logique d'annulation ici
                    alert('Rendez-vous annulé avec succès');
                }
            });
        });
    </script>

<div class="popup-overlay" id="popupOverlay">
  <div class="popup-container">
    <div class="popup-header">Modifier le rendez-vous</div>
    <div class="popup-content">
      <form method="post" action="" id="appointmentForm">
        <!-- 1 hidden field INSIDE the form -->
        <input type="hidden" name="id" id="appointmentId">

        <div class="form-row">
          <div class="form-group">
            <label for="date">Date</label>
            <input 
              type="date" 
              name="date_rdv" 
              id="date" 
              class="form-input" 
              required
            >
          </div>
          <div class="form-group">
            <label for="time">Heure</label>
            <input 
              type="time" 
              name="heure_rdv" 
              id="time" 
              class="form-input" 
              required
            >
          </div>
        </div>

        <div class="popup-actions">
          <button type="button" class="btn btn-cancel" onclick="closePopup()">Annuler</button>
          <button type="submit" class="btn btn-save">Enregistrer</button>
        </div>
      </form>

      <!-- show PHP errors here if $errorMsg is set -->
      <?php if (!empty($errorMsg)): ?>
        <div class="alert alert-danger mt-3">
          <?= htmlspecialchars($errorMsg) ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>


    <script>
        function openPopup(button) {
            // Get data from the button that was clicked
            const id = button.getAttribute('data-id');
            const date = button.getAttribute('data-date');
            const time = button.getAttribute('data-time');
            
            // Populate the form with the data
            document.getElementById('appointmentId').value = id;
            document.getElementById('date').value = date;
            document.getElementById('time').value = time;
            
            // Show the popup
            const popup = document.getElementById('popupOverlay');
            popup.classList.add('show');
            popup.style.animation = 'fadeIn 0.3s ease-out';
        }

        function closePopup() {
            const popup = document.getElementById('popupOverlay');
            popup.style.animation = 'fadeOut 0.3s ease-out';
            setTimeout(() => {
                popup.classList.remove('show');
            }, 300);
        }

        // Close popup when clicking outside
        document.getElementById('popupOverlay').addEventListener('click', function(e) {
            if (e.target === this) {
                closePopup();
            }
        });

        // Close popup with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePopup();
            }
        });

        // Handle form submission
        document.getElementById('appointmentForm').addEventListener('submit', function(e) {
            
            
            const id = document.getElementById('appointmentId').value;
            const date = document.getElementById('date').value;
            const time = document.getElementById('time').value;
            
            // Format date for display
            const dateObj = new Date(date);
            const formattedDate = dateObj.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            
            console.log('Appointment data:', {
                id: id,
                date: date,
                time: time
            });
            
            // Here you would typically send the data to your server
            // Example AJAX call:
            /*
            fetch('update_appointment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: id,
                    date: date,
                    time: time
                })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert('Rendez-vous modifié avec succès!');
                    closePopup();
                    // Optionally reload the page or update the UI
                } else {
                    alert('Erreur lors de la modification');
                }
            });
            */
            
            // For demo purposes, show confirmation
            const popup = document.querySelector('.popup-container');
            popup.style.animation = 'pulse 0.3s ease-out';
            
            setTimeout(() => {
                alert(`Rendez-vous #${id} modifié avec succès!\nDate: ${formattedDate}\nHeure: ${time}`);
                closePopup();
            }, 300);
        });

        // Add fade in and fade out animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from {
                    opacity: 0;
                }
                to {
                    opacity: 1;
                }
            }
            
            @keyframes fadeOut {
                from {
                    opacity: 1;
                }
                to {
                    opacity: 0;
                }
            }
            
            @keyframes pulse {
                0%, 100% {
                    transform: scale(1);
                }
                50% {
                    transform: scale(1.02);
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>