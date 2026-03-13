<?php
#include $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/views/header.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/UserJobs.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/Job.php";

$user_jobs = new UserJobs($conn);
$job = new Job($conn);
$isAdmin = isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin';

if ($isAdmin) {
    // Admins see all jobs
    $allJobs = $job->getAll();
    $jobIdsOfUser = array_column($allJobs, 'jobId');
} else {
    $jobIdsOfUser = $user_jobs->getJobsForUserByID($_SESSION['userId']);
}
?>

<script src='../resources/js/fullCalendar.min.js'></script>
<script src='../resources/js/fullCalendarBootstrapPlugin.js'></script>

<div class="container-fluid pb-4 pt-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary">
                    <h5 class="mb-0"><i class="bi bi-funnel"></i> Filter</h5>
                </div>
                <div class="card-body">
                    <!-- Berufsbereich Filter -->
                    <div class="mb-4">
                        <label for="filterJob" class="form-label fw-bold">Berufsbereich</label>
                        <select id="filterJob" class="form-select">
                            <option value="">Alle Bereiche</option>
                            <?php foreach ($jobIdsOfUser as $jobId): ?>
                                <option value="<?= htmlspecialchars($jobId); ?>">
                                    <?= htmlspecialchars($job->getNameById($jobId)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Zeitraum Filter -->
                    <div class="mb-3">
                        <label for="filterStartDate" class="form-label fw-bold">Von</label>
                        <input type="date" id="filterStartDate" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="filterEndDate" class="form-label fw-bold">Bis</label>
                        <input type="date" id="filterEndDate" class="form-control">
                    </div>
                    <button id="resetFilter" class="btn btn-outline-secondary btn-sm w-100 mt-2">Filter zurücksetzen</button>
                </div>
            </div>
        </div>

        <!-- Calendar -->
        <div class="col-md-9 col-lg-10">
            <div class="calendar-container shadow-sm p-3 bg-white rounded">
                <div id='calendar'></div>
            </div>
        </div>
    </div>
</div>
<?php if (strtolower($_SESSION['role']) != 'ausbilder'): ?>
<!-- Modal -->
<div class="modal fade" id="calendermanagementModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="calendermanagementModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="createEvent" method="post" action="../controllers/createEvent.php">
                <div class="modal-header">
                    <h1 class="modal-title fs-5 text-center" id="calendermanagementModalLabel">Termin erstellen</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-0">
                    <div class="text-center">
                        <p class="text-muted mb-4">Füllen Sie die Details für den neuen Termin aus.</p>
                    </div>

                    <!-- Titel -->
                    <div class="form-floating mb-3">
                        <input type="text" name="title" class="form-control" id="title" placeholder="Name des Termins" required>
                        <label for="title">Name des Termins</label>
                    </div>

                    <!-- Start Datum & Zeit -->
                    <div class="row g-2 mb-3">
                        <div class="col-md">
                            <div class="form-floating">
                                <input type="date" name="startdate" class="form-control" id="startdate" required value="" min="<?php echo date('Y-m-d'); ?>">
                                <label for="startdate">Start-Datum</label>
                            </div>
                        </div>
                        <div class="col-md">
                            <div class="form-floating">
                                <input type="time" name="starttime" class="form-control" id="starttime" required>
                                <label for="starttime">Start-Uhrzeit</label>
                            </div>
                        </div>
                    </div>

                    <!-- Ende Datum & Zeit -->
                    <div class="row g-2 mb-3">
                        <div class="col-md">
                            <div class="form-floating">
                                <input type="date" name="enddate" class="form-control" id="enddate" required value=""  min="<?php echo date('Y-m-d'); ?>" max="2100-12-31">
                                <label for="enddate">End-Datum</label>
                            </div>
                        </div>
                        <div class="col-md">
                            <div class="form-floating">
                                <input type="time" name="endtime" class="form-control" id="endtime" min="00:00" max="23:59" required>
                                <label for="endtime">End-Uhrzeit</label>
                            </div>
                        </div>
                    </div>

                    <!-- Beschreibung -->
                    <div class="form-floating mb-3">
                        <input type="text" name="description" class="form-control" id="description" placeholder="Ort oder Beschreibung">
                        <label for="description">Beschreibung / Ort (z.B. Hauptstraße)</label>
                    </div>

                    <!-- Berufsbereich Auswahl -->
                    <div class="form-floating mb-4">
                        <select name="jobselection" class="form-select" id="jobselection" required>
                            <option value="" disabled selected>Bitte wählen...</option>
                            <?php foreach ($jobIdsOfUser as $jobId): ?>
                                <option value="<?= htmlspecialchars($jobId); ?>">
                                    <?= htmlspecialchars($job->getNameById($jobId)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label for="jobselection">Berufsbereich</label>
                    </div>
                    <div id="dateError" class="alert alert-danger d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <button id="modal_submit_btn" type="submit" class="btn btn-primary disabled" aria-disabled="true">Termin speichern</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
<!-- Modal Change-->
<div class="modal fade" id="calenderChageModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="calenderChageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="changeEventForm" method="post" action="../controllers/changeEvent.php">
                <div class="modal-header">
                    <h1 class="modal-title fs-5 text-center" id="calenderChageModalLabel">Termin ändern</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-0">
                    <div class="text-center" id="changeModalTitle">
                        <p class="text-muted mb-4">Füllen Sie die Details für die Änderung des Termin aus.</p>
                    </div>

                    <!-- Titel -->
                    <div class="form-floating mb-3">
                        <input type="text" name="changetitle" class="form-control" id="changetitle" placeholder="Name des Termins" required>
                        <label for="changetitle">Name des Termins</label>
                    </div>

                    <!-- Start Datum & Zeit -->
                    <div class="row g-2 mb-3">
                        <div class="col-md">
                            <div class="form-floating">
                                <input type="date" name="changestartdate" class="form-control" id="changestartdate" required value="">
                                <label for="changestartdate">Start-Datum</label>
                            </div>
                        </div>
                        <div class="col-md">
                            <div class="form-floating">
                                <input type="time" name="changestarttime" class="form-control" id="changestarttime" required>
                                <label for="changestarttime">Start-Uhrzeit</label>
                            </div>
                        </div>
                    </div>

                    <!-- Ende Datum & Zeit -->
                    <div class="row g-2 mb-3">
                        <div class="col-md">
                            <div class="form-floating">
                                <input type="date" name="changeenddate" class="form-control" id="changeenddate" required value="">
                                <label for="changeenddate">End-Datum</label>
                            </div>
                        </div>
                        <div class="col-md">
                            <div class="form-floating">
                                <input type="time" name="changeendtime" class="form-control" id="changeendtime" required>
                                <label for="changeendtime">End-Uhrzeit</label>
                            </div>
                        </div>
                    </div>

                    <!-- Beschreibung -->
                    <div class="form-floating mb-3">
                        <input type="text" name="changedescription" class="form-control" id="changedescription" placeholder="Ort oder Beschreibung">
                        <label for="changedescription">Beschreibung / Ort (z.B. Hauptstraße)</label>
                    </div>

                    <!-- Berufsbereich Auswahl -->
                    <div class="form-floating mb-4">
                        <select name="changejobselection" class="form-select" id="changejobselection" required>
                            <option value="" disabled selected>Bitte wählen...</option>
                            <?php foreach ($jobIdsOfUser as $jobId): ?>
                                <option value="<?= htmlspecialchars($jobId); ?>">
                                    <?= htmlspecialchars($job->getNameById($jobId)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label for="changejobselection">Berufsbereich</label>
                    </div>
                </div>
                <input name="changeappointmentId" id="changeappointmentId" value="" class="invisible h-0 w-0">
                <div class="modal-footer" id="changeModalFooter">
                    <div id="modalFooterChangeButtons">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                        <button id="modal_submit_btn" type="submit" class="btn btn-primary" aria-disabled="false">Termin speichern</button>
                    </div>
                    <div id="modalFooterCreatedBy" class="mx-auto" style="display: none;">
                        <input type="text" class="form-control disabled" disabled id="modalFooterCreatedByInput" placeholder="Creator" value="Creator Unbekannt">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    const currentUserId = <?= $_SESSION['userId']; ?>;
    const currentUserRole = "<?= $_SESSION['role']; ?>";
</script>
<script src="/resources/js/loadFullCalender.js"></script>
<?php
include "footer.php"
?>