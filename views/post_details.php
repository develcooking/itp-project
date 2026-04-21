<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/models/Forum.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/models/Topic.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/models/Post.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/models/Comment.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/models/PostAttachment.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/views/header.php";

// Check if user is logged in
if (!isset($_SESSION['userId'])) {
    header("Location: /views/loginsite.php");
    exit();
}

$postId = isset($_GET['postId']) ? intval($_GET['postId']) : null;
$topicId = isset($_GET['topicId']) ? intval($_GET['topicId']) : null;
$jobId = isset($_GET['jobId']) ? intval($_GET['jobId']) : null;
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : null;

if (!$postId || !$topicId || !$jobId) {
    header("Location: /views/forum.php?error=invalid_params");
    exit();
}

$forumModel = new Forum($conn);
$topicModel = new Topic($conn);
$postModel = new Post($conn);
$commentModel = new Comment($conn);
$attachmentModel = new PostAttachment($conn);

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
$comments = $commentModel->getByPostId($postId, $searchTerm);

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
                    <form class="d-flex" method="GET" action="">
                        <input type="hidden" name="jobId" value="<?= $jobId ?>">
                        <input type="hidden" name="topicId" value="<?= $topicId ?>">
                        <input type="hidden" name="postId" value="<?= $postId ?>">
                        <input class="form-control form-control-sm me-2" type="search" name="search" placeholder="Beiträge durchsuchen..." aria-label="Search" value="<?= htmlspecialchars($searchTerm ?? '') ?>">
                        <button class="btn btn-sm btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                        <?php if ($searchTerm): ?>
                            <a href="?jobId=<?= $jobId ?>&topicId=<?= $topicId ?>&postId=<?= $postId ?>" class="btn btn-sm btn-outline-danger ms-2" title="Suche zurücksetzen"><i class="bi bi-x-circle"></i></a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="card-body bg-light flex-grow-1">
                    <!-- Main Post -->
                    <?php
                    $userVote = $post['voteType'] ?? 'noreaction';
                    $upIcon = ($userVote === 'up') ? 'bi-hand-thumbs-up-fill' : 'bi-hand-thumbs-up';
                    $downIcon = ($userVote === 'down') ? 'bi-hand-thumbs-down-fill' : 'bi-hand-thumbs-down';
                    $upVoteClass = ($userVote === 'up') ? 'forum-vote-up' : 'forum-vote-neutral';
                    $downVoteClass = ($userVote === 'down') ? 'forum-vote-down' : 'forum-vote-neutral';
                    ?>

                    <div class="card mb-4 border-0 shadow-sm" id="post-<?= $post['postId'] ?>">
                        <div class="card-body m-2">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-bold d-flex align-items-center gap-2">
                                    <?php if (!empty($post['hasProfileImage'])): ?>
                                        <img
                                            src="/controllers/profileImage.php?userId=<?= (int)$post['userId'] ?>"
                                            alt="Profilbild von <?= htmlspecialchars($post['userName'] ?? 'Unbekannt') ?>"
                                            class="forum-post-avatar rounded-circle border"
                                            onerror="this.onerror=null;this.src='/resources/imgs/icon.png';">
                                    <?php else: ?>
                                        <i class="bi bi-person-circle forum-post-avatar-icon" aria-label="Standard Profilbild"></i>
                                    <?php endif; ?>
                                    <span>
                                        <?= htmlspecialchars($post['userName'] ?? 'Unbekannt') ?>
                                        <?php if (!empty($post['school_company'])): ?>
                                            <span class="text-muted fw-normal ms-1" style="opacity: 0.6;">- <?= htmlspecialchars($post['school_company']) ?></span>
                                        <?php endif; ?>
                                    </span>
                                </span>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if (!empty($post['edited'])): ?>
                                        <span class="text-muted small" style="opacity:0.7;">Beitrag bearbeitet</span>
                                    <?php endif; ?>
                                    <?php if ($post['userId'] == $_SESSION['userId']): ?>
                                        <i class="bi bi-pencil text-muted edit-post-btn me-2" style="cursor: pointer;" data-post-id="<?= $post['postId'] ?>" title="Bearbeiten"></i>
                                        <i class="bi bi-trash3 text-muted delete-post-btn me-2" style="cursor: pointer;" data-post-id="<?= $post['postId'] ?>" title="Löschen"></i>
                                    <?php elseif ($isAdmin): ?>
                                        <i class="bi bi-trash3 text-muted delete-post-btn me-2" style="cursor: pointer;" data-post-id="<?= $post['postId'] ?>" title="Löschen"></i>
                                    <?php endif; ?>
                                    <small class="text-muted">
                                        <?= date('d.m.Y H:i', strtotime($post['createdAt'])) ?>
                                    </small>
                                </div>
                            </div>
                            <div class="post-content" id="post-content-<?= $post['postId'] ?>">
                                <?= HtmlSanitizer::sanitize($post['content'] ?? '') ?>
                            </div>

                            <?php
                                // Load attachments for this post
                                $attachments = $attachmentModel->getByPostId($post['postId']);

                                foreach ($attachments as $file) {
                                    $isImage = strpos($file['fileType'], 'image/') === 0;
                                    echo '<div class="mt-2">';
                                    if ($isImage) {
                                        echo '<img src="/controllers/download.php?id='.$file['attachmentId'].'" 
                                        style="max-width:200px; border-radius:8px; display:block; margin-bottom:5px;">';
                                    }
                                    echo '<a href="/controllers/download.php?id='.$file['attachmentId'].'" target="_blank">';
                                    echo '📎 ' . htmlspecialchars($file['fileName']);
                                    echo '</a>';
                                    echo '</div>';
                                }
                            ?>

                            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                <div class="d-flex gap-2">
                                    <form method="POST" action="/controllers/forum_actions.php">
                                        <input type="hidden" name="action" value="voteUp">
                                        <input type="hidden" name="postId" value="<?= $post['postId'] ?>">
                                        <input type="hidden" name="redirectTo" value="/views/post_details.php?postId=<?= $postId ?>&topicId=<?= $topicId ?>&jobId=<?= $jobId ?>">
                                        <button class="btn btn-sm btn-light">
                                            <i class="bi <?= $upIcon ?> forum-vote-icon <?= $upVoteClass ?>"></i>
                                            <span><?= $post['reaction_positive'] ?></span>
                                        </button>
                                    </form>

                                    <form method="POST" action="/controllers/forum_actions.php">
                                        <input type="hidden" name="action" value="voteDown">
                                        <input type="hidden" name="postId" value="<?= $post['postId'] ?>">
                                        <input type="hidden" name="redirectTo" value="/views/post_details.php?postId=<?= $postId ?>&topicId=<?= $topicId ?>&jobId=<?= $jobId ?>">
                                        <button class="btn btn-sm btn-light">
                                            <i class="bi <?= $downIcon ?> forum-vote-icon <?= $downVoteClass ?>"></i>
                                            <span><?= $post['reaction_negative'] ?></span>
                                        </button>
                                    </form>
                                </div>
                                <span class="text-muted small"><i class="bi bi-chat-dots me-1"></i> <?= count($comments) ?> Kommentare</span>
                            </div>
                        </div>
                    </div>

                    <!-- Add Comment Form -->
                    <div class="card shadow-sm border-0 mt-4 mx-2">
                        <div class="card-header bg-white border-bottom fw-bold" style="color: var(--accentColor);">
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
                                    <a href="/views/forum.php?jobId=<?= $jobId ?>&topicId=<?= $topicId ?>" class="btn btn-secondary shadow-sm">Zurück zum Thema</a>
                                    <button type="submit" class="btn btn-form-sub shadow-sm">
                                        <i class="bi bi-send me-1"></i> Kommentar absenden
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Comments Section -->
                    <div class="d-flex justify-content-between align-items-center mt-5 mb-3 mx-2 px-2">
                        <h5 class="mb-0">Kommentare (<?= count($comments) ?>)</h5>
                        <?php if ($searchTerm): ?>
                            <span class="badge bg-info text-dark">
                                Filter: "<?= htmlspecialchars($searchTerm) ?>"
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if (empty($comments)): ?>
                        <div class="alert alert-light border shadow-sm mx-2" role="alert">
                            <i class="bi bi-info-circle me-2"></i>
                            <?php if ($searchTerm): ?>
                                Keine Kommentare gefunden, die "<?= htmlspecialchars($searchTerm) ?>" enthalten.
                                <a href="?jobId=<?= $jobId ?>&topicId=<?= $topicId ?>&postId=<?= $postId ?>" class="alert-link ms-2">Suche zurücksetzen</a>
                            <?php else: ?>
                                Noch keine Kommentare. Seien Sie der Erste!
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                            <?php 
                                // Check if edited (modifiedAt > createdAt + 5 seconds to avoid sync issues)
                                $isEdited = strtotime($comment['modifiedAt']) > (strtotime($comment['createdAt']) + 5);
                            ?>
                            <div class="card mb-3 border-0 shadow-sm mx-2" id="comment-<?= $comment['commentId'] ?>">
                                <div class="card-body m-2">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="fw-bold d-flex align-items-center gap-2">
                                            <?php if (!empty($comment['hasProfileImage'])): ?>
                                                <img
                                                    src="/controllers/profileImage.php?userId=<?= (int)$comment['userId'] ?>"
                                                    alt="Profilbild"
                                                    class="forum-post-avatar rounded-circle border"
                                                    onerror="this.onerror=null;this.src='/resources/imgs/icon.png';">
                                            <?php else: ?>
                                                <i class="bi bi-person-circle forum-post-avatar-icon" aria-label="Standard Profilbild"></i>
                                            <?php endif; ?>
                                            <span>
                                                <?= htmlspecialchars($comment['userName'] ?? 'Unbekannt') ?>
                                                <?php if (!empty($comment['school_company'])): ?>
                                                    <span class="text-muted fw-normal ms-1" style="opacity: 0.6;">- <?= htmlspecialchars($comment['school_company']) ?></span>
                                                <?php endif; ?>
                                            </span>
                                        </span>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php if ($isEdited): ?>
                                                <span class="text-muted small" style="opacity:0.7;">bearbeitet</span>
                                            <?php endif; ?>
                                            <?php if ($comment['userId'] == $_SESSION['userId']): ?>
                                                <i class="bi bi-pencil text-muted edit-comment-btn me-2" style="cursor: pointer;" data-comment-id="<?= $comment['commentId'] ?>" title="Bearbeiten"></i>
                                                <i class="bi bi-trash3 text-muted delete-comment-btn me-2" style="cursor: pointer;" data-comment-id="<?= $comment['commentId'] ?>" title="Löschen"></i>
                                            <?php elseif ($isAdmin): ?>
                                                <i class="bi bi-trash3 text-muted delete-comment-btn me-2" style="cursor: pointer;" data-comment-id="<?= $comment['commentId'] ?>" title="Löschen"></i>
                                            <?php endif; ?>
                                            <small class="text-muted">
                                                <?= date('d.m.Y H:i', strtotime($comment['createdAt'])) ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="post-content" id="comment-content-<?= $comment['commentId'] ?>">
                                        <?= HtmlSanitizer::sanitize($comment['content'] ?? '') ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for editing Post -->
<div class="modal fade" id="editPostModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: var(--accentColor);">
                <h5 class="modal-title">Beitrag bearbeiten</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editPostForm" action="/controllers/forum_actions.php" method="POST">
                    <?php echo getCsrfTokenInput(); ?>
                    <input type="hidden" name="action" value="editPost">
                    <input type="hidden" name="postId" id="editPostId">
                    <input type="hidden" name="postContent" id="editPostContentHidden">
                    <input type="hidden" name="redirectTo" value="/views/post_details.php?postId=<?= $postId ?>&topicId=<?= $topicId ?>&jobId=<?= $jobId ?>">
                    <div class="mb-3">
                        <label class="form-label">Ihre Nachricht</label>
                        <div id="quillEditorEdit" style="height: 200px; background: white;"></div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                        <button type="submit" class="btn text-white" style="background-color: var(--accentColor);">Änderungen speichern</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form for deleting Post -->
<form id="deletePostForm" action="/controllers/forum_actions.php" method="POST" style="display: none;">
    <?php echo getCsrfTokenInput(); ?>
    <input type="hidden" name="action" value="deletePost">
    <input type="hidden" name="postId" id="deletePostId">
    <input type="hidden" name="redirectTo" value="/views/forum.php?jobId=<?= $jobId ?>&topicId=<?= $topicId ?>">
</form>

<script src="/resources/js/postCreateEditor.js"></script>
    
<!-- Modal for editing comments -->
<div class="modal fade" id="editCommentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editCommentForm" action="/controllers/forum_actions.php" method="POST">
                <?php echo getCsrfTokenInput(); ?>
                <div class="modal-header text-white" style="background-color: var(--accentColor);">
                    <h5 class="modal-title">Kommentar bearbeiten</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="editComment">
                    <input type="hidden" name="commentId" id="editCommentId">
                    <input type="hidden" name="commentContent" id="editCommentContentHidden">
                    <input type="hidden" name="redirectTo" value="/views/post_details.php?postId=<?= $postId ?>&topicId=<?= $topicId ?>&jobId=<?= $jobId ?>">
                    <div id="quillEditorCommentEdit" style="height: 200px; background: white;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="submit" class="btn text-white" style="background-color: var(--accentColor);">Speichern</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="deleteCommentForm" action="/controllers/forum_actions.php" method="POST" style="display:none;">
    <?php echo getCsrfTokenInput(); ?>
    <input type="hidden" name="action" value="deleteComment">
    <input type="hidden" name="commentId" id="deleteCommentId">
    <input type="hidden" name="redirectTo" value="/views/post_details.php?postId=<?= $postId ?>&topicId=<?= $topicId ?>&jobId=<?= $jobId ?>">
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Quill editor for comments
    if (document.getElementById('quillEditorComment')) {
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
            var text = quillEditorComment.getText().trim();
            if (text.length === 0) {
                alert('Bitte geben Sie einen Kommentar ein.');
                return false;
            }
            document.getElementById('commentContentHidden').value = content;
            this.submit();
        });
    }

    // Initialize Quill editor for comment editing
    var quillEditorCommentEdit;
    if (document.getElementById('quillEditorCommentEdit')) {
        quillEditorCommentEdit = new Quill('#quillEditorCommentEdit', {
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
    }

    // Handle comment edit button click
    document.querySelectorAll('.edit-comment-btn').forEach(button => {
        button.addEventListener('click', function() {
            const commentId = this.getAttribute('data-comment-id');
            const content = document.getElementById('comment-content-' + commentId).innerHTML;
            
            document.getElementById('editCommentId').value = commentId;
            quillEditorCommentEdit.root.innerHTML = content;
            
            var editModal = new bootstrap.Modal(document.getElementById('editCommentModal'));
            editModal.show();
        });
    });

    document.getElementById('editCommentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        document.getElementById('editCommentContentHidden').value = quillEditorCommentEdit.root.innerHTML;
        this.submit();
    });

    // Handle comment delete button click
    document.querySelectorAll('.edit-post-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            const content = document.getElementById('post-content-' + postId).innerHTML;
            
            document.getElementById('editPostId').value = postId;
            if (window.quillEditorEdit) {
                window.quillEditorEdit.root.innerHTML = content;
            }
            
            var editPostModal = new bootstrap.Modal(document.getElementById('editPostModal'));
            editPostModal.show();
        });
    });

    document.querySelectorAll('.delete-comment-btn').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Möchten Sie diesen Kommentar wirklich löschen?')) {
                const commentId = this.getAttribute('data-comment-id');
                document.getElementById('deleteCommentId').value = commentId;
                document.getElementById('deleteCommentForm').submit();
            }
        });
    });
});
</script>

<?php include $_SERVER['DOCUMENT_ROOT'] . "/views/footer.php"; ?>
