<?php

include $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";

if (!isset($_SESSION)) {
    session_start();
}

// check the logininfo
$error = '';
$rolle = ''; // init vars for admin-status
$username = isset($_SESSION['user']) ? $_SESSION['user'] : null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Login
    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // 1. Corrected column name and removed trailing comma
        $stmt = $conn->prepare("SELECT passwort_hash, art FROM Benutzer WHERE name = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($hashedPassword, $fetchedRole);
        $stmt->fetch();

        if ($stmt->num_rows > 0 && password_verify($password, $hashedPassword)) {
            session_regenerate_id(true);
            $_SESSION['user'] = $username;
            $_SESSION['rolle'] = $fetchedRole; // Store the role from the 'art' column

            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            // 2. Use the array that you check at the bottom
            $errorMessages[] = "Invalid user name or password";
        }
        $stmt->close();
    }

    // Logout
    if (isset($_POST['logout'])) {
        session_destroy();
        header("Location: " . $_SERVER['PHP_SELF']); // Reload site if logout
        exit();
    }
}

$errorMessages = [];

// Display error messages
if (!empty($errorMessages)) {
    echo "<div style='background-color: red'>";
    foreach ($errorMessages as $errorMessage) {
        echo "<p>$errorMessage</p>";
    }
    echo "</div>";
    die();
}
