<?php
/**
 * Database Configuration File
 * Establishes a global connection object for MySQL.
 */

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'cv_database');

// Enable MySQLi error reporting
mysqli_report(MYSQLI_REPORT_OFF);

// Attempt to connect to MySQL database
$conn = @new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    // If database doesn't exist, attempt connection without database to show helpful instructions
    $server_conn = @new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD);
    if ($server_conn->connect_error) {
        $db_error_message = "Tidak dapat terhubung ke MySQL server. Pastikan MySQL (XAMPP/WAMP) sudah berjalan. Error: " . $server_conn->connect_error;
    } else {
        $db_error_message = "Database '" . DB_NAME . "' tidak ditemukan. Silakan buat database '" . DB_NAME . "' di phpMyAdmin dan impor file `database.sql`.";
    }
} else {
    $conn->set_charset("utf8mb4");
}

/**
 * Utility function to close the database connection.
 */
function closeDB($connection) {
    if ($connection && $connection instanceof mysqli) {
        $connection->close();
    }
}

// Global connection object
global $db;
$db = ($conn && !$conn->connect_error) ? $conn : null;
?>