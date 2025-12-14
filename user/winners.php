<?php
require_once 'db_config.php';
require_once '../includes/settings_helper.php';

$winners = json_decode(file_get_contents('../data/winners.json'), true) ?: [];

$site_title = getSetting('site_title');
$college_name = getSetting('college_name');
$footer_text = getSetting('footer_text');
$about_text = getSetting('about_text');
$instagram_url = getSetting('instagram_url');
$facebook_url = getSetting('facebook_url');
$twitter_url = getSetting('twitter_url');
$linkedin_url = getSetting('linkedin_url');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Winners - <?= htmlspecialchars($site_title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .winner-card { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px; margin: 20px 0; padding: 25px;
            color: white; position: relative; overflow: hidden;
            animation: slideIn 0.6s ease-out;
        }
        .winner-card::before {
            content: ''; position: absolute; top: -50%; right: -50%;
            width: 100px; height: 100px; background: rgba(255,255,255,0.1);
            border-radius: 50%; animation: float 3s ease-in-out infinite;
        }
        .position-1st { background: linear-gradient(135deg, #FFD700, #FFA500); }
        .position-2nd { background: linear-gradient(135deg, #C0C0C0, #808080); }
        .position-3rd { background: linear-gradient(135deg, #CD7F32, #8B4513); }
        .winner-name { font-size: 1.8em; font-weight: bold; margin-bottom: 10px; }
        .event-title { font-size: 1.2em; opacity: 0.9; margin-bottom: 15px; }
        .position-badge {
            position: absolute; top: 15px; right: 15px;
            background: rgba(255,255,255,0.2); padding: 5px 15px;
            border-radius: 20px; font-weight: bold;
        }
        .trophy { font-size: 3em; float: left; margin-right: 20px; }
        @keyframes slideIn { from { opacity: 0; transform: translateY(30px); } }
        @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-10px); } }
        .no-winners { text-align: center; padding: 50px; color: #fff; }
        .filter-tabs { display: flex; justify-content: center; margin: 40px 0; flex-wrap: wrap; }
        .winners-section { padding: 80px 0; background: linear-gradient(to right, #2a75bb, #0c4da2); }
    </style>
</head>
<body>
    <ul class="bg-animation"></ul>
    
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
               <img src="it-club-logo.png" alt="IT Club Logo" height="50">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <div class="hamburger-lines">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="events.php">Events</a></li>
                    <li class="nav-item"><a class="nav-link active" href="winners.php">Winners</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            Login
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="login.php?role=faculty">Faculty Login</a></li>
                            <li><a class="dropdown-item" href="login.php?role=member">Member Login</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header text-center text-white">
        <div class="container">
            <h1 data-aos="fade-down">🏆 Event Winners</h1>
            <p data-aos="fade-up" data-aos-delay="200">Celebrating our champions and achievers</p>
        </div>
    </section>

    <!-- Winners Section -->
    <section class="winners-section">
        <div class="container">
            <div class="filter-tabs">
                <button class="btn filter-btn active" onclick="filterWinners('all')">All Winners</button>
                <button class="btn filter-btn" onclick="filterWinners('1st')">🥇 1st Place</button>
                <button class="btn filter-btn" onclick="filterWinners('2nd')">🥈 2nd Place</button>
                <button class="btn filter-btn" onclick="filterWinners('3rd')">🥉 3rd Place</button>
            </div>

            <div id="winners-list">
                <?php if (empty($winners)): ?>
                    <div class="no-winners" data-aos="fade-up">
                        <h3>🎉 Winners will be announced soon!</h3>
                        <p>Stay tuned for exciting results from our events.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($winners as $index => $winner): ?>
                        <div class="winner-card position-<?= $winner['position'] ?>" data-position="<?= $winner['position'] ?>" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                            <div class="position-badge"><?= ucfirst($winner['position']) ?> Place</div>
                            <div class="trophy">
                                <?= $winner['position'] === '1st' ? '🥇' : ($winner['position'] === '2nd' ? '🥈' : '🥉') ?>
                            </div>
                            <div>
                                <div class="winner-name"><?= htmlspecialchars($winner['name']) ?></div>
                                <div class="event-title">📅 <?= htmlspecialchars($winner['event']) ?></div>
                                <?php if (!empty($winner['prize'])): ?>
                                    <div style="margin-top: 10px;">💰 Prize: ₹<?= $winner['prize'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

<?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            AOS.init({ duration: 600, easing: 'ease-out', once: true });
            
            // Navbar scroll behavior
            window.addEventListener('scroll', function() {
                const navbar = document.querySelector('.navbar');
                if (window.pageYOffset > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });
            
            // Back to top button
            const backToTopButton = document.getElementById('backToTop');
            window.addEventListener('scroll', () => {
                if (window.pageYOffset > 300) {
                    backToTopButton.classList.add('show');
                } else {
                    backToTopButton.classList.remove('show');
                }
            });
            backToTopButton.addEventListener('click', (e) => {
                e.preventDefault();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
            
            // Animated background
            const bgAnimation = document.querySelector('.bg-animation');
            for (let i = 0; i < 10; i++) {
                const li = document.createElement('li');
                const size = Math.random() * 60 + 20;
                const position = Math.random() * 100;
                const delay = Math.random() * 2;
                const duration = Math.random() * 10 + 15;
                li.style.cssText = `width:${size}px;height:${size}px;left:${position}%;animation-delay:${delay}s;animation-duration:${duration}s`;
                bgAnimation.appendChild(li);
            }
        });
        
        function filterWinners(position) {
            const cards = document.querySelectorAll('.winner-card');
            const tabs = document.querySelectorAll('.filter-btn');
            
            tabs.forEach(tab => tab.classList.remove('active'));
            event.target.classList.add('active');
            
            cards.forEach(card => {
                if (position === 'all' || card.dataset.position === position) {
                    card.style.display = 'block';
                    card.style.animation = 'slideIn 0.6s ease-out';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>