<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . "/middleware/startSession.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/Appointment.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/Forum.php"; // For hasAccess method
require_once $_SERVER['DOCUMENT_ROOT'] . "/middleware/HtmlSanitizer.php";
require_once __DIR__ . "/api_helper.php";

$method = $_SERVER['REQUEST_METHOD'];
$appointment = new Appointment($conn);
$forumModel = new Forum($conn);
$userId = $_SESSION['userId'] ?? 0;

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            if ($appointment->getById(intval($_GET['id']))) {
                // Check access
                if (!$forumModel->hasAccess($userId, $appointment->getJobId()) && $_SESSION['role'] !== 'Admin') {
                    sendResponse(false, null, 'Forbidden', 403);
                }
                
                $data = [
                    'appointmentId' => $appointment->getAppointmentId(),
                    'jobId' => $appointment->getJobId(),
                    'title' => $appointment->getTitle(),
                    'start' => $appointment->getStart(),
                    'end' => $appointment->getEnd(),
                    'description' => $appointment->getDescription(),
                    'createdBy' => $appointment->getCreatedBy(),
                    'modifiedBy' => $appointment->getModifiedBy(),
                    'recurrenceType' => $appointment->getRecurrenceType(),
                    'recurrenceInterval' => $appointment->getRecurrenceInterval(),
                    'recurrenceUntil' => $appointment->getRecurrenceUntil()
                ];
                sendResponse(true, $data);
            } else {
                sendResponse(false, null, 'Appointment not found', 404);
            }
        } else {
            $start = $_GET['start'] ?? null;
            $end = $_GET['end'] ?? null;
            if ($_SESSION['role'] === 'Admin') {
                sendResponse(true, $appointment->getAll(null, $start, $end));
            } else {
                sendResponse(true, $appointment->getForUserJobs($userId));
            }
        }
        break;

    case 'POST':
        checkLoggedIn();
        $data = getJsonInput();
        if (!$data || !isset($data['title']) || !isset($data['jobId'])) {
            sendResponse(false, null, 'Invalid input or missing title/jobId', 400);
        }
        hasAccessToJob($data['jobId']);

        $appointment->setTitle($data['title'])
                    ->setJobId(intval($data['jobId']))
                    ->setStart($data['start'] ?? date('Y-m-d H:i:s'))
                    ->setEnd($data['end'] ?? date('Y-m-d H:i:s'))
                    ->setDescription(HtmlSanitizer::sanitize($data['description'] ?? ''))
                    ->setCreatedBy($_SESSION['userId'])
                    ->setModifiedBy($_SESSION['userId'])
                    ->setRecurrenceType($data['recurrenceType'] ?? 'none')
                    ->setRecurrenceInterval(intval($data['recurrenceInterval'] ?? 1))
                    ->setRecurrenceUntil($data['recurrenceUntil'] ?? null);

        if ($appointment->post()) {
            sendResponse(true, ['appointmentId' => $conn->insert_id], 'Appointment created successfully', 201);
        } else {
            sendResponse(false, null, 'Failed to create appointment', 500);
        }
        break;

    case 'PUT':
        checkLoggedIn();
        $data = getJsonInput();
        if (!$data || !isset($data['appointmentId'])) {
            sendResponse(false, null, 'Invalid input or missing appointmentId', 400);
        }

        $id = intval($data['appointmentId']);
        if (!$appointment->getById($id)) {
            sendResponse(false, null, 'Appointment not found', 404);
        }

        // JobId might not be in PUT data if only title changed
        $jobId = $data['jobId'] ?? $appointment->getJobId();
        hasAccessToJob($jobId);

        if (empty($_SESSION['userId'])) {
            sendResponse(false, null, 'Unauthorized: Login required', 401);
        }
        // Only admin or creator can update
        if ($_SESSION['role'] !== 'admin' && $_SESSION['userId'] !== $appointment->getCreatedBy()) {
            sendResponse(false, null, 'Unauthorized', 403);
        }

        if (isset($data['title'])) $appointment->setTitle($data['title']);
        if (isset($data['start'])) $appointment->setStart($data['start']);
        if (isset($data['end'])) $appointment->setEnd($data['end']);
        if (isset($data['description'])) $appointment->setDescription(HtmlSanitizer::sanitize($data['description']));
        if (isset($data['jobId'])) $appointment->setJobId(intval($data['jobId']));
        if (isset($data['recurrenceType'])) $appointment->setRecurrenceType($data['recurrenceType']);
        if (isset($data['recurrenceInterval'])) $appointment->setRecurrenceInterval(intval($data['recurrenceInterval']));
        if (isset($data['recurrenceUntil'])) $appointment->setRecurrenceUntil($data['recurrenceUntil']);

        if ($appointment->update($id)) {
            sendResponse(true, null, 'Appointment updated successfully', 204);
        } else {
            sendResponse(false, null, 'Failed to update appointment', 500);
        }
        break;

    case 'DELETE':
        checkLoggedIn();
        $data = getJsonInput();
        $id = $data['appointmentId'] ?? $_GET['id'] ?? null;
        
        if (!$id) sendResponse(false, null, 'Missing appointmentId', 400);

        if (!$appointment->getById(intval($id))) {
            sendResponse(false, null, 'Appointment not found', 404);
        }

        if (empty($_SESSION['userId'])) {
            sendResponse(false, null, 'Unauthorized: Login required', 401);
        }
        if ($_SESSION['role'] !== 'admin' && $_SESSION['userId'] !== $appointment->getCreatedBy()) {
            sendResponse(false, null, 'Unauthorized', 401);
        }

        if ($appointment->delete(intval($id))) {
            sendResponse(true, null, 'Appointment deleted successfully');
        } else {
            sendResponse(false, null, 'Failed to delete appointment', 500);
        }
        break;

    default:
        sendResponse(false, null, 'Method not allowed', 405);
        break;
}

$conn->close();
