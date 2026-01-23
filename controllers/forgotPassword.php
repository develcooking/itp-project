<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/User.php";

if (!isset($_SESSION)) {
    session_start();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['resetPassword'])) {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $securityAnswer = $_POST['securityAnswer'];

        if(empty($email) || empty($securityAnswer)){
            http_response_code(400);
            $error = "Email and Password are required.";
        }elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            http_response_code(400);
            $error = "Invalid email format.";
        }else{
            $user = new User($conn);

            if($user->getByEmail($email)){
                if($securityAnswer === $user->securityAnswer){
                    header("Location: /views/resetPassword.php");
                }
                //$success
            }else{
                http_response_code(400);
                $error = "Invalid credentials.";
            }
        }
    }
}
