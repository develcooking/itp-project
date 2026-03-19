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
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : null;

$isAdmin = isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin';

if ($isAdmin) {
    // Admins see all Berufsbereiche
    $bereiche = $forumModel->getallBereiche();
} else {
    $bereiche = $forumModel->getBereiche();
}
$topics = [];
$posts = [];
$currentJobName = "Berufsbereich";
$currentTopicName = "";

if ($selectedJobId) {
    if (!$isAdmin) {
        if (!$forumModel->hasAccess($_SESSION['userId'] ?? 0, $selectedJobId)) {
            header("Location: /views/forum.php?error=no_access");
            exit();
        }
    }
    $topics = $forumModel->getTopicsByBereich($selectedJobId, $searchTerm);
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
        $posts = $postModel->getByTopicId($selectedTopicId, $_SESSION['userId']);
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
                               <?= $selectedJobId == $bereich['jobId'] ? 'style="background-color: var(--orangeLight); border-color: var(--accentColor);"' : '' ?>>
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
    <div class="card shadow-sm min-vh-75 d-flex flex-column">
        <?php if ($selectedTopicId): ?>
            <!-- Thread View (Posts) -->
            <div class="card-header d-flex justify-content-between align-items-center bg-light">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="?jobId=<?= $selectedJobId ?>" class="text-decoration-none"><?= htmlspecialchars($currentJobName) ?></a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($currentTopicName) ?></li>
                    </ol>
                </nav>
            </div>
            <div class="card-body bg-light flex-grow-1">
                <?php if (empty($posts)): ?>
                    <div class="p-5 text-center text-muted">
                        Noch keine Beiträge in diesem Thread.
                    </div>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>

                <?php
                $userVote = $post['voteType'] ?? 'noreaction';

                $upIcon = ($userVote === 'up') ? 'bi-hand-thumbs-up-fill' : 'bi-hand-thumbs-up';
                $downIcon = ($userVote === 'down') ? 'bi-hand-thumbs-down-fill' : 'bi-hand-thumbs-down';

                $upColor = ($userVote === 'up') ? 'forum-vote-up' : 'forum-vote-neutral';
                $downColor = ($userVote === 'down') ? 'forum-vote-down' : 'forum-vote-neutral';
                ?>

                <div class="card mb-3 border-0 shadow-sm">
                    <div class="card-body m-2">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold text-primary d-flex align-items-center gap-2">
                                <?php if (!empty($post['hasProfileImage'])): ?>
                                    <img
                                        src="/controllers/profileImage.php?userId=<?= (int)$post['userId'] ?>"
                                        alt="Profilbild von <?= htmlspecialchars($post['userName'] ?? 'Unbekannt') ?>"
                                        class="forum-post-avatar rounded-circle border"
                                        onerror="this.onerror=null;this.src='/resources/imgs/icon.png';">
                                <?php else: ?>
                                <i class="bi bi-person-circle forum-post-avatar-icon" aria-label="Standard Profilbild"></i>
                                <?php endif; ?>
                                <?= htmlspecialchars($post['userName'] ?? 'Unbekannt') ?>
                            </span>
                            <small class="text-muted">
                                <?= date('d.m.Y H:i', strtotime($post['createdAt'])) ?>
                            </small>
                        </div>
                        <div class="post-content p-2">
                            <?= strip_tags($post['content'], '<h1><h2><h3><h4><h5><h6><p><br><strong><em><u><s><blockquote><pre><ol><ul><li><a>') ?>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                            <div class="d-flex gap-2">
                                <form method="POST" action="/controllers/forum_actions.php">
                                    <input type="hidden" name="action" value="voteUp">
                                    <input type="hidden" name="postId" value="<?= $post['postId'] ?>">
                                    <button class="btn btn-sm btn-light">
                                        <i class="bi <?= $upIcon ?> forum-vote-icon <?= $upColor ?>"></i>
                                        <span><?= $post['reaction_positive'] ?></span>
                                    </button>
                                </form>

                                <form method="POST" action="/controllers/forum_actions.php">
                                    <input type="hidden" name="action" value="voteDown">
                                    <input type="hidden" name="postId" value="<?= $post['postId'] ?>">
                                    <button class="btn btn-sm btn-light" type="submit">
                                        <i class="bi <?= $downIcon ?> forum-vote-icon <?= $downColor ?>"></i>
                                        <span><?= $post['reaction_negative'] ?></span>
                                    </button>
                                </form>
                            </div>

                            <a href="/views/post_details.php?postId=<?= $post['postId'] ?>&topicId=<?= $selectedTopicId ?>&jobId=<?= $selectedJobId ?>" class="btn btn-sm btn-form-sub">
                                <i class="bi bi-chat-dots me-1"></i> Kommentare (<?= $post['comment_count'] ?>)
                            </a>
                        </div>
                    </div>
                </div>

                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            <div class="card-footer bg-light d-flex justify-content-end align-items-center gap-2">
                <button class="btn btn-form-sub" data-bs-toggle="modal" data-bs-target="#createPostModal">
                    <i class="bi bi-plus-circle me-1"></i> Neuer Beitrag
                </button>
            </div>

                <?php elseif ($selectedJobId): ?>
                    <!-- Threads View (Topics) -->
                    <div class="card-header d-flex justify-content-between align-items-center bg-light">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($currentJobName) ?></li>
                            </ol>
                        </nav>
                        <?php // search field ?>
                        <div class="d-flex align-items-center">
                            <form class="d-flex me-3" method="GET" action="">
                                <input type="hidden" name="jobId" value="<?= $selectedJobId ?>">
                                <input class="form-control form-control-sm me-2" type="search" name="search" placeholder="Suche..." aria-label="Search" value="<?= htmlspecialchars($searchTerm ?? '') ?>">
                                <button class="btn btn-sm btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                            </form>
                            <button class="btn btn-sm btn-form-sub" data-bs-toggle="modal" data-bs-target="#createTopicModal">
                                <i class="bi bi-plus-circle me-1"></i> Neues Thema
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($topics)): ?>
                            <div class="p-5 text-center text-muted">Noch keine Themen in diesem Bereich. Seien Sie der/die Erste!</div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($topics as $topic): ?>
                                    <div class="list-group-item p-3">
                                        <div class="row align-items-center">
                                                                    
                                            <!-- COLUMN 1: Topic Name -->
                                            <div class="col-md-9">
                                                <a href="?jobId=<?= $selectedJobId ?>&topicId=<?= $topic['topicId'] ?>" class="text-decoration-none">
                                                    <h6 class="mb-1 fw-bold">
                                                        <i class="bi bi-chat-left-text me-2 text-primary"></i>
                                                        <?= htmlspecialchars($topic['name']) ?>
                                                    </h6>
                                                </a>
                                            </div>
                                                                    
                                            <!-- COLUMN 2: Pin Button -->
                                            <div class="col-md-1 text-center">
                                                <?php if ($isAdmin): ?>
                                                    <form method="POST" action="/controllers/forum_actions.php">
                                                        <?php echo getCsrfTokenInput(); ?>
                                                        <input type="hidden" name="action" value="togglePin">
                                                        <input type="hidden" name="topicId" value="<?= $topic['topicId'] ?>">
                                                
                                                        <button class="btn btn-sm btn-warning">
                                                            <?= !empty($topic['pinned']) ? 'Unpin' : '📌 Pin' ?>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                                
                                            <!-- COLUMN 3: Meta Infos -->
                                            <div class="col-md-2 d-flex justify-content-end align-items-center gap-2">
                                                
                                                <?php if (!empty($topic['pinned'])): ?>
                                                    <span>📌</span>
                                                <?php endif; ?>
                                                
                                                <small class="text-muted">
                                                    <?= htmlspecialchars($topic['userName'] ?? 'Unbekannt') ?>
                                                </small>
                                                
                                                <a href="?jobId=<?= $selectedJobId ?>&topicId=<?= $topic['topicId'] ?>">
                                                    <i class="bi bi-arrow-right text-muted"></i>
                                                </a>
                                                
                                            </div>
                                                
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
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
            <div class="card-body p-0 flex-grow-1">
                <?php if (empty($topics)): ?>
                    <div class="p-5 text-center text-muted">Noch keine Themen in diesem Bereich. Seien Sie der Erste!</div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($topics as $topic): ?>
                            <a href="?jobId=<?= $selectedJobId ?>&topicId=<?= $topic['topicId'] ?>" class="list-group-item list-group-item-action py-3">
                                <div class="d-flex justify-content-between align-items-center">
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
            <div class="card-body text-center d-flex flex-column justify-content-center flex-grow-1">
                <i class="bi bi-chat-dots forum-empty-icon"></i>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: var(--accentColor);">
                <h5 class="modal-title">Neues Thema und initiales Beitrag erstellen</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createTopicForm" action="/controllers/forum_actions.php" method="POST">
                    <?php echo getCsrfTokenInput(); ?>
                    <input type="hidden" name="action" value="createTopic">
                    <input type="hidden" name="jobId" value="<?= $selectedJobId ?>">
                    <div class="mb-3">
                        <label for="topicName" class="form-label">Titel des Themas</label>
                        <input type="text" class="form-control" name="topicName" id="topicName" placeholder="Titel eingeben" required>
                    </div>
                <!-- Modal for creating initial Post -->
                    <input type="hidden" name="postContent" id="postContentHiddenInitial">
                    <div class="mb-3">
                        <label class="form-label">Ihre Nachricht</label>
                        <div id="quillEditorInitial" style="height: 200px; background: white;"></div>
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
                <h5 class="modal-title">Neuen Beitrag erstellen</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createPostForm" action="/controllers/forum_actions.php" method="POST">
                    <?php echo getCsrfTokenInput(); ?>
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
                        <button type="submit" class="btn text-white" style="background-color: var(--accentColor);">Beitrag absenden</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
    
<script src="/resources/js/postCreateEditor.js"></script>

<?php include $_SERVER['DOCUMENT_ROOT'] . "/views/footer.php"; ?>
