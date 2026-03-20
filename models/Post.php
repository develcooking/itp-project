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
    private int $edited = 0;
    private int $reaction_negative;
    private int $reaction_positive;
    private int $createdBy;
    private int $modifiedBy;
    private string $userName = '';
    private ?bool $profileImageColumnsAvailable = null;

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
            $selectProfileImageState = $this->hasProfileImageColumns()
                ? ", (u.profileImage IS NOT NULL) AS hasProfileImage"
                : ", 0 AS hasProfileImage";

            $query = "
            SELECT 
                p.*,
                u.userName,
                u.school_company
                {$selectProfileImageState},

                (SELECT COUNT(*) FROM user_reactions ur WHERE ur.postId = p.postId AND ur.voteType = 'up') AS reaction_positive,
                (SELECT COUNT(*) FROM user_reactions ur WHERE ur.postId = p.postId AND ur.voteType = 'down') AS reaction_negative,
                (SELECT ur.voteType FROM user_reactions ur WHERE ur.postId = p.postId AND ur.userId = ?) AS voteType,
                (SELECT COUNT(*) FROM Comments c WHERE c.postId = p.postId) AS comment_count

            FROM Posts p
            JOIN Users u ON p.userId = u.userId

            WHERE p.topicId = ?

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
                    'school_company' => $row['school_company'],
                    'hasProfileImage' => ((int)($row['hasProfileImage'] ?? 0)) === 1,
                    'content' => $row['content'],
                    'description' => $row['description'],
                    'edited' => (int)($row['edited'] ?? 0),
                    'reaction_negative' => $row['reaction_negative'],
                    'reaction_positive' => $row['reaction_positive'],
                    'comment_count' => (int)($row['comment_count'] ?? 0),
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
        SET content = ?, description = ?, reaction_negative = ?, reaction_positive = ?, edited = 1 WHERE postId = ?";

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
        $this->edited = (int)($row['edited'] ?? 0);
        $this->reaction_negative = $row['reaction_negative'];
        $this->reaction_positive = $row['reaction_positive'];
        $this->createdBy = $row['createdBy'];
        $this->modifiedBy = $row['modifiedBy'];
        $this->userName = $row['userName'] ?? '';
    }

public function getRecentForUserJobs(int $userId, int $jobLimit = 4): array
{
    $query = "SELECT 
                p.postId,
                p.topicId,
                p.userId,
                p.content,
                p.description,
                p.createdAt,
                t.name as topicName,
                t.createdAt as topicCreatedAt,
                t.jobId,
                j.name as jobName,
                u.userName,
                u.firstName,
                u.lastName
              FROM " . $this->table . " p
              INNER JOIN Topics t ON p.topicId = t.topicId
              INNER JOIN Jobs j ON t.jobId = j.jobId
              INNER JOIN users_jobs uj ON j.jobId = uj.jobId
              INNER JOIN Users u ON p.userId = u.userId
              WHERE uj.userId = ?
              AND p.postId = (
                  SELECT p2.postId 
                  FROM Posts p2 
                  WHERE p2.topicId = t.topicId 
                  ORDER BY p2.createdAt DESC 
                  LIMIT 1
              )
              ORDER BY t.createdAt DESC";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $postsByJob = [];
    $jobCount = [];
    
    while ($row = $result->fetch_assoc()) {
        $jobId = $row['jobId'];

        if (!isset($jobCount[$jobId])) {
            if (count($jobCount) >= $jobLimit) {
                continue;
            }
            $jobCount[$jobId] = 0;
        }

        if ($jobCount[$jobId] >= 1) {
            continue;
        }
        
        if (!isset($postsByJob[$jobId])) {
            $postsByJob[$jobId] = [
                'jobId' => $jobId,
                'jobName' => $row['jobName'],
                'posts' => []
            ];
        }
        
        $postsByJob[$jobId]['posts'][] = [
            'postId' => $row['postId'],
            'topicId' => $row['topicId'],
            'topicName' => $row['topicName'],
            'content' => $row['content'],
            'description' => $row['description'],
            'userName' => $row['userName'],
            'firstName' => $row['firstName'],
            'lastName' => $row['lastName'],
            'createdAt' => $row['createdAt'],
            'topicCreatedAt' => $row['topicCreatedAt']
        ];
        
        $jobCount[$jobId]++;
    }
    
    $stmt->close();
    return array_values($postsByJob);

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