<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/User.php";

if (empty($_SESSION['userId'])) {
    header("Location: /views/loginsite.php");
    exit();
}

$errors = [];
$success = '';

$user = new User($conn);
$user->getById($_SESSION['userId']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['saveProfile'])) {
    $userName = htmlspecialchars(trim($_POST['userName'] ?? ''));
    $firstName = htmlspecialchars(trim($_POST['firstName'] ?? ''));
    $lastName = htmlspecialchars(trim($_POST['lastName'] ?? ''));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));

    if (empty($userName)) {
        $errors['userName'] = 'Benutzername darf nicht leer sein!';
    }

    if (empty($firstName)) {
        $errors['firstName'] = 'Vorname darf nicht leer sein!';
    }

    if (empty($lastName)) {
        $errors['lastName'] = 'Nachname darf nicht leer sein!';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Bitte geben Sie eine gültige E-Mail-Adresse ein!';
    }

    if (empty($errors)) {
        if ($userName !== $user->getUserName() && $user->userNameExists($userName)) {
            $errors['userName'] = 'Dieser Benutzername ist bereits vergeben!';
        }

        if ($email !== $user->getEmail() && $user->emailExists($email)) {
            $errors['email'] = 'Diese E-Mail-Adresse ist bereits registriert!';
        }
    }

    if (empty($errors)) {
        $user->setUserName($userName);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setEmail($email);

        if ($user->updateProfile($_SESSION['userId'])) {
            $_SESSION['userName'] = $userName;
            $_SESSION['firstName'] = $firstName;
            $_SESSION['lastName'] = $lastName;
            $_SESSION['email'] = $email;
            $success = 'Profil erfolgreich aktualisiert!';
            $user->getById($_SESSION['userId']);
        } else {
            $errors['general'] = 'Fehler beim Speichern. Bitte versuchen Sie es später erneut.';
        }
    }
}

require $_SERVER['DOCUMENT_ROOT'] . "/views/profile.php";
exit();
