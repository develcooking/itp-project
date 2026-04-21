<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/Job.php";
require_once __DIR__ . "/api_helper.php";

$method = $_SERVER['REQUEST_METHOD'];
$job = new Job($conn);

switch ($method) {
    case 'GET':
        checkLoggedIn();
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
        if (!$data || empty($data['name'])) sendResponse(false, null, 'Missing name', 400);

        $job->setJobName(HtmlSanitizer::sanitize($data['name']));
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
        if (!$data || !isset($data['jobId']) || empty($data['name'])) {
            sendResponse(false, null, 'Missing jobId or name', 400);
        }

        $jobId = intval($data['jobId']);
        if ($jobId <= 0) sendResponse(false, null, 'Invalid jobId', 400);

        if ($job->update($jobId, HtmlSanitizer::sanitize($data['name']))) {
            sendResponse(true, null, 'Job updated successfully');
        } else {
            sendResponse(false, null, 'Failed to update job', 500);
        }
        break;

    case 'DELETE':
        checkAdmin();
        $data = getJsonInput();
        $id = intval($data['jobId'] ?? $_GET['id'] ?? 0);
        
        if ($id <= 0) sendResponse(false, null, 'Invalid jobId', 400);

        if ($job->delete($id)) {
            sendResponse(true, null, 'Job deleted successfully');
        } else {
            sendResponse(false, null, 'Failed to delete job', 500);
        }
        break;

    default:
        sendResponse(false, null, 'Method not allowed', 405);
        break;
}

$conn->close();
