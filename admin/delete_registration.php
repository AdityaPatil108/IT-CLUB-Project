<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

require_once '../user/db_config.php';

// Process deletion request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = $_POST['id'];
    $delete_sql = "DELETE FROM event_registrations WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $id);
    
    if ($delete_stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Registration deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting registration']);
    }
    
    $delete_stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

$conn->close();
?>