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

// Sicherstellen dass wir den Post gefunden haben
if (!$post) {
    header("Location: /views/forum.php?error=post_not_found");
    exit();
}

$topicName = $topicModel->getName();
?>

<link href="../resources/css/quill.snow.css" rel="stylesheet" />
<script src="../resources/js/quill.js"></script>

<style>
    .post-content blockquote {
        border-left: 4px solid #ccc;
        margin-bottom: 5px;
        margin-top: 5px;
        padding-left: 16px;
        font-style: italic;
    }
    .post-content h1 { font-size: 2em; font-weight: bold; }
    .post-content h2 { font-size: 1.5em; font-weight: bold; }
    .post-content h3 { font-size: 1.17em; font-weight: bold; }
</style>

<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/views/forum.php" class="text-decoration-none">Forum</a></li>
                    <li class="breadcrumb-item"><a href="/views/forum.php?jobId=<?= $jobId ?>" class="text-decoration-none">Bereich</a></li>
                    <li class="breadcrumb-item"><a href="/views/forum.php?jobId=<?= $jobId ?>&topicId=<?= $topicId ?>" class="text-decoration-none"><?= htmlspecialchars($topicName) ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Beitrag</li>
                </ol>
            </nav>

            <!-- Main Post Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><?= htmlspecialchars($topicName) ?></h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-bold text-primary">
                            <i class="bi bi-person-circle me-1"></i>
                            <?= htmlspecialchars($post['userName'] ?? 'Unbekannt') ?>
                        </span>
                        <small class="text-muted">
                            <?= date('d.m.Y H:i', strtotime($post['createdAt'])) ?>
                        </small>
                    </div>

                    <div class="post-content p-3 bg-light rounded mb-3">
                        <?= strip_tags($post['content'], '<h1><h2><h3><h4><h5><h6><p><br><strong><em><u><s><blockquote><pre><ol><ul><li><a>') ?>
                    </div>

                    <!-- Voting Buttons -->
                    <?php
                    $userVote = $post['voteType'] ?? 'noreaction';
                    $upIcon = ($userVote === 'up') ? 'bi-hand-thumbs-up-fill' : 'bi-hand-thumbs-up';
                    $downIcon = ($userVote === 'down') ? 'bi-hand-thumbs-down-fill' : 'bi-hand-thumbs-down';
                    $upColor = ($userVote === 'up') ? 'green' : '#6c757d';
                    $downColor = ($userVote === 'down') ? 'red' : '#6c757d';
                    ?>

                    <div class="d-flex gap-2">
                        <form method="POST" action="/controllers/forum_actions.php">
                            <input type="hidden" name="action" value="voteUp">
                            <input type="hidden" name="postId" value="<?= $post['postId'] ?>">
                            <input type="hidden" name="redirectTo" value="/views/post_details.php?postId=<?= $postId ?>&topicId=<?= $topicId ?>&jobId=<?= $jobId ?>">
                            <button class="btn btn-sm btn-light">
                                <i class="bi <?= $upIcon ?>" style="color: <?= $upColor ?>; font-size:18px;"></i>
                                <span><?= $post['reaction_positive'] ?></span>
                            </button>
                        </form>

                        <form method="POST" action="/controllers/forum_actions.php">
                            <input type="hidden" name="action" value="voteDown">
                            <input type="hidden" name="postId" value="<?= $post['postId'] ?>">
                            <input type="hidden" name="redirectTo" value="/views/post_details.php?postId=<?= $postId ?>&topicId=<?= $topicId ?>&jobId=<?= $jobId ?>">
                            <button class="btn btn-sm btn-light">
                                <i class="bi <?= $downIcon ?>" style="color: <?= $downColor ?>; font-size:18px;"></i>
                                <span><?= $post['reaction_negative'] ?></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Comments Section -->
            <h5 class="mb-3">Kommentare (<?= count($comments) ?>)</h5>

            <!-- Comments List -->
            <?php if (empty($comments)): ?>
                <div class="alert alert-info" role="alert">
                    <i class="bi bi-info-circle me-2"></i>Noch keine Kommentare. Seien Sie der Erste!
                </div>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="card shadow-sm mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-bold text-primary">
                                    <i class="bi bi-person-circle me-1"></i>
                                    <?= htmlspecialchars($comment['userName'] ?? 'Unbekannt') ?>
                                </span>
                                <small class="text-muted">
                                    <?= date('d.m.Y H:i', strtotime($comment['createdAt'])) ?>
                                </small>
                            </div>
                            <div class="ps-3">
                                <?= strip_tags($comment['content'], '<h1><h2><h3><h4><h5><h6><p><br><strong><em><u><s><blockquote><pre><ol><ul><li><a>') ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Add Comment Form -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Kommentar hinzufügen</h6>
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
                            <label class="form-label">Ihr Kommentar</label>
                            <div id="quillEditorComment" style="height: 150px; background: white;"></div>
                        </div>
                        
                        <div class="text-end">
                            <a href="/views/forum.php?jobId=<?= $jobId ?>&topicId=<?= $topicId ?>" class="btn btn-secondary">Zurück</a>
                            <button type="submit" class="btn text-white" style="background-color: var(--accentColor);">Kommentar absenden</button>
                        </div>
                    </form>
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
        
        if (quillEditorComment.getText().trim().length === 0) {
            alert('Bitte geben Sie einen Kommentar ein.');
            return false;
        }
        
        document.getElementById('commentContentHidden').value = content;
        this.submit();
    });
});
</script>

<?php include $_SERVER['DOCUMENT_ROOT'] . "/views/footer.php"; ?>
