<?php

class Forum {
    
    private $conn;
    public $userid;
    private $Tusers_jobs = 'users_jobs';
    public $TJobs = 'Jobs';
    
    public function __construct($conn){
        $this->conn = $conn;
    }
    
    public function getBereiche(): array {

         //$userid = $_SESSION['userid'];
        //$query = "SELECT jobId FROM " . $this->Tusers_jobs . " WHERE userId = ?";
        //$stmt = $this->conn->prepare($query);
        //$stmt->bind_param("i", $userid);
        //$stmt->execute();
        //$bereich_id = [];
        //$result = $stmt->get_result();
        //if($result->num_rows > 0){
        //    while($row = $result->fetch_assoc()){
        //        $bereich_id[] = $row; //['berufsbereich_id'];
        //    }}
        //$stmt->close();

        //testing! (replace later with session query)
        $bereich_id = [1, 2];
        
        $condition = implode(", ", $bereich_id);

        $query = "SELECT name FROM " . $this->TJobs . " WHERE jobId IN (" . $condition . ")";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();

        $bereiche = []; // always initialize

        while ($row = $result->fetch_assoc()) {
            $bereiche[] = $row['name']; // push into array
        }

        $stmt->close();
        return $bereiche;
    }
}