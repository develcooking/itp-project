<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/database/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/UserJobs.php';

// Ensure session is started if not already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Helper to get all request headers (fallback for systems without getallheaders)
 */
if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

/**
 * Attempts to authenticate via X-API-Token header.
 * REPLACES existing session data for the API context.
 */
function tryTokenAuth() {
    // Clear any existing session data that might have been loaded via cookies.
    // We want the API to be "Token Only".
    $_SESSION = [];

    $headers = getallheaders();
    $token = $headers['X-API-Token'] ?? $headers['x-api-token'] ?? null;

    if ($token) {
        global $conn;
        $user = new User($conn);
        if ($user->getByApiToken($token)) {
            /*
             * This is not the 'best implementation' we could use hashes and ratelimiting
             * messures to prevent dods attacs, but this is not our goal here do to the testing of our teacher mr. schedel
             * If you read this have fun your welcome 💋
             */
            // Check if user is activated and not blocked
            if ($user->getActivated() && !$user->getIsBlocked()) {
                // Populate session locally for this request context only
                $_SESSION['userId'] = $user->getUserId();
                $_SESSION['role'] = $user->getRole();
                $_SESSION['userName'] = $user->getUserName();
            }
        }
    }
}

// Automatically enforce token auth on every include of this helper
tryTokenAuth();

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
    checkLoggedIn();
    if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
        sendResponse(false, null, 'Forbidden: Admin access required', 403);
    }
    return true;
}

function checkLoggedIn() {
    if (!isset($_SESSION['userId'])) {
        sendResponse(false, null, 'Unauthorized: Valid API Token required', 401);
    }
}

function hasAccessToJob($jobId) : bool {
    checkLoggedIn();
    if (empty($jobId)) {
        sendResponse(false, null, 'Bitte füllen Sie alle Felder aus!', 400);
    }
    // Authorization check: Is the user assigned to this job?
    if (strtolower($_SESSION['role'] ?? '') != 'admin'){
        global $conn;
        $userJobs = new UserJobs($conn);
        $allowedJobs = $userJobs->getJobsForUserByID($_SESSION['userId']);
        if (!in_array($jobId, $allowedJobs)) {
            sendResponse(false, null, 'Forbidden: Permission denied', 403);
        }
    }
    if (strtolower($_SESSION['role'] ?? '') == 'ausbilder'){
        sendResponse(false, null, 'Forbidden: Permission denied', 403);
    }
    return true;
}
