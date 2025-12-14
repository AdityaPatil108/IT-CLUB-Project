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
    header("location: manage_events.php");
    exit;
}

$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
if ($id === false) {
    header("location: manage_events.php");
    exit;
}
$title = $event_date = $location = $description = $current_image = $price = "";
$is_paid = 0;
$current_qr = "";
$title_err = $event_date_err = $location_err = $description_err = $image_err = $price_err = $qr_err = "";

// Get event details
$sql = "SELECT * FROM events WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

// If event doesn't exist, redirect to manage events page
if ($result->num_rows == 0) {
    header("location: manage_events.php");
    exit;
}

$event = $result->fetch_assoc();
$title = $event['title'];
$event_date = date('Y-m-d\TH:i', strtotime($event['event_date']));
$location = $event['location'];
$description = $event['description'];
$current_image = $event['image'];
$is_paid = isset($event['is_paid']) ? $event['is_paid'] : 0;
$price = isset($event['price']) ? $event['price'] : 0;
$current_qr = isset($event['payment_qr']) ? $event['payment_qr'] : '';

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate title
    if (empty(trim($_POST["title"]))) {
        $title_err = "Please enter a title.";
    } else {
        $title = trim($_POST["title"]);
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
        $location = trim($_POST["location"]);
    }
    
    // Validate description
    if (empty(trim($_POST["description"]))) {
        $description_err = "Please enter event description.";
    } else {
        $description = trim($_POST["description"]);
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
    
    // Validate QR code (if paid event)
    $update_qr = false;
    if ($is_paid && isset($_FILES["payment_qr"]) && $_FILES["payment_qr"]["error"] != 4) {
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        $qr_name = $_FILES["payment_qr"]["name"];
        $qr_size = $_FILES["payment_qr"]["size"];
        $qr_tmp = $_FILES["payment_qr"]["tmp_name"];
        $qr_ext = strtolower(pathinfo($qr_name, PATHINFO_EXTENSION));
        
        if (!in_array($qr_ext, $allowed_types)) {
            $qr_err = "Only JPG, JPEG, PNG & GIF files are allowed for QR code.";
        }
        
        if ($qr_size > 5000000) {
            $qr_err = "QR code file size must be less than 5MB.";
        }
        
        $update_qr = true;
    }
    
    // Check if a new image was uploaded
    $update_image = false;
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] != 4) {
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        $file_name = $_FILES["image"]["name"];
        $file_size = $_FILES["image"]["size"];
        $file_tmp = $_FILES["image"]["tmp_name"];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Check file extension
        if (!in_array($file_ext, $allowed_types)) {
            $image_err = "Only JPG, JPEG, PNG & GIF files are allowed.";
        }
        
        // Check file size (5MB max)
        if ($file_size > 5000000) {
            $image_err = "File size must be less than 5MB.";
        }
        
        $update_image = true;
    }
    
    // Check input errors before updating in database
    if (empty($title_err) && empty($event_date_err) && empty($location_err) && empty($description_err) && empty($image_err) && empty($price_err) && empty($qr_err)) {
        
        $qr_path = $current_qr;
        
        // Process QR code if one was uploaded
        if ($update_qr) {
            $upload_dir = "../uploads/";
            
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $new_qr_name = 'qr_' . bin2hex(random_bytes(8)) . '.' . $qr_ext;
            $qr_upload_path = $upload_dir . $new_qr_name;
            
            if (move_uploaded_file($qr_tmp, $qr_upload_path)) {
                $qr_path = "uploads/" . $new_qr_name;
            } else {
                $qr_err = "Failed to upload QR code.";
            }
        }
        
        // If a new image was uploaded
        if ($update_image) {
            // Upload image
            $upload_dir = "../uploads/";
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $new_file_name = bin2hex(random_bytes(8)) . '.' . $file_ext;
            $upload_path = $upload_dir . $new_file_name;
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Update the image path
                $image_path = "uploads/" . $new_file_name;
                
                // Prepare an update statement with new image
                $sql = "UPDATE events SET title = ?, event_date = ?, location = ?, description = ?, image = ?, is_paid = ?, price = ?, payment_qr = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssidsi", $title, $event_date, $location, $description, $image_path, $is_paid, $price, $qr_path, $id);
            } else {
                $image_err = "Failed to upload image.";
            }
        } else {
            // Prepare an update statement without changing the image
            $sql = "UPDATE events SET title = ?, event_date = ?, location = ?, description = ?, is_paid = ?, price = ?, payment_qr = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssidsi", $title, $event_date, $location, $description, $is_paid, $price, $qr_path, $id);
        }
        
        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            // Redirect to manage events page with success message
            header("location: manage_events.php?success=update");
            exit;
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
        
        // Close statement
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event - IT Club</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../user/style.css">
    <link rel="stylesheet" href="admin_style.css">
    <style>
        .current-image {
            max-width: 200px;
            border-radius: 5px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        @media (max-width: 576px) {
            .current-image {
                max-width: 100%;
            }
            
            .d-flex.gap-2 {
                flex-direction: column;
            }
            
            .d-flex.gap-2 .btn {
                margin-bottom: 10px;
                width: 100%;
            }
        }
    </style>
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
                    <h1 class="h2">Edit Event</h1>
                    <div>
                        <a href="manage_events.php" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to Events
                        </a>
                    </div>
                </div>

                <div class="admin-content">
                    <div class="card">
                        <div class="card-body">
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . intval($id), ENT_QUOTES, 'UTF-8'); ?>" method="post" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Event Title</label>
                                    <input type="text" class="form-control <?php echo (!empty($title_err)) ? 'is-invalid' : ''; ?>" id="title" name="title" value="<?php echo $title; ?>">
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
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_paid" name="is_paid" <?php echo $is_paid ? 'checked' : ''; ?> onchange="togglePaymentFields()">
                                        <label class="form-check-label" for="is_paid">
                                            This is a paid event
                                        </label>
                                    </div>
                                </div>
                                
                                <div id="payment-fields" style="display: <?php echo $is_paid ? 'block' : 'none'; ?>;">
                                    <div class="mb-3">
                                        <label for="price" class="form-label">Event Price (₹)</label>
                                        <input type="number" step="0.01" min="0" class="form-control <?php echo (!empty($price_err)) ? 'is-invalid' : ''; ?>" id="price" name="price" value="<?php echo $price; ?>" placeholder="Enter price in rupees">
                                        <span class="invalid-feedback"><?php echo $price_err; ?></span>
                                    </div>
                                    
                                    <?php if (!empty($current_qr)): ?>
                                    <div class="mb-3">
                                        <label class="form-label">Current QR Code</label>
                                        <div class="mb-2">
                                            <img src="../<?php echo htmlspecialchars($current_qr); ?>" style="max-width: 150px; border: 1px solid #ddd; border-radius: 5px;" alt="Current QR Code">
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="mb-3">
                                        <label for="payment_qr" class="form-label">Change Payment QR Code (Optional)</label>
                                        <input type="file" class="form-control <?php echo (!empty($qr_err)) ? 'is-invalid' : ''; ?>" id="payment_qr" name="payment_qr" accept="image/*">
                                        <div class="form-text">Upload new QR code for payment (JPG, PNG, GIF only, max 5MB)</div>
                                        <span class="invalid-feedback"><?php echo $qr_err; ?></span>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Current Image</label>
                                    <div class="mb-3">
                                        <img src="../<?php echo htmlspecialchars($current_image); ?>" class="current-image" alt="Current Event Image">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="image" class="form-label">Change Image (Optional)</label>
                                    <input type="file" class="form-control <?php echo (!empty($image_err)) ? 'is-invalid' : ''; ?>" id="image" name="image">
                                    <div class="form-text">Upload a new image for the event (JPG, PNG, GIF only, max 5MB)</div>
                                    <span class="invalid-feedback"><?php echo $image_err; ?></span>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">Update Event</button>
                                    <a href="manage_events.php" class="btn btn-secondary">Cancel</a>
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
                document.getElementById('price').value = '0';
            }
        }
    </script>
</body>
</html>
<?php 
$stmt->close();
$conn->close(); 
?>