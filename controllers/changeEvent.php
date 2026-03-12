<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/controllers/login.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/Appointment.php";
$errorMessages = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // create Termin

    $title = htmlspecialchars($_POST['changetitle']);
    $createdBy = $_SESSION['userId'];
    $modifiedBy = $_SESSION['userId'];
    $startdate = htmlspecialchars($_POST['changestartdate']);
    $starttime = htmlspecialchars($_POST['changestarttime']);
    $enddate = htmlspecialchars($_POST['changeenddate']);
    $endtime = htmlspecialchars($_POST['changeendtime']);
    $appointmentId = htmlspecialchars($_POST['changeappointmentId']);
    if (isset($_POST["changedescription"])) {
        $description = htmlspecialchars($_POST["changedescription"]);
    } else {
        $description = '';
    }
    $jobId = htmlspecialchars($_POST['changejobselection']);
    if (!is_numeric($jobId)) {
        die("Invalid job id");
    }
    if (!is_numeric($appointmentId)) {
        die("Invalid job id");
    } else {

    }

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


        if ($appointmentmodel->update($appointmentId)) {
            http_response_code(response_code: 201);
            #echo "Termin erfolgreich erstellt!";
            header("Location: " . "../views/appointmentManagement.php");
        } else {
            http_response_code(500);
            array_push($errorMessages, "Fehler beim Erstellen des Termins.");
        }
        $conn->close();
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