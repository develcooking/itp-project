$(document).ready(function() {
    $('#jobsTable').DataTable({
        order: [[0, 'asc']],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/2.0.0/i18n/de-DE.json'
        },
        columnDefs: [
            { orderable: false, targets: 1 }
        ]
    });

    // Berufsbereich erstellen
    $('#createJobForm').on('submit', function(e) {
        e.preventDefault();
        const name = $('#newJobName').val().trim();
        if (!name) return;

        $.ajax({
            url: '/controllers/adminJobs.php',
            type: 'POST',
            dataType: 'json',
            data: { action: 'createJob', name: name },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Fehler: ' + (response.message || 'Unbekannter Fehler'));
                }
            },
            error: function(xhr) {
                var msg = 'Fehler beim Erstellen des Berufsbereichs.';
                try {
                    var res = JSON.parse(xhr.responseText);
                    if (res.message) msg = res.message;
                } catch(e) {}
                alert(msg);
            }
        });
    });

    // Bearbeiten-Modus aktivieren
    $(document).on('click', '.edit-btn', function() {
        const row = $(this).closest('tr');
        row.find('.job-name-display').addClass('d-none');
        row.find('.job-name-input').removeClass('d-none').focus();
        row.find('.edit-btn').addClass('d-none');
        row.find('.delete-btn').addClass('d-none');
        row.find('.save-btn').removeClass('d-none');
        row.find('.cancel-btn').removeClass('d-none');
    });

    // Bearbeiten abbrechen
    $(document).on('click', '.cancel-btn', function() {
        const row = $(this).closest('tr');
        const original = row.find('.job-name-display').text().trim();
        row.find('.job-name-input').val(original).addClass('d-none');
        row.find('.job-name-display').removeClass('d-none');
        row.find('.edit-btn').removeClass('d-none');
        row.find('.delete-btn').removeClass('d-none');
        row.find('.save-btn').addClass('d-none');
        row.find('.cancel-btn').addClass('d-none');
    });

    // Berufsbereich speichern
    $(document).on('click', '.save-btn', function() {
        const row = $(this).closest('tr');
        const jobId = row.data('jobId');
        const original = row.find('.job-name-display').text().trim();
        const newName = row.find('.job-name-input').val().trim();

        if (!newName) {
            alert('Der Name darf nicht leer sein.');
            return;
        }

        if (newName === original) {
            row.find('.cancel-btn').trigger('click');
            return;
        }

        $.ajax({
            url: '/controllers/adminJobs.php',
            type: 'POST',
            dataType: 'json',
            data: { action: 'updateJob', jobId: jobId, name: newName },
            success: function(response) {
                if (response.success) {
                    row.find('.job-name-display').text(newName).removeClass('d-none');
                    row.find('.job-name-input').addClass('d-none');
                    row.find('.edit-btn').removeClass('d-none');
                    row.find('.delete-btn').removeClass('d-none');
                    row.find('.save-btn').addClass('d-none');
                    row.find('.cancel-btn').addClass('d-none');
                } else {
                    alert('Fehler: ' + (response.message || 'Unbekannter Fehler'));
                }
            },
            error: function() {
                alert('Fehler beim Speichern.');
            }
        });
    });

    // Berufsbereich löschen
    $(document).on('click', '.delete-btn', function() {
        const row = $(this).closest('tr');
        const jobId = row.data('jobId');
        const name = row.find('.job-name-display').text().trim();

        if (!confirm('Berufsbereich "' + name + '" wirklich löschen?')) return;

        $.ajax({
            url: '/controllers/adminJobs.php',
            type: 'POST',
            dataType: 'json',
            data: { action: 'deleteJob', jobId: jobId },
            success: function(response) {
                if (response.success) {
                    $('#jobsTable').DataTable().row(row).remove().draw();
                } else {
                    alert('Fehler: ' + (response.message || 'Unbekannter Fehler'));
                }
            },
            error: function() {
                alert('Fehler beim Löschen.');
            }
        });
    });
});
