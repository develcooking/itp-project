<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/middleware/startSession.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/controllers/login.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/Appointment.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/UserJobs.php";

if (!isset($_SESSION['userId'])) {
    http_response_code(401);
    die("Unauthorized");
}

$errorMessages = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // create Termin

    $title = htmlspecialchars($_POST['changetitle']);
    $userId = $_SESSION['userId'];
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
        die("Invalid appointment id");
    }

    $appointmentmodel = new Appointment($conn);
    if (!$appointmentmodel->getById($appointmentId)) {
        http_response_code(404);
        die("Appointment not found");
    }

    if (strtolower($_SESSION['role']) != 'admin'){
        // Only the creator can change it, exapt admin
        if ($appointmentmodel->getCreatedBy() !== $userId) {
            http_response_code(403);
            die("You are not authorized to change this event. Only the creator can modify it.");
        }

        // Is  user assigned to job
        $userJobs = new UserJobs($conn);
        $allowedJobs = $userJobs->getJobsForUserByID($userId);
        if (!in_array($jobId, $allowedJobs)) {
            http_response_code(403);
            die("You are not authorized for this professional area.");
        }
    }

    $startDateTime = $startdate . ' ' . $starttime . ':00'; // Ergebnis: "2023-10-27 14:30:00"
    $endDateTime = $enddate . ' ' . $endtime . ':00';
    if (strtotime($startDateTime) < strtotime($endDateTime)) {


        if (empty($title) || empty($userId) || empty($startDateTime) || empty($endDateTime)) {
            http_response_code(400);
            array_push($errorMessages, "Bitte füllen Sie alle Felder aus!");
        } else {
            $appointmentmodel->setTitle($title);
            $appointmentmodel->setJobId($jobId);
            $appointmentmodel->setStart($startDateTime);
            $appointmentmodel->setEnd($endDateTime);
            $appointmentmodel->setDescription($description);
            $appointmentmodel->setModifiedBy($userId);

            if ($appointmentmodel->update($appointmentId)) {
                #echo "Termin erfolgreich erstellt!";
                header("Location: " . "../views/appointmentManagement.php");
            } else {
                http_response_code(500);
                array_push($errorMessages, "Fehler beim Erstellen des Termins.");
            }
            $conn->close();
        }
    } else {
        $errorMessages[] = "Start date must be less than end date";
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
