<?php
	class BillSystems
	{
		private $link;
		function __construct( $connection )
		{
			$this->link = $connection;
		}

	//consulta los endpoints de facturacion
		function getEndpoints(){
			$sql = "";
		}
	//consume apis de facturacion
		function sendRequestData(){
			$sql = "";
		}
	}
?>