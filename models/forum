<?php

class Forum{
    
    private $conn;
    private $userid = $_SESSION['userid'];
    private $Tbenutzer_berufsbereich = 'benutzer_berufsbereich';
    private $TBerufsbereiche = 'Berufsbereiche';
    
    //$userid = $_SESSION['userid'];

    public function __construct($db){
        $this->conn = $db;
    }

    public function getBereiche(){

        $query = "SELECT berufsbereich_id FROM " . $this->Tbenutzer_berufsbereich " WHERE benutzer_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(i, $userid);
        $stmt->execute();

        $result = $stmt->get_result();
        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $bereich_id = $row['berufsbereich_id'];
            }
        }

        $stmt->close();
        $query = "SELECT name FROM Berufsbereiche WHERE ID = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $bereich_id);
        $stmt->execute();

        $result = $stmt->get_result();
        if($result->num_rows > 0){
            while($row = $result->fetch_assoc())
            {
            $bereiche[] = [                                
            'name' => $row['name'],            
                          ]
            }
        }
        $stmt->close();
        
        return $bereiche ?? false;

    }
}


?>