<?php
// Initializes PHP session handling so session data can be stored and accessed
// Used here to track successful registration flow between pages
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/User.php";


$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['createAccount'])) {
        $userName = htmlspecialchars(trim($_POST['userName']));;
        $firstName = htmlspecialchars(trim($_POST['firstName']));
        $lastName = htmlspecialchars(trim($_POST['lastName']));
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $email = htmlspecialchars(trim($_POST['email']));
        $password = htmlspecialchars(trim($_POST['password']));
        $role = htmlspecialchars(trim($_POST['role']));
        $securityAnswer = htmlspecialchars(trim($_POST['securityAnswer']));

        if (empty($userName) || empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($role) || empty($securityAnswer)) {
            http_response_code(400);
            $error = 'Bitte füllen Sie alle Felder aus!';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            $error = 'Ungültiges E-Mail-Format!';
        } elseif (strlen($password) < 6) {
            //Es wird nachgearbeitet durch RegEx für Validierung, falls möglich das in Class zu machen
            http_response_code(400);
            $error = 'Passwort muss mindestens 6 Zeichen lang sein!';
        } else {
            $user = new User($conn);

            if ($user->getByEmail($email)) {
                http_response_code(409);
                $error = 'Diese E-Mail-Adresse ist bereits registriert!';
            } else {
                $user->setUserName($userName);
                $user->setFirstName($firstName);
                $user->setLastName($lastName) ;
                $user->setEmail($email);
                $user->setPassword($password);
                $user->setRole($role);
                $user->setSecurityAnswer($securityAnswer);
                $user->setActivated(0);
                $user->setCreatedBy(null);
                $user->setModifiedBy(null);

                if ($user->post()) {
                    header("Location: /views/successRegister.php");
                    $_SESSION['registered'] = true;
                    exit();
                } else {
                    http_response_code(500);
                    $error = 'Fehler beim Registrieren. Bitte versuchen Sie es später erneut.';
                }
            }
        }
    }
}

if (!empty($error)) {
    echo "<div class='error'>" . htmlspecialchars($error) . "</div>";
}
if (!empty($success)) {
    echo "<div class='success'>" . htmlspecialchars($success) . "</div>";
}
