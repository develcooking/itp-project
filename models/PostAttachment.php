<?php

class PostAttachment
{
    private $conn;
    private string $table = 'postAttachments';

    private int $attachmentId;
    private int $postId;
    private string $fileName;
    private string $fileType;
    private int $fileSize;
    private $fileData;
    private string $createdAt;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getByPostId(int $postId): array
    {
        $query = "SELECT attachmentId, fileName, fileType, fileSize, createdAt FROM " . $this->table . " WHERE postId = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $postId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $attachments = [];
        while ($row = $result->fetch_assoc()) {
            $attachments[] = $row;
        }
        $stmt->close();
        return $attachments;
    }

    public function getById(int $attachmentId): ?array
    {
        $query = "SELECT * FROM " . $this->table . " WHERE attachmentId = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $attachmentId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $attachment = $result->fetch_assoc();
        $stmt->close();
        return $attachment;
    }

    public function create(int $postId, string $fileName, string $fileType, int $fileSize, $fileData): bool
    {
        $query = "INSERT INTO " . $this->table . " (postId, fileName, fileType, fileSize, fileData) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("issis", $postId, $fileName, $fileType, $fileSize, $fileData);
        
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }
        
        $stmt->close();
        return false;
    }

    public function delete(int $attachmentId): bool
    {
        $query = "DELETE FROM " . $this->table . " WHERE attachmentId = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $attachmentId);
        
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }
        
        $stmt->close();
        return false;
    }
}
