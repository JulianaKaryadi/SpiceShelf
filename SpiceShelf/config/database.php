<?php
class Database {
    private $servername = "localhost";
    private $username = "root";  
    private $password = "";     
    private $dbname = "spiceshelf"; 
    private $conn;

    public function getConnection() {
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);

        if ($this->conn->connect_error) {
            error_log("Connection failed: " . $this->conn->connect_error);  
            throw new Exception("Database connection failed. Please try again later.");
        }

        return $this->conn;
    }

    public function closeConnection() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>