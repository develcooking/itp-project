<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/controllers/login.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/Appointment.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/UserJobs.php";

if (!isset($_SESSION['userId'])) {
    http_response_code(401);
    die("Unauthorized");
}

$errorMessages = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form values
    $title = $_POST['title'];
    $createdBy = $_SESSION['userId'];
    $modifiedBy = $_SESSION['userId'];
    $startdate = $_POST['startdate'];
    $starttime = $_POST['starttime'];
    $enddate = $_POST['enddate'];
    $endtime = $_POST['endtime'];
    $jobId = $_POST['jobselection'];

    if (isset($_POST["description"])) {
        $description = $_POST["description"];
    } else {
        $description = '';
    }
    $jobId = $_POST['jobselection'];

    // Authorization check: Is the user assigned to this job?
    if (strtolower($_SESSION['role']) != 'admin'){
        $userJobs = new UserJobs($conn);
        $allowedJobs = $userJobs->getJobsForUserByID($createdBy);
        if (!in_array($jobId, $allowedJobs)) {
            http_response_code(403);
            die("You are not authorized to create events for this professional area.");
        }
    }
    // Build datetime values
    $startDateTime = $startdate . ' ' . $starttime . ':00'; // Ergebnis: "2023-10-27 14:30:00"
    $endDateTime = $enddate . ' ' . $endtime . ':00';

    if (strtotime($startDateTime) < strtotime($endDateTime)) {
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
    echo "<div style='background-color:red; padding:10px; color:white;'>";
    foreach ($errorMessages as $errorMessage) {
        echo "<p>$errorMessage</p>";
    }
    echo "</div>";
    die();
}
?>