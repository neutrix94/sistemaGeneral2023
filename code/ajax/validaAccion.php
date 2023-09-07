<?php


	include("../../conectMin.php");
	extract($_GET);
	if(isset($_POST['fl']) && $_POST['fl']=='historico_precio_compra'){	
		$sql="DELETE FROM ec_historico_precio_compra";
		$eje=mysql_query($sql)or die("Error al eliminar para reemplazar el historico de precios de compra!!!");
		$sql="INSERT INTO ec_historico_precio_compra 
				SELECT
				null,
				id_productos,
				precio_compra,
				DATE_FORMAT(now(),'%Y')
			FROM ec_productos WHERE id_productos>0";
		$eje=mysql_query($sql)or die("Error al reemplazar el historico de precios de compra!!!".mysql_error());
		die("Histórico de precios de compra actualizado exitodsamente!!!");
	}
/**/
	if(isset($flag) && $flag=='excluye'){
		$sql="INSERT INTO ec_exclusiones_transferencia VALUES(null,{$id},{$user_sucursal},'{$observaciones}',now(),now(),1)";
		$eje=mysql_query($sql)or die(mysql_error());/*
		$sql="UPDATE ec_transferencia_raciones SET observaciones=1 WHERE id_producto={$id}";
		$eje=mysql_query($sql)or die(mysql_error());*/
		die('ok|El producto fue excluido de las Transferencias exitosamente!!!');
	}
/**/	
	
/**/
	if(isset($flag) && $flag=='devolucion'){
	//consultamos el tipo de sistema
		$sql="SELECT id_sucursal FROM sys_sucursales WHERE acceso=1";
		$eje=mysql_query($sql)or die("Error al consultar la sucursal de logueo!!!");
		$suc_sys=mysql_fetch_row($eje);
	//consultamos la sucursal donde se realizó la devolución
		$sql="SELECT tipo_sistema, IF(tipo_sistema=-1,'linea','local') FROM ec_devolucion WHERE id_devolucion=$id";
		$eje=mysql_query($sql)or die("Error al consultar la sucursal de logueo!!!");
		$suc_dev=mysql_fetch_row($eje);
	//comparamos sucursales
		if($suc_sys[0]!=$suc_dev[0]){
			die("Esta devolución se comenzó en el sistema ".$suc_dev[1]." y deberá de ser terminada en el sistema ");
		} 

		$sql="SELECT IF(status>0,'Esta devolución ya fue Terminada',observaciones) FROM ec_devolucion WHERE id_devolucion=".$id;
		$eje=mysql_query($sql)or die("Error al consultar url de reanudación de Devolución!!!\n\n".mysql_error()."\n\n".$sql);
		$r=mysql_fetch_row($eje);
		if($r[0]=='Esta nota ya fue Terminada'){
			die($r[0]);
		}
		die('ok|'.$r[0]);
	}
/**/
	//die("$tabla - $no_tabla");
	//ZWNfb3JkZW5lc19jb21wcmE= - MA==
//implementación de Oscar para cancelar Transferencias 27.05.2018
	if(isset($flag)&&$flag==1){
		//die($id);
		$sql="SELECT id_estado FROM ec_transferencias WHERE id_transferencia=$id";
		$eje=mysql_query($sql)or die("Error al consultar estado de la transferencia!!!");
		$r=mysql_fetch_row($eje);
		//die($r[0]);
		if($r[0]>1){
			if($r[0]==5){
				die('ok|Esta transferencia ya fue cancelada!!!');
			}
			die("ok|No se puede cancelar la transferencia porque esta ya fue autorizada!!!");
		}/*
		$sql="UPDATE ec_transferencias SET id_estado=5 WHERE id_transferencia=$id";
		$eje=mysql_query($sql)or die("Error al cancelar la transferencia!!!\n\n".$sql."\n\n".mysql_error());
	//Implementación Oscar 26.02.2019 para eliminar transfeerncias de la BD
		*/
/*implementacion Oscar 2023 para resetear el campo de racion tomada si es el caso*/
		$sql = "SELECT 
					GROUP_CONCAT( DISTINCT(id_producto_or) SEPARATOR ',' ),
					( SELECT id_sucursal_destino FROM ec_transferencias WHERE id_transferencia = {$id} )
				FROM ec_transferencia_productos 
				WHERE id_transferencia = {$id}
				AND es_racionado = 1";
		$eje=mysql_query($sql);
		if(!$eje){
			mysql_query("ROLLBACK");//hacemos ROLLBACK
			die("Error al consultar los detalles de transferencias!!!\n\n".mysql_error()."\n\n".$sql);
		}
		$row = mysql_fetch_row( $eje );
		
/**/
		mysql_query("BEGIN");//marcamos inicio de transacción
		if( $row[0] != '' ){
			$sql = "UPDATE sys_sucursales_producto 
						SET racion_tomada = 0 
					WHERE id_producto IN( {$row[0]} )
					AND id_sucursal IN ( {$row[1]} )";
			$eje=mysql_query($sql);
			if(!$eje){
				mysql_query("ROLLBACK");//hacemos ROLLBACK
				die("Error al resetear los detalles de racion!!!\n\n".mysql_error()."\n\n".$sql);
			}
		}
		$sql="DELETE FROM ec_movimiento_almacen WHERE id_transferencia=$id";
		$eje=mysql_query($sql);
		if(!$eje){
			mysql_query("ROLLBACK");//hacemos ROLLBACK
			die("Error al eliminar los movimientos de almacén de la Transferencia!!!\n\n".mysql_error()."\n\n".$sql);
		}
		$sql="DELETE FROM ec_transferencias WHERE id_transferencia=$id";
		$eje=mysql_query($sql);
		if(!$eje){
			mysql_query("ROLLBACK");//hacemos ROLLBACK
			die("Error al eliminar la Transferencia!!!\n\n".mysql_error()."\n\n".$sql);
		}
		mysql_query("COMMIT");//autorizamos la transacción
		die('ok|Transferencia cancelada exitosamente!!!');
	}
//fin de cambio 27.05.2018
	
	
	$tab=base64_decode($tabla);
/*Implementación Oscar 2021*/
	if ( $tab == 'ec_transferencias' ){
		$sql = "SELECT 
					IF( '{$user_tipo_sistema}' = 'linea', 1, permite_transferencias) 
				FROM sys_sucursales WHERE id_sucursal = '{$sucursal_id}'";
		$eje = mysql_query( $sql ) or die( "Error al consultar permisos de transferencias!!! " . mysql_error() );
		$r = mysql_fetch_row( $eje );
		if ( $r[0] == 0){
			die("No se pueden agregar transferencias localmente, haga su transferencia en el sistema en linea"
				. " o comuniquese con el administrador del sistema.");
		}else{
			die("SI|");
		}

	}
/*Fin de cambio Oscar 2021*/
	
	if(($tab == 'ec_pedidos' || $tab == 'ec_movimiento_almacen' || $tab == 'ec_almacen' || $tab == 'ec_precios' || $tab == 'ec_transferencias' || $tab == 'ec_devolucion_transferencia') && $tipo == 3)
	{
		
		if($tab == 'ec_pedidos')
			$campoL="id_pedido";
		if($tab == 'ec_movimiento_almacen')
			$campoL="id_movimiento_almacen";
		if($tab == 'ec_almacen')
			$campoL="id_almacen";
		if($tab == 'ec_precios')
			$campoL="id_precio";
		if($tab == 'ec_transferencias')
			$campoL="id_transferencia";
		if($tab == 'ec_devolucion_transferencia')
			$campoL="id_devolucion_transferencia";					
		
		$sql="SELECT 1 FROM $tab WHERE $campoL = $id_valor/*AND (ultima_sincronizacion = '0000-00-00 00:00:00' OR ultima_sincronizacion IS NULL)*/";
		$res=mysql_query($sql) or die(mysql_error());
		
		if(mysql_num_rows($res) <= 0)
			die("El dato ya ha sido sincronizado con las demas sucursales.");
	}
	
	if($tab == 'ec_ordenes_compra' && $tipo == 1 && $no_tabla == 'MA==')
	{
		$sql="	SELECT
				id_estatus_oc
				FROM ec_ordenes_compra
				WHERE id_orden_compra = $id_valor";
				
		$res=mysql_query($sql) or die(mysql_error());

		$row=mysql_fetch_row($res);
		
		if($row[0] > 2)
		{
			die("No es posible modificar una requesición ya autorizada");
		}		
	}
	
	
	if($tab == 'ec_movimiento_almacen' && $tipo == 1)
	{
		$sql="	SELECT
				COUNT(1)
				FROM ec_movimiento_almacen
				WHERE id_movimiento_almacen=$id_valor
				AND id_pedido=-1
				AND id_orden_compra=-1
				AND id_maquila=-1
				AND id_transferencia=-1";
				
		$res=mysql_query($sql) or die(mysql_error());

		$row=mysql_fetch_row($res);
		
		if($row[0] <= 0)
		{
			die("No es posible modificar el movimiento de inventario ya que esta ligado a otros registros.");
		}
		
	}
	
	
	
	if($tabla == 'ZWNfb3JkZW5lc19jb21wcmE=' && $no_tabla == 'MA==')
	{
		//die("Nl");
		$sql="SELECT id_estatus_oc FROM ec_ordenes_compra WHERE id_orden_compra=$id_valor";
		$res=mysql_query($sql) or die("Error en:\n$sql\n\nDescripción:".mysql_error());
		
		$row=mysql_fetch_row($res);
		if($row[0] != 1)
			die("No es posible modificar esta requisición");
	}
	
	if($tabla == 'ZWNfb3JkZW5lc19jb21wcmE=' && $no_tabla == 'MQ==')
	{
		//die("Nl");
		$sql="SELECT id_estatus_oc FROM ec_ordenes_compra WHERE id_orden_compra=$id_valor";
		$res=mysql_query($sql) or die("Error en:\n$sql\n\nDescripción:".mysql_error());
		
		$row=mysql_fetch_row($res);
		if($row[0] > 3 && $mod_oc_surtimiento == 0)
			die("No es posible modificar esta Orden de compra, ya que ha comenzado a recibirse");
	}
	
	
	//echo $tabla.', tipo:'.$tipo;
	//die($id_valor."    ".$tipo);
	echo "SI|".base64_encode($id_valor)."|".base64_encode($tipo);

?>