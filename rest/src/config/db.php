<?php
  class db{
      //Variables
      private $dbHost;// = 'localhost'
      private $dbUser;// = 'root'
      private $dbPass;// = ''
      private $dbName;// = 'base_cdll_mis_pruebas'

      function __construct(){
        error_reporting(0);
        include( '../../config.inc.php' );
        $this->dbHost = $dbHost;
        $this->dbUser = $dbUser;
        $this->dbPass = $dbPassword;
        $this->dbName = $dbName;
      }
      //Conexión
      public function conectDB(){
        $mysqlConnect = "mysql:host=$this->dbHost;dbname=$this->dbName";
        $dbConnection = new PDO($mysqlConnect, $this->dbUser, $this->dbPass);
        $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $dbConnection;
      }
  }
?>
