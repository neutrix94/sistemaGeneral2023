<?php

	if( isset( $_GET['fl_triggers'] ) ){
		include 'MasterConnection.php';
		include 'FrontEnd.php';
		include 'Triggers.php';
		$action = $_GET['fl_triggers'];
		$MasterConnection = new MasterConnection( 'localhost', 'root', '', 'information_schema' );// $db_host, $db_user, $db_pass, $db_name
		
		$link = $MasterConnection->getConnection();
//var_dump($link);
		$schema_name = 'wwsist_sistema_general_produccion_2023';
		//	echo 'here';
		$Triggers = new Triggers( $link, $schema_name );
		//	echo 'here';

		switch ( $action ) {
			case 'getTableStructure':
			//die( "table :" . $_GET['table_name'] );
				return $Triggers->getTableStructure( $_GET['table_name'] );
			break;
			
			case 'getTableTriggers':
				echo $Triggers->getTableTriggers( $_GET['table_name'] );
			break;

			case 'buildTriggers' ://table_name fields key_field
				echo $Triggers->buildTriggers( $_GET['table_name'], $_GET['fields'], $_GET['key_field'] );//$events, $timing
			break;

			default:
				# code...
			break;
		}
	}

?>