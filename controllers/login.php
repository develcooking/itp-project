<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/User.php";

if (!isset($_SESSION)) {
    session_start();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];

        if (empty($email) || empty($password)) {
            http_response_code(400);
            $error = "Email and password are required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            $error = "Invalid email format";
        } else {
            $user = new User($conn);

            if ($user->getByEmail($email)) {
                if (password_verify($password, $user->password)) {
                    session_regenerate_id(true);
                    $_SESSION['logged_in'] = true;
                    $_SESSION['userId'] = $user->userId;
                    $_SESSION['userName'] = $user->userName;
                    $_SESSION['firstName'] = $user->firstName;
                    $_SESSION['lastName'] = $user->lastName;
                    $_SESSION['email'] = $user->email;
                    $_SESSION['role'] = $user->role;

                    header("Location: /views/startpage.php");
                    exit();
                } else {
                    http_response_code(400);
                    $error = "Invalid email or password";
                }
            } else {
                http_response_code(400);
                $error = "Invalid email or password";
            }
        }
    }

    if (isset($_POST['logout'])) {
        session_destroy();

        header("Location: /views/loginsite.php");
        exit();
    }
}

if (!empty($error)) {
    echo "<div class='error'>" . htmlspecialchars($error) . "</div>";
}