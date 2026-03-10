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
}