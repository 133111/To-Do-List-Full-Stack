<?php
/**
 * Configuration File untuk MySQL Database
 */

// MySQL Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Ubah jika MySQL punya password
define('DB_NAME', 'todo_list');

// Password Hashing
define('PASSWORD_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_COST', 10);

// Session/Token Configuration
define('TOKEN_LENGTH', 32);
define('TOKEN_EXPIRY', 86400 * 7); // 7 hari

// App Configuration
define('APP_NAME', 'To-Do List Manager');
define('APP_VERSION', '1.0.0');
define('DEBUG_MODE', false);

// CORS Configuration
define('CORS_ORIGIN', '*');
define('CORS_METHODS', 'POST, GET, OPTIONS');
define('CORS_HEADERS', 'Content-Type, Authorization');

// Database Helper
class Database {
    private static $connection = null;

    public static function connect() {
        if (self::$connection === null) {
            self::$connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if (self::$connection->connect_error) {
                die(json_encode([
                    'success' => false,
                    'message' => 'Database Connection Failed: ' . self::$connection->connect_error
                ]));
            }
            
            self::$connection->set_charset('utf8mb4');
        }
        
        return self::$connection;
    }

    public static function getInstance() {
        return self::connect();
    }
}

// Error Handler
if (!DEBUG_MODE) {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
?>
