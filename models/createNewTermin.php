<?php
include $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
$errorMessages = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // create Termin
    if (isset($_POST['createTermin'])) {
        $Titel = $_POST['titel'];
        $UserID = $_POST['userid'];
        $Datum = $_POST['datum'];
        $Uhrzeit = $_POST['uhrzeit'];
        $Beschreibung = $_POST['beschreibung']
        
        if (empty($Titel) || empty($UserID) || empty($Datum) || empty($Uhrzeit) || empty($Beschreibung)) {
            array_push($errorMessages, "Bitte füllen Sie alle Felder aus!");    
        } else {

            $stmt = $conn->prepare("INSERT INTO Termin (Titel, Datum, Uhrzeit, Beschreibung, userid) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $Titel, $Datum, $Uhrzeit, $Beschreibung, $UserID);

            if ($stmt->execute()) {
                echo "Termin erfolgreich erstellt!";
            } else {
                echo "Fehler beim erstellen des Termins: " . $stmt->error;
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
