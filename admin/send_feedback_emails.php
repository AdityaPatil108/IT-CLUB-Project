<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: ../user/login.php");
    exit;
}

require_once '../includes/feedback_mailer.php';

$event_id = $_GET['id'] ?? 0;

if ($event_id && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['resend'])) {
        $sent_count = resendFeedbackEmails($event_id);
        $message = "resend";
    } else {
        $sent_count = sendFeedbackEmailsForEvent($event_id);
        $message = "sent";
    }
    
    if ($sent_count !== false) {
        header("location: dashboard.php?feedback_" . $message . "=" . $sent_count);
    } else {
        header("location: dashboard.php?error=feedback_send_failed");
    }
    exit;
}

// Get event details
$event_sql = "SELECT * FROM events WHERE id = ? AND event_date < NOW()";
$event_stmt = $conn->prepare($event_sql);
$event_stmt->bind_param("i", $event_id);
$event_stmt->execute();
$event_result = $event_stmt->get_result();

if ($event_result->num_rows === 0) {
    header("location: dashboard.php");
    exit;
}

$event = $event_result->fetch_assoc();

// Get participant count
$participants_sql = "SELECT COUNT(DISTINCT email) as count FROM event_registrations WHERE event_id = ?";
$participants_stmt = $conn->prepare($participants_sql);
$participants_stmt->bind_param("i", $event_id);
$participants_stmt->execute();
$participants_result = $participants_stmt->get_result();
$participant_count = $participants_result->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Feedback Emails</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h4>Send Feedback Emails</h4>
                    </div>
                    <div class="card-body">
                        <h5><?php echo htmlspecialchars($event['title']); ?></h5>
                        <p class="text-muted"><?php echo date('M d, Y', strtotime($event['event_date'])); ?></p>
                        
                        <?php if ($event['feedback_sent']): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Feedback emails have already been sent for this event.
                            </div>
                        <?php endif; ?>
                        
                        <p>This will send feedback request emails to <strong><?php echo $participant_count; ?></strong> participants.</p>
                        
                        <form method="POST">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-envelope"></i> Send Feedback Emails
                                </button>
                                <button type="submit" name="resend" value="1" class="btn btn-warning">
                                    <i class="fas fa-redo"></i> Resend Feedback Emails
                                </button>
                                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>