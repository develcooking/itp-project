<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../database/db.php';

session_start();

function controllerSendError($code, $message) {
    http_response_code($code);
    die("<div style='color:red;font-weight:bold'>" . htmlspecialchars($message) . "</div>");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['passwordForgot'])) {

    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $securityAnswer = trim($_POST['securityAnswer'] ?? '');

    if (!$email || !$securityAnswer) {
        controllerSendError(400, "Alle Felder sind erforderlich");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        controllerSendError(400, "Ungültige E-Mail-Adresse");
    }

    $user = new User($conn);
    $user->getByEmail($email);

    if (!$user->getUserId()) {
        // we will not reveal whether the email exists or not
        controllerSendError(404, "E-Mail oder Sicherheitsantwort ist falsch");
    }

    $inputAnswer  = strtolower(trim($securityAnswer));
    $storedAnswer = strtolower(trim($user->getSecurityAnswer()));

    if ($inputAnswer !== $storedAnswer) {
        controllerSendError(401, "Sicherheitsantwort falsch");
    }

    // Create reset session
    $_SESSION['password_reset'] = [
        'email'    => $user->getEmail(),
        'verified' => true,
        'time'     => time()
    ];

    header("Location: /views/passwordReset.php");
    exit();
}
