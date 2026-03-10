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

<div class="container d-flex justify-content-center align-items-center m-5">
    <div class="row w-100 justify-content-center m-5">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6 p-3">
            <div class="card bg-light shadow p-4 text-center">
                <h2 class="fw-bold mb-2">Neues Passwort</h2>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="post" class="p-3" action="/controllers/passwordReset.php">
                    <div class="form-floating mb-3 position-relative password-container">
                                <input type="password" name="newPassword" class="form-control pe-5" id="passwordReset" placeholder="Passwort" required>
                                <label for="password">Passwort</label>
                                <span class="password-eye">
                                    <img id="eyeOpenReset" src="/resources/imgs/eye.svg" alt="Show password" width="16" height="16" style="cursor:pointer;">
                                    <img id="eyeSlashReset" src="/resources/imgs/eye-slash.svg" alt="Hide password" width="16" height="16" style="cursor:pointer; display:none;">
                                </span>
                            </div>
                            <div class="form-floating mb-3 position-relative password-container">
                                <input type="password" name="confirmPassword" class="form-control pe-5" id="confirmPasswordReset" placeholder="Passwort" required>
                                <label for="confirmpassword">Passwort bestätigen</label>
                                <span class="password-eye">
                                    <img id="eyeOpenConfirmReset" src="/resources/imgs/eye.svg" alt="Show password" width="16" height="16" style="cursor:pointer;">
                                    <img id="eyeSlashConfirmReset" src="/resources/imgs/eye-slash.svg" alt="Hide password" width="16" height="16" style="cursor:pointer; display:none;">
                                </span>
                            </div>

                    <button class="btn btn-outline-primary btn-lg w-100" type="submit" name="passwordReset" value="1">
                        Bestätigen
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
                </main>
<script src="/resources/js/formValidation.js"></script>
<?php include $_SERVER['DOCUMENT_ROOT'] . "/views/footer.php"; ?>
