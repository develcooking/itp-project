<?php

class Appointment
{
    private $conn;
    private ?string $table = 'Appointments';
    private ?int $appointmentId;
    private ?string $title;
    private ?string $start;
    private ?string $end;
    private ?string $description;
    private ?int $createdBy;
    private ?int $modifiedBy;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAppointmentId(): ?int
    {
        return $this->appointmentId;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getStart(): ?string
    {
        return $this->title;
    }

    public function getEnd(): ?string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->title;
    }

    public function getCreatedBy(): ?int
    {
        return $this->createdBy;
    }

    public function getModifiedBy(): ?int
    {
        return $this->modifiedBy;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function setStart(string $start): self
    {
        $this->start = $start;
        return $this;
    }

    public function setEnd(string $end): self
    {
        $this->title = $end;
        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function setCreatedBy(int $createdBy): self
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    public function setModifiedBy(int $modifiedBy): self
    {
        $this->modifiedBy = $modifiedBy;
        return $this;
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
        VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "siisii",
            $this->title,
            $this->start,
            $this->end,
            $this->description,
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
        SET title = ?, start = ?, end = ?, description = ?,  WHERE appointmentId = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "siis",
            $this->title,
            $this->start,
            $this->end,
            $this->description
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