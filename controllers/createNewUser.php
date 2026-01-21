<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/User.php";


$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['createAccount'])) {
        $name = $_POST['nachname'];
        $vorname = $_POST['vorname'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $art = $_POST['art'] ?? '';
        
        if(empty($name) || empty($vorname) || empty($username) || empty($password) || empty($email) || empty($art)){
            http_response_code(400);
            $error = 'Bitte füllen Sie alle Felder aus!';
        }elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            http_response_code(400);
            $error = 'Ungültiges E-Mail-Format!';
        }elseif(strlen($password) < 6){
            //Es wird nachgearbeitet durch RegEx für Validierung, falls möglich das in Class zu machen
            http_response_code(400);
            $error = 'Passwort muss mindestens 6 Zeichen lang sein!';
        }else{
            $user = new User($conn);

            if($user->getByEmail($email)){
                http_response_code(409);
                $error = 'Diese E-Mail-Adresse ist bereits registriert!';
            }else{
                $user->name = $name;
                $user->vorname = $vorname;
                $user->email = $email;
                $user->username = $username;
                $user->passwort = $password;
                $user->art = $art;

                if($user->post()){
                    http_response_code(201);
                    $success = 'Benutzer erfolgreich registriert. Sie können sich jetzt anmelden.';
                    header("Location: /views/loginsite.php");
                    exit();
                }else{
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
