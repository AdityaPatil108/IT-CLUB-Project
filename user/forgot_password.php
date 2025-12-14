<?php
session_start();
require_once '../config/database.php';
require_once '../includes/settings_helper.php';
require_once '../includes/security.php';

$conn = getDBConnection();

// Get settings
$site_description = getSetting('site_description', 'Fostering tech innovation and collaboration among students.');
$footer_text = getSetting('footer_text', '© 2025 IT Club, Computer Science Dept, Sangameshwar College. All rights reserved.');
$instagram_url = getSetting('instagram_url', '#');

$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    
    if (!validateEmail($email)) {
        $error = "Please enter a valid email address.";
    } else {
    
    // Check if username exists with matching email
    $sql = "SELECT * FROM users WHERE username = ? AND email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    }
    if ($result->num_rows == 1) {
        // Generate a secure random password
        $new_password = bin2hex(random_bytes(4));
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update the password
        $update_sql = "UPDATE users SET password = ? WHERE username = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ss", $hashed_password, $username);
        
        if ($update_stmt->execute()) {
            // Display the new password directly
            $message = "Your password has been reset successfully!";
            $new_password_display = $new_password; // Store for display
        } else {
            $error = "Error resetting password. Please try again.";
        }
        
        $update_stmt->close();
    } else {
        $error = "No account found with that username and email combination.";
    }
    
    $stmt->close();
    }
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - IT Club</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    <li class="nav-item"><a class="nav-link" href="events.php">Events</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" role="button" data-bs-toggle="dropdown">
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
            <h1 style="color: #fff;">Forgot Password</h1>
        </div>
    </section>

    <!-- Forgot Password Section -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0"><i class="fas fa-key me-2"></i>Reset Your Password</h4>
                        </div>
                        <div class="card-body p-4">
                            <?php if(!empty($message)): ?>
                                <div class="alert alert-success">
                                    <?php echo $message; ?>
                                    <?php if(isset($new_password_display)): ?>
                                        <hr>
                                        <div class="text-center">
                                            <strong>Your New Password:</strong><br>
                                            <span class="badge bg-dark fs-5 mt-2" id="newPassword"><?php echo htmlspecialchars($new_password_display); ?></span>
                                            <br>
                                            <button type="button" class="btn btn-sm btn-outline-success mt-2" onclick="copyPassword()">
                                                <i class="fas fa-copy"></i> Copy Password
                                            </button>
                                        </div>
                                        <div class="mt-3">
                                            <small class="text-muted">Please save this password securely and change it after logging in.</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="login.php" class="btn btn-primary">Go to Login</a>
                                </div>
                            <?php elseif(!empty($error)): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>
                            
                            <?php if(empty($message)): ?>
                                <p class="text-muted mb-4">Enter your username to reset your password.</p>
                                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="username" name="username" required>
                                    </div>
                                    <div class="mb-4">
                                        <label for="email" class="form-label">Email (for verification)</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">Reset Password</button>
                                    </div>
                                </form>
                            <?php endif; ?>
                            
                            <div class="mt-3 text-center">
                                <a href="login.php" class="text-decoration-none">Back to Login</a>
                            </div>
                        </div>
                    </div>
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
    <script>
        function copyPassword() {
            const passwordText = document.getElementById('newPassword').textContent;
            navigator.clipboard.writeText(passwordText).then(function() {
                // Show success feedback
                const button = event.target.closest('button');
                const originalHTML = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check"></i> Copied!';
                button.classList.remove('btn-outline-success');
                button.classList.add('btn-success');
                
                setTimeout(function() {
                    button.innerHTML = originalHTML;
                    button.classList.remove('btn-success');
                    button.classList.add('btn-outline-success');
                }, 2000);
            });
        }
    </script>
</body>
</html>