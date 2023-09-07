<?php

	include("../../conectMin.php");
	
	extract($_GET);
	//die($rango_fechas);//rango de fechas para el grid 50 correspondiente a la bitácora de sincronización
	
	//buscamos el periodo activo
	/*$sql="SELECT id_periodo FROM eye_periodo WHERE activo=1";	
	$res=mysql_query($sql);	
	if(!$res)	  
	{
		Muestraerror($smarty, "", "2", mysql_error(), $sql, "contenido.php"); 
	}	
	if(mysql_num_rows($res) > 0)
	{
		$row=mysql_fetch_row($res);		
		$periodo=$row[0];
	}
	*/
	
	//Buscamos el query de datos
	$sql="SELECT query FROM sys_grid WHERE tabla_relacionada='$tabla' AND id_grid=$id_grid";
	$res=mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
	//die( $sql );
	if(mysql_num_rows($res) <= 0)
		die("Error: No se encontro la informacion requerida");
	
	$row=mysql_fetch_row($res);
	$consulta=$row[0];
	
	$consulta=str_replace('$llave', $id, $consulta);
	$consulta=str_replace('$periodo', $periodo, $consulta);
/*implementación de Oscar 13.07.2018 para filtrar notificaciones en grid de bitácora por perfil de usuario*/
	$consulta=str_replace('perfil_del_usuario', $perfil_usuario, $consulta);
/*Fin de cambio*/

/*implementación de Oscar 13.07.2018 para filtrar por sucursal*/
	$consulta=str_replace('$SUCURSAL', $user_sucursal, $consulta);
	//die($consulta);
/*Fin de cambio*/

/*Implementacion Oscar 16.07.2019 para poner como valor defeault el id de usuario desde la consulta del grid*/
	$consulta=str_replace('$USUARIO', $user_id, $consulta);
/*Fin de cambio Oscar 16.07.2019*/

/*implementacion Oscar 2023 para el prefijo de los codigos unicos*/
	if( $id_grid == '48' ){
		$sql_aux = "SELECT prefijo_codigos_unicos AS prefix FROM sys_configuracion_sistema LIMIT 1";
		$stm = mysql_query( $sql_aux ) or die( "Error al consultar el prefijo de los codigos unicos : " . mysql_error() );
		$aux_row = mysql_fetch_assoc( $stm );
		$consulta=str_replace('$TAG_PREFIX', $aux_row['prefix'], $consulta);
	}
/**/

/*Implementación de Oscar 14.08.2018 para rango de fechas en bitácora de sincronización*/
	if($id_grid==50){//si es el grid de bitácora de sincronización
	//separamos las fechas
		$fechas=explode("|",$rango_fechas);
		$condicion_fechas="";
		if($fechas[0]!=null && $fechas[0]!=''){
			$condicion_fechas=" AND (sr.fecha LIKE '%".$fechas[0]."%')";
		}
		if($fechas[1]!=null && $fechas[1]!=''){
			$condicion_fechas=" AND (sr.fecha LIKE '%".$fechas[1]."%')";
		}
		if(($fechas[0]!=null && $fechas[0]!='')&&($fechas[1]!=null && $fechas[1]!='')){
			$condicion_fechas=" AND(sr.fecha BETWEEN '".$fechas[0]." 00:00:00' AND '".$fechas[1]." 23:59:59')";
		}
	//concatenamos el filtrado por fecha en la consulta original de la BD
		$consulta.=$condicion_fechas;
		//die($consulta);
	}
/*Fin de cambio*/

/*implementación de Oscar 28.07.2018 para cargar el detalle de recepción de acuerdo al folio de referencia proveedor*
	$sq="SELECT folio_referencia_proveedor FROM ec_oc_recepcion WHERE id_oc_recepcion=$id LIMIT 1";
	$eje=mysql_query($sq)or die("Error al consultar el id de la recepción!!!\n\n".$sq."\n\n".mysql_error());
	$r=mysql_fetch_row($eje);
	$folio_ref=$r[0];
	$consulta=str_replace('$FOLIO_PROVEEDOR', $folio_ref, $consulta);*/
/*fin de cambio*/

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

?>