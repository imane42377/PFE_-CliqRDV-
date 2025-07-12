<?php
session_start();

require_once 'config.php'; 
$conn = mysqli_connect($host, $user, $password, $dbname);
if (mysqli_connect_errno()) {
    die("Erreur de connexion MySQL : " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['id'], $_POST['date_rdv'], $_POST['heure_rdv'])
) {
    // Récupère et sécurise
    $rdvId = intval($_POST['id']);
    $date  = $_POST['date_rdv'];   // format YYYY-MM-DD
    $time  = $_POST['heure_rdv'];  // format HH:MM

    // Prépare et exécute la requête
    $stmt = mysqli_prepare(
      $conn,
      "UPDATE rendezvous
         SET date_rdv  = ?,
             heure_rdv = ?
       WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, 'ssi', $date, $time, $rdvId);

    if (mysqli_stmt_execute($stmt)) {
        // succès → on recharge la page pour afficher les nouveaux horaires
        header('Location: ' . $_SERVER['PHP_SELF'] . '?modif=success');
        exit;
    } else {
        // échec → on stocke l’erreur pour l’afficher sous la popup
        $errorMsg = "Erreur lors de la mise à jour : " . mysqli_stmt_error($stmt);
    }
}
if ($_SERVER['REQUEST_METHOD']==='POST' && !empty($_POST['delete_rdv_id'])) {
  $idRdv = intval($_POST['delete_rdv_id']);
  $userId = intval($_SESSION['user_id']);

  // Sécurité : vérifier que le RDV t'appartient
  $sql = "
    SELECT r.id
      FROM rendezvous r
      JOIN prestataire p ON p.id = r.presta
     WHERE r.id = $idRdv
       AND (p.prestataire_id = $userId
            OR p.email = '".mysqli_real_escape_string($conn,$_SESSION['user_email'])."')
  ";
  $res = mysqli_query($conn,$sql);
  if ($res && mysqli_num_rows($res)>0) {
    mysqli_query($conn,"DELETE FROM rendezvous WHERE id = $idRdv");
    // tu peux stocker un message flash si tu veux
  }
 // récupère ton idPres depuis la session ou la requête GET initiale
$idPres = intval($_SESSION['idP'] ?? $_GET['x'] ?? 0);

// suppression du RDV...
mysqli_query($conn, "DELETE FROM rendezvous WHERE id = $idRdv");

// puis, redirection en réinjectant x
header("Location: dashboardDoc.php?x={$idPres}");
exit;
}
if ($_SERVER['REQUEST_METHOD']==='POST' && !empty($_POST['delete_rdv_id'])) {
    $idRdv  = intval($_POST['delete_rdv_id']);
    $idUser = intval($_SESSION['user_id']);
    // on récupère l'id du prestataire pour la redirection
    $idPres = intval($_SESSION['idP'] ?? $_GET['x'] ?? 0);

    // (optionnel) on vérifie que ce RDV appartient bien à ce prestataire
    $sql = "
      SELECT r.id
        FROM rendezvous r
        JOIN prestataire p ON p.id = r.presta
       WHERE r.id = $idRdv
         AND (p.prestataire_id = $idUser
              OR p.email = '".mysqli_real_escape_string($conn,$_SESSION['user_email'])."')
    ";
    $res = mysqli_query($conn, $sql);
    if ($res && mysqli_num_rows($res)>0) {
      mysqli_query($conn, "DELETE FROM rendezvous WHERE id = $idRdv");
    }
    // on redirige en réinjectant x pour ne pas perdre le contexte
    header("Location: dashboardDoc.php?x={$idPres}");
    exit;
}
if (isset($_SESSION['user_email'])) {
    $email=$_SESSION['user_email'];
    $id=$_SESSION['user_id'];
    $req="select * from prestataire where prestataire_id='$id' or email='$email'";
    $res=mysqli_query($conn , $req);
      if ($res && mysqli_num_rows($res) > 0) {
    $rows =mysqli_fetch_assoc($res);
    
}
$idPres= $rows['id'];
  $rdv_pass = "SELECT COUNT(*) as total FROM rendezvous WHERE date_rdv < NOW() AND presta = '$idPres'";
    $res_pass  = mysqli_query($conn, $rdv_pass);
    if ($res_pass && mysqli_num_rows($res_pass ) > 0) {
        $row_pass  = mysqli_fetch_assoc($res_pass );
    }

    $rdv_venir = "SELECT COUNT(*) as total FROM rendezvous WHERE date_rdv > NOW() AND presta = '$idPres'";
    $res_venir = mysqli_query($conn, $rdv_venir);
    if ($res_venir && mysqli_num_rows($res_venir ) > 0) {
        $row_venir  = mysqli_fetch_assoc($res_venir);
        
    }
 $rdv_hist="select * from prestataire , rendezvous , client where rendezvous.presta='$idPres' and rendezvous.presta = prestataire.id and client.client_id=rendezvous.client and date_rdv < NOW()   ";
  $res_hist=mysqli_query($conn, $rdv_hist);

 $rdv_proch="select * from prestataire , rendezvous ,client where rendezvous.presta='$idPres' and rendezvous.presta = prestataire.id and client.client_id=rendezvous.client and date_rdv < NOW()  ";
  $res_proch=mysqli_query($conn, $rdv_proch);

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

}
// Simulation des données du médecin (en production, récupérer depuis la base de données)



// Simulation des notifications
$notifications = [
    ['message' => 'Rappel : Consultation avec Marie Dubois demain à 14h30.', 'type' => 'info'],
    ['message' => 'Nouveau rendez-vous en attente de confirmation.', 'type' => 'warning']
];

// Fonction pour afficher les badges de statut
function getStatutBadge($statut) {
    switch ($statut) {
        case 'info':
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
    <title>Tableau de Bord - Médecin</title>
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

        header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(30, 58, 138, 0.15);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
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
            margin-top: 100px;
        }

        .welcome-card, .stats-card, .rdv-card, .notification-card, .table-responsive {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 10px 40px rgba(30, 58, 138, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.1);
            transition: all 0.3s ease;
        }

        .stats-card:hover, .rdv-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(30, 58, 138, 0.15);
            border-color: rgba(59, 130, 246, 0.3);
        }

        .rdv-card {
            border-left: 4px solid #3b82f6;
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
        }

        .section-title {
            color: #0f172a;
            font-weight: 600;
            margin-bottom: 1.5rem;
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(90deg, #3b82f6, #1d4ed8);
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
    </style>
</head>
<body>
    <!-- Navigation -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container">
                <div class="logo">CliqRDV</div>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link active" href="#"><i class="fas fa-tachometer-alt me-1"></i>Tableau de Bord</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="calendrier.php?x=<?= $rows['id'] ?>"><i class="fas fa-calendar me-1"></i>Calendrier</a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="profilePrestataire1.php"><i class="fas fa-user me-1"></i>Profil</a>
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
    </header>

    <!-- Contenu principal -->
    <div class="container main-content">
        <!-- Carte de bienvenue -->
        <div class="welcome-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2>
                 <?php
                     if (!empty($rows['photo_profil']) && file_exists(__DIR__ . '/' . $rows['photo_profil'])) {
                     echo '<img src="' . htmlspecialchars($rows['photo_profil'], ENT_QUOTES, 'UTF-8') . '" alt="Photo de profil" class="rounded-circle me-2" style="width: 90px; height: 90px; object-fit: cover;">';
                     } else {
                        echo '<i class="fas fa-user-md me-2"></i>';
                     }
                  
                    $heure = date("H"); 
                    if ($heure >= 5 && $heure < 12) {
                        $salutation = "Bonjour";
                    } elseif ($heure >= 12 && $heure < 18) {
                        $salutation = "Bon après-midi";
                    } else {
                        $salutation = "Bonsoir";
                    }

                    echo $salutation . ', Dr ' . htmlspecialchars($rows['nom_prenom'], ENT_QUOTES, 'UTF-8') . ' !';
                    ?>
                    </h2>
                    <p class="mb-0">Gérez vos rendez-vous et consultez vos statistiques en un clin d'œil.</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="calendrier.php" class="btn btn-primary-custom">
                        <i class="fas fa-calendar-plus me-2"></i>Planifier
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistiques rapides -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card text-center">
                    <i class="fas fa-calendar-check text-success" style="font-size: 2rem;"></i>
                    <h4 class="mt-2"><?php echo$row_venir['total']; ?></h4>
                    <p class="text-muted mb-0">RDV à venir</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card text-center">
                    <i class="fas fa-history text-info" style="font-size: 2rem;"></i>
                    <h4 class="mt-2"><?php echo $row_pass ['total']; ?></h4>
                    <p class="text-muted mb-0">RDV passés</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card text-center">
                    <i class="fas fa-bell text-warning" style="font-size: 2rem;"></i>
                    <h4 class="mt-2"><?php echo count($notifications); ?></h4>
                    <p class="text-muted mb-0">Notifications</p>
                </div>
            </div>
        </div>

        <!-- Rendez-vous à venir -->
        <h3 class="section-title"><i class="fas fa-calendar-alt me-2"></i>Prochains rendez-vous</h3>
        <?php if($res_proch && mysqli_num_rows($res_proch)>0) 
                {
                foreach($res_proch as $rdv): ?>
                
            <div class="row align-items-center rdv-card">
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="h5 text-primary mb-0"><?php echo date('d', strtotime($rdv['date_rdv'])); ?></div>
                        <small class="text-muted"><?php echo strftime('%B %Y', strtotime($rdv['date_rdv'])); ?></small>
                        <div class="mt-1"><strong><?php echo $rdv['heure_rdv']; ?></strong></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <h5 class="mb-1"><?php echo 'ok'; ?></h5>
                    <p class="mb-1"><i class="fas fa-user me-1"></i><?php echo htmlspecialchars($rdv['nom_complet']); ?></p>
                    <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($rdv['adresse']); ?></small>
                </div>
                <div class="col-md-3 text-end">
                    <?php// echo getStatutBadge($rdv['statut']); ?>
                    <div class="mt-2">
                       <button
                            class="btn btn-sm btn-outline-primary me-1"
                            title="Modifier"
                            onclick="openPopup(this)"
                            data-id="<?= htmlspecialchars($rdv['id'], ENT_QUOTES) ?>"
                            data-date="<?= htmlspecialchars($rdv['date_rdv'], ENT_QUOTES) ?>"
                            data-time="<?= htmlspecialchars($rdv['heure_rdv'], ENT_QUOTES) ?>"
                            >
                            <i class="fas fa-edit"></i>
                            </button>
                      <form method="POST" action ='' onsubmit="return confirm('Êtes-vous sûr de vouloir annuler ce rendez-vous ?')" style="display:inline">
                    <input type="hidden" name="delete_rdv_id"
                            value="<?= (int)$rdv['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Annuler">
                        <i class="fas fa-times"></i>
                    </button>
                    </form>
                    </div>
                </div>
            </div>
        <?php endforeach;} 
        else {echo "<script> alert ('ok'); </script>" ;}?>

        <!-- Notifications 
        <h3 class="section-title mt-4"><i class="fas fa-bell me-2"></i>Notifications récentes
            <span class="notification-badge"><?php echo count($notifications); ?></span>
        </h3>
        <div class="notification-card">
            <?php foreach ($notifications as $notif): ?>
                <div class="alert alert-<?php echo $notif['type']; ?> py-2">
                    <small><i class="fas fa-<?php echo $notif['type'] === 'info' ? 'info-circle' : 'exclamation-triangle'; ?> me-1"></i>
                    <?php echo htmlspecialchars($notif['message']); ?></small>
                </div>
            <?php endforeach; ?>
        </div>-->

        <!-- Historique -->
       <h3 class="section-title mt-4">
  <i class="fas fa-history me-2"></i>Historique des rendez-vous
</h3>
<?php if($res_hist && mysqli_num_rows($res_hist)>0): ?>
  <div class="table-responsive">
    <table class="table table-hover">
      <thead class="table-light">
        <tr>
          <th>Date</th>
          <th>Patient</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($res_hist as $rdv): ?>
          <tr>
            <td>
              <?= date('d/m/Y à H:i',
                  strtotime($rdv['date_rdv'].' '.$rdv['heure_rdv']))
              ?>
            </td>
            <td><?= htmlspecialchars($rdv['nom_complet']) ?></td>
            <td>
              <!-- Formulaire POST pour déclencher la suppression -->
              <form method="POST" style="display:inline"
                    onsubmit="return confirm('Supprimer ce rendez-vous ?');">
                <input type="hidden"
                       name="delete_rdv_id"
                       value="<?= (int)$rdv['id'] ?>">
                <button type="submit"
                        class="btn btn-sm btn-outline-danger"
                        title="Supprimer">
                  <i class="fas fa-times"></i>
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php else: ?>
  <p>Aucun historique !</p>
<?php endif; ?>


    <!--script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script-->
        <script src="./bootstrap/bootstrap.bundle.min.js"></script>

    <script>
        // Animation des cartes au chargement
        document.addEventListener('DOMContentLoaded', () => {
            const cards = document.querySelectorAll('.stats-card, .rdv-card, .notification-card');
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
        }
    </script>
    <div class="popup-overlay" id="popupOverlay">
  <div class="popup-container">
    <div class="popup-header">
      <span class="popup-title">Modifier le rendez-vous</span>
      <button class="close-btn" onclick="closePopup()">&times;</button>
    </div>
    <div class="popup-content">
      <form method="post" action="" id="appointmentForm">
        <input type="hidden" name="id" id="appointmentId">
        <div class="form-row">
          <div class="form-group">
            <label for="date">Date</label>
            <input type="date" name="date_rdv" id="date" class="form-input" required>
          </div>
          <div class="form-group">
            <label for="time">Heure</label>
            <input type="time" name="heure_rdv" id="time" class="form-input" required>
          </div>
        </div>
        <div class="popup-actions">
          <button type="button" class="btn btn-cancel" onclick="closePopup()">Annuler</button>
          <button type="submit" class="btn btn-save">Enregistrer</button>
        </div>
      </form>
      <!-- Affichage d’erreur si nécessaire -->
      <?php if (!empty($errorMsg)): ?>
        <div class="alert alert-danger mt-3"><?= htmlspecialchars($errorMsg) ?></div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- 3) Le CSS (à placer dans votre <head> ou votre fichier CSS) -->
<style>
.popup-overlay {
  position: fixed; inset: 0;
  background: rgba(0,0,0,0.5);
  display: none; align-items: center; justify-content: center;
  z-index: 1000;
}
.popup-overlay.show { display: flex; }
.popup-container {
  background: white;
  border-radius: 12px;
  width: 90%; max-width: 500px;
  animation: slideIn 0.3s ease-out;
  overflow: hidden;
}
@keyframes slideIn {
  from { opacity: 0; transform: translateY(-20px) }
  to   { opacity: 1; transform: translateY(0) }
}
.popup-header {
  background: #357abd; color: white;
  padding: 16px; display: flex; justify-content: space-between; align-items: center;
}
.popup-title { font-size: 18px; }
.close-btn {
  background: none; border: none;
  color: white; font-size: 24px; cursor: pointer;
}
.popup-content { padding: 20px; }
.form-row { display: flex; gap: 16px; }
.form-group { flex: 1; display: flex; flex-direction: column; }
.form-group label { margin-bottom: 4px; font-weight: 600; }
.form-input {
  padding: 8px; border: 1px solid #ccc; border-radius: 6px;
}
.popup-actions {
  margin-top: 20px; display: flex; justify-content: flex-end; gap: 8px;
}
.btn-cancel {
  background: #6c757d; color: white; border: none; padding: 8px 16px; border-radius: 6px;
}
.btn-save {
  background: #28a745; color: white; border: none; padding: 8px 16px; border-radius: 6px;
}
</style>

<!-- 4) Le JavaScript (juste avant la fin de </body>) -->
<script>
function openPopup(button) {
  const id   = button.dataset.id;
  const date = button.dataset.date;
  const time = button.dataset.time;

  document.getElementById('appointmentId').value   = id;
  document.getElementById('date').value            = date;
  document.getElementById('time').value            = time;

  const overlay = document.getElementById('popupOverlay');
  overlay.classList.add('show');
}

function closePopup() {
  document.getElementById('popupOverlay').classList.remove('show');
}

// Optionnel : fermer au clic extérieur ou à Échap
document.getElementById('popupOverlay').addEventListener('click', e => {
  if (e.target.id === 'popupOverlay') closePopup();
});
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') closePopup();
});
</script>
</body>
</html>