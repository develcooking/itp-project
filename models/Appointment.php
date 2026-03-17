<?php

class Appointment
{
    private $conn;
    private ?string $table = 'Appointments';
    private ?int $appointmentId;
    private ?int $jobId;
    private ?string $title;
    private ?string $start;
    private ?string $end;
    private ?string $description;
    private ?int $createdBy;
    private ?int $modifiedBy;
    private ?string $creatorName;
    private ?string $recurrenceType = 'none';
    private ?int $recurrenceInterval = 1;
    private ?string $recurrenceUntil = null;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getCreatorName(): ?string
    {
        return $this->creatorName;
    }

    public function getAppointmentId(): ?int
    {
        return $this->appointmentId;
    }
    public function getJobId(): ?int
    {
        return $this->jobId;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getStart(): ?string
    {
        return $this->start; 
    }

    public function getEnd(): ?string
    {
        return $this->end; 
    }

    public function getDescription(): ?string
    {
        return $this->description; 
    }

    public function getCreatedBy(): ?int
    {
        return $this->createdBy;
    }

    public function getModifiedBy(): ?int
    {
        return $this->modifiedBy;
    }

    public function getRecurrenceType(): ?string
    {
        return $this->recurrenceType;
    }

    public function getRecurrenceInterval(): ?int
    {
        return $this->recurrenceInterval;
    }

    public function getRecurrenceUntil(): ?string
    {
        return $this->recurrenceUntil;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function setJobId(int $jobId): self
    {
        $this->jobId = $jobId;
        return $this;
    }

    public function setStart(string $start): self
    {
        $this->start = $start;
        return $this;
    }

    public function setEnd(string $end): self
    {
        $this->end = $end;
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
    public function setRecurrenceType(string $type): self
    {
        $this->recurrenceType = $type;
        return $this;
    }
    public function setRecurrenceInterval(int $interval): self
    {
        $this->recurrenceInterval = $interval;
        return $this;
    }
    public function setRecurrenceUntil(?string $until): self
    {
        $this->recurrenceUntil = $until;
        return $this;
    }

    public function getAll($filterJobId = null, $start = null, $end = null)
    {
        $query = "SELECT a.*, u.userName as creatorName FROM " . $this->table . " a 
                  LEFT JOIN Users u ON a.createdBy = u.userId WHERE 1=1";
        $params = [];
        $types = "";

        if ($filterJobId) {
            $query .= " AND a.jobId = ?";
            $params[] = $filterJobId;
            $types .= "i";
        }

        if ($start && $end) {
             // For recurring events, we need to fetch them even if the base record is before the start
             // as long as the series hasn't ended.
             $query .= " AND ( (a.recurrence_type = 'none' AND a.end >= ? AND a.start <= ?) 
                           OR (a.recurrence_type != 'none' AND a.start <= ? AND (a.recurrence_until IS NULL OR a.recurrence_until >= ?)) )";
             $params[] = $start;
             $params[] = $end;
             $params[] = $end;
             $params[] = $start;
             $types .= "ssss";
        } elseif ($start) {
            $query .= " AND (a.end >= ? OR (a.recurrence_type != 'none' AND (a.recurrence_until IS NULL OR a.recurrence_until >= ?)))";
            $params[] = $start;
            $params[] = $start;
            $types .= "ss";
        } elseif ($end) {
            $query .= " AND a.start <= ?";
            $params[] = $end;
            $types .= "s";
        }

        $stmt = $this->conn->prepare($query);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $appointments = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $appointments[] = [
                    'appointmentId' => $row['appointmentId'],
                    'title' => $row['title'],
                    'jobId' => $row['jobId'],
                    'start' => $row['start'],
                    'end' => $row['end'],
                    'description' => $row['description'],
                    'recurrence_type' => $row['recurrence_type'],
                    'recurrence_interval' => $row['recurrence_interval'],
                    'recurrence_until' => $row['recurrence_until'],
                    'createdAt' => $row['createdAt'],
                    'modifiedAt' => $row['modifiedAt'],
                    'createdBy' => $row['createdBy'],
                    'modifiedBy' => $row['modifiedBy'],
                    'creatorName' => $row['creatorName']
                ];
            }
        }

        $stmt->close();
        return $appointments;
    }

    public function getForUserJobs(int $userId): array
    {
        $query = "SELECT a.* FROM " . $this->table . " a 
                  JOIN users_jobs uj ON a.jobId = uj.jobId 
                  WHERE uj.userId = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $appointments = [];
        while ($row = $result->fetch_assoc()) {
            $appointments[] = $row;
        }
        $stmt->close();
        return $appointments;
    }

    public function post(): bool
    {
        $query = " INSERT INTO " . $this->table . "
        (title, jobId, start, end, description, createdBy, modifiedBy, recurrence_type, recurrence_interval, recurrence_until) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);

        // types: s=string, i=integer
        // Title(s), JobId(i), Start(s), End(s), Desc(s), Created(i), Modified(i) type(s) interval(i) until (s)
        $stmt->bind_param(
            "sisssiisis",
            $this->title,
            $this->jobId,
            $this->start,
            $this->end,
            $this->description,
            $this->createdBy,
            $this->modifiedBy,
            $this->recurrenceType,
            $this->recurrenceInterval,
            $this->recurrenceUntil
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
        $query = "SELECT a.*, u.userName as creatorName FROM " . $this->table . " a 
                  LEFT JOIN Users u ON a.createdBy = u.userId 
                  WHERE a.appointmentId = ?";
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
    private function mapData($row) {
        $this->appointmentId = $row['appointmentId'];
        $this->jobId = $row['jobId'];
        $this->title = $row['title'];
        $this->start = $row['start'];
        $this->end = $row['end'];
        $this->description = $row['description'];
        $this->recurrenceType = $row['recurrence_type'] ?? 'none';
        $this->recurrenceInterval = $row['recurrence_interval'] ?? 1;
        $this->recurrenceUntil = $row['recurrence_until'] ?? null;
        $this->createdBy = $row['createdBy'];
        $this->modifiedBy = $row['modifiedBy'];
        $this->creatorName = $row['creatorName'] ?? null;
    }


    public function update($appointmentId)
    {
        $query = " UPDATE " . $this->table . " 
        SET title = ?, start = ?, end = ?, description = ?, jobId = ?, recurrence_type = ?, recurrence_interval = ?, recurrence_until = ? WHERE appointmentId = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "ssssisisi",
            $this->title,
            $this->start,
            $this->end,
            $this->description,
            $this->jobId,
            $this->recurrenceType,
            $this->recurrenceInterval,
            $this->recurrenceUntil,
            $appointmentId
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
        $stmt->bind_param("i", $appointmentId);
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }

        $stmt->close();
        return false;
    }
}
