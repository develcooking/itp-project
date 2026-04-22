<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/middleware/startSession.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/controllers/login.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/Appointment.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/UserJobs.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/middleware/HtmlSanitizer.php";

if (!isset($_SESSION['userId'])) {
    http_response_code(401);
    die("Unauthorized");
}

$errorMessages = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form values
    $title = $_POST['changetitle'] ?? '';
    $userId = $_SESSION['userId'];
    $startdate = $_POST['changestartdate'] ?? '';
    $starttime = $_POST['changestarttime'] ?? '';
    $enddate = $_POST['changeenddate'] ?? '';
    $endtime = $_POST['changeendtime'] ?? '';
    $appointmentId = $_POST['changeappointmentId'] ?? '';
    $description = $_POST["changedescription"] ?? '';
    $jobId = $_POST['changejobselection'] ?? '';
    $recurrenceType = $_POST['changerecurrence_type'] ?? 'none';
    $recurrenceInterval = $_POST['changerecurrence_interval'] ?? 1;
    $recurrenceUntil = $_POST['changerecurrence_until'] ?? null;

    if (!is_numeric($jobId)) {
        die("Invalid job id");
    }
    if (!is_numeric($appointmentId)) {
        die("Invalid appointment id");
    }

    if (!in_array($recurrenceType, ['none', 'weekly', 'monthly'])) {
        http_response_code(400);
        die("Invalid recurrence type.");
    }

    if ($recurrenceType !== 'none') {
        if (!is_numeric($recurrenceInterval) || (int)$recurrenceInterval < 1 || $recurrenceInterval >= 24) {
            http_response_code(400);
            die("Invalid recurrence Interval.");
        }
        if (empty($recurrenceUntil)) {
            http_response_code(400);
            die("Recurrence end date is required for recurring events.");
        }
        $splitedRecurrenceEndDay = explode('-', $recurrenceUntil);
        if (count($splitedRecurrenceEndDay) !== 3 || !checkdate((int)$splitedRecurrenceEndDay[1], (int)$splitedRecurrenceEndDay[2], (int)$splitedRecurrenceEndDay[0])) {
            http_response_code(400);
            die("Invalid End Day for recurrence.");
        }
    } else {
        $recurrenceInterval = 1;
        $recurrenceUntil = null;
    }

    $appointmentmodel = new Appointment($conn);
    if (!$appointmentmodel->getById($appointmentId)) {
        http_response_code(404);
        die("Appointment not found");
    }

    if (strtolower($_SESSION['role']) != 'admin'){
        // Only the creator can change it, except admin
        if ($appointmentmodel->getCreatedBy() !== $userId) {
            http_response_code(403);
            die("You are not authorized to change this event. Only the creator can modify it.");
        }

        // Is user assigned to job
        $userJobs = new UserJobs($conn);
        $allowedJobs = $userJobs->getJobsForUserByID($userId);
        if (!in_array($jobId, $allowedJobs)) {
            http_response_code(403);
            die("You are not authorized for this professional area.");
        }
    }

    $startDateTime = $startdate . ' ' . $starttime . ':00';
    $endDateTime = $enddate . ' ' . $endtime . ':00';
    
    if (strtotime($startDateTime) < strtotime($endDateTime)) {
        if (empty($title) || empty($userId) || empty($startdate) || empty($starttime) || empty($enddate) || empty($endtime)) {
            http_response_code(400);
            array_push($errorMessages, "Bitte füllen Sie alle Felder aus!");
        } else {
            $appointmentmodel->setTitle(HtmlSanitizer::sanitize($title));
            $appointmentmodel->setJobId($jobId);
            $appointmentmodel->setStart($startDateTime);
            $appointmentmodel->setEnd($endDateTime);
            // Sanitize description
            $appointmentmodel->setDescription(HtmlSanitizer::sanitize($description));
            $appointmentmodel->setModifiedBy($userId);
            $appointmentmodel->setRecurrenceType($recurrenceType);
            $appointmentmodel->setRecurrenceInterval((int)$recurrenceInterval);
            $appointmentmodel->setRecurrenceUntil($recurrenceUntil);

            if ($appointmentmodel->update($appointmentId)) {
                header("Location: ../views/appointmentManagement.php");
                exit();
            } else {
                http_response_code(500);
                array_push($errorMessages, "Fehler beim Aktualisieren des Termins.");
            }
        }
    } else {
        $errorMessages[] = "Start date must be less than end date";
    }
}

// Display error messages
if (!empty($errorMessages)) {
    echo "<div style='background-color: red; padding:10px; color:white;'>";
    foreach ($errorMessages as $errorMessage) {
        echo "<p>" . HtmlSanitizer::escape($errorMessage) . "</p>";
    }
    echo "</div>";
    die();
}
