<?php
session_start();
require_once '../includes/env_loader.php';
require_once '../config/database.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../user/login.php');
    exit();
}

$conn = getDBConnection();
$gallery_file = '../data/event_gallery.json';
$gallery_data = json_decode(file_get_contents($gallery_file), true) ?: [];

// Get events
$events_query = "SELECT id, title FROM events ORDER BY event_date DESC";
$events_result = $conn->query($events_query);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['upload_images'])) {
        $event_id = $_POST['event_id'];
        
        if (!isset($gallery_data[$event_id])) {
            $gallery_data[$event_id] = [];
        }
        
        $upload_dir = '../uploads/gallery/';
        $uploaded_files = [];
        
        foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['gallery_images']['error'][$key] === UPLOAD_ERR_OK) {
                $file_name = time() . '_' . $key . '_' . $_FILES['gallery_images']['name'][$key];
                $target_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($tmp_name, $target_path)) {
                    $gallery_data[$event_id][] = 'uploads/gallery/' . $file_name;
                }
            }
        }
        
        file_put_contents($gallery_file, json_encode($gallery_data, JSON_PRETTY_PRINT));
        $success = "Images uploaded successfully!";
    } elseif (isset($_POST['delete_image'])) {
        $event_id = $_POST['event_id'];
        $image_index = $_POST['image_index'];
        
        if (isset($gallery_data[$event_id][$image_index])) {
            $image_path = '../' . $gallery_data[$event_id][$image_index];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
            unset($gallery_data[$event_id][$image_index]);
            $gallery_data[$event_id] = array_values($gallery_data[$event_id]);
            
            file_put_contents($gallery_file, json_encode($gallery_data, JSON_PRETTY_PRINT));
            $success = "Image deleted successfully!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Event Gallery - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../user/style.css">
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <button class="sidebar-toggle" id="sidebarToggle">
        <div class="hamburger-lines">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </button>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>

            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="admin-header d-flex justify-content-between align-items-center">
                    <h1 class="h2"><i class="fas fa-images me-2"></i>Event Gallery</h1>
                    <div>
                        <span>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
                    </div>
                </div>

                <div class="admin-content">
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i><?= $success ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5><i class="fas fa-upload me-2"></i>Upload Event Images</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Select Event:</label>
                                        <select name="event_id" class="form-select" required>
                                            <option value="">Choose Event</option>
                                            <?php while ($event = $events_result->fetch_assoc()): ?>
                                                <option value="<?= $event['id'] ?>"><?= htmlspecialchars($event['title']) ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Select Images/Videos:</label>
                                        <input type="file" name="gallery_images[]" class="form-control" multiple accept="image/*,video/*" required>
                                        <small class="text-muted">Supported: Images (JPG, PNG) and Videos (MP4, MOV)</small>
                                    </div>
                                </div>
                                <button type="submit" name="upload_images" class="btn btn-primary">
                                    <i class="fas fa-upload me-2"></i>Upload Images
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-photo-video me-2"></i>Event Galleries</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($gallery_data)): ?>
                                <p class="text-muted">No images uploaded yet.</p>
                            <?php else: ?>
                                <?php foreach ($gallery_data as $event_id => $images): ?>
                                    <?php if (!empty($images)): ?>
                                        <?php
                                        $event_query = "SELECT title FROM events WHERE id = $event_id";
                                        $event_result = $conn->query($event_query);
                                        $event_title = $event_result->fetch_assoc()['title'] ?? 'Unknown Event';
                                        ?>
                                        <h6 class="mt-4"><?= htmlspecialchars($event_title) ?></h6>
                                        <div class="row">
                                            <?php foreach ($images as $index => $image): ?>
                                                <div class="col-md-3 mb-3">
                                                    <div class="card">
                                                        <?php 
                                                        $file_ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));
                                                        $is_video = in_array($file_ext, ['mp4', 'mov', 'avi', 'webm']);
                                                        ?>
                                                        <?php if ($is_video): ?>
                                                            <div style="position: relative; height: 150px; background: #000;">
                                                                <video style="width: 100%; height: 100%; object-fit: cover;">
                                                                    <source src="../<?= $image ?>" type="video/<?= $file_ext ?>">
                                                                </video>
                                                                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-size: 2em;">
                                                                    <i class="fas fa-play-circle"></i>
                                                                </div>
                                                            </div>
                                                        <?php else: ?>
                                                            <img src="../<?= $image ?>" class="card-img-top" style="height: 150px; object-fit: cover;">
                                                        <?php endif; ?>
                                                        <div class="card-body p-2">
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="event_id" value="<?= $event_id ?>">
                                                                <input type="hidden" name="image_index" value="<?= $index ?>">
                                                                <button type="submit" name="delete_image" class="btn btn-sm btn-danger w-100" onclick="return confirm('Delete this <?= $is_video ? 'video' : 'image' ?>?')">
                                                                    <i class="fas fa-trash"></i> Delete
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
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