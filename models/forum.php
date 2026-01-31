<?php

class Forum {
    
    private $conn;
    public $userid;
    private $Tusers_jobs = 'users_jobs';
    public $TJobs = 'Jobs';
    
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

    public function getTopicsByBereich(int $bereich_id): array {

    $query = "SELECT topicId, name FROM Topics WHERE jobId = ?";

    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $bereich_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $topics = [];

    while ($row = $result->fetch_assoc()) {
        $topics[] = $row;
    }

    $stmt->close();
    return $topics;
    }

    public function createTopic(int $bereich_id, string $title): bool {
        $query = "INSERT INTO Topics (name, jobId, userId, createdBy, modifiedBy) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("siiii", $title, $bereich_id, $_SESSION['userId'], $_SESSION['userId'], $_SESSION['userId']);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}