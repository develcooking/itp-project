<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/models/Forum.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/models/Topic.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/models/Post.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/models/Comment.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/views/header.php";

// Check if user is logged in
if (!isset($_SESSION['userId'])) {
    header("Location: /views/loginsite.php");
    exit();
}

$postId = isset($_GET['postId']) ? intval($_GET['postId']) : null;
$topicId = isset($_GET['topicId']) ? intval($_GET['topicId']) : null;
$jobId = isset($_GET['jobId']) ? intval($_GET['jobId']) : null;

if (!$postId || !$topicId || !$jobId) {
    header("Location: /views/forum.php?error=invalid_params");
    exit();
}

$forumModel = new Forum($conn);
$topicModel = new Topic($conn);
$postModel = new Post($conn);
$commentModel = new Comment($conn);

$isAdmin = isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin';

// Sidebar data
if ($isAdmin) {
    $bereiche = $forumModel->getallBereiche();
} else {
    $bereiche = $forumModel->getBereiche();
}

// Check access
if (!$isAdmin) {
    if (!$forumModel->hasAccess($_SESSION['userId'], $jobId)) {
        header("Location: /views/forum.php?error=no_access");
        exit();
    }
}

// Get post details
if (!$postModel->getById($postId)) {
    header("Location: /views/forum.php?error=post_not_found");
    exit();
}

// Verify post belongs to topic
if ($postModel->getTopicId() != $topicId) {
    header("Location: /views/forum.php?error=invalid_post");
    exit();
}

// Get topic details
if (!$topicModel->getById($topicId)) {
    header("Location: /views/forum.php?error=topic_not_found");
    exit();
}

// Verify topic belongs to job
if ($topicModel->getJobId() != $jobId) {
    header("Location: /views/forum.php?error=invalid_topic");
    exit();
}

// Get comments
$comments = $commentModel->getByPostId($postId);

// Get post with vote counts
$postsWithVotes = $postModel->getByTopicId($topicId, $_SESSION['userId']);
$post = null;
if (!empty($postsWithVotes)) {
    foreach ($postsWithVotes as $p) {
        if ($p['postId'] == $postId) {
            $post = $p;
            break;
        }
    }
}

// Ensure post found
if (!$post) {
    header("Location: /views/forum.php?error=post_not_found");
    exit();
}

$currentJobName = "Berufsbereich";
foreach ($bereiche as $b) {
    if ($b['jobId'] == $jobId) {
        $currentJobName = $b['name'];
        break;
    }
}
$topicName = $topicModel->getName();
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
                            <a href="/views/forum.php?jobId=<?= $bereich['jobId'] ?>" 
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= $jobId == $bereich['jobId'] ? 'active' : '' ?>"
                               <?= $jobId == $bereich['jobId'] ? 'style="background-color: var(--orangeLight); border-color: var(--accentColor);"' : '' ?>>
                                <?= htmlspecialchars($bereich['name']) ?>
                                <i class="bi bi-chevron-right small <?= $jobId == $bereich['jobId'] ? '' : 'text-muted' ?>"></i>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            <div class="card shadow-sm min-vh-75 d-flex flex-column">
                <div class="card-header d-flex justify-content-between align-items-center bg-light">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="/views/forum.php?jobId=<?= $jobId ?>" class="text-decoration-none"><?= htmlspecialchars($currentJobName) ?></a></li>
                            <li class="breadcrumb-item"><a href="/views/forum.php?jobId=<?= $jobId ?>&topicId=<?= $topicId ?>" class="text-decoration-none"><?= htmlspecialchars($topicName) ?></a></li>
                            <li class="breadcrumb-item active" aria-current="page">Beitrag</li>
                        </ol>
                    </nav>
                </div>

                <div class="card-body bg-light flex-grow-1">
                    <!-- Main Post -->
                    <?php
                    $userVote = $post['voteType'] ?? 'noreaction';
                    $upIcon = ($userVote === 'up') ? 'bi-hand-thumbs-up-fill' : 'bi-hand-thumbs-up';
                    $downIcon = ($userVote === 'down') ? 'bi-hand-thumbs-down-fill' : 'bi-hand-thumbs-down';
                    $upColor = ($userVote === 'up') ? 'forum-vote-up' : 'forum-vote-neutral';
                    $downColor = ($userVote === 'down') ? 'forum-vote-down' : 'forum-vote-neutral';
                    ?>

                    <div class="card mb-4 border-0 shadow-sm">
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

                            <div class="d-flex gap-2 mt-3 pt-2 border-top">
                                <form method="POST" action="/controllers/forum_actions.php">
                                    <input type="hidden" name="action" value="voteUp">
                                    <input type="hidden" name="postId" value="<?= $post['postId'] ?>">
                                    <input type="hidden" name="redirectTo" value="/views/post_details.php?postId=<?= $postId ?>&topicId=<?= $topicId ?>&jobId=<?= $jobId ?>">
                                    <button class="btn btn-sm btn-light">
                                        <i class="bi <?= $upIcon ?> forum-vote-icon <?= $upColor ?>"></i>
                                        <span><?= $post['reaction_positive'] ?></span>
                                    </button>
                                </form>

                                <form method="POST" action="/controllers/forum_actions.php">
                                    <input type="hidden" name="action" value="voteDown">
                                    <input type="hidden" name="postId" value="<?= $post['postId'] ?>">
                                    <input type="hidden" name="redirectTo" value="/views/post_details.php?postId=<?= $postId ?>&topicId=<?= $topicId ?>&jobId=<?= $jobId ?>">
                                    <button class="btn btn-sm btn-light">
                                        <i class="bi <?= $downIcon ?> forum-vote-icon <?= $downColor ?>"></i>
                                        <span><?= $post['reaction_negative'] ?></span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Comments Section -->
                    <h5 class="mb-3 px-2">Kommentare (<?= count($comments) ?>)</h5>

                    <?php if (empty($comments)): ?>
                        <div class="alert alert-light border shadow-sm mx-2" role="alert">
                            <i class="bi bi-info-circle me-2 text-primary"></i>Noch keine Kommentare. Seien Sie der Erste!
                        </div>
                    <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="card mb-3 border-0 shadow-sm mx-2">
                                <div class="card-body m-2">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="fw-bold text-primary d-flex align-items-center gap-2">
                                            <?php if (!empty($comment['hasProfileImage'])): ?>
                                                <img
                                                    src="/controllers/profileImage.php?userId=<?= (int)$comment['userId'] ?>"
                                                    alt="Profilbild"
                                                    class="forum-post-avatar rounded-circle border"
                                                    onerror="this.onerror=null;this.src='/resources/imgs/icon.png';">
                                            <?php else: ?>
                                                <i class="bi bi-person-circle forum-post-avatar-icon" aria-label="Standard Profilbild"></i>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($comment['userName'] ?? 'Unbekannt') ?>
                                        </span>
                                        <small class="text-muted">
                                            <?= date('d.m.Y H:i', strtotime($comment['createdAt'])) ?>
                                        </small>
                                    </div>
                                    <div class="post-content p-2">
                                        <?= strip_tags($comment['content'], '<h1><h2><h3><h4><h5><h6><p><br><strong><em><u><s><blockquote><pre><ol><ul><li><a>') ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Add Comment Form -->
                    <div class="card shadow-sm border-0 mt-4 mx-2">
                        <div class="card-header bg-white border-bottom fw-bold">
                            <i class="bi bi-reply me-2"></i>Kommentar hinzufügen
                        </div>
                        <div class="card-body">
                            <form id="createCommentForm" action="/controllers/forum_actions.php" method="POST">
                                <?php echo getCsrfTokenInput(); ?>
                                <input type="hidden" name="action" value="createComment">
                                <input type="hidden" name="postId" value="<?= $postId ?>">
                                <input type="hidden" name="topicId" value="<?= $topicId ?>">
                                <input type="hidden" name="jobId" value="<?= $jobId ?>">
                                <input type="hidden" name="commentContent" id="commentContentHidden">
                                
                                <div class="mb-3">
                                    <div id="quillEditorComment" style="height: 150px; background: white;"></div>
                                </div>
                                
                                <div class="text-end">
                                    <a href="/views/forum.php?jobId=<?= $jobId ?>&topicId=<?= $topicId ?>" class="btn btn-secondary shadow-sm">Zurück</a>
                                    <button type="submit" class="btn btn-form-sub shadow-sm">
                                        <i class="bi bi-send me-1"></i> Kommentar absenden
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Quill editor for comments
    var quillEditorComment = new Quill('#quillEditorComment', {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['blockquote'],
                ['link']
            ]
        }
    });

    // Handle form submission
    document.getElementById('createCommentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        var content = quillEditorComment.root.innerHTML;
        
        // Basic validation: check if there's actual text content
        var text = quillEditorComment.getText().trim();
        if (text.length === 0) {
            alert('Bitte geben Sie einen Kommentar ein.');
            return false;
        }
        
        document.getElementById('commentContentHidden').value = content;
        this.submit();
    });
});
</script>

<?php include $_SERVER['DOCUMENT_ROOT'] . "/views/footer.php"; ?>
