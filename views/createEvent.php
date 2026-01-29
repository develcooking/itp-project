<?php
include $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
include $homepath . "/views/header.php";
require_once $homepath . "/models/UserJobs.php";
require_once $homepath . "/models/Job.php";

$user_jobs = new UserJobs($conn);
$job = new Job($conn);
$jobIdsOfUser = $user_jobs->getJobsForUserByID($_SESSION['userId']);
?>

<div class="container min-vh-100 d-flex justify-content-center align-items-center my-5">
  <div class="row w-100 justify-content-center">
    <div class="col-12 col-sm-10 col-md-8 col-lg-6">
      
      <div class="card bg-light shadow p-4">
        <div class="text-center">
          <h2 class="fw-bold mb-2">Termin erstellen</h2>
          <p class="text-muted mb-4">Füllen Sie die Details für den neuen Termin aus.</p>
        </div>

        <form id="createEvent" method="post" action="../controllers/createEvent.php">
          
          <!-- Titel -->
          <div class="form-floating mb-3">
            <input type="text" name="title" class="form-control" id="title" placeholder="Name des Termins" required>
            <label for="title">Name des Termins</label>
          </div>

          <!-- Start Datum & Zeit -->
          <div class="row g-2 mb-3">
            <div class="col-md">
              <div class="form-floating">
                <input type="date" name="startdate" class="form-control" id="startdate" required>
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
                <input type="date" name="enddate" class="form-control" id="enddate" required>
                <label for="enddate">End-Datum</label>
              </div>
            </div>
            <div class="col-md">
              <div class="form-floating">
                <input type="time" name="endtime" class="form-control" id="endtime" required>
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

          <button class="btn btn-outline-primary btn-lg w-100" type="submit" name="createEvent">
            Termin speichern
          </button>

          <div class="mt-3 text-center">
            <a href="appointmentManagement.php" class="text-muted small text-decoration-none">Abbrechen</a>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>

<?php include $homepath . "/views/footer.php"; ?>