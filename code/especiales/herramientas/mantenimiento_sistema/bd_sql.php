<?php
	$fl=$_POST['flag'];
	//include( '../../../../conectMin.php' );
//instancia de clase para ejecutar coimandos DDL en mysql ( Implementacion Oscar 2023 )
	include( '../../../../conexionMysqli.php' );
	include( './mysqlDDL.php' );
	$mysqlDDL = new mysqlDDL( $link );

	if( $fl == 'pause_sinchronization_apis' || $fl == 'renew_sinchronization_apis' ){
		$value = ( $fl == 'pause_sinchronization_apis' ? 1 : 0 );
		$action = ( $fl == 'pause_sinchronization_apis' ? 'bloquear' : 'desbloquear' );
		$sql = "UPDATE sys_configuracion_sistema SET bloquear_apis_sincronizacion = {$value}";
		$stm = $link->query( $sql ) or die( "Error al {$action} APIS de sincronizacion : {$sql} {$link->error}" );
		die( 'ok' );
		//renew_sinchronization_apis
	}
	if( $fl == 'pause_sinchronization_apis_store' || $fl == 'renew_sinchronization_apis_store' ){
		$value = ( $fl == 'pause_sinchronization_apis_store' ? 0 : 1 );
		$action = ( $fl == 'pause_sinchronization_apis_store' ? 'bloquear' : 'desbloquear' );
		$sql = "UPDATE sys_resumen_sincronizacion_sucursales SET permite_sincronizar_manualmente = {$value}";
		//die($sql);
		$stm = $link->query( $sql ) or die( "Error al {$action} sincronizacion de la sucursal: {$sql} {$link->error}" );
		die( 'ok' );
		//renew_sinchronization_apis
	}

	if( $fl == 'restoration_mode' ){//{$user_id}
	//consulta si las apis estan bloqueadas
		$sql = "SELECT 
					bloquear_apis_sincronizacion AS apis_are_locked
				FROM sys_configuracion_sistema
				WHERE id_configuracion_sistema = 1";
		$stm = $link->query( $sql ) or die( "Error al {$action} APIS de sincronizacion : {$sql} {$link->error}" );
		$row = $stm->fetch_assoc();
		if( $row['apis_are_locked'] == 0 ){
			die( "Es necesario bloquear las APIS antes de activar el modo de restauracion!" );
		}
		$sql="INSERT INTO sys_respaldos ( id_respaldo, id_usuario, fecha, hora, observaciones, realizado ) 
		VALUES( NULL, 1, now(), now(), 'Respaldo generado por el usuario $user_id desde $user_sucursal',0)";
		$stm = $link->query( $sql ) or die( "Error al insertar registro de sincronizacion : {$sql} {$link->error}" );
		$id = $link->insert_id;
	//consulta el prefijo de la sucursal de acceso
		$sql = "SELECT prefijo AS store_prefix FROM sys_sucursales WHERE acceso = 1";
		$stm = $link->query( $sql ) or die( "Error al consultar prefijo de la sucursal : {$link->error}" );
		$row = $stm->fetch_assoc();
		$store_prefix = $row['store_prefix'];
	//actualiza el folio unico
		$sql = "UPDATE sys_respaldos SET folio_unico = '{$store_prefix}_RESTAURACION_{$id}' WHERE id_respaldo = {$id}";
		$stm = $link->query( $sql ) or die( "Error al actualizar folio unico del respaldo : {$link->error}" );
	//recupera los datos
		$sql = "SELECT 
					fecha AS date, 
					hora AS hour, 
					folio_unico AS unique_folio 
				FROM sys_respaldos 
				WHERE id_respaldo = {$id}";
		$stm = $link->query( $sql ) or die( "Error al consultar prefijo de la sucursal : {$link->error}" );
		$row = $stm->fetch_assoc();
		die( "ok|El folio de restauracion es : '{$row['unique_folio']}'\nFecha y hora : {$row['date']} {$row['hour']}" );
	}

//1. Insercion de procedures
	if($fl=='procedures_inserta'){	
		$getStoredProcedures = $mysqlDDL->alterInventoryTriggersSinceFiles( '../../../../respaldos/storedProcedures/' );
		if( $getStoredProcedures != 'ok' ){
			die( "Ocurrio un Error al insertar los Stored procedures : {$getStoredProcedures}" );
		}
	//1.1. Incluye archivo /conexionDoble.php
		/*include('../../../../conexionDoble.php');
		$s=$hostLocal;
		$bd=$nombreLocal;
		$u=$userLocal;
		$p=$passLocal;

		$conexion_sqli=new mysqli($s,$u,$p,$bd);
		if($conexion_sqli->connect_errno){
			die("sin conexion");
		}else{
			//echo "conectado";
		}

		$cadena_arreglo="";
		$fp = fopen("../../../../respaldos/procedures.sql", "r")or die("Error");
		while (!feof($fp)){
		 	$linea = fgets($fp);
		 	$cadena_arreglo.=$linea;
		}
		fclose($fp);
	//echo $cadena_arreglo;
		//$cadena_arreglo=str_replace("DELIMITER $$", "", $cadena_arreglo);
		$arreglo_procedure=explode("|", $cadena_arreglo);
		for($i=0;$i<sizeof($arreglo_procedure);$i++){
	//		echo "Array: ".$arreglo_procedure[$i]."\n";
			$arreglo_procedure[$i]=str_replace("DELIMITER $$", "", $arreglo_procedure[$i]);
			$arreglo_procedure[$i]=str_replace("$$", "", $arreglo_procedure[$i]);
			$eje=mysqli_multi_query($conexion_sqli,$arreglo_procedure[$i]);
			if(!$eje){
				die("Error con mysqli!!!".mysqli_error($conexion_sqli));
			}
		}*/
		die('ok|');
	}
/*Fin de cambio Oscar 20.12.2019*/

//2. Incluye archivo /conectMin.php
	include('../../../../conectMin.php');
	$dato=$_POST['valor'];
	$id_agrupacion=$_POST['tipo_agrupacion'];
	$tipo_mantenimiento=$_POST['tipo'];

//3. Calculo de dias
	if($fl=='obtener_dias'){
		$fcha=$_POST['fecha'];
		$sql="SELECT TIMESTAMPDIFF(DAY,'$fcha',CURRENT_DATE())";
		$eje=mysql_query($sql)or die("Error al calcular los días de diferencia entre fechas!!!".mysql_error());
		$r=mysql_fetch_row($eje);
		die($r[0]);
	}

//4. Actualizacion de configuracion de agrupamiento
		switch($fl){
			case 'por_dia':
				$sql="UPDATE sys_configuracion_sistema SET minimo_agrupar_ma_dia='$dato' WHERE id_configuracion_sistema=1";
			break;
			case 'por_ano':
				$sql="UPDATE sys_configuracion_sistema SET minimo_agrupar_ma_ano='$dato' WHERE id_configuracion_sistema=1";
			break;
			case 'por_anteriores':
				$sql="UPDATE sys_configuracion_sistema SET minimo_agrupar_ma_anteriores='$dato' WHERE id_configuracion_sistema=1";
			break;
			case 'por_dia_vta':
				$sql="UPDATE sys_configuracion_sistema SET minimo_agrupar_vtas_dias='$dato' WHERE id_configuracion_sistema=1";
			break;
			case 'por_ano_vta':
				$sql="UPDATE sys_configuracion_sistema SET minimo_agrupar_vtas_ano='$dato' WHERE id_configuracion_sistema=1";
			break;
			case 'por_anteriores_vta':
				$sql="UPDATE sys_configuracion_sistema SET minimo_agrupar_vtas_anteriores='$dato' WHERE id_configuracion_sistema=1";
			break;
			case 'eliminar_sin_uso':
				$sql="UPDATE sys_configuracion_sistema SET minimo_eliminar_reg_no_usados='$dato' WHERE id_configuracion_sistema=1";
			break;
			case 'eliminar_alertas_inventario':
				$sql="UPDATE sys_configuracion_sistema SET minimo_eliminar_reg_sin_inventario='$dato' WHERE id_configuracion_sistema=1";
				//die($sql);
			break;

	/*Implementacion Oscar 20-09-2020 para recalcular inventarios de ec_almacen_producto por medio del boton*/
			case "recalcula_inventario_almacen" : 
				$sql = "CALL recalculaInventariosAlmacen()";
			break;

			case 'historico_productos' : 
				mysql_query( "BEGIN" );
			//elimina historico anterior
				$sql = "TRUNCATE ec_productos_notas_historico";
				$eje = mysql_query($sql) or die( "Error al eliminar notas de tabla de históricos : " . mysql_error() );
			//inserta la nota general
				$sql = "INSERT INTO ec_productos_notas_historico
						SELECT 
							NULL,
							/*id_categoria_nota*/-1,
							/*id_valor_nota*/-1,
							/*id_producto*/id_productos,
							1,
							observaciones
						FROM ec_productos
						WHERE id_productos > 0";
				$eje = mysql_query($sql) or die( "Error al insertar notas generales en tabla de históricos : " . mysql_error() );
			//actualiza la nota general en productos
				$sql = "UPDATE ec_productos SET observaciones = '' WHERE id_productos >0";
				$eje = mysql_query($sql) or die( "Error al resetear nota general en productos : " . mysql_error() );
			

				$sql = "INSERT INTO ec_productos_notas_historico
						SELECT 
							NULL,
							id_categoria_nota,
							id_valor_nota,
							id_producto,
							id_usuario,
							nota
						FROM ec_productos_notas
						WHERE id_producto_nota > 0";
				$eje = mysql_query($sql) or die( "Error al insertar notas en la tabla de históricos : " . mysql_error() );
				$sql = "DELETE FROM ec_productos_notas WHERE id_producto_nota > 0 ";
				$eje = mysql_query($sql) or die( "Error al eliminar notas en la tabla de notas de productos : " . mysql_error() );
				
				mysql_query( "COMMIT" );
				die( 'ok' );
			break;
/*Fin de cambio*/
		}
//die($sql);
/**/ 

//5. Ejecucion de procedures
	if($fl=='procedure'){
	//elimina los triggers ( implementacion Oscar 2023 )
		$disabled_triggers = $mysqlDDL->alterInventoryTriggersSinceFiles( '../../../../respaldos/eliminarTriggersInventarios/' );
		if( $disabled_triggers != 'ok' ){
			die( "Error al deshabilitar los triggers de inventario : {$disabled_triggers}" );
		}
		if($id_agrupacion==2){
        //agrupa a nivel producto ( implermentacion Oscar 2023 )
            $sql = "CALL parametrosAgrupaMovimientosAlmacenProveedorProducto($id_agrupacion,$dato);";
            $stm = mysql_query( $sql ) or die( "Error al correr el procedure de agrupamiento proveedor producto por dia : " . mysql_error() );
        //agrupa a nivel proveedor producto
			$sql="CALL parametrosAgrupaMovimientosAlmacen($id_agrupacion,$dato);";//flag:'procedure',valor:dato_nvo,tipo_agrupacion:flag
		}

		if($id_agrupacion==3){
            $sql = "CALL parametrosAgrupaMovimientosAlmacenPorAnoProveedorProducto($id_agrupacion,$dato);";
            $stm = mysql_query( $sql ) or die( "Error al correr el procedure de agrupamiento proveedor producto por año : " . mysql_error() );
			$sql="CALL parametrosAgrupaMovimientosAlmacenPorAno($id_agrupacion,$dato);";//flag:'procedure',valor:dato_nvo,tipo_agrupacion:flag
		}

		if($id_agrupacion==4){
            $sql = "CALL agrupaMovimientosProveedorProducto($id_agrupacion,$dato);";
            $stm = mysql_query( $sql ) or die( "Error al correr el procedure de agrupamiento proveedor producto por anteriores : " . mysql_error() );
			$sql="CALL agrupaMovimientosAlmacen($id_agrupacion,$dato);";//flag:'procedure',valor:dato_nvo,tipo_agrupacion:flag
		}	

		if($tipo_mantenimiento=='vta'){
		//	die('here');
			switch ($id_agrupacion) {
				case 2:
					$sql="CALL parametrosAgrupaVentas($id_agrupacion,$dato);"; 
					break;
				case 3:
					$sql="CALL parametrosAgrupaVentasPorAno($id_agrupacion,$dato);"; 
					break;
				case 4:
					$sql="CALL agrupaVentas($id_agrupacion,$dato);";
					break;
				case 5:
					$sql="CALL eliminaRegistrosMantenimiento($dato);";
					break;

				case 8:
					$sql="CALL eliminaRegistrosProductosSinInventario($dato);";
					break;
			}
		}	
	}
//tipo
//	mysql_query("BEGIN");//marcamos el inicio de transaccion
	$eje=mysql_query($sql);
	if(!$eje){
		$error=mysql_error();
//		mysql_query("ROLLBACK");//cancemos transaccion
		die($error);
	}

/*Implementacion Oscar 20-09-2020 para recalcular inventarios de ec_almacen_producto*/
	if($fl=='procedure'){
		$sql = "CALL recalculaInventariosAlmacen()";
		$eje = mysql_query($sql);
		if( !$eje ){
			$error=mysql_error();
			die("Error al recalcular el inventario de almacenes por producto : " . $error);
		}
	/*implementacion OScar 2023 para recalcular el inventario a nivel proveedor producto*/
		$sql = "CALL recalculaInventarioAlmacenProveedorProducto()";
		$eje = mysql_query($sql);
		if( !$eje ){
			$error=mysql_error();
			die("Error al recalcular el inventario de almacenes por proveedor - producto : " . $error);
		}
	/*fin de cambio Oscxar 2023*/
	}
/*Fin de cambio*/
/*Implementacion Oscar 29-03-2022 para resetear los productos con orden de lista cero ( 0 ) */
	if($fl=='recorre_productos_por_liberar'){
		$sql = "CALL recorre_productos_por_liberar()";
		$eje = mysql_query($sql) or die( "error" . mysql_error() );
		if( !$eje ){
			$error=mysql_error();
			die("Error al resetear los productos con orden de lista Cero (0) : " . $error);
		}
	}
/*Fin de cambio*/
/*Implementacion Oscar 21-06-2022 códigos de barras únicos */
	if($fl=='prefijo_codigos_unicos'){
		include( '../../../../conexionMysqli.php' );
		include( '../../Etiquetas/barcodes/ajax/db.php' );
		updateBarcodesPrefix( $link, 1 );
	}
	if( $fl == 'validateBarcodesSeriesUpdate' ){
		include( '../../../../conexionMysqli.php' );
		include( '../../Etiquetas/barcodes/ajax/db.php' );
		die( validateBarcodesSeriesUpdate( $link ) );
	}

	if( $fl == 'updateBarcodesPrefix' ){
		include( '../../../../conexionMysqli.php' );
		include( '../../Etiquetas/barcodes/ajax/db.php' );
		die( updateBarcodesPrefix( $link ) );
	}
/*Fin de cambio*/

//implementacion Oscar 2023 para reinsertar los almacenes productos que faltan
	if( $fl == "reinsertar_almacen_producto" ){
		$sql = "CALL reinsertaAlmacenProducto()";
		$eje = mysql_query($sql) or die( "error" . mysql_error() );
		if( !$eje ){
			$error=mysql_error();
			die("Error al reinsertar los productos faltantes en almacen produicto : " . $error);
		}
	}
//fin de cambio Oscar 2023

//implementacion Oscar 2023 para reinsertar trigger de inventario
	if( $fl == 'procedure' || $fl ==  'triggers_movimientos' ){
		$enabled_triggers = $mysqlDDL->alterInventoryTriggersSinceFiles( '../../../../respaldos/triggersInventarios/' );
		if( $enabled_triggers != 'ok' ){
			die( "Ocurrio un problema al reinsertar los triggers de inventario en la base de datos : {$enabled_triggers}" );
		}
		if( $fl ==  'triggers_movimientos' ){
			die('ok');
		}
	}
//implementacion Oscar 2023 para reinsertar triggers de sistema
	if( $fl == 'triggers_sistema' ){
		$enabled_triggers = $mysqlDDL->alterInventoryTriggersSinceFiles( '../../../../respaldos/triggers_sistema/' );
		if( $enabled_triggers != 'ok' ){
			die( "Ocurrio un problema al reinsertar los triggers del sistema en la base de datos : {$enabled_triggers}" );
		}
		if( $fl ==  'triggers_movimientos' ){
			die('ok');
		}
	}
//fin de cambio Oscar 2023
//implementacion Oscar 2023 para reinsertar triggers de sistema
	if( $fl == 'triggers_transferencias' ){
		$enabled_triggers = $mysqlDDL->alterInventoryTriggersSinceFiles( '../../../../respaldos/triggers_transferencias/' );
		if( $enabled_triggers != 'ok' ){
			die( "Ocurrio un problema al reinsertar los triggers de transferencias en la base de datos : {$enabled_triggers}" );
		}
		if( $fl ==  'triggers_transferencias' ){
			die('ok');
		}
	}
//fin de cambio Oscar 2023

//implementacion Oscar 2023 para actualizar Scripts desde boton
	if( $fl == 'update_scripts' ){
	//consulta el path local desde el archivo conexion_inicial.txt
		$local_path = "";
		$archivo_path = "../../../../conexion_inicial.txt";
		if(file_exists($archivo_path) ){
			$file = fopen($archivo_path,"r");
			$line=fgets($file);
			fclose($file);
			$config=explode("<>",$line);
			$tmp=explode("~",$config[0]);
			$local_path = base64_decode( $tmp[1] );
		}else{
			die("No hay archivo de configuración!!!");
		}
	//fin de cambio Oscar 2023
		$sql = "SELECT 
	        	TRIM(value) AS path
	        FROM api_config WHERE name = 'path'";
		$stm = mysql_query( $sql ) or die( "Error al consultar path de api : " . mysql_error() );
		//die( $sql );
		$config_row = mysql_fetch_assoc( $stm );
		//$api_path = $config_row['path']."/rest/mysql_versioner/updateScripts";
		$api_path = "http://localhost/{$local_path}/rest/mysql_versioner/updateScripts";
	//die( 'Here : ' . $api_path );
		$post_data = json_encode( $petition_data );
		$resp = "";
		$crl = curl_init( "{$api_path}" );
		curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($crl, CURLINFO_HEADER_OUT, true);
		curl_setopt($crl, CURLOPT_POST, true);
		curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
		//curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 60000);
		curl_setopt($crl, CURLOPT_HTTPHEADER, array(
		  'Content-Type: application/json',
		  'token: ' . $token)
		);
		$resp = curl_exec($crl);//envia peticion
		curl_close($crl);
		//var_dump($resp);
		$response = json_decode($resp);
		die( $response->response );
		
	}
/*implementacion Oscar 2023 para eliminar los alertas de inventario que son erroneas ( mayor a 1000 piezas )*/
	if( $fl == "eliminar_alertas_inventarios_erroneas" ){
	/*consulta para verificar que alertas son erroneas ( no se usa durante este proceso )
		$sql = "SELECT 
					*,
					REPLACE( ( REPLACE( SUBSTRING_INDEX(`observaciones`, ' ', 3), 'Se pidieron ', '' ) ), 'Se pidió ', '' ) 
				FROM `ec_productos_sin_inventario` 
				WHERE REPLACE( ( REPLACE( SUBSTRING_INDEX(`observaciones`, ' ', 3), 'Se pidieron ', '' ) ), 'Se pidió ', '' ) >= 1000 
				ORDER BY REPLACE( ( REPLACE( SUBSTRING_INDEX(`observaciones`, ' ', 3), 'Se pidieron ', '' ) ), 'Se pidió ', '' ) ASC";*/
		$number = $_POST['min_number'];
		$sql = "DELETE FROM ec_productos_sin_inventario 
				WHERE REPLACE( ( REPLACE( SUBSTRING_INDEX( observaciones, ' ', 3), 'Se pidieron ', '' ) ), 'Se pidió ', '' ) >= {$number}";
		$stm = mysql_query( $sql ) or die( "Error al eliminar las alertas de inventario erroneas ( mayores a 1000 piezas ): " . mysql_error() );
		die( 'ok' );
	}
//fin de cambio Oscar 2023

//	mysql_query("COMMIT");//autorizamos transaccion
	die('ok');
?>