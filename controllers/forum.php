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