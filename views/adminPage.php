<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/controllers/adminPage.php';
?>

<?php include './header.php'; ?>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="../resources/css/datatables.min.css">
    <!-- DataTables JS -->
    <script src="../resources/js/datatables.min.js"></script>

    <div style="padding: 0 20px; font-family: Arial, Helvetica, sans-serif;">

    <h1 style="font-size: 1.8rem; font-weight: 600; margin-top: 28px; margin-bottom: 28px; color: #333;">Benutzerverwaltung</h1>

    <!-- Box: Nicht aktivierte Benutzer -->
    <div style="background: #fff; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); padding: 24px; margin-bottom: 28px;">
        <h2 style="font-size: 1.3rem; font-weight: 600; margin: 0 0 16px 0; color: #444;">Noch nicht aktivierte Benutzer</h2>
        <?php if (empty($pendingUsers)): ?>
            <p style="font-size: 1.1rem; color: #888; margin: 12px 0 0 0;">Aktuell liegen keine neuen Anfragen vor.</p>
        <?php else: ?>
        <table id="pendingUsersTable" class="display" style="width:100%">
            <thead>
            <tr>
                <th>Benutzername</th>
                <th>Vorname</th>
                <th>Nachname</th>
                <th>Email</th>
                <th>Rolle</th>
                <th>Wann</th>
                <th>Aktionen</th>
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
                    <td style="white-space:nowrap; width:1%;">
                        <button class="accept-btn" data-user-id="<?= $user['userId'] ?>" style="margin-right:4px; background-color:#28a745; color:#fff; border:none; padding:6px 12px; border-radius:4px; cursor:pointer;">Akzeptieren</button>
                        <button class="reject-btn" data-user-id="<?= $user['userId'] ?>" style="background-color:#dc3545; color:#fff; border:none; padding:6px 12px; border-radius:4px; cursor:pointer;">Ablehnen</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- Box: Aktivierte Benutzer -->
    <div style="background: #fff; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); padding: 24px; margin-bottom: 28px;">
        <h2 style="font-size: 1.3rem; font-weight: 600; margin: 0 0 16px 0; color: #444;">Aktivierte Benutzer</h2>
        <table id="usersTable" class="display" style="width:100%">
            <thead>
            <tr>
                <th>Benutzername</th>
                <th>Vorname</th>
                <th>Nachname</th>
                <th>Email</th>
                <th>Rolle</th>
                <th>Wann</th>
                <th>Berufsbereiche</th>
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
                    <td style="white-space:nowrap; width:1%;">
                        <button class="jobs-btn" data-user-id="<?= $user['userId'] ?>" data-username="<?= htmlspecialchars($user['userName'] ?? '') ?>">
                            Berufsbereiche
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<!-- Jobs Modal -->
<div id="jobsModalOverlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center;">
    <div style="background:#fff; border-radius:8px; width:500px; max-width:90%; max-height:80vh; display:flex; flex-direction:column; box-shadow:0 4px 20px rgba(0,0,0,0.3);">
        <div style="padding:16px 20px; border-bottom:1px solid #dee2e6;">
            <h4 id="jobsModalTitle" style="margin:0;">Berufsbereiche</h4>
        </div>
        <div id="jobsModalBody" style="padding:16px 20px; overflow-y:auto; flex:1;">
            <p>Laden...</p>
        </div>
        <div style="padding:12px 20px; border-top:1px solid #dee2e6; text-align:right;">
            <button id="jobsModalClose" style="padding:6px 20px; cursor:pointer;">OK</button>
        </div>
    </div>
</div>
    </div>
</main>

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
    });

    // Berufsbereiche Modal
    const overlay = $('#jobsModalOverlay');
    const modalTitle = $('#jobsModalTitle');
    const modalBody = $('#jobsModalBody');

    // Open modal
    $(document).on('click', '.jobs-btn', function() {
        const userId = $(this).data('userId');
        const userName = $(this).data('username');
        modalTitle.text('Berufsbereiche von ' + userName);
        modalBody.html('<p>Laden...</p>');
        overlay.css('display', 'flex');

        $.ajax({
            url: '/controllers/admin.php',
            type: 'POST',
            dataType: 'json',
            data: { action: 'getJobsForUser', userId: userId },
            success: function(response) {
                if (response.success) {
                    let html = '<div style="display:flex; flex-direction:column; gap:8px;">';
                    if (response.jobs.length === 0) {
                        html += '<p>Keine Berufsbereiche vorhanden.</p>';
                    } else {
                        response.jobs.forEach(function(job) {
                            html += '<label style="display:flex; align-items:center; gap:8px; cursor:pointer;">';
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

    // Close modal
    $('#jobsModalClose').click(function() {
        overlay.css('display', 'none');
    });
    overlay.click(function(e) {
        if (e.target === this) {
            overlay.css('display', 'none');
        }
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
</script>
<?php include './footer.php'; ?>