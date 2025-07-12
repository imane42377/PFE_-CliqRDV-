<?php
session_start();
require_once 'config.php'; 
$conn = mysqli_connect($host, $user, $password, $dbname);
if (!$conn) {
    die("Échec de la connexion : " . mysqli_connect_error());
}

$erreur = "";
$y="";

if((isset($_GET['y'])) && (isset($_GET['id']))){
    $y='jjj';
$_SESSION['idP'] = $_GET['id'];
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        if (!empty($_POST['email']) && !empty($_POST['password'])) {
            // supprime les caractères non valides
            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'];
            
            // Valider le format de l'email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $erreur = "Format d'adresse e-mail invalide";
            } else {
                //une requête préparée pour éviter l'injection SQL
                $sql = "select * FROM connexion WHERE email = ? and mot_passe= ?";
                $stmt = mysqli_prepare($conn, $sql);
                
                if ($stmt) {
                    // Lier les paramètres ; s signifie string
                    mysqli_stmt_bind_param($stmt, "ss", $email,$password);
                    
                    // Exécuter la requête
                    mysqli_stmt_execute($stmt);
                    
                    // Obtenir le résultat
                    $result = mysqli_stmt_get_result($stmt);
                    
                    if ($result && mysqli_num_rows($result) > 0) {
                        $user = mysqli_fetch_assoc($result);
                        if ($password === $user['mot_passe']) {
                            // Définir les variables de session
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['user_email'] = $user['email'];
                            $_SESSION['logged_in'] = true;
                            
                            // Régénérer l'ID de session pour la sécurité
                            session_regenerate_id(true);
                            
                            if($user['typeC']== 'client'){
                            mysqli_stmt_close($stmt);
                             if(!empty ($y)){
                             header('Location: NewRDV.php?id='.$_SESSION['idP']);
                             }else{header('Location: dashboardClient.php');}
                            }else{
                                 if(!empty ($y)){
                             header('Location: NewRDV.php?id='.$_SESSION['idP']);
                             }else{header('Location: dashboardDoc.php');}
                            }                            
                       
                            
                        } else {
                            $erreur = "Adresse e-mail ou mot de passe incorrect";
                        }
                    } else {
                        $erreur = "Adresse e-mail ou mot de passe incorrect";
                    }
                    
                    // Fermer la requête préparée
                    mysqli_stmt_close($stmt);
                } else {
                    $erreur = "Erreur de préparation de la requête";
                    error_log("Erreur de préparation : " . mysqli_error($conn));
                }
            }
        } else {
            $erreur = "Veuillez remplir tous les champs";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - CliqRDV</title>
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

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(30, 58, 138, 0.15);
            width: 100%;
            max-width: 450px;
            border: 1px solid rgba(59, 130, 246, 0.2);
            animation: slideInUp 0.6s ease-out;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h2 {
            color: #1e3a8a;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: #475569;
            font-size: 1rem;
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

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

        .form-group input {
            padding: 1rem 1.5rem;
            border: 2px solid #e0f2fe;
            border-radius: 15px;
            font-size: 1rem;
            outline: none;
            transition: all 0.3s ease;
            color: #1e3a8a;
            background: rgba(255, 255, 255, 0.8);
        }

        .form-group input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background: rgba(255, 255, 255, 1);
        }

        .form-group input::placeholder {
            color: #94a3b8;
        }

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
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            font-size: 1.2rem;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: #3b82f6;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #3b82f6;
        }

        .remember-me label {
            color: #475569;
            font-size: 0.9rem;
            cursor: pointer;
        }

        .forgot-password {
            color: #3b82f6;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .forgot-password:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }

        .button-group {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 1rem;
        }

        .btn-login {
            padding: 0.75rem 2rem;
            background: linear-gradient(45deg, #3b82f6, #1d4ed8);
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: all 0.5s ease;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
            background: linear-gradient(45deg, #1d4ed8, #1e40af);
        }

        .btn-login:active {
            transform: translateY(0);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
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

        .signup-link {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(59, 130, 246, 0.2);
        }

        .signup-link p {
            color: #475569;
            margin-bottom: 0.5rem;
        }

        .signup-link a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .signup-link a:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }

        .error-message {
            color: #dc2626;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            padding: 0.75rem;
            background: rgba(220, 38, 38, 0.1);
            border: 1px solid rgba(220, 38, 38, 0.3);
            border-radius: 8px;
            display: block;
        }

        .form-group.error input {
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }

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

        .btn-login.loading .loading-spinner {
            display: inline-block;
        }

        .btn-login.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
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

        @media (max-width: 768px) {
            .login-container {
                padding: 2rem;
                margin: 1rem;
            }

            .form-options {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!--button class="exit-button" onclick="goBack()" title="Fermer">×</button-->
        
        <div class="login-header">
            <h2>Connexion</h2>
            <p>Accédez à votre espace personnel</p>
        </div>

        <form class="login-form" id="loginForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <?php if (!empty($erreur)) : ?>
                <div class="error-message">
                    <?= htmlspecialchars($erreur) ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="email">Adresse e-mail</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="votre@email.com"
                    value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <div class="password-container">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Votre mot de passe"
                        required
                        minlength="6"
                    >
                    <button
                        type="button" 
                        class="password-toggle" 
                        onclick="togglePassword()"
                        aria-label="Afficher/Masquer le mot de passe"
                    >
                        👁️
                    </button>
                </div>
            </div>

            <div class="form-options">
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Se souvenir de moi</label>
                </div>
                <a href="#" class="forgot-password">Mot de passe oublié ?</a>
            </div>

            <div class="button-group">
                <button type="submit" class="btn-login" id="loginBtn">
                    <span class="loading-spinner"></span>
                    Se connecter
                </button>
                <button type="button" class="btn-cancel" onclick="goBack()">
                    Annuler
                </button>
            </div>
        </form>

        <div class="signup-link">
            <p>Vous n'avez pas encore de compte ?</p>
            <a href="inscription.php">Créer un compte</a>
        </div>
    </div>

    <script>
        function annuler() {
            document.getElementById("email").value = "";
            document.getElementById("password").value = "";
        }

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.querySelector('.password-toggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                toggleBtn.textContent = '👁️';
            }
        }

        function goBack() {
            window.history.back();
        }

        // Form submission with loading animation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const loginBtn = document.getElementById('loginBtn');
            loginBtn.classList.add('loading');
        });

        // Enhanced form interactions
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function() {
                this.closest('.form-group').style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.closest('.form-group').style.transform = 'scale(1)';
            });
        });

        // Client-side validation
        document.getElementById('email').addEventListener('input', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (this.value && !emailRegex.test(this.value)) {
                this.setCustomValidity('Veuillez saisir une adresse e-mail valide');
            } else {
                this.setCustomValidity('');
            }
        });

        document.getElementById('password').addEventListener('input', function() {
            if (this.value.length > 0 && this.value.length < 6) {
                this.setCustomValidity('Le mot de passe doit contenir au moins 6 caractères');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>