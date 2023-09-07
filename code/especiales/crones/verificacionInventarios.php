<?php
	include('report.php');//incluye archivo que genera reportes
	$report = new reportByOscar();//instancia de la clase para generar reportes

//comprueba si la petición proviene de un navegador o un CRON
	$es_navegador = 0;
	if( isset($_GET['download_report']) && $_GET['download_report'] == 1 ){
		$es_navegador = 1;
	}else{
		$es_navegador = 0;
	}

//incluye archivo de conexión
	include('../../../config.inc.php');
//conexion a mysqli
	$link = mysqli_connect($dbHost, $dbUser, $dbPassword, $dbName);

//variables de ayuda para mensaje y contador de fallas
	$mensaje = '';
	$fallas = 0;

//declara fechas de referencia
	$fecha_inicio_revision = date('Y-m-d H:i:s');
	$fecha_referencia = date('Y-m-d');

/*consulta inventarios descuadrados en la tabla de logs
	$sql = "SELECT 
				id_log,
				fecha_registro,
				TipoTransaccion,
				id_movimiento,
				id_movimiento_almacen_detalle,
				id_almacen_producto,
				id_almacen,
				id_producto,
				cantidad_inventario_antes,
				cantidad_movimiento_anterior,
				cantidad_movimiento,
				cantidad_actualizacion,
				cantidad_inventario_despues 
			FROM Log_almacen_producto 
			WHERE inventario_cuadrado = 0
			/*AND fecha_registro LIKE '%{$fecha_referencia}%'
			ORDER BY fecha_registro DESC";
	$eje = $link->query( $sql )or die("Error al consultar los inventarios descuadrados : " 
		. $link->error );

	$num = $eje->num_rows;//cuenta registros encontrados

//creacion del contenido del correo
	if( $num > 0 ){//si detecta que hay fallas crea tabla para enviar por correo
	//datos del encabezado
		$encabezado = array('#', 'Id Log', 'Fecha', 'Tipo de Transacción', 
			'Id de cabecera de Movimiento', 'Id de detalle de Movimiento', 
			'Id de almacen producto', 'Almacen', 'Producto', 'Cantidad Inv Anterior',
			'Cantidad Mov Anterior', 'Cantidad Movimiento', 'Cantidad Actualizacion',
			'Cantidad inventario después');
		$mensaje = ( $es_navegador == 1 ? 
					$report->csv_header_generator( $encabezado ) : 
					$report->crea_tabla_log( $encabezado, '<h2>Se localizaron las siguientes diferencias entre los inventarios : </h2>' ) 
		);//crea encabezado de la tabla
		$contenido_tabla = '';
		while ( $row = $eje->fetch_assoc() ) {
			$fallas ++;//suma una falla al reporte que se envia por correo
		//crea una fila por cada registro encontrado con diferencias
			$contenido_tabla .= ( $es_navegador == 1 ? 
								$report->csv_row_generator( $row ) : 
								$report->crea_fila_tabla_log( $row ) );
		}
	//agrega el contenido a la tabla
		if ( $es_navegador == 0 ){
			$mensaje = str_replace('|table_content|', $contenido_tabla, $mensaje);
		}else{
			$mensaje .= $contenido_tabla;
		}
	}else{
//si no encuentra diferencias en los inventarios, notifica que todo está bien
		$mensaje = "<h2>No se encontraron diferencias en la tabla de Logs de inventario (Log_almacen_producto).</h2>";
	}*/
//verifica inventario 
	include('verificacionInventariosCalculo.php');
	include('barcodes.php');
//variable que idica el termino de la revision
	$fecha_fin_revision = date('Y-m-d H:i:s');
	$mensaje .= "<p> Inicio de revisión de inventarios : <b>{$fecha_inicio_revision}</b></p>";
	$mensaje .= "<p> Fin de revisión de inventarios : <b>{$fecha_fin_revision}</b></p>";
	
	echo ($es_navegador == 1 ? $report->genera_descarga_csv( $mensaje ) : $report->enviar_email( $mensaje ) );
	echo ($es_navegador == 0 ? $mensaje : '');
?>