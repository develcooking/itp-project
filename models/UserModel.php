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

    public function getById(){

    }

    public function update(){

    }

    public function delete(){

    }
}
?>