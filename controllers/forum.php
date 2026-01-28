<?php 

require_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/forum.php";
    
    $model = new Forum($conn);
    
    $bereiche = $model->getBereiche();
    
    // Variablen an View übergeben
   //echo($_SERVER['DOCUMENT_ROOT'] . '/views/forum_start.php');
    //require_once $_SERVER['DOCUMENT_ROOT'] . '/views/forum_start.php';
    //include $_SERVER['DOCUMENT_ROOT'] . "/views/forum_start.php";