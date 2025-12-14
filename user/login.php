<?php
session_start();
require_once 'db_config.php';
require_once '../includes/settings_helper.php';
require_once '../includes/security.php';

// Get settings
$site_description = getSetting('site_description', 'Fostering tech innovation and collaboration among students.');
$footer_text = getSetting('footer_text', '© 2025 IT Club, Computer Science Dept, Sangameshwar College. All rights reserved.');
$instagram_url = getSetting('instagram_url', '#');

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Input validation using security helper
    $username = validateInput($_POST['username'] ?? '', 'string', true);
    $password = $_POST['password'] ?? '';
    $role = validateInput($_POST['role'] ?? '', 'role', true);
    
    if (!$username || !$password || !$role) {
        $error = "Invalid input data!";
    } else {
        // Check rate limiting
        $client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (!checkRateLimit($client_ip)) {
            $error = "Too many login attempts. Please try again later.";
        } else {
            try {
                $sql = "SELECT id, username, password, email FROM users WHERE username = ? AND role = ?";
                $stmt = $conn->prepare($sql);
                
                if (!$stmt) {
                    throw new Exception("Database prepare failed: " . $conn->error);
                }
                
                $stmt->bind_param("ss", $username, $role);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows == 1) {
                    $row = $result->fetch_assoc();
                    
                    // Only use password_verify for hashed passwords
                    if (password_verify($password, $row['password'])) {
                        $_SESSION['loggedin'] = true;
                        $_SESSION['id'] = $row['id'];
                        $_SESSION['username'] = htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8');
                        $_SESSION['email'] = htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8');
                        $_SESSION['role'] = $role;
                        
                        header("location: ../admin/dashboard.php");
                        exit;
                    } else {
                        recordFailedAttempt($client_ip);
                        $error = "Invalid credentials!";
                    }
                } else {
                    recordFailedAttempt($client_ip);
                    $error = "Invalid credentials!";
                }
                
                $stmt->close();
            } catch (Exception $e) {
                error_log("Login error: " . $e->getMessage());
                recordFailedAttempt($client_ip);
                $error = "Login failed. Please try again.";
            }
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - IT Club</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .login-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .login-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            padding: 1.5rem 1rem;
        }
        
        .input-group-text {
            background-color: var(--primary-color);
            color: white;
            border: none;
            width: 45px;
            justify-content: center;
        }
        
        .form-control {
            border-radius: 0 5px 5px 0;
            height: 50px;
            font-size: 1.1rem;
        }
        
        .form-control:focus {
            box-shadow: none;
            border-color: var(--primary-color);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            height: 50px;
            font-size: 1.1rem;
        }
        
        .card-footer {
            background-color: #f8f9fa;
            border-top: 1px solid #eee;
        }
        
        .card-footer a {
            color: var(--primary-color);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .card-footer a:hover {
            color: var(--accent-color);
            text-decoration: underline;
        }
        
        /* Additional responsive styles */
        @media (max-width: 768px) {
            .login-section {
                padding: 3rem 1rem;
            }
            
            .card-body {
                padding: 1.5rem !important;
            }
            
            .page-header h1 {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 576px) {
            .login-card {
                border-radius: 10px;
            }
            
            .card-body {
                padding: 1.25rem !important;
            }
            
            .form-control {
                height: 45px;
                font-size: 1rem;
            }
            
            .btn-primary {
                height: 45px;
                font-size: 1rem;
            }
            
            .card-header i {
                font-size: 2.5rem !important;
            }
            
            .card-header h3 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <ul class="bg-animation">
        <li></li><li></li><li></li><li></li><li></li>
    </ul>
    
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="../index.php">
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
                    <li class="nav-item"><a class="nav-link" href="./index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="./index.php#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="./index.php#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="./events.php">Events</a></li>
                    <li class="nav-item"><a class="nav-link" href="./winners.php">Winners</a></li>
                    <li class="nav-item"><a class="nav-link" href="./contact.php">Contact</a></li>
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

    <!-- Login Section -->
    <section class="page-header text-center">
        <div class="container" style="color: #fff;">
            <h1>
                <?php 
                $role = isset($_GET['role']) ? htmlspecialchars($_GET['role'], ENT_QUOTES, 'UTF-8') : 'faculty';
                echo ucfirst($role) . " Login";
                ?>
            </h1>
        </div>
    </section>

    <section class="login-section py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8 col-sm-12">
                    <div class="card shadow login-card">
                        <div class="card-header text-white text-center py-4">
                            <i class="fas <?php echo isset($_GET['role']) && htmlspecialchars($_GET['role'], ENT_QUOTES, 'UTF-8') == 'member' ? 'fa-user-graduate' : 'fa-chalkboard-teacher'; ?> fa-3x mb-3"></i>
                            <h3><?php echo isset($_GET['role']) ? ucfirst(htmlspecialchars($_GET['role'], ENT_QUOTES, 'UTF-8')) : 'Faculty'; ?> Login</h3>
                        </div>
                        <div class="card-body p-5">
                            <?php if(!empty($error)): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>
                            
                            <?php if(isset($debug)): ?>
                                <?php echo $debug; ?>
                            <?php endif; ?>
                            
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?role=<?php echo isset($_GET['role']) ? htmlspecialchars($_GET['role'], ENT_QUOTES, 'UTF-8') : 'faculty'; ?>">
                                <div class="mb-4 input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                                </div>
                                <div class="mb-4 input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                                </div>
                                <input type="hidden" name="role" value="<?php echo isset($_GET['role']) ? htmlspecialchars($_GET['role'], ENT_QUOTES, 'UTF-8') : 'faculty'; ?>">
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-sign-in-alt me-2"></i> Login
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="card-footer text-center py-3">
                            <p class="mb-0">Login as 
                                <?php if(isset($_GET['role']) && htmlspecialchars($_GET['role'], ENT_QUOTES, 'UTF-8') == 'member'): ?>
                                    <a href="login.php?role=faculty">Faculty</a>
                                <?php else: ?>
                                    <a href="login.php?role=member">Member</a>
                                <?php endif; ?>
                            </p>
                            <p class="mt-2 mb-0"><a href="forgot_password.php">Forgot Password?</a></p>
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
                        <li><a href="../index.php">Home</a></li>
                        <li><a href="../events.php">Events</a></li>
                        <li><a href="../contact.php">Contact</a></li>
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
</body>
</html>