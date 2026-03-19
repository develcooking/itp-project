<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/middleware/startSession.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";

// Blocked-Check für eingeloggte Nutzer (nicht auf der Login-Seite selbst)
$_headerPage = basename($_SERVER['PHP_SELF'], '.php');
if (!empty($_SESSION['userId']) && $_headerPage !== 'loginsite') {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/models/User.php';
    $headerUser = new User($conn);
    if ($headerUser->getById($_SESSION['userId'])) {
         // Abgelaufene temporäre Sperren automatisch aufheben
        $headerUser->clearExpiredBlock($_SESSION['userId']);
        $headerUser->getById($_SESSION['userId']);

        if ($headerUser->getIsBlocked()) {
            session_unset();
            session_destroy();
            header('Location: /views/loginsite.php?blocked=permanent');
            exit;
        } elseif ($headerUser->getBlockedUntil() !== null) {
            $until = urlencode(date('d.m.Y \u\m H:i \U\h\r', strtotime($headerUser->getBlockedUntil())));
            session_unset();
            session_destroy();
            header('Location: /views/loginsite.php?blocked=temp&until=' . $until);
            exit;
        }
    }
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
<meta name="csrf-token" content="<?php echo getCsrfToken(); ?>">
<script src="/resources/js/csrf.js"></script>
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
    <!-- Desktop links -->
    <div class="d-none d-lg-flex flex-grow-1">
        <a href="/controllers/startpage.php" class="nav-btn-primary border-end <?= $current_page==='startpage'?'current':'' ?>">Startseite</a>
        <a href="/views/appointmentManagement.php" class="nav-btn-primary border-end <?= $current_page==='appointmentManagement'?'current':'' ?>">Termine</a>
        <a href="/views/forum.php" class="nav-btn-primary border-end <?= $current_page==='forum'?'current':'' ?>">Forum</a>
        <?php if ($_SESSION['role'] === 'Admin'): ?>
        <a href="/views/adminPage.php" class="nav-btn-primary border-end <?= $current_page==='adminPage'?'current':'' ?>">Benutzerverwaltung</a>
        <a href="/views/adminJobs.php" class="nav-btn-primary border-end <?= $current_page==='adminJobs'?'current':'' ?>">Berufsbereiche</a>
        <?php endif; ?>
    </div>
    <!-- Desktop account dropdown -->
    <div class="btn-group nav-logout-form d-none d-lg-flex" role="group">
        <a href="/controllers/profile.php" class="btn rounded-0 nav-account-btn btn-outline-primary">
            <i class="bi bi-person-circle me-1"></i> Account
        </a>
        <button type="button" class="btn btn-outline-primary rounded-0 dropdown-toggle dropdown-toggle-split nav-account-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="visually-hidden">Menü öffnen</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end nav-account-dropdown">
            <li>
                <form method="post" action="/controllers/login.php" class="m-0">
                    <?php echo getCsrfTokenInput(); ?>
                    <button type="submit" name="logout" class="dropdown-item nav-logout-btn">Abmelden</button>
                </form>
            </li>
        </ul>
    </div>
</nav>
<?php endif; ?>

    <!-- Mobile hamburger button -->
    <button class="btn btn-default offcanvas-toggle d-lg-none ms-auto me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileOffcanvas" aria-controls="mobileOffcanvas">
        <span class="navbar-toggler-icon"></span>
    </button>

<!-- Mobile Offcanvas Menu -->
<!-- Mobile Offcanvas Menu (right side) -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="mobileOffcanvas" aria-labelledby="mobileOffcanvasLabel">
  <div class="offcanvas-header justify-content-between">
    <h5 class="offcanvas-title" id="mobileOffcanvasLabel">Menü</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Schließen"></button>
  </div>
  <div class="offcanvas-body d-flex flex-column">
    <a href="/controllers/startpage.php" class="nav-btn-primary mb-2 <?= $current_page==='startpage'?'current':'' ?>">Startseite</a>
    <a href="/views/appointmentManagement.php" class="nav-btn-primary mb-2 <?= $current_page==='appointmentManagement'?'current':'' ?>">Termine</a>
    <a href="/views/forum.php" class="nav-btn-primary mb-2 <?= $current_page==='forum'?'current':'' ?>">Forum</a>
    <?php if ($_SESSION['role'] === 'Admin'): ?>
      <a href="/views/adminPage.php" class="nav-btn-primary mb-2 <?= $current_page==='adminPage'?'current':'' ?>">Benutzerverwaltung</a>
      <a href="/views/adminJobs.php" class="nav-btn-primary mb-2 <?= $current_page==='adminJobs'?'current':'' ?>">Berufsbereiche</a>
    <?php endif; ?>
    
    <a href="/controllers/profile.php" class="nav-btn-primary mb-2 <?= $current_page==='profile'?'current':'' ?>">Account</a>
    
    <!-- Spacer so account/logout are at bottom -->
    <div class="mt-auto mobile_logout">
      <form method="post" action="/controllers/login.php">
          <?php echo getCsrfTokenInput(); ?>
          <button type="submit" name="logout" class="btn nav-logout-btn w-100">Abmelden</button>
      </form>
    </div>
  </div>
</div>