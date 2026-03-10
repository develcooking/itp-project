<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}
include_once $_SERVER['DOCUMENT_ROOT'] . "/controllers/login.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/models/Topic.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/models/Post.php";
include_once __DIR__ . "/api_helper.php";

if (!isset($_SESSION)) session_start();

$method = $_SERVER['REQUEST_METHOD'];
$resource = $_GET['resource'] ?? 'topics'; // 'topics' or 'posts'

if ($resource === 'topics') {
    $topic = new Topic($conn);
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                if ($topic->getById(intval($_GET['id']))) {
                    $data = [
                        'topicId' => $topic->getTopicId(),
                        'name' => $topic->getName(),
                        'jobId' => $topic->getJobId(),
                        'userId' => $topic->getUserId(),
                        'createdBy' => $topic->getCreatedBy(),
                        'modifiedBy' => $topic->getModifiedBy(),
                    ];
                    sendResponse(true, $data);
                } else {
                    sendResponse(false, null, 'Topic not found', 404);
                }
            } elseif (isset($_GET['name'])) {
                if ($topic->getByName($_GET['name'])) {
                    $data = [
                        'topicId' => $topic->getTopicId(),
                        'name' => $topic->getName(),
                        'jobId' => $topic->getJobId(),
                        'userId' => $topic->getUserId(),
                        'createdBy' => $topic->getCreatedBy(),
                        'modifiedBy' => $topic->getModifiedBy(),
                    ];
                    sendResponse(true, $data);
                } else {
                    sendResponse(false, null, 'Topic not found', 404);
                }
            }
            else {
                sendResponse(true, $topic->getAll());
            }
            break;

        case 'POST':
            checkLoggedIn();
            $data = getJsonInput();
            if (!$data || !isset($data['name']) || !isset($data['jobId'])) {
                sendResponse(false, null, 'Missing title or jobId', 400);
            }
            $topic->setName($data['name']);
            $topic->setJobId(intval($data['jobId']));
            $topic->setUserId($_SESSION['userId']);
            $topic->setCreatedBy($_SESSION['userId']);
            $topic->setModifiedBy($_SESSION['userId']);

            if ($topic->post()) {
                sendResponse(true, ['topicId' => $conn->insert_id], 'Topic created successfully', 204);
            } else {
                sendResponse(false, null, 'Failed to create topic', 500);
            }
            break;

        case 'PUT':
            checkLoggedIn();
            $data = getJsonInput();
            if (!$data || !isset($data['topicId'])) sendResponse(false, null, 'Missing topicId', 400);
            $id = intval($data['topicId']);
            if (!$topic->getById($id)) sendResponse(false, null, 'Topic not found', 404);

            if ($_SESSION['role'] !== 'admin' && $_SESSION['userId'] !== $topic->getUserId()) {
                sendResponse(false, null, 'Unauthorized', 401);
            }

            if (isset($data['name'])) $topic->setName($data['name']);
            if (isset($data['jobId'])) $topic->setJobId(intval($data['jobId']));

            if ($topic->update($id)) {
                sendResponse(true, null, 'Topic updated successfully', 204);
            } else {
                sendResponse(false, null, 'Failed to update topic', 500);
            }
            break;

        case 'DELETE':
            checkLoggedIn();
            $data = getJsonInput();
            $id = $data['topicId'] ?? $_GET['id'] ?? null;
            if (!$id) sendResponse(false, null, 'Missing topicId', 400);
            if (!$topic->getById(intval($id))) sendResponse(false, null, 'Topic not found', 404);

            if ($_SESSION['role'] !== 'admin' && $_SESSION['userId'] !== $topic->getUserId()) {
                sendResponse(false, null, 'Unauthorized', 403);
            }

            if ($topic->delete(intval($id))) {
                sendResponse(true, null, 'Topic deleted successfully', 204);
            } else {
                sendResponse(false, null, 'Failed to delete topic', 500);
            }
            break;
    }
} elseif ($resource === 'posts') {
    $post = new Post($conn);
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                if ($post->getById(intval($_GET['id']))) {
                    $data = [
                        'postId' => $post->getPostId(),
                        'topicId' => $post->getTopicId(),
                        'userId' => $post->getUserId(),
                        'content' => $post->getContent(),
                        'description' => $post->getDescription(),
                        'reaction_negative' => $post->getReactionNegative(),
                        'reaction_positive' => $post->getReactionPositive(),
                        'createdBy' => $post->getCreatedBy(),
                        'modifiedBy' => $post->getModifiedBy()
                    ];
                    sendResponse(true, $data);
                } else {
                    sendResponse(false, null, 'Post not found', 404);
                }
            } else {
                // Optionally filter by topicId
                $allPosts = $post->getAll();
                if (isset($_GET['topicId'])) {
                    $allPosts = array_values(array_filter($allPosts, function($p) {
                        return $p['topicId'] == $_GET['topicId'];
                    }));
                }
                sendResponse(true, $allPosts);
            }
            break;

        case 'POST':
            checkLoggedIn();
            $data = getJsonInput();
            if (!$data || !isset($data['topicId']) || !isset($data['content'])) {
                sendResponse(false, null, 'Missing topicId or content', 400);
            }
            $post->setTopicID(intval($data['topicId']));
            $post->setUserId($_SESSION['userId']);
            $post->setContent($data['content']);
            $post->setDescription($data['description'] ?? '');
            $post->setReactionNegative(0);
            $post->setReactionPositive(0);
            $post->setContent($_SESSION['userId']);
            $post->setModifiedBy($_SESSION['userId']);

            if ($post->post()) {
                sendResponse(true, ['postId' => $conn->insert_id], 'Post created successfully', 201);
            } else {
                sendResponse(false, null, 'Failed to create post', 500);
            }
            break;

        case 'PUT':
            checkLoggedIn();
            $data = getJsonInput();
            if (!$data || !isset($data['postId'])) sendResponse(false, null, 'Missing postId', 400);
            $id = intval($data['postId']);
            if (!$post->getById($id)) sendResponse(false, null, 'Post not found', 404);

            if ($_SESSION['role'] !== 'admin' && $_SESSION['userId'] !== $post->getUserId()) {
                sendResponse(false, null, 'Unauthorized', 403);
            }

            if (isset($data['content'])) $post->setContent($data['content']);
            if (isset($data['description'])) $post->setDescription($data['description']);
            if (isset($data['reaction_negative'])) $post->setReactionNegative(intval($data['reaction_negative']));
            if (isset($data['reaction_positive'])) $post->setReactionPositive(intval($data['reaction_positive']));

            if ($post->update($id)) {
                sendResponse(true, null, 'Post updated successfully');
            } else {
                sendResponse(false, null, 'Failed to update post', 500);
            }
            break;

        case 'DELETE':
            checkLoggedIn();
            $data = getJsonInput();
            $id = $data['postId'] ?? $_GET['id'] ?? null;
            if (!$id) sendResponse(false, null, 'Missing postId', 400);
            if (!$post->getById(intval($id))) sendResponse(false, null, 'Post not found', 404);

            if (empty($_SESSION['userId'])) {
                sendResponse(false, null, 'Unauthorized: Login required', 401);
            }
            if ($_SESSION['role'] !== 'admin' && $_SESSION['userId'] !== $post->getUserId()) {
                sendResponse(false, null, 'Unauthorized', 401);
            }

            if ($post->delete(intval($id))) {
                sendResponse(true, null, 'Post deleted successfully');
            } else {
                sendResponse(false, null, 'Failed to delete post', 500);
            }
            break;
    }
} else {
    sendResponse(false, null, 'Invalid resource', 400);
}

$conn->close();
