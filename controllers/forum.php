<?php 

require_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/forum.php";
    
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

    $model = new Forum($conn);

    if (isset($_GET['bereich_id'])) {

    //foreach ($_GET as $id) { echo $id;};
    $bereich_id = (int)$_GET['bereich_id'];
    $bereich = (string)$_GET['name'];

    $topics = $model->getTopicsByBereich($bereich_id);

    require $_SERVER['DOCUMENT_ROOT'] . '/views/forum_topics.php';
    exit;
    }
    
    $bereiche = $model->getBereiche();

    require $_SERVER['DOCUMENT_ROOT'] . '/views/forum_start.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'createTopic') {
    $title = trim($_POST['title']);
    //$description = trim($_POST['description']);
    $bereich_id = (int)$_POST['bereich_id'];
    $bereich = (string)$_POST['name'];
    $content = ($_POST['content']);
    echo "<script>console.log('$bereich');</script>";
    echo $_POST;
    if (empty($title) || empty($content)) {
        // Fehlerbehandlung, z.B. Session Nachricht
        $_SESSION['error'] = 'Titel und initiales Beitrag sind erforderlich.';
        header('Location: /controllers/forum.php?bereich_id=' . $bereich_id . '&name=' . $bereich);
        exit;
    } else {
        $model->createTopic($bereich_id, $title, $content);
        header('Location: /controllers/forum.php?bereich_id=' . $bereich_id . '&name=' . $bereich); 
        exit;
    }
}