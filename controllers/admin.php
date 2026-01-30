<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/database/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/User.php';

$users = [];

if ($conn) {
    $userModel = new User($conn);
    $users = $userModel->getAll();
}
?>