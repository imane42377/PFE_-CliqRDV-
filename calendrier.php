<?php
session_start();

require_once 'config.php'; 

$conn = mysqli_connect($host, $user, $password, $dbname);
if (mysqli_connect_errno()) {
    die("Erreur de connexion MySQL : " . mysqli_connect_error());
}

if (isset($_GET['x'])){
    $id=$_GET['x'];
    $rdv="select * from  rendezvous , client where rendezvous.presta='$id'
     and client.client_id=rendezvous.client and date_rdv >= CURRENT_DATE() and heure_rdv >= CURRENT_TIME ";
   $res=mysqli_query($conn, $rdv);
   
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendrier des Consultations - 13 Juin 2025</title>
    <style>
        /* Définir les variables de couleur */
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --text-dark: #1f2937;
            --text-light: #9ca3af;
            --bg-light: #f9fafb;
            --border-color: #e5e7eb;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --error-color: #ef4444;
        }

        /* Réinitialisation et style de base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            padding: 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        /* Conteneur du tableau */
        .schedule-container {
            max-width: 900px;
            width: 100%;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        /* Style du tableau */
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
        }

        /* En-tête du tableau */
        .schedule-table thead {
            background-color: var(--primary-color);
            color: white;
        }

        .schedule-table th {
            padding: 1rem;
            font-weight: 600;
            text-align: left;
            font-size: 1rem;
        }

        /* Corps du tableau */
        .schedule-table tbody tr {
            transition: background-color 0.3s ease;
        }

        .schedule-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .schedule-table tbody tr:hover {
            background-color: rgba(37, 99, 235, 0.1);
        }

        .schedule-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.95rem;
        }

        /* Style des statuts */
        .status {
            padding: 0.3rem 0.8rem;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
        }

        .status-confirmed {
            background-color: var(--success-color);
            color: white;
        }

        .status-pending {
            background-color: var(--warning-color);
            color: white;
        }

        .status-cancelled {
            background-color: var(--error-color);
            color: white;
        }

        /* Style du bouton d'annulation */
        .cancel-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            background-color: #ef4444;
            color: white;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .cancel-btn:hover {
            background-color:#dc2626;
            transform: translateY(-2px);
        }

        .cancel-btn:active {
            transform: translateY(0);
        }

        .cancel-btn:disabled {
            background-color: #fca5a5;
            cursor: not-allowed;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .schedule-table th,
            .schedule-table td {
                padding: 0.8rem;
                font-size: 0.9rem;
            }

            .schedule-container {
                margin: 0 1rem;
            }

            .cancel-btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
            }
        }

        @media (max-width: 480px) {
            .schedule-table {
                display: block;
                overflow-x: auto;
            }

            .schedule-table th,
            .schedule-table td {
                font-size: 0.85rem;
                padding: 0.6rem;
            }

            .cancel-btn {
                padding: 0.3rem 0.6rem;
                font-size: 0.75rem;
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
</head>
<body>
        <button class="exit-button" id="exitBtn1" onclick="exitApplication()" title="Fermer">×</button>
<script>
  function exitApplication() {
            window.history.back();
        }
</script>
    <!-- Conteneur du calendrier -->
    <div class="schedule-container">
        <!-- Tableau des consultations -->
         <?php if($res && mysqli_num_rows($res) > 0){?>
        <table class="schedule-table">
            <thead>
                <tr>
                    <th>Heure</th>
                    <th>Patient</th>
                    <th>Motif</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
            foreach($res as $row){

           
         ?>
                <tr>
                    <td><?php echo  htmlspecialchars(date("H:i", strtotime($row['heure_rdv']))) ; ?></td>
                    <td><?php echo  htmlspecialchars($row['nom_complet']) ; ?></td>
                    <?php
                    if($row['motif']==""){
                        ?>
                        <td><?php
                         echo  htmlspecialchars("---") ;
                          ?></td>
                   <?php 
                   }else{
                    ?>
                    <td><?php echo  htmlspecialchars($row['motif']) ; ?></td>
                    <?php
                }
                ?>
                 <td>
                    <?php 
                    // Affichage du statut avec classes CSS appropriées
                    if ($row['etat'] == "confirmé") {
                        echo '<span class="status status-confirmed">Confirmé</span>';
                    } elseif ($row['etat'] == "en attente") {
                        echo '<span class="status status-pending">En attente</span>';
                    } elseif ($row['etat'] == "annulé") {
                        echo '<span class="status status-cancelled">Annulé</span>';
                    } else {
                          echo  htmlspecialchars("---") ;
                    }
                    ?>
                </td>
                <td>
                    <?php if ($row['etat'] == "annulé") { ?>
                        <button class="cancel-btn" disabled>Annuler</button>
                    <?php } else { ?>
                        <button class="cancel-btn" 
                            onclick="if(confirm('Voulez-vous vraiment annuler le rendez-vous ?')) {
                                window.location.href = 'annuler.php?id=<?php echo urlencode($row['client_id']); ?>&rdv=<?php echo urlencode($row['id']); ?>&pre=<?php echo urlencode($row['presta']); ?>';
                            }">
                            Annuler
                        </button>
                    <?php } ?>
                </td>
                </tr>
               <?php }}
               else{
                ?>
                  <div class="container">
<span> aucun creanau aujourd'hui !</span>
                  </div>
                <?php
               }?>  
            </tbody>
        </table>
       
    </div>

    <script>
        // Fonction pour simuler l'annulation d'un rendez-vous
        function cancelAppointment(patientName, time) {
            if (confirm(`Voulez-vous vraiment annuler le rendez-vous de ${patientName} à ${time} ?`)) {
                alert(`Rendez-vous de ${patientName} à ${time} annulé avec succès.`);
                // Note : Dans un vrai système, une requête AJAX vers un script PHP serait utilisée pour mettre à jour la base de données.
            }
        }
    </script>
</body>
</html>