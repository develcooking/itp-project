<?php

class Post
{

    private $conn;
    private string $table = 'Posts';

    private int $postId;
    private int $topicId;
    private int $userId;
    private int $content;
    private int $description;
    private int $reaction_negative;
    private int $reaction_positive;
    private int $createdBy;
    private int $modifiedBy;

    public function __construct($db)
    {
        $this->conn = $db;
    }
    /* #### Set functions #### */
    public function setPostID($postId) :void {
        $this->postId = $postId;
    }
    public function setTopicID($topicId) :void {
        $this->topicId = $topicId;
    }
    public function setUserId($userId) :void {
        $this->userId = $userId;
    }
    public function setContent($content) :void {
        $this->content = $content;
    }
    public function setDescription($description) :void {
        $this->description = $description;
    }
    public function setReactionNegative($reactionNegative) :void {
        $this->reaction_negative = $reactionNegative;
    }
    public function setReactionPositive($reactionPositive) :void {
        $this->reaction_positive = $reactionPositive;
    }
    public function setCreatedBy($createdBy) :void {
        $this->createdBy = $createdBy;
    }
    public function setModifiedBy($modifiedBy) :void {
        $this->modifiedBy = $modifiedBy;
    }
    /* #### Get functions #### */
    public function getPostId():int {
        return $this->postId;
    }
    public function getTopicId():int {
        return $this->topicId;
    }
    public function getUserId():int {
        return $this->userId;
    }
    public function getContent():string {
        return $this->content;
    }
    public function getDescription():string {
        return $this->description;
    }
    public function getReactionNegative():int {
        return $this->reaction_negative;
    }
    public function getReactionPositive():int {
        return $this->reaction_positive;
    }
    public function getCreatedBy():int {
        return $this->createdBy;
    }
    public function getModifiedBy():int {
        return $this->modifiedBy;
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
        (topicId, userId, content, description, reaction_negative, reaction_positive, createdBy, modifiedBy) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "iissiiii",
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
        SET content = ?, description = ?, reaction_negative = ?, reaction_positive = ? WHERE postId = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "ssiii",
            $this->content,
            $this->description,
            $this->reaction_negative,
            $this->reaction_positive,
            $postId
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
        $stmt->bind_param("i", $postId);
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }

        $stmt->close();
        return false;
    }

    private function mapData($row)
    {
        $this->postId = $row['postId'];
        $this->topicId = $row['topicId'];
        $this->userId = $row['userId'];
        $this->content = $row['content'];
        $this->description = $row['description'];
        $this->reaction_negative = $row['reaction_negative'];
        $this->reaction_positive = $row['reaction_positive'];
        $this->createdBy = $row['createdBy'];
        $this->modifiedBy = $row['modifiedBy'];
    }


}