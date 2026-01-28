<?php
include $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
include $homepath . "/views/header.php";
?>

    <div class="container min-vh-100 d-flex justify-content-center align-items-center my-4">
        <div class="row w-100 justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-6">

                <?php if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']): ?>
                    <div class="card bg-light shadow p-4 text-center">

                        <h2 class="fw-bold mb-2">Registrieren</h2>
                        <p class="text-muted mb-4">Bitte geben Sie Ihre Daten ein.</p>

                        <form method="post" action="../controllers/register.php">

                            <div class="form-floating mb-3">
                                <input type="text" name="userName" id="userName" class="form-control" placeholder="Benutzername" required>
                                <label for="userName">Benutzername</label>
                            </div>

                            <div class="form-floating mb-3">
                                <input type="text" name="firstName" id="firstName" class="form-control" placeholder="Vorname" required>
                                <label for="firstName">Vorname</label>
                            </div>

                            <div class="form-floating mb-3">
                                <input type="text" name="lastName" id="lastName" class="form-control" placeholder="Nachname" required>
                                <label for="lastName">Nachname</label>
                            </div>

                            <div class="form-floating mb-3">
                                <input type="email" name="email" id="email" class="form-control" placeholder="E-Mail-Adresse" required>
                                <label for="email">E-Mail-Adresse</label>
                            </div>
                            <div class="form-floating mb-3 position-relative password-container">
                                <input type="password" name="password" class="form-control pe-5" id="password" placeholder="Passwort" required>
                                <label for="password">Passwort</label>
                                <span class="password-eye">
                                    <img id="eyeOpen" src="/resources/imgs/eye.svg" alt="Show password" width="16" height="16" style="cursor:pointer;">
                                    <img id="eyeSlash" src="/resources/imgs/eye-slash.svg" alt="Hide password" width="16" height="16" style="cursor:pointer; display:none;">
                                </span>
                            </div>
                            <div class="form-floating mb-3 position-relative password-container">
                                <input type="password" name="password" class="form-control pe-5" id="confirmPassword" placeholder="Passwort" required>
                                <label for="confirmpassword">Passwort bestätigen</label>
                                <span class="password-eye">
                                    <img id="eyeOpenConfirm" src="/resources/imgs/eye.svg" alt="Show password" width="16" height="16" style="cursor:pointer;">
                                    <img id="eyeSlashConfirm" src="/resources/imgs/eye-slash.svg" alt="Hide password" width="16" height="16" style="cursor:pointer; display:none;">
                                </span>
                            </div>


                            <div class="mb-3">
                                <select name="securityQuestion" class="form-select" required>
                                    <option value="" disabled selected>Bitte Sicherheitsfrage auswählen</option>
                                    <option value="pet">Wie hieß dein erstes Haustier?</option>
                                    <option value="school">Wie hieß deine Grundschule?</option>
                                    <option value="city">In welcher Stadt wurdest du geboren?</option>
                                    <option value="mother_maiden">Wie lautet der Mädchenname deiner Mutter?</option>
                                    <option value="first_car">Was war dein erstes Auto?</option>
                                    <option value="nickname">Wie lautete dein Kindheitsspitzname?</option>
                                </select>
                            </div>

                            <div class="form-floating mb-3">
                                <input type="text" name="securityAnswer" class="form-control" placeholder="Antwort" required>
                                <label for="securityAnswer">Antwort auf die Sicherheitsfrage</label>
                            </div>

                            <div class="mb-4">
                                <select name="role" class="form-select" required>
                                    <option value="" disabled selected>Bitte Rolle wählen</option>
                                    <option value="Lehrer">Lehrkraft</option>
                                    <option value="Ausbilder">Ausbilder</option>
                                    <option value="Admin">Admin</option>
                                </select>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="datenschutzCheck" name="datenschutz" required>
                                <label class="form-check-label" for="datenschutzCheck">
                                    Ich habe die
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#datenschutzModal">
                                        Datenschutzerklärung
                                    </a>
                                    gelesen und akzeptiere sie.
                                </label>
                            </div>

                            <button class="btn btn-outline-primary btn-lg w-100 mt-3" type="submit" name="createAccount">
                                Registrieren
                            </button>

                        </form>

                        <hr class="my-4">

                        <p class="mb-0">
                            Bereits einen Account?
                            <a href="loginsite.php" class="fw-bold text-decoration-none">Hier anmelden</a>
                        </p>

                    </div>
                <?php else: ?>
                    <div class="card bg-light shadow p-4 text-center">
                        <h2 class="fw-bold mb-3">Bereits angemeldet</h2>
                        <p class="text-muted mb-4">Sie sind bereits als <?= htmlspecialchars($_SESSION['userName']); ?> angemeldet.</p>
                        <a href="/views/dashboard.php" class="btn btn-primary btn-lg w-100 mb-2">Zum Dashboard</a>
                        <form method="post" action="../controllers/login.php">
                            <button class="btn btn-outline-danger btn-lg w-100" type="submit" name="logout">
                                Abmelden
                            </button>
                        </form>
                    </div>
                <?php endif; ?>

            </div>
        </div>
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
<script src="/resources/js/createAccount.js"></script>
<?php include $homepath . "/views/footer.php"; ?>