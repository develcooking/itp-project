<?php
header('Content-Type: application/json');

include $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";

$startParam = isset($_GET['start']) ? $_GET['start'] : null;
$endParam   = isset($_GET['end'])   ? $_GET['end']   : null;

session_start();
// Sicherstellen, dass userId gesetzt ist, sonst Abbruch
if (!isset($_SESSION['userId'])) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}
$userId = $_SESSION['userId'];

// 1. Jobs des Users holen
$sqlJobs = "SELECT jobId FROM users_jobs WHERE userId = ?";
$stmtJobs = $conn->prepare($sqlJobs);
$jobsOfUser = [];

if ($stmtJobs) {
    $stmtJobs->bind_param("i", $userId);
    $stmtJobs->execute();
    $resultJobs = $stmtJobs->get_result();
    while ($row = $resultJobs->fetch_assoc()) {
        $jobsOfUser[] = $row['jobId'];
    }
    $stmtJobs->close();
}

// check if user has no jobs if yes retun empty array to prevent wierd behavior
if (empty($jobsOfUser)) {
    echo json_encode([]);
    exit;
}

// we have to make the query dynamic with questionmarks: ?,?,?
$placeholders = implode(',', array_fill(0, count($jobsOfUser), '?'));

$sql = "SELECT appointmentId, title, start, end, description, jobId FROM Appointments 
        WHERE start >= ? AND end <= ? AND jobId IN ($placeholders)";

$stmt = $conn->prepare($sql);

if ($stmt) {
    // generate i's for every job the user has
    $types = "ss" . str_repeat("i", count($jobsOfUser));
    
    // merge parameters
    $params = array_merge([$startParam, $endParam], $jobsOfUser);
    
    // map the dynamc types and parameters via bind
    $stmt->bind_param($types, ...$params);
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = [
            'appointmentId'    => $row['appointmentId'],
            'title' => $row['title'],
            'start' => $row['start'],
            'end'   => $row['end'],
            'extendedProps' => [
                'description' => $row['description']
            ]
        ];
    }
    echo json_encode($events);
    $stmt->close();
} else {
    // Fallback bei SQL Fehler
    http_response_code(500);
    echo json_encode([]);
}

$conn->close();
?>