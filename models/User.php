<?php

class User{
    private $conn;
    private $table = 'Benutzer';
    public $id;
    public $name;
    public $vorname;
    public $email;
    public $passwort;
    public $art;

    public function __construct($db){
        $this->conn = $db;
    }

    public function getAll(){
        $query = " SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $users[] = [
                    
                    'id' => $row['userid'],
                    'name' => $row['name'],
                    'vorname' => $row['vorname'],
                    'email' => $row['email'],
                    'art' => $row['art']
                ];
            }
        }

        $stmt->close();
        return $users ?? [];
    }

    public function post(){
        $query = " INSERT INTO " . $this->table . "
        (name, vorname, email, password_hash, art) 
        VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);

        //$hashedPassword = password_hash($this->passwort, PASSWORD_DEFAULT)

        $stmt->bind_param(
            "ssss",
            $this->name,
            $this->vorname,
            $this->email,
          //  $hashedPassword,
            $this->art
        );

        if ($stmt->execute()) {
        $this->id = $stmt->insert_id;
        $stmt->close();
        return true;
    }
    
    $stmt->close();
    return false;
    }

    public function getById($id){
        $query = "SELECT * FROM " . $this->table . " WHERE userid = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
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

    public function update(){
        $query = " UPDATE " . $this->table . " 
        SET name = ?, vorname = ?, email = ?, password_hash = ?, art = ? 
        WHERE userid = ?";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param(
        "sssssi",
        $this->name,
        $this->vorname,
        $this->email,
        $this->passwort,
        $this->art,
        $this->id
    );

    if ($stmt->execute()) {
        $stmt->close();
        return true;
    }
    
        $stmt->close();
        return false;
    }

    public function delete(){
        $query = "DELETE FROM " . $this->table . " WHERE userid = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->id);
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }
        
        $stmt->close();
        return false;
    }
    
    private function mapData($row) {
        $this->id = $row['userid'];
        $this->name = $row['name'];
        $this->vorname = $row['vorname'] ?? null;
        $this->email = $row['email'];
        $this->passwort = $row['password_hash'];
        $this->art = $row['art'];
    }

    public function toArray() {
    return [
        'id' => $this->id,
        'name' => $this->name,
        'vorname' => $this->vorname,
        'email' => $this->email,
        'art' => $this->art
    ];
    }

    public function getByEmail($email){
        $query = "SELECT * FROM " . $this->table . " WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
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
}
?>