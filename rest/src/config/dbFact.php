<?php
  class dbFact{
      //Variables
      private $dbHost = 'www.lacasadelasluces.com';
      private $dbUser = 'wwlaca_production2022';
      private $dbPass = 'ZI6&knjM1**#';
      private $dbName = 'wwlaca_fact_casa_luces_bazar';

      //Conexión
      public function conectDB(){
        $mysqlConnect = "mysql:host=$this->dbHost;dbname=$this->dbName";
        $dbConnection = new PDO($mysqlConnect, $this->dbUser, $this->dbPass);
        $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $dbConnection;
      }
  }
?>
