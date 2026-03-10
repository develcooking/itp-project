<?php

class Topic
{
    private $conn;
    private $table = 'Topics';
    private int $topicId;
    private string $name;
    private int $jobId;
    private int $userId;
    private int $createdBy;
    private int $modifiedBy;

    /* #### Set functions #### */
    public function setTopicId($topicId): void
    {
        $this->topicId = $topicId;
    }
    public function setName(string $name): void
    {
        $this->name = $name;
    }
    public function setJobId(int $jobId): void
    {
        $this->jobId = $jobId;
    }
    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }
    public function setCreatedBy(int $userId): void
    {
        $this->createdBy = $userId;
    }
    public function setModifiedBy(int $userId): void
    {
        $this->modifiedBy = $userId;
    }
    /* #### Get functions #### */
    public function getTopicId(): int
    {
        return $this->topicId;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function getJobId(): int
    {
        return $this->jobId;
    }
    public function getUserId(): int
    {
        return $this->userId;
    }
    public function getCreatedBy(): int
    {
        return $this->createdBy;
    }
    public function getModifiedBy(): int
    {
        return $this->modifiedBy;
    }

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
                $topics[] = [
                    'topicId' => $row['topicId'],
                    'name' => $row['name'],
                    'jobId' => $row['jobId'],
                    'userId' => $row['userId'],
                    'createdAt' => $row['createdAt'],
                    'modifiedAt' => $row['modifiedAt'],
                    'createdBy' => $row['createdBy'],
                    'modifiedBy' => $row['modifiedBy']
                ];
            }
        }

        $stmt->close();
        return $topics ?? [];
    }

    public function post()
    {
        $query = " INSERT INTO " . $this->table . "
        (name, jobId, userId, createdBy, modifiedBy) 
        VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "siiii",
            $this->name,
            $this->jobId,
            $this->userId,
            $this->createdBy,
            $this->modifiedBy
        );

        if ($stmt->execute()) {
            $this->topicId = $stmt->insert_id;
            $stmt->close();

            return true;
        }

        $stmt->close();
        return false;
    }

    public function getById($topicId)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE topicId = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $topicId);
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
    public function getByName($topicName)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE name = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $topicName);
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

    public function update($topicId)
    {
        $query = " UPDATE " . $this->table . " 
        SET name = ?, jobId = ?, userId = ? WHERE topicId = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "siii",
            $this->name,
            $this->jobId,
            $this->userId,
            $topicId
        );

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }

        $stmt->close();
        return false;
    }

    public function delete($topicId)
    {
        $query = "DELETE FROM " . $this->table . " WHERE topicId = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $topicId);
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }

        $stmt->close();
        return false;
    }

    private function mapData($row)
    {
        $this->topicId = $row['topicId'];
        $this->name = $row['name'];
        $this->jobId = $row['jobId'];
        $this->userId = $row['userId'];
        $this->createdBy = $row['createdBy'];
        $this->modifiedBy = $row['modifiedBy'];
    }
}