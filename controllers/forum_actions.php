<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/controllers/login.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/models/Forum.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/models/Topic.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/models/Post.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['userId'])) {
    header("Location: /views/loginsite.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $forumModel = new Forum($conn);
        $userId = $_SESSION['userId'];

        switch ($_POST['action']) {
            case 'createTopic':
                $name = trim($_POST['topicName'] ?? '');
                $jobId = intval($_POST['jobId'] ?? 0);
                $content = trim($_POST['postContent'] ?? '');
                $topicId = intval($_POST['topicId'] ?? 0);
                $jobId = intval($_POST['jobId'] ?? 0);
                
                if (!empty($name) && $jobId > 0) {
                    // Check access
                    if (!$forumModel->hasAccess($userId, $jobId)) {
                        header("Location: /views/forum.php?error=no_access");
                        exit();
                    }

                    $topic = new Topic($conn);
                    $topic->setName($name);
                    $topic->setJobId($jobId);
                    $topic->setUserId($userId);
                    $topic->setCreatedBy($userId);
                    $topic->setModifiedBy($userId);
                    
                    //if ($topic->post()) {
                    //    header("Location: /views/forum.php?jobId=$jobId&topicId=" . $topic->getTopicId());
                    //    exit();
                    //}

                    // Basic XSS protection on input (optional, but good practice)
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
                        header("Location: /views/forum.php?jobId=$jobId&topicId=$topicId");
                        exit();
                    }
                }
                header("Location: /views/forum.php?jobId=$jobId&error=topic_failed");
                break;

            case 'createPost':
                $content = trim($_POST['postContent'] ?? '');
                $topicId = intval($_POST['topicId'] ?? 0);
                $jobId = intval($_POST['jobId'] ?? 0);
                
                if (!empty($content) && $topicId > 0) {
                    // Check access to jobId
                    if (!$forumModel->hasAccess($userId, $jobId)) {
                        header("Location: /views/forum.php?error=no_access");
                        exit();
                    }

                    // Ensure topic is in this job
                    if (!$forumModel->isTopicInJob($topicId, $jobId)) {
                        header("Location: /views/forum.php?jobId=$jobId&error=invalid_topic");
                        exit();
                    }

                    // Basic XSS protection on input (optional, but good practice)
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
                        header("Location: /views/forum.php?jobId=$jobId&topicId=$topicId");
                        exit();
                    }
                }
                header("Location: /views/forum.php?jobId=$jobId&topicId=$topicId&error=post_failed");
                break;
        }
    }
}

header("Location: /views/forum.php");
exit();
