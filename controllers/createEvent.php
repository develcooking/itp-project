<?php
include $_SERVER['DOCUMENT_ROOT'] . "/controllers/login.php";
require_once $homepath . "/models/Appointment.php";
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
            $appointmentmodel = new Appointment($conn);
            $appointmentmodel->setTitle($title);
            $appointmentmodel->setJobId($jobId);
            $appointmentmodel->setStart($startDateTime);
            $appointmentmodel->setEnd($endDateTime);
            $appointmentmodel->setDescription($description);
            $appointmentmodel->setCreatedBy($createdBy);
            $appointmentmodel->setModifiedBy($modifiedBy);
            

            if ($appointmentmodel->post()) {
                http_response_code(response_code: 201);
                echo "Termin erfolgreich erstellt!";
            } else {
                http_response_code(500);
                array_push($errorMessages, "Fehler beim Erstellen des Termins.");
            }
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