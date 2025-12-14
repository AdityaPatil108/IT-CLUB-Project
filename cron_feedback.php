<?php
// This script should be run via cron job every hour to send feedback emails
// Add to crontab: 0 * * * * /usr/bin/php /path/to/your/project/cron_feedback.php

require_once 'includes/feedback_mailer.php';

// Log file for debugging
$log_file = __DIR__ . '/logs/feedback_cron.log';
$log_dir = dirname($log_file);

if (!is_dir($log_dir)) {
    mkdir($log_dir, 0755, true);
}

$timestamp = date('Y-m-d H:i:s');
$log_message = "[$timestamp] Starting feedback email cron job\n";

try {
    $sent_count = autoSendFeedbackEmails();
    $log_message .= "[$timestamp] Successfully sent $sent_count feedback emails\n";
    echo "Sent $sent_count feedback emails\n";
} catch (Exception $e) {
    $log_message .= "[$timestamp] Error: " . $e->getMessage() . "\n";
    echo "Error: " . $e->getMessage() . "\n";
}

$log_message .= "[$timestamp] Feedback email cron job completed\n\n";
file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
?>