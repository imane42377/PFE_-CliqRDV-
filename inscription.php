<?php
session_start();

 require_once 'config.php'; 
$conn = mysqli_connect($host, $user, $password, $dbname);
if (!$conn) {
    die("Échec de la connexion : " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        if (!empty($_POST['email']) && !empty($_POST['password'])) {
            $email = $_POST['email'];
            $password = $_POST['password'];
            $userT = $_POST['typeCompte'];
            $req="insert into connexion(email,mot_passe,typeC) values ('$email','$password','$userT')";
            $res=mysqli_query($conn,$req);
            if($res){
                
            $message_succes = "L'inscription est réussie ! Vous pouvez maintenant vous connecter.";
           
            $sql = "select id, email, mot_passe FROM connexion WHERE email = ?";
                $stmt = mysqli_prepare($conn, $sql);
                
                if ($stmt) {
                    // Lier les paramètres ; s signifie string
                    mysqli_stmt_bind_param($stmt, "s", $email);
                    
                    // Exécuter la requête
                    mysqli_stmt_execute($stmt);
                    
                    // Obtenir le résultat
                    $result = mysqli_stmt_get_result($stmt);
                    
                    if ($result && mysqli_num_rows($result) > 0) {
                        $user = mysqli_fetch_assoc($result);

            if($userT=="client"){
                $_SESSION['user_email']=$email;
                $_SESSION['user_id']=$user['id'];
                header("Refresh: 2; url=fich_client1.php");
            }else{
                $_SESSION['password']= $password;
                $_SESSION['user_email']=$email;
                $_SESSION['user_id']=$user['id'];
                header("Refresh: 2; url=fich_prestataire.php");
            }
             } else {
                        $erreur = "Adresse e-mail ou mot de passe incorrect";
                    }
            }else {
                    // Gérer les erreurs d'exécution (ex: email déjà existant si unique)
                    $reqq="select * from connexion where mot_passe='$password'";
                    $ress=mysqli_query($conn,$reqq);
                    $num=mysqli_num_rows($ress);
                    if (mysqli_errno($conn) == 1062) { // Code d'erreur pour clé unique dupliquée
                         $message_erreur = "Cette adresse e-mail est déjà utilisée.";
                    } elseif($num > 0) {
                         $message_erreur = "Ce mot de passe est déjà utilisé.";
                    }else{
                         $message_erreur = "Une erreur est survenue lors de l'inscription : " . mysqli_error($conn);
                    }
            }}}}}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - CliqRDV</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, rgb(100, 184, 253) 0%, rgb(66, 168, 252) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        /* Container principal du formulaire */
        .signup-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(30, 58, 138, 0.15);
            width: 100%;
            max-width: 450px;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        /* En-tête du formulaire */
        .signup-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .signup-header .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 2rem;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 1rem;
        }

        .signup-header h2 {
            color: #1e3a8a;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .signup-header p {
            color: #475569;
            font-size: 1rem;
        }

        /* Formulaire */
        .signup-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        /* Groupes de champs */
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-group label {
            color: #1e3a8a;
            font-weight: 600;
            font-size: 0.9rem;
        }

        /* Style pour les boutons radio */
        .account-type-label {
            color: #1e3a8a;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .radio-group {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .radio-option {
            position: relative;
        }

        .radio-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        .radio-label {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border: 2px solid #e0f2fe;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
            font-weight: normal;
        }

        .radio-label:hover {
            border-color: #3b82f6;
            background: rgba(255, 255, 255, 1);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
        }

        .radio-option input[type="radio"]:checked + .radio-label {
            border-color: #3b82f6;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(147, 197, 253, 0.1));
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .radio-icon {
            font-size: 1.5rem;
            min-width: 2rem;
            text-align: center;
        }

        .radio-content {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .radio-title {
            color: #1e3a8a;
            font-weight: 600;
            font-size: 1rem;
        }

        .radio-description {
            color: #64748b;
            font-size: 0.85rem;
            font-weight: normal;
        }

        .form-group input {
            padding: 1rem 1.5rem;
            border: 2px solid #e0f2fe;
            border-radius: 15px;
            font-size: 1rem;
            outline: none;
            transition: all 0.3s ease;
            color: #1e3a8a;
            background: rgba(255, 255, 255, 0.8);
            width: 100%;
        }

        .form-group input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background: rgba(255, 255, 255, 1);
        }

        .form-group input::placeholder {
            color: #94a3b8;
        }

        /* Champ de mot de passe avec icône intégrée */
        .password-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-container input {
            width: 100%;
            padding-right: 3.5rem !important;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            font-size: 1.2rem;
            transition: color 0.3s ease;
            z-index: 10;
        }

        .password-toggle:hover {
            color: #3b82f6;
        }

        /* Boutons */
        .button-group {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 1rem;
        }

        .btn-signup {
            padding: 0.75rem 2rem;
            background: linear-gradient(45deg, #16a34a, #15803d);
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-signup::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: all 0.5s ease;
        }

        .btn-signup:hover::before {
            left: 100%;
        }

        .btn-signup:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(34, 197, 94, 0.4);
            background: linear-gradient(45deg, #15803d, #166534);
        }

        .btn-signup:active {
            transform: translateY(0);
            box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);
        }

        .btn-cancel {
            padding: 0.75rem 2rem;
            background: linear-gradient(45deg, #64748b, #475569);
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(100, 116, 139, 0.3);
        }

        .btn-cancel:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(100, 116, 139, 0.4);
            background: linear-gradient(45deg, #475569, #334155);
        }

        /* Lien de connexion */
        .login-link {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(59, 130, 246, 0.2);
        }

        .login-link p {
            color: #475569;
            margin-bottom: 0.5rem;
        }

        .login-link a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }

        /* Messages d'erreur */
        .error-message {
            color: #dc2626;
            font-size: 0.8rem;
            margin-top: 0.25rem;
            display: none;
        }

        .form-group.error input {
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }

        .form-group.error .error-message {
            display: block;
        }

        /* Message de succès pour les mots de passe correspondants */
        .success-message {
            color: #16a34a;
            font-size: 0.8rem;
            margin-top: 0.25rem;
            display: none;
        }

        .form-group.success input {
            border-color: #16a34a;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
        }

        .form-group.success .success-message {
            display: block;
        }

        /* Animation de chargement */
        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
            margin-right: 0.5rem;
        }

        .btn-signup.loading .loading-spinner {
            display: inline-block;
        }

        .btn-signup.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .signup-container {
                padding: 2rem;
                margin: 1rem;
            }

            .radio-label {
                padding: 0.75rem;
                gap: 0.75rem;
            }

            .radio-icon {
                font-size: 1.25rem;
                min-width: 1.5rem;
            }

            .radio-title {
                font-size: 0.9rem;
            }

            .radio-description {
                font-size: 0.8rem;
            }
        }

        /* Animations d'entrée */
        .signup-container {
            animation: slideInUp 0.6s ease-out;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .php-message {
            margin-bottom: 1rem;
            padding: 0.75rem;
            border-radius: 8px;
            font-size: 0.9rem;
            text-align: center;
        }

        .php-message.success {
            color: #16a34a;
            background: rgba(53, 220, 38, 0.1);
            border: 1px solid rgba(21, 120, 42, 0.3);
        }

        .php-message.error {
            color: #dc2626;
            background: rgba(255, 0, 0, 0.1);
            border: 1px solid rgba(220, 38, 38, 0.3);
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <div class="signup-header">
            <h2>Inscription</h2>
            <p>Créez votre compte en quelques étapes</p>
        </div>
  <?php if (!empty($message_succes)) : ?>
            <div class="php-message success">
                <?= htmlspecialchars($message_succes) ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($message_erreur)) : ?>
            <div class="php-message error">
                <?= htmlspecialchars($message_erreur) ?>
            </div>
        <?php endif; ?>
        <form class="signup-form" id="signupForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label class="account-type-label">Type de compte</label>
                <div class="radio-group">
                    <div class="radio-option">
                        <input 
                            type="radio" 
                            id="client" 
                            name="typeCompte" 
                            value="client"
                            checked
                            required
                        >
                        <label for="client" class="radio-label">
                            <span class="radio-icon">👤</span>
                            <div class="radio-content">
                                <span class="radio-title">Client</span>
                                <span class="radio-description">Je souhaite prendre des rendez-vous</span>
                            </div>
                        </label>
                    </div>
                    <div class="radio-option">
                        <input 
                            type="radio" 
                            id="prestataire" 
                            name="typeCompte" 
                            value="prestataire"
                            required
                        >
                        <label for="prestataire" class="radio-label">
                            <span class="radio-icon">💼</span>
                            <div class="radio-content">
                                <span class="radio-title">Prestataire</span>
                                <span class="radio-description">Je propose mes services</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Adresse e-mail</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="votre@email.com"
                    required
                >
                <div class="error-message" id="emailError">Veuillez saisir une adresse e-mail valide</div>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <div class="password-container">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Minimum 6 caractères"
                        required
                    >
                    <button 
                        type="button" 
                        class="password-toggle" 
                        onclick="togglePassword('password')"
                        aria-label="Afficher/Masquer le mot de passe"
                    >
                        👁️
                    </button>
                </div>
                <div class="error-message" id="passwordError">Le mot de passe doit contenir au moins 6 caractères</div>
            </div>

            <div class="form-group">
                <label for="confirmPassword">Confirmer le mot de passe</label>
                <div class="password-container">
                    <input 
                        type="password" 
                        id="confirmPassword" 
                        name="confirmPassword" 
                        placeholder="Confirmez votre mot de passe"
                        required
                    >
                    <button 
                        type="button" 
                        class="password-toggle" 
                        onclick="togglePassword('confirmPassword')"
                        aria-label="Afficher/Masquer la confirmation du mot de passe"
                    >
                        👁️
                    </button>
                </div>
                <div class="error-message" id="confirmPasswordError">Les mots de passe ne correspondent pas</div>
                <div class="success-message" id="confirmPasswordSuccess">✓ Les mots de passe correspondent</div>
            </div>

            <div class="button-group">
                <button type="submit" class="btn-signup" id="signupBtn">
                    <span class="loading-spinner"></span>
                    Créer mon compte
                </button>
                <button type="button" class="btn-cancel" onclick="goBack()">
                    Annuler
                </button>
            </div>
        </form>

        <!-- Lien de connexion -->
        <div class="login-link">
            <p>Vous avez déjà un compte ?</p>
            <a href="connexion.php">Se connecter</a>
        </div>
    </div>

    <script>
        //  mot de passe
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const toggleBtn = passwordInput.nextElementSibling;
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                toggleBtn.textContent = '👁️';
            }
        }

        // Validation 
        function validateEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        function validatePassword(password) {
            return password.length >= 6;
        }

        function validatePasswordMatch(password, confirmPassword) {
            return password === confirmPassword && password.length > 0;
        }

        // Gestion des erreurs et succès
        function showValidation(fieldId, type = 'error', show = true) {
            const formGroup = document.getElementById(fieldId).closest('.form-group');
            formGroup.classList.remove('error', 'success');
            
            if (show) {
                formGroup.classList.add(type);
            }
        }

        // Validation en temps réel pour l'email
        document.getElementById('email').addEventListener('blur', function() {
            const isValid = validateEmail(this.value);
            showValidation('email', 'error', !isValid && this.value.length > 0);
        });

        // Validation en temps réel pour le mot de passe
        document.getElementById('password').addEventListener('input', function() {
            const isValid = validatePassword(this.value);
            showValidation('password', 'error', !isValid && this.value.length > 0);
            
            // Vérifier aussi la confirmation si elle est remplie
            const confirmPassword = document.getElementById('confirmPassword').value;
            if (confirmPassword.length > 0) {
                checkPasswordMatch();
            }
        });

        // Validation de la confirmation du mot de passe
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (confirmPassword.length > 0) {
                const isMatch = validatePasswordMatch(password, confirmPassword);
                
                if (isMatch) {
                    showValidation('confirmPassword', 'success', true);
                } else {
                    showValidation('confirmPassword', 'error', true);
                }
            } else {
                showValidation('confirmPassword', 'error', false);
            }
        }

        document.getElementById('confirmPassword').addEventListener('input', checkPasswordMatch);

        // Soumission du formulaire
        document.getElementById('signupForm').addEventListener('submit', function(e) {

            const accountType = document.querySelector('input[name="typeCompte"]:checked').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const signupBtn = document.getElementById('signupBtn');
            
            // Validation complète
            const emailValid = validateEmail(email);
            const passwordValid = validatePassword(password);
            const passwordsMatch = validatePasswordMatch(password, confirmPassword);
            
            showValidation('email', 'error', !emailValid);
            showValidation('password', 'error', !passwordValid);
            
            if (passwordsMatch) {
                showValidation('confirmPassword', 'success', true);
            } else {
                showValidation('confirmPassword', 'error', true);
            }
            
            if (emailValid && passwordValid && passwordsMatch) {
                // Animation de chargement
                signupBtn.classList.add('loading');
                
                // Simulation de l'inscription
                setTimeout(() => {
                    signupBtn.classList.remove('loading');
                }, 2500);
            }
        });

         function goBack() {
            window.history.back();
        }

        // Animation de focus pour les champs
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function() {
                this.closest('.form-group').style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.closest('.form-group').style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>