<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/middleware/startSession.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/controllers/login.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/models/Forum.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/models/Topic.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/models/Post.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/app/services/TopicPostNotificationService.php";

if (!isset($_SESSION['userId'])) {
    header("Location: /views/loginsite.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $forumModel = new Forum($conn);
    $userId = $_SESSION['userId'];
    $isAdmin = isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin';

    switch ($_POST['action']) {
        case 'createTopic':
            $name = trim($_POST['topicName'] ?? '');
            $jobId = intval($_POST['jobId'] ?? 0);
            $content = trim($_POST['postContent'] ?? '');

            if (!empty($name) && $jobId > 0) {
                if (!$isAdmin && !$forumModel->hasAccess($userId, $jobId)) {
                    header("Location: /views/forum.php?error=no_access");
                    exit();
                }

                $topic = new Topic($conn);
                $topic->setName($name);
                $topic->setJobId($jobId);
                $topic->setUserId($userId);
                $topic->setCreatedBy($userId);
                $topic->setModifiedBy($userId);

                if ($topic->post()) {

// Basic XSS protection on input (optional, but good practice)
                        $content = strip_tags($content, '<h1><h2><h3><h4><h5><h6><p><br><strong><em><u><s><blockquote><pre><ol><ul><li><a>');
                        $topicId = $topic->getTopicId();

                    $post = new Post($conn);
                    $post->setTopicID($topicId);
                    $post->setUserId($userId);
                    $post->setContent($content);
                    $post->setDescription('');
                    $post->setReactionNegative(0);
                    $post->setReactionPositive(0);
                    $post->setCreatedBy($userId);
                    $post->setModifiedBy($userId);

                    if ($post->post()) {

                        $postId = $post->getPostId();

                        handleAttachments($conn, $postId);

                        header("Location: /views/forum.php?jobId=$jobId&topicId=$topicId");
                        exit();
                    }
                }
            }
            header("Location: /views/forum.php?jobId=$jobId&error=topic_failed");
            exit();

        case 'createPost':
            $content = trim($_POST['postContent'] ?? '');
            $topicId = intval($_POST['topicId'] ?? 0);
            $jobId = intval($_POST['jobId'] ?? 0);

            if (!empty($content) && $topicId > 0) {
                if (!$isAdmin && !$forumModel->hasAccess($userId, $jobId)) {
                    header("Location: /views/forum.php?error=no_access");
                    exit();
                }

                // Ensure topic is in this job
                if (!$forumModel->isTopicInJob($topicId, $jobId)) {
                    header("Location: /views/forum.php?jobId=$jobId&error=invalid_topic");
                    exit();
                }

                // XSS protection on input
                $content = strip_tags($content, '<h1><h2><h3><h4><h5><h6><p><br><strong><em><u><s><blockquote><pre><ol><ul><li><a>');

                $post = new Post($conn);
                $post->setTopicID($topicId);
                $post->setUserId($userId);
                $post->setContent($content);
                $post->setDescription('');
                $post->setReactionNegative(0);
                $post->setReactionPositive(0);
                $post->setCreatedBy($userId);
                $post->setModifiedBy($userId);

                if ($post->post()) {

                    $postId = $post->getPostId();

                    handleAttachments($conn, $postId);

                    $notificationService = new TopicPostNotificationService($conn);
                    $notificationService->notifyTopicOwnerAboutNewPost($topicId, $postId, $userId);

                    header("Location: /views/forum.php?jobId=$jobId&topicId=$topicId");
                    exit();
                }
            }
            header("Location: /views/forum.php?jobId=$jobId&topicId=$topicId&error=post_failed");
            exit();

        case 'voteUp':
        case 'voteDown':
            $postId = intval($_POST['postId'] ?? 0);
            if ($postId > 0) {
                $post = new Post($conn);
                $post->vote($postId, $userId, $_POST['action'] === 'voteUp' ? 'up' : 'down');
            }
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
    }
}

header("Location: /views/forum.php");
exit();


// =========================
// HANDLE ATTACHMENTS (CLEAN)
// =========================
function handleAttachments($conn, $postId)
{
    if (!isset($_FILES['attachments'])) return;

    $allowed = ['jpg','jpeg','png','gif','pdf','txt','doc','docx'];

    foreach ($_FILES['attachments']['tmp_name'] as $key => $tmpName) {

        if ($_FILES['attachments']['error'][$key] !== 0) continue;

        $fileName = $_FILES['attachments']['name'][$key];
        $fileType = $_FILES['attachments']['type'][$key];
        $fileSize = $_FILES['attachments']['size'][$key];

        // Limit size (5MB)
        if ($fileSize > 5 * 1024 * 1024) continue;

        // Validate extension
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) continue;

        $fileData = file_get_contents($tmpName);

        $stmt = $conn->prepare("
            INSERT INTO postAttachments 
            (postId, fileName, fileType, fileSize, fileData)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->bind_param("issis", $postId, $fileName, $fileType, $fileSize, $fileData);
        $stmt->execute();
    }
}