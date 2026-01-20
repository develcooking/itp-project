<?php include $_SERVER['DOCUMENT_ROOT'] . "/controllers/login.php";?>

<!DOCTYPE html>
<html land="de-DE">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../resources/imgs/icon.png">
    <title><?=$site_name?></title>
    <link rel="stylesheet" type="text/css" href="../styles.css" />
</head>
<body>
    <div class="header">
        <a class="hat" href="../index.php" tabindex="-1">
            <h2><?=$site_name?></h2>
        </a>

        <div id="headerspacer"></div>

        <div class="login">
            <?php if (!isset($_SESSION['user'])): ?>
                <form method="post" action="">
                    <input type="text" name="username" required placeholder="Username">
                    <input type="password" name="password" required placeholder="Password">
                    <button class="submitbtn" type="submit" name="login"><?= "Log in"?></button>
                </form>
                <?php if (isset($error)): ?>
                    <p style="color: red;"><?=$error; ?></p>
                <?php endif; ?>
            <?php else: ?>
                <form method="post" action="">
                    <button class="submitbtn" type="submit" name="logout" <?php if ($isAdmin == TRUE){echo 'style="color: red"';}?>><?= "Logout"?> (<?php echo htmlspecialchars($_SESSION['user']); ?>)</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

<div class="main-container">
  <?php include "sidebar.php" ?>
  <div class="content"></div>