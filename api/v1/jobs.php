<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . "/middleware/startSession.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/Job.php";
require_once __DIR__ . "/api_helper.php";

$method = $_SERVER['REQUEST_METHOD'];
$job = new Job($conn);

if (!is_numeric($_GET['id'])) {
    sendResponse(false, null, 'JobId is not numeric!', 400);
}
switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            if (hasAccessToJob($_GET['id'])) {
                if ($job->getById(intval($_GET['id']))) {
                    $data = [
                        'jobId' => $_GET['id'],
                        'name' => $job->getNameById(intval($_GET['id']))
                    ];
                    sendResponse(true, $data, '', 200);
                } else {
                    sendResponse(false, null, 'Job not found', 404);
                }
            }
        } else {
            if (checkAdmin()) {
                sendResponse(true, $job->getAll(), '', 200);
            } else {
                sendResponse(false, null, 'Forbidden: Admin access required', 403);
            }

        }
        break;

    case 'POST':
        checkAdmin();
        $data = getJsonInput();
        if (!$data || !isset($data['name'])) sendResponse(false, null, 'Invalid input or missing name', 400);

        $job->setJobName($data['name']);
        $job->setCreateBy($_SESSION['userId'] ?? 0);
        $job->setModifiedBy($_SESSION['userId'] ?? 0);

        if ($job->post()) {
            sendResponse(true, null, 'Job created successfully', 201);
        } else {
            sendResponse(false, null, 'Failed to create job', 500);
        }
        break;

    case 'PUT':
        checkAdmin();
        $data = getJsonInput();
        if (!$data || !isset($data['jobId']) || !isset($data['name'])) {
            sendResponse(false, null, 'Invalid input or missing jobId/name', 400);
        }

        if ($job->update(intval($data['jobId']), $data['name'])) {
            sendResponse(true, null, 'Job updated successfully', 204);
        } else {
            sendResponse(false, null, 'Failed to update job', 500);
        }
        break;

    case 'DELETE':
        checkAdmin();
        $data = getJsonInput();
        $id = $data['jobId'] ?? $_GET['id'] ?? null;
        
        if (!$id) sendResponse(false, null, 'Missing jobId', 400);

        if ($job->delete(intval($id))) {
            sendResponse(true, null, 'Job deleted successfully', 204);
        } else {
            sendResponse(false, null, 'Failed to delete job', 500);
        }
        break;

    default:
        sendResponse(false, null, 'Method not allowed', 405);
        break;
}

$conn->close();
