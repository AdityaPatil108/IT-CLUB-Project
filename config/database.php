<?php
// Secure database configuration
class DatabaseConfig {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        $servername = $_ENV['DB_HOST'] ?? 'localhost';
        $username = $_ENV['DB_USER'] ?? 'root';
        $password = $_ENV['DB_PASS'] ?? '';
        $dbname = $_ENV['DB_NAME'] ?? 'it_club_new';
        
        try {
            $this->connection = new mysqli($servername, $username, $password, $dbname);
            if ($this->connection->connect_error) {
                throw new Exception("Connection failed: " . $this->connection->connect_error);
            }
            $this->connection->set_charset("utf8");
        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
}

// Get database connection
function getDBConnection() {
    return DatabaseConfig::getInstance()->getConnection();
}
?>