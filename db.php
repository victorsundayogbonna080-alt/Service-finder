<?php
// Railway & XAMPP compatible database connection
$host     = getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: 'localhost';
$username = getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_ROOT_PASSWORD') ?: '';
$dbname   = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'fixit_direct';
$port     = getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: '3306';

// Connect without relying on mysqli_report()
$conn = @new mysqli($host, $username, $password, $dbname, (int)$port);

// Check if connection failed
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>





























<!-- 
// Bulletproof environment variable matching for Railway & XAMPP
// $host     = getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: 'localhost';
// $username = getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: 'root';
// $password = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_ROOT_PASSWORD') ?: '';
// $dbname   = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'fixit_direct';
// $port     = getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: '3306';

// mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// try {
//     $conn = new mysqli($host, $username, $password, $dbname, $port);
//     $conn->set_charset("utf8mb4");
// } catch (mysqli_sql_exception $e) {
//     die("Database Connection Failed: " . $e->getMessage());
-->