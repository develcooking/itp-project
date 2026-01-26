<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/User.php";


$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['createAccount'])) {
        $userName = $_POST['userName'];
        $firstName = $_POST['firstName'];
        $lastName = $_POST['lastName'];
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        $role = $_POST['role'];
        $securityAnswer = $_POST['securityAnswer'];
        $validRoles = ['Ausbilder', 'Lehrer', 'Admin'];

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
                $user->setLastName($lastName);
                $user->setEmail($email);
                $user->setPassword($password);
                $user->setRole($role);
                $user->setSecurityAnswer(password_hash($securityAnswer, PASSWORD_DEFAULT));
                $user->setActivated(0);
                $user->setCreatedBy(null);
                $user->setModifiedBy(null);

                if ($user->post()) {
                    http_response_code(201);
                    $success = 'Benutzer erfolgreich registriert. Sie können sich jetzt anmelden.';
                    header("Location: /views/loginsite.php");
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
