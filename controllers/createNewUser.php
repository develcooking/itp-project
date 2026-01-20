<?php
include $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
$errorMessages = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Register
    if (isset($_POST['createAccount'])) {
        $nachName = $_POST['nachname'];
        $vorName = $_POST['vorname'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $email = $_POST['email'];
        $art = $_POST['art'];
        
        if (empty($nachName) || empty($vorName) || empty($username) || empty($password) || empty($email) || empty($art)) {
            array_push($errorMessages, "Bitte füllen Sie alle Felder aus!");
        } else {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $conn->prepare("INSERT INTO Benutzer (name, vorname, username, password, email, art) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $nachName, $vorName, $username, $hashedPassword, $email, $art);

            if ($stmt->execute()) {
                echo "Benutzer erfolgreich registriert!";
            } else {
                echo "Fehler beim Registrieren: " . $stmt->error;
            }

            $stmt->close();
            $conn->close();
        }
    }
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
