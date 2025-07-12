<?php
session_start();

// 0) Si pas d'email en session, on redirige vers la connexion
if (empty($_SESSION['user_email'])) {
    header('Location: connexion.php');
    exit;
}

$emailSession = $_SESSION['user_email'];
require_once 'config.php'; 

$conn = mysqli_connect($host, $user, $password, $dbname);
if (mysqli_connect_errno()) {
    die("Erreur de connexion MySQL : " . mysqli_connect_error());
}

$errors = [];

$sql = "SELECT id FROM connexion WHERE email = ?";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    $errors[] = "Erreur de préparation MySQL pour client_id : " . mysqli_error($conn);
} else {
    mysqli_stmt_bind_param($stmt, "s", $emailSession);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) {
        $client_id = $row['id'];
    } else {
        $errors[] = "Aucun utilisateur trouvé pour l'email : " . htmlspecialchars($emailSession);
    }
    mysqli_stmt_close($stmt);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération & nettoyage
    $nomComplet    = trim($_POST['fullName']    ?? '');
    $email         = trim($_POST['email']       ?? '');
    $passwordPlain = $_POST['password']         ?? '';
    $telephone     = trim($_POST['phone']       ?? '');
    $dateNaiss     = $_POST['birthDate']        ?? '';
    $sexe          = $_POST['gender']           ?? '';
    $adresse       = trim($_POST['address']     ?? '') ?: null;

    // Validation minimale
    if ($nomComplet    === '') $errors[] = "Le nom complet est requis.";
    if ($email         === '') $errors[] = "L'email est requis.";
    if ($passwordPlain === '') $errors[] = "Le mot de passe est requis.";
    if ($telephone     === '') $errors[] = "Le téléphone est requis.";
    if ($dateNaiss     === '') $errors[] = "La date de naissance est requise.";
    if ($sexe          === '') $errors[] = "Le sexe est requis.";

    // Validation de l'email
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email n'est pas valide.";
    }

    // Validation du numéro de téléphone
    //if ($telephone !== '' && !preg_match('/^\+?\d{10,15}$/', $telephone)) {
      //  $errors[] = "Le numéro de téléphone est invalide.";
    //}
    // Gestion de l'upload de la photo (optionnel)
    $photo_path = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . "/Uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $photo_name = basename($_FILES['photo']['name']);
        $photo_tmp = $_FILES['photo']['tmp_name'];
        $photo_path = "Uploads/" . time() . "_" . $photo_name;

        // Validation du fichier
        if ($_FILES['photo']['size'] > 5 * 1024 * 1024) {
            $errors[] = "La taille de la photo ne doit pas dépasser 5MB.";
        } elseif (!in_array($_FILES['photo']['type'], ['image/jpeg', 'image/png'])) {
            $errors[] = "Seuls les fichiers JPG et PNG sont acceptés.";
        } elseif (!is_uploaded_file($photo_tmp)) {
            $errors[] = "Erreur : fichier photo non valide.";
        } else {
            // Vérification du contenu de l'image
            $imageInfo = getimagesize($photo_tmp);
            if ($imageInfo === false) {
                $errors[] = "Le fichier uploadé n'est pas une image valide.";
            } elseif (!move_uploaded_file($photo_tmp, $uploadDir . basename($photo_path))) {
                $errors[] = "Erreur lors de l'enregistrement de la photo.";
            }
        }
    }

    if (empty($errors)) {
        // Hachage du mot de passe
        $hashPassword = password_hash($passwordPlain, PASSWORD_BCRYPT);

        // Préparation de la requête
        $sql = "
            INSERT INTO client
               (Client_id,nom_complet, email, mot_de_passe, numero_telephone, date_naissance, sexe, adresse, photo)
            VALUES
               (?,?, ?, ?, ?, ?, ?, ?, ?)
        ";
        $stmt = mysqli_prepare($conn, $sql);
        if (! $stmt) {
            $errors[] = "Erreur de préparation MySQL : " . mysqli_error($conn);
        } else {
            // Liaison des paramètres
            mysqli_stmt_bind_param(
                $stmt,
                "issssssss",
                $client_id,
                $nomComplet,
                $email,
                $hashPassword,
                $telephone,
                $dateNaiss,
                $sexe,
                $adresse,
                $photo_path
            );

            // Exécution
            if (mysqli_stmt_execute($stmt)) {
                // Succès : on redirige
                header('Location: dashboardClient.php');
                exit;
            } else {
                $errors[] = "Erreur SQL lors de l'insertion : " . mysqli_stmt_error($stmt);
            }

            mysqli_stmt_close($stmt);
        }
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration du Profil Patient</title>
    <style>
        /* --- Votre CSS inchangé ci-dessous --- */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); min-height: 100vh; }
        .header1 { width:100%; background:rgba(255,255,255,0.9); backdrop-filter:blur(10px); padding:1rem 2rem; display:flex; justify-content:space-between; align-items:center; box-shadow:0 2px 10px rgba(0,0,0,0.1); }
        .container { max-width:1200px; margin:20px auto; width:1200px; background:white; border-radius:20px; box-shadow:0 20px 60px rgba(0,0,0,0.1); overflow:hidden; }
        .header { background:rgb(14,76,222); color:white; padding:30px; text-align:center; }
        .header h1 { font-size:24px; font-weight:bold; }
        .form-container { padding:40px; }
        .form-section { margin-bottom:40px; }
        .section-title { font-size:20px; font-weight:600; color:#2d3748; margin-bottom:20px; padding-bottom:10px; border-bottom:2px solid #e2e8f0; }
        .form-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(300px,1fr)); gap:20px; margin-bottom:30px; }
        .form-group { display:flex; flex-direction:column; }
        .form-group.full-width { grid-column:1/-1; }
        label { font-weight:500; color:#4a5568; margin-bottom:8px; font-size:14px; }
        .optional { color:#888; font-weight:400; font-size:12px; }
        input[type="text"],input[type="email"],input[type="password"],input[type="tel"],input[type="date"],select,textarea {
            padding:12px 16px; border:2px solid #e2e8f0; border-radius:8px; font-size:16px; transition:all 0.3s ease; background:white;
        }
        input:focus,select:focus,textarea:focus { outline:none; border-color:#4facfe; box-shadow:0 0 0 3px rgba(79,172,254,0.1); }
        select { cursor:pointer; }
        textarea { resize:vertical; min-height:100px; }
        .photo-upload-section { margin:0 auto 30px auto; background:#f8fafc; padding:25px; border-radius:15px; border:2px dashed #e2e8f0; width:500px; text-align:center; }
        .photo-container { display:flex; flex-direction:column; align-items:center; gap:20px; }
        .photo-preview { width:150px; height:150px; border-radius:50%; border:3px solid #e2e8f0; display:flex; align-items:center; justify-content:center; background:white; overflow:hidden; position:relative; }
        .photo-placeholder { display:flex; flex-direction:column; align-items:center; color:#a0aec0; text-align:center; }
        .photo-placeholder p { margin-top:10px; font-size:14px; font-weight:500; }
        #previewImage { width:100%; height:100%; object-fit:cover; }
        .photo-upload-controls { display:flex; flex-direction:column; align-items:center; gap:10px; }
        .btn-upload,.btn-remove { padding:10px 20px; border:none; border-radius:25px; font-weight:500; cursor:pointer; transition:all 0.3s ease; }
        .btn-upload { background:linear-gradient(135deg,#4facfe 0%,#00f2fe 100%); color:white; box-shadow:0 2px 10px rgba(79,172,254,0.3); }
        .btn-upload:hover { transform:translateY(-1px); box-shadow:0 4px 15px rgba(79,172,254,0.4); }
        .btn-remove { background:#fed7d7; color:#c53030; border:1px solid #feb2b2; }
        .btn-remove:hover { background:#feb2b2; }
        .upload-hint { font-size:12px; color:#718096; margin-top:5px; }
        .btn-save { background:rgb(14,76,222); color:white; padding:15px 40px; border:none; border-radius:50px; font-size:16px; font-weight:600; cursor:pointer; transition:all 0.3s ease; display:block; margin:0 auto; box-shadow:0 4px 15px rgba(79,172,254,0.3); }
        .btn-save:hover { transform:translateY(-2px); }
        .exit-button { position:fixed; top:15px; right:15px; width:40px; height:40px; background:rgba(255,255,255,0.9); border:none; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:18px; font-weight:bold; color:#666; box-shadow:0 2px 10px rgba(0,0,0,0.1); transition:all 0.3s ease; z-index:1000; backdrop-filter:blur(10px); }
        .exit-button:hover { background:#ff4757; color:white; transform:scale(1.1); box-shadow:0 4px 15px rgba(255,71,87,0.3); }
        .exit-button:active { transform:scale(0.95); }
        @media (max-width:768px) {
            .form-container { padding:20px; }
            .form-grid { grid-template-columns:1fr; }
            .container { margin:10px; width:auto; }
            .photo-upload-section { width:auto; }
            .photo-preview { width:120px; height:120px; }
        }
        .success-animation { animation:pulse 0.6s ease-in-out; }
        @keyframes pulse { 0%{transform:scale(1);} 50%{transform:scale(1.05);} 100%{transform:scale(1);} }
    </style>
</head>
<body>
    <!-- Affichage des erreurs si besoin -->
    <?php if (!empty($errors)): ?>
        <div style="background:#ffe6e6;color:#cc0000;padding:15px;margin:20px;border-radius:8px;">
            <ul>
                <?php foreach($errors as $e): ?>
                    <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Bouton de sortie -->
    <button class="exit-button" onclick="exitForm()" title="Fermer">×</button>

    <div class="container">
        <div class="header">
            <h1>Bienvenue sur notre plateforme !</h1>
            <p>Votre compte a été créé avec succès. Configurez maintenant votre profil pour une expérience personnalisée.</p>
        </div>

        <div class="form-container">
            <div class="form-section">
                <h2 class="section-title">Configuration de votre profil</h2>
                <form id="profileForm" action="" method="POST" enctype="multipart/form-data">
                    <div class="form-grid">
                        <!-- Nom complet -->
                        <div class="form-group">
                            <label for="fullName">Nom complet *</label>
                            <input type="text" id="fullName" name="fullName" required>
                        </div>
                        <!-- Email -->
                        <div class="form-group">
                            <label for="email">Adresse e-mail *</label>
                            <input type="email" id="email" name="email"
                                   value="<?= htmlspecialchars($emailSession, ENT_QUOTES, 'UTF-8') ?>"
                                   readonly>
                        </div>
                        <!-- Mot de passe -->
                        <div class="form-group">
                            <label for="password">Mot de passe *</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        <!-- Téléphone -->
                        <div class="form-group">
                            <label for="phone">Numéro de téléphone *</label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>
                        <!-- Date de naissance -->
                        <div class="form-group">
                            <label for="birthDate">Date de naissance *</label>
                            <input type="date" id="birthDate" name="birthDate" required>
                        </div>
                        <!-- Sexe -->
                        <div class="form-group">
                            <label for="gender">Sexe *</label>
                            <select id="gender" name="gender" required>
                                <option value="">Sélectionnez votre sexe</option>
                                <option value="homme">Homme</option>
                                <option value="femme">Femme</option>
                            </select>
                        </div>
                        <!-- Adresse -->
                        <div class="form-group full-width">
                            <label for="address">Adresse (optionnel)</label>
                            <textarea id="address" name="address"></textarea>
                        </div>
                    </div>

                    <!-- Photo de profil -->
                    <div class="photo-upload-section">
                        <div class="photo-container">
                            <div class="photo-preview">
                                <div class="photo-placeholder" id="photoPlaceholder">
                                    <div style="font-size:50px;color:#cbd5e0;">👤</div>
                                    <p>Photo de profil</p>
                                </div>
                                <img id="previewImage" style="display:none;" alt="Aperçu photo">
                            </div>
                            <div class="photo-upload-controls">
                                <button type="button" class="btn-upload" onclick="triggerFileInput()">Choisir une photo</button>
                                <button type="button" class="btn-remove" id="removePhotoBtn" onclick="removePhoto()" style="display:none;">Supprimer</button>
                                <div class="upload-hint">JPG, PNG – Max 5 MB <span class="optional">(Optionnel)</span></div>
                            </div>
                        </div>
                        <input type="file" id="photoInput" name="photo" accept="image/*" style="display:none;">
                    </div>

                    <button type="submit" class="btn-save">Configurer mon profil</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Reste de votre JavaScript inchangé…
        function exitForm() {
            if (confirm('Êtes-vous sûr de vouloir quitter ?')) window.close();
        }
        function triggerFileInput() {
            document.getElementById('photoInput').click();
        }
        function removePhoto() {
            const inp = document.getElementById('photoInput'),
                  prev = document.getElementById('previewImage'),
                  place = document.getElementById('photoPlaceholder'),
                  btn = document.getElementById('removePhotoBtn');
            inp.value = '';
            prev.style.display = 'none';
            place.style.display = 'flex';
            btn.style.display = 'none';
        }
        document.getElementById('photoInput').addEventListener('change', e => {
            const file = e.target.files[0];
            if (!file || file.size > 5*1024*1024) return alert('Max 5 MB');
            const reader = new FileReader();
            reader.onload = ev => {
                const prev = document.getElementById('previewImage'),
                      place = document.getElementById('photoPlaceholder'),
                      btn = document.getElementById('removePhotoBtn');
                prev.src = ev.target.result;
                prev.style.display = 'block';
                place.style.display = 'none';
                btn.style.display = 'block';
            };
            reader.readAsDataURL(file);
        });
        // …et le reste de votre code JS pour le drag & drop et validation en temps réel
    </script>
</body>
</html>
