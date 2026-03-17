<?php

class TopicPostNotificationService
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function notifyTopicOwnerAboutNewPost(int $topicId, int $postId, int $actorUserId): void
    {
        $topicData = $this->getTopicOwnerData($topicId);
        if ($topicData === null) {
            return;
        }

        $recipientUserId = (int)$topicData['ownerUserId'];
        $recipientEmail = (string)($topicData['email'] ?? '');
        $recipientRole = (string)($topicData['role'] ?? '');
        $sendNotification = ((int)($topicData['sendNotification'] ?? 1)) === 1;
        $topicName = (string)($topicData['topicName'] ?? 'Unbenanntes Thema');

        if ($recipientUserId === $actorUserId) {
            return;
        }

        if (!$this->isNotifiableRole($recipientRole)) {
            return;
        }

        if (!$sendNotification) {
            return;
        }

        if ($recipientEmail === '') {
            $this->logNotification($topicId, $postId, $recipientUserId, '', 'failed', 'Empfaenger hat keine E-Mail-Adresse.');
            return;
        }

        $mailSent = false;
        $errorMessage = null;

        try {
            if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
                throw new RuntimeException('PHPMailer ist nicht installiert. Fuehre "composer install" aus.');
            }

            $mailerClassName = 'PHPMailer\\PHPMailer\\PHPMailer';
            $mail = new $mailerClassName(true);
            $this->configureMailer($mail);

            $mail->setFrom(
                $_ENV['MAIL_FROM_ADDRESS'] ?? 'Ausbildungsportal@worldofmail.com',
                $_ENV['MAIL_FROM_NAME'] ?? 'Ausbildungsportal'
            );
            $mail->addAddress($recipientEmail);
            $mail->Subject = 'Neuer Beitrag in Ihrem Thema: ' . $topicName;
            $mail->isHTML(true);

            $topicUrl = $this->buildTopicUrl($topicId);
            $mail->Body = '<p>Hallo,</p>'
                . '<p>in Ihrem Thema <strong>' . htmlspecialchars($topicName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</strong> wurde ein neuer Beitrag erstellt.</p>'
                . '<p><a href="' . htmlspecialchars($topicUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">Zum Thema</a></p>'
                . '<p>Viele Gruesse<br>Ausbildungsportal</p>';
            $mail->AltBody = "Hallo,\n\nin Ihrem Thema '" . $topicName . "' wurde ein neuer Beitrag erstellt.\n"
                . "Zum Thema: " . $topicUrl . "\n\n"
                . 'Viele Gruesse\nAusbildungsportal';

            $mail->send();
            $mailSent = true;
        } catch (Throwable $e) {
            $errorMessage = $e->getMessage();
        }

        $this->logNotification(
            $topicId,
            $postId,
            $recipientUserId,
            $recipientEmail,
            $mailSent ? 'sent' : 'failed',
            $errorMessage
        );
    }

    private function configureMailer(object $mail): void
    {
        $mail->isSMTP();
        $mail->Host = $_ENV['MAIL_HOST'] ?? 'smtp.purelymail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['MAIL_USERNAME'] ?? 'Ausbildungsportal@worldofmail.com';
        $mail->Password = $_ENV['MAIL_PASSWORD'] ?? '';

        $encryption = strtolower((string)($_ENV['MAIL_ENCRYPTION'] ?? 'ssl'));
        if ($encryption === 'starttls' || $encryption === 'tls') {
            $mail->SMTPSecure = 'tls';
            $mail->Port = (int)($_ENV['MAIL_PORT'] ?? 587);
        } else {
            $mail->SMTPSecure = 'ssl';
            $mail->Port = (int)($_ENV['MAIL_PORT'] ?? 465);
        }

        $mail->CharSet = 'UTF-8';
    }

    private function getTopicOwnerData(int $topicId): ?array
    {
        $query = 'SELECT t.topicId, t.name AS topicName, t.userId AS ownerUserId, u.email, u.role, u.sendNotification
                  FROM Topics t
                  JOIN Users u ON u.userId = t.userId
                  WHERE t.topicId = ?
                  LIMIT 1';

        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $topicId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc() ?: null;
        $stmt->close();

        return $data;
    }

    private function isNotifiableRole(string $role): bool
    {
        $normalizedRole = strtolower(trim($role));
        $allowedRoles = ['1', '2', 'lehrer', 'lehrkraft', 'ausbilder'];

        return in_array($normalizedRole, $allowedRoles, true);
    }

    private function buildTopicUrl(int $topicId): string
    {
        $baseUrl = rtrim((string)($_ENV['APP_URL'] ?? ''), '/');
        if ($baseUrl === '' && isset($_SERVER['HTTP_HOST'])) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'];
        }

        return $baseUrl . '/views/forum.php?topicId=' . $topicId;
    }

    private function logNotification(
        int $topicId,
        int $postId,
        int $recipientUserId,
        string $recipientEmail,
        string $status,
        ?string $errorMessage
    ): void {
        $query = 'INSERT INTO TopicPostNotifications
                    (topicId, postId, recipientUserId, recipientEmail, status, errorMessage, createdBy, modifiedBy)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                  ON DUPLICATE KEY UPDATE
                    status = VALUES(status),
                    errorMessage = VALUES(errorMessage),
                    recipientEmail = VALUES(recipientEmail),
                    modifiedBy = VALUES(modifiedBy),
                    modifiedAt = CURRENT_TIMESTAMP';

        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            return;
        }

        $createdBy = $recipientUserId;
        $modifiedBy = $recipientUserId;
        $safeErrorMessage = $errorMessage === null ? null : mb_substr($errorMessage, 0, 1000);

        $stmt->bind_param(
            'iiisssii',
            $topicId,
            $postId,
            $recipientUserId,
            $recipientEmail,
            $status,
            $safeErrorMessage,
            $createdBy,
            $modifiedBy
        );
        $stmt->execute();
        $stmt->close();
    }
}
