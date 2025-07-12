<?php
session_start();

// Si l’utilisateur n’est pas connecté, on le redirige vers la page de connexion
if (!isset($_SESSION['user_email'])) {
    header("Location: connexion.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CliqRDV - Fiche du Prestataire de Santé</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            min-height: 100vh;
        }
        
        .header1 {
            width: 100%;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .container {
            max-width: 1200px;
            margin: 40px auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: rgb(14, 76, 222);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 24px;
            font-weight: bold;
        }

        .form-container {
            padding: 40px;
        }

        .form-section {
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            font-weight: 500;
            color: #4a5568;
            margin-bottom: 8px;
            font-size: 14px;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        textarea {
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: white;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="tel"]:focus,
        textarea:focus {
            outline: none;
            border-color: #4facfe;
            box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .working-hours {
            background: #f8fafc;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            width: 100%;
            margin: 0 auto 30px auto;
        }

        .day-row {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .day-row:last-child {
            border-bottom: none;
        }

        .day-name {
            width: 100px;
            font-weight: 500;
            color: #4a5568;
        }

        .day-checkbox {
            margin-right: 15px;
            width: 18px;
            height: 18px;
            accent-color: #4facfe;
        }

        .time-inputs {
            display: flex;
            gap: 10px;
            flex: 1;
        }

        .time-input {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
        }

        .consultation-fee {
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
        }

        .consultation-fee label {
            color: black;
            margin-bottom: 10px;
        }

        .consultation-fee input {
            background: rgba(255, 255, 255, 0.9);
            border: none;
            color: #2d3748;
        }

        .btn-save {
            background: rgb(14, 76, 222);
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: block;
            margin: 0 auto;
            box-shadow: 0 4px 15px rgba(79, 172, 254, 0.3);
        }

        .btn-save:hover {
            transform: translateY(-2px);
            background: rgb(14, 76, 222);
            color: white;
        }

        .photo-upload-section {
            margin: 0 auto 30px auto;
            background: #f8fafc;
            padding: 25px;
            border-radius: 15px;
            border: 2px dashed #e2e8f0;
            width: 500px;
            text-align: center;
        }

        .photo-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        .photo-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 3px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            overflow: hidden;
            position: relative;
        }

        .photo-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #a0aec0;
            text-align: center;
        }

        .photo-placeholder p {
            margin-top: 10px;
            font-size: 14px;
            font-weight: 500;
        }

        #previewImage {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-upload-controls {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .btn-upload, .btn-remove {
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-upload {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            box-shadow: 0 2px 10px rgba(79, 172, 254, 0.3);
        }

        .btn-upload:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(79, 172, 254, 0.4);
        }

        .btn-remove {
            background: #fed7d7;
            color: #c53030;
            border: 1px solid #feb2b2;
        }

        .btn-remove:hover {
            background: #feb2b2;
        }

        .upload-hint {
            font-size: 12px;
            color: #718096;
            margin-top: 5px;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .day-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .time-inputs {
                width: 100%;
            }

            .photo-container {
                flex-direction: column;
            }
            
            .photo-preview {
                width: 120px;
                height: 120px;
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

        /* Styles spécifiques pour la section Horaires (répétés ici pour clarté) */
        .horaires-container {
            margin-top: 20px;
            border: 1px solid #ccc;
            padding: 15px;
            border-radius: 8px;
            background-color: #f9f9f9;
        }

        .jour-block {
            margin-bottom: 15px;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .jour-label {
            font-weight: bold;
            font-size: 16px;
            margin-left: 8px;
        }

        .heures-selection select {
            padding: 6px;
            font-size: 14px;
        }

        .heures-selection span {
            margin-right: 5px;
            color: #4a5568;
        }

        .heures-selection {
            margin-top: 10px;
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        input[type="checkbox"] {
            transform: scale(1.2);
            cursor: pointer;
        }

    </style>
</head>
<body>
                <button class="exit-button" id="exitBtn1" onclick="exitApplication()" title="Fermer">×</button>
    <div class="container">
        <div class="header">
            <h1> Bienvenue sur notre plateforme !</h1>
            <p>Votre compte a été créé avec succès. Configurez maintenant votre profil pour une expérience personnalisée.</p>
        </div>

        <div class="form-container">
            <form id="providerForm" action="insert_prestataire.php" method="POST" enctype="multipart/form-data">
                <!-- Informations personnelles -->
                <div class="form-section">
                    <h2 class="section-title">Informations Personnelles</h2>
                    
                    <!-- Photo Upload Section -->
                    <div class="photo-upload-section">
                        <div class="photo-container">
                            <div class="photo-preview" id="photoPreview">
                                <div class="photo-placeholder">
                                    <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                    <p>Photo de profil</p>
                                </div>
                                <img id="previewImage" style="display: none;" alt="Photo de profil">
                            </div>
                            <div class="photo-upload-controls">
                                <input type="file" id="photoUpload" name="photo" accept="image/*" style="display: none;" required>
                                <button type="button" class="btn-upload" onclick="document.getElementById('photoUpload').click()">
                                    Choisir une photo
                                </button>
                                <button type="button" class="btn-remove" id="removePhoto" style="display: none;">
                                    Supprimer
                                </button>
                                <p class="upload-hint">Format accepté: JPG, PNG (max 5MB)</p>
                            </div>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name">Nom :</label>
                            <input type="text" id="name" name="name" placeholder="Dr. Nom Prénom" required>
                        </div>
                        <div class="form-group">
                            <label for="specialty">Spécialité :</label>
                            <input type="text" id="specialty" name="specialty" placeholder="Ex: Cardiologie, Dermatologie..." required>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label for="location">Localisation :</label>
                        <input type="text" id="location" name="location" placeholder="Adresse complète de votre cabinet" required>
                    </div>
                </div>

                <!-- Contact -->
                <div class="form-section">
                    <h2 class="section-title">Informations de Contact</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="phone">Numéro de téléphone :</label>
                            <input type="tel" id="phone" name="phone" placeholder="+212 6xx xxx xxx" required>
                        </div>
                        <div class="form-group">
                            <label for="fax">Fax :</label>
                            <input type="tel" id="fax" name="fax" placeholder="Numéro de fax (optionnel)">
                        </div>
                    </div>
                    <div class="form-group full-width">
                        <label for="email">Email :</label>
                        <input type="email" id="email" name="email" placeholder="votre.email@exemple.com" required>
                    </div>
                </div>

                <!-- Horaires de travail -->
                <div class="form-section">
                    <h2 class="section-title">Horaires de Travail</h2>
                    <div class="working-hours">
                        <div class="form-group horaires-container">
                            <label><strong>Horaires de travail :</strong></label>

                            <?php
                            $jours = ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche"];
                            $heures = [];
                            for ($i = 0; $i <= 23; $i++) {
                                $heures[] = str_pad($i, 2, "0", STR_PAD_LEFT) . ":00";
                                $heures[] = str_pad($i, 2, "0", STR_PAD_LEFT) . ":30";
                            }

                            foreach ($jours as $jour) {
                                $lower = strtolower($jour);
                                echo "<div class='jour-block'>
                                        <input type='checkbox' id='chk_$lower' name='jours[]' value='$jour' class='day-checkbox' onchange=\"toggleHeures('$lower')\">
                                        <label class='jour-label' for='chk_$lower'>$jour</label>
                                        <div id='heures_$lower' class='heures-selection' style='display:none;'>
                                            <span>De :</span>
                                            <select name='heure_debut[$jour]' class='time-input'>";
                                foreach ($heures as $heure) {
                                    echo "<option value='$heure'>$heure</option>";
                                }
                                echo       "</select>
                                            <span>à :</span>
                                            <select name='heure_fin[$jour]' class='time-input'>";
                                foreach ($heures as $heure) {
                                    echo "<option value='$heure'>$heure</option>";
                                }
                                echo       "</select>
                                        </div>
                                      </div>";
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Parcours Professionnel -->
                <div class="form-section">
                    <h2 class="section-title">Parcours Professionnel</h2>
                    <div class="form-group">
                        <label for="academic">Parcours Académique :</label>
                        <textarea id="academic" name="academic" placeholder="Décrivez votre formation et parcours académique..." required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="experience">Expériences Professionnelles :</label>
                        <textarea id="experience" name="experience" placeholder="Décrivez vos expériences professionnelles principales..." required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="diplomas">Diplômes :</label>
                        <textarea id="diplomas" name="diplomas" placeholder="Listez vos diplômes et certifications..." required></textarea>
                    </div>
                </div>

                <!-- Tarifs -->
                <div class="form-section">
                    <label for="consultation_fee">Tarifs de consultation :</label>
                    <input type="text" id="consultation_fee" name="consultation_fee" placeholder="Ex: 300 MAD" required>
                </div>

                <button type="submit" class="btn-save">Configurer mon Profil</button>
            </form>
        </div>
    </div>

    <script>
        // Photo upload functionality
        document.getElementById('photoUpload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file size (5MB max)
                if (file.size > 5 * 1024 * 1024) {
                    alert('La taille du fichier ne doit pas dépasser 5MB.');
                    e.target.value = '';
                    return;
                }

                // Validate file type
                if (!file.type.match(/image\/(jpeg|jpg|png)/)) {
                    alert('Seuls les fichiers JPG et PNG sont acceptés.');
                    e.target.value = '';
                    return;
                }

                // Preview the image
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewImage = document.getElementById('previewImage');
                    const placeholder = document.querySelector('.photo-placeholder');
                    
                    previewImage.src = e.target.result;
                    previewImage.style.display = 'block';
                    placeholder.style.display = 'none';
                    
                    document.getElementById('removePhoto').style.display = 'inline-block';
                };
                reader.readAsDataURL(file);
            }
        });

        // Remove photo functionality
        document.getElementById('removePhoto').addEventListener('click', function() {
            const previewImage = document.getElementById('previewImage');
            const placeholder = document.querySelector('.photo-placeholder');
            const fileInput = document.getElementById('photoUpload');
            
            previewImage.style.display = 'none';
            previewImage.src = '';
            placeholder.style.display = 'flex';
            fileInput.value = '';
            this.style.display = 'none';
        });

        // Form validation and submission simulation (optionnel)
        document.getElementById('providerForm').addEventListener('submit', function(e) {
            // Vous pouvez retirer cette partie si vous voulez soumettre directement au serveur
            // e.preventDefault();
            // const formData = new FormData(this);
            // const name = formData.get('name');
            // const specialty = formData.get('specialty');
            // if (!name || !specialty) {
            //     alert('Veuillez remplir au moins le nom et la spécialité.');
            //     return;
            // }
            // const button = document.querySelector('.btn-save');
            // const originalText = button.textContent;
            // button.textContent = 'Enregistrement...';
            // button.disabled = true;
            // setTimeout(() => {
            //     button.textContent = '✓ Profil Enregistré';
            //     setTimeout(() => {
            //         button.textContent = originalText;
            //         button.disabled = false;
            //     }, 2000);
            // }, 1500);
        });

        // Fonction pour afficher/masquer les sélecteurs d’heures
        function toggleHeures(jour) {
            const container = document.getElementById('heures_' + jour);
            const checkbox = document.getElementById('chk_' + jour);
            container.style.display = checkbox.checked ? 'flex' : 'none';
        }

        // Exit button functionality
        function exitApplication() {
            window.history.back();
            header('location:index.php');
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                exitApplication();
            }
        });

        // Initialiser tous les sélecteurs d’heures masqués
        document.querySelectorAll('.heures-selection').forEach(elem => {
            elem.style.display = 'none';
        });
    </script>
</body>
</html>
