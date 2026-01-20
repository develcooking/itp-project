<?php
$homepath = $_SERVER['DOCUMENT_ROOT'];
error_reporting(E_ALL);
ini_set('display_errors', 1);

$site_name = "AusbildungsportalNet";
// Setting the default config
$dbhost = "127.0.0.1";
$dbuser = "user1";
$dbpwd = "SicheresPasswort123";
$dbname = "Database";

try {
    // Create a new mysqli instance
    $conn = new mysqli($dbhost, $dbuser, $dbpwd, $dbname);

    // Check for connection errors
    if ($conn->connect_error) {
        throw new Exception("Connection with db faild: " . $conn->connect_error);
    }

} catch (mysqli_sql_exception $e) {
    // Handle mysqli specific exceptions
    die("Database Error: The Mariadb is properly down, or wasn't configured right, please ask a admin");
}