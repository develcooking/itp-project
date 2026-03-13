<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/User.php";

if (!isset($_SESSION)) {
    session_start();
}

if (empty($_SESSION['userId'])) {
    header("Location: /views/loginsite.php");
    exit();
}

$checkUser = new User($conn);
if ($checkUser->getById($_SESSION['userId'])) {
    if ($checkUser->getActivated() !== 1) {
        session_destroy();
        header("Location: /views/loginsite.php?deactivated=1");
        exit();
    }
} else {
    session_destroy();
    header("Location: /views/loginsite.php?deleted=1");
    exit();
}