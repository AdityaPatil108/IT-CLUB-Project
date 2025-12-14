<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: ../user/login.php");
    exit;
}

require_once '../user/db_config.php';

// Check if ID parameter exists
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("location: manage_faculty.php");
    exit;
}

$id = $_GET['id'];
$success_message = $error_message = "";

// Get faculty details
$sql = "SELECT username FROM users WHERE id = ? AND role = 'faculty'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

// If faculty doesn't exist, redirect to manage faculty page
if ($result->num_rows == 0) {
    header("location: manage_faculty.php");
    exit;
}

$faculty = $result->fetch_assoc();
$username = $faculty['username'];

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate password
    if (empty(trim($_POST["password"]))) {
        $error_message = "Please enter a password.";     
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $error_message = "Password must have at least 6 characters.";
    } elseif (empty(trim($_POST["confirm_password"]))) {
        $error_message = "Please confirm password.";     
    } else {
        $password = trim($_POST["password"]);
        $confirm_password = trim($_POST["confirm_password"]);
        if ($password != $confirm_password) {
            $error_message = "Password did not match.";
        }
    }
    
    // Check input errors before updating in database
    if (empty($error_message)) {
        
        // Prepare an update statement
        $sql = "UPDATE users SET password = ? WHERE id = ?";
         
        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("si", $param_password, $param_id);
            
            // Set parameters
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            $param_id = $id;
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                $success_message = "Password has been reset successfully!";
            } else {
                $error_message = "Oops! Something went wrong. Please try again later.";
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
    <title>Reset Faculty Password - IT Club</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../user/style.css">
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <!-- Mobile Sidebar Toggle Button -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Include Sidebar -->
            <?php include 'sidebar.php'; ?>

            <!-- Main Content -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="admin-header d-flex justify-content-between align-items-center">
                    <h1 class="h2">Reset Password for <?php echo htmlspecialchars($username); ?></h1>
                    <div>
                        <a href="edit_faculty.php?id=<?php echo $id; ?>" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to Edit Faculty
                        </a>
                    </div>
                </div>

                <div class="admin-content">
                    <?php if(!empty($success_message)): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    
                    <?php if(!empty($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    
                    <div class="card">
                        <div class="card-body">
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $id); ?>" method="post">
                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <small class="text-muted">Password must be at least 6 characters long.</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">Reset Password</button>
                                    <a href="edit_faculty.php?id=<?php echo $id; ?>" class="btn btn-secondary">Cancel</a>
                                </div>
                            </form>
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