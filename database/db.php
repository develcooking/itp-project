<?php
require_once __DIR__ . '/../vendor/autoload.php';

$homepath = $_SERVER['DOCUMENT_ROOT'];
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    ;
$conn = new mysqli(
    $_ENV['DB_HOST'],
    $_ENV['DB_USER'],
    $_ENV['DB_PASS'],
    $_ENV['DB_NAME']);
    
    if ($conn->connect_error) {
        throw new Exception("Connection with db faild: " . $conn->connect_error);
    }

} catch (mysqli_sql_exception $e) {
    die("Database Error: The Mariadb is properly down, or wasn't configured right, please ask a admin");
}