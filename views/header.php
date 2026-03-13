<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";

if (!isset($_SESSION)) {
    session_start();
}

// Get the current page name from the URL
$current_page = basename($_SERVER['REQUEST_URI'], '.php');
if (empty($current_page) || $current_page === '') {
    $current_page = 'index';
}
?>

<!DOCTYPE html>
<html lang="de-DE">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/resources/imgs/icon.png">
    <title>Ausbildungsportal.net</title>
    <link rel="stylesheet" href="../resources/css/bootstrap.min.css">
    <link rel="stylesheet" href="../resources/css/bootstrap-icons.css">
    <link rel="stylesheet" type="text/css" href="/resources/css/styles.css">
</head>
<body>
<?php if (empty($_SESSION['userId'])): ?>
    <!-- Header (logo only) for not logged-in users -->
    <header class="header d-flex align-items-center justify-content-between px-3">
        <a class="hat" href="../index.php">
            <?php
            $sitesWithTransperency = ['loginsite', 'createAccount', 'passwordReset', 'passwordForgot'];
            if (in_array($current_page, $sitesWithTransperency)):
            ?>
            <img src="/resources/imgs/logo-transperent.png" alt="Ausbildungsportal.net Logo" class="site-logo">
            <?php else: ?>
            <img src="/resources/imgs/logo.jpeg" alt="Ausbildungsportal.net Logo" class="site-logo">
            <?php endif; ?>
        </a>
        <div id="headerspacer"></div>
    </header>
<?php endif; ?>

<!-- Navbar (always visible when logged in) -->
<?php if (!empty($_SESSION['userId'])): ?>
<nav class="navbar justify-content-start border-2 border-bottom p-0">
    <a href="../index.php" class="logo border-end">
        <img src="/resources/imgs/logo.jpeg" alt="Ausbildungsportal.net Logo" class="nav-logo">
    </a>
    <a href="/views/startpage.php" class="nav-btn-primary border-end <?= $current_page === 'startpage' ? 'current' : ''; ?>">Startseite</a>
    <a href="/views/appointmentManagement.php" class="nav-btn-primary border-end <?= $current_page === 'appointmentManagement' ? 'current' : ''; ?>">Termine</a>
    <a href="/views/forum.php" class="nav-btn-primary border-end <?= $current_page === 'forum' ? 'current' : ''; ?>">Forum</a>
    <?php if ($_SESSION['role'] === 'Admin'): ?>
        <a href="/views/adminPage.php" class="nav-btn-primary border-end <?= $current_page === 'adminPage' ? 'current' : ''; ?>">Benutzerverwaltung</a>
        <a href="/views/adminJobs.php" class="nav-btn-primary border-end <?= $current_page === 'adminJobs' ? 'current' : ''; ?>">Berufsbereiche</a>
        <?php endif; ?>
    <div class="btn-group nav-logout-form" role="group">
        <a href="/views/profile.php" class="btn btn-outline-primary rounded-0 nav-account-btn">
            <i class="bi bi-person-circle me-1"></i> Account
        </a>
        <button type="button" class="btn btn-outline-primary rounded-0 dropdown-toggle dropdown-toggle-split nav-account-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="visually-hidden">Menü öffnen</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end nav-account-dropdown">
            <li>
                <form method="post" action="/controllers/login.php" class="m-0">
                    <button type="submit" name="logout" class="dropdown-item nav-logout-btn">Abmelden</button>
                </form>
            </li>
        </ul>
    </div>
</nav>
<?php endif; ?>

<main class="main-container align-items-center">







