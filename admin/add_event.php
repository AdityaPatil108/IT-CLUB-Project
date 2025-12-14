<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: ../user/login.php");
    exit;
}

require_once '../config/database.php';
require_once '../includes/security.php';

$conn = getDBConnection();

// Update events table to include payment fields and fix image column
$sql = "ALTER TABLE events ADD COLUMN IF NOT EXISTS is_paid TINYINT(1) DEFAULT 0, ADD COLUMN IF NOT EXISTS price DECIMAL(10,2) DEFAULT 0.00, ADD COLUMN IF NOT EXISTS payment_qr VARCHAR(255) DEFAULT NULL";
$conn->query($sql);
$sql = "ALTER TABLE events MODIFY COLUMN image VARCHAR(255) NULL DEFAULT NULL";
$conn->query($sql);

$title = $event_date = $location = $description = $price = "";
$is_paid = 0;
$title_err = $event_date_err = $location_err = $description_err = $image_err = $price_err = $qr_err = "";
$success_message = "";
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success_message = "Event added successfully!";
}

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate title
    if (empty(trim($_POST["title"]))) {
        $title_err = "Please enter a title.";
    } else {
        $title = sanitizeInput($_POST["title"]);
    }
    
    // Validate date
    if (empty(trim($_POST["event_date"]))) {
        $event_date_err = "Please enter event date.";
    } else {
        $event_date = trim($_POST["event_date"]);
    }
    
    // Validate location
    if (empty(trim($_POST["location"]))) {
        $location_err = "Please enter event location.";
    } else {
        $location = sanitizeInput($_POST["location"]);
    }
    
    // Validate description
    if (empty(trim($_POST["description"]))) {
        $description_err = "Please enter event description.";
    } else {
        $description = sanitizeInput($_POST["description"]);
    }
    
    // Validate payment type and price
    $is_paid = isset($_POST["is_paid"]) ? 1 : 0;
    if ($is_paid) {
        if (empty(trim($_POST["price"])) || !is_numeric(trim($_POST["price"])) || trim($_POST["price"]) <= 0) {
            $price_err = "Please enter a valid price for paid event.";
        } else {
            $price = trim($_POST["price"]);
        }
    } else {
        $price = 0;
    }
    
    // Validate QR code (required for paid events)
    $has_qr = isset($_FILES["payment_qr"]) && $_FILES["payment_qr"]["error"] != 4;
    $qr_ext = $qr_tmp = null;
    if ($is_paid) {
        if (!$has_qr) {
            $qr_err = "QR code is required for paid events.";
        }
    }
    if ($is_paid && $has_qr) {
        $qr_ext = strtolower(pathinfo($_FILES["payment_qr"]["name"], PATHINFO_EXTENSION));
        $qr_tmp = $_FILES["payment_qr"]["tmp_name"];
        if (!validateFileUpload($_FILES["payment_qr"])) {
            $qr_err = "Invalid QR code file. Only JPG, JPEG, PNG & GIF files are allowed.";
        } elseif ($_FILES["payment_qr"]["size"] > 5000000) {
            $qr_err = "QR code file size must be less than 5MB.";
        }
    }
    
    // Validate image (if provided)
    $has_image = isset($_FILES["image"]) && $_FILES["image"]["error"] != 4;
    $file_ext = $file_tmp = null;
    
    if ($has_image) {
        $file_ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $file_tmp = $_FILES["image"]["tmp_name"];
        if (!validateFileUpload($_FILES["image"])) {
            $image_err = "Invalid image file. Only JPG, JPEG, PNG & GIF files are allowed.";
        } elseif ($_FILES["image"]["size"] > 5000000) {
            $image_err = "File size must be less than 5MB.";
        }
    }
    
    // Check input errors before inserting in database
    if (empty($title_err) && empty($event_date_err) && empty($location_err) && empty($description_err) && empty($image_err) && empty($price_err) && empty($qr_err)) {
        
        $param_image = null;
        $param_qr = null;
        
        // Process QR code if one was uploaded for paid event
        if ($is_paid && $has_qr) {
            $upload_dir = "../uploads/";
            
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $new_qr_name = 'qr_' . bin2hex(random_bytes(8)) . '.' . $qr_ext;
            $qr_upload_path = $upload_dir . $new_qr_name;
            
            if (move_uploaded_file($qr_tmp, $qr_upload_path)) {
                $param_qr = "uploads/" . $new_qr_name;
            } else {
                $qr_err = "Failed to upload QR code.";
            }
        }
        
        // Process image if one was uploaded
        if ($has_image) {
            // Upload image
            $upload_dir = "../uploads/";
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $new_file_name = bin2hex(random_bytes(8)) . '.' . $file_ext;
            $upload_path = $upload_dir . $new_file_name;
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                $param_image = "uploads/" . $new_file_name;
            } else {
                $image_err = "Failed to upload image.";
            }
        }
        
        // Prepare an insert statement
        $sql = "INSERT INTO events (title, event_date, location, description, image, is_paid, price, payment_qr) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("sssssids", $param_title, $param_event_date, $param_location, $param_description, $param_image, $param_is_paid, $param_price, $param_qr);
            
            // Set parameters
            $param_title = $title;
            $param_event_date = $event_date;
            $param_location = $location;
            $param_description = $description;
            $param_is_paid = $is_paid;
            $param_price = $price;
            // $param_qr is already set above
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                header("location: add_event.php?success=1");
                exit;
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
    <title>Add Event - IT Club</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../user/style.css">
    <link rel="stylesheet" href="admin_style.css">
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
                    <h1 class="h2">Add New Event</h1>
                    <div>
                        <a href="dashboard.php" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                        </a>
                    </div>
                </div>

                <div class="admin-content">
                    <?php if(!empty($success_message)): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    
                    <div class="card">
                        <div class="card-body">
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"], ENT_QUOTES, 'UTF-8'); ?>" method="post" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Event Title</label>
                                    <input type="text" class="form-control <?php echo (!empty($title_err)) ? 'is-invalid' : ''; ?>" id="title" name="title" value="<?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>">
                                    <span class="invalid-feedback"><?php echo $title_err; ?></span>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="event_date" class="form-label">Event Date & Time</label>
                                    <input type="datetime-local" class="form-control <?php echo (!empty($event_date_err)) ? 'is-invalid' : ''; ?>" id="event_date" name="event_date" value="<?php echo $event_date; ?>">
                                    <span class="invalid-feedback"><?php echo $event_date_err; ?></span>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="location" class="form-label">Location</label>
                                    <input type="text" class="form-control <?php echo (!empty($location_err)) ? 'is-invalid' : ''; ?>" id="location" name="location" value="<?php echo $location; ?>">
                                    <span class="invalid-feedback"><?php echo $location_err; ?></span>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control <?php echo (!empty($description_err)) ? 'is-invalid' : ''; ?>" id="description" name="description" rows="5"><?php echo $description; ?></textarea>
                                    <span class="invalid-feedback"><?php echo $description_err; ?></span>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check p-2 border rounded" style="background-color: #f8f9fa;">
                                        <input class="form-check-input" type="checkbox" id="is_paid" name="is_paid" <?php echo $is_paid ? 'checked' : ''; ?> onchange="togglePaymentFields()">
                                        <label class="form-check-label fw-bold" for="is_paid">
                                            <i class=""></i>
                                            Is this is a paid event
                                        </label>
                                    </div>
                                </div>
                                
                                <div id="payment-fields" style="display: <?php echo $is_paid ? 'block' : 'none'; ?>;">
                                    <div class="mb-3">
                                        <label for="price" class="form-label">Event Price (₹)</label>
                                        <input type="number" step="0.01" min="0" class="form-control <?php echo (!empty($price_err)) ? 'is-invalid' : ''; ?>" id="price" name="price" value="<?php echo $price; ?>" placeholder="Enter price in rupees">
                                        <span class="invalid-feedback"><?php echo $price_err; ?></span>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="payment_qr" class="form-label">Payment QR Code <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control <?php echo (!empty($qr_err)) ? 'is-invalid' : ''; ?>" id="payment_qr" name="payment_qr" accept="image/*">
                                        <div class="form-text">Upload QR code for payment (Required for paid events - JPG, PNG, GIF only, max 5MB)</div>
                                        <span class="invalid-feedback"><?php echo $qr_err; ?></span>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="image" class="form-label">Event Image (Optional)</label>
                                    <input type="file" class="form-control <?php echo (!empty($image_err)) ? 'is-invalid' : ''; ?>" id="image" name="image">
                                    <div class="form-text">Upload an image for the event (JPG, PNG, GIF only, max 5MB)</div>
                                    <span class="invalid-feedback"><?php echo $image_err; ?></span>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary" id="addEventBtn">
                                        <span id="addEventText">Add Event</span>
                                        <span id="addEventSpinner" class="spinner-border spinner-border-sm ms-2" style="display: none;"></span>
                                    </button>
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
    <script>
        function togglePaymentFields() {
            const isPaid = document.getElementById('is_paid').checked;
            const paymentFields = document.getElementById('payment-fields');
            
            paymentFields.style.display = isPaid ? 'block' : 'none';
            
            if (!isPaid) {
                document.getElementById('price').value = '';
                document.getElementById('payment_qr').value = '';
            }
        }
        
        // Form submission loading state
        document.querySelector('form').addEventListener('submit', function() {
            const btn = document.getElementById('addEventBtn');
            const text = document.getElementById('addEventText');
            const spinner = document.getElementById('addEventSpinner');
            
            btn.disabled = true;
            text.textContent = 'Adding Event...';
            spinner.style.display = 'inline-block';
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>