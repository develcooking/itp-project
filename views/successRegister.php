<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/middleware/startSession.php";

// Prevent direct access
if (empty($_SESSION['registered'])) {
    header("Location: /views/createAccount.php");
    exit();
}

unset($_SESSION['registered']);

include $_SERVER['DOCUMENT_ROOT'] . "/views/header.php";
?>

<link rel="stylesheet" href="/resources/css/successRegister.css">

<div class="container min-vh-100 d-flex justify-content-center align-items-center my-4">
    <div class="row w-100 justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6">
            <div class="card bg-light shadow p-5 text-center">
                <div class="success-icon">🎉</div>
                <h2 class="text-success">Registrierung erfolgreich</h2>
                <p>Dein Account wurde erfolgreich erstellt.</p>
                <a href="/views/loginsite.php" class="btn btn-outline-primary btn-lg w-100">Zurück zur Anmeldung</a>
            </div>
        </div>
    </div>
</div>

<script src="/resources/js/successRegister.js"></script>
<?php include $_SERVER['DOCUMENT_ROOT'] . "/views/footer.php"; ?>
