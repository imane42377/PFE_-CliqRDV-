

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

// On n'affiche les résultats que si on a cliqué
$showResults = $searchClicked;
?>
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
</style> 
 
 <div class="search-section">
                    <div class="search-container">
                        <form class="search-container" method="get" action="">
                        <input type="text" class="search-input" placeholder="Rechercher un médecin ou une spécialité" id="searchInput" name="specialite">
                        <input type="text" class="search-input" name="location" id="location" placeholder= "localisation">
                        <button class="search-btn"   name="search" type = "submit" >🔍 Rechercher</button>
                        </form>
                    </div>
</div>
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
                            <?php if (!empty($d['photo_profil'])): ?>
                                <div class="provider-photo">
                                    <img src="<?= htmlspecialchars($d['photo_profil'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($d['nom_prenom'], ENT_QUOTES) ?>">
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

                            <button class="book-btn" onclick="window.location.href='NewRDV.php?id=<?=$d['id']?>';">Prendre rendez-vous</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>


<script>
      document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
  
        document.getElementById('closeResultsBtn').addEventListener('click', () => {
            document.getElementById('resultsSection').classList.remove('active');
        });

        function bookAppointment() {
            const btn = document.querySelector('.appointment-btn');
            const originalText = btn.innerHTML;
            
            btn.innerHTML = '<span class="loading"></span> Redirection...';
            btn.disabled = true;
            
            setTimeout(() => {
                alert('Redirection vers la prise de rendez-vous...\n\nCette fonctionnalité sera disponible prochainement !');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 1500);
        }

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
</script>



