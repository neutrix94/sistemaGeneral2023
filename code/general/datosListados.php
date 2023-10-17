<?php
/*version 30.10.2019*/
	extract($_POST);
	extract($_GET);
	require("../../conectMin.php");
	//die( 'here' );
//Buscamos los datos de configuracion del listado
	$sql="SELECT
	      tabla,
		  consulta,
		  campos,
		  ver,
		  modificar,
		  eliminar,
		  condicion,
		  no_tabla,
		  consulta_buscador
		  FROM sys_listados
		  WHERE id_listado = '$id_listado'";

	$res=mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
	
	if(mysql_num_rows($res) == 0)
		die("No se encontraron datos para el listado");
		
	$datList=mysql_fetch_assoc($res);
	//die($datList['campos']);
	$campos=explode('|', $datList['campos']);
	
	//buscamos los permisos
	
	$sql="SELECT id_menu FROM sys_menus WHERE tabla_relacionada = '".$datList['tabla']."' AND no_tabla='".$datList['no_tabla']."'";
	
	//die($sql);
	$res=mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
	 
	$row=mysql_fetch_row($res);



	/////////////////////////////////////////////////////modiicacion Oscar 22.03.2018 por cambio de perfiles 
	$sql="SELECT
	      nuevo,
	      modificar,
	      eliminar,
	      imprimir,
	      generar,
	      prf.admin,
		  u.autorizar_req
	      FROM sys_permisos p
	      JOIN sys_users_perfiles prf ON p.id_perfil=prf.id_perfil 
	      JOIN sys_users u
	      WHERE p.id_menu=".$row[0]."
	      AND prf.id_perfil=$perfil_usuario
	      AND u.id_usuario=$user_id";
	     // die($sql);
	      /*JOIN sys_users u ON p.id_usuario = u.id_usuario
	      WHERE p.id_menu=".$row[0]."
	      AND p.id_usuario=".$user_id;*/
	
	$res=mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());	
	$num=mysql_num_rows($res); 
	
	$row=mysql_fetch_row($res);
	
	$mostrar_nuevo=$row[0];
	$mostrar_mod=$row[1];
	$mostrar_eli=$row[2];
	$mostrar_imp=$row[3];
	$mostrar_gen=$row[4];
    $es_admin=$row[5];
	$aut_re=$row[6];
	
	//editamos la consulta 
	$consulta=$datList['consulta'];
	//die($datList['ver']);
	if($datList['ver'] == '1')
		$consulta=str_replace("FROM ".$datList['tabla'], ", 'ver' FROM ".$datList['tabla'], $consulta);
	if($datList['modificar'] == '1' && $mostrar_mod == '1')
		$consulta=str_replace("FROM ".$datList['tabla'], ", 'modificar' FROM ".$datList['tabla'], $consulta);
	if($datList['eliminar'] == '1' && $mostrar_eli == '1')
		$consulta=str_replace("FROM ".$datList['tabla'], ", 'eliminar' FROM ".$datList['tabla'], $consulta);
	if(($datList['tabla'] == 'ec_ordenes_compraNOVA' || ($datList['tabla'] == 'ec_pedidos' )
	   || $datList['tabla'] == 'ec_movimiento_almacenNOVA' || ($datList['tabla'] == 'ec_pedidos' && $datList['no_tabla'] == 1))//&& $datList['no_tabla'] == 0
	  && $mostrar_imp == '1')
		$consulta=str_replace("FROM ".$datList['tabla'], ", 'imp' FROM ".$datList['tabla'], $consulta);
	if($datList['tabla'] == 'ec_ordenes_compra' && $datList['no_tabla'] == '0' && $aut_re == 1)
		$consulta=str_replace("FROM ".$datList['tabla'], ", 'aut' FROM ".$datList['tabla'], $consulta);
	if($datList['tabla'] == 'ec_pedidos' && $datList['no_tabla'] == 0 && $aut_pedidos == 1)
		$consulta=str_replace("FROM ".$datList['tabla'], ", 'aut' FROM ".$datList['tabla'], $consulta);
	if($datList['tabla'] == 'ec_pedidosNOVER' && $datList['no_tabla'] == 1)
	{
		$consulta=str_replace("FROM ".$datList['tabla'], ", 'fac' FROM ".$datList['tabla'], $consulta);
	}
	
	if($datList['tabla'] == 'ec_transferencias' && $datList['no_tabla'] == 0 )
	{
		$consulta=str_replace("FROM ".$datList['tabla'], ", 'aut' FROM ".$datList['tabla'], $consulta);
		$consulta=str_replace("FROM ".$datList['tabla'], ", 'imp','imp', 'imp' FROM ".$datList['tabla'], $consulta);
	}
/*implementacion Oscar 2023 para el listado de  resoluciones, se modifica 07.03.2023 para incluir transferencias rapidas */
	if( $datList['tabla'] == 'ec_transferencias' && ( $datList['no_tabla'] == 12 || $datList['no_tabla'] == 6 ) ){
		$consulta=str_replace("FROM ".$datList['tabla'], ", 'imp' FROM ".$datList['tabla'], $consulta);
	}
/*fin de cambio Oscar 2023*/

	if($datList['tabla'] == 'ec_transferencias' && $datList['no_tabla'] == 6)
	{
		//$consulta=str_replace("FROM ".$datList['tabla'], ", 'aut' FROM ".$datList['tabla'], $consulta);
		$consulta=str_replace("FROM ".$datList['tabla'], " , 'imp' FROM ".$datList['tabla'], $consulta);
	}
	
	if($datList['tabla'] == 'ec_transferencias' && $datList['no_tabla'] == 1)
	{
		$consulta=str_replace("FROM ".$datList['tabla'], ", 'sal' FROM ".$datList['tabla'], $consulta);
	}
	
	if($datList['tabla'] == 'ec_transferencias' && $datList['no_tabla'] == 2)
	{
		$consulta=str_replace("FROM ".$datList['tabla'], ", 'rec' FROM ".$datList['tabla'], $consulta);
	}
	
	if($datList['tabla'] == 'ec_transferencias' && $datList['no_tabla'] == 3)
	{
		$consulta=str_replace("FROM ".$datList['tabla'], ", 'ver' FROM ".$datList['tabla'], $consulta);
	}
	
	if($datList['tabla'] == 'ec_transferencias' && $datList['no_tabla'] == 4)//resolución de TRansferencias
	{
		$consulta=str_replace("FROM ".$datList['tabla'], ", 'res' FROM ".$datList['tabla'], $consulta);
	}
	
	
	if($datList['tabla'] == 'ec_devolucion_transferencia' && $datList['no_tabla'] == 1)
	{
		$consulta=str_replace("FROM ".$datList['tabla'], ", 'res' FROM ".$datList['tabla'], $consulta);
	}
/*Implementación Oscar 21.02.2018 para insertar el campo de impresión de resolución de Transferencia*/
	if($datList['tabla'] == 'ec_transferencias' && $datList['no_tabla'] == 5)
	{
		$consulta=str_replace("FROM ".$datList['tabla'], ", 'imp' FROM ".$datList['tabla'], $consulta);
	}
/*Fin de cambio Oscar 21.02.02019*/

/*Implementación Oscar 03.03.2018 para Reanudar proceso de devolución*/
	if($datList['tabla'] == 'ec_devolucion' && $datList['no_tabla'] == 1)
	{
		$consulta=str_replace("FROM ".$datList['tabla'], ", 'cont' FROM ".$datList['tabla'], $consulta);
	}
/*Fin de cambio Oscar 21.02.02019*/

/*Implementación Oscar 25.03.2018 para mandar producto a exclusión de Transferencias*/
//die($datList['no_tabla']);
	if($datList['tabla'] == 'ec_transferencia_raciones' && $datList['no_tabla'] == 0)
	{
		$consulta=str_replace("FROM ec_productos p", ", 'imp' FROM ec_productos p", $consulta);
	}
/*Fin de cambio Oscar 25.03.2019*/		

/*implementacion Oscar 19.08.2019 para el botón de impresión de credencial de usuario*/
	if($datList['tabla']=='sys_users' && $datList['no_tabla']==0){
		$consulta=str_replace(" FROM sys_users", ", 'imp' FROM sys_users", $consulta);
	}
/*Fin de cambio Oscar 19.08.2019*/

/*implementacion Oscar 28.08.2019 para el botón de impresión de credencial de usuario*/
	if($datList['tabla']=='ec_ordenes_compra' && $datList['no_tabla']==1){
		$consulta=str_replace(" FROM ec_ordenes_compra", ", 'imp' FROM ec_ordenes_compra", $consulta);
	}
/*Fin de cambio Oscar 19.08.2019*/


/*implementacion Oscar 28.08.2019 para el botón de impresión de credencial de usuario*/
	if($datList['tabla']=='ec_transferencias' && $datList['no_tabla']==6){
		$consulta=str_replace(" FROM ec_transferencias", ", 'imp', 'imp' FROM ec_transferencias", $consulta);
	}
	/*if($datList['tabla']=='ec_transferencias' && $datList['no_tabla']==6){
		$consulta=str_replace(" FROM ec_transferencias", ", 'imp' FROM ec_transferencias", $consulta);
	}*/
/*Fin de cambio Oscar 19.08.2019*/


/*Implementación Oscar 2023/09/23 para el boton de reimprimir desde el listado de cola de impresion*/
	if($datList['tabla']=='sys_archivos_descarga' && $datList['no_tabla']==0){
		//die( 'here' );
		$consulta=str_replace("FROM sys_archivos_descarga", ", 'imp' FROM sys_archivos_descarga", $consulta);
	}
/*Fin de cambio Oscar 2023/09/23*/

//implementacion Oscar 2023 para no cargar datos del listado de vizualizacion de racion
	if( $datList['tabla']=='sys_sucursales_producto' && $valor == '' ){
		//die( 'here' );
		$consulta.=" WHERE 1=2";		
	}else{
		$consulta.=" WHERE 1=1";		

	}
	//die($consulta);
	
	$datList['condicion']=str_replace('$SUCURSAL', $user_sucursal, $datList['condicion']);
	
	
	$consulta.=" ".$datList['condicion'];

		
	if($valor != '')
	{
//implementación de Oscar 15/02/2017
	//fragmentamos busqueda
		$consulta.=" AND (";//abrimos coincidencias 
		$palabras=explode(" ",$valor);
		for($i=0;$i<sizeof($campos);$i++){
			if($i>0 && $campos[$i]!=''){
				$consulta.=" OR ";
			}
			if($campos[$i]==$datList["consulta_buscador"]){
			for($j=0;$j<sizeof($palabras);$j++){
				if($j==0){
				$consulta.="(";
				}else{
					if($palabras[$j]!=''){
						$consulta.=" AND ";
					}
				}
				if($palabras[$j]!=''){
					$consulta.=$campos[$i]." LIKE '%".$palabras[$j]."%'";
				}	
				if($j==sizeof($palabras)-1){
					$consulta.=")";
				}
			}
			}else{
				if($campos[$i]!='')
					$consulta.="(".$campos[$i]." like '%".$valor."%')";
			}
		}
	
		$consulta.=")";//cerramos coincidencias
		/*die("consulta".substr($consulta, 650));
GROUP BY ocr.id_oc_recepcion*/
	}
	if($id_listado==86){
		$consulta=str_replace("\nGROUP BY cc.id_caja_cuenta", "", $consulta);
		//$consulta.="GROUP BY cc.id_caja_cuenta";
	}

	//die($sql);

		/*$consulta.=" AND $campo ";
		if($operador == 'contiene')
			$consulta.=" LIKE '%$valor%'";
		if($operador == 'empieza')
			$consulta.=" LIKE '$valor%'";
		if($operador == '=')
			$consulta.=" = '$valor'";
		if($operador == '!=')
			$consulta.=" <> '$valor'";
		if($operador == '>')
			$consulta.=" > '$valor'";
		if($operador == '<')
			$consulta.=" < '$valor'";
		if($operador == '>=')
			$consulta.=" >= '$valor'";
		if($operador == '<=')
			$consulta.=" <= '$valor'";						
		*/	
	
	
	
	if($datList['tabla'] == 'eq_cotizador' && $es_admin == '0')
		$consulta.=" AND id_usuario=$user_id";
			
/*implementación de Oscar para listado de bitácora de sincronización de acuerdo al perfil del usuario 13.07.2018*/
	if($datList['tabla']=='sys_modulos_sincronizacion'){
	//	die('kjbsag');
		$consulta=str_replace('perfil_del_usuario',$perfil_usuario,$consulta);
	}
/*fin de cambio*/

/*Implementación Oscar 27.07.2018 para agrupar las notas de venta*/
/**/
	//ordenacion	
	if(isset($orderGRC)){
//die($id_listado.'---'. $orderGRC);
	/*implementación Oscar 29.08.2018 para ordenar desc en listados al cargar*/
		if($orderGRC=='ec_ordenes_compra.id_orden_compra'){
			$sentidoOr=" DESC";
		}
		if($orderGRC=='ocr.id_oc_recepcion'){
			$sentidoOr=" DESC";
		}
	/*fin de cambio 29.08.2018*/

	/*implementación Oscar 03.09.2018 para ordenar desc en listado de transferencias al cargar*/
		if($orderGRC=='t.id_transferencia' && ($datList['no_tabla']==0 || $datList['no_tabla']==2 || $datList['no_tabla']==4) ){
			
			$orderGRC="CONCAT(t.fecha,' ',t.hora)";/*si se desea ordenar por id de transferencia se tiene que modificar esta condición*/
			$sentidoOr=" DESC";
		}

		if( $orderGRC=='t.id_transferencia' && $datList['no_tabla']==6 ){
			
			$orderGRC="t.id_transferencia";/*si se desea ordenar por id de transferencia se tiene que modificar esta condición*/
			$sentidoOr=" DESC";
		}
	/*Fin de cambio Oscar 02.11.2018*/

	/*implementacion Oscar 2018.11.02 para ordena las notas de venta de manera descendente al cargar*/
		//die($datList['tabla'].'|'.$orderGRC);
		if($datList['tabla']=='ec_pedidos' && $orderGRC=='p.id_pedido'){
			$sentidoOr=" DESC";
			//die('jshke');
		}
	/**/

	/*implementacion Oscar 2019.02.09 para ordena las notas de venta de manera descendente al cargar*/
		//die($datList['tabla'].'|'.$orderGRC);
		if($datList['tabla']=='ec_gastos' && $orderGRC=='c.id_gastos'){
			$sentidoOr=" DESC";
			//die('jshke');
		}
	/**/

	/*implementacion Oscar 2019.02.21 para ordena las resoluciones de transferencias de manera descendente al cargar*/
		//die($datList['tabla'].'|'.$orderGRC);
		if($datList['tabla']=='ec_transferencias' && $datList['no_tabla']=='5' && $orderGRC=='t.id_transferencia'){
			$sentidoOr=" DESC";
		}
	/*Fin de cambio Oscar 2019.02.21*/

	/*implementacion Oscar 03.03.2019 para ordena las devoluciones pendientes de manera descendente al cargar*/
		//die($datList['tabla'].'|'.$orderGRC);
		if($datList['tabla']=='ec_devolucion' && $datList['no_tabla']=='1' && $orderGRC=='c.id_devolucion'){
			$sentidoOr=" DESC";
		}
	/*Fin de cambio Oscar 03.03.2019*/
	/*implementacion Oscar 03.03.2019 para ordena las devoluciones pendientes de manera descendente al cargar*/
		if($datList['tabla']=='ec_bitacora_movimiento_caja' && $datList['no_tabla']=='0' && $orderGRC=='bm.id_bitacora_movimiento'){
			$sentidoOr=" DESC";
		}
	/*Fin de cambio Oscar 03.03.2019*/

	/*implementacion Oscar 2019.10.15 para ordena los registros de nomina de manera descendente al cargar*/
		if($datList['tabla']=='ec_registro_nomina' && ($orderGRC=='id_registro_nomina' || $orderGRC=='fecha')){
			$sentidoOr=" DESC";
		}
	/*Fin de cambio Oscar 2019.10.15*/

	/*implementacion Oscar 2019.10.24 para ordena los registros de ventas de manera descendente al cargar*/
		if($datList['tabla']=='ec_pedidos' && $datList['no_tabla']=='1' && $orderGRC=='p.id_pedido'){
			$sentidoOr=" DESC";
		}
	/*Fin de cambio Oscar 2019.10.24*/

	/*implementacion Oscar 2019.10.24 para ordena los registros de cortes de cajas de manera descendente al cargar*/
		if($datList['tabla']=='ec_sesion_caja' && $datList['no_tabla']=='0' && $orderGRC=='sc.id_sesion_caja'){
			$sentidoOr=" DESC";
		}
	/*Fin de cambio Oscar 2019.10.24*/

	/*implementacion Oscar 2023 para ordena los registros de bloques de validacion manera descendente al cargar*/
		if( ( $datList['tabla']=='ec_bloques_transferencias_validacion' || $datList['tabla']=='ec_bloques_transferencias_recepcion' )
			&& $datList['no_tabla']=='1' && 
			( $orderGRC=='id_bloque_transferencia_validacion' || $orderGRC=='id_bloque_transferencia_recepcion' ) ){
			$sentidoOr=" DESC";
		}
	/*Fin de cambio Oscar 2023*/
	/*implementacion Oscar 2023 para ordena los registros de bloques de validacion manera descendente al cargar*/
		if( ( $datList['tabla']=='ec_devolucion' && $datList['no_tabla']== '0' ) && $orderGRC=='c.id_devolucion' ){
			$sentidoOr=" DESC";
		}
	/*Fin de cambio Oscar 2023*/
	/*implementacion Oscar 2023 para ordena los registros de movimientos de almacen*///die( 'here : ' . $orderGRC  );
		if( $datList['tabla']=='ec_movimiento_almacen' && ( $orderGRC == 'm.id_movimiento_almacen' || $orderGRC == 'ma.observaciones' ) ){
			if( $orderGRC == 'ma.observaciones' ){
				$orderGRC == 'ma.id_movimiento_almacen';
			}
			$sentidoOr=" DESC";
		}
	/*Fin de cambio Oscar 2023*/
	/*implementacion Oscar 2023 para ordena las transferencias de resolucion Descendente por default*/;
		if( $datList['tabla']=='ec_transferencias' && $datList['no_tabla']=='9' && $orderGRC == 't.id_transferencia' ){
			$sentidoOr=" DESC";
		}
	/*Fin de cambio Oscar 2023*/
	//	die('here'.$orderGRC);

		$consulta.=" ORDER BY ".$orderGRC." $sentidoOr";

	}else{
		$consulta.=" ORDER BY ".$campos[0]." DESC";
	}
	
	
	//die($consulta);
	
	
	$consulta=str_replace('$escuela', $user_escuela, $consulta);
	$consulta=str_replace('$PERFIL', $perfil_usuario, $consulta);/*implemntacion Oscar 30.10.2019 para reemplazar la variable del perfil*/

	//Ponemos el inicio y fin que nos marca el grid
	if(isset($ini) && isset($fin))
	{
		//Conseguimos el número de datos real
		$resultado=mysql_query($consulta) or die("Consulta:".mysql_error()."\n$consulta\n\nDescripcion:\n");
		$numtotal=mysql_num_rows($resultado);
		
		//Añadimos el limit para el paginador
		$consulta.=" LIMIT $ini, $fin";
	}
	
	
/*implementacion Oscar 11.06.2019 para mandar el id del movimiento para cargar la bitácora de cambios*/
	$consulta=str_replace('$LLAVE', $id, $consulta);

/*implementacion Oscar 01.11.2019 para que funcionen los filtros por sucursal y tipo de conrte de caja*/
	if(isset($fltros_adic) && $fltros_adic!=''){
		$consulta=str_replace('WHERE 1=1', 'WHERE 1=1 '.$fltros_adic, $consulta);
	}
/*fin de cambio Oscar 01.11.2019*/

/*Fin de cambio Oscar 11.06.2019*/
	/*$file = fopen("archivo.txt", "w");
	fwrite($file, $consulta);
	fclose($file);
	fclose($file);*/

//	die($consulta);	
	//Buscamos los datos de la consulta final
	$res=mysql_query($consulta) or die("Error en:".mysql_error()."\n$consulta\n\nDescripcion:\n");
	
	$num=mysql_num_rows($res);		
	
	echo "exito";
	for($i=0;$i<$num;$i++)
	{
		$row=mysql_fetch_row($res);
		echo "|";
		for($j=0;$j<sizeof($row);$j++)
		{	
			if($j > 0)
				echo "~";
			echo $row[$j];
		}	
	}
	
	
	//Enviamos en el ultimo dato los datos del listado, numero de datos y datos que se muestran
	if(isset($ini) && isset($fin))
		echo "|$numtotal~$num";
	

?>