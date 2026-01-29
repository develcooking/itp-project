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

 <h3> Berufsbereiche im Forum </h3>

<?php if(empty($bereiche)): ?>
    <p>Keine Berufsbereiche gefunden.</p>
<?php else: ?>

    <?php foreach ($bereiche as $bereich): ?>

      <?php echo(var_dump($bereich)); ?>

    <div class="card card-forum m-2">
      <div class="card-body">
        <h3 class="card-title"> <?= $bereich['name'] ?> </h3>

        <!-- <form action="../controllers/forum.php" method="post">
            <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card’s content.</p>
            <a href="/forum?bereich=<?= $bereich['jobId'] ?>" class="card-link">Zu den Themen</a>
            <a href="/forum_topics<?= $bereich['jobId'] ?>" class="card-link">Zu den Themen</a>
            <input type="hidden" name="bereich" value="<?= $bereich ?>">
        </form> -->

        <a href="/forum?bereich_id=<?= $bereich['jobId'] ?>" class="card-link">Zu den Themen</a>

      </div>
    </div>

    <?php endforeach; ?>

<?php endif; ?>

</body>
</html>