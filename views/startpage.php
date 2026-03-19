<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/views/header.php";
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="card-title mb-1">
                        Willkommen, <?= htmlspecialchars($_SESSION['firstName']) ?>!
                    </h4>
                    <p class="text-muted mb-0">
                        <i class="bi bi-calendar-check me-1"></i>
                        <?= date('l, d. F Y') ?>
                    </p>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history me-2"></i>Nächste Termine
                    </h5>
                    <a href="/views/appointmentManagement.php" class="btn btn-sm btn-outline-primary">
                        Alle
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($upcomingAppointments)): ?>
                        <div class="p-4 text-center text-muted">
                            <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                            Keine bevorstehenden Termine
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($upcomingAppointments as $appointment): ?>
                                <?php
                                $startDate = new DateTime($appointment['start']);
                                $endDate = new DateTime($appointment['end']);
                                $isToday = $startDate->format('Y-m-d') === date('Y-m-d');
                                $isTomorrow = $startDate->format('Y-m-d') === date('Y-m-d', strtotime('+1 day'));
                                ?>
                                <div class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 fw-bold">
                                                <?= htmlspecialchars($appointment['title']) ?>
                                            </h6>
                                            <p class="mb-1 small text-muted">
                                                <i class="bi bi-briefcase me-1"></i>
                                                <?= htmlspecialchars($appointment['jobName']) ?>
                                            </p>
                                            <p class="mb-0 small">
                                                <i class="bi bi-clock me-1"></i>
                                                <?= $startDate->format('H:i') ?> - <?= $endDate->format('H:i') ?>
                                            </p>
                                        </div>
                                        <div class="text-end ms-3">
                                            <?php if ($isToday): ?>
                                                <span class="badge bg-danger">Heute</span>
                                            <?php elseif ($isTomorrow): ?>
                                                <span class="badge bg-warning">Morgen</span>
                                            <?php else: ?>
                                                <small class="text-muted">
                                                    <?= $startDate->format('d.m.Y') ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-chat-left-text me-2"></i>Neue Beiträge aus Ihren Berufsbereichen
                    </h5>
                    <a href="/views/forum_start.php" class="btn btn-sm btn-outline-primary">
                        Zum Forum
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentPosts)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-chat-square-dots fs-1 d-block mb-3"></i>
                            <p>Noch keine Beiträge in Ihren Berufsbereichen</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentPosts as $jobPosts): ?>
                            <div class="mb-4">
                                <h6 class="text-secondary fw-bold border-bottom pb-2 mb-3">
                                    <?= htmlspecialchars($jobPosts['jobName']) ?>
                                </h6>

                                <?php foreach ($jobPosts['posts'] as $post): ?>
                                    <?php
                                    $postDate = new DateTime($post['createdAt']);
                                    $contentPreview = strip_tags($post['content']);
                                    $contentPreview = mb_substr($contentPreview, 0, 150) . '...';
                                    ?>
                                    <div class="card mb-3 border-start  border-3">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="card-subtitle text-muted mb-0">
                                                    <i class="bi bi-chat-left-dots me-1"></i>
                                                    <?= htmlspecialchars($post['topicName']) ?>
                                                </h6>
                                                <small class="text-muted">
                                                    <?= $postDate->format('d.m.Y H:i') ?>
                                                </small>
                                            </div>
                                            
                                            <p class="card-text mb-2">
                                                <?= htmlspecialchars($contentPreview) ?>
                                            </p>
                                            
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    <i class="bi bi-person-circle me-1"></i>
                                                    <?= htmlspecialchars($post['userName']) ?>
                                                </small>
                                                <a href="/views/forum.php?jobId=<?= $jobPosts['jobId'] ?>&topicId=<?= $post['topicId'] ?>#post-<?= $post['postId'] ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    Weiterlesen
                                                    <i class="bi bi-arrow-right ms-1"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'] . "/views/footer.php"; ?>