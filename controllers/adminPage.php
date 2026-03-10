<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/database/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Job.php';

$currentUserId = $_SESSION['userId'] ?? null;
if (empty($currentUserId)) {
    header('Location: /views/loginsite.php');
    exit;
}

$currentUser = new User($conn);
if (!$currentUser->getById($currentUserId) || strtolower($currentUser->getRole()) !== 'admin') {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

$userModel = new User($conn);
$allUsers = $userModel->getAll();

$pendingUsers = array_filter($allUsers, fn($u) => !$u['activated']);
$activatedUsers = array_filter($allUsers, fn($u) => $u['activated']);

$jobs = [];
$jobModel = new Job($conn);
$jobs = $jobModel->getAll();
