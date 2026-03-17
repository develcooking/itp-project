<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once $_SERVER['DOCUMENT_ROOT'] . "/middleware/csrf.php";
generateCsrfToken();
validateCsrfOrDie();