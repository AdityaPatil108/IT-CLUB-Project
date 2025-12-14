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
    header("location: manage_members.php");
    exit;
}

$id = $_GET['id'];
$username = $email = "";
$username_err = $email_err = "";
$success_message = "";

// Get member details
$sql = "SELECT * FROM users WHERE id = ? AND role = 'member'";
if (!($stmt = $conn->prepare($sql))) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $id);
if (!$stmt->execute()) {
    die("Error executing query: " . $stmt->error);
}
$result = $stmt->get_result();

// If member doesn't exist, redirect to manage members page
if ($result->num_rows == 0) {
    header("location: manage_members.php");
    exit;
}

$member = $result->fetch_assoc();
$username = $member['username'];
$email = $member['email'];

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } else {
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE username = ? AND id != ?";
        
        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("si", $param_username, $id);
            
            // Set parameters
            $param_username = trim($_POST["username"]);
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Store result
                $stmt->store_result();
                
                if ($stmt->num_rows == 1) {
                    $username_err = "This username is already taken.";
                } else {
                    $username = trim($_POST["username"]);
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            
            // Close statement
            $stmt->close();
        }
    }
    
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email.";
    } else {
        $email = trim($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email_err = "Please enter a valid email address.";
        }
    }
    
    // Check input errors before updating in database
    if (empty($username_err) && empty($email_err)) {
        
        // Prepare an update statement
        $sql = "UPDATE users SET username = ?, email = ? WHERE id = ?";
         
        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("ssi", $param_username, $param_email, $param_id);
            
            // Set parameters
            $param_username = $username;
            $param_email = $email;
            $param_id = $id;
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                $success_message = "Member updated successfully!";
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
    <title>Edit Member - IT Club</title>
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
                    <h1 class="h2">Edit Member</h1>
                    <div>
                        <a href="manage_members.php" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to Members
                        </a>
                    </div>
                </div>

                <div class="admin-content">
                    <?php if(!empty($success_message)): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    
                    <div class="card">
                        <div class="card-body">
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $id); ?>" method="post">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" id="username" name="username" value="<?php echo $username; ?>">
                                    <span class="invalid-feedback"><?php echo $username_err; ?></span>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo $email; ?>">
                                    <span class="invalid-feedback"><?php echo $email_err; ?></span>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <div class="d-grid">
                                        <a href="reset_password.php?id=<?php echo $id; ?>" class="btn btn-outline-secondary">Reset Password</a>
                                    </div>
                                    <small class="text-muted">Click the button above to reset this member's password.</small>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">Update Member</button>
                                    <a href="manage_members.php" class="btn btn-secondary">Cancel</a>
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