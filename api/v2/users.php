<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/User.php";
require_once __DIR__ . "/api_helper.php";

$method = $_SERVER['REQUEST_METHOD'];
$user = new User($conn);

// Only Admins can use this api endpoint
checkAdmin();
switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            if ($user->getById(intval($_GET['id']))) {
                sendResponse(true, $user->toArray());
            } else {
                sendResponse(false, null, 'User not found', 404);
            }
        } else {
            sendResponse(true, $user->getAll());
        }
        break;

    case 'POST':
        $data = getJsonInput();
        if (!$data) sendResponse(false, null, 'Invalid JSON input', 400);

        $user->setUserName(HtmlSanitizer::sanitize($data['userName'] ?? ''))
             ->setFirstName(HtmlSanitizer::sanitize($data['firstName'] ?? ''))
             ->setLastName(HtmlSanitizer::sanitize($data['lastName'] ?? ''))
             ->setEmail(filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL))
             ->setPassword($data['password'] ?? '')
             ->setRole(in_array($data['role'] ?? '', ['Ausbilder', 'Lehrer', 'Admin']) ? $data['role'] : 'Lehrer')
             ->setSecurityAnswer($data['securityAnswer'] ?? '')
             ->setActivated(intval($data['activated'] ?? 1))
             ->setCreatedBy($_SESSION['userId'] ?? 0);

        if ($user->post()) {
            sendResponse(true, $user->toArray(), 'User created successfully', 201);
        } else {
            sendResponse(false, null, 'Failed to create user', 500);
        }
        break;

    case 'PUT':
        $data = getJsonInput();
        if (!$data || !isset($data['userId'])) sendResponse(false, null, 'Missing userId', 400);
        
        $userId = intval($data['userId']);
        if ($userId <= 0) sendResponse(false, null, 'Invalid userId', 400);
        
        // Authorization: Only admin or the user themselves
        if (strtolower($_SESSION['role'] ?? '') !== 'admin' && intval($_SESSION['userId'] ?? 0) !== $userId) {
            sendResponse(false, null, 'Unauthorized to modify this user', 403);
        }

        if (!$user->getById($userId)) sendResponse(false, null, 'User not found', 404);

        if (isset($data['userName'])) $user->setUserName(HtmlSanitizer::sanitize($data['userName']));
        if (isset($data['firstName'])) $user->setFirstName(HtmlSanitizer::sanitize($data['firstName']));
        if (isset($data['lastName'])) $user->setLastName(HtmlSanitizer::sanitize($data['lastName']));
        if (isset($data['email'])) $user->setEmail(filter_var($data['email'], FILTER_SANITIZE_EMAIL));
        if (isset($data['password'])) $user->setPassword($data['password']);
        if (isset($data['role']) && strtolower($_SESSION['role'] ?? '') === 'admin') {
            if (in_array($data['role'], ['Ausbilder', 'Lehrer', 'Admin'])) {
                $user->setRole($data['role']);
            }
        }
        if (isset($data['securityAnswer'])) $user->setSecurityAnswer($data['securityAnswer']);
        if (isset($data['activated']) && strtolower($_SESSION['role'] ?? '') === 'admin') {
            $user->setActivated(intval($data['activated']));
        }

        if ($user->update($userId)) {
            sendResponse(true, null, 'User updated successfully');
        } else {
            sendResponse(false, null, 'Failed to update user', 500);
        }
        break;

    case 'DELETE':
        $data = getJsonInput();
        $id = intval($data['userId'] ?? $_GET['id'] ?? 0);
        
        if ($id <= 0) sendResponse(false, null, 'Invalid userId', 400);

        if ($user->delete($id)) {
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
