<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "it_club_new";

// Create connection without database first
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Create database if not exists
$conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
$conn->select_db($dbname);

// Create events table if not exists
$sql = "CREATE TABLE IF NOT EXISTS events (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    event_date DATETIME NOT NULL,
    location VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    image VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) !== TRUE) {
    error_log("Error creating events table: " . $conn->error);
    throw new Exception("Events table creation failed");
}

// Create users table if not exists
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role ENUM('faculty', 'member') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) !== TRUE) {
    error_log("Error creating users table: " . $conn->error);
    throw new Exception("Users table creation failed");
}

// Create event_queries table if not exists
$sql = "CREATE TABLE IF NOT EXISTS event_queries (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    event_id INT(11) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    query TEXT NOT NULL,
    status ENUM('pending', 'answered') DEFAULT 'pending',
    response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
)";

if ($conn->query($sql) !== TRUE) {
    error_log("Error creating event_queries table: " . $conn->error);
    throw new Exception("Event queries table creation failed");
}

// Create event_feedback table if not exists
$sql = "CREATE TABLE IF NOT EXISTS event_feedback (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    event_id INT(11) NOT NULL,
    user_name VARCHAR(100) NOT NULL,
    user_email VARCHAR(100) NOT NULL,
    rating INT(1) NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
)";

if ($conn->query($sql) !== TRUE) {
    error_log("Error creating event_feedback table: " . $conn->error);
}

// Insert default admin user if not exists
$check_user = "SELECT * FROM users WHERE username = 'admin'";
$result = $conn->query($check_user);
if ($result->num_rows == 0) {
    $default_password = password_hash('admin123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password, email, role) VALUES ('admin', '$default_password', 'admin@itclub.edu', 'faculty')";
    if ($conn->query($sql) !== TRUE) {
        echo "Error creating default user: " . $conn->error;
    }
}
?>