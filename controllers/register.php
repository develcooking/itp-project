<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/User.php";

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['createAccount'])) {
    $userName = htmlspecialchars(trim($_POST['userName'] ?? ''));
    $firstName = htmlspecialchars(trim($_POST['firstName'] ?? ''));
    $lastName = htmlspecialchars(trim($_POST['lastName'] ?? ''));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $password = htmlspecialchars(trim($_POST['password'] ?? ''));
    $role = htmlspecialchars(trim($_POST['role'] ?? ''));
    $securityAnswer = htmlspecialchars(trim($_POST['securityAnswer'] ?? ''));

    if (empty($userName) || empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($role) || empty($securityAnswer)) {
        $errors = 'Bitte füllen Sie alle Felder aus!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Ungültiges E-Mail-Format!';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Passwort muss mindestens 6 Zeichen lang sein!';
    } else {
        $user = new User($conn);

        if ($user->emailExists($email)) {
            $errors['email'] = 'Diese E-Mail-Adresse ist bereits registriert!';
        } elseif ($user->userNameExists($userName)) {
            $errors['userName'] = 'Dieser Benutzername ist bereits registriert!';
        } else {
            $user->setUserName($userName);
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setEmail($email);
            $user->setPassword($password);
            $user->setRole($role);
            $user->setSecurityAnswer(password_hash($securityAnswer, PASSWORD_DEFAULT));
            $user->setActivated(0);
            $user->setCreatedBy(null);
            $user->setModifiedBy(null);

            if ($user->post()) {
                $_SESSION['registered'] = true;
                header("Location: /views/successRegister.php", true, 303);
                exit();
            } else {
                $errors = 'Fehler beim Registrieren. Bitte versuchen Sie es später erneut.';
            }
        }
    }
}

require $_SERVER['DOCUMENT_ROOT'] . "/views/createAccount.php";
exit();