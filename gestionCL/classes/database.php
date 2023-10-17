<?php

class db
{

    // used to connect to the database
    private $dbHost;// = 'localhost';
    private $dbUser;// = 'root';
    private $dbPass;// = '';
    private $dbName;// = 'base_cdll_mis_pruebas';

    function __construct(){
        error_reporting(0);
        include( '../../config.inc.php' );
        $this->dbHost = $dbHost;
        $this->dbUser = $dbUser;
        $this->dbPass = $dbPassword;
        $this->dbName = $dbName;
    }

    // get the database connection
    public function conectDB()
    {
        try {
          $mysqlConnect = "mysql:host=$this->dbHost;dbname=$this->dbName;charset=UTF8";
          $dbConnection = new PDO($mysqlConnect, $this->dbUser, $this->dbPass);
          $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            echo "Database Connection Error: " . $exception->getMessage();
        }
        return $dbConnection;
    }

}
