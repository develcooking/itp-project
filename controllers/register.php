<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/middleware/startSession.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/User.php";

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['createAccount'])) {
    $userName = htmlspecialchars(trim($_POST['userName'] ?? ''));
    $firstName = htmlspecialchars(trim($_POST['firstName'] ?? ''));
    $lastName = htmlspecialchars(trim($_POST['lastName'] ?? ''));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirmPassword'] ?? '');
    $role = htmlspecialchars(trim($_POST['role'] ?? ''));
    $securityAnswer = trim($_POST['securityAnswer'] ?? '');
    $schoolCompany = htmlspecialchars(trim($_POST['school_company'] ?? ''));

    if (empty($userName) || empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($role) || empty($securityAnswer) || empty($confirmPassword)) {
        $errors['general'] = 'Bitte füllen Sie alle Felder aus!';
    } elseif ($password !== $confirmPassword) {
        $errors['password'] = 'Passwörter stimmen nicht überein!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Ungültiges E-Mail-Format!';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Passwort muss mindestens 8 Zeichen lang sein!';
    } elseif ($password == $securityAnswer) {
        $errors['password'] = 'Passwort kann nicht Sicherheitsantwort sein!';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors['password'] = 'Passwort muss min ein Großzeichen haben!';
    } elseif (!preg_match('/[a-z]/', $password)) {
        $errors['password'] = 'Passwort muss min ein Kleinzeichen haben!';
    } elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors['password'] = 'Passwort muss min ein Sonderzeichen haben!';
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
            $user->setSchoolCompany($schoolCompany);
            $user->setActivated(0);
            $user->setCreatedBy(null);
            $user->setModifiedBy(null);

            if ($user->post()) {
                $_SESSION['registered'] = true;
                header("Location: /views/successRegister.php");
                exit();
            } else {
                $errors['general'] = 'Fehler beim Registrieren. Bitte versuchen Sie es später erneut.';
            }
        }
    }
}

require $_SERVER['DOCUMENT_ROOT'] . "/views/createAccount.php";
exit();