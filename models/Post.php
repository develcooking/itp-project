<?php

class Post
{

    private $conn;
    private $table = 'Posts';

    public $postId;
    public $topicId;
    public $userId;
    public $content;
    public $description;
    public $reaction_negative;
    public $reaction_positive;
    public $createdBy;
    public $modifiedBy;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll()
    {
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $posts[] = [
                    'postId' => $row['postId'],
                    'topicId' => $row['topicId'],
                    'userId' => $row['userId'],
                    'content' => $row['content'],
                    'description' => $row['description'],
                    'reaction_negative' => $row['reaction_negative'],
                    'reaction_positive' => $row['reaction_positive'],
                    'createdAt' => $row['createdAt'],
                    'modifiedAt' => $row['modifiedAt'],
                    'createdBy' => $row['createdBy'],
                    'modifiedBy' => $row['modifiedBy']
                ];
            }
        }

        $stmt->close();
        return $posts ?? [];
    }

    public function post()
    {
        $query = " INSERT INTO " . $this->table . "
        (postId, topicId, userId, content, description, reaction_negative, reaction_positive,  createdBy, modifiedBy) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "iiissiiii",
            $this->postId,
            $this->topicId,
            $this->userId,
            $this->content,
            $this->description,
            $this->reaction_negative,
            $this->reaction_positive,
            $this->createdBy,
            $this->modifiedBy
        );

        if ($stmt->execute()) {
            $this->postId = $stmt->insert_id;
            $stmt->close();

            return true;
        }

        $stmt->close();
        return false;
    }

    public function getById($postId)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE postId = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $postId);
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

    public function update($postId)
    {
        $query = " UPDATE " . $this->table . " 
        SET topicId = ?, userId = ?, content = ?, description = ?, reaction_negative = ?, reaction_positive  WHERE postId = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "iissii",
            $this->topicId,
            $this->userId,
            $this->content,
            $this->description,
            $this->reaction_negative,
            $this->reaction_positive,
        );

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }

        $stmt->close();
        return false;
    }

    public function delete($postId)
    {
        $query = "DELETE FROM " . $this->table . " WHERE postId = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->postId);
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }

        $stmt->close();
        return false;
    }


}