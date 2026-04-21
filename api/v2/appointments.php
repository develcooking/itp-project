<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit;
}

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
        checkLoggedIn();
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
        
        // 1. Basic Presence Validation
        if (!$data || !isset($data['title']) || !isset($data['jobId'])) {
            sendResponse(false, null, 'Missing required fields: title, jobId', 400);
        }

        // 2. Type & Value Validation
        $jobId = intval($data['jobId']);
        if ($jobId <= 0) {
            sendResponse(false, null, 'Invalid jobId', 400);
        }

        // 3. Authorization
        hasAccessToJob($jobId);

        // 4. Sanitize Strings (Against XSS)
        $title = HtmlSanitizer::sanitize($data['title'] ?? '');
        $description = HtmlSanitizer::sanitize($data['description'] ?? '');
        
        // 5. Whitelist Enum values (Against malicious logic)
        $recurrenceType = $data['recurrenceType'] ?? 'none';
        if (!in_array($recurrenceType, ['none', 'weekly', 'monthly'])) {
            $recurrenceType = 'none';
        }

        $interval = intval($data['recurrenceInterval'] ?? 1);
        if ($interval < 1 || $interval > 24) $interval = 1;

        $appointment->setTitle($title)
                    ->setJobId($jobId)
                    ->setStart($data['start'] ?? date('Y-m-d H:i:s'))
                    ->setEnd($data['end'] ?? date('Y-m-d H:i:s'))
                    ->setDescription($description)
                    ->setCreatedBy($_SESSION['userId'])
                    ->setModifiedBy($_SESSION['userId'])
                    ->setRecurrenceType($recurrenceType)
                    ->setRecurrenceInterval($interval)
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
            sendResponse(false, null, 'Missing appointmentId', 400);
        }

        $id = intval($data['appointmentId']);
        if ($id <= 0 || !$appointment->getById($id)) {
            sendResponse(false, null, 'Appointment not found', 404);
        }

        // Authorization: Only admin or creator
        if (strtolower($_SESSION['role']) !== 'admin' && $_SESSION['userId'] !== $appointment->getCreatedBy()) {
            sendResponse(false, null, 'Unauthorized to modify this appointment', 403);
        }

        if (isset($data['jobId'])) {
            $jobId = intval($data['jobId']);
            if ($jobId > 0) {
                hasAccessToJob($jobId);
                $appointment->setJobId($jobId);
            }
        }

        if (isset($data['title'])) $appointment->setTitle(HtmlSanitizer::sanitize($data['title']));
        if (isset($data['description'])) $appointment->setDescription(HtmlSanitizer::sanitize($data['description']));
        if (isset($data['start'])) $appointment->setStart($data['start']);
        if (isset($data['end'])) $appointment->setEnd($data['end']);
        
        if (isset($data['recurrenceType'])) {
            if (in_array($data['recurrenceType'], ['none', 'weekly', 'monthly'])) {
                $appointment->setRecurrenceType($data['recurrenceType']);
            }
        }
        if (isset($data['recurrenceInterval'])) {
            $interval = intval($data['recurrenceInterval']);
            if ($interval >= 1 && $interval <= 24) {
                $appointment->setRecurrenceInterval($interval);
            }
        }
        if (isset($data['recurrenceUntil'])) $appointment->setRecurrenceUntil($data['recurrenceUntil']);

        if ($appointment->update($id)) {
            sendResponse(true, null, 'Appointment updated successfully');
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
