<?php
require_once __DIR__ . '/../config/database.php';

// Create settings table if not exists
function createSettingsTable() {
    $conn = getDBConnection();
    $sql = "CREATE TABLE IF NOT EXISTS site_settings (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) NOT NULL UNIQUE,
        setting_value TEXT NOT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->query($sql);
    
    // Insert default settings
    $defaults = [
        'contact_email' => 'itclub@newcollege.edu.in',
        'contact_phone' => '+91 9876543210',
        'contact_address' => 'New IT College, Mumbai, Maharashtra, India',
        'site_description' => 'Fostering tech innovation and collaboration among students.',
        'footer_text' => '© 2025 IT Club, Computer Science Dept, New IT College. All rights reserved.',
        'instagram_url' => '#'
    ];
    
    foreach ($defaults as $key => $value) {
        $check = "SELECT * FROM site_settings WHERE setting_key = ?";
        $stmt = $conn->prepare($check);
        $stmt->bind_param("s", $key);
        $stmt->execute();
        if ($stmt->get_result()->num_rows == 0) {
            $insert = "INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)";
            $insert_stmt = $conn->prepare($insert);
            $insert_stmt->bind_param("ss", $key, $value);
            $insert_stmt->execute();
        }
    }
}

function getSetting($key, $default = '') {
    try {
        $conn = getDBConnection();
        createSettingsTable();
        
        $sql = "SELECT setting_value FROM site_settings WHERE setting_key = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $row['setting_value'];
        }
        return $default;
    } catch (Exception $e) {
        return $default;
    }
}

function updateSetting($key, $value) {
    try {
        $conn = getDBConnection();
        createSettingsTable();
        
        $sql = "INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $key, $value);
        return $stmt->execute();
    } catch (Exception $e) {
        return false;
    }
}

function getAllSettings() {
    try {
        $conn = getDBConnection();
        createSettingsTable();
        
        $sql = "SELECT setting_key, setting_value FROM site_settings";
        $result = $conn->query($sql);
        $settings = [];
        while ($row = $result->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    } catch (Exception $e) {
        return [];
    }
}
?>