<?php
  class dbFact{
      //Variables
      private $dbHost = 'www.sistemageneralcasa.com';
      private $dbUser = 'wwsist_produccionFacturacion';
      private $dbPass = 'T+u7Uw_qYzt#';
      private $dbName = 'wwsist_produccion_casa_luces_bazar';

      //Conexión
      public function conectDB(){
        $mysqlConnect = "mysql:host=$this->dbHost;dbname=$this->dbName";
        $dbConnection = new PDO($mysqlConnect, $this->dbUser, $this->dbPass);
        $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $dbConnection;
      }
  }
?>
