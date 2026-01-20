<?php

include $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
$errorMessages = [];

if (!isset($_SESSION)) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Register
    if (isset($_POST['register'])) {
        $name = $_POST['name'];
        $vorname = $_POST['vorname'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $email = $_POST['email'];
        $art = $_POST['art'];
        
        if (empty($name) || empty($vorname) || empty($username) || empty($password) || empty($email) || empty($art)) {
            $errorMessages = "Bitte füllen Sie alle Felder aus!"; //TODO Error Handling
        } else {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $conn->prepare("INSERT INTO Benutzer (name, vorname, username, password, email, art) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $name, $vorname, $username, $hashedPassword, $email, $art);

            if ($stmt->execute()) {
                echo "Benutzer erfolgreich registriert!";
            } else {
                echo "Fehler beim Registrieren: " . $stmt->error;
            }

            $stmt->close();
        }
    }
}

    // Logout
    if (isset($_POST['logout'])) {
        session_destroy();
        header("Location: " . $_SERVER['PHP_SELF']); // Reload site if logout
        exit();
    }



// Display error messages
if (!empty($errorMessages)) {
    echo "<div style='background-color: red'>";
    foreach ($errorMessages as $errorMessage) {
        echo "<p>$errorMessage</p>";
    }
    echo "</div>";
    die();
}
