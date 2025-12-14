<?php
/**
 * Password Migration Script
 * Run this once to convert any plain text passwords to hashed passwords
 */

require_once '../user/db_config.php';

try {
    // Get all users with potentially plain text passwords
    $sql = "SELECT id, username, password FROM users";
    $result = $conn->query($sql);
    
    $updated_count = 0;
    
    while ($row = $result->fetch_assoc()) {
        // Check if password is already hashed (bcrypt hashes start with $2y$)
        if (!password_get_info($row['password'])['algo']) {
            // This is likely a plain text password, hash it
            $hashed_password = password_hash($row['password'], PASSWORD_DEFAULT);
            
            $update_sql = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($update_sql);
            
            if ($stmt) {
                $stmt->bind_param("si", $hashed_password, $row['id']);
                if ($stmt->execute()) {
                    $updated_count++;
                    echo "Updated password for user: " . htmlspecialchars($row['username']) . "\n";
                }
                $stmt->close();
            }
        }
    }
    
    echo "Migration completed. Updated $updated_count passwords.\n";
    
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}

$conn->close();
?>