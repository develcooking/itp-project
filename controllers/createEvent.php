<?php
include $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
$errorMessages = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // create Termin
    if (isset($_POST['createEvent'])) {
        $title = $_POST['title'];
        $createdBy = $_SESSION['userId'];
        $modifiedBy = $_SESSION['userId'];
        $date1 = $_POST['date1'];
        $time1 = $_POST['time1'];
        $date2 = $_POST['date2'];
        $time2 = $_POST['time2'];
        $description = $_POST['description'];
        $jobId = $_POST['jobId'];

        $startDateTime = $date1 . ' ' . $time1 . ':00'; // Ergebnis: "2023-10-27 14:30:00"
        $endDateTime   = $date2 . ' ' . $time2 . ':00';
        
        if (empty($title) || empty($createdBy) || empty($startDateTime) || empty($endDateTime) || empty($Beschreibung)) {
            array_push($errorMessages, "Bitte füllen Sie alle Felder aus!");    
        } else {

            $stmt = $conn->prepare("INSERT INTO Appointments (title, start, end, description, createdBy, modifiedBy, jobId) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssiii", $title, $startDateTime, $endDateTime, $description, $createdBy, $modifiedBy ,$jobId);

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