<?php

class Forum{
    
    private $conn;
    public $userid;
    private $Tusers_jobs = 'users_jobs';
    public $TJobs = 'Jobs';
    
    
    public function __construct($conn){
        $this->conn = $conn;
    }
    
    public function getBereiche(): array{
        
        $userid = $_SESSION['userid'];
        $query = "SELECT jobId FROM " . $this->Tusers_jobs . " WHERE userId = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userid);
        $stmt->execute();
        $bereich_id = [];
        $result = $stmt->get_result();
        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $bereich_id[] = $row; //['berufsbereich_id'];
            }}
        $stmt->close();
        
        $condition = implode(separator: ', ', array: $bereich_id);
        $query = "SELECT name FROM " . $this->TJobs . " WHERE jobId IN(" . $condition . ")";
        $stmt = $this->conn->prepare($query);
        //$stmt->bind_param("i", $bereich_id['berufsbereich_id']);
        $stmt->execute();
        $bereiche = [];
        $result = $stmt->get_result();

        if($result->num_rows === 0){
            return [];
        }
        
        while($row = $result->fetch_assoc())
        {
            $bereiche[] = $row; 
        
        }
        $stmt->close();
        
        return $bereiche;

    }
    }