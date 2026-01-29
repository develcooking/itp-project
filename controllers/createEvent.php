<?php
include $_SERVER['DOCUMENT_ROOT'] . "/controllers/login.php";
$errorMessages = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // create Termin
    if (isset($_POST['createEvent'])) {
        $title = $_POST['title'];
        $createdBy = $_SESSION['userId'];
        $modifiedBy = $_SESSION['userId'];
        $startdate = $_POST['startdate'];
        $starttime = $_POST['starttime'];
        $enddate = $_POST['enddate'];
        $endtime = $_POST['endtime'];
        if (isset($_POST["description"])) {
            $description = $_POST["description"];
        } else {
            $description = '';
        }
        $jobId = $_POST['jobselection'];

        $startDateTime = $startdate . ' ' . $starttime . ':00'; // Ergebnis: "2023-10-27 14:30:00"
        $endDateTime = $enddate . ' ' . $endtime . ':00';

        if (empty($title) || empty($createdBy) || empty($startDateTime) || empty($endDateTime)) {
            http_response_code(400);
            array_push($errorMessages, "Bitte füllen Sie alle Felder aus!");
        } else {
            $stmt = $conn->prepare("INSERT INTO Appointments (title, start, end, description, createdBy, modifiedBy, jobId) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssiii", $title, $startDateTime, $endDateTime, $description, $createdBy, $modifiedBy, $jobId);

            if ($stmt->execute()) {
                http_response_code(response_code: 201);
                echo "Termin erfolgreich erstellt!";
            } else {
                http_response_code(500);
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