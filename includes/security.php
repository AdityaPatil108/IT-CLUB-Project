<?php
/**
 * Security helper functions for input validation and sanitization
 */

/**
 * Validate and sanitize input data
 */
function validateInput($data, $type = 'string', $required = true) {
    if ($required && (empty($data) || !isset($data))) {
        return false;
    }
    
    switch ($type) {
        case 'email':
            return filter_var($data, FILTER_VALIDATE_EMAIL);
        case 'int':
            return filter_var($data, FILTER_VALIDATE_INT);
        case 'string':
            return trim(htmlspecialchars($data, ENT_QUOTES, 'UTF-8'));
        case 'role':
            return in_array($data, ['faculty', 'member']) ? $data : false;
        default:
            return trim(htmlspecialchars($data, ENT_QUOTES, 'UTF-8'));
    }
}

/**
 * Sanitize output for display
 */
function sanitizeOutput($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize input data (alias for validateInput with string type)
 */
function sanitizeInput($data) {
    return validateInput($data, 'string', false);
}

/**
 * Validate email address
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate integer
 */
function validateInteger($value) {
    return filter_var($value, FILTER_VALIDATE_INT);
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Rate limiting for login attempts
 */
function checkRateLimit($identifier, $max_attempts = 5, $time_window = 900) {
    $key = 'login_attempts_' . $identifier;
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'first_attempt' => time()];
    }
    
    $attempts = $_SESSION[$key];
    
    // Reset if time window has passed
    if (time() - $attempts['first_attempt'] > $time_window) {
        $_SESSION[$key] = ['count' => 0, 'first_attempt' => time()];
        return true;
    }
    
    return $attempts['count'] < $max_attempts;
}

/**
 * Record failed login attempt
 */
function recordFailedAttempt($identifier) {
    $key = 'login_attempts_' . $identifier;
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'first_attempt' => time()];
    }
    
    $_SESSION[$key]['count']++;
}

/**
 * Validate file upload
 */
function validateFileUpload($file, $allowed_types = ['jpg', 'jpeg', 'png', 'gif'], $max_size = 5242880) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_types)) {
        return false;
    }
    
    if ($file['size'] > $max_size) {
        return false;
    }
    
    return true;
}
?>