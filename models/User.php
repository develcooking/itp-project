<?php

class User{
    private $conn;
    private $table = 'Benutzer';
    public $userId;
    public $userName;
    public $firstName;
    public $lastName;
    public $email;
    public $password;
    public $role;
    public $securityAnswer;
    public $activated;
    public $createdAt;
    public $modifiedAt;
    public $createdBy;
    public $modifiedBy;

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
                    'userId' => $row['userid'],
                    'userName' => $row['userName'],
                    'firstName' => $row['firstName'],
                    'lastName' => $row['lastName'],
                    'email' => $row['email'],
                    'password' => $row['password'],
                    'role' => $row['role'],
                    'securityAnswer' => $row['securityAnswer'],
                    'activated' => $row['activated'],
                    'createdAt' => $row['createdAt'],
                    'modifiedAt' => $row['modifiedAt'],
                    'createdBy' => $row['createdBy'],
                    'modifiedBy' => $row['modifiedBy']
                ];
            }
        }

        $stmt->close();
        return $users ?? [];
    }

    public function post(){
        $query = " INSERT INTO " . $this->table . "
        (userName, firstName, lastName, email, password, role, securityAnswer, activated, createdAt, modifiedAt, createdBy, modifiedBy) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);

        $password = password_hash($this->password, PASSWORD_DEFAULT);

        $stmt->bind_param(
            "sssssssissii",
            $this->userName,
            $this->firstName,
            $this->lastName,
            $this->email,
            $password,
            $this->role,
            $this->securityAnswer,
            $this->activated,
            $this->createdAt,
            $this->modifiedAt,
            $this->createdBy,
            $this->modifiedBy
        );

        if ($stmt->execute()) {
        $this->id = $stmt->insert_id;
        $stmt->close();


        /* grad geht ja so dass der user by system erstellt wird, aber muss nicht so sein,
        -> so ja mit der funktion kann ma machen createdBy === userId
        if ($this->createdBy === 0) {
            $this->createdBy = $this->userId;
            $this->modifiedBy = $this->userId;
            $this->updateCreatedBy();
        }
        */

        return true;
    }
    
    $stmt->close();
    return false;
    }

    public function getById($userId){
        $query = "SELECT * FROM " . $this->table . " WHERE userid = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
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


    public function update($userId){
        $query = " UPDATE " . $this->table . " 
        SET userName = ?, firstName = ?, lastName = ?, email = ?, password = ?, role = ?, securityAnswer = ?, activated = ? WHERE userid = ?";
    
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
        "sssssi",
        $this->userId,
        $this->userName,
        $this->firstName,
        $this->lastName,
        $this->email,
        $this->password,
        $this->role,
        $this->securityAnswer,
        $this->activated);

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }
    
        $stmt->close();
        return false;
    }

    public function delete($userId){
        $query = "DELETE FROM " . $this->table . " WHERE userid = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->userId);
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }
        
        $stmt->close();
        return false;
    }
    
    private function mapData($row) {
        $this->userId = $row['userId'];
        $this->userName = $row['userName'];
        $this->firstName = $row['firstName'] ?? null;
        $this->lastName = $row['lastName'];
        $this->email = $row['email'];
        $this->password = $row['password'];
        $this->role = $row['role'];
        $this->securityAnswer = $row['securityAnswer'];
        $this->activated = $row['activated'];
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

    private function updateCreatedBy() {
        $query = "UPDATE " . $this->table . " SET createdBy = ?, modifiedBy = ? WHERE userid = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iii", $this->userId, $this->userId, $this->userId);
        $stmt->execute();
        $stmt->close();
    }
}
?>