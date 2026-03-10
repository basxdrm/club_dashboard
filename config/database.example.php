<?php
/**
 * Database Configuration — Example File
 * 
 * คัดลอกไฟล์นี้เป็นชื่อ database.php แล้วใส่ค่าจริงของคุณ:
 *   cp config/database.example.php config/database.php
 */

defined('APP_ACCESS') or die('Direct access not permitted');

date_default_timezone_set('Asia/Bangkok');

// ==============================
// แก้ไขค่าด้านล่างให้ตรงกับ server ของคุณ
// ==============================
define('DB_HOST', 'localhost');       // Database host
define('DB_PORT', '3306');            // Database port
define('DB_NAME', 'your_db_name');    // ชื่อ database
define('DB_USER', 'your_db_user');    // ชื่อ user
define('DB_PASS', 'your_db_password'); // รหัสผ่าน
define('DB_CHARSET', 'utf8mb4');

/**
 * Database Class
 */
class Database {
    private $connection;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => false
            ];

            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);

        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            die("เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล");
        }
    }

    public function getConnection() {
        return $this->connection;
    }
}

function getDatabaseConnection() {
    $db = new Database();
    return $db->getConnection();
}
