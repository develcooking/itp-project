<?php   

class User{
    private $conn;
    private $table = 'users';
    private $id;
    public $name;
    public $vorname;
    public $email;
    private $passwort;
    public $art;

    public function __construct($db){
        $this->conn = $db;
    }

    public function getAll(){

    }

    public function post(){
    }

    public function getById($id){
        $query = "SELECT * FROM " . $this->table . " WHERE id = ? LIMIT 1";
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

    }

    public function delete(){
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->id);
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }
        
        $stmt->close();
        return false;
    }
}
?>