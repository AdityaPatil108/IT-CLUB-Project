<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: ../user/login.php");
    exit;
}

require_once '../user/db_config.php';

// Check if ID parameter exists
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("location: manage_events.php");
    exit;
}

$id = $_GET['id'];

// Get event details to find the image path
$sql = "SELECT image FROM events WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $event = $result->fetch_assoc();
    $image_path = realpath("../" . $event['image']);
    if ($image_path === false || !file_exists($image_path)) {
        $image_path = null;
    }
    
    // Delete the event from database
    $delete_sql = "DELETE FROM events WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $id);
    
    if ($delete_stmt->execute()) {
        // Try to delete the image file if it exists
        if ($image_path && file_exists($image_path)) {
            unlink($image_path);
        }
        
        // Redirect to manage events page with success message
        header("location: manage_events.php?success=delete");
    } else {
        // If deletion failed, redirect back with error
        header("location: manage_events.php?error=delete");
    }
    
    $delete_stmt->close();
} else {
    // If event not found, redirect back
    header("location: manage_events.php");
}

$stmt->close();
$conn->close();
?>