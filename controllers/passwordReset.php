<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/middleware/startSession.php";
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../database/db.php';

/**
 * Hilfsfunktion für Redirects mit Fehlermeldung
 */
function redirectWithError($message, $target = "/views/passwordReset.php")
{
    $_SESSION['reset_error'] = $message;
    header("Location: " . $target);
    exit();
}

// Access control
if (empty($_SESSION['password_reset']) || $_SESSION['password_reset']['verified'] !== true) {
    header("Location: /views/passwordForgot.php");
    exit();
}

// Expiration check (15 min)
if (time() - $_SESSION['password_reset']['time'] > 900) {
    unset($_SESSION['password_reset']);
    redirectWithError("Die Sitzung ist abgelaufen. Bitte erneut versuchen.", "/views/passwordForgot.php");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_SESSION['password_reset']['email'] ?? '';
    $newPassword = $_POST['newPassword'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    // validate
    if (empty($newPassword) || empty($confirmPassword)) {
        redirectWithError("Alle Felder sind erforderlich");
    }

    if ($newPassword !== $confirmPassword) {
        redirectWithError("Passwörter stimmen nicht überein");
    }

    if (strlen($newPassword) < 8) {
        redirectWithError("Passwort muss mindestens 8 Zeichen haben");
    }

    try {
        $user = new User($conn);

        if (!$user->getByEmail($email)) {
            redirectWithError("Benutzer nicht gefunden.");
        }

        $user->setPassword($newPassword);
        $success = $user->updatePasswordByID();

        if ($success) {
            // success: clear session
            unset($_SESSION['password_reset']);

            session_regenerate_id(true);
            $_SESSION['reset_success'] = "Passwort erfolgreich zurückgesetzt.";
            header("Location: /views/loginsite.php");
            exit();
        } else {
            redirectWithError("Fehler beim Zurücksetzen: Benutzer nicht gefunden oder Systemfehler.");
        }
    } catch (Exception $e) {
        // log the error $e->getMessage();
        redirectWithError("Ein interner Fehler ist aufgetreten.");
    }
}
// if someone tries to access this controller without POST
header("Location: /views/passwordForgot.php");
exit();