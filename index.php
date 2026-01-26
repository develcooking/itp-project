<?php
session_start();
if (!isset($_SESSION['user'])) {
  header('Location: ' . "/views/loginsite.php");
} else {
    header('Location: ' . "/views/startpage.php");
}
?>