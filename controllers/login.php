<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/middleware/startSession.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/User.php";

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];

        if (empty($email) || empty($password)) {
            $errors['login'] = "E-Mail und Passwort sind erforderlich.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['login'] = "Ungültiges E-Mail-Format.";
        } else {
            $user = new User($conn);

            if ($user->getByEmail($email)) {
                if (password_verify($password, $user->getPassword())) {
                    if ($user->getActivated() !== 1) {
                        $errors['login'] = "Ihr Konto ist nicht aktiviert. Bitte wenden Sie sich an einen Administrator.";
                    } else {
                        session_regenerate_id(true);
                        $_SESSION['logged_in'] = true;
                        $_SESSION['userId'] = $user->getUserId();
                        $_SESSION['userName'] = $user->getUserName();
                        $_SESSION['firstName'] = $user->getFirstName();
                        $_SESSION['lastName'] = $user->getLastName();
                        $_SESSION['email'] = $user->getEmail();
                        $_SESSION['role'] = $user->getRole();

                        header("Location: /controllers/startpage.php");
                        exit();
                    }
                } else {
                    $errors['login'] = "Ungültige E-Mail oder Passwort.";
                }
            } else {
                $errors['login'] = "Ungültige E-Mail oder Passwort.";
            }
        }
    }

    if (isset($_POST['logout'])) {
        session_unset();
        session_destroy();

        header("Location: /views/loginsite.php");
        exit();
    }
}

if (!empty($errors)) {
    require $_SERVER['DOCUMENT_ROOT'] . "/views/loginsite.php";
    exit();
}