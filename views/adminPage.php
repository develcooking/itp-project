<?php

if (!isset($users)) {
    $users = [];
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
    <?php include '../includes/header.php'; ?>

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
                    <th>Aktionen</th>
                </tr>
            </thead>
        </table>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>