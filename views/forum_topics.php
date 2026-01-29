<?php
include $homepath . "/views/header.php";
?>

<h2>Topics</h2>

<?php if(empty($topics)): ?>
    <p>Keine Themen für dieses Bereich gefunden.</p>
<?php else: ?>
<?php foreach ($topics as $topic): ?>

<div class="card m-2">
    <div class="card-body">
        <h5><?= htmlspecialchars($topic['title']) ?></h5>
    </div>
</div>

<?php endforeach; ?>
<?php endif; ?>