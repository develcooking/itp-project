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

if (!isset($profileStats) || !is_array($profileStats)) {
    $profileStats = $user->getProfileStats();
}

$profileStats = array_merge([
    'topicCount' => 0,
    'postCount' => 0,
    'reactionPositiveCount' => 0,
    'reactionNegativeCount' => 0
], $profileStats);
?>

    <div class="container min-vh-100 d-flex justify-content-center align-items-center my-4">
        <div class="row w-100 justify-content-center align-items-stretch g-4">
            <div class="col-12 col-lg-8">
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

                    <form method="post" action="/controllers/profile.php" enctype="multipart/form-data">
                        <?php echo getCsrfTokenInput(); ?>
                        <input type="hidden" name="removeProfileImage" id="removeProfileImage" value="0">

                        <div class="mb-3">
                            <?php $hasProfileImage = $user->hasProfileImage(); ?>
                            <img
                                id="profileImagePreview"
                                src="<?= $hasProfileImage ? '/controllers/profileImage.php?v=' . time() : '' ?>"
                                alt="Aktuelles Profilbild"
                                class="rounded-circle border profile-image-preview <?= $hasProfileImage ? '' : 'd-none' ?>">
                            <svg
                                id="profileImagePlaceholder"
                                class="profile-image-placeholder-icon <?= $hasProfileImage ? 'd-none' : '' ?>"
                                viewBox="0 0 16 16"
                                fill="currentColor"
                                role="img"
                                aria-label="Standard Profilbild">
                                <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"></path>
                                <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1"></path>
                            </svg>
                        </div>

                        <div class="mb-3 text-start">
                            <input
                                type="file"
                                name="profileImage"
                                id="profileImage"
                                class="d-none"
                                accept="image/jpeg,image/png,image/webp">
                            <div class="d-flex flex-column flex-md-row gap-2 align-items-stretch">
                                <label for="profileImage" class="form-control d-flex align-items-center justify-content-start gap-2 flex-grow-1 mb-0">
                                    <span class="btn btn-outline-primary btn-sm">Datei auswahlen</span>
                                    <span id="profileImageFileName" class="text-muted">Profilbild hochladen (JPG, PNG, WEBP, max. 2 MB)</span>
                                </label>
                                <?php if ($hasProfileImage): ?>
                                    <button class="btn btn-outline-danger" type="button" id="removeProfileImageButton">
                                        Profilbild löschen
                                    </button>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($errors['profileImage'])): ?>
                                <div class="invalidUserName mt-1">
                                    <?= htmlspecialchars($errors['profileImage']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

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

                        <div class="form-floating mb-3">
                            <input
                            type="text"
                            id="role"
                            class="form-control"
                            placeholder="Rolle"
                            value="<?= htmlspecialchars($user->getRole()) ?>"
                            disabled>
                            <label for="role">Rolle</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input
                            type="text"
                            name="school_company"
                            id="school_company"
                            class="form-control"
                            placeholder="Schule / Betrieb"
                            value="<?= htmlspecialchars($user->getSchoolCompany() ?? '') ?>">
                            <label for="school_company">Schule / Betrieb</label>
                        </div>

                        <div class="form-check form-switch text-start mb-3">
                            <input type="hidden" name="sendNotification" value="0">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                role="switch"
                                id="sendNotification"
                                name="sendNotification"
                                value="1"
                                <?= $user->getSendNotification() ? 'checked' : '' ?>>
                            <label class="form-check-label" for="sendNotification">
                                Benachrichtigungen für neue Beiträge in meinen Themen erhalten
                            </label>
                        </div>

                        <button class="btn btn-outline-primary btn-lg w-100 mt-3" type="submit" name="saveProfile">
                            Speichern
                        </button>

                        <a href="/controllers/startpage.php" class="btn btn-outline-secondary btn-lg w-100 mt-2">
                            Abbrechen
                        </a>

                    </form>

                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card bg-light shadow h-100 p-4">
                    <h3 class="fw-bold mb-2">Statistik</h3>
                    <p class="text-muted mb-4">Übersicht Ihrer Aktivitat im Forum.</p>

                    <div class="d-flex flex-column gap-3">
                        <div class="border rounded-3 p-3 bg-white">
                            <div class="text-muted small">Erstellte Themen</div>
                            <div class="fs-3 fw-bold"><?= (int)($profileStats['topicCount'] ?? 0) ?></div>
                        </div>

                        <div class="border rounded-3 p-3 bg-white">
                            <div class="text-muted small">Erstellte Beitrage</div>
                            <div class="fs-3 fw-bold"><?= (int)($profileStats['postCount'] ?? 0) ?></div>
                        </div>

                        <div class="border rounded-3 p-3 bg-white">
                            <div class="text-muted small">Erhaltene positive Reaktionen</div>
                            <div class="fs-3 fw-bold\"><?= (int)($profileStats['reactionPositiveCount'] ?? 0) ?></div>
                        </div>

                        <div class="border rounded-3 p-3 bg-white">
                            <div class="text-muted small">Erhaltene negative Reaktionen</div>
                            <div class="fs-3 fw-bold\"><?= (int)($profileStats['reactionNegativeCount'] ?? 0) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include $homepath . "/views/footer.php"; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const profileImageInput = document.getElementById('profileImage');
    const profileImageFileName = document.getElementById('profileImageFileName');
    const profileImagePreview = document.getElementById('profileImagePreview');
    const profileImagePlaceholder = document.getElementById('profileImagePlaceholder');
    const removeProfileImageInput = document.getElementById('removeProfileImage');
    const removeProfileImageButton = document.getElementById('removeProfileImageButton');
    let currentPreviewObjectUrl = null;

    if (!profileImageInput || !profileImageFileName || !profileImagePreview || !profileImagePlaceholder || !removeProfileImageInput) {
        return;
    }

    profileImageInput.addEventListener('change', function () {
        if (profileImageInput.files && profileImageInput.files.length > 0) {
            const selectedFile = profileImageInput.files[0];
            removeProfileImageInput.value = '0';

            profileImageFileName.textContent = selectedFile.name;
            profileImageFileName.classList.remove('text-muted');

            if (currentPreviewObjectUrl) {
                URL.revokeObjectURL(currentPreviewObjectUrl);
            }

            currentPreviewObjectUrl = URL.createObjectURL(selectedFile);
            profileImagePreview.src = currentPreviewObjectUrl;
            profileImagePreview.classList.remove('d-none');
            profileImagePlaceholder.classList.add('d-none');
        } else {
            profileImageFileName.textContent = 'Profilbild hochladen (JPG, PNG, WEBP, max. 2 MB)';
            profileImageFileName.classList.add('text-muted');

            if (currentPreviewObjectUrl) {
                URL.revokeObjectURL(currentPreviewObjectUrl);
                currentPreviewObjectUrl = null;
            }
        }
    });

    if (removeProfileImageButton) {
        removeProfileImageButton.addEventListener('click', function () {
            removeProfileImageInput.value = '1';
            profileImageInput.value = '';

            if (currentPreviewObjectUrl) {
                URL.revokeObjectURL(currentPreviewObjectUrl);
                currentPreviewObjectUrl = null;
            }

            profileImagePreview.classList.add('d-none');
            profileImagePlaceholder.classList.remove('d-none');
            profileImageFileName.textContent = 'Profilbild wird beim Speichern gelöscht';
            profileImageFileName.classList.remove('text-muted');
        });
    }
});
</script>
