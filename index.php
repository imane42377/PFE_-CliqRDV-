<?php
session_start();
require_once 'config.php';  

$conn = mysqli_connect($host, $user, $password, $dbname);
if (!$conn) {
    die("Erreur de connexion MySQL : " . mysqli_connect_error());
}
// 2) Récupération des filtres GET
$keyword       = trim($_GET['specialite']  ?? '');
$location      = trim($_GET['location'] ?? '');

// Détecter si on a appuyé sur le bouton Rechercher
$searchClicked = isset($_GET['search']);

// Initialiser la liste
$docs = [];

if ($searchClicked) {
    // 3) Construction dynamique de la requête
    $sql    = "SELECT * FROM prestataire WHERE 1=1";
    $params = [];
    $types  = "";

    if ($keyword !== "") {
        $sql      .= " AND (nom_prenom LIKE ? OR specialite LIKE ?)";
        $params[]  = "%{$keyword}%";
        $params[]  = "%{$keyword}%";
        $types    .= "ss";
    }
    if ($location !== "") {
        $sql      .= " AND localisation LIKE ?";
        $params[]  = "%{$location}%";
        $types    .= "s";
    }

    // 4) Préparation & exécution
    $stmt = mysqli_prepare($conn, $sql);
    if ($types !== "") {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $res  = mysqli_stmt_get_result($stmt);
    $docs = mysqli_fetch_all($res, MYSQLI_ASSOC);

    mysqli_stmt_close($stmt);
}

mysqli_close($conn);

// On n’affiche les résultats que si on a cliqué
$showResults = $searchClicked;


?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CliqRDV - Vos rendez-vous médicaux en ligne</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #1e3a8a;
            background: linear-gradient(135deg, rgb(100, 184, 253) 0%, rgb(66, 168, 252) 100%);
            min-height: 100vh;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(30, 58, 138, 0.15);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.8rem;
            font-weight: bold;
            color: #2563eb;
        }

        .logo::before {
            content: "📅";
            font-size: 2rem;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: #1e3a8a;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-links a:hover {
            color: #3b82f6;
            transform: translateY(-2px);
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 50%;
            background: linear-gradient(90deg, #3b82f6, #1d4ed8);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .auth-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn-secondary {
            padding: 0.7rem 1.5rem;
            border: 2px solid #3b82f6;
            background: transparent;
            color: #3b82f6;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: linear-gradient(45deg, #3b82f6, #1d4ed8);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.4);
        }

        .btn-primary {
            padding: 0.7rem 1.5rem;
            background: linear-gradient(45deg, #1d4ed8, #1e40af);
            color: white;
            border: none;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(29, 78, 216, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(29, 78, 216, 0.4);
            background: linear-gradient(45deg, #1e40af, #1e3a8a);
        }

        /* Hero Section */
        .hero {
            margin-top: 80px;
            padding: 4rem 0;
            text-align: center;
            color: #1e3a8a;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><radialGradient id="a" cx="50%" cy="50%"><stop offset="0%" stop-color="rgba(255,255,255,0.2)"/><stop offset="100%" stop-color="transparent"/></radialGradient></defs><circle cx="200" cy="300" r="150" fill="url(%23a)"/><circle cx="800" cy="700" r="200" fill="url(%23a)"/><circle cx="600" cy="200" r="100" fill="url(%23a)"/></svg>');
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            animation: slideInUp 1s ease-out;
            color: #0f172a;
            text-shadow: 0 2px 4px rgba(255, 255, 255, 0.3);
        }

        .hero p {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            color: #1e3a8a;
            animation: slideInUp 1s ease-out 0.2s both;
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

        /* Search Section */
        .search-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            margin: 2rem auto;
            max-width: 1000px;
            box-shadow: 0 10px 40px rgba(30, 58, 138, 0.15);
            animation: slideInUp 1s ease-out 0.4s both;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .search-container {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-input {
            flex: 1;
            padding: 1rem 1.5rem;
            border: 2px solid #e0f2fe;
            border-radius: 25px;
            font-size: 1rem;
            outline: none;
            transition: all 0.3s ease;
            min-width: 300px;
            color: #1e3a8a;
        }

        .search-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .search-input::placeholder {
            color: #64748b;
        }

        .search-btn {
            padding: 1rem 2rem;
            background: linear-gradient(45deg, #3b82f6, #1d4ed8);
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
            background: linear-gradient(45deg, #1d4ed8, #1e40af);
        }

        .book-btn{
            margin: auto;
            padding: 1rem 2rem;
            background: linear-gradient(45deg, #0ea5e9, #0284c7);
            color: #ffffff;
            border: none;
            border-radius: 25px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3);
        }

         .book-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(14, 165, 233, 0.4);
            background: linear-gradient(45deg, #0284c7, #0369a1);
        }

        /* Features Section */
        .features {
            padding: 4rem 0;
            background: rgba(255, 255, 255, 0.1);
            margin: 3rem 0;
            backdrop-filter: blur(5px);
        }

        .features h2 {
            text-align: center;
            font-size: 2.5rem;
            color: #0f172a;
            margin-bottom: 3rem;
            text-shadow: 0 2px 4px rgba(255, 255, 255, 0.3);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            padding: 2rem 0;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 2rem;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(30, 58, 138, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(59, 130, 246, 0.1);
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.1), transparent);
            transition: all 0.5s ease;
        }

        .feature-card:hover::before {
            left: 100%;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(30, 58, 138, 0.15);
            border-color: rgba(59, 130, 246, 0.3);
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            color: #1e3a8a;
            margin-bottom: 1rem;
        }

        .feature-card p {
            color: #475569;
            line-height: 1.6;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .search-container {
                flex-direction: column;
            }
            
            .search-input {
                min-width: 100%;
            }

            .features h2 {
                font-size: 2rem;
            }
        }

        /* Animations */
        .fade-in {
            animation: fadeIn 1s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        /* Loading animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

                    .results-section {
                    background: rgba(255, 255, 255, 0.98);
                    backdrop-filter: blur(12px);
                    border-radius: 20px;
                    margin: 3rem auto;
                    max-width: 1200px;
                    box-shadow: 0 15px 50px rgba(30, 58, 138, 0.2);
                    border: 1px solid rgba(59, 130, 246, 0.3);
                    overflow: hidden;
                    display: none;
                    animation: fadeIn 0.6s cubic-bezier(0.22, 1, 0.36, 1);
                    transform-origin: top center;
                    }

                    .results-section.active {
                    display: block;
                    }

                    .results-header {
                    position: relative;
                    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
                    color: #fff;
                    padding: 1.8rem 2rem;
                    text-align: center;
                    box-shadow: 0 4px 20px rgba(29, 78, 216, 0.3);
                    }

                    .results-header h2 {
                    font-size: 2rem;
                    margin-bottom: 0.5rem;
                    font-weight: 700;
                    letter-spacing: 0.5px;
                    }

                    .results-count {
                    opacity: 0.9;
                    font-size: 1.1rem;
                    }

                    .close-results-btn {
                    position: absolute;
                    top: 1.5rem;
                    right: 1.5rem;
                    background: rgba(255, 255, 255, 0.2);
                    border: none;
                    color: #fff;
                    width: 40px;
                    height: 40px;
                    border-radius: 50%;
                    font-size: 1.5rem;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    }

                    .close-results-btn:hover {
                    background: rgba(255, 255, 255, 0.3);
                    transform: rotate(90deg);
                    }

                    .results-container {
                    padding: 2.5rem;
                    }

                    .provider-card {
                    display: flex;
                    align-items: center;
                    gap: 2rem;
                    background: #fff;
                    border-radius: 18px;
                    padding: 2rem;
                    margin-bottom: 1.8rem;
                    box-shadow: 0 5px 25px rgba(30, 58, 138, 0.08);
                    border: 1px solid rgba(59, 130, 246, 0.15);
                    transition: all 0.4s cubic-bezier(0.22, 1, 0.36, 1);
                    position: relative;
                    overflow: hidden;
                    }

                    .provider-card:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 10px 30px rgba(30, 58, 138, 0.15);
                    border-color: rgba(59, 130, 246, 0.3);
                    }

                    .provider-card::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 5px;
                    height: 100%;
                    background: linear-gradient(to bottom, #3b82f6, #1d4ed8);
                    }

                    .provider-photo img {
                    width: 120px;
                    height: 120px;
                    border-radius: 50%;
                    object-fit: cover;
                    border: 4px solid #e0f2fe;
                    box-shadow: 0 5px 15px rgba(30, 58, 138, 0.1);
                    transition: all 0.3s ease;
                    }

                    .provider-card:hover .provider-photo img {
                    transform: scale(1.05);
                    box-shadow: 0 8px 25px rgba(30, 58, 138, 0.15);
                    }

                    .provider-info {
                    flex: 1;
                    }

                    .provider-info p {
                    margin: 0.6rem 0;
                    line-height: 1.5;
                    color: #1e293b;
                    font-size: 1.05rem;
                    }

                    .provider-info p strong {
                    display: inline-block;
                    width: 140px;
                    font-weight: 600;
                    color: #3b82f6;
                    }



                    .no-results {
                    text-align: center;
                    padding: 4rem 2rem;
                    color: #64748b;
                    background: rgba(241, 245, 249, 0.5);
                    border-radius: 15px;
                    margin: 2rem 0;
                    }

                    .no-results-icon {
                    font-size: 5rem;
                    margin-bottom: 1.5rem;
                    color: #cbd5e1;
                    }

                    .no-results h3 {
                    font-size: 1.8rem;
                    margin-bottom: 1rem;
                    color: #475569;
                    }

                    .no-results p {
                    font-size: 1.1rem;
                    max-width: 500px;
                    margin: 0 auto;
                    }

                    @keyframes fadeIn {
                    from {
                        opacity: 0;
                        transform: translateY(20px) scale(0.98);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0) scale(1);
                    }
                    }

                    /* Responsive */
                    @media (max-width: 768px) {
                    .provider-card {
                        flex-direction: column;
                        text-align: center;
                        padding: 2rem 1.5rem;
                    }
                    
                    .provider-info p strong {
                        width: auto;
                        display: block;
                        margin-bottom: 0.2rem;
                    }
                    
                    .book-btn {
                        margin-top: 1.5rem;
                        margin-left: 0;
                        width: 100%;
                    }
                    
                    .results-container {
                        padding: 1.5rem;
                    }
                    
                    .provider-photo img {
                        width: 100px;
                        height: 100px;
                    }
                    }

    </style>
</head>
<body>
    <header>
        <nav class="container">
            <div class="logo">CliqRDV</div>
            <ul class="nav-links">
                <li><a href="index.php" class="active">Accueil</a></li>
                
            </ul>
            <div class="auth-buttons">
                <a href="connexion.php" class="btn-secondary">Connexion</a>
                <a href="inscription.php" class="btn-primary">Inscription</a>
            </div>
        </nav>
    </header>

    <main>
        <section class="hero">
            <div class="container">
                <div class="hero-content">
                    <h1>Votre santé, notre priorité</h1>
                    <p>Prenez rendez-vous avec des médecins qualifiés en quelques clics. Simple, rapide et 100% en ligne.</p>
                </div>
                
                <div class="search-section">
                    <div class="search-container">
                        <form class="search-container" method="get" action="">
                        <input type="text" class="search-input" placeholder="Rechercher un médecin ou une spécialité" id="searchInput" name="specialite">
                        <input type="text" class="search-input" name="location" id="location" placeholder= "localisation">
                        <button class="search-btn"   name="search" type = "submit" >🔍 Rechercher</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <section id="resultsSection" class="results-section<?= $showResults ? ' active' : '' ?>">
          <div class="results-header">
            <h2>Résultats de recherche</h2>
            <p class="results-count"><?= count($docs) ?> prestataire(s) trouvé(s)</p>
            <button id="closeResultsBtn" class="close-results-btn" title="Fermer">&times;</button>
          </div>
          <div class="results-container">
            <?php if (empty($docs)): ?>
              <div class="no-results">
                <div class="no-results-icon">🔍</div>
                <h3>Aucun résultat trouvé</h3>
                <p>Essayez de modifier vos critères de recherche</p>
              </div>
            <?php else: ?>
            
              <?php foreach($docs as $d): ?>
            <div class="provider-card">
                <!-- Photo (VARCHAR path) -->
                <?php if (!empty($d['photo_profil'])): ?>
                <div class="provider-photo">
                    <img 
                    src="<?= htmlspecialchars($d['photo_profil'], ENT_QUOTES) ?>"
                    alt="<?= htmlspecialchars($d['nom_prenom'], ENT_QUOTES) ?>"
                    >
                </div>
                <?php endif; ?>

                <div class="provider-info">
                <p><strong>Nom :</strong> <?= htmlspecialchars($d['nom_prenom'], ENT_QUOTES) ?></p>
                <p><strong>Spécialité :</strong> <?= htmlspecialchars($d['specialite'], ENT_QUOTES) ?></p>
                <p><strong>Localisation :</strong> <?= htmlspecialchars($d['localisation'], ENT_QUOTES) ?></p>
                <p><strong>Téléphone :</strong> <?= htmlspecialchars($d['tele'], ENT_QUOTES) ?></p>
                <?php if (!empty($d['tarif'])): ?>
                    <p><strong>Tarif :</strong> <?= htmlspecialchars($d['tarif'], ENT_QUOTES) ?> DH</p>
                <?php endif; ?>
                <?php if (!empty($d['email'])): ?>
                    <p><strong>Email :</strong> <?= htmlspecialchars($d['email'], ENT_QUOTES) ?></p>
                <?php endif; ?>
                <?php if (!empty($d['parcours'])): ?>
                    <p><strong>Parcours :</strong> <?= htmlspecialchars($d['parcours'], ENT_QUOTES) ?></p>
                <?php endif; ?>
                </div>

                <button
                class="book-btn pulse"
                onclick="window.location.href='connexion.php?y=1&id=<?=$d['id']?>';"
                >
                📅 Prendre rendez-vous
                </button>
            </div>
            <?php endforeach; ?>
                </div>
            <?php endif; ?>
          </div>
        </section>

      </div>
    </section>
        <section class="features">
            <div class="container">
                <h2>Pourquoi choisir CliqRDV ?</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <span class="feature-icon">⚡</span>
                        <h3>Rapide et efficace</h3>
                        <p>Prenez rendez-vous en moins de 2 minutes avec notre interface intuitive et moderne.</p>
                    </div>
                    <div class="feature-card">
                        <span class="feature-icon">🏥</span>
                        <h3>Médecins qualifiés</h3>
                        <p>Accédez à un réseau de professionnels de santé certifiés et expérimentés.</p>
                    </div>
                    <div class="feature-card">
                        <span class="feature-icon">🔒</span>
                        <h3>100% sécurisé</h3>
                        <p>Vos données personnelles et médicales sont protégées par un chiffrement de niveau bancaire.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>

          document.getElementById('closeResultsBtn')
      .addEventListener('click', () => {
        document.getElementById('resultsSection')
          .classList.remove('active');
      });

        // Animation au scroll
        function animateOnScroll() {
            const elements = document.querySelectorAll('.feature-card');
            elements.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                const elementVisible = 150;
                
                if (elementTop < window.innerHeight - elementVisible) {
                    element.style.animation = 'slideInUp 0.6s ease-out forwards';
                }
            });
        }

        window.addEventListener('scroll', animateOnScroll);

        // Fonctions interactives
        function performSearch() {
            const searchInput = document.getElementById('searchInput');
            const searchBtn = document.querySelector('.search-btn');
            const query = searchInput.value.trim();
            
            if (query) {
                // Animation de recherche
                searchBtn.innerHTML = '<span class="loading"></span> Recherche...';
                searchBtn.disabled = true;
                
                setTimeout(() => {
                    alert(`Recherche pour: "${query}"\n\nCette fonctionnalité sera disponible prochainement !`);
                    searchBtn.innerHTML = '🔍 Rechercher';
                    searchBtn.disabled = false;
                }, 2000);
            } else {
                searchInput.focus();
                searchInput.style.borderColor = '#ef4444';
                searchInput.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.1)';
                setTimeout(() => {
                    searchInput.style.borderColor = '#e0f2fe';
                    searchInput.style.boxShadow = 'none';
                }, 2000);
            }
        }

       
        // Recherche avec Enter
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });

        // Effet de parallax léger sur le hero
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const hero = document.querySelector('.hero');
            if (hero) {
                hero.style.transform = `translateY(${scrolled * 0.3}px)`;
            }
        });

        // Animation d'apparition progressive des cartes
        setTimeout(() => {
            document.querySelectorAll('.feature-card').forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease-out';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 200);
            });
        }, 500);

        // Amélioration de l'expérience utilisateur
        document.addEventListener('DOMContentLoaded', function() {
            // Animation d'entrée pour l'en-tête
            const header = document.querySelector('header');
            header.style.transform = 'translateY(-100%)';
            setTimeout(() => {
                header.style.transition = 'transform 0.5s ease-out';
                header.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>
</html>