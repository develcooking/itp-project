<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/controllers/login.php";
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
        switch ($_POST['action']) {
            case 'createTopic':
                $name = trim($_POST['topicName'] ?? '');
                $jobId = intval($_POST['jobId'] ?? 0);
                
                if (!empty($name) && $jobId > 0) {
                    $topic = new Topic($conn);
                    $topic->setName($name);
                    $topic->setJobId($jobId);
                    $topic->setUserId($_SESSION['userId']);
                    $topic->setCreatedBy($_SESSION['userId']);
                    $topic->setModifiedBy($_SESSION['userId']);
                    
                    if ($topic->post()) {
                        header("Location: /views/forum.php?jobId=$jobId&topicId=" . $topic->getTopicId());
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
                    $post = new Post($conn);
                    $post->setTopicID($topicId);
                    $post->setUserId($_SESSION['userId']);
                    $post->setContent($content);
                    $post->setDescription('');
                    $post->setReactionNegative(0);
                    $post->setReactionPositive(0);
                    $post->setCreatedBy($_SESSION['userId']);
                    $post->setModifiedBy($_SESSION['userId']);
                    
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
