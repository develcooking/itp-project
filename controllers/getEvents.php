<?php
header('Content-Type: application/json');

include $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
require_once $homepath . "/models/UserJobs.php";
require_once $homepath . "/models/Job.php";
require_once $homepath . "/models/Appointment.php";

$startParam = isset($_GET['start']) && $_GET['start'] !== '' ? date('Y-m-d H:i:s', strtotime($_GET['start'])) : null;
$endParam   = isset($_GET['end'])   && $_GET['end']   !== '' ? date('Y-m-d H:i:s', strtotime($_GET['end']))   : null;

if (isset($_GET['filterStart']) && $_GET['filterStart'] !== '') {
    $fs = date('Y-m-d H:i:s', strtotime($_GET['filterStart'] . ' 00:00:00'));
    if (!$startParam || $fs > $startParam) {
        $startParam = $fs;
    }
}
if (isset($_GET['filterEnd']) && $_GET['filterEnd'] !== '') {
    $fe = date('Y-m-d H:i:s', strtotime($_GET['filterEnd'] . ' 23:59:59'));
    if (!$endParam || $fe < $endParam) {
        $endParam = $fe;
    }
}

$filterJobId = isset($_GET['jobId']) && $_GET['jobId'] !== '' ? (int)$_GET['jobId'] : null;

session_start();
if (!isset($_SESSION['userId'])) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}
$userId = $_SESSION['userId'];
$isAdmin = isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin';

// get jobs of user
$userJobsModel = new UserJobs($conn);
$jobsOfUser = $userJobsModel->getJobsForUserByID($userId);

// check if user has no jobs if yes retun empty array to prevent wierd behavior
// Admins can see everything, so they don't need assigned jobs
if (empty($jobsOfUser) && !$isAdmin) {
    echo json_encode([]);
    exit;
}

$userJobIds = array_map('intval', $jobsOfUser);

$appointmentmodel = new Appointment($conn);
// Fetch appointments with initial filtering in SQL
$appointments = $appointmentmodel->getAll($filterJobId, $startParam, $endParam);

$events = [];

foreach ($appointments as $row) {
    // Only jobs user is assignd to (Admins see all)
    if (!$isAdmin && !in_array((int)$row['jobId'], $userJobIds, true)) {
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
            'creatorName' => $row['creatorName'],
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