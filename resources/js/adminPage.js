$(document).ready(function() {
    $('#pendingUsersTable').DataTable({
        order: [[5, 'desc']],
        language: {
            url: '/resources/js/datatables-de-DE.json'
        }
    });

    $('#usersTable').DataTable({
        order: [[5, 'desc']],
        language: {
            url: '/resources/js/datatables-de-DE.json'
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

    // Rolle ändern per Dropdown
    $(document).on('change', '.role-dropdown', function() {
        const dropdown = $(this);
        const userId = dropdown.data('userId');
        const newRole = dropdown.val();

        $.ajax({
            url: '/controllers/admin.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'updateRole',
                userId: userId,
                role: newRole
            },
            success: function(response) {
                if (!response.success) {
                    alert('Fehler: ' + response.message);
                    location.reload();
                }
            },
            error: function(xhr, status, error) {
                alert('Fehler beim Ändern der Rolle: ' + error);
                location.reload();
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
                        // All/None Toggle
                        html += '<div class="form-check form-switch mb-2 border-bottom pb-2">';
                        html += '  <input class="form-check-input" type="checkbox" role="switch" id="toggleAllJobs" data-user-id="' + userId + '">';
                        html += '  <label class="form-check-label fw-bold" for="toggleAllJobs">Alle auswählen / abwählen</label>';
                        html += '</div>';

                        response.jobs.forEach(function(job) {
                            html += '<label class="d-flex align-items-center gap-2" role="button">';
                            html += '<input type="checkbox" class="job-checkbox" data-user-id="' + userId + '" data-job-id="' + job.jobId + '"' + (job.assigned ? ' checked' : '') + '>';
                            html += '<span>' + $('<span>').text(job.name).html() + '</span>';
                            html += '</label>';
                        });
                    }
                    html += '</div>';
                    modalBody.html(html);
                    updateToggleAllState();
                } else {
                    modalBody.html('<p>Fehler: ' + response.message + '</p>');
                }
            },
            error: function() {
                modalBody.html('<p>Fehler beim Laden der Berufsbereiche.</p>');
            }
        });
    });

    // Toggle All Jobs
    $(document).on('change', '#toggleAllJobs', function() {
        const isChecked = $(this).is(':checked');
        const userId = $(this).data('userId');
        
        // Loop through all checkboxes and trigger change if they differ from master toggle
        $('.job-checkbox').each(function() {
            if ($(this).is(':checked') !== isChecked) {
                $(this).prop('checked', isChecked).trigger('change');
            }
        });
    });

    // Function to update the state of the "Toggle All" checkbox
    function updateToggleAllState() {
        const total = $('.job-checkbox').length;
        const checked = $('.job-checkbox:checked').length;
        const toggleAll = $('#toggleAllJobs');

        if (total > 0) {
            toggleAll.prop('checked', total === checked);
        }
    }

    // Toggle job checkbox
    $(document).on('change', '.job-checkbox', function(e, isBulk) {
        const checkbox = $(this);
        const userId = checkbox.data('userId');
        const jobId = checkbox.data('jobId');
        const assign = checkbox.is(':checked') ? 1 : 0;

        // If not part of a bulk operation, we might want to update the master toggle
        updateToggleAllState();

        $.ajax({
            url: '/controllers/admin.php',
            type: 'POST',
            dataType: 'json',
            data: { action: 'toggleUserJob', userId: userId, jobId: jobId, assign: assign },
            success: function(response) {
                if (!response.success) {
                    alert('Fehler: ' + response.message);
                    checkbox.prop('checked', !checkbox.is(':checked'));
                    updateToggleAllState();
                }
            },
            error: function() {
                alert('Fehler beim Speichern der Zuweisung.');
                checkbox.prop('checked', !checkbox.is(':checked'));
                updateToggleAllState();
            }
        });
    });

    // Aktivierten Benutzer löschen
    $(document).on('click', '.delete-user-btn', function() {
        const btn = $(this);
        const userId = btn.data('userId');
        const userName = btn.data('username');
        const row = btn.closest('tr');

        if (!confirm('Benutzer "' + userName + '" wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden!')) return;

        $.ajax({
            url: '/controllers/admin.php',
            type: 'POST',
            dataType: 'json',
            data: { action: 'deleteUser', userId: userId },
            success: function(response) {
                if (response.success) {
                    $('#usersTable').DataTable().row(row).remove().draw();
                } else {
                    alert('Fehler: ' + response.message);
                }
            },
            error: function() {
                alert('Fehler beim Löschen des Benutzers.');
            }
        });
    });

    // Sperren Modal öffnen
    const blockModalEl = document.getElementById('blockModal');
    const blockModal = new bootstrap.Modal(blockModalEl);
    let blockTargetUserId = null;

    $(document).on('click', '.block-btn', function() {
        const btn = $(this);
        blockTargetUserId = btn.data('userId');
        const userName = btn.data('username');
        $('#blockModalUserName').text('Benutzer: ' + userName);
        blockModal.show();
    });

    // Dauer auswählen und Sperren ausführen
    $(document).on('click', '.block-duration-btn', function() {
        const duration = $(this).data('duration');
        if (!blockTargetUserId) return;

        $.ajax({
            url: '/controllers/admin.php',
            type: 'POST',
            dataType: 'json',
            data: { action: 'blockUser', userId: blockTargetUserId, duration: duration },
            success: function(response) {
                if (response.success) {
                    blockModal.hide();
                    location.reload();
                } else {
                    alert('Fehler: ' + response.message);
                }
            },
            error: function() {
                alert('Fehler beim Sperren des Benutzers.');
            }
        });
    });

    // Benutzer freigeben
    $(document).on('click', '.unblock-btn', function() {
        const btn = $(this);
        const userId = btn.data('userId');
        const userName = btn.data('username');

        if (!confirm('Benutzer "' + userName + '" wirklich freigeben?')) return;

        $.ajax({
            url: '/controllers/admin.php',
            type: 'POST',
            dataType: 'json',
            data: { action: 'unblockUser', userId: userId },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Fehler: ' + response.message);
                }
            },
            error: function() {
                alert('Fehler beim Freigeben des Benutzers.');
            }
        });
    });
});
