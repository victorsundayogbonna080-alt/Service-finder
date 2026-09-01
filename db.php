<?php
// Database configuration for XAMPP default settings
$host     = 'localhost';
$username = 'root'; // Default XAMPP username
$password = '';     // Default XAMPP password is empty
$dbname   = 'fixit_direct'; // The database we just created in phpMyAdmin

// Enable error reporting for MySQLi (great for debugging)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Establish the connection
    $conn = new mysqli($host, $username, $password, $dbname);
    
    // Set charset to ensure emojis (like our icons) load correctly
    $conn->set_charset("utf8mb4");
    
} catch (mysqli_sql_exception $e) {
    // If the connection fails, stop the app and show a clean error
    die("Database Connection Failed: " . $e->getMessage());
}
?>