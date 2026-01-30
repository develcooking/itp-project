<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/controllers/admin.php';
?>

<?php include './header.php'; ?>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="../resources/css/datatables.min.css">
    <!-- DataTables JS -->
    <script src="../resources/js/datatables.min.js"></script>

    <h1>Admin Panel - Benutzerverwaltung</h1>

    <table id="usersTable" class="display" style="width:100%">
        <thead>
        <tr>
            <th>Benutzername</th>
            <th>Vorname</th>
            <th>Nachname</th>
            <th>Email</th>
            <th>Rolle</th>
            <th>Wann</th>
            <th>Aktiviert</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['userName'] ?? '') ?></td>
                <td><?= htmlspecialchars($user['firstName'] ?? '') ?></td>
                <td><?= htmlspecialchars($user['lastName'] ?? '') ?></td>
                <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                <td><?= htmlspecialchars($user['role'] ?? '') ?></td>
                <td><?= htmlspecialchars($user['createdAt'] ?? '') ?></td>
                <td>
                    <button class="toggle-btn" data-user-id="<?= $user['userId'] ?>" data-activated="<?= $user['activated'] ?>">
                        <?= $user['activated'] ? 'Ja' : 'Nein' ?>
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php include './footer.php'; ?>

<script>
    $(document).ready(function() {
        $('#usersTable').DataTable({
            order: [[5, 'desc']],
            language: {
                url: 'https://cdn.datatables.net/plug-ins/2.0.0/i18n/de-DE.json'
            }
        });

        // Toggle aktiviert Status
        $('.toggle-btn').click(function() {
            const userId = $(this).data('user-id');
            const currentStatus = parseInt($(this).data('activated'));
            const newStatus = currentStatus === 1 ? 0 : 1;
            const button = $(this);

            $.ajax({
                url: '/api/users.php',
                type: 'POST',
                dataType: 'text', // receive raw text so we can inspect HTML/errors
                data: {
                    action: 'toggleActivated',
                    userId: userId,
                    activated: newStatus
                },
                success: function(rawResponse) {
                    // try to parse JSON, otherwise show raw response for debugging
                    try {
                        const response = JSON.parse(rawResponse);
                        if (response.success) {
                            button.text(newStatus === 1 ? 'Ja' : 'Nein');
                            button.data('activated', newStatus);
                        } else {
                            alert('Fehler: ' + response.message);
                        }
                    } catch (err) {
                        console.error('Failed to parse JSON. Raw response below:\n', rawResponse);
                        alert('Fehler beim Aktualisieren des Status: ungültige API-Antwort. Siehe Konsole (Network → Response)');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);
                    alert('Fehler beim Aktualisieren des Status: ' + error + '\nSiehe Konsole für die rohe Antwort.');
                }
            });
        });
    });
</script>
<?php include './footer.php'; ?>