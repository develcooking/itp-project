<?php

class Appointment
{
    private $conn;
    private $table = 'Appointments';
    public $appointmentId;
    public $title;
    public $start;
    public $end;
    public $description;
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
                $appointments[] = [
                    'appointmentId' => $row['appointmentId'],
                    'title' => $row['title'],
                    'start' => $row['start'],
                    'end' => $row['end'],
                    'description' => $row['description'],
                    'userId' => $row['userId'],
                    'createdAt' => $row['createdAt'],
                    'modifiedAt' => $row['modifiedAt'],
                    'createdBy' => $row['createdBy'],
                    'modifiedBy' => $row['modifiedBy']
                ];
            }
        }

        $stmt->close();
        return $appointments ?? [];
    }

    public function post()
    {
        $query = " INSERT INTO " . $this->table . "
        (title, start, end, description, userId, createdBy, modifiedBy) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "siisiii",
            $this->title,
            $this->start,
            $this->end,
            $this->description,
            $this->userId,
            $this->createdBy,
            $this->modifiedBy
        );

        if ($stmt->execute()) {
            $this->appointmentId = $stmt->insert_id;
            $stmt->close();

            return true;
        }

        $stmt->close();
        return false;
    }

    public function getById($appointmentId)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE appointmentId = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $appointmentId);
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

    public function update($appointmentId)
    {
        $query = " UPDATE " . $this->table . " 
        SET title = ?, start = ?, end = ?, description = ?, userId = ?  WHERE appointmentId = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "sii",
            $this->title,
            $this->start,
            $this->end,
            $this->description,
            $this->userId
        );

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }

        $stmt->close();
        return false;
    }

    public function delete($appointmentId)
    {
        $query = "DELETE FROM " . $this->table . " WHERE appointmentId = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->appointmentId);
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }

        $stmt->close();
        return false;
    }

}