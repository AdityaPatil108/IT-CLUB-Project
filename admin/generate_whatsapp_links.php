<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: ../user/login.php");
    exit;
}

require_once '../user/db_config.php';

$event_id = $_GET['event_id'] ?? 0;

if (!$event_id) {
    echo "<h3>Invalid Event ID</h3>";
    exit;
}

// Get event details
$event_sql = "SELECT * FROM events WHERE id = ? AND event_date < NOW()";
$event_stmt = $conn->prepare($event_sql);
$event_stmt->bind_param("i", $event_id);
$event_stmt->execute();
$event_result = $event_stmt->get_result();

if ($event_result->num_rows === 0) {
    echo "<h3>Event not found or not completed yet</h3>";
    exit;
}

$event = $event_result->fetch_assoc();

// Get participants with phone numbers
$participants_sql = "SELECT DISTINCT name, email, phone FROM event_registrations WHERE event_id = ? AND phone IS NOT NULL AND phone != ''";
$participants_stmt = $conn->prepare($participants_sql);
$participants_stmt->bind_param("i", $event_id);
$participants_stmt->execute();
$participants_result = $participants_stmt->get_result();

$base_url = 'https://itclub.sanmcs.com';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Feedback Links</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h3><i class="fab fa-whatsapp me-2"></i>WhatsApp Feedback Links</h3>
                        <h5 class="mb-0"><?php echo htmlspecialchars($event['title']); ?></h5>
                        <small><?php echo date('M d, Y', strtotime($event['event_date'])); ?></small>
                    </div>
                    <div class="card-body">
                        <?php if ($participants_result->num_rows > 0): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong><?php echo $participants_result->num_rows; ?></strong> participants with phone numbers found.
                                Click the WhatsApp buttons to send feedback requests.
                            </div>
                            
                            <div class="row">
                                <?php while ($participant = $participants_result->fetch_assoc()): ?>
                                    <?php
                                    $token = md5($event_id . $participant['email'] . 'feedback_secret');
                                    $feedback_url = $base_url . "/user/feedback.php?event_id=" . $event_id . "&email=" . urlencode($participant['email']) . "&token=" . $token;
                                    
                                    $message = "Hi " . $participant['name'] . "! Thank you for participating in '" . $event['title'] . "'. Please share your feedback: " . $feedback_url;
                                    $phone = preg_replace('/[^0-9]/', '', $participant['phone']);
                                    $whatsapp_url = "https://wa.me/" . $phone . "?text=" . urlencode($message);
                                    ?>
                                    
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card h-100 border-success">
                                            <div class="card-body">
                                                <h6 class="card-title text-truncate"><?php echo htmlspecialchars($participant['name']); ?></h6>
                                                <p class="card-text small text-muted mb-1">
                                                    <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($participant['phone']); ?>
                                                </p>
                                                <p class="card-text small text-muted mb-3">
                                                    <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($participant['email']); ?>
                                                </p>
                                                <a href="<?php echo $whatsapp_url; ?>" target="_blank" class="btn btn-success btn-sm w-100">
                                                    <i class="fab fa-whatsapp me-1"></i>Send WhatsApp
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                No participants with phone numbers found for this event.
                            </div>
                        <?php endif; ?>
                        
                        <div class="mt-4 text-center">
                            <a href="dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php $conn->close(); ?>