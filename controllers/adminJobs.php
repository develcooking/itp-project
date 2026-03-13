<?php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/User.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/Job.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

$currentUserId = $_SESSION['userId'] ?? null;
if (empty($currentUserId)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated'], JSON_UNESCAPED_UNICODE);
    exit;
}

$adminUser = new User($conn);
if (!$adminUser->getById($currentUserId) || strtolower($adminUser->getRole()) !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden: admin only'], JSON_UNESCAPED_UNICODE);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'createJob':
        $name = htmlspecialchars(trim(($_POST['name'] ?? '')));

        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Name darf nicht leer sein'], JSON_UNESCAPED_UNICODE);
            break;
        }

        $jobModel = new Job($conn);

        if ($jobModel->existsByName($name)) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Ein Berufsbereich mit diesem Namen existiert bereits'], JSON_UNESCAPED_UNICODE);
            break;
        }

        $jobModel->setJobName($name);
        $jobModel->setCreateBy($currentUserId);
        $jobModel->setModifiedBy($currentUserId);

        if ($jobModel->post()) {
            echo json_encode(['success' => true, 'message' => 'Berufsbereich erstellt'], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Fehler beim Erstellen des Berufsbereichs'], JSON_UNESCAPED_UNICODE);
        }
        break;

    case 'updateJob':
        $jobId = intval($_POST['jobId'] ?? 0);
        $name = htmlspecialchars(trim($_POST['name'] ?? ''));

        if ($jobId <= 0 || empty($name)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Ungültige Parameter'], JSON_UNESCAPED_UNICODE);
            break;
        }

        $jobModel = new Job($conn);
        if ($jobModel->update($jobId, $name)) {
            echo json_encode(['success' => true, 'message' => 'Berufsbereich aktualisiert'], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Fehler beim Aktualisieren'], JSON_UNESCAPED_UNICODE);
        }
        break;

    case 'deleteJob':
        $jobId = intval($_POST['jobId'] ?? 0);

        if ($jobId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Ungültige Job-ID'], JSON_UNESCAPED_UNICODE);
            break;
        }

        $jobModel = new Job($conn);
        if ($jobModel->delete($jobId)) {
            echo json_encode(['success' => true, 'message' => 'Berufsbereich gelöscht'], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Fehler beim Löschen'], JSON_UNESCAPED_UNICODE);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action'], JSON_UNESCAPED_UNICODE);
        break;
}

$conn->close();
