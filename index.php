<?php
session_start();
if (!isset($_SESSION['userId'])) {
  header('Location: ' . "/views/loginsite.php");
} else {
    header('Location: ' . "/views/startpage.php");
}
?>