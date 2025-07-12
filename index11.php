<?php
session_start();

require_once 'config.php'; 

$conn = mysqli_connect($host, $user, $password, $dbname);
if (!$conn) {
    die("Erreur de connexion MySQL : " . mysqli_connect_error());
}

// 2) Récupération des filtres GET
$keyword       = trim($_GET['keyword']  ?? '');
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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CliqRDV - Vos rendez-vous médicaux en ligne</title>
  <style>
    /* Reset & Layout */
    * { margin:0; padding:0; box-sizing:border-box; }
    body {
      font-family:'Arial',sans-serif;
      background:linear-gradient(135deg,#64B8FD 0%,#42A8FC 100%);
      color:#1e3a8a;
      min-height:100vh;
    }
    .container { max-width:1400px; margin:0 auto; padding:0 20px; }

    /* Header & Nav */
    header {
      background:rgba(255,255,255,0.95);
      backdrop-filter:blur(10px);
      box-shadow:0 2px 20px rgba(30,58,138,0.15);
      position:fixed; top:0; width:100%; z-index:1000;
    }
    nav { display:flex; justify-content:space-between; align-items:center; padding:1rem 2rem; }
    .logo { display:flex; align-items:center; gap:10px; font-size:1.8rem; font-weight:bold; color:#2563eb; }
    .logo::before { content:"📅"; font-size:2rem; }
    .nav-links { display:flex; list-style:none; gap:2rem; align-items:center; }
    .nav-links a {
      text-decoration:none; color:#1e3a8a; font-weight:500;
      position:relative; transition:all .3s;
    }
    .nav-links a:hover { color:#3b82f6; transform:translateY(-2px); }
    .nav-links a::after {
      content:''; position:absolute; width:0; height:2px;
      bottom:-5px; left:50%; background:linear-gradient(90deg,#3b82f6,#1d4ed8);
      transform:translateX(-50%); transition:all .3s;
    }
    .nav-links a:hover::after { width:100%; }
    .auth-buttons { display:flex; gap:1rem; }
    .btn-secondary, .btn-primary {
      padding:.7rem 1.5rem; border-radius:25px; font-weight:600; transition:all .3s;
    }
    .btn-secondary {
      border:2px solid #3b82f6; background:transparent; color:#3b82f6;
    }
    .btn-secondary:hover {
      background:linear-gradient(45deg,#3b82f6,#1d4ed8); color:#fff;
      transform:translateY(-2px); box-shadow:0 5px 15px rgba(59,130,246,0.4);
    }
     .exit-button { position:fixed; top:15px; right:15px; width:40px; height:40px; background:rgba(255,255,255,0.9); border:none; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:18px; font-weight:bold; color:#666; box-shadow:0 2px 10px rgba(0,0,0,0.1); transition:all 0.3s ease; z-index:1000; backdrop-filter:blur(10px); }
     .exit-button:hover { background:#ff4757; color:white; transform:scale(1.1); box-shadow:0 4px 15px rgba(255,71,87,0.3); }
     .exit-button:active { transform:scale(0.95); }
    .btn-primary {
      background:linear-gradient(45deg,#1d4ed8,#1e40af); color:#fff; border:none;
      box-shadow:0 4px 15px rgba(29,78,216,0.3);
    }
    .btn-primary:hover {
      background:linear-gradient(45deg,#1e40af,#1e3a8a);
      transform:translateY(-2px); box-shadow:0 8px 25px rgba(29,78,216,0.4);
    }

    /* Hero */
    .hero { margin-top:80px; padding:4rem 0; text-align:center; }
    .hero-content h1 {
      font-size:3.5rem; color:#0f172a;
      text-shadow:0 2px 4px rgba(255,255,255,0.3);
      animation:slideInUp 1s ease-out;
    }
    .hero-content p {
      font-size:1.3rem; color:#1e3a8a;
      animation:slideInUp 1s ease-out .2s both;
    }
    @keyframes slideInUp { from{opacity:0;transform:translateY(30px);} to{opacity:1;transform:translateY(0);} }

    /* Search */
    .search-section {
      background:rgba(255,255,255,0.95); backdrop-filter:blur(10px);
      border-radius:20px; padding:2rem; margin:2rem auto;
      max-width:1000px; box-shadow:0 10px 40px rgba(30,58,138,0.15);
      border:1px solid rgba(59,130,246,0.2);
    }
    .search-container {
      display:flex; gap:1rem; flex-wrap:wrap; align-items:center;
    }
    .search-input {
      flex:1; min-width:300px; padding:1rem 1.5rem;
      border:2px solid #e0f2fe; border-radius:25px;
      transition:all .3s;
    }
    .search-input:focus {
      border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,0.1);
      outline:none;
    }
    .search-btn, .appointment-btn {
      padding:1rem 2rem; border:none; border-radius:25px;
      cursor:pointer; transition:all .3s;
    }
    .search-btn {
      background:linear-gradient(45deg,#3b82f6,#1d4ed8); color:#fff;
    }
    .search-btn:hover {
      transform:translateY(-2px);
      background:linear-gradient(45deg,#1d4ed8,#1e40af);
    }
    .appointment-btn {
      background:linear-gradient(45deg,#0ea5e9,#0284c7);
      color:#fff; box-shadow:0 4px 15px rgba(14,165,233,0.3);
    }
    .appointment-btn:hover {
      transform:translateY(-2px);
      background:linear-gradient(45deg,#0284c7,#0369a1);
      box-shadow:0 8px 25px rgba(14,165,233,0.4);
    }

    /* Results */
    .results-section {
      background:rgba(255,255,255,0.95); backdrop-filter:blur(10px);
      border-radius:20px; margin:2rem auto; max-width:1200px;
      box-shadow:0 10px 40px rgba(30,58,138,0.15);
      border:1px solid rgba(59,130,246,0.2);
      overflow:hidden; display:none; animation:fadeIn .6s ease-out;
    }
    .results-section.active { display:block; }
    .results-header {
      position:relative; background:linear-gradient(45deg,#3b82f6,#1d4ed8);
      color:#fff; padding:1.5rem 2rem; text-align:center;
    }
    .results-header h2 { font-size:1.8rem; margin-bottom:.5rem; }
    .results-count { opacity:.9; }
    .close-results-btn {
      position:absolute; top:1rem; right:1rem;
      background:transparent; border:none; color:#fff;
      font-size:1.5rem; cursor:pointer;
    }
    .results-container { padding:2rem; }

    /* Provider Card */
    .provider-card {
      display:flex; align-items:flex-start; gap:2rem;
      background:#fff; border-radius:15px; padding:2rem;
      margin-bottom:1.5rem; box-shadow:0 3px 15px rgba(30,58,138,0.1);
      border:1px solid rgba(59,130,246,0.1); text-align:left;
    }
    .provider-photo img {
      width:100px; height:100px; border-radius:50%; object-fit:cover;
      border:2px solid #e0f2fe;
    }

        /* Coloration de la section “provider-info” */
    .provider-info p {
    margin: .5rem 0;
    line-height: 1.4;
    color:rgb(21, 24, 199);            /* couleur des valeurs */
    }

    .provider-info p strong {
    display: inline-block;
    width: 120px;
    font-weight: 600;
    color:rgb(30, 122, 175);            /* couleur des labels */
    }
    .book-btn {
      align-self:center; margin-left:auto;
      padding:1rem 2rem; background:linear-gradient(45deg,#0ea5e9,#0284c7);
      color:#fff; border:none; border-radius:25px; cursor:pointer;
      transition:all .3s;
    }





    .book-btn:hover {
      transform:translateY(-2px);
      background:linear-gradient(45deg,#0284c7,#0369a1);
    }

    .no-results { text-align:center; padding:4rem 2rem; color:#64748b; }
    .no-results-icon { font-size:4rem; margin-bottom:1rem; }

    @keyframes fadeIn { from{opacity:0;} to{opacity:1;} }

    
  </style>
</head>
<body>
  <header>
    <nav class="container">
      <div class="logo">CliqRDV</div>
      <ul class="nav-links">
        <li><a href="index.php" class="active">Accueil</a></li>
        <li><a href="#">Contact</a></li>
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
          <p>Prenez rendez-vous avec des médecins qualifiés en quelques clics.</p>
        </div>

        <div class="search-section">
          <form class="search-container" method="get" action="">
            <input
              type="text"
              name="keyword"
              class="search-input"
              placeholder="Rechercher un médecin ou spécialité"
              value="<?= htmlspecialchars($keyword, ENT_QUOTES) ?>"
            >
            <input
              type="text"
              name="location"
              class="search-input"
              placeholder="Localisation"
              value="<?= htmlspecialchars($location, ENT_QUOTES) ?>"
            >
            <button type="submit" name="search" class="search-btn">Rechercher</button>

          </form>
        </div>

        <section
          id="resultsSection"
          class="results-section<?= $showResults ? ' active' : '' ?>"
        >
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
                class="book-btn"
                onclick="window.location.href='connexion.php';"
                >
                Prendre rendez-vous
                </button>
            </div>
            <?php endforeach; ?>
                </div>
            <?php endif; ?>
          </div>
        </section>

      </div>
    </section>
  </main>

  <script>
    document.getElementById('closeResultsBtn')
      .addEventListener('click', () => {
        document.getElementById('resultsSection')
          .classList.remove('active');
      });

    function bookAppointment() {
      alert('Redirection vers la prise de rendez-vous…');
    }
  </script>
  <style>
  /* === Pourquoi choisir CliqRDV ? === */
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
  padding: 0 2rem;
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
}

.feature-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 20px 50px rgba(30, 58, 138, 0.15);
}

.feature-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
  display: inline-block;
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
</style>
      </section> <!-- end of previous section -->

    <!-- === Pourquoi choisir CliqRDV ? === -->
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
    // ... existing JS ...
  </script>
</body>
</html>

</body>
</html>
