<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/controllers/adminPage.php';
include './header.php';
?>
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
                    <td>
                        <select class="role-dropdown" data-user-id="<?= $user['userId'] ?>">
                            <option value="Lehrer" <?= ($user['role'] ?? '') === 'Lehrer' ? 'selected' : '' ?>>Lehrkraft</option>
                            <option value="Ausbilder" <?= ($user['role'] ?? '') === 'Ausbilder' ? 'selected' : '' ?>>Ausbilder</option>
                            <option value="Admin" <?= ($user['role'] ?? '') === 'Admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </td>
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
                <th class="w-auto">Aktionen</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($activatedUsers as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['userName'] ?? '') ?></td>
                    <td><?= htmlspecialchars($user['firstName'] ?? '') ?></td>
                    <td><?= htmlspecialchars($user['lastName'] ?? '') ?></td>
                    <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                    <td>
                        <select class="role-dropdown" data-user-id="<?= $user['userId'] ?>">
                            <option value="Lehrer" <?= ($user['role'] ?? '') === 'Lehrer' ? 'selected' : '' ?>>Lehrkraft</option>
                            <option value="Ausbilder" <?= ($user['role'] ?? '') === 'Ausbilder' ? 'selected' : '' ?>>Ausbilder</option>
                            <option value="Admin" <?= ($user['role'] ?? '') === 'Admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </td>
                    <td><?= $user['createdAt'] ? date('d.m.Y H:i', strtotime($user['createdAt'])) : '' ?></td>
                    <td class="text-nowrap" style="width:1%">
                        <button class="btn btn-primary btn-sm me-1 jobs-btn" data-user-id="<?= $user['userId'] ?>" data-username="<?= htmlspecialchars($user['userName'] ?? '') ?>">
                            Berufsbereiche
                        </button>
                        <button class="btn btn-danger btn-sm delete-user-btn" data-user-id="<?= $user['userId'] ?>" data-username="<?= htmlspecialchars($user['userName'] ?? '') ?>">
                            Löschen
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

<?php include $_SERVER['DOCUMENT_ROOT'] . "/views/footer.php"; ?>

<script src="/resources/js/adminPage.js"></script>