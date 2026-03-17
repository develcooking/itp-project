<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/middleware/checkAuth.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/Appointment.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/Post.php";

$userId = $_SESSION['userId'];

$appointmentModel = new Appointment($conn);
$upcomingAppointments = $appointmentModel->getUpcomingForUser($userId, 5);
$postModel = new Post($conn);
$recentPosts = $postModel->getRecentForUserJobs($userId, 3);

require_once $_SERVER['DOCUMENT_ROOT'] . "/views/startpage.php";