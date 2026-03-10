<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../database/db.php';

session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['passwordForgot'])) {

    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $securityAnswer = trim($_POST['securityAnswer'] ?? '');

    if (empty($email) || empty($securityAnswer)) {
        $errors['general'] = "Alle Felder sind erforderlich";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['general'] = "Ungültige E-Mail-Adresse";
    } else {
        $user = new User($conn);
        $user->getByEmail($email);

        if (!$user->getUserId()) {
            $errors['general'] = "E-Mail oder Sicherheitsantwort ist falsch";
        } else {
            $inputAnswer = trim($securityAnswer);
            $storedHash  = $user->getSecurityAnswer();

            if (!password_verify($inputAnswer, $storedHash)) {
                $errors['general'] = "Sicherheitsantwort falsch";
            } else {
                // Create reset session
                $_SESSION['password_reset'] = [
                    'email'    => $user->getEmail(),
                    'verified' => true,
                    'time'     => time()
                ];

                header("Location: /views/passwordReset.php", true, 303);
                exit();
            }
        }
    }
}

require $_SERVER['DOCUMENT_ROOT'] . "/views/passwordForgot.php";
exit();