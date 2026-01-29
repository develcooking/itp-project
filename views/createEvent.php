<?php
include $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
include $homepath . "/views/header.php";
require_once $homepath . "/models/UserJobs.php";
require_once $homepath . "/models/Job.php";
?>
<form id="createEvent" method="post" action="../controllers/createEvent.php">
    <p>Erstelle Termin</p>
    <input type="title" name="title" required placeholder="Name des Termins">
    <input type="date" name="date1" required placeholder="Erstes Datum">
    <input type="time" name="time1" required placeholder="erste Uhrzeit">
    <input type="date" name="date2" required placeholder="zweites Datum">
    <input type="time" name="time2" required placeholder="zweites Uhrzeit">
    <input type="text" name="description" placeholder="In der Hautstraße">
    <select name="jobselection" required>
        <option value="" disabled selected>Bitte Berufsbereich auswählen</option>
        <?php
            $user_jobs = new UserJobs($conn);
            $job = new Job($conn);
            $userId = $_SESSION['userId'];
            //var_dump($userId);
            $jobIdsOfUser = $user_jobs->getJobsForUserByID($_SESSION['userId']);
            //var_dump($jobIdsOfUser);
        ?>
            <?php foreach ($jobIdsOfUser as $jobId):?>
                <option value="<?= $jobId; ?>"><?= $job->getNameById($jobId); ?></option>
            <?php endforeach; ?>

    </select>
    <button class="submitbtn" type="submit" name="createEvent">Create Event</button>
</form>