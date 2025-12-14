<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: ../user/login.php");
    exit;
}

require_once '../user/db_config.php';

$event_id = $_GET['event_id'] ?? 0;

if ($event_id) {
    // Get event details
    $event_sql = "SELECT * FROM events WHERE id = ? AND event_date < NOW()";
    $event_stmt = $conn->prepare($event_sql);
    $event_stmt->bind_param("i", $event_id);
    $event_stmt->execute();
    $event_result = $event_stmt->get_result();
    
    if ($event_result->num_rows > 0) {
        $event = $event_result->fetch_assoc();
        
        // Get participants
        $participants_sql = "SELECT DISTINCT name, email FROM event_registrations WHERE event_id = ?";
        $participants_stmt = $conn->prepare($participants_sql);
        $participants_stmt->bind_param("i", $event_id);
        $participants_stmt->execute();
        $participants_result = $participants_stmt->get_result();
        
        $base_url = 'https://itclub.sanmcs.com';
        
        echo "<h3>Feedback Email Details for: " . htmlspecialchars($event['title']) . "</h3>";
        echo "<p><strong>Total Participants:</strong> " . $participants_result->num_rows . "</p>";
        echo "<hr>";
        
        echo "<h4>Email List (Copy to your email client):</h4>";
        $emails = [];
        while ($participant = $participants_result->fetch_assoc()) {
            $emails[] = $participant['email'];
        }
        echo "<textarea class='form-control' rows='5' style='width:100%;'>" . implode(', ', $emails) . "</textarea>";
        
        echo "<hr>";
        echo "<h4>Email Template:</h4>";
        echo "<div style='border:1px solid #ccc; padding:15px; background:#f9f9f9;'>";
        echo "<strong>Subject:</strong> Share Your Feedback - " . htmlspecialchars($event['title']) . "<br><br>";
        echo "<strong>Message:</strong><br>";
        echo "Dear Participant,<br><br>";
        echo "Thank you for participating in '" . htmlspecialchars($event['title']) . "'!<br><br>";
        echo "We would love to hear your feedback about the event. Your input helps us improve future events.<br><br>";
        echo "Please click the link below to share your feedback:<br>";
        echo $base_url . "/user/feedback.php?event_id=" . $event_id . "&email=[PARTICIPANT_EMAIL]&token=[TOKEN]<br><br>";
        echo "Best regards,<br>IT Club Team";
        echo "</div>";
        
        echo "<hr>";
        echo "<h4>Individual Feedback Links:</h4>";
        
        // Reset result pointer
        $participants_stmt->execute();
        $participants_result = $participants_stmt->get_result();
        
        while ($participant = $participants_result->fetch_assoc()) {
            $token = md5($event_id . $participant['email'] . 'feedback_secret');
            $feedback_url = $base_url . "/user/feedback.php?event_id=" . $event_id . "&email=" . urlencode($participant['email']) . "&token=" . $token;
            
            echo "<div style='margin:10px 0; padding:10px; border:1px solid #ddd;'>";
            echo "<strong>" . htmlspecialchars($participant['name']) . "</strong> (" . htmlspecialchars($participant['email']) . ")<br>";
            echo "<a href='" . $feedback_url . "' target='_blank'>" . $feedback_url . "</a>";
            echo "</div>";
        }
    }
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
textarea { margin: 10px 0; }
</style>