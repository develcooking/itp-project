<?php
include $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
include $homepath . "/views/header.php";
?>

<div class="center-card-container">

  <?php if (!isset($_SESSION['user'])): ?>
    <div class="center-card card">

  <h2 class="fw-bold mb-2">Anmelden</h2>
  <p class="text-muted mb-4">Bitte geben Sie Ihre Zugangsdaten ein.</p>

  <form method="post" action="../controllers/login.php">

    <div class="form-floating mb-3">
      <input type="email" name="email" class="form-control" id="email" placeholder="E-Mail-Adresse" required>
      <label for="email">E-Mail-Adresse</label>
    </div>

    <div class="form-floating mb-3">
      <input type="password" name="password" class="form-control" id="password" placeholder="Passwort" required>
      <label for="password">Passwort</label>
    </div>

    <div class="mb-2 text-start">
      <a href="reset.php" class="text-muted small">
        Passwort vergessen?
      </a>
    </div>

    <button class="btn btn-outline-primary btn-lg w-100" type="submit" name="login">
      Login
    </button>

  </form>

  <hr class="my-4">

  <p class="mb-0">
    Noch keinen Account?
    <a href="createAccount.php" class="fw-bold text-decoration-none">
      Jetzt registrieren
    </a>
  </p>

</div>

  <?php else: ?>
    <form method="post" action="">
      <button class="btn btn-outline-primary btn-lg" type="submit" name="logout">
        Logout (<?= htmlspecialchars($_SESSION['user']); ?>)
      </button>
    </form>
  <?php endif; ?>

</div>

<?php include $homepath . "/views/footer.php"; ?>
