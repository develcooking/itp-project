<?php
header('Content-Type: application/json');

include $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
require_once $homepath . "/models/UserJobs.php";
require_once $homepath . "/models/Job.php";
require_once $homepath . "/models/Appointment.php";

$startParam = isset($_GET['start']) ? $_GET['start'] : null;
$endParam   = isset($_GET['end'])   ? $_GET['end']   : null;

session_start();
if (!isset($_SESSION['userId'])) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}
$userId = $_SESSION['userId'];

// get jobs of user
$userJobsModel = new UserJobs($conn);
$jobsOfUser = $userJobsModel->getJobsForUserByID($userId);

// check if user has no jobs if yes retun empty array to prevent wierd behavior
if (empty($jobsOfUser) || $jobsOfUser == []) {
    echo json_encode([]);
    exit;
}

$appointmentmodel = new Appointment($conn);
$appointments = $appointmentmodel->getAll();

//var_dump($appointments);
$userJobIds = array_map('intval', $jobsOfUser);


$events = [];

foreach ($appointments as $row) {

    if ($startParam && $row['start'] < $startParam) {
        continue;
    }

    if ($endParam && $row['start'] > $endParam) {
        continue;
    }

    if (!in_array((int)$row['jobId'], $userJobIds, true)) {
        continue;
    }

    $events[] = [
        'id'            => (int)$row['appointmentId'],
        'appointmentId' => (int)$row['appointmentId'],
        'title'         => $row['title'],
        'start'         => $row['start'],
        'end'           => $row['end'],
        'extendedProps' => [
            'description' => $row['description'],
            'jobId'       => (int)$row['jobId'],
            'createdBy'   => (int)$row['createdBy'],
        ],
    ];
}
/* Debugging output
print("---event:\n");
var_dump($events);
print("---jobsOfUser:\n");
var_dump($jobsOfUser);
var_dump(array_keys($jobsOfUser[0] ?? []));
*/

// JSON ausgeben
http_response_code(200);
echo json_encode($events);

$conn->close();
?>