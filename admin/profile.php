<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: ../user/login.php");
    exit;
}

require_once '../user/db_config.php';

$username = $_SESSION['username'];
$role = $_SESSION['role'];
$current_password = $new_password = $confirm_password = "";
$current_password_err = $new_password_err = $confirm_password_err = "";
$success_message = $error_message = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate current password
    if (empty(trim($_POST["current_password"]))) {
        $current_password_err = "Please enter your current password.";
    } else {
        $current_password = trim($_POST["current_password"]);
    }
    
    // Validate new password
    if (empty(trim($_POST["new_password"]))) {
        $new_password_err = "Please enter a new password.";
    } elseif (strlen(trim($_POST["new_password"])) < 6) {
        $new_password_err = "Password must have at least 6 characters.";
    } else {
        $new_password = trim($_POST["new_password"]);
    }
    
    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm the password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($new_password_err) && ($new_password != $confirm_password)) {
            $confirm_password_err = "Passwords did not match.";
        }
    }
    
    // Check input errors before updating the database
    if (empty($current_password_err) && empty($new_password_err) && empty($confirm_password_err)) {
        // Prepare a select statement to get the user's current password
        $sql = "SELECT password FROM users WHERE username = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $username);
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Store result
                $stmt->store_result();
                
                // Check if username exists
                if ($stmt->num_rows == 1) {
                    // Bind result variables
                    $stmt->bind_result($hashed_password);
                    if ($stmt->fetch()) {
                        // Check if the current password is correct
                        if (password_verify($current_password, $hashed_password) || $current_password === $hashed_password) {
                            // Current password is correct, update the password
                            $sql = "UPDATE users SET password = ? WHERE username = ?";
                            
                            if ($update_stmt = $conn->prepare($sql)) {
                                // Bind variables to the prepared statement as parameters
                                $param_password = password_hash($new_password, PASSWORD_DEFAULT);
                                $update_stmt->bind_param("ss", $param_password, $username);
                                
                                // Attempt to execute the prepared statement
                                if ($update_stmt->execute()) {
                                    $success_message = "Password updated successfully!";
                                    // Clear form fields
                                    $current_password = $new_password = $confirm_password = "";
                                } else {
                                    $error_message = "Oops! Something went wrong. Please try again later.";
                                }
                                
                                // Close statement
                                $update_stmt->close();
                            }
                        } else {
                            // Current password is not correct
                            $current_password_err = "The current password you entered is not correct.";
                        }
                    }
                }
            } else {
                $error_message = "Oops! Something went wrong. Please try again later.";
            }
            
            // Close statement
            $stmt->close();
        }
    }
}

// Get user information
$sql = "SELECT username, role, created_at FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - IT Club</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../user/style.css">
    <link rel="stylesheet" href="admin_style.css">
    <style>
        .profile-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .profile-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 30px;
            text-align: center;
            color: white;
        }
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: white;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin: 0 auto 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        @media (max-width: 768px) {
            .profile-header {
                padding: 20px;
            }
            
            .profile-avatar {
                width: 80px;
                height: 80px;
                font-size: 30px;
                margin-bottom: 15px;
            }
            
            .profile-header h3 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Sidebar Toggle Button -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <div class="hamburger-lines">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </button>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Include Sidebar -->
            <?php include 'sidebar.php'; ?>

            <!-- Main Content -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="admin-header d-flex justify-content-between align-items-center">
                    <h1 class="h2">My Profile</h1>
                    <div>
                        <a href="dashboard.php" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                        </a>
                    </div>
                </div>

                <div class="admin-content">
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="profile-card">
                                <div class="profile-header">
                                    <div class="profile-avatar">
                                        <i class="fas <?php echo $role == 'faculty' ? 'fa-chalkboard-teacher' : 'fa-user-graduate'; ?>"></i>
                                    </div>
                                    <h3><?php echo htmlspecialchars($username); ?></h3>
                                    <p class="mb-0"><?php echo ucfirst($role); ?></p>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-user me-2"></i> Username</span>
                                            <span class="text-muted"><?php echo htmlspecialchars($user['username']); ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-user-tag me-2"></i> Role</span>
                                            <span class="text-muted"><?php echo ucfirst($user['role']); ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-calendar-alt me-2"></i> Joined</span>
                                            <span class="text-muted"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">Change Password</h5>
                                </div>
                                <div class="card-body">
                                    <?php if(!empty($success_message)): ?>
                                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                                    <?php endif; ?>
                                    
                                    <?php if(!empty($error_message)): ?>
                                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                                    <?php endif; ?>
                                    
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                        <div class="mb-3">
                                            <label for="current_password" class="form-label">Current Password</label>
                                            <input type="password" class="form-control <?php echo (!empty($current_password_err)) ? 'is-invalid' : ''; ?>" id="current_password" name="current_password">
                                            <span class="invalid-feedback"><?php echo $current_password_err; ?></span>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="new_password" class="form-label">New Password</label>
                                            <input type="password" class="form-control <?php echo (!empty($new_password_err)) ? 'is-invalid' : ''; ?>" id="new_password" name="new_password">
                                            <span class="invalid-feedback"><?php echo $new_password_err; ?></span>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                                            <input type="password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" id="confirm_password" name="confirm_password">
                                            <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">Update Password</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="admin_script.js"></script>
</body>
</html>
<?php $conn->close(); ?>