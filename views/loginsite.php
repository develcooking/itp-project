<div class="login">
    <?php if (!isset($_SESSION['user'])): ?>
        <form method="post" action="">
            <input type="text" name="username" required placeholder="Username">
            <input type="password" name="password" required placeholder="Password">
            <button class="submitbtn" type="submit" name="login"><?= "Log in" ?></button>
        </form>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?= $error; ?></p>
        <?php endif; ?>
    <?php else: ?>
        <form method="post" action="">
            <button class="submitbtn" type="submit" name="logout" <?php if ($isAdmin == TRUE) {
                echo 'style="color: red"';
            } ?>><?= "Logout" ?> (<?php echo htmlspecialchars($_SESSION['user']); ?>)</button>
        </form>
    <?php endif; ?>
</div>