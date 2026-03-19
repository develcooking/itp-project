<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/middleware/startSession.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/User.php";

if (empty($_SESSION['userId'])) {
    header("Location: /views/loginsite.php");
    exit();
}

$errors = [];
$success = '';

$allowedImageMimes = ['image/jpeg', 'image/png', 'image/webp'];
$maxProfileImageBytes = 2 * 1024 * 1024;

$user = new User($conn);
$user->getById($_SESSION['userId']);
$profileStats = $user->getProfileStats();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['saveProfile'])) {
    $userName = htmlspecialchars(trim($_POST['userName'] ?? ''));
    $firstName = htmlspecialchars(trim($_POST['firstName'] ?? ''));
    $lastName = htmlspecialchars(trim($_POST['lastName'] ?? ''));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $schoolCompany = htmlspecialchars(trim($_POST['school_company'] ?? ''));
    $sendNotification = isset($_POST['sendNotification']) && $_POST['sendNotification'] === '1';
    $removeProfileImage = isset($_POST['removeProfileImage']) && $_POST['removeProfileImage'] === '1';
    $profileImageData = null;
    $profileImageMime = null;

    if ($removeProfileImage && !$user->canStoreProfileImage()) {
        $errors['profileImage'] = 'Profilbild konnte nicht gelöscht werden.';
    }

    if (isset($_FILES['profileImage']) && ($_FILES['profileImage']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        if (!$user->canStoreProfileImage()) {
            $errors['profileImage'] = 'Profilbild konnte nicht gespeichert werden.';
        }

        $uploadError = $_FILES['profileImage']['error'];

        if (empty($errors['profileImage']) && $uploadError !== UPLOAD_ERR_OK) {
            $errors['profileImage'] = 'Fehler beim Upload des Profilbildes.';
        } elseif (empty($errors['profileImage']) && ($_FILES['profileImage']['size'] ?? 0) > $maxProfileImageBytes) {
            $errors['profileImage'] = 'Das Profilbild darf maximal 2 MB groß sein.';
        } elseif (empty($errors['profileImage'])) {
            $tmpName = $_FILES['profileImage']['tmp_name'] ?? '';

            if (!is_uploaded_file($tmpName)) {
                $errors['profileImage'] = 'Ungültiger Datei-Upload erkannt.';
            } else {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $detectedMime = $finfo ? finfo_file($finfo, $tmpName) : false;
                if ($finfo) {
                    finfo_close($finfo);
                }

                $imageType = @exif_imagetype($tmpName);
                $allowedExifTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP];

                if ($detectedMime === false || !in_array($detectedMime, $allowedImageMimes, true) || !in_array($imageType, $allowedExifTypes, true)) {
                    $errors['profileImage'] = 'Nur JPG, PNG oder WEBP sind erlaubt.';
                } else {
                    $fileData = file_get_contents($tmpName);
                    if ($fileData === false || $fileData === '') {
                        $errors['profileImage'] = 'Das Profilbild konnte nicht gelesen werden.';
                    } else {
                        $profileImageData = $fileData;
                        $profileImageMime = $detectedMime;
                    }
                }
            }
        }
    }

    if (empty($userName)) {
        $errors['userName'] = 'Benutzername darf nicht leer sein!';
    }

    if (empty($firstName)) {
        $errors['firstName'] = 'Vorname darf nicht leer sein!';
    }

    if (empty($lastName)) {
        $errors['lastName'] = 'Nachname darf nicht leer sein!';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Bitte geben Sie eine gültige E-Mail-Adresse ein!';
    }

    if (empty($errors)) {
        if ($userName !== $user->getUserName() && $user->userNameExists($userName)) {
            $errors['userName'] = 'Dieser Benutzername ist bereits vergeben!';
        }

        if ($email !== $user->getEmail() && $user->emailExists($email)) {
            $errors['email'] = 'Diese E-Mail-Adresse ist bereits registriert!';
        }
    }

    if (empty($errors)) {
        $user->setUserName($userName);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setEmail($email);
        $user->setSchoolCompany($schoolCompany ?: null);
        $user->setSendNotification($sendNotification);

        if ($profileImageData !== null && $profileImageMime !== null) {
            $user->setProfileImage($profileImageData, $profileImageMime);
        } elseif ($removeProfileImage) {
            $user->setProfileImage(null, null);
        }

        if ($user->updateProfile()) {
            $_SESSION['userName'] = $userName;
            $_SESSION['firstName'] = $firstName;
            $_SESSION['lastName'] = $lastName;
            $_SESSION['email'] = $email;
            $_SESSION['sendNotification'] = $sendNotification ? 1 : 0;
            $success = 'Profil erfolgreich aktualisiert!';
        } else {
            $errors['general'] = 'Fehler beim Speichern. Bitte versuchen Sie es später erneut.';
        }
    }
}

require $_SERVER['DOCUMENT_ROOT'] . "/views/profile.php";
exit();
