<?php
include $_SERVER['DOCUMENT_ROOT'] . "/views/header.php";
include $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
?>

<!-- Include the Quill library -->
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<!-- Include stylesheet -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />

<div class="forum-container">

<h2>Themen zum Berufsbereich: <?= $bereich ?></h2>
<!-- Button zum Erstellen eines neuen Themas -->
<button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createTopicModal">
    Erstellen
</button>

<?php if(empty($topics)): ?>
    <p>Keine Themen für dieses Bereich gefunden.</p>
<?php else: ?>
<?php foreach ($topics as $topic): ?>

<div class="card m-2">
    <div class="card-body">
        <h5><?= htmlspecialchars($topic['name']) ?></h5>
        <a href="/controllers/forum.php?name=<?= $bereich['name'] ?>&topic_id=<?= $topic['topicId'] ?>" class="card-link">Zu den Beiträgen</a>
    </div>
</div>

<?php endforeach; ?>
<?php endif; ?>

<!-- Modal zum Erstellen eines neuen Themas -->
<div class="modal fade" id="createTopicModal" tabindex="-1" aria-labelledby="createTopicModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            
            <!-- Modal-Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="createTopicModalLabel">Neues Thema erstellen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <!-- Modal-Body -->
            <div class="modal-body">

                <form id="createTopicForm" action="/controllers/forum.php" method="post">
                    <input type="hidden" name="action" value="createTopic">
                    <input type="hidden" name="bereich_id" value="<?= htmlspecialchars($bereich_id) ?>">
                    <input type="hidden" name="name" value="<?= htmlspecialchars($bereich) ?>">
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Titel *</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                                        
                    <label for="editor" class="form-label">Initiales Beitrag erstellen *</label>

                    <div class="mb-3">
                        <label for="description" class="form-label">Beitragtitel *</label>
                        <input type="text" class="form-control" id="description" name="description" required>
                    </div>
                    
                    <div id="editor">
                        <!-- Hier wird der Quill-Editor initialisiert -->
                         <input type="hidden" name="content" id="content" required>
                    </div>

                    <!-- Initialize Quill editor -->
                    <script>
                     const quill = new Quill('#editor', {
                       theme: 'snow'
                     });

                    const form = document.getElementById("createTopicForm");
                    
                    form.onsubmit = function() {
                        document.getElementById("content").value = quill.root.innerHTML;
                    };
                    </script>

                </form> 
            </div>
            
            <!-- Modal-Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                <button type="submit" form="createTopicForm" class="btn btn-primary">Erstellen</button>
            </div>
            
        </div>
    </div>
</div>

</div>
<?php include $_SERVER['DOCUMENT_ROOT'] . "/views/footer.php"; ?>