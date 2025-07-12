<?php
session_start();

// 0) Si l’utilisateur n’est pas connecté, on le redirige vers la page de connexion
if (empty($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit;
}
require_once 'config.php'; 

$conn = mysqli_connect($host, $user, $password, $dbname);
if (mysqli_connect_errno()) {
    die("Erreur de connexion MySQL : " . mysqli_connect_error());
}

// 2) Récupération des infos session
$id    = $_SESSION['user_id'];
$email = $_SESSION['user_email'];

// 3) Lecture du profil client
$sql = "SELECT * FROM client WHERE client_id = '$id' OR email = '$email' LIMIT 1";
$res = mysqli_query($conn, $sql);
if ($res && mysqli_num_rows($res) > 0) {
    $row = mysqli_fetch_assoc($res);
} else {
    // pas de données, préparer un tableau vide
    $row = [
        'photo'             => '',
        'nom_complet'       => '',
        'date_naissance'    => '',
        'adresse'           => '',
        'numero_telephone'  => '',
        'email'             => '',
    ];
}

// 4) Mise à jour du profil si formulaire soumis
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_profile'])) {
    // Récupération des champs
    $nom_complet       = $_POST['nom_complet'];
    $date_naissance    = $_POST['date_naissance'];
    $adresse           = $_POST['adresse'];
    $numero_telephone  = $_POST['numero_telephone'];
    $new_email         = $_POST['email'];

    // Gestion de l'upload de photo
    $photoPath = $row['photo']; // conserver l'ancienne si pas de nouveau upload
    if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        // Créer le dossier uploads s'il n'existe pas
        if (!is_dir(__DIR__ . '/uploads')) {
            mkdir(__DIR__ . '/uploads', 0755, true);
        }
        $tmpName = $_FILES['photo']['tmp_name'];
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $newName = 'uploads/photo_' . uniqid() . '.' . $ext;
        if (move_uploaded_file($tmpName, __DIR__ . '/' . $newName)) {
            $photoPath = $newName;
            // (optionnel) supprimer l'ancienne photo :
            // if (!empty($row['photo']) && file_exists(__DIR__ . '/' . $row['photo'])) {
            //     unlink(__DIR__ . '/' . $row['photo']);
            // }
        }
    }

    // Préparation de la requête UPDATE
    $update_sql = "
        UPDATE client
        SET nom_complet      = ?,
            date_naissance   = ?,
            adresse          = ?,
            numero_telephone = ?,
            email            = ?,
            photo            = ?
        WHERE client_id = ?
    ";
    $stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($stmt, 'ssssssi',
        $nom_complet,
        $date_naissance,
        $adresse,
        $numero_telephone,
        $new_email,
        $photoPath,
        $id
    );
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['user_email'] = $new_email;
        echo "<script>
                alert('Profil mis à jour avec succès !');
                window.location.href = 'profileClient.php';
              </script>";
        exit;
    } else {
        echo "<script>alert('Erreur lors de la mise à jour du profil.');</script>";
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CliqRDV - Profil Client</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .top-container {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 1.5rem;
            flex: 1;
        }
        .profile-header {
            display: grid;
            grid-template-columns: 180px 1fr 300px;
            gap: 2.5rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.08);
            padding: 2.5rem;
            margin-bottom: 3rem;
            transition: transform 0.3s ease;
        }
        .profile-header:hover { transform: translateY(-5px); }
        .profile-picture {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .profile-picture:hover { transform: scale(1.05); }
        .profile-info { flex: 1; }
        .profile-name {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.75rem;
        }
        .profile-bio {
            color: var(--text-medium);
            font-size: 1.1rem;
            margin-bottom: 2rem;
            max-width: 600px;
        }
        .profile-meta { display: flex; gap: 2rem; margin-bottom: 1.5rem; }
        .meta-item {
            display: flex; align-items: center; gap: 0.75rem;
            color: var(--text-medium); font-size: 1rem;
        }
        .meta-item i { color: var(--primary-color); font-size: 1.2rem; }
        .action-buttons { display: flex; gap: 1.5rem; margin-top: 1.5rem; }
        .btn {
            padding: 0.8rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
            font-size: 1rem;
        }
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        .sidebar-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.08);
            padding: 1.5rem;
            margin-bottom: 3rem;
            transition: transform 0.3s ease;
        }
        .sidebar-card:hover { transform: translateY(-5px); }
        .sidebar-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--text-dark);
        }
        .contact-info { display: flex; flex-direction: column; gap: 1.5rem; }
        .contact-item { display: flex; align-items: flex-start; gap: 1rem; }
        .contact-icon { color: var(--primary-color); font-size: 1.3rem; margin-top: 0.3rem; }
        .contact-text { color: var(--text-medium); font-size: 1rem; }
        .contact-text strong {
            color: var(--text-dark); display: block; margin-bottom: 0.3rem;
        }
        .exit-button {
            position: fixed; top: 20px; right: 20px;
            width: 50px; height: 50px;
            background: rgba(255,255,255,0.95);
            border: none; border-radius: 50%;
            cursor: pointer; display: flex; align-items: center;
            justify-content: center; font-size: 1.5rem;
            font-weight: bold; color: var(--text-medium);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease; z-index: 1000;
            backdrop-filter: blur(10px);
        }
        .exit-button:hover {
            background: var(--error-color); color: white;
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(255,71,87,0.3);
        }
        .modal {
            display: none; position: fixed; top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); z-index: 1000;
            justify-content: center; align-items: center;
        }
        .modal-content {
            background: white; border-radius: 16px;
            padding: 2rem; width: 100%; max-width: 600px;
            box-shadow: 0 6px 25px rgba(0,0,0,0.2);
            position: relative;
        }
        .modal-header {
            display: flex; justify-content: space-between;
            align-items: center; margin-bottom: 1.5rem;
        }
        .modal-title {
            font-size: 1.5rem; font-weight: 700;
            color: var(--text-dark);
        }
        .close-modal {
            background: none; border: none;
            font-size: 1.5rem; color: var(--text-medium);
            cursor: pointer; transition: color 0.3s ease;
        }
        .close-modal:hover { color: var(--error-color); }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label {
            display: block; font-weight: 600;
            color: var(--text-dark); margin-bottom: 0.5rem;
            height: 17px;
        }
        .form-group input {
            width: 100%; padding: 0.8rem;
            border: 1px solid var(--border-color);
            border-radius: 8px; font-size: 1rem;
            color: var(--text-dark);
            transition: border-color 0.3s ease;
        }
        .form-group input:focus {
            outline: none; border-color: var(--primary-color);
        }
        .modal-footer {
            display: flex; gap: 1rem; justify-content: flex-end;
        }
            
        label.btn-photo {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;              /* slightly more space between icon & text */
            padding: 1.2rem 2.4rem;     /* taller & wider button */
            background-color: var(--primary-color);
            color: #fff;
            font-size: 1.4rem;          /* larger text */
            font-weight: 700;
            border-radius: 12px;        /* slightly rounder */
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            }

        label.btn-photo:hover {
            background-color: var(--secondary-color);
            transform: scale(1.03);     /* subtle grow on hover */
            }

        label.btn-photo i {
            font-size: 1.6rem;          /* bigger icon */
            }

            /* hide the native file input */
        label.btn-photo input[type="file"] {
            display: none;
            }

        @media (max-width: 1024px) {
            .profile-header {
                grid-template-columns: 1fr;
                text-align: center; gap: 1.5rem;
            }
            .profile-picture { margin: 0 auto; }
            .sidebar { margin-top: 2rem; }
        }
        @media (max-width: 768px) {
            .top-container { margin: 2rem 1rem; }
            .profile-name { font-size: 1.6rem; }
            .profile-meta {
                flex-direction: column; gap: 1rem;
                align-items: center;
            }
            .action-buttons {
                flex-direction: column; align-items: center;
            }
            .btn { width: 100%; justify-content: center; }
        }
        @media (max-width: 480px) {
            .profile-picture {
                width: 120px; height: 120px;
            }
            .profile-name { font-size: 1.4rem; }
            .sidebar-card { padding: 1.5rem; }
            .modal-content {
                margin: 1rem; padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <button class="exit-button" onclick="window.location.href='dashboardClient.php';">
        <i class="fas fa-times"></i>
    </button>

    <div class="top-container">
        <div class="profile-header">
            <?php
            if (!empty($row['photo']) && file_exists(__DIR__ . '/' . $row['photo'])) {
                echo '<img src="' . htmlspecialchars($row['photo'], ENT_QUOTES) . '" class="profile-picture">';
            } else {
                echo '<i class="fas fa-user-circle profile-picture"></i>';
            }
            ?>
            <div class="profile-info">
                <h1 class="profile-name">
                    <?php echo htmlspecialchars($row['nom_complet'] ?: 'Utilisateur'); ?>
                </h1>
                <p class="profile-bio">Client fidèle de CliqRDV, passionné par la santé et le bien-être.</p>
                <div class="profile-meta">
                    <div class="meta-item">
                        <i class="fas fa-birthday-cake"></i>
                        <span><?php echo htmlspecialchars($row['date_naissance'] ?: 'Non spécifié'); ?></span>
                    </div>
                </div>
                <div class="action-buttons">
                    <button class="btn btn-primary" onclick="openModal()">
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
                                <span><?php echo htmlspecialchars($row['adresse'] ?: 'Non spécifié'); ?></span>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone-alt contact-icon"></i>
                            <div class="contact-text">
                                <strong>Téléphone</strong>
                                <span><?php echo htmlspecialchars($row['numero_telephone'] ?: 'Non spécifié'); ?></span>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope contact-icon"></i>
                            <div class="contact-text">
                                <strong>Email</strong>
                                <span><?php echo htmlspecialchars($row['email'] ?: 'Non spécifié'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal pour modifier le profil -->
        <div class="modal" id="editProfileModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Modifier le profil</h2>
                    <button class="close-modal" onclick="closeModal()">&times;</button>
                </div>
                <form method="post" action="" enctype="multipart/form-data">
                    <input type="hidden" name="update_profile" value="1">
                    <div class="form-group">
                        <label for="nom_complet">Nom complet</label>
                        <input type="text" id="nom_complet" name="nom_complet"
                               value="<?php echo htmlspecialchars($row['nom_complet']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="date_naissance">Date de naissance</label>
                        <input type="date" id="date_naissance" name="date_naissance"
                               value="<?php echo htmlspecialchars($row['date_naissance']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="adresse">Adresse</label>
                        <input type="text" id="adresse" name="adresse"
                               value="<?php echo htmlspecialchars($row['adresse']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="numero_telephone">Téléphone</label>
                        <input type="tel" id="numero_telephone" name="numero_telephone"
                               value="<?php echo htmlspecialchars($row['numero_telephone']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email"
                               value="<?php echo htmlspecialchars($row['email']); ?>" required>
                    </div>
                    <!-- HTML -->
                    <div class="form-group">
                    <label for="photo" class="btn-photo">
                        <i class="fas fa-camera"></i> Choisir une photo
                        <input type="file" id="photo" name="photo" accept="image/*">
                    </label>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" onclick="closeModal()">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function openModal() {
        document.getElementById('editProfileModal').style.display = 'flex';
    }
    function closeModal() {
        document.getElementById('editProfileModal').style.display = 'none';
    }
    </script>
</body>
</html>
