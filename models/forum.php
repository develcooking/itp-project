<?php

class Forum{
    
    private $conn;
    public $userid;
    private $Tbenutzer_berufsbereich = 'benutzer_berufsbereich';
    public $TBerufsbereiche = 'Berufsbereiche';
    
    
    public function __construct($conn){
        $this->conn = $conn;
    }
    
    public function getBereiche(): array{
        
        //$userid = $_SESSION['userid'];
        //$query = "SELECT berufsbereich_id FROM " . $this->Tbenutzer_berufsbereich . " WHERE benutzer_id = ?";
        //$stmt = $this->conn->prepare($query);
        //$stmt->bind_param("i", $userid);
        //$stmt->execute();
//
        //$result = $stmt->get_result();
        //if($result->num_rows > 0){
        //    while($row = $result->fetch_assoc()){
        //        $bereich_id = $row['berufsbereich_id'];
        //    }
        //}

        //$stmt->close();
        $query = "SELECT name FROM " . $this->TBerufsbereiche . " WHERE ID = 1";
        $stmt = $this->conn->prepare($query);
        //$stmt->bind_param("i", $bereich_id);
        $stmt->execute();

        $result = $stmt->get_result();

        if($result->num_rows > 0){
            while($row = $result->fetch_assoc())
            {
            $bereiche = [                                
            'name' => $row['name']            
            ];
            
            }
        }
        else{ echo 'kein result';}
        $stmt->close();
        
        return $bereiche;

    }
}