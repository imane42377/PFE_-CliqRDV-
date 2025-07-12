<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// — 1) Vérifier que l’utilisateur est bien connecté (si votre logique l’exige)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'config.php'; 
$conn = mysqli_connect($host, $user, $password, $dbname);
if (!$conn) {
    die("Erreur de connexion : " . mysqli_connect_error());
}

// — 3) Vérifier que tous les champs requis sont présents, y compris “experience” et “jours[]”
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
        $_POST['experience'],   // ← Champ “experience”
        $_POST['jours'],        // ← Tableau des jours cochés
        $_FILES['photo']        // ← Photo
    )
) {
    // — 4) Lecture et nettoyage des données
    $name             = trim($_POST['name']);
    $specialty        = trim($_POST['specialty']);
    $location         = trim($_POST['location']);
    $phone            = trim($_POST['phone']);
    $fax              = trim($_POST['fax']);
    $email            = trim($_POST['email']);
    $academic         = trim($_POST['academic']);
    $diplomas         = trim($_POST['diplomas']);
    $consultation_fee = trim($_POST['consultation_fee']);
    $experience       = trim($_POST['experience']);      // ← Nouveau champ
    $mot_de_passe     = $_SESSION['password'] ?? '';
    if ($mot_de_passe === "") {
        die("Le mot de passe n'est pas défini en session.");
    }

    // — 5) Gestion de l’upload de la photo
    $uploadDir = __DIR__ . "/uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $photo_name = basename($_FILES['photo']['name']);
    $photo_tmp  = $_FILES['photo']['tmp_name'];
    $photo_path = "uploads/" . $photo_name;

    if (!is_uploaded_file($photo_tmp)) {
        die("Erreur : fichier photo non valide.");
    }
    if (!move_uploaded_file($photo_tmp, $uploadDir . $photo_name)) {
        die("Erreur lors de l'enregistrement de la photo.");
    }

    // — 6) Insertion dans la table “prestataire” (avec “experience”)
    $sql_prest = "
        INSERT INTO prestataire
            (nom_prenom, specialite, localisation, tele, fax, email,
             parcours, diplomes, tarif, experience, mdp, photo_profil)
        VALUES
            (?,         ?,          ?,           ?,    ?,   ?,
             ?,       ?,       ?,      ?,          ?,   ?)
    ";
    $stmt_prest = mysqli_prepare($conn, $sql_prest);
    if (!$stmt_prest) {
        die("Erreur de préparation (prestataire) : " . mysqli_error($conn));
    }

    // Bind des 12 paramètres : tous en “s” (string)
    mysqli_stmt_bind_param(
        $stmt_prest,
        "ssssssssssss",
        $name,
        $specialty,
        $location,
        $phone,
        $fax,
        $email,
        $academic,
        $diplomas,
        $consultation_fee,
        $experience,     // ← “experience”
        $mot_de_passe,
        $photo_path
    );

    if (!mysqli_stmt_execute($stmt_prest)) {
        echo "Erreur lors de l'insertion du prestataire : " . mysqli_stmt_error($stmt_prest);
        mysqli_stmt_close($stmt_prest);
        mysqli_close($conn);
        exit;
    }

    // Récupérer l’ID généré pour ce prestataire
    $id_prestataire = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt_prest);
    header('location:index.php');


    // — 7) Insertion des horaires dans la table “horairestravail”
    // Structure attendue : id (AUTO_INCREMENT), prestataire_id, jour_semaine, heure_debut, heure_fin
    if (is_array($_POST['jours'])) {
        $sql_horaire = "
            INSERT INTO horairestravail
                (prestataire_id, jour_semaine, heure_debut, heure_fin)
            VALUES
                (?,            ?,            ?,           ?)
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

    // — 8) Fermer la connexion MySQL
    mysqli_close($conn);

} else {
    // Si un champ requis manque
    echo "<p>Certains champs sont manquants ou le formulaire n’a pas été envoyé correctement.</p>";
}
?>
