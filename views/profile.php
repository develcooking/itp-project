<?php
include $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
include $_SERVER['DOCUMENT_ROOT'] . "/views/header.php";

if (empty($_SESSION['userId'])) {
    header("Location: /views/loginsite.php");
    exit();
}

if (!isset($user)) {
    require_once $_SERVER['DOCUMENT_ROOT'] . "/models/User.php";
    $user = new User($conn);
    $user->getById($_SESSION['userId']);
}
?>

    <div class="container min-vh-100 d-flex justify-content-center align-items-center my-4">
        <div class="row w-100 justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-6">
                <div class="card bg-light shadow p-4 text-center">

                    <h2 class="fw-bold mb-2">Mein Profil</h2>
                    <p class="text-muted mb-4">Bearbeiten Sie Ihre persönlichen Daten.</p>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success" role="alert">
                            <?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errors['general'])): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= htmlspecialchars($errors['general']) ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="/controllers/profile.php">

                        <div class="form-floating mb-3">
                            <input
                            type="text"
                            name="userName"
                            id="userName"
                            class="form-control"
                            placeholder="Benutzername"
                            value="<?= htmlspecialchars($user->getUserName()) ?>"
                            required>
                            <label for="userName">Benutzername</label>
                            <?php if (!empty($errors['userName'])): ?>
                                <div class="invalidUserName">
                                    <?= htmlspecialchars($errors['userName']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-floating mb-3">
                            <input
                            type="text"
                            name="firstName"
                            id="firstName"
                            class="form-control"
                            placeholder="Vorname"
                            value="<?= htmlspecialchars($user->getFirstName()) ?>"
                            required>
                            <label for="firstName">Vorname</label>
                            <?php if (!empty($errors['firstName'])): ?>
                                <div class="invalidUserName">
                                    <?= htmlspecialchars($errors['firstName']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-floating mb-3">
                            <input
                            type="text"
                            name="lastName"
                            id="lastName"
                            class="form-control"
                            placeholder="Nachname"
                            value="<?= htmlspecialchars($user->getLastName()) ?>"
                            required>
                            <label for="lastName">Nachname</label>
                            <?php if (!empty($errors['lastName'])): ?>
                                <div class="invalidUserName">
                                    <?= htmlspecialchars($errors['lastName']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-floating mb-3">
                            <input
                            type="email"
                            name="email"
                            id="email"
                            class="form-control"
                            placeholder="E-Mail-Adresse"
                            value="<?= htmlspecialchars($user->getEmail()) ?>"
                            required>
                            <label for="email">E-Mail-Adresse</label>
                            <?php if (!empty($errors['email'])): ?>
                                <div class="invalidUserName">
                                    <?= htmlspecialchars($errors['email']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <button class="btn btn-outline-primary btn-lg w-100 mt-3" type="submit" name="saveProfile">
                            Speichern
                        </button>

                        <a href="/views/startpage.php" class="btn btn-outline-secondary btn-lg w-100 mt-2">
                            Abbrechen
                        </a>

                    </form>

                </div>
            </div>
        </div>
    </div>

<?php include $homepath . "/views/footer.php"; ?>
