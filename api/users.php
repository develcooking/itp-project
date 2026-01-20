<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

include __DIR__ . "/../database/db.php";
include __DIR__ . "/../models/User.php";

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $user = new User($conn);
    
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        
        if ($user->getById($id)) {
            echo json_encode([
                'success' => true,
                'data' => $user->toArray()
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'User not found'
            ], JSON_UNESCAPED_UNICODE);
        }
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Please provide id parameter'
        ], JSON_UNESCAPED_UNICODE);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();