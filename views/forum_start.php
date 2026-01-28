<?php
include $homepath . "/views/header.php";
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Forum – Berufsbereiche</title>
</head>
<body>

<?php if(empty($bereiche)): ?>
    <p>Keine Berufsbereiche gefunden.</p>
<?php else: ?>

    <?php foreach ($bereiche as $bereich): ?>

    <div class="card card-forum m-2">
      <div class="card-body">
        <h5 class="card-title"> <?= htmlspecialchars($bereich) ?> </h5>
        <!--    <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card’s content.</p> -->
        <a href="/forum?bereich_id=<?= urlencode($bereich) ?>" class="card-link">Zu den Themen</a>
      </div>
    </div>

    <?php endforeach; ?>

<?php endif; ?>

</body>
</html>