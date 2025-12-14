<?php
session_start();
require_once 'db_config.php';
require_once '../includes/settings_helper.php';

// Get settings
$site_description = getSetting('site_description', 'Fostering tech innovation and collaboration among students.');
$footer_text = getSetting('footer_text', '© 2025 IT Club, Computer Science Dept, Sangameshwar College. All rights reserved.');
$instagram_url = getSetting('instagram_url', '#');

// Create event_queries table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS event_queries (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    event_id INT(11) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    query TEXT NOT NULL,
    status ENUM('pending', 'answered') DEFAULT 'pending',
    response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
)";

if ($conn->query($sql) !== TRUE) {
    die("Error creating table: " . $conn->error);
}

$success = false;
$error = '';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate inputs
    if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['query']) || empty($_POST['event_id'])) {
        $error = "All fields are required.";
    } else {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $query = trim($_POST['query']);
        $event_id = (int)$_POST['event_id'];
        $event_title = isset($_POST['event_title']) ? $_POST['event_title'] : '';
        
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } else {
            // Insert query into database
            $sql = "INSERT INTO event_queries (event_id, name, email, query) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isss", $event_id, $name, $email, $query);
            
            if ($stmt->execute()) {
                $success = true;
            } else {
                $error = "Error submitting query. Please try again.";
            }
            
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Query Submission - IT Club</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Animated Background -->
    <ul class="bg-animation">
        <li></li><li></li><li></li><li></li><li></li>
    </ul>
    
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
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
                    <li class="nav-item"><a class="nav-link active" href="events.php">Events</a></li>
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
    <section class="page-header text-center">
        <div class="container">
            <h1 style="color: #fff;">Query Submission</h1>
        </div>
    </section>

    <!-- Query Result Section -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <?php if ($success): ?>
                        <div class="card shadow">
                            <div class="card-body text-center p-5">
                                <div class="mb-4">
                                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                                </div>
                                <h3>Query Submitted Successfully!</h3>
                                <p class="lead">Thank you for your query. We will get back to you as soon as possible.</p>
                                <div class="mt-4">
                                    <a href="events.php" class="btn btn-primary">Browse Events</a>
                                    <a href="index.php" class="btn btn-outline-secondary ms-2">Back to Home</a>
                                </div>
                            </div>
                        </div>
                    <?php elseif (!empty($error)): ?>
                        <div class="card shadow">
                            <div class="card-body text-center p-5">
                                <div class="mb-4">
                                    <i class="fas fa-exclamation-circle text-danger" style="font-size: 4rem;"></i>
                                </div>
                                <h3>Error</h3>
                                <p class="lead"><?php echo $error; ?></p>
                                <div class="mt-4">
                                    <button class="btn btn-primary" onclick="history.back()">Go Back</button>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card shadow">
                            <div class="card-body text-center p-5">
                                <div class="mb-4">
                                    <i class="fas fa-exclamation-circle text-warning" style="font-size: 4rem;"></i>
                                </div>
                                <h3>Invalid Request</h3>
                                <p class="lead">Please submit your query through the event details page.</p>
                                <div class="mt-4">
                                    <a href="events.php" class="btn btn-primary">Browse Events</a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h4>IT Club</h4>
                    <p><?php echo htmlspecialchars($site_description); ?></p>
                </div>
                <div class="col-md-4 mb-4">
                    <h4>Quick Links</h4>
                    <ul class="list-unstyled">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="events.php">Events</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h4>Connect With Us</h4>
                    <div class="social-icons">
                        <a href="<?php echo htmlspecialchars($instagram_url); ?>"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <hr class="mt-4 mb-4" style="background-color: rgba(255,255,255,0.1);">
            <p class="text-center mb-0"><?php echo htmlspecialchars($footer_text); ?></p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();
        
        // Change navbar background on scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>