<?php

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

function checkAdmin() {
    if (!isset($_SESSION)) session_start();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        sendResponse(false, null, 'Unauthorized: Admin access required', 403);
    }
}

function checkLoggedIn() {
    if (!isset($_SESSION)) session_start();
    if (!isset($_SESSION['userId'])) {
        sendResponse(false, null, 'Unauthorized: Login required', 403);
    }
}
