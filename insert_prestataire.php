<?php
session_start();
require_once 'config.php'; 
$conn = mysqli_connect($host, $user, $password, $dbname);
if (!$conn) {
    die("Erreur de connexion : " . mysqli_connect_error());
}
$emailSession = $_SESSION['user_email'];
$idSession= $_SESSION['user_id'];

// 3) Vérifier que tous les champs requis sont présents, sauf “photo” qui est optionnel
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
    // 4) Lecture et nettoyage des données
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


    // 5) Gestion de l’upload de la photo (optionnel)
    $photo_path = NULL; // Par défaut, pas de photo
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . "/Uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $photo_name = basename($_FILES['photo']['name']);
        $photo_tmp  = $_FILES['photo']['tmp_name'];
        $photo_path = "Uploads/" . $photo_name;

        if (!is_uploaded_file($photo_tmp)) {
            echo "Erreur : fichier photo non valide.";
        } elseif (!move_uploaded_file($photo_tmp, $uploadDir . $photo_name)) {
            echo "Erreur lors de l'enregistrement de la photo.";
        }
    }

    // 6) Insertion dans la table “prestataire” (avec “experience”)
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
    
    // Bind des paramètres : tous en “s” (string), photo_path peut être NULL
    mysqli_stmt_bind_param(
        $stmt_prest,
        "issssssssssss",
        $idSession,
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
        echo "Erreur lors de l'insertion du prestataire : " . mysqli_stmt_error($stmt_prest);
        mysqli_stmt_close($stmt_prest);
        mysqli_close($conn);
        exit;
    }
    $id_prestataire = mysqli_insert_id($conn); // récupérer l'ID du prestataire ajouté
    mysqli_stmt_close($stmt_prest);

    // 7) Insertion des horaires dans la table “horairestravail”
    if (is_array($_POST['jours'])) {
        $sql_horaire = "
            INSERT INTO horairestravail
                (prestataire, jour_semaine, heure_debut, heure_fin)
            VALUES
                (?, ?, ?, ?)
        ";

        foreach ($_POST['jours'] as $jour) {
            $jour_clean = trim($jour);

            // Vérifier que les champs “heure_debut[$jour]” et “heure_fin[$jour]” sont bien envoyés
            if (
                isset($_POST['heure_debut'][$jour_clean]) &&
                isset($_POST['heure_fin'][$jour_clean])
            ) {
                $heure_debut = $_POST['heure_debut'][$jour_clean];
                $heure_fin   = $_POST['heure_fin'][$jour_clean];

                $stmt_horaire = mysqli_prepare($conn, $sql_horaire);
                if ($stmt_horaire) {
                    mysqli_stmt_bind_param(
                        $stmt_horaire,
                        "isss",
                        $id_prestataire,
                        $jour_clean,
                        $heure_debut,
                        $heure_fin
                    );
                    mysqli_stmt_execute($stmt_horaire);
                    mysqli_stmt_close($stmt_horaire);

                } else {
                    echo "<p>Erreur de préparation (horaire) pour le jour \"$jour_clean\" : " . mysqli_error($conn) . "</p>";
                }
            } else {
                echo "<p>Attention : aucune plage horaire définie pour le jour « $jour_clean ».</p>";
            }
        }
    } else {
        echo "<p>Aucun jour de disponibilité n’a été sélectionné.</p>";
    }

    mysqli_close($conn);
     header('location:dashboardDoc.php');

} else {
    // Si un champ requis manque
    echo "<p>Certains champs sont manquants ou le formulaire n’a pas été envoyé correctement.</p>";
}
?>