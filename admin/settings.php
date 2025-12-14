<?php
session_start();
require_once '../config/database.php';
require_once '../includes/settings_helper.php';

$conn = getDBConnection();

// Check if user is logged in as faculty
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'faculty') {
    header("Location: ../user/login.php?role=faculty");
    exit();
}

$success_message = $error_message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $settings_to_update = [
        'contact_email' => $_POST['contact_email'] ?? '',
        'contact_phone' => $_POST['contact_phone'] ?? '',
        'contact_address' => $_POST['contact_address'] ?? '',
        'site_title' => $_POST['site_title'] ?? '',
        'site_description' => $_POST['site_description'] ?? '',
        'college_name' => $_POST['college_name'] ?? '',
        'department_name' => $_POST['department_name'] ?? '',
        'instagram_url' => $_POST['instagram_url'] ?? '',
        'facebook_url' => $_POST['facebook_url'] ?? '',
        'twitter_url' => $_POST['twitter_url'] ?? '',
        'linkedin_url' => $_POST['linkedin_url'] ?? '',
        'footer_text' => $_POST['footer_text'] ?? '',
        'about_text' => $_POST['about_text'] ?? '',
        'default_event_location' => $_POST['default_event_location'] ?? '',
        'event_registration_email' => $_POST['event_registration_email'] ?? ''
    ];
    
    $updated = true;
    foreach ($settings_to_update as $key => $value) {
        $updated &= updateSetting($key, $value);
    }
    
    if ($updated) {
        $success_message = "All settings updated successfully!";
    } else {
        $error_message = "Error updating some settings.";
    }
}

// Get current settings
$settings = getAllSettings();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Settings - IT Club Admin</title>
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
                    <h1 class="h2">Site Settings</h1>
                    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                </div>

                <div class="admin-content">
                    <div class="card">
                        <div class="card-body">
                        <?php if($success_message): ?>
                            <div class="alert alert-success"><?php echo $success_message; ?></div>
                        <?php endif; ?>
                        
                        <?php if($error_message): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>
                        
                        <form method="post">
                            <h5 class="mb-3">Contact Information</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="contact_email" class="form-label">Contact Email</label>
                                    <input type="email" class="form-control" id="contact_email" name="contact_email" 
                                           value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="contact_phone" class="form-label">Contact Phone</label>
                                    <input type="text" class="form-control" id="contact_phone" name="contact_phone" 
                                           value="<?php echo htmlspecialchars($settings['contact_phone'] ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="contact_address" class="form-label">Contact Address</label>
                                <textarea class="form-control" id="contact_address" name="contact_address" rows="3" required><?php echo htmlspecialchars($settings['contact_address'] ?? ''); ?></textarea>
                            </div>
                            
                            <hr class="my-4">
                            <h5 class="mb-3">Site Information</h5>
                            
                            <div class="mb-3">
                                <label for="site_title" class="form-label">Site Title</label>
                                <input type="text" class="form-control" id="site_title" name="site_title" 
                                       value="<?php echo htmlspecialchars($settings['site_title'] ?? 'IT Club'); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="site_description" class="form-label">Site Description</label>
                                <textarea class="form-control" id="site_description" name="site_description" rows="2" required><?php echo htmlspecialchars($settings['site_description'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="college_name" class="form-label">College Name</label>
                                <input type="text" class="form-control" id="college_name" name="college_name" 
                                       value="<?php echo htmlspecialchars($settings['college_name'] ?? 'Sangameshwar College'); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="department_name" class="form-label">Department Name</label>
                                <input type="text" class="form-control" id="department_name" name="department_name" 
                                       value="<?php echo htmlspecialchars($settings['department_name'] ?? 'Computer Science Department'); ?>" required>
                            </div>
                            
                            <hr class="my-4">
                            <h5 class="mb-3">Social Media & Links</h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="instagram_url" class="form-label">Instagram URL</label>
                                    <input type="url" class="form-control" id="instagram_url" name="instagram_url" 
                                           value="<?php echo htmlspecialchars($settings['instagram_url'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="facebook_url" class="form-label">Facebook URL</label>
                                    <input type="url" class="form-control" id="facebook_url" name="facebook_url" 
                                           value="<?php echo htmlspecialchars($settings['facebook_url'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="twitter_url" class="form-label">Twitter URL</label>
                                    <input type="url" class="form-control" id="twitter_url" name="twitter_url" 
                                           value="<?php echo htmlspecialchars($settings['twitter_url'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="linkedin_url" class="form-label">LinkedIn URL</label>
                                    <input type="url" class="form-control" id="linkedin_url" name="linkedin_url" 
                                           value="<?php echo htmlspecialchars($settings['linkedin_url'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            <h5 class="mb-3">Footer & Copyright</h5>
                            
                            <div class="mb-3">
                                <label for="footer_text" class="form-label">Footer Copyright Text</label>
                                <input type="text" class="form-control" id="footer_text" name="footer_text" 
                                       value="<?php echo htmlspecialchars($settings['footer_text'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="about_text" class="form-label">About IT Club Text</label>
                                <textarea class="form-control" id="about_text" name="about_text" rows="4"><?php echo htmlspecialchars($settings['about_text'] ?? 'We are a community of tech enthusiasts dedicated to learning, sharing knowledge, and building innovative solutions.'); ?></textarea>
                            </div>
                            
                            <hr class="my-4">
                            <h5 class="mb-3">Event Settings</h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="default_event_location" class="form-label">Default Event Location</label>
                                    <input type="text" class="form-control" id="default_event_location" name="default_event_location" 
                                           value="<?php echo htmlspecialchars($settings['default_event_location'] ?? 'College Auditorium'); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="event_registration_email" class="form-label">Event Registration Email</label>
                                    <input type="email" class="form-control" id="event_registration_email" name="event_registration_email" 
                                           value="<?php echo htmlspecialchars($settings['event_registration_email'] ?? $settings['contact_email'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-primary btn-lg">Update All Settings</button>
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