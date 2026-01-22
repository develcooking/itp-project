<?php
include $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
include $homepath . "/views/header.php";
?>
<div class="login">
    <?php if (!isset($_SESSION['user'])): ?>
        <form id="createAccountForm" method="post" action="../controllers/createNewUser.php">
            <p>Create Account</p>
            <input type="text" name="userName" required placeholder="Username">
            <input type="text" name="firstName" required placeholder="Vorname">
            <input type="text" name="lastName" required placeholder="Nachname">
            <input type="text" name="email" required placeholder="E-Mail address">
            <input type="password" name="password" required placeholder="Password">
            <select name="role" required>
                <option value="Lehrer">Lehrer</option>
                <option value="Ausbilder">Ausbilder</option>
                <option value="Admin">Admin</option>
            </select>
            <input type="text" name="securityAnswer" required placeholder="Security">
            <button class="submitbtn" type="submit" name="createAccount">Create Account</button>
        </form>
    <?php else: ?>
        <form method="post" action="">
            <button class="submitbtn" type="submit" name="logout"><?= "Logout" ?> (<?= htmlspecialchars($_SESSION['user']); ?>)</button>
        </form>
    <?php endif; ?>
</div>