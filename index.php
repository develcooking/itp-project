<?php
session_start();
if (!isset($_SESSION['user'])) {
  include $_SERVER['DOCUMENT_ROOT'] . "/views/header.php";
  echo '<div>';
  echo '<h1 style="text-align: center;">You are not allowed to see this side</h1>';
  echo '<h1 style="text-align: center;">Please log in</h1>';
  echo '</div>';
  die();
} else {
  header('Location: ' . "/views/startpage.php");
}

?>