<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    require_once 'db_config.php';
} catch (Exception $e) {
    die('Database connection failed: ' . $e->getMessage());
}

$event_id = $_GET['event_id'] ?? 0;
$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';

// Verify token (skip for testing)
if (!empty($token)) {
    $expected_token = md5($event_id . $email . 'feedback_secret');
    if ($token !== $expected_token) {
        die('Invalid access token');
    }
}

// Debug: Show available events
if ($event_id == 0) {
    $all_events = $conn->query("SELECT id, title FROM events");
    echo "Available events:<br>";
    while($row = $all_events->fetch_assoc()) {
        echo "ID: {$row['id']} - {$row['title']}<br>";
    }
    exit;
}

// Get event details
try {
    $event_sql = "SELECT * FROM events WHERE id = ?";
    $event_stmt = $conn->prepare($event_sql);
    if (!$event_stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }
    $event_stmt->bind_param("i", $event_id);
    $event_stmt->execute();
    $event_result = $event_stmt->get_result();
    
    if ($event_result->num_rows === 0) {
        throw new Exception('Event not found. Event ID: ' . $event_id);
    }
    
    $event = $event_result->fetch_assoc();
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}

// Check if feedback already submitted (skip if table doesn't exist)
$already_submitted = false;
try {
    $check_sql = "SELECT id FROM event_feedback WHERE event_id = ? AND participant_email = ?";
    $check_stmt = $conn->prepare($check_sql);
    if ($check_stmt) {
        $check_stmt->bind_param("is", $event_id, $email);
        $check_stmt->execute();
        $existing = $check_stmt->get_result();
        $already_submitted = $existing->num_rows > 0;
    }
} catch (Exception $e) {
    // Table doesn't exist yet
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$already_submitted) {
    $rating = $_POST['rating'];
    $suggestions = $_POST['suggestions'] ?? '';
    $name = $_POST['name'];
    
    $insert_sql = "INSERT INTO event_feedback (event_id, participant_email, participant_name, rating, suggestions) VALUES (?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("issis", $event_id, $email, $name, $rating, $suggestions);
    
    if ($insert_stmt->execute()) {
        $success = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Feedback - <?php echo htmlspecialchars($event['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: linear-gradient(to right, #2a75bb, #0c4da2); min-height: 100vh; }
        .feedback-container { max-width: 650px; margin: 30px auto; }
        .card { border: none; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
        .card-header { background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); border-radius: 20px 20px 0 0 !important; padding: 30px; }
        .star-rating { font-size: 2.5rem; text-align: center; margin: 20px 0; }
        .star { color: #e0e0e0; cursor: pointer; transition: all 0.3s ease; margin: 0 5px; }
        .star:hover, .star.active { color: var(--accent-color); transform: scale(1.1); }
        .form-control { border-radius: 15px; border: 2px solid #e9ecef; padding: 15px; }
        .form-control:focus { border-color: var(--primary-color); box-shadow: 0 0 0 0.2rem rgba(12, 77, 162, 0.25); }
        .btn-primary { background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); border: none; border-radius: 15px; padding: 15px 30px; font-weight: 600; }
        .alert { border-radius: 15px; }
        .form-label { font-weight: 600; color: var(--dark-color); margin-bottom: 10px; }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="feedback-container">
            <div class="card shadow">
                <div class="card-header text-white text-center">
                    <h2><i class="fas fa-star me-3"></i>Event Feedback</h2>
                    <h5 class="mb-0 mt-2"><?php echo htmlspecialchars($event['title']); ?></h5>
                    <small class="opacity-75"><?php echo date('M d, Y', strtotime($event['event_date'])); ?></small>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success text-center">
                            <i class="fas fa-check-circle fa-3x mb-3"></i>
                            <h4>Thank You!</h4>
                            <p>Your feedback has been submitted successfully.</p>
                        </div>
                    <?php elseif ($already_submitted): ?>
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle fa-3x mb-3"></i>
                            <h4>Already Submitted</h4>
                            <p>You have already provided feedback for this event.</p>
                        </div>
                    <?php else: ?>
                        <form method="POST">
                            <div class="mb-4">
                                <label class="form-label"><i class="fas fa-user me-2"></i>Your Name</label>
                                <input type="text" name="name" class="form-control" placeholder="Enter your full name" required>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label text-center d-block"><i class="fas fa-star me-2"></i>Rate This Event</label>
                                <div class="star-rating" id="starRating">
                                    <span class="star" data-rating="1"><i class="fas fa-star"></i></span>
                                    <span class="star" data-rating="2"><i class="fas fa-star"></i></span>
                                    <span class="star" data-rating="3"><i class="fas fa-star"></i></span>
                                    <span class="star" data-rating="4"><i class="fas fa-star"></i></span>
                                    <span class="star" data-rating="5"><i class="fas fa-star"></i></span>
                                </div>
                                <p class="text-center text-muted small mb-0" id="ratingText">Click stars to rate</p>
                                <input type="hidden" name="rating" id="ratingInput" required>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label"><i class="fas fa-lightbulb me-2"></i>Suggestions for Improvement</label>
                                <textarea name="suggestions" class="form-control" rows="4" placeholder="Share your thoughts and suggestions to help us improve future events..."></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Submit Feedback
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        const ratingTexts = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];
        
        document.querySelectorAll('.star').forEach(star => {
            star.addEventListener('click', function() {
                const rating = this.dataset.rating;
                document.getElementById('ratingInput').value = rating;
                document.getElementById('ratingText').textContent = ratingTexts[rating];
                
                document.querySelectorAll('.star').forEach((s, index) => {
                    s.classList.toggle('active', index < rating);
                });
            });
            
            star.addEventListener('mouseenter', function() {
                const rating = this.dataset.rating;
                document.querySelectorAll('.star').forEach((s, index) => {
                    s.style.color = index < rating ? '#ffd700' : '#e0e0e0';
                });
            });
        });
        
        document.getElementById('starRating').addEventListener('mouseleave', function() {
            const currentRating = document.getElementById('ratingInput').value;
            document.querySelectorAll('.star').forEach((s, index) => {
                s.style.color = index < currentRating ? '#ffd700' : '#e0e0e0';
            });
        });
    </script>
</body>
</html>