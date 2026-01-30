<?php 

require_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/forum.php";
    
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

    $model = new Forum($conn);

    if (isset($_GET['bereich_id'])) {

    $bereich_id = (int)$_GET['bereich_id'];

    $topics = $model->getTopicsByBereich($bereich_id);

    require $_SERVER['DOCUMENT_ROOT'] . '/views/forum_topics.php';
    exit;
}
    
    $bereiche = $model->getBereiche();

    require $_SERVER['DOCUMENT_ROOT'] . '/views/forum_start.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'createTopic') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $bereich_id = (int)$_POST['bereich_id'];

    if (empty($title)) {
        // Fehlerbehandlung, z.B. Session Nachricht
        $_SESSION['error'] = 'Titel ist erforderlich.';
        header('Location: /controllers/forum.php?bereich_id=' . $bereich_id);
        exit;
    } else {
        $model->createTopic($bereich_id, $title, $description);
        header('Location: /controllers/forum.php?bereich_id=' . $bereich_id);
        exit;
    }
}