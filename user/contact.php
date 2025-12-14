<?php
require_once '../config/database.php';
require_once '../includes/settings_helper.php';
require_once '../includes/security.php';
require_once '../includes/gmail_smtp.php';

$conn = getDBConnection();

// Get contact settings
$contact_email = getSetting('contact_email');
$contact_phone = getSetting('contact_phone');
$contact_address = getSetting('contact_address');
$site_title = getSetting('site_title');
$site_description = getSetting('site_description');
$footer_text = getSetting('footer_text');
$instagram_url = getSetting('instagram_url');
$facebook_url = getSetting('facebook_url');
$twitter_url = getSetting('twitter_url');
$linkedin_url = getSetting('linkedin_url');

$success_message = $error_message = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = validateInput($_POST["name"] ?? '', 'string', true);
    $email = validateInput($_POST["email"] ?? '', 'email', true);
    $subject = validateInput($_POST["subject"] ?? '', 'string', true);
    $message = validateInput($_POST["message"] ?? '', 'string', true);
    
    if (!$name || !$email || !$subject || !$message) {
        $error_message = "Please fill all required fields with valid data.";
    } else {
        try {
            // Create contact_messages table if not exists
            $create_table = "CREATE TABLE IF NOT EXISTS contact_messages (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL,
                subject VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $conn->query($create_table);
            
            // Insert message into database
            $sql = "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Database prepare failed");
            }
            
            $stmt->bind_param("ssss", $name, $email, $subject, $message);
            
            if ($stmt->execute()) {
                // Send email notification to admin
                $emailSent = sendContactNotification($name, $email, $subject, $message);
                
                $success_message = "Thank you for your message! We will get back to you soon.";
                if (!$emailSent) {
                    error_log("Failed to send email notification for contact form");
                }
                
                $name = $email = $subject = $message = "";
            } else {
                throw new Exception("Failed to save message");
            }
            
            $stmt->close();
        } catch (Exception $e) {
            error_log("Contact form error: " . $e->getMessage());
            $error_message = "Sorry, there was an error sending your message. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - IT Club</title>
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
            <a class="navbar-brand d-flex align-items-center" href="#">
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
                    <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="events.php">Events</a></li>
                    <li class="nav-item"><a class="nav-link" href="winners.php">Winners</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button">
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
        <div class="container" style="color: #fff;">
            <h1>Contact Us</h1>
            <p class="lead">Get in touch with the IT Club team</p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-6" data-aos="fade-right">
                    <h2 class="section-title" style="color: var(--primary-color);">Get In Touch</h2>
                    
                    <?php if(!empty($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $success_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(!empty($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="contact-form-wrapper p-4 bg-white rounded shadow">
                        <div class="mb-3">
                            <label for="name" class="form-label text-dark">Your Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label text-dark">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label text-dark">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" value="<?php echo htmlspecialchars($subject ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label text-dark">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
                
                <div class="col-lg-6" data-aos="fade-left" data-aos-delay="200">
                    <div class="ps-lg-5">
                        <h2 class="section-title" style="color: var(--primary-color);">Contact Information</h2>
                        
                        <div class="contact-info mb-4">
                            <div class="d-flex mb-3">
                                <div class="contact-icon me-3">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <h5 class="text-dark">Our Location</h5>
                                    <p class="text-dark"><?php echo htmlspecialchars($contact_address); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="contact-info mb-4">
                            <div class="d-flex mb-3">
                                <div class="contact-icon me-3">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <h5 class="text-dark">Email Us</h5>
                                    <p class="text-dark"><?php echo htmlspecialchars($contact_email); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="contact-info mb-4">
                            <div class="d-flex mb-3">
                                <div class="contact-icon me-3">
                                    <i class="fas fa-phone-alt"></i>
                                </div>
                                <div>
                                    <h5 class="text-dark">Call Us</h5>
                                    <p class="text-dark"><?php echo htmlspecialchars($contact_phone); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="map-container rounded shadow">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3801.775008237214!2d75.90338467479637!3d17.660812083273715!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bc5da715821adc1%3A0x9eb7e6d96ad484ef!2sSangameshwar%20College!5e0!3m2!1sen!2sin!4v1752991984856!5m2!1sen!2sin" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 600,
            easing: 'ease-out',
            once: true,
            offset: 50,
            disable: false
        });
        
        // Change navbar background on scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
        
        // Fix mobile dropdown
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownToggle = document.querySelector('.dropdown-toggle');
            const dropdownMenu = document.querySelector('.dropdown-menu');
            
            if (dropdownToggle && dropdownMenu) {
                dropdownToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropdownMenu.classList.toggle('show');
                });
                
                document.addEventListener('click', function(e) {
                    if (!dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
                        dropdownMenu.classList.remove('show');
                    }
                });
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>