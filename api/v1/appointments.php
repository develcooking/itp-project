<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

include_once $_SERVER['DOCUMENT_ROOT'] . "/controllers/login.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/models/Appointment.php";
include_once __DIR__ . "/api_helper.php";

if (!isset($_SESSION)) session_start();

$method = $_SERVER['REQUEST_METHOD'];
$appointment = new Appointment($conn);

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            if ($appointment->getById(intval($_GET['id']))) {
                $data = [
                    'appointmentId' => $appointment->getAppointmentId(),
                    'jobId' => $appointment->getJobId(),
                    'title' => $appointment->getTitle(),
                    'start' => $appointment->getStart(),
                    'end' => $appointment->getEnd(),
                    'description' => $appointment->getDescription(),
                    'createdBy' => $appointment->getCreatedBy(),
                    'modifiedBy' => $appointment->getModifiedBy()
                ];
                sendResponse(true, $data);
            } else {
                sendResponse(false, null, 'Appointment not found', 404);
            }
        } else {
            sendResponse(true, $appointment->getAll());
        }
        break;

    case 'POST':
        checkLoggedIn();
        $data = getJsonInput();
        if (!$data || !isset($data['title']) || !isset($data['jobId'])) {
            sendResponse(false, null, 'Invalid input or missing title/jobId', 400);
        }

        $appointment->setTitle($data['title'])
                    ->setJobId(intval($data['jobId']))
                    ->setStart($data['start'] ?? date('Y-m-d H:i:s'))
                    ->setEnd($data['end'] ?? date('Y-m-d H:i:s'))
                    ->setDescription($data['description'] ?? '')
                    ->setCreatedBy($_SESSION['userId'])
                    ->setModifiedBy($_SESSION['userId']);

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

        // Only admin or creator can update
        if ($_SESSION['role'] !== 'admin' && $_SESSION['userId'] !== $appointment->getCreatedBy()) {
            sendResponse(false, null, 'Unauthorized', 403);
        }

        if (isset($data['title'])) $appointment->setTitle($data['title']);
        if (isset($data['start'])) $appointment->setStart($data['start']);
        if (isset($data['end'])) $appointment->setEnd($data['end']);
        if (isset($data['description'])) $appointment->setDescription($data['description']);

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

        if ($_SESSION['role'] !== 'admin' && $_SESSION['userId'] !== $appointment->getCreatedBy()) {
            sendResponse(false, null, 'Unauthorized', 403);
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
