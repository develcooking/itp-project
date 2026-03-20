<?php

class Comment
{
    private $conn;
    private string $table = 'Comments';

    private int $commentId;
    private int $postId;
    private int $userId;
    private string $content;
    private string $userName = '';
    private string $school_company = '';
    private string $createdAt = '';
    private string $modifiedAt = '';

    private ?bool $profileImageColumnsAvailable = null;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    private function hasProfileImageColumns(): bool
    {
        if ($this->profileImageColumnsAvailable !== null) {
            return $this->profileImageColumnsAvailable;
        }

        $query = "SHOW COLUMNS FROM Users LIKE 'profileImage'";
        $result = $this->conn->query($query);
        $this->profileImageColumnsAvailable = $result && $result->num_rows > 0;

        return $this->profileImageColumnsAvailable;
    }

    /* #### Set functions #### */
    public function setCommentId($commentId): void
    {
        $this->commentId = $commentId;
    }

    public function setPostId($postId): void
    {
        $this->postId = $postId;
    }

    public function setUserId($userId): void
    {
        $this->userId = $userId;
    }

    public function setContent($content): void
    {
        $this->content = $content;
    }

    /* #### Get functions #### */
    public function getCommentId(): int
    {
        return $this->commentId;
    }

    public function getPostId(): int
    {
        return $this->postId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getUserName(): string
    {
        return $this->userName;
    }

    public function getSchoolCompany(): string
    {
        return $this->school_company;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getModifiedAt(): string
    {
        return $this->modifiedAt;
    }

    /**
     * Get all comments for a post
     */
    public function getByPostId($postId, ?string $search = null): array
    {
        $selectProfileImageState = $this->hasProfileImageColumns()
            ? ", (u.profileImage IS NOT NULL) AS hasProfileImage"
            : ", 0 AS hasProfileImage";

        $query = "
            SELECT 
                c.*,
                u.userName,
                u.school_company
                {$selectProfileImageState}
            FROM " . $this->table . " c
            JOIN Users u ON c.userId = u.userId
            WHERE c.postId = ?
        ";

        if (!empty($search)) {
            $query .= " AND c.content LIKE ? ";
        }

        $query .= " ORDER BY c.createdAt ASC";

        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $searchTerm = "%" . $search . "%";
            $stmt->bind_param("is", $postId, $searchTerm);
        } else {
            $stmt->bind_param("i", $postId);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $comments = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $comments[] = [
                    'commentId' => $row['commentId'],
                    'postId' => $row['postId'],
                    'userId' => $row['userId'],
                    'userName' => $row['userName'],
                    'school_company' => $row['school_company'] ?? '',
                    'hasProfileImage' => ((int)($row['hasProfileImage'] ?? 0)) === 1,
                    'content' => $row['content'],
                    'createdAt' => $row['createdAt'],
                    'modifiedAt' => $row['modifiedAt']
                ];
            }
        }

        $stmt->close();
        return $comments;
    }

    /**
     * Create a new comment
     */
    public function create(): bool
    {
        $query = "INSERT INTO " . $this->table . " (postId, userId, content, createdBy, modifiedBy) 
                  VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iisii", $this->postId, $this->userId, $this->content, $this->userId, $this->userId);

        if ($stmt->execute()) {
            $this->commentId = $stmt->insert_id;
            $stmt->close();
            return true;
        }

        $stmt->close();
        return false;
    }

    /**
     * Delete a comment
     */
    public function delete($commentId): bool
    {
        $query = "DELETE FROM " . $this->table . " WHERE commentId = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $commentId);

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }

        $stmt->close();
        return false;
    }

    /**
     * Update comment content
     */
    public function update($commentId, $content): bool
    {
        $query = "UPDATE " . $this->table . " SET content = ?, modifiedBy = ? WHERE commentId = ?";

        $stmt = $this->conn->prepare($query);
        $userId = $this->userId;
        $stmt->bind_param("sii", $content, $userId, $commentId);

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }

        $stmt->close();
        return false;
    }

    /**
     * Get comment by ID
     */
    public function getById($commentId): bool
    {
        $query = "SELECT c.*, u.userName, u.school_company FROM " . $this->table . " c JOIN Users u ON c.userId = u.userId WHERE c.commentId = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $commentId);
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

    private function mapData($row): void
    {
        $this->commentId = $row['commentId'];
        $this->postId = $row['postId'];
        $this->userId = $row['userId'];
        $this->content = $row['content'];
        $this->userName = $row['userName'] ?? '';
        $this->school_company = $row['school_company'] ?? '';
        $this->createdAt = $row['createdAt'] ?? '';
        $this->modifiedAt = $row['modifiedAt'] ?? '';
    }
}
