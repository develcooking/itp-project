<?php

class Job
{
    private $conn;
    private $table = "Jobs";

    public $jobId;
    public $name;
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
                $jobs[] = [
                    'jobId' => $row['jobId'],
                    'name' => $row['name'],
                    'createdAt' => $row['createdAt'],
                    'modifiedAt' => $row['modifiedAt'],
                    'createdBy' => $row['createdBy'],
                    'modifiedBy' => $row['modifiedBy']
                ];
            }
        }

        $stmt->close();
        return $jobs ?? [];
    }

    public function post()
    {
        $query = " INSERT INTO " . $this->table . "
        (name, createdBy, modifiedBy) 
        VALUES (?, ?, ?)";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "sii",
            $this->name,
            $this->createdBy,
            $this->modifiedBy
        );

        if ($stmt->execute()) {
            $this->jobId = $stmt->insert_id;
            $stmt->close();

            return true;
        }

        $stmt->close();
        return false;
    }

    public function getById($jobId)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE jobId = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $jobId);
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

    public function update($jobId)
    {
        $query = " UPDATE " . $this->table . " 
        SET name = ?  WHERE jobId = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "s",
            $this->name
        );

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }

        $stmt->close();
        return false;
    }

    public function delete($jobId)
    {
        $query = "DELETE FROM " . $this->table . " WHERE jobId = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->jobId);
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }

        $stmt->close();
        return false;
    }
}