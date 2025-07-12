<?php
session_start();
$idC = $_SESSION['user_id'];

require_once 'config.php'; 

$conn = mysqli_connect($host, $user, $password, $dbname);
if (mysqli_connect_errno()) {
    die("Erreur de connexion MySQL : " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $creneauComplet = $_POST['creneau_selectionne'];
        $elements = explode(' ', $creneauComplet); // [2025, juin, 14, 09:30]
        $dateT = $elements[0] . ' ' . $elements[1] . ' ' . $elements[2]; // "2025 juin 14"
        $heure = $elements[3]; // "09:30"
        $date = date('Y-m-d', strtotime($dateT));
        $motif=$_POST['motif'];
    $idP = $_SESSION['pres_id'];

    $reqInsert = "INSERT INTO rendezvous (date_rdv, heure_rdv,motif, presta, client) VALUES ('$date', '$heure','$motif','$idP', '$idC')";
    $resultq = mysqli_query($conn, $reqInsert);
    if($resultq) {
        echo "<script>alert('Rendez-vous confirmé avec succès') ;
        window.location.href = 'dashboardClient.php'; </script>";
    }
    else {
        echo "<script>alert('Rendez-vous $date') ; </script>";
    }
}

if ((isset($_GET['id'])) || (isset($_SESSION['idP']))) {
    $id = $_GET['id'] ?? $_SESSION['idP'];
    $_SESSION['pres_id'] = $id;
    $req = "SELECT * FROM prestataire WHERE id = '$id'";
    $res = mysqli_query($conn, $req);
    if ($res && mysqli_num_rows($res) > 0) {
        $rows = mysqli_fetch_assoc($res);
    } else {
        echo "<script>alert('Prestataire introuvable');</script>";
    }
    
    $reqq = "SELECT * FROM horairestravail WHERE prestataire = '$id'";
    $ress = mysqli_query($conn, $reqq);
    if ($ress && mysqli_num_rows($ress) > 0) {
        $rowss = mysqli_fetch_assoc($ress);
        $disponible = '';
    } else {
        $disponible = 'non';
    }

    setlocale(LC_TIME, 'fr_FR.utf8', 'fra', 'fr_FR', 'fr');
    $jour_semaine = strftime('%A');

    $reqq = "SELECT * FROM horairestravail WHERE prestataire = '$id'";
    $ress = mysqli_query($conn, $reqq);

    $joursSemaine = [];
    if ($ress && mysqli_num_rows($ress) > 0) {
        while ($row = mysqli_fetch_assoc($ress)) {
            $joursSemaine[] = [
                'jour' => strtolower($row['jour_semaine']),
                'debut' => $row['heure_debut'],
                'fin' => $row['heure_fin']
            ];
        }
    }

    $semaineCourante = isset($_GET['semaine']) ? (int)$_GET['semaine'] : 0;
    $dateReference = new DateTime();
    $dateReference->modify($semaineCourante . ' weeks');

    $dateDebutSemaine = clone $dateReference;
    $dateDebutSemaine->modify('wednesday this week');

    $jours = [];
    $datesFormatees = [];
    foreach ($joursSemaine as $jour) {
        $jourNom = $jour['jour'];
        if (!isset($jours[$jourNom])) {
            $dateJour = clone $dateDebutSemaine;
            $datesFormatees[$jourNom] = $dateJour->format('d M Y');
            $jours[$jourNom] = $dateJour->format('d M');
            $dateDebutSemaine->modify('+1 day');
        }
    }

    $urlSemainePrecedente = '?id=' . $id . '&semaine=' . ($semaineCourante - 1);
    $urlSemaineSuivante   = '?id=' . $id . '&semaine=' . ($semaineCourante + 1);

    $urlCetteSemaine = '?semaine=0';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="./bootstrap/bootstrapajax.min.css" rel="stylesheet">
    <link href="./bootstrap/fontawesome/css/all.min.css" rel="stylesheet">
    <title>Réserver un rendez-vous</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .medecin-info {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .medecin-photo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 20px;
            border: 3px solid #3498db;
        }
        
        .medecin-details h2 {
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .medecin-details p {
            color: #7f8c8d;
        }
        
        table {
            border-collapse: collapse;
            width: 100%;
            max-width: 800px;
            margin: 20px auto;
            box-shadow: 0 2px 3px rgba(0,0,0,0.1);
        }
        
        th, td {
            border: 1px solid #e0e0e0;
            padding: 12px 8px;
            text-align: center;
        }
        
        th {
            background-color: #f8f8f8;
            color: #333;
            font-weight: normal;
        }
        
        td {
            background-color: white;
        }
        
        .horaire {
            font-weight: bold;
            color: #333;
            background-color: #f8f8f8 !important;
        }
        
        .navigation {
            display: flex;
            justify-content: space-between;
            max-width: 800px;
            margin: 0 auto 20px;
            align-items: center;
        }
        
        .navigation a {
            padding: 8px 16px;
            text-decoration: none;
            background-color: #6c757d;
            color: white;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .navigation a:hover {
            background-color: #5a6268;
        }
        
        .semaine-courante {
            font-weight: bold;
            color: #333;
        }
        
        .libre {
            color: #999;
        }
        
        td.selectable:hover {
            background-color: #e9ecef;
            cursor: pointer;
        }
        
        td.selectable.selected {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        
        .btn-primary {
            background-color: #2ecc71;
            flex: 2;
        }

        .btn-primary:hover {
            background-color: #27ae60;
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
 /* Style pour le champ Motif */
        .motif-container {
            margin: 20px 0;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .motif-label {
            font-weight: 600;
            color: #2c3e50;
            font-size: 16px;
        }

        .motif-input {
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            color: #333;
            outline: none;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            width: 100%;
        }

        .motif-input:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .motif-input::placeholder {
            color: #7f8c8d;
        }

  
    </style>
</head>
<body>
    <div class="container">
        <button class="exit-button" id="exitBtn1" onclick="exitApplication()" title="Fermer">×</button>
        <script>
            function exitApplication() {
                window.history.back();
            }
        </script>
        <h1>Choisissez votre rendez-vous</h1>
        
        <div class="medecin-info">
            <?php
            if (!empty($rows['photo_profil']) && file_exists(__DIR__ . '/' . $rows['photo_profil'])) {
                echo '<img src="' . htmlspecialchars($rows['photo_profil'], ENT_QUOTES, 'UTF-8') . '" alt="Photo du médecin" class="medecin-photo">';
            } else {
                echo '<i class="fas fa-user-md me-2" style="width: 90px; height: 90px;"></i>';
            }
            ?>
            <div class="medecin-details">
                <h2><?php echo $rows['nom_prenom'] ?></h2>
                <p><?php echo $rows['specialite'] ?></p>
                <p>Créneaux disponibles: 
                <?php
                if (empty($disponible)) {
                    echo date('H:i', strtotime($rowss['heure_debut'])) . ' - ' . date('H:i', strtotime($rowss['heure_fin']));
                } else {
                    echo 'aucun disponibilité !';
                }
                ?>
                </p>
            </div>
        </div>
        
        <div class="navigation">
            <a href="<?php echo $urlSemainePrecedente; ?>">← Semaine précédente</a>
            <span class="semaine-courante">
                Semaine du <?php echo reset($jours); ?> au <?php echo end($jours); ?>
            </span>
            <a href="<?php echo $urlSemaineSuivante; ?>">Semaine suivante →</a>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th></th>
                    <?php foreach ($jours as $jour => $date): ?>
                        <th><?php echo ucfirst($jour); ?></th>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <th></th>
                    <?php foreach ($jours as $jour => $date): ?>
                        <th><?php echo $date; ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $creneaux = [];

                foreach ($joursSemaine as $j) {
                    $heureDebut = new DateTime($j['debut']);
                    $heureFin = new DateTime($j['fin']);

                    while ($heureDebut <= $heureFin) {
                        $h = $heureDebut->format('H:i');
                        if (!in_array($h, $creneaux)) {
                            $creneaux[] = $h;
                        }
                        $heureDebut->modify('+30 minutes');
                    }
                }

                sort($creneaux);
foreach ($creneaux as $creneau) {
    echo "<tr>";
    echo "<td class='horaire'>$creneau</td>";

    foreach ($jours as $jourNom => $date) {
        $afficher = '—';

        foreach ($joursSemaine as $j) {
            if (strtolower($j['jour']) === strtolower($jourNom)) {
                $debut = new DateTime($j['debut']);
                $fin = new DateTime($j['fin']);
                $heureCreneau = new DateTime($creneau);

                if ($heureCreneau >= $debut && $heureCreneau <= $fin) {
                    $afficher = $creneau;
                }
            }
        }

        if ($afficher == '—') {
            echo "<td><span class='libre'>—</span></td>";
        } else {
            // Calculer le nombre de rendez-vous pour ce créneau (date + heure)
            $dateFormateeSQL = date('Y-m-d', strtotime($datesFormatees[$jourNom])); // Convertir la date au format SQL (YYYY-MM-DD)
            $sqlCount = "SELECT COUNT(*) as total FROM rendezvous WHERE date_rdv = ? AND heure_rdv = ?";
            $stmt = mysqli_prepare($conn, $sqlCount);
            mysqli_stmt_bind_param($stmt, 'ss', $dateFormateeSQL, $creneau); // Utiliser $creneau directement
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $total);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);

            if ($total >= 4) {
                // Créneau plein, cellule grisée, non cliquable
                echo "<td style='color: #ccc; background-color:#f0f0f0; cursor: not-allowed;' title='Créneau complet'>$afficher</td>";
            } else {
                // Créneau disponible, cellule sélectionnable
                echo "<td class='selectable' data-creneau='$afficher' data-date='". htmlspecialchars($datesFormatees[$jourNom]) ."' data-jour='". ucfirst($jourNom) ."'>$afficher</td>";
            }
        }
    }
    echo "</tr>";
}
                ?>
            </tbody>
        </table>
        <form method="post" id="formCreneau" style="text-align:center; margin-bottom: 20px;" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="container">
                <div class="confirmation" id="confirmation">
                    <h3>Récapitulatif de votre rendez-vous</h3>
                    <div class="recap-details">
                        <p><strong>Professionnel :</strong> <span id="recap-medecin" name="nom"><?php echo $rows['nom_prenom']; ?></span></p>
                        <p><strong>Spécialité :</strong> <span id="recap-specialite" name="specialite"><?php echo $rows['specialite']; ?></span></p>
                        <input type="hidden" name="creneau_selectionne" id="creneau_selectionne">
                        <p><strong>Date : </strong><span id="affichage_date"></span></p>
                        <p><strong>Heure : </strong><span id="affichage_heure"></span></p> 
                        <div class="motif-container">
                            <label for="motif" class="motif-label">Motif de la consultation :</label>
                            <input type="text" id="motif" name="motif" class="motif-input" placeholder="Entrez le motif (ex. Consultation générale)" required>
                            <span class="error-message" id="motif-error">Veuillez indiquer le motif de la consultation.</span>
                        </div>              
                    </div>
                    <div class="confirmation-actions">
                        <button type="submit" class="btn btn-primary" id="confirmBtn">Confirmer le rendez-vous</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tds = document.querySelectorAll('td.selectable');
            const input = document.getElementById('creneau_selectionne');
            const affichageDate = document.getElementById('affichage_date');
            const affichageHeure = document.getElementById('affichage_heure');

            tds.forEach(td => {
                td.addEventListener('click', () => {
                    const creneau = td.getAttribute('data-creneau');
                    const date = td.getAttribute('data-date');
                    const jour = td.getAttribute('data-jour');
                    
                    // Formatage de la date pour l'affichage
                    const dateObj = new Date(date);
                    const options = { day: 'numeric', month: 'long', year: 'numeric' };
                    const dateFormatee = dateObj.toLocaleDateString('fr-FR', options);
                    
                    input.value = date + ' ' + creneau;
                    affichageDate.textContent = dateFormatee;
                    affichageHeure.textContent = creneau;

                    tds.forEach(el => el.classList.remove('selected'));
                    td.classList.add('selected');
                });
            });
        });
    </script>
</body>
</html>