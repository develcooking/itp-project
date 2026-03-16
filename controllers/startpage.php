<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/middleware/checkAuth.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/Appointment.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/Post.php";

$userId = $_SESSION['userId'];

// Получаем ближайшие appointments (на неделю вперёд)
$appointmentModel = new Appointment($conn);
$upcomingAppointments = $appointmentModel->getUpcomingForUser($userId, 5);

// Получаем последние посты из 4 berufsbereiche (по 1 topic)
$postModel = new Post($conn);
$recentPosts = $postModel->getRecentForUserJobs($userId, 3);
// Передаём данные в view
require_once $_SERVER['DOCUMENT_ROOT'] . "/views/startpage.php";