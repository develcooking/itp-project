<?php
include $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
include $homepath . "/views/header.php";
?>
<div class="login">
    <?php if (!isset($_SESSION['user'])): ?>
        <form id="createAccountForm" method="post" action="../controllers/createNewUser.php">
            <p>Create Account</p>
            <input type="text" name="username" required placeholder="Username">
            <input type="text" name="vorname" required placeholder="Vorname">
            <input type="text" name="nachname" required placeholder="Nachname">
            <input type="text" name="email" required placeholder="E-Mail address">
            <input type="password" name="password" required placeholder="Password">
            <select name="art" required>
                <option value="leher">leher</option>
                <option value="ausbilder">ausbilder</option>
                <option value="admin">admin</option>
            </select>
            <button class="submitbtn" type="submit" name="createAccount">Create Account</button>
        </form>
    <?php else: ?>
        <form method="post" action="">
            <button class="submitbtn" type="submit" name="logout"><?= "Logout" ?> (<?= htmlspecialchars($_SESSION['user']); ?>)</button>
        </form>
    <?php endif; ?>
</div>