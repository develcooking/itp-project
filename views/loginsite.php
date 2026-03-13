<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/middleware/startSession.php";
include $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
include $_SERVER['DOCUMENT_ROOT'] . "/views/header.php";

if (!empty($_SESSION['userId'])) {
    header("Location: /views/startpage.php");
    exit();
}
?>

<div class="flex-grow-1 d-flex justify-content-center align-items-center m-5">
  <div class="row w-100 justify-content-center">
    <div class="col-12 col-sm-10 col-md-8 col-lg-6 p-3">
      
      <?php if (isset($_GET['expired']) && $_GET['expired'] == 1): ?>
        <div class="alert alert-warning text-center mb-4">
            Ihre Sitzung ist abgelaufen. Bitte melden Sie sich erneut an.
        </div>
      <?php endif; ?>

      <?php if (!isset($_SESSION['userId'])): ?>
        <div class="card bg-light shadow p-4 text-center">

          <h2 class="fw-bold mb-2">Anmelden</h2>
          <p class="text-muted mb-4">Bitte geben Sie Ihre Zugangsdaten ein.</p>

          <?php if (!empty($errors['login'])): ?>
            <div class="alert alert-warning" role="alert">
              <?= htmlspecialchars($errors['login']) ?>
            </div>
          <?php endif; ?>

          <form method="post" action="../controllers/login.php">

            <div class="form-floating mb-3">
              <input type="email" name="email" class="form-control" id="emailLogin" placeholder="E-Mail-Adresse" required>
              <label for="email">E-Mail-Adresse</label>
            </div>

            <div class="form-floating mb-3 position-relative password-container">
              <input type="password" name="password" class="form-control pe-5" id="passwordLogin" placeholder="Passwort" required>
              <label for="password">Passwort</label>

              <span class="password-eye">
                <!-- Eye open -->
                <img id="eyeOpenLogin" src="/resources/imgs/eye.svg" alt="Show password" width="16" height="16" style="cursor:pointer;">
                <!-- Eye slash -->
                <img id="eyeSlashLogin" src="/resources/imgs/eye-slash.svg" alt="Hide password" width="16" height="16" style="cursor:pointer; display:none;">
              </span>
            </div>
            <div class="mb-2 text-start">
              <a href="passwordForgot.php" class="text-muted small">Passwort vergessen?</a>
            </div>

            <button class="btn btn-outline-primary btn-lg w-100" type="submit" name="login">
              Login
            </button>

          </form>

          <hr class="my-4">

          <p class="mb-0">
            Noch keinen Account?
            <a href="createAccount.php" class="fw-bold text-decoration-none">Jetzt registrieren</a>
          </p>

        </div>
      <?php else: ?>
        <form method="post" action="">
          <button class="btn btn-outline-primary btn-lg w-100" type="submit" name="logout">
            Logout (<?= htmlspecialchars($_SESSION['userName']); ?>)
          </button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>
      </main>
<script src="/resources/js/formValidation.js"></script>

<?php include $_SERVER['DOCUMENT_ROOT'] . "/views/footer.php";?>
