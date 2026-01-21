<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/User.php";

if (!isset($_SESSION)) {
    session_start();
}

$error = '';
$statusCode = 200;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $statusCode = 400;
            $error = "Email and password are required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $statusCode = 400;
            $error = "Invalid email format";
        } else {
            $user = new User($conn);
            
            if ($user->getByEmail($email)) {
                if (password_verify($password, $user->passwort)) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user->id;
                    $_SESSION['email'] = $user->email;
                    $_SESSION['name'] = $user->name;
                    $_SESSION['vorname'] = $user->vorname;
                    $_SESSION['art'] = $user->art;
                    $_SESSION['logged_in'] = true;
                    
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                } else {
                    $statusCode = 400;
                    $error = "Invalid email or password";
                }
            } else {
                $statusCode = 400;
                $error = "Invalid email or password";
            }
        }
        http_response_code($statusCode);
    }
    
    if (isset($_POST['logout'])) {
        session_destroy();
        
        header("Location: /login.php");
        exit();
    }
}

if (!empty($error)) {
    echo "<div class='error'>" . htmlspecialchars($error) . "</div>";
}