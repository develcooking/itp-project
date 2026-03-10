<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

include_once $_SERVER['DOCUMENT_ROOT'] . "/controllers/login.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/models/User.php";
include_once __DIR__ . "/api_helper.php";

if (!isset($_SESSION)) session_start();

$method = $_SERVER['REQUEST_METHOD'];
$user = new User($conn);

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            if ($user->getById(intval($_GET['id']))) {
                sendResponse(true, $user->toArray());
            } else {
                sendResponse(false, null, 'User not found', 404);
            }
        } else {
            // For now, only admin can list all users
            checkAdmin();
            sendResponse(true, $user->getAll());
        }
        break;

    case 'POST':
        checkAdmin();
        $data = getJsonInput();
        if (!$data) sendResponse(false, null, 'Invalid JSON input', 400);

        $user->setUserName($data['userName'] ?? '')
             ->setFirstName($data['firstName'] ?? '')
             ->setLastName($data['lastName'] ?? '')
             ->setEmail($data['email'] ?? '')
             ->setPassword($data['password'] ?? '')
             ->setRole($data['role'] ?? 'user')
             ->setSecurityAnswer($data['securityAnswer'] ?? '')
             ->setActivated($data['activated'] ?? 1)
             ->setCreatedBy($_SESSION['userId'] ?? 0);

        if ($user->post()) {
            sendResponse(true, $user->toArray(), 'User created successfully', 201);
        } else {
            sendResponse(false, null, 'Failed to create user', 500);
        }
        break;

    case 'PUT':
        $data = getJsonInput();
        if (!$data || !isset($data['userId'])) sendResponse(false, null, 'Invalid input or missing userId', 400);
        
        $userId = intval($data['userId']);
        
        // Only admin or the user themselves can update
        if ($_SESSION['role'] !== 'admin' && $_SESSION['userId'] !== $userId) {
            sendResponse(false, null, 'Unauthorized', 403);
        }

        if (!$user->getById($userId)) sendResponse(false, null, 'User not found', 404);

        if (isset($data['userName'])) $user->setUserName($data['userName']);
        if (isset($data['firstName'])) $user->setFirstName($data['firstName']);
        if (isset($data['lastName'])) $user->setLastName($data['lastName']);
        if (isset($data['email'])) $user->setEmail($data['email']);
        if (isset($data['password'])) $user->setPassword($data['password']);
        if (isset($data['role']) && $_SESSION['role'] === 'admin') $user->setRole($data['role']);
        if (isset($data['securityAnswer'])) $user->setSecurityAnswer($data['securityAnswer']);
        if (isset($data['activated']) && $_SESSION['role'] === 'admin') $user->setActivated($data['activated']);

        if ($user->update($userId)) {
            sendResponse(true, null, 'User updated successfully');
        } else {
            sendResponse(false, null, 'Failed to update user', 500);
        }
        break;

    case 'DELETE':
        checkAdmin();
        $data = getJsonInput();
        $id = $data['userId'] ?? $_GET['id'] ?? null;
        
        if (!$id) sendResponse(false, null, 'Missing userId', 400);

        if ($user->delete(intval($id))) {
            sendResponse(true, null, 'User deleted successfully');
        } else {
            sendResponse(false, null, 'Failed to delete user', 500);
        }
        break;

    default:
        sendResponse(false, null, 'Method not allowed', 405);
        break;
}

$conn->close();
