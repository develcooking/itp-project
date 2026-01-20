<?php
include $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
include $homepath . "/views/header.php";
?>
<div class="login">
    <?php if (!isset($_SESSION['user'])): ?>
        <form method="post" action="../controllers/login.php">
            <input type="text" name="email" required placeholder="E-Mail Adress">
            <input type="password" name="password" required placeholder="Password">
            <button class="submitbtn" type="submit" name="login">Log in</button>
        </form>
        <br>
        <hr>
        <a href="createAccount.php">Have no Account yet? Create one</a>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?= $error; ?></p>
        <?php endif; ?>
    <?php else: ?>
        <form method="post" action="">
            <button class="submitbtn" type="submit" name="logout"><?= "Logout" ?> (<?= htmlspecialchars($_SESSION['user']); ?>)</button>
        </form>
    <?php endif; ?>
</div>