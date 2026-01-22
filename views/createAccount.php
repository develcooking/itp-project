<?php
include $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
include $homepath . "/views/header.php";
?>

<div class="center-card-container">

  <?php if (!isset($_SESSION['user'])): ?>
    <div class="center-card card">

      <h2 class="fw-bold mb-2">Registrieren</h2>
      <p class="text-muted mb-4">Bitte geben Sie Ihre Daten ein.</p>

      <form method="post" action="../controllers/createNewUser.php">

        <div class="form-floating mb-3">
          <input type="text" name="username" class="form-control" id="username" placeholder="Benutzername" required>
          <label for="username">Benutzername</label>
        </div>

        <div class="form-floating mb-3">
          <input type="text" name="FirstName" class="form-control" id="FirstName" placeholder="Vorname" required>
          <label for="FirstName">Vorname</label>
        </div>

        <div class="form-floating mb-3">
          <input type="text" name="LastName" class="form-control" id="LastName" placeholder="Nachname" required>
          <label for="LastName">Nachname</label>
        </div>

        <div class="form-floating mb-3">
          <input type="email" name="Email" class="form-control" id="Email" placeholder="E-Mail-Adresse" required>
          <label for="Email">E-Mail-Adresse</label>
        </div>

        <div class="form-floating mb-3">
          <input type="password" name="Passwort" class="form-control" id="Passwort" placeholder="Passwort" required>
          <label for="Passwort">Passwort</label>
        </div>
        <!-- Sicherheitsfrage -->
        <div class="mb-3">
          <select name="SecurityQuestion" id="SecurityQuestion" class="form-select" required>
            <option value="" disabled selected>Bitte Sicherheitsfrage auswählen</option>
            <option value="pet">Wie hieß dein erstes Haustier?</option>
            <option value="school">Wie hieß deine Grundschule?</option>
            <option value="city">In welcher Stadt wurdest du geboren?</option>
            <option value="mother_maiden">Wie lautet der Mädchenname deiner Mutter?</option>
            <option value="first_car">Was war dein erstes Auto?</option>
            <option value="nickname">Wie lautete dein Kindheitsspitzname?</option>
          </select>
        </div>

        <!-- Antwort -->
        <div class="form-floating mb-3">
          <input type="text" name="SecurityAnswer" class="form-control" id="SecurityAnswer" placeholder="Antwort" required>
          <label for="SecurityAnswer">Antwort auf die Sicherheitsfrage</label>
        </div>

        <div class="mb-4">
            <select name="art" id="art" class="form-select" required>
                <option value="" disabled selected>Bitte Rolle wählen</option>
                <option value="lehrer">Lehrkraft</option>
                <option value="ausbilder">Ausbilder</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <div class="form-check mt-3">
          <input class="form-check-input" type="checkbox" id="datenschutzCheck" name="datenschutz" required>
          <label class="form-check-label" for="datenschutzCheck">
            Ich habe die 
            <a href="#" data-bs-toggle="modal" data-bs-target="#datenschutzModal">
              Datenschutzerklärung
            </a> 
            gelesen und akzeptiere sie.
          </label>
        </div>
          
        <button class="btn btn-outline-primary btn-lg w-100 mt-4" type="submit" name="createAccount">
          Registrieren
        </button>

      </form>

      <hr class="my-4">

      <p class="mb-0">
        Bereits einen Account?
        <a href="loginsite.php" class="fw-bold text-decoration-none">
          Hier anmelden
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

<!-- ================= DATENSCHUTZ MODAL ================= -->
<div class="modal fade" id="datenschutzModal" tabindex="-1" aria-labelledby="datenschutzModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="datenschutzModalLabel">Datenschutzerklärung</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <div class="modal-body">
        <p><strong>1. Allgemeine Hinweise</strong></p>
        <p>Der Schutz deiner persönlichen Daten ist uns wichtig.</p>

        <p><strong>2. Datenerhebung</strong></p>
        <p>Personenbezogene Daten werden nur erhoben, wenn du diese freiwillig angibst.</p>

        <p><strong>3. Zweck</strong></p>
        <p>Die Verarbeitung erfolgt zur Erstellung und Verwaltung deines Benutzerkontos.</p>

        <p><strong>4. Weitergabe</strong></p>
        <p>Es erfolgt keine Weitergabe an Dritte ohne deine ausdrückliche Zustimmung.</p>

        <p><strong>5. Rechte</strong></p>
        <p>Du hast jederzeit das Recht auf Auskunft, Löschung und Berichtigung deiner Daten.</p>
      </div>
      
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Schließen</button>
      </div>

    </div>
  </div>
</div>
<?php include $homepath . "/views/footer.php"; ?>
