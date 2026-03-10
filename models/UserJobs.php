<?php
class UserJobs {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // get job IDs for a given user ID
    public function getJobsForUserByID($userId): array {
        $sql = "SELECT jobId FROM users_jobs WHERE userId = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        
        $jobs = [];

        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $jobs[] = $row['jobId'];
        }  

        $stmt->close();
        return $jobs;
    }

    // get user IDs for a given job ID
    public function getUsersForJobID($jobID): array {
        $sql = "SELECT userId FROM users_jobs WHERE jobId = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $jobID);
        $stmt->execute();
        
        $users = [];

        
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $users[] = $row['userId']; 
        }  

        $stmt->close();
        return $users;
    }

    // Assign a job to a user
    public function assign(int $userId, int $jobId, int $createdBy): bool {
        $sql = "INSERT IGNORE INTO users_jobs (userId, jobId, createdBy, modifiedBy) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiii", $userId, $jobId, $createdBy, $createdBy);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Remove a job from a user
    public function remove(int $userId, int $jobId): bool {
        $sql = "DELETE FROM users_jobs WHERE userId = ? AND jobId = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $userId, $jobId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Remove all jobs for a user
    public function removeAllForUser(int $userId): bool {
        $sql = "DELETE FROM users_jobs WHERE userId = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}