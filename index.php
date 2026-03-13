<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/middleware/startSession.php";

if (!empty($_SESSION['userId'])) {
    header("Location: /views/startpage.php");
    exit();
}

header("Location: /views/loginsite.php");
exit();