<?php include $_SERVER['DOCUMENT_ROOT'] . "/controllers/login.php";?>
<?php
// Get the current page name from the URL
$current_page = basename($_SERVER['REQUEST_URI'], '.php');
if (empty($current_page) || $current_page === '') {
    $current_page = 'index';
}
?>

<!DOCTYPE html>
<html land="de-DE">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/resources/imgs/icon.png">
    <title>Ausbildungsportal.net</title>
    <link rel="stylesheet" href="../resources/css/bootstrap.min.css">
    <link rel="stylesheet" href="../resources/css/bootstrap-icons.css">
    <link rel="stylesheet" href="/resources/css/datatables.min.css">
    <link rel="stylesheet" type="text/css" href="/resources/css/styles.css">
</head>
<body>
     <script src="/resources/js/datatables.min.js"></script>
    <div class="header">
        <a class="hat" href="../index.php" tabindex="-1">
            <h2>Ausbildungsportal.net</h2>
        </a>

        <div id="headerspacer"></div>
    </div>

    <?php if (!empty($_SESSION['userId'])): ?>
    <nav class="navbar">
        <a href="/views/startpage.php" class="nav-btn <?= $current_page === 'startpage' ? 'current' : ''; ?>">Startseite</a>
        <!-- <a href="/views/loginsite.php" class="nav-btn <?=  $current_page === 'loginsite' ? 'current' : ''; ?>">Login</a> -->
        <a href="/views/appointmentManagement.php" class="nav-btn <?=  $current_page === 'appointmentManagement' ? 'current' : ''; ?>">Termine</a>
        <a href="/controllers/forum.php" class="nav-btn <?=  $current_page === 'forum' ? 'current' : ''; ?>">Forum</a>
        <?php if ($_SESSION['role'] === 'Admin'): ?>   <!-- Nur Admins dürfen Adminpage sehen  !-->
        <a href="/views/adminPage.php" class="nav-btn <?= $current_page === 'adminPage' ? 'current' : ''; ?>">Admin</a>
         <?php endif; ?>
        <form method="post" action="/controllers/login.php" class="nav-logout-form">
            <button type="submit" name="logout" class="nav-btn">Abmelden</button>
        </form>
    </nav>
    <?php endif; ?>

<div class="main-container">
        <!--  -->






