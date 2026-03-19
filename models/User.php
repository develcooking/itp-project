<?php

class User
{
    private $conn;
    private ?string $table = 'Users';
    private ?int $userId = null;
    private ?string $userName = '';
    private ?string $firstName = '';
    private ?string $lastName = '';
    private ?string $email = '';
    private ?string $password = '';
    private ?string $role = '';
    private ?string $securityAnswer = '';
    private ?int $activated = null;
    private ?string $schoolCompany = null;
    private bool $sendNotification = true;
    private ?string $profileImage = null;
    private ?string $profileImageMime = null;
    private bool $hasProfileImage = false;
    private bool $profileImageDirty = false;
    private ?bool $profileImageColumnsAvailable = null;
    private ?int $createdBy = null;
    private ?int $modifiedBy = null;
    private bool $isBlocked = false;
    private ?string $blockedUntil = null;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function getSecurityAnswer(): ?string
    {
        return $this->securityAnswer;
    }

    public function getActivated(): ?int
    {
        return $this->activated;
    }

    public function getSchoolCompany(): ?string
    {
        return $this->schoolCompany;
    }

    public function getSendNotification(): bool
    {
        return $this->sendNotification;
    }

    public function getProfileImage(): ?string
    {
        return $this->profileImage;
    }

    public function getProfileImageMime(): ?string
    {
        return $this->profileImageMime;
    }

    public function hasProfileImage(): bool
    {
        return $this->hasProfileImage;
    }

    public function canStoreProfileImage(): bool
    {
        return $this->hasProfileImageColumns();
    }

    public function getCreatedBy(): ?int
    {
        return $this->createdBy;
    }

    public function getModifiedBy(): ?int
    {
        return $this->modifiedBy;
    }

    public function getIsBlocked(): bool
    {
        return $this->isBlocked;
    }

    public function getBlockedUntil(): ?string
    {
        return $this->blockedUntil;
    }

    public function setUserName(string $userName): self
    {
        $this->userName = $userName;
        return $this;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function setSecurityAnswer(string $securityAnswer): self
    {
        $this->securityAnswer = $securityAnswer;
        return $this;
    }

    public function setActivated(int $activated): self
    {
        $this->activated = $activated;
        return $this;
    }

    public function setSchoolCompany(?string $schoolCompany): self
    {
        $this->schoolCompany = $schoolCompany;
        return $this;
    }

    public function setSendNotification(bool $sendNotification): self
    {
        $this->sendNotification = $sendNotification;
        return $this;
    }

    public function setProfileImage(?string $profileImage, ?string $profileImageMime): self
    {
        $this->profileImage = $profileImage;
        $this->profileImageMime = $profileImageMime;
        $this->hasProfileImage = !empty($profileImage);
        $this->profileImageDirty = true;
        return $this;
    }

    public function setCreatedBy(?int $createdBy): self
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    public function setModifiedBy(?int $modifiedBy): self
    {
        $this->modifiedBy = $modifiedBy;
        return $this;
    }

    public function setIsBlocked(bool $isBlocked): self
    {
        $this->isBlocked = $isBlocked;
        return $this;
    }

    public function setBlockedUntil(?string $blockedUntil): self
    {
        $this->blockedUntil = $blockedUntil;
        return $this;
    }

    public function getAll(): array
    {
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();

        $users = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $users[] = [
                    'userId' => $row['userId'],
                    'userName' => $row['userName'],
                    'firstName' => $row['firstName'],
                    'lastName' => $row['lastName'],
                    'email' => $row['email'],
                    'password' => $row['password'],
                    'role' => $row['role'],
                    'securityAnswer' => $row['securityAnswer'],
                    'activated' => $row['activated'],
                    'school_company' => $row['school_company'],
                    'sendNotification' => (int)($row['sendNotification'] ?? 1),
                    'createdAt' => $row['createdAt'],
                    'modifiedAt' => $row['modifiedAt'],
                    'createdBy' => $row['createdBy'],
                    'modifiedBy' => $row['modifiedBy'],
                    'isBlocked' => (bool)($row['isBlocked'] ?? false),
                    'blockedUntil' => $row['blockedUntil'] ?? null
                ];
            }
        }

        $stmt->close();
        return $users;
    }

    public function post(): bool
    {
        $query = "INSERT INTO " . $this->table . "
        (userName, firstName, lastName, email, password, role, securityAnswer, school_company, activated, createdBy, modifiedBy) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);

        $password = password_hash($this->password, PASSWORD_DEFAULT);
        $createdBy = $this->createdBy ?? null;
        $modifiedBy = $this->modifiedBy ?? null;

        $stmt->bind_param(
            "ssssssssiii",
            $this->userName,
            $this->firstName,
            $this->lastName,
            $this->email,
            $password,
            $this->role,
            $this->securityAnswer,
            $this->schoolCompany,
            $this->activated,
            $createdBy,
            $modifiedBy
        );

        if ($stmt->execute()) {
            $this->userId = $stmt->insert_id;
            $stmt->close();

            if ($this->createdBy === 0) {
                $this->createdBy = $this->userId;
                $this->modifiedBy = $this->userId;
                $this->updateCreatedBy();
            }


            return true;
        }

        $stmt->close();
        return false;
    }

    public function getById($userId)
    {
        $query = $this->hasProfileImageColumns()
            ? "SELECT userId, userName, firstName, lastName, email, password, role, securityAnswer, activated, school_company, sendNotification, profileImageMime, (profileImage IS NOT NULL AND OCTET_LENGTH(profileImage) > 0 AND profileImageMime IS NOT NULL) AS hasProfileImage, createdBy, modifiedBy, isBlocked, blockedUntil FROM " . $this->table . " WHERE userid = ?"
            : "SELECT userId, userName, firstName, lastName, email, password, role, securityAnswer, activated, school_company, sendNotification, createdBy, modifiedBy, isBlocked, blockedUntil FROM " . $this->table . " WHERE userid = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $this->mapData($row);
            $stmt->close();
            return true;
        }

        $stmt->close();
        return false;
    }
    public function getUserNameByID($id): ?string {
        $query = "SELECT userName FROM " . $this->table . " WHERE userid = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $userName =  $row["userName"];
            $stmt->close();
            return $userName;
        }

        $stmt->close();
        return null;
    }

    public function updatePasswordByID(): bool
    {
        $query = "UPDATE " . $this->table . " SET password = ? WHERE userid = ?";
        $stmt = $this->conn->prepare($query);

        $password = password_hash($this->password, PASSWORD_DEFAULT);
        $stmt->bind_param("si",$password, $this->userId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
    public function updateSecureityAnswereByID(): bool
    {
        $query = "UPDATE " . $this->table . " SET securityAnswer = ? WHERE userid = ?";
        $stmt = $this->conn->prepare($query);

        $securityAnswer = password_hash($this->securityAnswer, PASSWORD_DEFAULT);
        $stmt->bind_param("si", $securityAnswer, $this->userId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function update(int $userId): bool
    {
        $query = "UPDATE " . $this->table . "
              SET userName = ?, firstName = ?, lastName = ?, email = ?, role = ?, school_company = ?, activated = ?
              WHERE userId = ?";

        $stmt = $this->conn->prepare($query);

        $stmt->bind_param(
            "ssssssii",
            $this->userName,
            $this->firstName,
            $this->lastName,
            $this->email,
            $this->role,
            $this->schoolCompany,
            $this->activated,
            $userId
        );

        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function delete($userId)
    {
        $query = "DELETE FROM " . $this->table . " WHERE userid = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }

        $stmt->close();
        return false;
    }

    private function mapData($row)
    {
        $this->userId = $row['userId'];
        $this->userName = $row['userName'];
        $this->firstName = $row['firstName'];
        $this->lastName = $row['lastName'];
        $this->email = $row['email'];
        $this->password = $row['password'];
        $this->role = $row['role'];
        $this->securityAnswer = $row['securityAnswer'];
        $this->activated = $row['activated'];
        $this->schoolCompany = $row['school_company'] ?? null;
        $this->sendNotification = ((int)($row['sendNotification'] ?? 1)) === 1;
        $this->profileImageMime = $row['profileImageMime'] ?? null;
        $this->hasProfileImage = isset($row['hasProfileImage'])
            ? ((int)$row['hasProfileImage']) === 1
            : !empty($row['profileImage']);
        $this->profileImage = $row['profileImage'] ?? null;
        $this->isBlocked = (bool)($row['isBlocked'] ?? false);
        $this->blockedUntil = $row['blockedUntil'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'userId' => $this->getUserId(),
            'userName' => $this->getUserName(),
            'firstName' => $this->getFirstName(),
            'lastName' => $this->getLastName(),
            'email' => $this->getEmail(),
            'role' => $this->getRole(),
            'activated' => $this->getActivated(),
            'school_company' => $this->getSchoolCompany(),
            'sendNotification' => $this->getSendNotification() ? 1 : 0
        ];
    }

    public function getByEmail($email)
    {
        $query = $this->hasProfileImageColumns()
            ? "SELECT userId, userName, firstName, lastName, email, password, role, securityAnswer, activated, school_company, sendNotification, profileImageMime, (profileImage IS NOT NULL AND OCTET_LENGTH(profileImage) > 0 AND profileImageMime IS NOT NULL) AS hasProfileImage, createdBy, modifiedBy, isBlocked, blockedUntil FROM " . $this->table . " WHERE email = ?"
            : "SELECT userId, userName, firstName, lastName, email, password, role, securityAnswer, activated, school_company, sendNotification, createdBy, modifiedBy, isBlocked, blockedUntil FROM " . $this->table . " WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $this->mapData($row);
            $stmt->close();
            return true;
        }

        $stmt->close();
        return false;
    }

    public function userNameExists(string $userName): bool
    {
        $query = "SELECT COUNT(userName) AS username_count FROM " . $this->table . " WHERE userName = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $userName);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row['username_count'] > 0;
    }

    public function emailExists(string $email): bool
    {
    $query = "SELECT COUNT(email) AS email_count FROM " . $this->table . " WHERE email = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['email_count'] > 0;
    }

    public function updateProfile(): bool
    {
        $sendNotification = $this->sendNotification ? 1 : 0;

        if ($this->profileImageDirty && $this->hasProfileImageColumns()) {
            if ($this->profileImage === null || $this->profileImageMime === null) {
                $query = "UPDATE " . $this->table . " SET userName = ?, firstName = ?, lastName = ?, email = ?, school_company = ?, sendNotification = ?, profileImage = NULL, profileImageMime = NULL WHERE userId = ?";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("sssssii", $this->userName, $this->firstName, $this->lastName, $this->email, $this->schoolCompany, $sendNotification, $this->userId);
            } else {
                $query = "UPDATE " . $this->table . " SET userName = ?, firstName = ?, lastName = ?, email = ?, school_company = ?, sendNotification = ?, profileImage = ?, profileImageMime = ? WHERE userId = ?";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("sssssibsi", $this->userName, $this->firstName, $this->lastName, $this->email, $this->schoolCompany, $sendNotification, $this->profileImage, $this->profileImageMime, $this->userId);
                $stmt->send_long_data(6, $this->profileImage);
            }
        } else {
            $query = "UPDATE " . $this->table . " SET userName = ?, firstName = ?, lastName = ?, email = ?, school_company = ?, sendNotification = ? WHERE userId = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("sssssii", $this->userName, $this->firstName, $this->lastName, $this->email, $this->schoolCompany, $sendNotification, $this->userId);
        }

        $ok = $stmt->execute();
        $stmt->close();
        $this->profileImageDirty = false;
        return $ok;
    }

    public function getProfileImagePayloadById(int $userId): ?array
    {
        if (!$this->hasProfileImageColumns()) {
            return null;
        }

        $query = "SELECT profileImage, profileImageMime FROM " . $this->table . " WHERE userId = ? AND profileImage IS NOT NULL AND OCTET_LENGTH(profileImage) > 0 AND profileImageMime IS NOT NULL LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            return null;
        }

        $row = $result->fetch_assoc();
        $stmt->close();

        if (empty($row['profileImage']) || empty($row['profileImageMime'])) {
            return null;
        }

        return [
            'data' => $row['profileImage'],
            'mime' => $row['profileImageMime']
        ];
    }

    public function getProfileStats(): array
    {
        $query = "SELECT
                    (SELECT COUNT(*) FROM Topics t WHERE t.userId = ?) AS topicCount,
                    (SELECT COUNT(*) FROM Posts p WHERE p.userId = ?) AS postCount,
                    (
                        SELECT COUNT(*)
                        FROM user_reactions ur
                        INNER JOIN Posts p ON p.postId = ur.postId
                        WHERE p.userId = ?
                          AND ur.voteType = 'up'
                    ) AS reactionPositiveCount,
                    (
                        SELECT COUNT(*)
                        FROM user_reactions ur
                        INNER JOIN Posts p ON p.postId = ur.postId
                        WHERE p.userId = ?
                          AND ur.voteType = 'down'
                    ) AS reactionNegativeCount";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iiii", $this->userId, $this->userId, $this->userId, $this->userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc() ?: [];
        $stmt->close();

        return [
            'topicCount' => (int)($row['topicCount'] ?? 0),
            'postCount' => (int)($row['postCount'] ?? 0),
            'reactionPositiveCount' => (int)($row['reactionPositiveCount'] ?? 0),
            'reactionNegativeCount' => (int)($row['reactionNegativeCount'] ?? 0)
        ];
    }

    public function blockUser(int $userId, bool $permanent, ?string $blockedUntil): bool
    {
        if ($permanent) {
            $query = "UPDATE " . $this->table . " SET isBlocked = 1, blockedUntil = NULL WHERE userId = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $userId);
        } else {
            $query = "UPDATE " . $this->table . " SET isBlocked = 0, blockedUntil = ? WHERE userId = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("si", $blockedUntil, $userId);
        }
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function unblockUser(int $userId): bool
    {
        $query = "UPDATE " . $this->table . " SET isBlocked = 0, blockedUntil = NULL WHERE userId = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function clearExpiredBlock(int $userId): bool
    {
        $query = "UPDATE " . $this->table . " SET blockedUntil = NULL WHERE userId = ? AND blockedUntil IS NOT NULL AND blockedUntil <= NOW()";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function updateRole(int $userId, string $role): bool
    {
        $allowedRoles = ['Ausbilder', 'Lehrer', 'Admin'];
        if (!in_array($role, $allowedRoles)) {
            return false;
        }

        $query = "UPDATE " . $this->table . " SET role = ? WHERE userId = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("si", $role, $userId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    private function updateCreatedBy()
    {
        $query = "UPDATE " . $this->table . " SET createdBy = ?, modifiedBy = ? WHERE userid = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iii", $this->userId, $this->userId, $this->userId);
        $stmt->execute();
        $stmt->close();

        $this->createdBy = $this->userId;
        $this->modifiedBy = $this->userId;
    }

    private function hasProfileImageColumns(): bool
    {
        if ($this->profileImageColumnsAvailable !== null) {
            return $this->profileImageColumnsAvailable;
        }

        $query = "SHOW COLUMNS FROM " . $this->table . " LIKE 'profileImage'";
        $result = $this->conn->query($query);

        if (!$result || $result->num_rows === 0) {
            $this->profileImageColumnsAvailable = false;
            return false;
        }

        $mimeQuery = "SHOW COLUMNS FROM " . $this->table . " LIKE 'profileImageMime'";
        $mimeResult = $this->conn->query($mimeQuery);
        $this->profileImageColumnsAvailable = $mimeResult && $mimeResult->num_rows > 0;

        return $this->profileImageColumnsAvailable;
    }
}
