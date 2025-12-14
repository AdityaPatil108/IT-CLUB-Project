<?php
// Test feedback link generator
$event_id = 1; // Change to your event ID
$email = "test@example.com"; // Change to test email
$token = md5($event_id . $email . 'feedback_secret');

$feedback_url = "http://localhost/IT%20Club/user/feedback.php?event_id=" . $event_id . "&email=" . urlencode($email) . "&token=" . $token;

echo "Test Feedback URL:<br>";
echo '<a href="' . $feedback_url . '" target="_blank">' . $feedback_url . '</a>';
?>