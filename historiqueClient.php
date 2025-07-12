<?php
require_once 'config.php'; 
$conn = mysqli_connect($host, $user, $password, $dbname);

// Traitement des filtres
$date_debut = isset($_GET['date_debut']) ? $_GET['date_debut'] : '';
$date_fin = isset($_GET['date_fin']) ? $_GET['date_fin'] : '';
$mot_cle = isset($_GET['mot_cle']) ? trim($_GET['mot_cle']) : '';

// Fonction de filtrage
function filtrer_rendez_vous($rendez_vous, $date_debut, $date_fin, $mot_cle) {
    $resultats = [];

    foreach ($rendez_vous as $rdv) {
        $inclure = true;

        // Filtre par date de début
        if (!empty($date_debut) && $rdv['date'] < $date_debut) {
            $inclure = false;
        }

        // Filtre par date de fin
        if (!empty($date_fin) && $rdv['date'] > $date_fin) {
            $inclure = false;
        }

        // Filtre par mot-clé
        if (!empty($mot_cle)) {
            $texte_recherche = strtolower($rdv['patient'] . ' ' . $rdv['medecin'] . ' ' . $rdv['type']);
            if (strpos($texte_recherche, strtolower($mot_cle)) === false) {
                $inclure = false;
            }
        }

        if ($inclure) {
            $resultats[] = $rdv;
        }
    }

    return $resultats;
}

// Appliquer les filtres
$rendez_vous_filtres = filtrer_rendez_vous($rendez_vous, $date_debut, $date_fin, $mot_cle);

// Fonction pour formater la date
function formater_date($date) {
    $mois = [
        '01' => 'janvier', '02' => 'février', '03' => 'mars', '04' => 'avril',
        '05' => 'mai', '06' => 'juin', '07' => 'juillet', '08' => 'août',
        '09' => 'septembre', '10' => 'octobre', '11' => 'novembre', '12' => 'décembre'
    ];

    $parties = explode('-', $date);
    $jour = ltrim($parties[2], '0');
    $mois_nom = $mois[$parties[1]];
    $annee = $parties[0];

    return "$jour $mois_nom $annee";
}

// Fonction pour obtenir la classe CSS du statut
function obtenir_classe_statut($statut) {
    switch ($statut) {
        case 'Programmé':
            return 'statut-programme';
        case 'Terminé':
            return 'statut-termine';
        case 'Annulé':
            return 'statut-annule';
        default:
            return 'statut-programme';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hub de l'Historique des Rendez-vous</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }

.header {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.5rem;
            font-weight: bold;
            color: rgb(14, 62, 101);
        }

        .logo-icon {
            width: 32px;
            height: 32px;
            background: #2196f3;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .nav-menu {
            display: flex;
            gap: 2rem;
            list-style: none;
        }

        .nav-menu a {
            text-decoration: none;
            color: #666;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .nav-menu a:hover,
        .nav-menu a.active {
            background:#1976d2;;
            color: white;
        }

        .container {
            max-width: 1400px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .filtres {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin: -30px auto 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            position: relative;
            z-index: 10;
        }

        .filtres-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 2fr auto auto;
            gap: 20px;
            align-items: end;
        }

        .filtre-groupe {
            display: flex;
            flex-direction: column;
        }

        .filtre-groupe label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #555;
        }

        .filtre-input {
            padding: 12px 16px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .filtre-input:focus {
            outline: none;
            border-color: #667eea;
        }

        .recherche-container {
            position: relative;
        }

        .recherche-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            padding: 12px 16px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
            background:#00264c;
            color: white;
            width:200px;
            margin-left:350px;
        }

        .btn-primary:hover {
            background: rgb(6, 59, 111);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: transparent;
            color: #666;
            border: 2px solid #e1e5e9;
        }

        .btn-secondary:hover {
            background: #f8f9fa;
        }

        .rendez-vous-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .rendez-vous-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
        }

        .rendez-vous-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .patient-nom {
            font-size: 1.4rem;
            font-weight: 600;
            color: #667eea;
            margin-bottom: 8px;
        }

        .medecin {
            color: #666;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            color: #555;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .icon {
            width: 16px;
            height: 16px;
            opacity: 0.7;
        }

        .statut {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .statut-programme {
            background: #e3f2fd;
            color: #1976d2;
        }

        .statut-termine {
            background: #e8f5e8;
            color: #2e7d32;
        }

        .statut-annule {
            background: #ffebee;
            color: #c62828;
        }

        .aucun-resultat {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }

        .aucun-resultat img {
            width: 200px;
            height: 200px;
            background: #f0f0f0;
            border-radius: 12px;
            margin: 0 auto 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 1.2rem;
        }

        .aucun-resultat h3 {
            font-size: 1.5rem;
            color: #999;
            margin-bottom: 12px;
        }

        .aucun-resultat p {
            color: #666;
            font-size: 1.1rem;
        }

        @media (max-width: 768px) {
            .filtres-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .rendez-vous-grid {
                grid-template-columns: 1fr;
            }

            .header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
  <header class="header">
        <div class="logo">
            <div class="logo-icon">📅</div>
           CliqRDV
        </div>
        <nav>
            <ul class="nav-menu">
                <li><a href="index.php" >Accueil </a></li>
                <li><a href="rechercher.php" >Recherche</a></li>
                <li><a href="historiqueDoc.php" class="active">Mes rendez-vous</a></li>
                <li><a href="#">ProfessionSanté</a></li>
            </ul>
        </nav>
</header>
    <div class="container">
        <form method="GET" class="filtres">
            <div class="filtres-grid">
                <div class="filtre-groupe">
                    <label for="date_debut">Date de début</label>
                    <input type="date" id="date_debut" name="date_debut" class="filtre-input"
                           value="<?php echo htmlspecialchars($date_debut); ?>" placeholder="Du">
                </div>

                <div class="filtre-groupe">
                    <label for="date_fin">Date de fin</label>
                    <input type="date" id="date_fin" name="date_fin" class="filtre-input"
                           value="<?php echo htmlspecialchars($date_fin); ?>" placeholder="Au">
                </div>

                <!--div class="filtre-groupe">
                    <label for="mot_cle">Mot-clé</label>
                    <div class="recherche-container">
                        <input type="text" id="mot_cle" name="mot_cle" class="filtre-input"
                               value="<?php //echo htmlspecialchars($mot_cle); ?>"
                               placeholder="Rechercher par nom, motif...">
                  
                    </div>
                </div-->

                <button type="submit" class="btn btn-primary">
                    Appliquer les filtres
                </button>

                <a href="?" class="btn btn-secondary">
                    ✕ Effacer
                </a>
            </div>
        </form>

        <?php if (count($rendez_vous_filtres) > 0): ?>
            <div class="rendez-vous-grid">
                <?php foreach ($rendez_vous_filtres as $rdv): ?>
                    <div class="rendez-vous-card">
                        <span class="statut <?php echo obtenir_classe_statut($rdv['statut']); ?>">
                            <?php echo htmlspecialchars($rdv['statut']); ?>
                        </span>

                        <div class="patient-nom">
                            <?php echo htmlspecialchars($rdv['patient']); ?>
                        </div>

                        <div class="medecin">
                            👨‍⚕️ <?php echo htmlspecialchars($rdv['medecin']); ?>
                        </div>

                        <div class="info-row">
                            <span class="icon">📅</span>
                            <span><?php echo formater_date($rdv['date']); ?></span>
                        </div>

                        <div class="info-row">
                            <span class="icon">🕐</span>
                            <span><?php echo htmlspecialchars($rdv['heure']); ?></span>
                        </div>

                        <div class="info-row">
                            <span class="icon">🏥</span>
                            <span><?php echo htmlspecialchars($rdv['type']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="aucun-resultat">
                <div class="aucun-resultat-icon">
                    Aucun Rendez-vous
                </div>
                <h3>Aucun Rendez-vous</h3>
                <p>Aucun rendez-vous ne correspond à vos filtres actuels.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Auto-submit form when filters change
        document.addEventListener('DOMContentLoaded', function() {
            const dateInputs = document.querySelectorAll('input[type="date"]');

            dateInputs.forEach(input => {
                input.addEventListener('change', function() {
                    // Optional: Auto-submit on date change
                    // this.form.submit();
                });
            });
        });
    </script>
</body>
</html>
