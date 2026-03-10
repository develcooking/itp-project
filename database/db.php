<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
$homepath = $_SERVER['DOCUMENT_ROOT'];

error_reporting(E_ALL);
ini_set('display_errors', 1);
$conn = null;

if (!($_ENV['DB_HOST'] && $_ENV['DB_USER'] && $_ENV['DB_PASS'] && $_ENV['DB_NAME'])) {
    die("ENV-NotSet");
}
try {
    $conn = new mysqli(
        $_ENV['DB_HOST'],
        $_ENV['DB_USER'],
        $_ENV['DB_PASS'],
        $_ENV['DB_NAME']
    );
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    
} catch (mysqli_sql_exception $e) {
    die("Database Error: " . $e->getMessage());
}