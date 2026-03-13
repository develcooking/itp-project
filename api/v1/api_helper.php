<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/UserJobs.php';
function sendResponse($success, $data = null, $message = null, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    
    $response = ['success' => $success];
    if ($data !== null) $response['data'] = $data;
    if ($message !== null) $response['message'] = $message;
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

function getJsonInput() {
    return json_decode(file_get_contents('php://input'), true);
}

function checkAdmin() : bool {
    if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
        sendResponse(false, null, 'Forbidden: Admin access required', 403);
    }
    return true;
}

function checkLoggedIn() {
    if (!isset($_SESSION['userId'])) {
        sendResponse(false, null, 'Forbidden: Login required', 403);
    }
}

function hasAccessToJob($jobId) : bool {
    if (empty($jobId)) {
        sendResponse(false, null, 'Bitte füllen Sie alle Felder aus!', 400);
    }
    // Authorization check: Is the user assigned to this job?
    if (strtolower($_SESSION['role']) != 'admin'){
        $userJobs = new UserJobs($conn);
        $allowedJobs = $userJobs->getJobsForUserByID($_SESSION['userId']);
        if (!in_array($jobId, $allowedJobs)) {
            sendResponse(false, null, 'Forbidden: Permission denied', 403);
        }
    }
    if (strtolower($_SESSION['role']) == 'ausbilder'){
        sendResponse(false, null, 'Forbidden: Permission denied', 403);
    }
    return true;
}
$conn->close();
