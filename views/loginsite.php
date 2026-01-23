<?php
include $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
include $homepath . "/views/header.php";
?>

<div class="container min-vh-100 d-flex justify-content-center align-items-center">
  <div class="row w-100 justify-content-center">
    <div class="col-12 col-sm-10 col-md-8 col-lg-6">
      
      <?php if (!isset($_SESSION['user'])): ?>
        <div class="card bg-light shadow p-4 text-center">

          <h2 class="fw-bold mb-2">Anmelden</h2>
          <p class="text-muted mb-4">Bitte geben Sie Ihre Zugangsdaten ein.</p>

          <form method="post" action="../controllers/login.php">

            <div class="form-floating mb-3">
              <input type="email" name="email" class="form-control" placeholder="E-Mail-Adresse" required>
              <label for="email">E-Mail-Adresse</label>
            </div>

            <div class="form-floating mb-3 position-relative password-container">
  <input type="password" name="password" class="form-control pe-5" id="password_login" placeholder="Passwort" required>
  <label for="password_login">Passwort</label>
  <span class="password-eye"> <!-- no duplicate ID -->
    <!-- Eye open -->
    <svg id="eyeOpen_login" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
      <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
      <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
    </svg>
    <!-- Eye slash -->
    <svg id="eyeSlash_login" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-slash" viewBox="0 0 16 16" style="display:none;">
      <path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7 7 0 0 0-2.79.588l.77.771A6 6 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755q-.247.248-.517.486z"/>
      <path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829"/>
      <path d="M3.35 5.47q-.27.24-.518.487A13 13 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7 7 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12z"/>
    </svg>
  </span>
</div>


            <div class="mb-2 text-start">
              <a href="reset.php" class="text-muted small">Passwort vergessen?</a>
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
            Logout (<?= htmlspecialchars($_SESSION['user']); ?>)
          </button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>
<script src="/resources/js/createAccount.js"></script>
<?php include $homepath . "/views/footer.php"; ?>
