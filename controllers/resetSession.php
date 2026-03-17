<?php
// This is only a test file
// Remove all session variables
require_once $_SERVER['DOCUMENT_ROOT'] . "/middleware/startSession.php";
session_unset();

// Destroy the session
session_destroy();

// Remove the cookie from the browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirect back to passwordForgot page
header("Location: /views/passwordForgot.php");
exit();
