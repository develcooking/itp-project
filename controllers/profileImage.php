<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/middleware/startSession.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/User.php";

if (empty($_SESSION['userId'])) {
    http_response_code(403);
    exit();
}

$user = new User($conn);
$requestedUserId = filter_input(INPUT_GET, 'userId', FILTER_VALIDATE_INT);
$targetUserId = $requestedUserId !== false && $requestedUserId !== null ? (int)$requestedUserId : (int)$_SESSION['userId'];

if ($targetUserId <= 0) {
    http_response_code(400);
    exit();
}

$payload = $user->getProfileImagePayloadById($targetUserId);

if ($payload === null) {
    http_response_code(404);
    exit();
}

$allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
$mime = $payload['mime'];

if (!in_array($mime, $allowedMimes, true)) {
    http_response_code(415);
    exit();
}

header('Content-Type: ' . $mime);
header('Cache-Control: private, max-age=300');

echo $payload['data'];
