<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../database/db.php';

session_start();

// Access control
if (empty($_SESSION['password_reset']) || $_SESSION['password_reset']['verified'] !== true) {
    header("Location: /views/passwordForgot.php");
    exit();
}

// Expiration check (15 min)
if (time() - $_SESSION['password_reset']['time'] > 900) {
    unset($_SESSION['password_reset']);
    header("Location: /views/passwordForgot.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_SESSION['password_reset']['email'] ?? '';
    $newPassword = $_POST['newPassword'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    // Validation
    if (empty($newPassword) || empty($confirmPassword)) {
        $_SESSION['reset_error'] = "Alle Felder sind erforderlich";
    } elseif ($newPassword !== $confirmPassword) {
        $_SESSION['reset_error'] = "Passwörter stimmen nicht überein";
    } elseif (strlen($newPassword) < 4) { 
        $_SESSION['reset_error'] = "Passwort muss mindestens 4 Zeichen haben";
    } else {
        // Update password
        $user = new User($conn);
        $success = $user->resetPassword($email, $newPassword, null);

        if ($success) {
            unset($_SESSION['password_reset']);
            session_regenerate_id(true);
            $_SESSION['reset_success'] = "Passwort erfolgreich zurückgesetzt.";
            header("Location: /views/loginsite.php");
            exit();
        } else {
            $_SESSION['reset_error'] = "Fehler beim Zurücksetzen des Passworts. Benutzer nicht gefunden.";
        }
    }

    header("Location: /views/passwordReset.php");
    exit();
}
header("Location: /views/passwordForgot.php");
exit();
