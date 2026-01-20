<?php
session_start();
if (!isset($_SESSION['user'])) {
    include $_SERVER['DOCUMENT_ROOT'] . "/views/header.php";
    ?>
    <form method="post" action="../controllers/login.php">
        <input type="text" name="username" required placeholder="Username">
        <input type="password" name="password" required placeholder="Password">
        <button class="submitbtn" type="submit" name="login"><?= "Log in" ?></button>
    </form>
<?php
    die();
} else {
    header('Location: ' . "/views/startpage.php");
}

?>