<?php 

    require_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/models/forum.php";
    
    $model = new Forum($conn);
    
    $bereiche = $model->getBereiche();
    
    // Variablen an View übergeben
    require $_SERVER['DOCUMENT_ROOT'] . '/views/forum_start.php';