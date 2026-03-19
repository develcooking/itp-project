<?php

class Forum {
    
    private $conn;
    private int $userid;
    private string $Tusers_jobs = 'users_jobs';
    private string $TJobs = 'Jobs';
    
    public function __construct($conn){
        $this->conn = $conn;
    }
    
    public function getBereiche(): array {

        $userid = $_SESSION['userId'] ?? null;

        if (empty($userid)) {
            return [];
        }

        $query = "SELECT j.* FROM " . $this->Tusers_jobs . " uj JOIN " . $this->TJobs . " j ON uj.jobId = j.jobId WHERE uj.userId = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userid);
        $stmt->execute();
        $result = $stmt->get_result();

        $bereiche = [];
        while ($row = $result->fetch_assoc()) {
            $bereiche[] = $row;
        }

        $stmt->close();
        return $bereiche;
    }
    public function getallBereiche(): array {

        $query = "SELECT * FROM " . $this->TJobs ;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();

        $bereiche = [];
        while ($row = $result->fetch_assoc()) {
            $bereiche[] = $row;
        }

        $stmt->close();
        return $bereiche;
    }

    public function hasAccess(int $userId, int $jobId): bool {
        $query = "SELECT COUNT(*) as count FROM " . $this->Tusers_jobs . " WHERE userId = ? AND jobId = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $userId, $jobId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return ($row['count'] ?? 0) > 0;
    }

    public function isTopicInJob(int $topicId, int $jobId): bool {
        $query = "SELECT COUNT(*) as count FROM Topics WHERE topicId = ? AND jobId = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $topicId, $jobId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return ($row['count'] ?? 0) > 0;
    }

    public function getTopicsByBereich(int $bereich_id, ?string $search = null): array {
        // Modified query to include searching in post and comment content
        $query = "
            SELECT DISTINCT t.*, u.userName 
            FROM Topics t 
            LEFT JOIN Users u ON t.userId = u.userId 
        ";

        if (!empty($search)) {
            $query .= " 
                LEFT JOIN Posts p ON t.topicId = p.topicId 
                LEFT JOIN Comments c ON p.postId = c.postId
            ";
        }

        $query .= " WHERE t.jobId = ? ";

        if (!empty($search)) {
            // Search in topic name, post content, OR comment content
            $query .= " AND (t.name LIKE ? OR p.content LIKE ? OR c.content LIKE ?)";
        }

        $query .= " ORDER BY t.pinned DESC, t.createdAt DESC";

        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $searchTerm = "%" . $search . "%";
            // Bind parameters for jobId, topic name search, post content search, and comment search
            $stmt->bind_param("isss", $bereich_id, $searchTerm, $searchTerm, $searchTerm);
        } else {
            $stmt->bind_param("i", $bereich_id);
        }
        
        $stmt->execute();

        $result = $stmt->get_result();
        $topics = [];

        while ($row = $result->fetch_assoc()) {
            $topicId = $row['topicId'];
            $topics[$topicId] = $row;
            $topics[$topicId]['matching_posts'] = [];
            $topics[$topicId]['matching_comments'] = [];
        }
        $stmt->close();

        // If searching, fetch matching posts/comments for these topics to show snippets
        if (!empty($search) && !empty($topics)) {
            $topicIds = array_keys($topics);
            $idList = implode(',', array_fill(0, count($topicIds), '?'));
            
            // 1. Fetch matching posts
            $postQuery = "SELECT postId, topicId, content FROM Posts WHERE topicId IN ($idList) AND content LIKE ?";
            $stmt = $this->conn->prepare($postQuery);
            $types = str_repeat('i', count($topicIds)) . 's';
            $params = [...$topicIds, $searchTerm];
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $postResult = $stmt->get_result();
            
            while ($postRow = $postResult->fetch_assoc()) {
                $content = strip_tags($postRow['content']);
                $pos = mb_stripos($content, $search);
                if ($pos !== false) {
                    $start = max(0, $pos - 40);
                    $snippet = mb_substr($content, $start, 100);
                    $topics[$postRow['topicId']]['matching_posts'][] = [
                        'postId' => $postRow['postId'],
                        'content_snippet' => $snippet
                    ];
                }
            }
            $stmt->close();

            // 2. Fetch matching comments
            $commentQuery = "
                SELECT c.commentId, p.postId, p.topicId, c.content 
                FROM Comments c
                JOIN Posts p ON c.postId = p.postId
                WHERE p.topicId IN ($idList) AND c.content LIKE ?
            ";
            $stmt = $this->conn->prepare($commentQuery);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $commentResult = $stmt->get_result();

            while ($commentRow = $commentResult->fetch_assoc()) {
                $content = strip_tags($commentRow['content']);
                $pos = mb_stripos($content, $search);
                if ($pos !== false) {
                    $start = max(0, $pos - 40);
                    $snippet = mb_substr($content, $start, 100);
                    $topics[$commentRow['topicId']]['matching_comments'][] = [
                        'postId' => $commentRow['postId'],
                        'commentId' => $commentRow['commentId'],
                        'content_snippet' => $snippet
                    ];
                }
            }
            $stmt->close();
        }

        return array_values($topics);
    }

    public function createTopic(int $bereich_id, string $title): bool {
        $query = "INSERT INTO Topics (name, jobId, userId, createdBy, modifiedBy) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("siiii", $title, $bereich_id, $_SESSION['userId'], $_SESSION['userId'], $_SESSION['userId']);
        $result = $stmt->execute();
        $stmt->close();
        if ($result === true) {
            $postModel = new Post($this->conn);
            //$postModel->post
            
            return $result;
        }
        return $result;
    }
}