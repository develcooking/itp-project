<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/middleware/startSession.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/Appointment.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/UserJobs.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/middleware/HtmlSanitizer.php";

if (!isset($_SESSION['userId'])) {
    http_response_code(401);
    die("Unauthorized");
}

// Role check: Only Admin and Lehrer can create events
$userRole = strtolower($_SESSION['role'] ?? '');
if ($userRole === 'ausbilder') {
    http_response_code(403);
    die("You are not authorized to create events.");
}

$errorMessages = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form values
    $title = $_POST['title'] ?? '';
    $createdBy = $_SESSION['userId'];
    $modifiedBy = $_SESSION['userId'];
    $startdate = $_POST['startdate'] ?? '';
    $starttime = $_POST['starttime'] ?? '';
    $enddate = $_POST['enddate'] ?? '';
    $endtime = $_POST['endtime'] ?? '';
    $jobId = $_POST['jobselection'] ?? '';
    $description = $_POST['description'] ?? '';

    // Authorization check: Is the user assigned to this job? (unless Admin)
    if ($userRole !== 'admin') {
        $userJobs = new UserJobs($conn);
        $allowedJobs = $userJobs->getJobsForUserByID($createdBy);
        if (!in_array($jobId, $allowedJobs)) {
            http_response_code(403);
            die("You are not authorized to create events for this professional area.");
        }
    }

    // Build datetime values
    // timestring: "2014-10-27 14:30:00"
    $startDateTime = $startdate . ' ' . $starttime . ':00';
    $endDateTime = $enddate . ' ' . $endtime . ':00';

    if (strtotime($startDateTime) < strtotime($endDateTime)) {
        if (empty($title) || empty($createdBy) || empty($startdate) || empty($starttime) || empty($enddate) || empty($endtime) || empty($jobId)) {
            http_response_code(400);
            array_push($errorMessages, "Bitte füllen Sie alle Felder aus!");
        } else {
            $appointmentmodel = new Appointment($conn);
            $appointmentmodel->setTitle($title);
            $appointmentmodel->setJobId($jobId);
            $appointmentmodel->setStart($startDateTime);
            $appointmentmodel->setEnd($endDateTime);
            // Sanitize description before saving
            $appointmentmodel->setDescription(HtmlSanitizer::sanitize($description));
            $appointmentmodel->setCreatedBy($createdBy);
            $appointmentmodel->setModifiedBy($modifiedBy);

            if ($appointmentmodel->post()) {
                header("Location: /views/appointmentManagement.php");
                exit();
            } else {
                http_response_code(500);
                array_push($errorMessages, "Fehler beim Erstellen des Termins.");
            }
        }
    } else {
        $errorMessages[] = "Start date must be less than end date";
    }
}

// Display error messages
if (!empty($errorMessages)) {
    echo "<div style='background-color:red; padding:10px; color:white;'>";
    foreach ($errorMessages as $errorMessage) {
        // Use escape for plain text error output
        echo "<p>" . HtmlSanitizer::escape($errorMessage) . "</p>";
    }
    echo "</div>";
    die();
}
?>
