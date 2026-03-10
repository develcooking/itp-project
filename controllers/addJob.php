<?php
include $_SERVER['DOCUMENT_ROOT'] . "/controllers/login.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/Job.php";

$errorMessages = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['role'] == '0') {
    // create Job (Berufsbereich)
    $name = $_POST['name'];
    $createdBy = $_SESSION['userId'];
    $modifiedBy = $_SESSION['userId'];

    if (empty($name) || empty($createdBy) || empty($modifiedBy)) {
        http_response_code(400);
        array_push($errorMessages, "Bitte füllen Sie alle Felder aus!");
    } else {
        $job = new Job($conn);
        $job->setJobName($name);
        $job->setCreateBy($createdBy);
        $job->setModifiedBy($modifiedBy);
        if ($job->post()) {
            http_response_code(201);
        } else {
            http_response_code(500);
            array_push($errorMessages, "Fehler beim Erstellen des Berufsbereiches.");
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

header("Location: ". $_SERVER['DOCUMENT_ROOT'] . "/views/addJob.php");