<?php
$homepath = $_SERVER['DOCUMENT_ROOT'];
error_reporting(E_ALL);
ini_set('display_errors', 1);

$site_name = "Pheelix";
// Setting the default config
$dbhost = "localhost"; #TODO muss angepasst werden
$dbuser = "mysqluser";
$dbpwd = "mypwd";
$dbname = "mydatabase";
$defaultlang = "en_US.UTF-8";

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