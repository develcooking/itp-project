<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/middleware/startSession.php";

// Privent direct access
 if (!isset($_SESSION['registered'])) {
     header("Location: /views/register.php");
    exit();
 }

unset($_SESSION['registered']);
?>

<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<title>Registrierung erfolgreich</title>
<link rel="stylesheet" href="/resources/css/bootstrap.min.css">
<link rel="stylesheet" href="/resources/css/successRegister.css">
</head>

<body>

<div class="card bg-light shadow p-5 text-center">
    <div class="success-icon">🎉</div>
    <h2 class="text-success">Registrierung erfolgreich</h2>
    <p>Dein Account wurde erfolgreich erstellt.</p>
    <a href="/views/loginsite.php" class="btn btn-outline-primary btn-lg w-100">Zurück zur Anmeldung</a>
</div>
<script src="/resources/js/successRegister.js"></script>
</body>
</html>
