<?php
session_start();
require_once 'db_config.php';
require_once '../includes/settings_helper.php';

// Get settings
$site_description = getSetting('site_description', 'Fostering tech innovation and collaboration among students.');
$footer_text = getSetting('footer_text', '© 2025 IT Club, Computer Science Dept, Sangameshwar College. All rights reserved.');
$instagram_url = getSetting('instagram_url', '#');

// Create registrations table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS event_registrations (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    event_id INT(11) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NULL,
    department VARCHAR(100),
    is_student TINYINT(1) DEFAULT 1,
    payment_screenshot VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
)";

if ($conn->query($sql) !== TRUE) {
    die("Error creating table: " . $conn->error);
}

// Add payment_screenshot column if it doesn't exist
$sql = "ALTER TABLE event_registrations ADD COLUMN IF NOT EXISTS payment_screenshot VARCHAR(255) DEFAULT NULL";
$conn->query($sql);

$name = $email = $phone = $department = "";
$name_err = $email_err = $event_id_err = $screenshot_err = "";
$success_message = "";

// Check if event_id is provided
if (!isset($_GET['event_id']) || empty($_GET['event_id'])) {
    $event_id_err = "No event selected.";
} else {
    $event_id = filter_var($_GET['event_id'], FILTER_VALIDATE_INT);
    if ($event_id === false) {
        $event_id_err = "Invalid event ID.";
    }
    
    // Check if event exists
    $check_event = "SELECT * FROM events WHERE id = ?";
    $stmt = $conn->prepare($check_event);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $event_id_err = "Invalid event selected.";
    } else {
        $event = $result->fetch_assoc();
    }
    $stmt->close();
}

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter your name.";
    } else {
        $name = trim($_POST["name"]);
    }
    
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email_err = "Please enter a valid email address.";
        }
    }
    
    // Get other form data
    $phone = trim($_POST["phone"]);
    $department = trim($_POST["department"]);
    $is_student = isset($_POST["is_student"]) ? 1 : 0;
    $event_id = $_POST["event_id"];
    
    // Validate payment screenshot for paid events
    $screenshot_path = null;
    if (isset($event['is_paid']) && $event['is_paid']) {
        if (!isset($_FILES["payment_screenshot"]) || $_FILES["payment_screenshot"]["error"] == 4) {
            $screenshot_err = "Payment screenshot is required for paid events.";
        } else {
            $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
            $file_name = $_FILES["payment_screenshot"]["name"];
            $file_size = $_FILES["payment_screenshot"]["size"];
            $file_tmp = $_FILES["payment_screenshot"]["tmp_name"];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            // Sanitize file extension to prevent path traversal
            $file_ext = preg_replace('/[^a-z0-9]/', '', $file_ext);
            
            if (!in_array($file_ext, $allowed_types)) {
                $screenshot_err = "Only JPG, JPEG, PNG & GIF files are allowed.";
            }
            
            if ($file_size > 5000000) {
                $screenshot_err = "File size must be less than 5MB.";
            }
            
            if (empty($screenshot_err)) {
                $upload_dir = "../uploads/screenshots/";
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $new_file_name = 'payment_' . bin2hex(random_bytes(8)) . '.' . $file_ext;
                $upload_path = $upload_dir . $new_file_name;
                
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    $screenshot_path = "uploads/screenshots/" . $new_file_name;
                } else {
                    $screenshot_err = "Failed to upload payment screenshot.";
                }
            }
        }
    }
    
    // Check input errors before inserting in database
    if (empty($name_err) && empty($email_err) && empty($event_id_err) && empty($screenshot_err)) {
        
        // Prepare an insert statement
        $sql = "INSERT INTO event_registrations (event_id, name, email, phone, department, is_student, payment_screenshot) VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("issssis", $event_id, $name, $email, $phone, $department, $is_student, $screenshot_path);
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                $success_message = "Registration successful! We look forward to seeing you at the event.";
                // Clear form fields
                $name = $email = $phone = $department = "";
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            
            // Close statement
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
    <title>Register for Event - IT Club</title>
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
            <h1 style="color: #fff;">Event Registration</h1>
            <?php if(isset($event)): ?>
                <p class="lead" style="color: #fff;">Register for: <?php echo htmlspecialchars($event['title']); ?></p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Registration Form Section -->
    <section class="py-5">
        <div class="container">
            <?php if(!empty($event_id_err)): ?>
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="alert alert-danger text-center">
                            <?php echo $event_id_err; ?>
                            <p class="mt-3"><a href="events.php" class="btn btn-primary">View All Events</a></p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-lg-6" data-aos="fade-right">
                        <?php if(!empty($success_message)): ?>
                            <div class="card shadow">
                                <div class="card-body text-center p-5">
                                    <div class="mb-4">
                                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                                    </div>
                                    <h3>Registration Successful!</h3>
                                    <p class="lead"><?php echo $success_message; ?></p>
                                    <div class="mt-4">
                                        <a href="events.php" class="btn btn-primary">Browse More Events</a>
                                        <a href="index.php" class="btn btn-outline-secondary ms-2">Back to Home</a>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="card shadow">
                                <div class="card-header bg-primary text-white">
                                    <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Registration Form</h4>
                                </div>
                                <div class="card-body p-4">
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?event_id=" . $event_id); ?>" method="post" enctype="multipart/form-data">
                                        <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
                                        
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Full Name</label>
                                            <input type="text" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" id="name" name="name" value="<?php echo $name; ?>" required>
                                            <span class="invalid-feedback"><?php echo $name_err; ?></span>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email Address</label>
                                            <input type="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo $email; ?>" required>
                                            <span class="invalid-feedback"><?php echo $email_err; ?></span>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Phone Number</small></label>
                                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $phone; ?>" placeholder="Enter your phone number">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="department" class="form-label">Department/Year</label>
                                            <input type="text" class="form-control" id="department" name="department" value="<?php echo $department; ?>">
                                        </div>
                                        
                                        <div class="mb-3 form-check">
                                            <input type="checkbox" class="form-check-input" id="is_student" name="is_student" checked>
                                            <label class="form-check-label" for="is_student">I am a student</label>
                                        </div>
                                        
                                        <?php if (isset($event['is_paid']) && $event['is_paid']): ?>
                                        <div class="mb-3">
                                            <label for="payment_screenshot" class="form-label">Payment Screenshot <span class="text-danger">*</span></label>
                                            <input type="file" class="form-control <?php echo (!empty($screenshot_err)) ? 'is-invalid' : ''; ?>" id="payment_screenshot" name="payment_screenshot" accept="image/*">
                                            <div class="form-text">Upload screenshot of payment (₹<?php echo number_format($event['price'], 2); ?>) - JPG, PNG, GIF only, max 5MB</div>
                                            <div class="mt-2" id="imagePreview" style="display: none;">
                                                <img id="previewImg" src="" alt="Preview" style="max-width: 200px; max-height: 200px; border: 1px solid #ddd; border-radius: 5px;">
                                            </div>
                                            <span class="invalid-feedback"><?php echo $screenshot_err; ?></span>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                                <span id="submitText">Register Now</span>
                                                <span id="submitSpinner" class="spinner-border spinner-border-sm ms-2" style="display: none;"></span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-lg-6" data-aos="fade-left" data-aos-delay="200">
                        <?php if(isset($event)): ?>
                            <div class="card shadow h-100">
                                <img src="../<?php echo htmlspecialchars($event['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($event['title']); ?>">
                                <div class="card-body">
                                    <h3 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                                    <p class="card-text"><i class="far fa-calendar-alt me-2"></i><?php echo date('F d, Y', strtotime($event['event_date'])); ?> at <?php echo date('g:i A', strtotime($event['event_date'])); ?></p>
                                    <p class="card-text"><i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($event['location']); ?></p>
                                    <p class="card-text"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h4>IT Club</h4>
                    <p>Fostering tech innovation and collaboration among students.</p>
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
                        <a href="https://www.instagram.com/sangameshwar_sanmcs_dept?igsh=MW55bWRvcXhxc3YxNw=="><i class="fab fa-instagram"></i></a>
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
        
        // Image preview functionality
        const paymentScreenshot = document.getElementById('payment_screenshot');
        if (paymentScreenshot) {
            paymentScreenshot.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('previewImg').src = e.target.result;
                        document.getElementById('imagePreview').style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                } else {
                    document.getElementById('imagePreview').style.display = 'none';
                }
            });
        }
        
        // Form submission with loading state
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function() {
                const submitBtn = document.getElementById('submitBtn');
                const submitText = document.getElementById('submitText');
                const submitSpinner = document.getElementById('submitSpinner');
                
                if (submitBtn && submitText && submitSpinner) {
                    submitBtn.disabled = true;
                    submitText.textContent = 'Registering...';
                    submitSpinner.style.display = 'inline-block';
                }
            });
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>