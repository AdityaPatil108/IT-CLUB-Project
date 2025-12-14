<?php
require_once 'gmail_smtp.php';
require_once '../user/db_config.php';

function sendFeedbackEmail($participant_email, $participant_name, $event_id, $event_title) {
    $token = md5($event_id . $participant_email . 'feedback_secret');
    // Hardcoded base URL for deployed server
    $base_url = 'https://itclub.sanmcs.com';
    
    $feedback_url = $base_url . "/user/feedback.php?event_id=" . $event_id . "&email=" . urlencode($participant_email) . "&token=" . $token;
    
    $subject = "Share Your Feedback - " . $event_title;
    $message = "Dear " . $participant_name . ",\n\n";
    $message .= "Thank you for participating in '" . $event_title . "'!\n\n";
    $message .= "We would love to hear your feedback about the event. Your input helps us improve future events.\n\n";
    $message .= "Please click the link below to share your feedback:\n";
    $message .= $feedback_url . "\n\n";
    $message .= "The feedback form will only take a few minutes to complete.\n\n";
    $message .= "Best regards,\n";
    $message .= "IT Club Team";
    
    return sendGmailSMTP($participant_email, $subject, $message);
}

function sendFeedbackEmailsForEvent($event_id) {
    global $conn;
    
    // Get event details
    $event_sql = "SELECT * FROM events WHERE id = ? AND event_date < NOW()";
    $event_stmt = $conn->prepare($event_sql);
    $event_stmt->bind_param("i", $event_id);
    $event_stmt->execute();
    $event_result = $event_stmt->get_result();
    
    if ($event_result->num_rows === 0) {
        return false;
    }
    
    $event = $event_result->fetch_assoc();
    
    // Get all participants
    $participants_sql = "SELECT DISTINCT name, email FROM event_registrations WHERE event_id = ?";
    $participants_stmt = $conn->prepare($participants_sql);
    $participants_stmt->bind_param("i", $event_id);
    $participants_stmt->execute();
    $participants_result = $participants_stmt->get_result();
    
    $sent_count = 0;
    $batch_size = 10;
    $delay = 2;
    
    while ($participant = $participants_result->fetch_assoc()) {
        if (sendFeedbackEmail($participant['email'], $participant['name'], $event_id, $event['title'])) {
            $sent_count++;
            sleep($delay);
            
            if ($sent_count % $batch_size == 0) {
                sleep(5);
            }
        }
    }
    
    return $sent_count;
}

// Resend emails for specific event
function resendFeedbackEmails($event_id) {
    return sendFeedbackEmailsForEvent($event_id);
}

// Auto-send emails for completed events (can be called via cron)
function autoSendFeedbackEmails() {
    global $conn;
    
    $events_sql = "SELECT id FROM events WHERE event_date < NOW() AND feedback_sent = 0";
    $events_result = $conn->query($events_sql);
    
    $total_sent = 0;
    while ($event = $events_result->fetch_assoc()) {
        $sent = sendFeedbackEmailsForEvent($event['id']);
        if ($sent !== false) {
            $total_sent += $sent;
        }
    }
    
    return $total_sent;
}
?>