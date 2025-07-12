<?php
session_start();

require_once 'config.php'; 
$conn = mysqli_connect($host, $user, $password, $dbname);
if (!$conn) {
    die("Erreur de connexion : " . mysqli_connect_error());
}

// Vérifier que tous les champs requis sont présents
if (
    isset(
        $_POST['name'],
        $_POST['specialty'],
        $_POST['location'],
        $_POST['phone'],
        $_POST['fax'],
        $_POST['email'],
        $_POST['academic'],
        $_POST['consultation_fee'],
        $_POST['diplomas'],
        $_POST['experience'],
        $_POST['jours']
    )
) {
    // Nettoyage des données
    $name             = trim($_POST['name']);
    $specialty        = trim($_POST['specialty']);
    $location         = trim($_POST['location']);
    $phone            = trim($_POST['phone']);
    $fax              = trim($_POST['fax']);
    $email            = trim($_POST['email']);
    $academic         = trim($_POST['academic']);
    $diplomas         = trim($_POST['diplomas']);
    $consultation_fee = trim($_POST['consultation_fee']);
    $experience       = trim($_POST['experience']);
    $mot_de_passe     = $_SESSION['password'] ?? '';
    
    if ($mot_de_passe === "") {
        die("Le mot de passe n'est pas défini en session.");
    }

    // Gestion de l'upload de la photo
    $photo_path = NULL;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . "/Uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $photo_name = basename($_FILES['photo']['name']);
        $photo_tmp  = $_FILES['photo']['tmp_name'];
        $photo_path = "Uploads/" . $photo_name;

        if (!move_uploaded_file($photo_tmp, $uploadDir . $photo_name)) {
            echo "Erreur lors de l'enregistrement de la photo.";
        }
    }

    // Insertion dans la table prestataire
    $sql_prest = "
        INSERT INTO prestataire
            (prestataire_id, nom_prenom, specialite, localisation, tele, fax, email,
             parcours, diplomes, tarif, experience, mdp, photo_profil)
        VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";
    $stmt_prest = mysqli_prepare($conn, $sql_prest);
    if (!$stmt_prest) {
        die("Erreur de préparation (prestataire) : " . mysqli_error($conn));
    }

    // Utilisation de l'ID de session comme prestataire_id
    $prestataire_id = $_SESSION['user_id'];

    mysqli_stmt_bind_param(
        $stmt_prest,
        "issssssssssss",
        $prestataire_id,
        $name,
        $specialty,
        $location,
        $phone,
        $fax,
        $email,
        $academic,
        $diplomas,
        $consultation_fee,
        $experience,
        $mot_de_passe,
        $photo_path
    );

    if (!mysqli_stmt_execute($stmt_prest)) {
        die("Erreur lors de l'insertion du prestataire : " . mysqli_stmt_error($stmt_prest));
    }
    
    // Récupération du dernier ID inséré (si la table a un auto-increment)
    $id_prestataire = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt_prest);

    // Insertion des horaires dans la table horairestravail
    if (is_array($_POST['jours'])) {
        $sql_horaire = "
            INSERT INTO horairestravail
                (prestataire_id, jour_semaine, heure_debut, heure_fin)
            VALUES
                (?, ?, ?, ?)
        ";
        $stmt_horaire = mysqli_prepare($conn, $sql_horaire);
        
        if (!$stmt_horaire) {
            die("Erreur de préparation (horaire) : " . mysqli_error($conn));
        }

        foreach ($_POST['jours'] as $jour) {
            $jour_clean = trim($jour);
            
            if (
                isset($_POST['heure_debut'][$jour_clean]) &&
                isset($_POST['heure_fin'][$jour_clean]) &&
                !empty($_POST['heure_debut'][$jour_clean]) &&
                !empty($_POST['heure_fin'][$jour_clean])
            ) {
                $heure_debut = $_POST['heure_debut'][$jour_clean];
                $heure_fin   = $_POST['heure_fin'][$jour_clean];
                
                mysqli_stmt_bind_param(
                    $stmt_horaire,
                    "isss",
                    $prestataire_id,  // Utilisation du même ID que pour prestataire
                    $jour_clean,
                    $heure_debut,
                    $heure_fin
                );
                
                if (!mysqli_stmt_execute($stmt_horaire)) {
                    echo "<p>Erreur lors de l'insertion des horaires pour $jour_clean : " . mysqli_stmt_error($stmt_horaire) . "</p>";
                }
            }
        }
        
        mysqli_stmt_close($stmt_horaire);
    } else {
        echo "<p>Aucun jour de disponibilité n'a été sélectionné.</p>";
    }

    mysqli_close($conn);
    header('Location: dashboardDoc.php');
    exit;

} else {
    echo "<p>Certains champs sont manquants ou le formulaire n'a pas été envoyé correctement.</p>";
}
?>