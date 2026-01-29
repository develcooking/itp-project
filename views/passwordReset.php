<?php
session_start();

if (empty($_SESSION['password_reset']) || $_SESSION['password_reset']['verified'] !== true) {
    header("Location: /views/passwordForgot.php");
    exit();
}

// Expiration check
if (time() - $_SESSION['password_reset']['time'] > 900) {
    unset($_SESSION['password_reset']);
    header("Location: /views/passwordForgot.php");
    exit();
}

$error = $_SESSION['reset_error'] ?? '';
unset($_SESSION['reset_error']);
?>

<?php include $_SERVER['DOCUMENT_ROOT'] . "/views/header.php"; ?>

<div class="container min-vh-100 d-flex justify-content-center align-items-center">
    <div class="row w-100 justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6">
            <div class="card bg-light shadow p-4 text-center">
                <h2 class="fw-bold mb-2">Neues Passwort festlegen</h2>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="post" action="/controllers/passwordReset.php">
                    <div class="form-floating mb-3">
                        <input type="password" name="newPassword" class="form-control" required>
                        <label>Neues Passwort</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="password" name="confirmPassword" class="form-control" required>
                        <label>Passwort bestätigen</label>
                    </div>

                    <button class="btn btn-outline-primary btn-lg w-100" type="submit" name="passwordReset" value="1">
                        Bestätigen
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="/resources/js/formValidation.js"></script>
<?php include $_SERVER['DOCUMENT_ROOT'] . "/views/footer.php"; ?>
