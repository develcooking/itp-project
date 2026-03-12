<?php

class Job
{
    private $conn;
    private string $table = "Jobs";
    private int $jobId;
    private string $name;
    private int $createdBy;
    private int $modifiedBy;

    public function __construct($db)
    {
        $this->conn = $db;
    }
    public function setJobName($name) : void
    {
        $this->name = $name;
    }
    public function setCreateBy($id) : void
    {
        $this->createdBy = $id;
    }
    public function setModifiedBy($id) : void
    {
        $this->modifiedBy = $id;
    }
 

    public function getAll() : array
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

    public function existsByName($name) : bool
    {
        $query = "SELECT COUNT(*) as cnt FROM " . $this->table . " WHERE name = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['cnt'] > 0;
    }

    public function post() : bool
    {
        if ($this->existsByName($this->name)) {
            return false;
        }

        $query = " INSERT INTO " . $this->table . "
        (name, createdBy, modifiedBy ) VALUES (?, ?, ?)";

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

    public function getById($jobId) : bool
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
    public function getNameById($jobId) : string {
        $sql = "SELECT name FROM Jobs WHERE jobId = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $jobId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row["name"];
        }
        else {
            return "";
        }
    }

    private function mapData($row) : void
    {
        $this->jobId = $row['jobId'];
        $this->name = $row['name'];
        $this->createdBy = $row['createdBy'];
        $this->modifiedBy = $row['modifiedBy'];
    }

    public function update($jobId, $name) : bool {
        $query = " UPDATE " . $this->table . " 
        SET name = ? WHERE jobId = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "si",
            $name,
            $jobId
        );

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }

        $stmt->close();
        return false;
    }

    public function delete($jobId = null) : bool
    {
        $id = $jobId ?? $this->jobId;
        $query = "DELETE FROM " . $this->table . " WHERE jobId = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }

        $stmt->close();
        return false;
    }
}
