<?php
  class db{
      //Variables
      private $dbHost = 'localhost';
      private $dbUser = 'root';
      private $dbPass = '';
      private $dbName = 'wwsist_sistema_general_produccion_2023';

      //Conexión
      public function conectDB(){
        $mysqlConnect = "mysql:host=$this->dbHost;dbname=$this->dbName";
        $dbConnection = new PDO($mysqlConnect, $this->dbUser, $this->dbPass);
        $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $dbConnection;
      }
  }
?>
