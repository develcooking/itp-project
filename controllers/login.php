<?php

include $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";

if (!isset($_SESSION)){
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
        $password = $_POST['password']; //sanitize + esc_html

        // prepere SQL-query and execute it
        $stmt = $conn->prepare("SELECT passwd FROM users WHERE name = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($hashedPassword);
        $stmt->fetch();

        // check if the user exists and the password is valid
        if ($stmt->num_rows > 0 && password_verify($password, $hashedPassword)) {
            $_SESSION['user'] = $username; // login user

            // redirect to prevent repeted redirection
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            // Errorhandeling if the login was unsecessfull
            $error = "Invalid user name or password";
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
