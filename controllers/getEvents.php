<?php
header('Content-Type: application/json');

require_once $_SERVER['DOCUMENT_ROOT'] . "/middleware/startSession.php";
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

    $recurrenceType = $row['recurrence_type'] ?? 'none';
    
    if ($recurrenceType === 'none') {
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
                'isRecurring' => false,
                'recurrenceType' => $row['recurrence_type'],
                'recurrenceInterval' => $row['recurrence_interval'],
                'recurrenceUntil' => $row['recurrence_until']
            ],
        ];
    } else {
        $interval = (int)($row['recurrence_interval'] ?? 1);
        $recurrenceUntil = $row['recurrence_until'] ? new DateTime($row['recurrence_until'] . ' 23:59:59') : null;
        
        $baseStart = new DateTime($row['start']);
        $baseEnd = new DateTime($row['end']);
        $duration = $baseStart->diff($baseEnd);
        
        $rangeStart = $startParam ? new DateTime($startParam) : new DateTime('1970-01-01');
        $rangeEnd = $endParam ? new DateTime($endParam) : new DateTime('2099-12-31');

        $currentStart = clone $baseStart;
        
        // Optimization: Jump to the start of the range if the event started long ago
        if ($currentStart < $rangeStart) {
            if ($recurrenceType === 'weekly') {
                $weeksDiff = floor($currentStart->diff($rangeStart)->days / 7);
                $jumpIntervals = floor($weeksDiff / $interval);
                if ($jumpIntervals > 0) {
                    $currentStart->modify("+" . ($jumpIntervals * $interval) . " weeks");
                }
            } elseif ($recurrenceType === 'monthly') {
                $monthsDiff = ($rangeStart->format('Y') - $currentStart->format('Y')) * 12 + ($rangeStart->format('m') - $currentStart->format('m'));
                $jumpIntervals = floor($monthsDiff / $interval);
                if ($jumpIntervals > 0) {
                    $currentStart->modify("+" . ($jumpIntervals * $interval) . " months");
                }
            }
            
            // Backtrack one interval to ensure we don't skip an event that overlaps the start of the range
            if ($recurrenceType === 'weekly') {
                $currentStart->modify("-$interval weeks");
            } elseif ($recurrenceType === 'monthly') {
                $currentStart->modify("-$interval months");
            }
            
            // Ensure we don't backtrack before the original start date
            if ($currentStart < $baseStart) {
                $currentStart = clone $baseStart;
            }
        }

        // Loop to generate instances
        $safetyCounter = 0;
        while ($currentStart <= $rangeEnd && (!$recurrenceUntil || $currentStart <= $recurrenceUntil) && $safetyCounter < 1000) {
            $safetyCounter++;
            
            $currentEnd = clone $currentStart;
            $currentEnd->add($duration);

            // Check if this instance falls within the visible range
            if ($currentEnd >= $rangeStart && $currentStart <= $rangeEnd) {
                $events[] = [
                    'id'            => $row['appointmentId'] . '_' . $currentStart->format('YmdHi'),
                    'appointmentId' => (int)$row['appointmentId'],
                    'title'         => $row['title'],
                    'start'         => $currentStart->format('Y-m-d H:i:s'),
                    'end'           => $currentEnd->format('Y-m-d H:i:s'),
                    'extendedProps' => [
                        'description' => $row['description'],
                        'jobId'       => (int)$row['jobId'],
                        'createdBy'   => (int)$row['createdBy'],
                        'creatorName' => $row['creatorName'],
                        'isRecurring' => true,
                        'appointmentId' => (int)$row['appointmentId'],
                        'recurrenceType' => $row['recurrence_type'],
                        'recurrenceInterval' => $row['recurrence_interval'],
                        'recurrenceUntil' => $row['recurrence_until']
                    ],
                ];
            }

            // Move to next occurrence
            if ($recurrenceType === 'weekly') {
                $currentStart->modify("+$interval weeks");
            } elseif ($recurrenceType === 'monthly') {
                $currentStart->modify("+$interval months");
            } else {
                break;
            }
        }
    }
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