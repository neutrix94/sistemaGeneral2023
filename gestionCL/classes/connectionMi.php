<?php
class ConnectionMi {
    // used to connect to the database
    private $dbHost;// = 'localhost'
    private $dbUser;// = 'root'
    private $dbPass;// = ''
    private $dbName;// = 'base_cdll_mis_pruebas'
    public $conn;

    function __construct(){
        error_reporting(0);
        include( '../../config.inc.php' );
        $this->dbHost = $dbHost;
        $this->dbUser = $dbUser;
        $this->dbPass = $dbPassword;
        $this->dbName = $dbName;
    }
    
    public function openConnection() {
        $this->conn = new mysqli($this->dbHost, $this->dbUser, $this->dbPass, $this->dbName);

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }

        return $this->conn;
    }

    public function closeConnection() {
        $this->conn->close();
    }
}
?>
