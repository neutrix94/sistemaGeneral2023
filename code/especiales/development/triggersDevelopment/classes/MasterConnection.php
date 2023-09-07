<?php

	class MasterConnection
	{
		private $link;
		private $dbhost;
		private $dbUser;
		private $dbPassword;
		private $dbName;
		
		function __construct( $db_host, $db_user, $db_pass, $db_name ){

			$this->dbhost = $db_host;
			$this->dbUser = $db_user;
			$this->dbPassword = $db_pass;
			$this->dbName = $db_name;
			
			$this->link = mysqli_connect( $this->dbhost, $this->dbUser, $this->dbPassword, $this->dbName );
			if( $this->link->connect_error ){
				die( "Error al conectar con la Base de Datos : " . $this->link->connect_error);
			}
			$this->link->set_charset("utf8mb4");
		}

		function getConnection(){
			return $this->link;
		}
	}

?>