<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/controllers/login.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/views/header.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/Job.php";

if (!isset($_SESSION['userId']) || $_SESSION['role'] !== 'Admin') {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

$job = new Job($conn);
$allBerufsbereiche = $job->getAll();
?>
<!-- DataTables CSS -->
<link rel="stylesheet" href="../resources/css/datatables.min.css">
<!-- DataTables JS -->
<script src="../resources/js/datatables.min.js"></script>

<div class="container-fluid px-3">

    <h1 class="fw-bold my-4">Berufsbereiche</h1>

    <!-- Berufsbereich erstellen -->
    <div class="card bg-white shadow p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h5 fw-semibold mb-0">Neuen Berufsbereich anlegen</h2>
            <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#createJobCollapse" aria-expanded="false" aria-controls="createJobCollapse">
                <i class="bi bi-plus-lg"></i> Erstellen
            </button>
        </div>
        <div class="collapse mt-3" id="createJobCollapse">
            <form id="createJobForm" class="row g-2 align-items-end">
                <div class="col-12 col-md-8">
                    <label for="newJobName" class="form-label">Name des Berufsbereichs</label>
                    <input type="text" class="form-control" id="newJobName" name="name" required placeholder="z.B. Informatik, Elektrotechnik...">
                </div>
                <div class="col-12 col-md-4">
                    <button class="btn btn-primary w-100" type="submit">Berufsbereich erstellen</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Berufsbereiche Tabelle -->
    <div class="card bg-white shadow p-4 mb-4">
        <h2 class="h5 fw-semibold mb-3">Alle Berufsbereiche</h2>
        <?php if (empty($allBerufsbereiche)): ?>
            <p class="text-muted mt-2">Aktuell sind keine Berufsbereiche angelegt.</p>
        <?php else: ?>
        <table id="jobsTable" class="display w-100">
            <thead>
            <tr>
                <th>Name</th>
                <th class="w-auto">Aktionen</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($allBerufsbereiche as $berufsbereich): ?>
                <tr data-job-id="<?= $berufsbereich['jobId'] ?>">
                    <td>
                        <span class="job-name-display"><?= htmlspecialchars($berufsbereich['name']) ?></span>
                        <input type="text" class="form-control job-name-input d-none" value="<?= htmlspecialchars($berufsbereich['name']) ?>">
                    </td>
                    <td class="text-nowrap" style="width:1%">
                        <button class="btn btn-outline-secondary btn-sm edit-btn" style="min-width:90px">Bearbeiten</button>
                        <button class="btn btn-success btn-sm save-btn d-none" style="min-width:90px">Speichern</button>
                        <button class="btn btn-danger btn-sm delete-btn" style="min-width:90px">Löschen</button>
                        <button class="btn btn-secondary btn-sm cancel-btn d-none" style="min-width:90px">Abbrechen</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</div>

<?php include $_SERVER['DOCUMENT_ROOT'] . "/views/footer.php"; ?>
<script src="/resources/js/adminJobs.js"></script>
