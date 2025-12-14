<?php
require_once __DIR__ . '/env_loader.php';

function sendGmailSMTP($to, $subject, $message) {
    $smtp_server = "ssl://smtp.gmail.com";
    $smtp_port = 465;
    $username = "ap3441218@gmail.com";
    $password = $_ENV['GMAIL_APP_PASSWORD'] ?? 'blxvcfkeylqnqzwe';
    
    // Remove console logs for production
    $isLocal = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false);
    
    if ($isLocal) echo "<script>console.log('Connecting to Gmail SMTP SSL...');</script>";
    
    $socket = fsockopen($smtp_server, $smtp_port, $errno, $errstr, 30);
    if (!$socket) {
        echo "<script>console.error('Connection failed: $errstr');</script>";
        return false;
    }
    
    $response = fgets($socket, 515);
    if ($isLocal) echo "<script>console.log('Server: " . addslashes(trim($response)) . "');</script>";
    
    // EHLO
    fputs($socket, "EHLO gmail.com\r\n");
    $response = fgets($socket, 515);
    if ($isLocal) echo "<script>console.log('EHLO: " . addslashes(trim($response)) . "');</script>";
    
    // Read all EHLO responses
    while (substr($response, 3, 1) == '-') {
        $response = fgets($socket, 515);
        if ($isLocal) echo "<script>console.log('EHLO cont: " . addslashes(trim($response)) . "');</script>";
    }
    
    // AUTH LOGIN
    fputs($socket, "AUTH LOGIN\r\n");
    $response = fgets($socket, 515);
    if ($isLocal) echo "<script>console.log('AUTH: " . addslashes(trim($response)) . "');</script>";
    
    // Username
    fputs($socket, base64_encode($username) . "\r\n");
    $response = fgets($socket, 515);
    if ($isLocal) echo "<script>console.log('User: " . addslashes(trim($response)) . "');</script>";
    
    // Password
    fputs($socket, base64_encode($password) . "\r\n");
    $response = fgets($socket, 515);
    if ($isLocal) echo "<script>console.log('Pass: " . addslashes(trim($response)) . "');</script>";
    
    if (substr($response, 0, 3) != '235') {
        if ($isLocal) echo "<script>console.error('Auth failed: " . addslashes(trim($response)) . "');</script>";
        error_log('Gmail SMTP Auth failed: ' . trim($response));
        fclose($socket);
        return false;
    }
    if ($isLocal) echo "<script>console.log('Authentication SUCCESS!');</script>";
    
    // MAIL FROM
    fputs($socket, "MAIL FROM: <$username>\r\n");
    $response = fgets($socket, 515);
    if ($isLocal) echo "<script>console.log('FROM: " . addslashes(trim($response)) . "');</script>";
    
    // RCPT TO
    fputs($socket, "RCPT TO: <$to>\r\n");
    $response = fgets($socket, 515);
    if ($isLocal) echo "<script>console.log('TO: " . addslashes(trim($response)) . "');</script>";
    
    // DATA
    fputs($socket, "DATA\r\n");
    $response = fgets($socket, 515);
    if ($isLocal) echo "<script>console.log('DATA: " . addslashes(trim($response)) . "');</script>";
    
    // Email content
    $email_data = "From: IT Club <$username>\r\n";
    $email_data .= "To: $to\r\n";
    $email_data .= "Subject: $subject\r\n";
    $email_data .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $email_data .= "\r\n";
    $email_data .= $message . "\r\n";
    $email_data .= ".\r\n";
    
    fputs($socket, $email_data);
    $response = fgets($socket, 515);
    if ($isLocal) echo "<script>console.log('SEND: " . addslashes(trim($response)) . "');</script>";
    
    // QUIT
    fputs($socket, "QUIT\r\n");
    fclose($socket);
    
    $success = substr($response, 0, 3) == '250';
    if ($isLocal) echo "<script>console.log('Result: " . ($success ? 'SUCCESS' : 'FAILED') . "');</script>";
    if (!$success) error_log('Gmail SMTP send failed');
    return $success;
}

function sendContactNotification($name, $email, $subject, $message) {
    $email_subject = "New Contact Form: " . $subject;
    $email_body = "Contact Form Submission\n\n";
    $email_body .= "Name: " . $name . "\n";
    $email_body .= "Email: " . $email . "\n";
    $email_body .= "Subject: " . $subject . "\n";
    $email_body .= "Message:\n" . $message . "\n";
    $email_body .= "\nTime: " . date('Y-m-d H:i:s');
    
    return sendGmailSMTP('ap3441218@gmail.com', $email_subject, $email_body);
}

function sendQueryResponse($studentEmail, $studentName, $eventTitle, $query, $response) {
    $subject = "Query Response: " . $eventTitle;
    $body = "Dear " . $studentName . ",\n\n";
    $body .= "Your query about " . $eventTitle . ":\n" . $query . "\n\n";
    $body .= "Our response:\n" . $response . "\n\n";
    $body .= "Best regards,\nIT Club Team";
    
    return sendGmailSMTP($studentEmail, $subject, $body);
}
?>