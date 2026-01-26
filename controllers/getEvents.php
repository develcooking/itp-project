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

// WICHTIG: Wenn der User gar keine Jobs hat, sofort leeres Array zurückgeben.
// Sonst knallt der SQL-Befehl gleich bei "IN ()".
if (empty($jobsOfUser)) {
    echo json_encode([]);
    exit;
}

// 2. Termine abfragen
// Wir müssen dynamisch Fragezeichen generieren: ?,?,?
$placeholders = implode(',', array_fill(0, count($jobsOfUser), '?'));

// WICHTIG: 'jobId' im SELECT hinzufügen, da du es unten benutzt
$sql = "SELECT appointmentId, title, start, end, description, jobId FROM Appointments 
        WHERE start >= ? AND end <= ? AND jobId IN ($placeholders)";

$stmt = $conn->prepare($sql);

if ($stmt) {
    // Typen-String bauen: "ss" für start/end + "i" für jede JobID (z.B. "ssiii")
    $types = "ss" . str_repeat("i", count($jobsOfUser));
    
    // Parameter-Array zusammenführen: [start, end, jobID1, jobID2, ...]
    $params = array_merge([$startParam, $endParam], $jobsOfUser);
    
    // Dynamisches Binding mit dem Spread-Operator (...)
    // Das entpackt das Array in einzelne Argumente für bind_param
    $stmt->bind_param($types, ...$params);
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = [
            'appointmentId'    => $row['appointmentId'], // ID ist oft nützlich für Updates später
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
    echo json_encode([]);
}

$conn->close();
?>