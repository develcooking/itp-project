<?php
include $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
$errorMessages = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // create Thema
    if (isset($_POST['createThema'])) {
        $Name = $_POST['name'];
        $UserID = $_POST['userid'];
        $BerufsbereichID = $_POST['BerufsbereichID'];
        
        if (empty($Name) || empty($UserID) || empty($BerufsbereichID)) {
            array_push($errorMessages, "Bitte füllen Sie alle Felder aus!");
        } else {

            $stmt = $conn->prepare("INSERT INTO Thema (Name, BerufsbereichID, BenutzerID) VALUES (?, ?, ?)");
            $stmt->bind_param("sii", $Name, $UserID, $BerufsbereichID);

            if ($stmt->execute()) {
                echo "Thema erfolgreich erstellt!";
            } else {
                echo "Fehler beim erstellen des Themas: " . $stmt->error;
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
