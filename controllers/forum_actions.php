<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/middleware/startSession.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/controllers/login.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/models/Forum.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/models/Topic.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/models/Post.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/models/Comment.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/app/services/TopicPostNotificationService.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/middleware/HtmlSanitizer.php";

if (!isset($_SESSION['userId'])) {
    header("Location: /views/loginsite.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $forumModel = new Forum($conn);
        $userId = $_SESSION['userId'];
        $isAdmin = isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin';

        switch ($_POST['action']) {
            case 'createTopic':
                $name = trim($_POST['topicName'] ?? '');
                $jobId = intval($_POST['jobId'] ?? 0);
                $content = trim($_POST['postContent'] ?? '');
                //$topicId = intval($_POST['topicId'] ?? 0);
                $jobId = intval($_POST['jobId'] ?? 0);

                if (!empty($name) && $jobId > 0) {
                    if (!$isAdmin) {
                        // Check access
                        if (!$forumModel->hasAccess($userId, $jobId)) {
                            header("Location: /views/forum.php?error=no_access");
                            exit();
                        }
                    }

                    $topic = new Topic($conn);
                    $topic->setName($name);
                    $topic->setJobId($jobId);
                    $topic->setUserId($userId);
                    $topic->setCreatedBy($userId);
                    $topic->setModifiedBy($userId);

                    if ($topic->post()) {

                        // Basic XSS protection on input (optional, but good practice)
                        $content = HtmlSanitizer::sanitize($content);
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
                            header("Location: /views/forum.php?jobId=$jobId&topicId=$topicId");
                            exit();
                        }
                    }
                }
                header("Location: /views/forum.php?jobId=$jobId&error=topic_failed");
                break;

            case 'createPost':
                $content = trim($_POST['postContent'] ?? '');
                $topicId = intval($_POST['topicId'] ?? 0);
                $jobId = intval($_POST['jobId'] ?? 0);

                if (!empty($content) && $topicId > 0) {
                    if (!$isAdmin) {
                        // Check access to jobId
                        if (!$forumModel->hasAccess($userId, $jobId)) {
                            header("Location: /views/forum.php?error=no_access");
                            exit();
                        }
                    }

                    // Ensure topic is in this job
                    if (!$forumModel->isTopicInJob($topicId, $jobId)) {
                        header("Location: /views/forum.php?jobId=$jobId&error=invalid_topic");
                        exit();
                    }

                    // XSS protection on input
                    $content = HtmlSanitizer::sanitize($content);

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
                        $notificationService = new TopicPostNotificationService($conn);
                        $notificationService->notifyTopicOwnerAboutNewPost($topicId, $post->getPostId(), $userId);

                        header("Location: /views/forum.php?jobId=$jobId&topicId=$topicId");
                        exit();
                    }
                }
                header("Location: /views/forum.php?jobId=$jobId&topicId=$topicId&error=post_failed");
                break;

            case 'createComment':
                $content = trim($_POST['commentContent'] ?? '');
                $postId = intval($_POST['postId'] ?? 0);
                $topicId = intval($_POST['topicId'] ?? 0);
                $jobId = intval($_POST['jobId'] ?? 0);

                if (!empty($content) && $postId > 0) {
                    if (!$isAdmin) {
                        // Check access to jobId
                        if (!$forumModel->hasAccess($userId, $jobId)) {
                            header("Location: /views/post_details.php?postId=$postId&topicId=$topicId&jobId=$jobId&error=no_access");
                            exit();
                        }
                    }

                    // Ensure post is in this topic
                    $postModel = new Post($conn);
                    if (!$postModel->getById($postId) || $postModel->getTopicId() != $topicId) {
                        header("Location: /views/post_details.php?postId=$postId&topicId=$topicId&jobId=$jobId&error=invalid_post");
                        exit();
                    }

                    // XSS protection on input
                    $content = strip_tags($content, '<h1><h2><h3><h4><h5><h6><p><br><strong><em><u><s><blockquote><pre><ol><ul><li><a>');

                    $comment = new Comment($conn);
                    $comment->setPostId($postId);
                    $comment->setUserId($userId);
                    $comment->setContent($content);

                    if ($comment->create()) {
                        header("Location: /views/post_details.php?postId=$postId&topicId=$topicId&jobId=$jobId");
                        exit();
                    }
                }
                header("Location: /views/post_details.php?postId=$postId&topicId=$topicId&jobId=$jobId&error=comment_failed");
                break;

            case 'voteUp':
                $postId = intval($_POST['postId'] ?? 0);
                if ($postId > 0) {
                    $post = new Post($conn);
                    $post->vote($postId, $_SESSION['userId'], 'up');
                }
                header("Location: " . $_SERVER['HTTP_REFERER']);
                exit();

            case 'voteDown':
                $postId = intval($_POST['postId'] ?? 0);
                if ($postId > 0) {
                    $post = new Post($conn);
                    $post->vote($postId, $_SESSION['userId'], 'down');
                }
                header("Location: " . $_SERVER['HTTP_REFERER']);
                exit();

            case 'togglePin':

                if (!$isAdmin) {
                    header("Location: /views/forum.php?error=no_permission");
                    exit();
                }

                $topicId = intval($_POST['topicId']);

                $topic = new Topic($conn);
                if ($topic->togglePin($topicId)) {
                    header("Location: " . $_SERVER['HTTP_REFERER']);
                    exit();
                }

                break;

            case 'editPost':
                $postId = intval($_POST['postId'] ?? 0);
                $content = trim($_POST['postContent'] ?? '');
                if ($postId > 0 && !empty($content)) {
                    $post = new Post($conn);
                    if ($post->getById($postId)) {
                        if ($post->getUserId() == $userId || $isAdmin) {
                            $content = HtmlSanitizer::sanitize($content);
                            $post->setContent($content);
                            $post->setModifiedBy($userId);
                            if ($post->update($postId)) {
                                header("Location: " . $_SERVER['HTTP_REFERER']);
                                exit();
                            }
                        }
                    }
                }
                header("Location: /views/forum.php?error=edit_failed");
                break;

            case 'deletePost':
                $postId = intval($_POST['postId'] ?? 0);
                if ($postId > 0) {
                    $post = new Post($conn);
                    if ($post->getById($postId)) {
                        if ($post->getUserId() == $userId || $isAdmin) {
                            if ($post->delete($postId)) {
                                header("Location: " . $_SERVER['HTTP_REFERER']);
                                exit();
                            }
                        }
                    }
                }
                header("Location: /views/forum.php?error=delete_failed");
                break;
        }
    }
}

header("Location: /views/forum.php");
exit();
