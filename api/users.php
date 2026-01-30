<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

session_start();

include __DIR__ . "/../database/db.php";
include __DIR__ . "/../models/User.php";

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'toggleActivated') {
        $userId = intval($_POST['userId'] ?? 0);
        $activated = intval($_POST['activated'] ?? 0);

        if ($userId > 0) {
            $user = new User($conn);
            if ($user->getById($userId)) {
                $user->setActivated($activated);
                if ($user->update($userId)) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'User status updated'
                    ], JSON_UNESCAPED_UNICODE);
                } else {
                    http_response_code(500);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Failed to update user'
                    ], JSON_UNESCAPED_UNICODE);
                }
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
                'message' => 'Invalid user ID'
            ], JSON_UNESCAPED_UNICODE);
        }
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ], JSON_UNESCAPED_UNICODE);
    }
} elseif ($method === 'GET') {
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
    } elseif (isset($_GET['all'])) {
        if ($user->getById($_SESSION['userId']) -> role == 'admin') {
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