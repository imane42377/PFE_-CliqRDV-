<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   	<link  href="./bootstrap/bootstrap-icons.css" rel="stylesheet">

    <title>Historique Client - Jean Dupont</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 50px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .client-name {
            font-size: 2rem;
            font-weight: bold;
            color:rgb(10, 40, 74);
            margin-bottom: 5px;
        }

        .page-title {
            font-size: 1.1rem;
            color: #666;
            font-weight: normal;
        }

                .btn {
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .table-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            background: #f8f9fa;
            padding: 18px 20px;
            text-align: left;
            font-weight: 600;
            color: #555;
            font-size: 0.9rem;
            border-bottom: 1px solid #e1e5e9;
        }

        .table td {
            padding: 18px 20px;
            border-bottom: 1px solid #f1f3f4;
            vertical-align: middle;
        }

        .table tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            text-align: center;
            min-width: 80px;
            display: inline-block;
        }

        .status-programme {
            background: #e3f2fd;
            color: #1976d2;
        }

        .status-termine {
            background: #e8f5e8;
            color: #2e7d32;
        }

        .status-annule {
            background: #ffebee;
            color: #d32f2f;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            width: 36px;
            height: 36px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .action-edit {
            background: #f0f7ff;
            color: #1976d2;
        }

        .action-edit:hover {
            background: #1976d2;
            color: white;
        }

        .action-delete {
            background: #fff5f5;
            color: #d32f2f;
        }

        .action-delete:hover {
            background: #d32f2f;
            color: white;
        }

        /* Modal de confirmation */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 12px;
            padding: 30px;
            max-width: 450px;
            width: 90%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: slideIn 0.3s ease;
        }

        .modal-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-icon {
            width: 48px;
            height: 48px;
            background: #ffebee;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 24px;
        }

        .modal-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
            margin: 0;
        }

        .modal-message {
            color: #666;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .modal-rdv-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            border-left: 4px solid #d32f2f;
        }

        .modal-rdv-info strong {
            color: #333;
        }

        .modal-buttons {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        .modal-btn {
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .modal-btn-cancel {
            background: #f8f9fa;
            color: #666;
            border: 1px solid #e1e5e9;
        }

        .modal-btn-cancel:hover {
            background: #e9ecef;
        }

        .modal-btn-confirm {
            background: #d32f2f;
            color: white;
        }

        .modal-btn-confirm:hover {
            background: #b71c1c;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translate(-50%, -60%);
            }
            to {
                opacity: 1;
                transform: translate(-50%, -50%);
            }
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

 .back-btn {
            background: #f5f5f5;
            color: #666;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-bottom: 20px;
        }

        .back-btn:hover {
            background: #e0e0e0;
        }

    </style>
</head>
<body>
    <?php
    // Données d'exemple des rendez-vous
    $rendezVous = [
        [
            'prestataire' => 'Dr. Smith',
            'date' => '22 avril 2024',
            'motif' => 'Routine Check',
            'statut' => 'programme'
        ],
        [
            'prestataire' => 'Dr. Wilson',
            'date' => '12 mars 2024',
            'motif' => 'Physical Therapy',
            'statut' => 'termine'
        ],
        [
            'prestataire' => 'Dr. Miller',
            'date' => '10 février 2024',
            'motif' => 'Follow-up',
            'statut' => 'programme'
        ],
        [
            'prestataire' => 'Dr. Smith',
            'date' => '05 janvier 2024',
            'motif' => 'Consultation',
            'statut' => 'termine'
        ],
        [
            'prestataire' => 'Dr. Davis',
            'date' => '20 novembre 2023',
            'motif' => 'Dental Cleaning',
            'statut' => 'annule'
        ],
        [
            'prestataire' => 'Dr. Jones',
            'date' => '01 novembre 2023',
            'motif' => 'Flu Shot',
            'statut' => 'termine'
        ]
    ];

    // Fonction pour obtenir le libellé du statut
    function getStatutLabel($statut) {
        switch($statut) {
            case 'programme': return 'Programmé';
            case 'termine': return 'Terminé';
            case 'annule': return 'Annulé';
            default: return $statut;
        }
    }

    // Filtres (à implémenter selon les besoins)
    $dateDebut = $_GET['date_debut'] ?? '';
    $dateFin = $_GET['date_fin'] ?? '';
    $motCle = $_GET['mot_cle'] ?? '';
    ?>

    <div class="container">
        <!-- En-tête -->
           <button class="back-btn" onclick="exit()">← Retour</button>

        <div class="header">
            <h1 class="client-name">Jean Dupont</h1>
        </div>

        <!-- Section des filtres -->
        <div class="filters-section">
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
        </div>

        <!-- Tableau des rendez-vous -->
        <div class="table-section">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom du prestataire</th>
                        <th>Date</th>
                        <th>Motif</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rendezVous as $rdv): ?>
                    <tr>
                        <td><?= htmlspecialchars($rdv['prestataire']) ?></td>
                        <td><?= htmlspecialchars($rdv['date']) ?></td>
                        <td><?= htmlspecialchars($rdv['motif']) ?></td>
                        <td>
                            <span class="status-badge status-<?= $rdv['statut'] ?>">
                                <?= getStatutLabel($rdv['statut']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="actions">
                                <button class="action-btn action-edit" title="Modifier">
                                    <i class="bi bi-pencil-fill"></i> 
                                </button>
                                <button class="action-btn action-delete" title="Supprimer">
                                    <i class="bi bi-trash-fill"></i>     
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
         function exit(){
              window.location.href = "index.php";
        }
        // Fonctions JavaScript pour les actions
        document.addEventListener('DOMContentLoaded', function() {
            // Gestionnaire pour les boutons d'édition
            document.querySelectorAll('.action-edit').forEach(btn => {
                btn.addEventListener('click', function() {
                    alert('Fonction d\'édition à implémenter');
                });
            });

            // Gestionnaire pour les boutons de suppression
            document.querySelectorAll('.action-delete').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (confirm('Êtes-vous sûr de vouloir supprimer ce rendez-vous ?')) {
                        alert('Fonction de suppression à implémenter');
                    }
                });
            });

            // Recherche en temps réel (optionnel)
            const searchInput = document.getElementById('mot_cle');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    // Implémentation de la recherche en temps réel si nécessaire
                });
            }
        });
    </script>
</body>
</html>
