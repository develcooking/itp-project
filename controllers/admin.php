<?php
header('Content-Type: application/json; charset=utf-8');

require_once $_SERVER['DOCUMENT_ROOT'] . "/middleware/startSession.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/User.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/Job.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/UserJobs.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

// nur Admins dürfen diese Aktionen ausführen
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
    case 'toggleActivated':
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
        break;

    case 'getJobsForUser':
        $userId = intval($_POST['userId'] ?? 0);
        if ($userId > 0) {
            $jobModel = new Job($conn);
            $allJobs = $jobModel->getAll();

            $userJobsModel = new UserJobs($conn);
            $assignedJobIds = $userJobsModel->getJobsForUserByID($userId);

            $result = [];
            foreach ($allJobs as $job) {
                $result[] = [
                    'jobId' => $job['jobId'],
                    'name' => $job['name'],
                    'assigned' => in_array($job['jobId'], $assignedJobIds)
                ];
            }

            echo json_encode(['success' => true, 'jobs' => $result], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid user ID'], JSON_UNESCAPED_UNICODE);
        }
        break;

    case 'toggleUserJob':
        $userId = intval($_POST['userId'] ?? 0);
        $jobId = intval($_POST['jobId'] ?? 0);
        $assign = intval($_POST['assign'] ?? 0);

        if ($userId > 0 && $jobId > 0) {
            $userJobsModel = new UserJobs($conn);
            if ($assign === 1) {
                $ok = $userJobsModel->assign($userId, $jobId, $currentUserId);
            } else {
                $ok = $userJobsModel->remove($userId, $jobId);
            }

            if ($ok) {
                echo json_encode(['success' => true, 'message' => 'Job assignment updated'], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update job assignment'], JSON_UNESCAPED_UNICODE);
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid user or job ID'], JSON_UNESCAPED_UNICODE);
        }
        break;

    case 'updateRole':
        $userId = intval($_POST['userId'] ?? 0);
        $role = trim($_POST['role'] ?? '');

        if ($userId > 0 && !empty($role)) {
            $user = new User($conn);
            if ($user->updateRole($userId, $role)) {
                echo json_encode(['success' => true, 'message' => 'Rolle aktualisiert'], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Ungültige Rolle oder Fehler beim Aktualisieren'], JSON_UNESCAPED_UNICODE);
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Ungültige Parameter'], JSON_UNESCAPED_UNICODE);
        }
        break;

    case 'blockUser':
        $userId = intval($_POST['userId'] ?? 0);
        $duration = trim($_POST['duration'] ?? '');

        $allowedDurations = ['1week', '1month', '1year', 'permanent'];
        if ($userId <= 0 || !in_array($duration, $allowedDurations)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Ungültige Parameter'], JSON_UNESCAPED_UNICODE);
            break;
        }

        $user = new User($conn);
        if (!$user->getById($userId)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Benutzer nicht gefunden'], JSON_UNESCAPED_UNICODE);
            break;
        }

        $permanent = ($duration === 'permanent');
        $blockedUntil = null;
        if (!$permanent) {
            $map = ['1week' => '+1 week', '1month' => '+1 month', '1year' => '+1 year'];
            $blockedUntil = date('Y-m-d H:i:s', strtotime($map[$duration]));
        }

        if ($user->blockUser($userId, $permanent, $blockedUntil)) {
            echo json_encode(['success' => true, 'permanent' => $permanent, 'blockedUntil' => $blockedUntil], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Fehler beim Sperren'], JSON_UNESCAPED_UNICODE);
        }
        break;

    case 'unblockUser':
        $userId = intval($_POST['userId'] ?? 0);

        if ($userId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Ungültige Benutzer-ID'], JSON_UNESCAPED_UNICODE);
            break;
        }

        $user = new User($conn);
        if ($user->unblockUser($userId)) {
            echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Fehler beim Freigeben'], JSON_UNESCAPED_UNICODE);
        }
        break;

    case 'deleteUser':
        $userId = intval($_POST['userId'] ?? 0);

        if ($userId > 0) {
            $user = new User($conn);
            if ($user->getById($userId)) {
                // Erst zugewiesene Jobs entfernen
                $userJobsModel = new UserJobs($conn);
                $userJobsModel->removeAllForUser($userId);

                if ($user->delete($userId)) {
                    echo json_encode(['success' => true, 'message' => 'User deleted'], JSON_UNESCAPED_UNICODE);
                } else {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Failed to delete user'], JSON_UNESCAPED_UNICODE);
                }
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'User not found'], JSON_UNESCAPED_UNICODE);
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid user ID'], JSON_UNESCAPED_UNICODE);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ], JSON_UNESCAPED_UNICODE);
        break;
}

$conn->close();
