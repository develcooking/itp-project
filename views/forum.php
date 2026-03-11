<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/models/Forum.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/models/Topic.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/models/Post.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/views/header.php";

$forumModel = new Forum($conn);
$topicModel = new Topic($conn);
$postModel = new Post($conn);

$selectedJobId = isset($_GET['jobId']) ? intval($_GET['jobId']) : null;
$selectedTopicId = isset($_GET['topicId']) ? intval($_GET['topicId']) : null;

$bereiche = $forumModel->getBereiche();
$topics = [];
$posts = [];
$currentJobName = "Berufsbereich";
$currentTopicName = "";

if ($selectedJobId) {
    $topics = $forumModel->getTopicsByBereich($selectedJobId);
    // Even if empty, we treat it as an empty topic list
    
    foreach ($bereiche as $b) {
        if ($b['jobId'] == $selectedJobId) {
            $currentJobName = $b['name'];
            break;
        }
    }
}

if ($selectedTopicId) {
    if ($topicModel->getById($selectedTopicId)) {
        $currentTopicName = $topicModel->getName();
        $posts = $postModel->getByTopicId($selectedTopicId);
    }
}
?>
    <link href="../resources/css/quill.snow.css" rel="stylesheet" />
    <script src="../resources/js/quill.js"></script>
<div class="container-fluid mt-4 forum-container">
    <div class="row">
        <!-- Sidebar: Berufsbereiche -->
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-header text-white" style="background-color: var(--accentColor);">
                    <h5 class="mb-0">Berufsbereiche</h5>
                </div>
                <div class="list-group list-group-flush">
                    <?php if (empty($bereiche)): ?>
                        <div class="p-3 text-center text-muted">Keine Bereiche verfügbar.</div>
                    <?php else: ?>
                        <?php foreach ($bereiche as $bereich): ?>
                            <a href="?jobId=<?= $bereich['jobId'] ?>" 
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= $selectedJobId == $bereich['jobId'] ? 'active' : '' ?>"
                               <?= $selectedJobId == $bereich['jobId'] ? 'style="background-color: var(--accentColor); border-color: var(--accentColor);"' : '' ?>>
                                <?= htmlspecialchars($bereich['name']) ?>
                                <i class="bi bi-chevron-right small <?= $selectedJobId == $bereich['jobId'] ? '' : 'text-muted' ?>"></i>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            <div class="card shadow-sm min-vh-75">
                <?php if ($selectedTopicId): ?>
                    <!-- Thread View (Posts) -->
                    <div class="card-header d-flex justify-content-between align-items-center bg-light">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="?jobId=<?= $selectedJobId ?>" class="text-decoration-none"><?= htmlspecialchars($currentJobName) ?></a></li>
                                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($currentTopicName) ?></li>
                            </ol>
                        </nav>
                        <button class="btn btn-sm btn-form-sub" data-bs-toggle="modal" data-bs-target="#createPostModal">
                            <i class="bi bi-reply me-1"></i> Antworten
                        </button>
                    </div>
                    <div class="card-body bg-light">
                        <?php if (empty($posts)): ?>
                            <div class="p-5 text-center text-muted">Noch keine Beiträge in diesem Thread.</div>
                        <?php else: ?>
                            <?php foreach ($posts as $post): ?>
                                <div class="card mb-3 border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="fw-bold text-primary"><i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($post['userName'] ?? 'Unbekannt') ?></span>
                                            <small class="text-muted"><?= date('d.m.Y H:i', strtotime($post['createdAt'])) ?></small>
                                        </div>
                                        <div class="post-content p-2">
                                            <?= $post['content'] ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                <?php elseif ($selectedJobId): ?>
                    <!-- Threads View (Topics) -->
                    <div class="card-header d-flex justify-content-between align-items-center bg-light">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($currentJobName) ?></li>
                            </ol>
                        </nav>
                        <button class="btn btn-sm btn-form-sub" data-bs-toggle="modal" data-bs-target="#createTopicModal">
                            <i class="bi bi-plus-circle me-1"></i> Neues Thema
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($topics)): ?>
                            <div class="p-5 text-center text-muted">Noch keine Themen in diesem Bereich. Seien Sie der Erste!</div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($topics as $topic): ?>
                                    <a href="?jobId=<?= $selectedJobId ?>&topicId=<?= $topic['topicId'] ?>" class="list-group-item list-group-item-action py-3">
                                        <div class="d-flex w-100 justify-content-between align-items-center">
                                            <h6 class="mb-1 fw-bold"><i class="bi bi-chat-left-text me-2 text-primary"></i><?= htmlspecialchars($topic['name']) ?></h6>
                                            <div class="d-flex align-items-center">
                                                <small class="text-muted me-3">Erstellt von: <?= htmlspecialchars($topic['userName'] ?? 'Unbekannt') ?></small>
                                                <i class="bi bi-arrow-right text-muted"></i>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    <!-- Default View -->
                    <div class="card-body text-center d-flex flex-column justify-content-center">
                        <i class="bi bi-chat-dots" style="font-size: 4rem; color: #dee2e6;"></i>
                        <h4 class="mt-3">Willkommen im Forum</h4>
                        <p class="text-muted">Bitte wählen Sie einen Berufsbereich aus der Liste links aus, um die Threads zu sehen.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal for creating Topic -->
<div class="modal fade" id="createTopicModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: var(--accentColor);">
                <h5 class="modal-title">Neues Thema erstellen</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="/controllers/forum_actions.php" method="POST">
                    <input type="hidden" name="action" value="createTopic">
                    <input type="hidden" name="jobId" value="<?= $selectedJobId ?>">
                    <div class="mb-3">
                        <label for="topicName" class="form-label">Titel des Themas</label>
                        <input type="text" class="form-control" name="topicName" id="topicName" placeholder="Titel eingeben" required>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                        <button type="submit" class="btn text-white" style="background-color: var(--accentColor);">Thema erstellen</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal for creating Post -->
<div class="modal fade" id="createPostModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: var(--accentColor);">
                <h5 class="modal-title">Antworten</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createPostForm" action="/controllers/forum_actions.php" method="POST">
                    <input type="hidden" name="action" value="createPost">
                    <input type="hidden" name="topicId" value="<?= $selectedTopicId ?>">
                    <input type="hidden" name="jobId" value="<?= $selectedJobId ?>">
                    <input type="hidden" name="postContent" id="postContentHidden">
                    <div class="mb-3">
                        <label class="form-label">Ihre Nachricht</label>
                        <div id="quillEditor" style="height: 200px; background: white;"></div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                        <button type="submit" class="btn text-white" style="background-color: var(--accentColor);">Antwort absenden</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

    <script>
        const quill = new Quill('#quillEditor', {
            theme: 'snow',
            placeholder: 'Schreiben Sie hier Ihre Nachricht...',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['link'],
                    ['clean']
                ]
            }
        });

        document.getElementById('createPostForm').onsubmit = function() {
            const postContentHidden = document.getElementById('postContentHidden');
            postContentHidden.value = quill.root.innerHTML;
            
            if (quill.getText().trim().length === 0) {
                alert('Bitte geben Sie eine Nachricht ein.');
                return false;
            }
        };
    </script>
<?php include $_SERVER['DOCUMENT_ROOT'] . "/views/footer.php"; ?>