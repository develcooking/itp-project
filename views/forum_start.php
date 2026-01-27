<?php
include $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
include $homepath . "/views/header.php";

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Forum – Berufsbereiche</title>
</head>
<body>

<?php foreach ($bereiche as $bereich): ?>

<div class="card" style="width: 18rem;">
  <div class="card-body">
    <h5 class="card-title"> <?=  htmlspecialchars($bereich['name']) ?> </h5>
<!--    <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card’s content.</p> -->
    <a href="/forum?bereich_id=<?= $bereich['name'] ?>" class="card-link">Zu den Themen</a>
  </div>
</div>

<?php endforeach; ?>
</body>
</html>
