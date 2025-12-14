<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

require_once '../config/database.php';
require_once '../includes/security.php';

$conn = getDBConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = validateInteger($_POST['id']);
    
    if ($id === false || $id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        exit;
    }
    
    $delete_sql = "DELETE FROM event_queries WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $id);
    
    if ($delete_stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    
    $delete_stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

$conn->close();
?>