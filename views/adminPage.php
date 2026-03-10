<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/controllers/adminPage.php';
?>

<?php include './header.php'; ?>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="../resources/css/datatables.min.css">
    <!-- DataTables JS -->
    <script src="../resources/js/datatables.min.js"></script>

    <div class="container-fluid px-3">

    <h1 class="fw-bold my-4">Benutzerverwaltung</h1>

    <!-- Box: Nicht aktivierte Benutzer -->
    <div class="card bg-white shadow p-4 mb-4">
        <h2 class="h5 fw-semibold mb-3">Noch nicht aktivierte Benutzer</h2>
        <?php if (empty($pendingUsers)): ?>
            <p class="text-muted mt-2">Aktuell liegen keine neuen Anfragen vor.</p>
        <?php else: ?>
        <table id="pendingUsersTable" class="display w-100">
            <thead>
            <tr>
                <th>Benutzername</th>
                <th>Vorname</th>
                <th>Nachname</th>
                <th>Email</th>
                <th>Rolle</th>
                <th>Wann</th>
                <th class="w-auto">Aktionen</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($pendingUsers as $user): ?>
                <tr id="pending-row-<?= $user['userId'] ?>">
                    <td><?= htmlspecialchars($user['userName'] ?? '') ?></td>
                    <td><?= htmlspecialchars($user['firstName'] ?? '') ?></td>
                    <td><?= htmlspecialchars($user['lastName'] ?? '') ?></td>
                    <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                    <td><?= htmlspecialchars($user['role'] ?? '') ?></td>
                    <td><?= $user['createdAt'] ? date('d.m.Y H:i', strtotime($user['createdAt'])) : '' ?></td>
                    <td class="text-nowrap" style="width:1%">
                        <button class="btn btn-success btn-sm me-1 accept-btn" data-user-id="<?= $user['userId'] ?>">Akzeptieren</button>
                        <button class="btn btn-danger btn-sm reject-btn" data-user-id="<?= $user['userId'] ?>">Ablehnen</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- Box: Aktivierte Benutzer -->
    <div class="card bg-white shadow p-4 mb-4">
        <h2 class="h5 fw-semibold mb-3">Aktivierte Benutzer</h2>
        <table id="usersTable" class="display w-100">
            <thead>
            <tr>
                <th>Benutzername</th>
                <th>Vorname</th>
                <th>Nachname</th>
                <th>Email</th>
                <th>Rolle</th>
                <th>Wann</th>
                <th class="w-auto">Berufsbereiche</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($activatedUsers as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['userName'] ?? '') ?></td>
                    <td><?= htmlspecialchars($user['firstName'] ?? '') ?></td>
                    <td><?= htmlspecialchars($user['lastName'] ?? '') ?></td>
                    <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                    <td><?= htmlspecialchars($user['role'] ?? '') ?></td>
                    <td><?= $user['createdAt'] ? date('d.m.Y H:i', strtotime($user['createdAt'])) : '' ?></td>
                    <td class="text-nowrap" style="width:1%">
                        <button class="btn btn-primary btn-sm jobs-btn" data-user-id="<?= $user['userId'] ?>" data-username="<?= htmlspecialchars($user['userName'] ?? '') ?>">
                            Berufsbereiche
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<!-- Jobs Modal -->
<div class="modal fade" id="jobsModal" tabindex="-1" aria-labelledby="jobsModalTitle" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="jobsModalTitle">Berufsbereiche</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="jobsModalBody">
                <p>Laden...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>
    </div>
</main>

<?php include './footer.php'; ?>

<script>
    $(document).ready(function() {
        $('#pendingUsersTable').DataTable({
            order: [[5, 'desc']],
            language: {
                url: 'https://cdn.datatables.net/plug-ins/2.0.0/i18n/de-DE.json'
            }
        });

        $('#usersTable').DataTable({
            order: [[5, 'desc']],
            language: {
                url: 'https://cdn.datatables.net/plug-ins/2.0.0/i18n/de-DE.json'
            }
        });

        // Benutzer akzeptieren (aktivieren)
        $('.accept-btn').click(function() {
            const userId = $(this).data('userId');
            const row = $(this).closest('tr');

            if (!confirm('Benutzer wirklich akzeptieren und aktivieren?')) return;

            $.ajax({
                url: '/controllers/admin.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'toggleActivated',
                    userId: userId,
                    activated: 1
                },
                success: function(response) {
                    if (response.success) {
                        $('#pendingUsersTable').DataTable().row(row).remove().draw();
                        location.reload();
                    } else {
                        alert('Fehler: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('Fehler beim Aktivieren: ' + error);
                }
            });
        });

        // Benutzer ablehnen (löschen)
        $('.reject-btn').click(function() {
            const userId = $(this).data('userId');
            const row = $(this).closest('tr');

            if (!confirm('Benutzer wirklich ablehnen und löschen? Diese Aktion kann nicht rückgängig gemacht werden!')) return;

            $.ajax({
                url: '/controllers/admin.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'deleteUser',
                    userId: userId
                },
                success: function(response) {
                    if (response.success) {
                        $('#pendingUsersTable').DataTable().row(row).remove().draw();
                    } else {
                        alert('Fehler: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('Fehler beim Löschen: ' + error);
                }
            });
        });

        // Berufsbereiche Modal
        const jobsModalEl = document.getElementById('jobsModal');
        const jobsModal = new bootstrap.Modal(jobsModalEl);
        const modalTitle = $('#jobsModalTitle');
        const modalBody = $('#jobsModalBody');

        // Open modal
        $(document).on('click', '.jobs-btn', function() {
            const userId = $(this).data('userId');
            const userName = $(this).data('username');
            modalTitle.text('Berufsbereiche von ' + userName);
            modalBody.html('<p>Laden...</p>');
            jobsModal.show();

            $.ajax({
                url: '/controllers/admin.php',
                type: 'POST',
                dataType: 'json',
                data: { action: 'getJobsForUser', userId: userId },
                success: function(response) {
                    if (response.success) {
                        let html = '<div class="d-flex flex-column gap-2">';
                        if (response.jobs.length === 0) {
                            html += '<p>Keine Berufsbereiche vorhanden.</p>';
                        } else {
                            response.jobs.forEach(function(job) {
                                html += '<label class="d-flex align-items-center gap-2" role="button">';
                                html += '<input type="checkbox" class="job-checkbox" data-user-id="' + userId + '" data-job-id="' + job.jobId + '"' + (job.assigned ? ' checked' : '') + '>';
                                html += '<span>' + $('<span>').text(job.name).html() + '</span>';
                                html += '</label>';
                            });
                        }
                        html += '</div>';
                        modalBody.html(html);
                    } else {
                        modalBody.html('<p>Fehler: ' + response.message + '</p>');
                    }
                },
                error: function() {
                    modalBody.html('<p>Fehler beim Laden der Berufsbereiche.</p>');
                }
            });
        });

        // Toggle job checkbox
        $(document).on('change', '.job-checkbox', function() {
            const checkbox = $(this);
            const userId = checkbox.data('userId');
            const jobId = checkbox.data('jobId');
            const assign = checkbox.is(':checked') ? 1 : 0;

            $.ajax({
                url: '/controllers/admin.php',
                type: 'POST',
                dataType: 'json',
                data: { action: 'toggleUserJob', userId: userId, jobId: jobId, assign: assign },
                success: function(response) {
                    if (!response.success) {
                        alert('Fehler: ' + response.message);
                        checkbox.prop('checked', !checkbox.is(':checked'));
                    }
                },
                error: function() {
                    alert('Fehler beim Speichern der Zuweisung.');
                    checkbox.prop('checked', !checkbox.is(':checked'));
                }
            });
        });
    });
</script>