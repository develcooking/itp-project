<?php
include $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
include $homepath . "/views/header.php";

if (isset($_SESSION['userId'])) {
    header("Location: /index.php");
    exit();
}
?>

<div class="flex-grow-1 d-flex justify-content-center align-items-center m-5">
    <div class="row w-100 justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6 p-3">

            <div class="card bg-light shadow p-4 text-center">

                <h2 class="fw-bold mb-2">Passwort zurücksetzen</h2>

                <form method="post" class="p-3" action="../controllers/passwordForgot.php">

                    <div class="form-floating mb-3">
                        <input
                        type="email"
                        name="email"
                        class="form-control"
                        id="emailForgot"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        placeholder="E-Mail-Adresse"
                        required>
                        <label for="email">E-Mail-Adresse</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input
                        type="text"
                        name="securityAnswer"
                        class="form-control"
                        id="securityAnswer"
                        placeholder="Antwort auf Sicherheitsfrage"
                        value="<?= htmlspecialchars($_POST['securityAnswer'] ?? '') ?>"
                        required>
                        <label for="securityAnswer">Antwort auf die Sicherheitsfrage</label>
                        <?php if (!empty($errors['general'])): ?>
                        <div class="generalError" style="font-size: .875em; color: #dc3545">
                            <?= htmlspecialchars($errors['general']) ?>
                        </div>
                    <?php endif; ?>
                    </div>

                    <button class="btn btn-outline-primary btn-lg w-100" type="submit" name="passwordForgot">
                        Weiter
                    </button>
                </form>

                <hr class="my-4">

                <p class="mb-0">
                    <a href="loginsite.php" class="fw-bold text-decoration-none">Zurück zur Anmeldung</a>
                </p>

            </div>

        </div>
    </div>
</div>
</main>
<script src="/resources/js/formValidation.js"></script>
<?php include $homepath . "/views/footer.php"; ?>
