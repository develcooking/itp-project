<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Current File: " . __FILE__ . "<br>";
echo "Dir: " . __DIR__ . "<br>";

echo "<br>Checking files:<br>";
echo "db.php exists: " . (file_exists($_SERVER['DOCUMENT_ROOT'] . "/database/db.php") ? "YES" : "NO") . "<br>";
echo "User.php exists: " . (file_exists($_SERVER['DOCUMENT_ROOT'] . "/models/User.php") ? "YES" : "NO") . "<br>";

echo "<br>ENV variables:<br>";
var_dump($_ENV);