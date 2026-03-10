<?php

class Topic
{
    private $conn;
    private $table = 'Topics';
    public $topicId;
    public $name;
    public $jobId;
    public $userId;
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