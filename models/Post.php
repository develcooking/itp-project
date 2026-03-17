<?php

class Post
{

    private $conn;
    private string $table = 'Posts';

    private int $postId;
    private int $topicId;
    private int $userId;
    private string $content;
    private string $description;
    private int $reaction_negative;
    private int $reaction_positive;
    private int $createdBy;
    private int $modifiedBy;
    private string $userName = '';

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
    public function setUserName(string $userName): void {
        $this->userName = $userName;
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
    public function getUserName():string {
        return $this->userName;
    }

        public function getByTopicId($topicId, $userId)   
         {
            $query = "
            SELECT 
                p.*,
                u.userName,

                SUM(CASE WHEN ur.voteType = 'up' THEN 1 ELSE 0 END) AS reaction_positive,
                SUM(CASE WHEN ur.voteType = 'down' THEN 1 ELSE 0 END) AS reaction_negative,

                MAX(CASE WHEN ur.userId = ? THEN ur.voteType ELSE NULL END) AS voteType

            FROM Posts p
            JOIN Users u ON p.userId = u.userId
            LEFT JOIN user_reactions ur ON p.postId = ur.postId

            WHERE p.topicId = ?

            GROUP BY p.postId
            ORDER BY p.createdAt ASC
            ";        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $userId, $topicId);
        $stmt->execute();
        $result = $stmt->get_result();
        $posts = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $posts[] = [
                    'voteType' => $row['voteType'] ?? 'noreaction',
                    'postId' => $row['postId'],
                    'topicId' => $row['topicId'],
                    'userId' => $row['userId'],
                    'userName' => $row['userName'],
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
        return $posts;
    }

    public function getAll()
    {
        $query = "SELECT p.*, u.userName FROM " . $this->table . " p JOIN Users u ON p.userId = u.userId";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $posts[] = [
                    'postId' => $row['postId'],
                    'topicId' => $row['topicId'],
                    'userId' => $row['userId'],
                    'userName' => $row['userName'],
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
        $query = "SELECT p.*, u.userName FROM " . $this->table . " p JOIN Users u ON p.userId = u.userId WHERE p.postId = ?";
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
        $this->userName = $row['userName'] ?? '';
    }

public function vote($postId, $userId, $type)
{
    if (!in_array($type, ['up','down'])) return false;

    // Check current vote
    $query = "SELECT voteType FROM user_reactions WHERE userId = ? AND postId = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("ii", $userId, $postId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $currentVote = $row['voteType'];

        if ($currentVote === $type) {
            // Same vote → switch to noreaction
            $update = "UPDATE user_reactions SET voteType = 'noreaction' WHERE userId = ? AND postId = ?";
            $stmt = $this->conn->prepare($update);
            $stmt->bind_param("ii", $userId, $postId);
            $stmt->execute();
            $stmt->close();
            return true;
        }

        // Different vote → switch vote
        $update = "UPDATE user_reactions SET voteType = ? WHERE userId = ? AND postId = ?";
        $stmt = $this->conn->prepare($update);
        $stmt->bind_param("sii", $type, $userId, $postId);
        $stmt->execute();
        $stmt->close();

        return true;
    }

    $stmt->close();

    // No previous vote → insert new row
    $insert = "INSERT INTO user_reactions (userId, postId, voteType) VALUES (?, ?, ?)";
    $stmt = $this->conn->prepare($insert);
    $stmt->bind_param("iis", $userId, $postId, $type);
    $stmt->execute();
    $stmt->close();

    return true;
}
}