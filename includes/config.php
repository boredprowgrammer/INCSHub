<?php
/**
 * Database Configuration
 * Church News Hub - Secure database connection settings
 */

// Database configuration - Aiven MySQL Cloud
define('DB_HOST', 'neoera-proton-31c4.e.aivencloud.com');
define('DB_PORT', '23691');
define('DB_NAME', '17852Hub');
define('DB_USER', 'avnadmin');
define('DB_PASS', 'AVNS_d1Esbs4iMnGULVJLDdx');
define('DB_CHARSET', 'utf8mb4');

// Site configuration
define('SITE_URL', 'http://localhost/church-news-hub');
define('ADMIN_PATH', '/admin');
define('UPLOAD_PATH', '/uploads/images/');
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes

// Security settings
define('SECURE_KEY', 'your-secure-random-key-here'); // Change this!
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds

// Error reporting (disable in production)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

/**
 * Database connection class with prepared statements
 */
class Database {
    private $connection;
    
    public function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET,
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            ];
            
            // Enable SSL for Aiven cloud connection
            if (DB_HOST !== 'localhost' && DB_HOST !== '127.0.0.1') {
                $options[PDO::MYSQL_ATTR_SSL_CA] = true;
            }
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please check your configuration.");
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database query failed: " . $e->getMessage());
            return false;
        }
    }
    
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
}

// Create global database instance
$db = new Database();

// Include additional configuration files
require_once __DIR__ . '/upload_config.php';
?>