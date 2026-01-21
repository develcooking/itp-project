<?php
include $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
$errorMessages = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // create Beitrag
    if (isset($_POST['createBeitrag'])) {
        $ThemaID = $_POST['ThemaID'];
        $UserID = $_POST['userid'];
        $Inhalt = $_POST['Inhalt'];
        $Beschreibung = $_POST['Beschreibung'];
        $Reaktion_Negativ = 0;
        $Reaktion_Positiv = 0; 

        if (empty($ThemaID) || empty($UserID) || empty($Inhalt) || empty($Beschreibung)) {
            array_push($errorMessages, "Bitte füllen Sie alle Felder aus!");
        } else {
            $stmt = $conn->prepare("INSERT INTO Beitraege (ThemaID, Inhalt, Beschreibung, BenutzerID, Reaktion_Negativ, Reaktion_Positiv) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issiii", $ThemaID, $Inhalt, $Beschreibung, $UserID, $Reaktion_Negativ, $Reaktion_Positiv);

            if ($stmt->execute()) {
                echo "Beitrag erfolgreich erstellt!";
            } else {
                echo "Fehler beim erstellen des Beitrags: " . $stmt->error;
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
