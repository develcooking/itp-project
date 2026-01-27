<?php
include $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
include $homepath . "/views/header.php";

if (isset($_SESSION['user'])) {
    header("Location: /index.php");
    exit();
}
?>

<div class="container min-vh-100 d-flex justify-content-center align-items-center">
    <div class="row w-100 justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6">

            <div class="card bg-light shadow p-4 text-center">

                <h2 class="fw-bold mb-2">Neues Passwort festlegen</h2>

                <form method="post" action="../controllers/login.php">

                    <div class="form-floating mb-3 position-relative password-container">
                                <input type="text" name="password" class="form-control pe-5" id="password" placeholder="Passwort" required>
                                <label for="password">Neues Passwort</label>
                            </div>
                            <div class="form-floating mb-3 position-relative password-container">
                                <input type="text" name="password" class="form-control pe-5" id="confirmPassword" placeholder="Passwort" required>
                                <label for="confirmpassword">Neues Passwort bestätigen</label>
                            </div>

                    <button class="btn btn-outline-primary btn-lg w-100" type="submit" name="reset">
                        Bestätigen
                    </button>
                </form>
                <hr class="my-4">
            </div>

        </div>
    </div>
</div>

<script src="/resources/js/createAccount.js"></script>
<?php include $homepath . "/views/footer.php"; ?>
