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
    $appointmentId = htmlspecialchars(trim($_POST['changeappointmentId']));
    if (!is_numeric($appointmentId)) {
        array_push($errorMessages, 'Appointment id must be numeric');
        die(json_encode($errorMessages));
    }

    $appointmentmodel = new Appointment($conn);
    // Authorization check: Has the user created this appointment?
    if (strtolower($_SESSION['role']) != 'admin'){
        if (!$appointmentmodel->getById($appointmentId) || $appointmentmodel->getCreatedBy() != $_SESSION['userId']) {
            array_push($errorMessages, 'You are not authorized to delete this appointment.');
            die(json_encode($errorMessages));
        }
    }
    // delete appointment by id
    if ($appointmentmodel->delete($appointmentId)) {
        header("Location: " . "../views/appointmentManagement.php");
    } else {
        http_response_code(500);
        array_push($errorMessages, "Fehler beim Löschen des Termins.");
    }
    $conn->close();

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
