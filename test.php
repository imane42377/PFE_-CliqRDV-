<?php
session_start();
$idC=$_SESSION['user_id'];
require_once 'config.php'; 

$conn = mysqli_connect($host, $user, $password, $dbname);
if (mysqli_connect_errno()) {
    die("Erreur de connexion MySQL : " . mysqli_connect_error());
}

if (isset($_GET['id'])){
    $id=$_GET['id'];
     $req="select * from prestataire where id= '$id' ";
    $res=mysqli_query($conn , $req);
      if ($res && mysqli_num_rows($res) > 0) {
    $rows =mysqli_fetch_assoc($res);
    
}
setlocale(LC_TIME, 'fr_FR.utf8', 'fra', 'fr_FR', 'fr');
// le jour en français
$jour_semaine = strftime('%A');

$reqq="select * from horairestravail where jour_semaine = '$jour_semaine' and prestataire = '$id'";
 $ress=mysqli_query($conn , $reqq);
    if ($ress && mysqli_num_rows($ress) > 0) {
    $rowss =mysqli_fetch_assoc($ress);
    $disponible='';

}else{
$disponible='non';
}


$medecin = [
    'heure_debut' => (isset($rowss) && !empty($rowss['heure_debut'])) ? $rowss['heure_debut'] : '-',
    'heure_fin' => (isset($rowss) && !empty($rowss['heure_fin'])) ? $rowss['heure_fin'] : '-',
    'duree_rdv' => 30
];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $time=$_POST['heure_rdv'];
    $day=$_POST['date_rdv'];
    $reqInsert="insert into rendezvous (date,heure,presta,client) values ('$day','$time','$id','$idC')";
echo 'bien';
}}
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
        
        .calendar-container {
            margin-top: 30px;
        }
        
        .date-selector {
            display: flex;
            overflow-x: auto;
            padding: 10px 0;
            margin-bottom: 20px;
            gap: 10px;
        }
        
        .date-option {
            min-width: 100px;
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .date-option:hover {
            background-color: #f0f0f0;
        }
        
        .date-option.selected {
            background-color: #3498db;
            color: white;
            border-color: #3498db;
        }
        
        .time-slots {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 10px;
            margin-top: 20px;
        }
        
        .time-slot {
            padding: 12px;
            text-align: center;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .time-slot:hover {
            background-color: #e9ecef;
        }
        
        .time-slot.selected {
            background-color: #2ecc71;
            color: white;
            border-color: #2ecc71;
        }
        
        .time-slot.unavailable {
            background-color: #e74c3c;
            color: white;
            cursor: not-allowed;
            opacity: 0.6;
        }
        
        .confirmation {
            margin-top: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
            display: none;
        }
        
        .btn {
            padding: 12px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            width: 100%;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #2980b9;
        }
        
        .btn:disabled {
            background-color: #95a5a6;
            cursor: not-allowed;
        }
        
        /* Style pour les jours passés */
        .date-option.past {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .confirmation {
    margin-top: 30px;
    padding: 25px;
    background-color: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
    display: none;
}

.confirmation h3 {
    color: #2c3e50;
    margin-bottom: 20px;
    text-align: center;
    font-size: 1.4rem;
}

.recap-details {
    background-color: white;
    padding: 20px;
    border-radius: 6px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.recap-details p {
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 1px solid #f0f0f0;
    color: #555;
}

.recap-details p:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.recap-details strong {
    color: #2c3e50;
    display: inline-block;
    width: 120px;
}

.confirmation-actions {
    display: flex;
    justify-content: space-between;
    gap: 15px;
}

.btn-secondary {
    background-color: #6c757d;
    flex: 1;
}

.btn-secondary:hover {
    background-color: #5a6268;
}

.btn-primary {
    background-color: #2ecc71;
    flex: 2;
}

.btn-primary:hover {
    background-color: #27ae60;
}
    </style>
</head>
<body>
    <div class="container">
        <h1>Choisissez votre rendez-vous</h1>
        
        <div class="medecin-info">
                    <?php
                     if (!empty($rows['photo_profil']) && file_exists(__DIR__ . '/' . $rows['photo_profil'])) {
                     echo '<img src="' . htmlspecialchars($rows['photo_profil'], ENT_QUOTES, 'UTF-8') . '"alt="Photo du médecin" class="medecin-photo">';
                     } else {
                        echo '<i class="fas fa-user-md me-2" style="width: 90px; height: 90px; "></i>';
                     }
                  ?>
                              <div class="medecin-details">
                <h2><?php echo $rows['nom_prenom']?></h2>
                <p><?php echo $rows['specialite']?></p>
                <p>Créneaux disponibles: <?php
                if(empty($disponible)){
                 echo date('H:i', strtotime( $rowss['heure_debut'])) .' - '. date('H:i', strtotime($rowss['heure_fin']));
                }else{
                    echo 'aucun disponibilité !';
                }
                  ?></p>
            </div>
        </div>
        <form id="rdvForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="hidden" name="date_rdv" id="inputDate">
                <input type="hidden" name="heure_rdv" id="inputTime">
        <div class="calendar-container">
            <h3>Choisissez une date</h3>
            <div class="date-selector" id="dateSelector">
                <!-- Les dates seront générées par JavaScript -->
            </div>
            
            <h3>Choisissez un horaire</h3>
            <div class="time-slots" id="timeSlots">
                <!-- Les créneaux horaires seront générés par JavaScript -->
            </div>
        </div>
            </form>
        <div class="confirmation" id="confirmation">
            <h3>Récapitulatif de votre rendez-vous</h3>
            <div class="recap-details">
                <p><strong>Professionnel :</strong> <span id="recap-medecin"><?php echo $rows['nom_prenom']; ?></span></p>
                <p><strong>Spécialité :</strong> <span id="recap-specialite"><?php echo $rows['specialite']; ?></span></p>
                <input type="hidden" name="creneau_selectionne" id="creneau_selectionne">
                <p>Créneau choisi : <strong id="affichage_selection"></strong></p>               
            </div>
            <div class="confirmation-actions">
                <button type="button" class="btn btn-secondary" id="modifyBtn">Modifier</button>
                <button type="submit" class="btn btn-primary" id="confirmBtn">Confirmer le rendez-vous</button>
            </div>
        </div>
    </div>

    <script>
        // Configuration
        const medecin = {
            heureDebut: '<?= $medecin['heure_debut'] ?>',
            heureFin: '<?= $medecin['heure_fin'] ?>',
            dureeRdv: <?= $medecin['duree_rdv'] ?>
        };
        
        // Variables globales
        let selectedDate = null;
        let selectedTime = null;
        
        // Fonction pour générer les dates disponibles (7 jours à partir d'aujourd'hui)
        function generateDates() {
            const dateSelector = document.getElementById('dateSelector');
            dateSelector.innerHTML = '';
            
            const today = new Date();
            
            for (let i = 0; i < 7; i++) {
                const date = new Date();
                date.setDate(today.getDate() + i);
                
                const dateOption = document.createElement('div');
                dateOption.className = 'date-option';
                
                // Ajouter classe 'past' pour les jours passés
                if (i === 0) {
                    dateOption.textContent = 'Aujourd\'hui';
                } else if (i === 1) {
                    dateOption.textContent = 'Demain';
                } else {
                    const options = { weekday: 'long', day: 'numeric', month: 'long' };
                    dateOption.textContent = date.toLocaleDateString('fr-FR', options);
                }
                
                // Stocker la date au format YYYY-MM-DD dans l'élément
                dateOption.dataset.date = date.toISOString().split('T')[0];
                
                // Désactiver les jours passés
                if (i === 0) {
                    dateOption.addEventListener('click', () => selectDate(date, dateOption));
                } else {
                    dateOption.addEventListener('click', () => selectDate(date, dateOption));
                }
                
                dateSelector.appendChild(dateOption);
            }
        }
        
        // Fonction pour sélectionner une date
        function selectDate(date, element) {
            // Désélectionner toutes les dates
            document.querySelectorAll('.date-option').forEach(el => {
                el.classList.remove('selected');
            });
            
            // Sélectionner la date cliquée
            element.classList.add('selected');
            selectedDate = date;
            
            // Générer les créneaux horaires pour cette date
            generateTimeSlots();
            
            // Masquer la confirmation
            document.getElementById('confirmation').style.display = 'none';
        }
        // Fonction pour générer les créneaux horaires
function generateTimeSlots() {
    const timeSlots = document.getElementById('timeSlots');
    timeSlots.innerHTML = '';
    
    if (!selectedDate || !medecin.heureDebut || !medecin.heureFin) {
        timeSlots.innerHTML = '<p>Aucun créneau disponible pour cette date</p>';
        return;
    }
    
    // Convertir les heures de début et fin en minutes depuis minuit
    const [startHour, startMinute] = medecin.heureDebut.split(':').map(Number);
    const [endHour, endMinute] = medecin.heureFin.split(':').map(Number);
    
    let currentHour = startHour;
    let currentMinute = startMinute;
    
    // Convertir en minutes pour faciliter la comparaison
    const startTotalMinutes = startHour * 60 + startMinute;
    const endTotalMinutes = endHour * 60 + endMinute;
    let currentTotalMinutes = startTotalMinutes;
    
    while (currentTotalMinutes < endTotalMinutes) {
        const timeSlot = document.createElement('div');
        timeSlot.className = 'time-slot';
        
        // Formater l'heure
        const formattedHour = String(currentHour).padStart(2, '0');
        const formattedMinute = String(currentMinute).padStart(2, '0');
        const timeString = `${formattedHour}:${formattedMinute}`;
        
        // Calculer l'heure de fin du créneau
        const endSlotMinutes = currentTotalMinutes + medecin.dureeRdv;
        const endSlotHour = Math.floor(endSlotMinutes / 60);
        const endSlotMinute = endSlotMinutes % 60;
        const endSlotFormattedHour = String(endSlotHour).padStart(2, '0');
        const endSlotFormattedMinute = String(endSlotMinute).padStart(2, '0');
        const endTimeString = `${endSlotFormattedHour}:${endSlotFormattedMinute}`;
        
        // Afficher l'heure de début et de fin
        timeSlot.textContent = `${timeString} - ${endTimeString}`;
        timeSlot.dataset.time = timeString;
        timeSlot.dataset.endTime = endTimeString;
        
        // Vérifier si le créneau dépasse l'heure de fin
        if (endSlotMinutes > endTotalMinutes) {
            timeSlot.classList.add('unavailable');
            timeSlot.title = "Ce créneau dépasse l'horaire de fermeture";
        } else {
            // Simuler des créneaux indisponibles
            const isUnavailable = Math.random() < 0.3; // 30% de chance d'être indisponible
            if (isUnavailable) {
                timeSlot.classList.add('unavailable');
                timeSlot.title = "Créneau déjà réservé";
            } else {
                timeSlot.addEventListener('click', () => selectTime(timeString, endTimeString, timeSlot));
            }
        }
        
        timeSlots.appendChild(timeSlot);
        
        // Passer au créneau suivant
        currentTotalMinutes += medecin.dureeRdv;
        currentHour = Math.floor(currentTotalMinutes / 60);
        currentMinute = currentTotalMinutes % 60;
    }
    
    if (timeSlots.children.length === 0) {
        timeSlots.innerHTML = '<p>Aucun créneau disponible pour cette date</p>';
    }
}

// Modifier la fonction selectTime pour afficher aussi l'heure de fin
function selectTime(startTime, endTime, element) {
    // Désélectionner tous les créneaux
    document.querySelectorAll('.time-slot').forEach(el => {
        el.classList.remove('selected');
    });
    
    // Sélectionner le créneau cliqué
    element.classList.add('selected');
    selectedTime = `${startTime} - ${endTime}`;
    
    // Afficher la confirmation
    const confirmation = document.getElementById('confirmation');
    confirmation.style.display = 'block';
    
    document.getElementById('inputDate').value = selectedDate.toISOString().split('T')[0];
    document.getElementById('inputTime').value = startTime;

    document.getElementById('selectedDate').textContent = `Date: ${selectedDate.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' })}`;
    document.getElementById('selectedTime').textContent = `Heure: ${selectedTime}`;
}
        
        // Initialisation
        document.addEventListener('DOMContentLoaded', () => {
            generateDates();
            
         document.getElementById('confirmBtn').addEventListener('click', () => {
        if (selectedDate && selectedTime) {
            document.getElementById('rdvForm').submit();
        } else {
            alert("Veuillez sélectionner une date et un horaire.");
        }
    });

            // Gestion du bouton de confirmation
           // document.getElementById('confirmBtn').addEventListener('click', () => {
           //     if (selectedDate && selectedTime) {
           //                  
            //    }
            //});
        });
    </script>
</body>
</html>
supprimer le code qui permet d'fficher 
Choisissez un horaire et Choisissez une date