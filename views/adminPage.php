<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/database/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/User.php';

$users = [];

if ($conn) {
    $userModel = new User($conn);
    $users = $userModel->getAll();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="../resources/css/style.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="../resources/css/datatables.min.css">
    <!-- DataTables JS -->
    <script src="../resources/js/datatables.min.js"></script>
</head>
<body>
    <?php include './header.php'; ?>

    <main class="container">
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
                    <td><?= $user['activated'] ? 'Ja' : 'Nein' ?></td>
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
        });
    </script>
</body>
</html>