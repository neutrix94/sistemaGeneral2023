<?php	
	header("Content-Type: text/plain;charset=utf-8");
	include("../../conectMin.php");

	$es_pedido = isset($_GET["pe"]) ? $_GET["pe"] : "0";
	$es_paquete = isset($_GET["pa"]) ? $_GET["pa"] : "0";
	$id_pedido = isset($_GET["idp"]) ? $_GET["idp"] : "0";
	$folio = "0";
	$nitems = $_GET["nitems"];
	$descuento = 0;
	$prefijo = "";
	$es_nuevo_registro = !($id_pedido > 0);
	$tipo_folio = $es_pedido ? "pedido" : "nv";
	$ix_regalo = isset($_GET["reg"]) ? $_GET["reg"] : null;
/*implementación Oscar 01.03.2019 para marcar el tipo de venta*/
	$tipo_venta=$_GET['tv'];
	if($tipo_venta==null||$tipo_venta==''){
		$tipo_venta=0;
	}
/*Fin de cambio Oscar 01.03.2019*/

	try
	{
/*implementación de Oscar 2019 para saber si la sucursal es multicajero*/
    $sql="SELECT 
    		IF(multicajero=1,0,
    			(SELECT 
    				id_cajero 
    			FROM ec_sesion_caja 
    			WHERE id_sucursal=$user_sucursal 
    			AND fecha=DATE_FORMAT(now(),'%Y-%m-%d') 
    			AND hora_fin='00:00:00') 
    		), 
		/*implementacion Oscar 2023 para obtener el id de sesion de caja*/
    		IF(multicajero=1,0,
    			(SELECT 
    				id_sesion_caja 
    			FROM ec_sesion_caja 
    			WHERE id_sucursal=$user_sucursal 
    			AND fecha=DATE_FORMAT(now(),'%Y-%m-%d') 
    			AND hora_fin='00:00:00') 
    		)
    FROM ec_configuracion_sucursal 
    WHERE id_sucursal=$user_sucursal";
    $eje=mysql_query($sql)or die("Error al consultar si la sucursal es multicajero!!!\n".mysql_error());
    $r_c=mysql_fetch_row($eje);
    $id_cajero=$r_c[0];
    $id_sesion_caja=$r_c[1];
//    die($id_cajero);
/*Fin de cambio Oscar 2019*/

/*implementación Oscar 04.06.2019 para validar que haya un logueo de cajero en la sucursal*/
	//sacamos la fecha actual desde mysql
		$sql="SELECT DATE_FORMAT(now(),'%Y-%m-%d')";
		$eje=mysql_query($sql)or die("Error al consultar la fecha actual!!!");
		$fecha_actual=mysql_fetch_row($eje);
	//comprobamos que haya una sesion abierta en el dia actual
		$sql="SELECT count(*) FROM ec_sesion_caja WHERE fecha='$fecha_actual[0]' AND id_sucursal=$user_sucursal AND hora_fin='00:00:00'";
		$eje=mysql_query($sql)or die("Error al verificar que haya sesión de caja abierta!!!\n".mysql_error());
		$r=mysql_fetch_row($eje);
		if($r[0]<1){
			die("sin_sesion_caja");
		}

/*fin de cambio Oscar 04.06.2019*/
		mysql_query("BEGIN");//marcamos inicio de transacción

/*implementación Oscar 03.09.2018 para reemplazar los datos del apartado*/
	$id_ped_apart=$_GET['id_ped_apart'];
	//die('aqui: '.$id_ped_apart);
	if($id_ped_apart!='' && $id_ped_apart!=null){
	/*implementacion Oscar 2023 para saber si un apartado ya fue validado*/
		$sql = "SELECT 
					venta_validada
				FROM ec_pedidos
				WHERE id_pedido = {$id_ped_apart}";
		$exc = mysql_query( $sql ) or die("Error al consultar si el apartado ya fue validado : " . mysql_error() );
		$was_validated = mysql_fetch_row( $exc );

	/**/
	/*implementación Oscar 13.11.2018 para capturar el detalle de la venta antes de que se modifique*
		$sql="SELECT id_producto,cantidad from ec_pedidos_detalle WHERE id_pedido=$id_ped_apart GROUP BY id_pedido_detalle";
		$eje_tmp=mysql_query($sql);
		if(!$eje_tmp){
			die("Error al buscar el detalle de movimiento de almacen antes de modificar la nota de venta!!!".mysql_error());
		}
		$temporales_detalle="";
		/*while($tmp_res=mysql_fetch_row($eje_tmp)){
			$temporales_detalle.=$tmp_res[0]."~".$tmp_res[1]."°";
		}*
/*Fin de cambio Oscar 13.11.2018*/
		$sql = "DELETE FROM ec_movimiento_detalle_proveedor_producto 
			WHERE id_movimiento_almacen_detalle 
			IN( SELECT id_movimiento_almacen_detalle 
				FROM ec_movimiento_detalle 
				WHERE id_pedido_detalle
				IN( SELECT id_pedido_detalle 
					FROM ec_pedidos_detalle 
					WHERE id_pedido = {$id_ped_apart})
			)";
		$eje=mysql_query($sql);
		if(!$eje){
			$error=mysql_error();
			mysql_query("ROLLBACK");
			die("Error al eliminar el detalle de movimientos a nivel proveedor producto!!!\n\n".$sql."\n\n{$error}" );
		}
		$sql = "UPDATE ec_pedidos set venta_validada = '0' WHERE id_pedido = {$id_ped_apart}";
		$eje=mysql_query($sql);
		if(!$eje){
			$error=mysql_error();
			mysql_query("ROLLBACK");
			die("Error al regresar nota de venta  a no validada!!!\n\n".$sql."\n\n{$error}" );
		}
	//
		$sql = "DELETE 
					FROM ec_pedidos_validacion_usuarios 
				WHERE id_pedido_detalle
				IN( SELECT id_pedido_detalle 
					FROM ec_pedidos_detalle 
					WHERE id_pedido = {$id_ped_apart}
				)";
		$eje=mysql_query($sql);
		if(!$eje){
			$error=mysql_error();
			mysql_query("ROLLBACK");
			die("Error al eliminar el detalle de validacion a nivel proveedor producto!!!\n\n".$sql."\n\n{$error}" );
		}

/*Implementacion Oscar 2023 para insertar movimiento inverso de productos tomados de exhibicion durante devolucion de apartado*/
		//include( '../../conexionMysqli.php' );
		if( $was_validated[0] == 1 ){
			$principal_warehouse;
			$exhibition_warehouse;
		//busca si tiene registros de exibicion relacionados
			$sql = "SELECT 
						tepp.id_producto AS product_id, 
						tepp.cantidad AS quantity,
						tepp.id_proveedor_producto AS product_provider_id
					FROM ec_temporal_exhibicion_proveedor_producto tepp 
					LEFT JOIN ec_temporal_exhibicion te 
					ON tepp.id_temporal_exhibicion = te.id_temporal_exhibicion
					WHERE te.id_pedido = {$id_ped_apart}
					GROUP BY tepp.id_temporal_exhibicion_proveedor_producto";
			$prods_stm = mysql_query( $sql ) or die( "Error al consultar registros de exhibicion temporal : " . mysql_error() );
			if( mysql_num_rows( $prods_stm ) <= 0 ){
				//return 'ok';
			}else{
		//busca almacen origen
				$sql = "SELECT
							id_almacen AS warehouse_id
					FROM ec_almacen 
					WHERE id_sucursal = {$user_sucursal}
					AND es_almacen = 1";
				$stm = mysql_query( $sql ) or die( "Error al consultar almacen principal : " . mysql_error() );
				$row = mysql_fetch_assoc( $stm );
				$principal_warehouse = $row['warehouse_id'];
			//busca almacen exhibicion
				$sql = "SELECT
							id_almacen AS warehouse_id
					FROM ec_almacen 
					WHERE id_sucursal = {$user_sucursal}
					AND nombre like '%EXHIBICION%'";
				$stm = mysql_query( $sql ) or die( "Error al consultar almacen exhibicion : " . mysql_error() );
				$row = mysql_fetch_assoc( $stm );
				$exhibition_warehouse = $row['warehouse_id'];
				while ( $prods_row = mysql_fetch_assoc( $prods_stm ) ){
					for( $i = 0; $i <= 1; $i++ ){
						$movement_type = 5;//entrada
						$warehouse_id = $exhibition_warehouse;
						if( $i == 0 ){
							$movement_type = 6;//salida
							$warehouse_id = $principal_warehouse;
						}
						if( $i == 0 ){
						//consulta si el producto es maquilado
							$sql = "SELECT
										p.id_productos AS product_id,
										IF( pd.cantidad IS NULL, 0, pd.cantidad) AS quantity,
										IF( pd.id_producto_ordigen IS NULL, -1, pd.id_producto_ordigen ) AS origin_product_id,
										IF( pd.id_producto_ordigen IS NULL, 
											{$prods_row['product_provider_id']},  
											(SELECT 
												ipp.id_proveedor_producto
											FROM ec_inventario_proveedor_producto ipp
											WHERE ipp.id_producto = pd.id_producto_ordigen
											AND ipp.id_almacen = {$principal_warehouse}
											ORDER BY ipp.inventario DESC 
											LIMIT 1 )
										) AS product_provider_id
									FROM ec_productos p
									LEFT JOIN ec_productos_detalle pd
									ON p.id_productos = pd.id_producto
									WHERE p.id_productos = {$prods_row['product_id']}";
							$maquile_stm = mysql_query( $sql ) or die( "Error al consulttar si el producto es maquilado : " . mysql_error() );
							$maquile_row = mysql_fetch_assoc( $maquile_stm );
							if( $maquile_row['origin_product_id'] != -1 ){
								$prods_row['product_id'] = $maquile_row['origin_product_id'];
								$prods_row['quantity'] = ( $prods_row['quantity'] * $maquile_row['quantity'] );
								$prods_row['product_provider_id'] = $maquile_row['product_provider_id'];
							}
						}
					//insertamos cabecera
						$sql="INSERT INTO ec_movimiento_almacen ( /*1*/id_tipo_movimiento, /*2*/id_usuario, 
							/*3*/id_sucursal, /*4*/fecha, /*5*/hora, /*6*/observaciones, /*7*/id_almacen, /*8*/id_pedido ) 
						VALUES ( /*1*/{$movement_type},/*2*/{$user_id},/*3*/{$user_sucursal},/*4*/now(),/*5*/now(), 
							/*6*/'{$obs}', /*7*/{$warehouse_id}, /*8*/-1 )";
						$stm = mysql_query( $sql ) or die( "Error al insertar la cabecera del movimiento almacen : " . mysql_error() );			
						$sql = "SELECT LAST_INSERT_ID()";
						$stm_2 = mysql_query( $sql ) or die( "Error al consultar id de cabecera de movimiento almacen : " . mysql_error() );
						$header_id = mysql_fetch_row( $stm_2 );
						$header_id = $header_id[0];
					//inserta detalles	
						$sql = "INSERT INTO ec_movimiento_detalle( id_movimiento, id_producto, cantidad, cantidad_surtida, 
						id_proveedor_producto, id_pedido_detalle ) VALUES ( {$header_id}, {$prods_row['product_id']}, {$prods_row['quantity']},
						{$prods_row['quantity']}, {$prods_row['product_provider_id']}, /*8*/-1 )";
						$stm_2 = mysql_query( $sql ) or die( "Error al insertar detalles de movimiento almacen por exhibicion : " . mysql_error() );
					}
				}
			}
		}
	/*implementacion Oscar 2023 para actualizar los detalle de movimentos de almacen antes de eliminar los detalles de venta*/
		$sql = "UPDATE ec_movimiento_detalle md
				LEFT JOIN ec_movimiento_almacen ma
				ON md.id_movimiento = ma.id_movimiento_almacen
				SET md.id_pedido_detalle = -1
				WHERE ma.id_pedido = {$id_ped_apart}";
		$stm_update = mysql_query( $sql ) or die( "Error al actualizar detalles de movimiento de almacen a -1 antes de eliminar los detalles de venta : " . mysql_error() );
		
	/*fin de cambio Oscar 2023*/

		$sql="DELETE FROM ec_pedidos_detalle WHERE id_pedido=$id_ped_apart";
		$eje=mysql_query($sql);
		if(!$eje){
			$error=mysql_error();
			mysql_query("ROLLBACK");
			die("Error al eliminar el detalle del apartado!!!\n\n".$sql."\n\n{$error}" );
		}

	$apartado_subtotal=0;//declaramos variable que sumará los totales
	//reinsertamos el detalle
		//die($nitems);
		for($ix=0; $ix<$nitems; ++$ix){
		
		/*implementacion Oscar 24.10.2019 para poder asignar id_precio al producto 18000 (Ultimas piezas)*/
//if($_GET["idp{$ix}"]=='1808'){
			if( $_GET["id_precio{$ix}"] == '' || $_GET["id_precio{$ix}"] == 'undefined' ){
				$sql="SELECT id_precio FROM sys_sucursales WHERE id_sucursal=$user_sucursal";
				$eje_prec_aux=mysql_query($sql);
				$r_prec_aux=mysql_fetch_row($eje_prec_aux);
				if( $r_prec_aux[0] == null || $r_prec_aux[0] == '' ){
					$r_prec_aux[0] = 1;
				}
				$_GET["id_precio{$ix}"]=$r_prec_aux[0];
			}

			$sql_tmp="SELECT p.es_externo FROM ec_precios p WHERE p.id_precio='{$_GET["id_precio{$ix}"]}'";
			$eje=mysql_query($sql_tmp);
			if(!$eje){
				die("Error al consultar si es producto externo");
			}
			$row=mysql_fetch_row($eje);
			if( $row[0] == null || $row[0] == '' ){
				$row[0] = 0;
			}
		/*Fin de cambio Oscar 24.10.2019*/
			$cs = "	INSERT INTO ec_pedidos_detalle
					SET
					/*0*/id_pedido_detalle=NULL,
					/*1*/id_pedido = '{$id_ped_apart}',
					/*2*/id_producto = '{$_GET["idp{$ix}"]}',
					/*3*/cantidad = '{$_GET["can{$ix}"]}',
					/*4*/precio = '{$_GET["pre{$ix}"]}',
					/*5*/monto = '{$_GET["mon{$ix}"]}',
					/*6*/iva = '0',
					/*7*/ieps = '0', 
					/*8*/cantidad_surtida = '0',
					/*9*/descuento=0,
					/*10*/modificado=0,
					/*11*/es_externo=$row[0],/*Campo implementado por Oscar 07.08.2018*/
					/*12*/id_precio='{$_GET["id_precio{$ix}"]}'";/*Campo implementado por Oscar 07.08.2018*/
				//echo $cs;
			if (!mysql_query($cs)){
				$error=mysql_error();
				throw new Exception("Imposible reemplazar el detalle del apartado.\n\n$cs\n\n".$error);
			}
            $apartado_subtotal+=$_GET["mon{$ix}"];	
        //echo $apartado_subtotal.'|';
		}//fin de for $ix
	//calculamos el descuento
		$totalPed=$_GET['totalPed'];
		$desc_total=$apartado_subtotal-$totalPed;
		//die($desc_total."|".$apartado_subtotal."|".$totalPed);
	//actualizamos la cabecera del pedido
		$sql="UPDATE ec_pedidos SET subtotal='$apartado_subtotal',total='$totalPed',descuento='$desc_total',pagado=0,ultima_modificacion=NOW()
				WHERE id_pedido='$id_ped_apart'";
		$eje=mysql_query($sql);
		if(!$eje){
			$error=mysql_error();
			mysql_query("ROLLBACK");
			die("Error al actualizar la cabecera del pedido!!!\n\n".$sql."\n\n".$error);
		}
		$monto_abonado=$_GET['abonado'];
		if($monto_abonado>0&&$monto_abonado!=null&&$monto_abonado!=''){
		//die($monto_abonado);
			$sql="SELECT
					(aux.interno/aux.total),
					(aux.externo/aux.total),
					aux.total
				FROM(
    				SELECT 
						SUM(monto-descuento) as total,
				    	SUM(IF(es_externo=1,monto-descuento,0)) as externo,
    					SUM(IF(es_externo=0,monto-descuento,0))as interno 
					FROM ec_pedidos_detalle WHERE id_pedido=$id_ped_apart
   				)aux";
			$eje=mysql_query($sql);
			if(!$eje){
				$error=mysql_error();
				mysql_query("ROLLBACK");
				die("Error al sacar porcentajes externos e interno!!!\n\n".$sql."\n\n".$error);
			}
			$dats=mysql_fetch_row($eje);
		//calculamos los montos
			$monto_pgo_int=round($monto_abonado*$dats[0],2);
			$monto_pgo_ext=round($monto_abonado*$dats[1],2);

/*si el abono es mayor al nuevo ticket*/	
			if($dats[2]<$monto_abonado){
			//consultamos el monto
				$sql="SELECT 
						SUM(IF(pd.id_pedido_detalle IS NOT NULL AND pd.es_externo=1,(pd.monto-pd.descuento),0))as externos,
						SUM(IF(pd.id_pedido_detalle IS NOT NULL AND pd.es_externo=0,(pd.monto-pd.descuento),0))as internos,
						(p.descuento/p.subtotal)as porcDesc
					FROM ec_pedidos_detalle pd
					LEFT JOIN ec_pedidos p ON pd.id_pedido=p.id_pedido
					WHERE pd.id_pedido=$id_ped_apart";
				$eje=mysql_query($sql);
				if(!$eje){
					$error=mysql_error();
					mysql_query("ROLLBACK");
					die("Error al consultar totales externo e interno!!!\n\n".$sql."\n\n".$error);
				}
				$dats=mysql_fetch_row($eje);
			//reasignamos los montos
				$monto_pgo_ext=round($dats[0]-($dats[0]*$dats[2]),2);
				$monto_pgo_int=round($dats[1]-($dats[1]*$dats[2]),2);
			}
/*termina condicion*/

		//reinsertamos el pago interno
			if($monto_pgo_int>0){
					$sql="INSERT INTO ec_pedido_pagos ( id_pedido_pago, id_pedido, id_tipo_pago, fecha, hora, monto,
					referencia, id_moneda, tipo_cambio, id_nota_credito, id_cxc, exportado, es_externo, id_cajero, id_sesion_caja ) 
					VALUES(null,$id_ped_apart,1,now(),now(),$monto_pgo_int,'',1,1,-1,-1,0,0, {$id_cajero}, {$id_sesion_caja} )";
					$eje=mysql_query($sql);
					if(!$eje){
						$error=mysql_error();
						mysql_query("ROLLBACK");
						die("Error al re insertar pago interno!!!\n\n".$sql."\n\n".$error);
					}
				}

		//reinsertamos el pago externo
			if($monto_pgo_ext>0){
				$sql="INSERT INTO ec_pedido_pagos ( id_pedido_pago, id_pedido, id_tipo_pago, fecha, hora, monto,
					referencia, id_moneda, tipo_cambio, id_nota_credito, id_cxc, exportado, es_externo, id_cajero, id_sesion_caja )  
					VALUES(null,$id_ped_apart,1,now(),now(),$monto_pgo_ext,'',1,1,-1,-1,0,1, {$id_cajero}, {$id_sesion_caja} )";
				$eje=mysql_query($sql);
				if(!$eje){
					$error=mysql_error();
					mysql_query("ROLLBACK");
					die("Error al re-insertar pago externo!!!\n\n".$sql."\n\n".$error);
				}
			}
	//actualizamos el status de la cabecera 
			$sql="UPDATE ec_pedidos p SET pagado=IF((p.total-(SELECT IF(SUM(monto) IS NULL, 0, SUM(monto)) 
				FROM ec_pedido_pagos WHERE id_pedido=p.id_pedido AND (referencia='' OR referencia=null))) <= 0, 1, 0), 
		/*implementación Oscar 19.11.2018 para dejar como modificadad la cabecera del pedido*/
				modificado=1
		/*fin de cambio Oscar 19.11.2018*/
				WHERE p.id_pedido=$id_ped_apart";
			$eje=mysql_query($sql);
			if(!$eje){
				$error=mysql_error();
				mysql_query("ROLLBACK");
				die("Error al actualizar el estatus de la nota de venta!!!\n\n".$sql."\n\n".$error);
			}
		}//fin de if $monto_abonado!=0
	/*Implementación Oscar 13.11.2018 para hacer movimientos de almacen correspondientes a la nueva nota de vanta*/
		$sql="SELECT 
				SUM(IF(es_externo=0,1,0)) as internos,
				SUM(IF(es_externo=1,1,0)) as externos
			FROM ec_pedidos_detalle WHERE id_pedido=$id_ped_apart";
		$eje=mysql_query($sql)or die("Error al contar los detalles del pedido modificado!!!\n\n".mysql_error());
		$num_movs=mysql_fetch_row($eje);
	//sacamos el id de almacen interno
		$sql_1="SELECT id_almacen FROM ec_almacen WHERE id_sucursal=$user_sucursal AND es_almacen=1";
		$eje_1=mysql_query($sql_1)or die("Error al consultar el almacen principal!!!\n\n".mysql_error());
		$ro=mysql_fetch_row($eje_1);
	//insertamos las cabeceras de los movimientos de almacen
		$id_mov_int='';
		$id_mov_ext='';
		for($i=0;$i<=1;$i++){
			if($num_movs[$i]>0){
				$sql="INSERT INTO ec_movimiento_almacen
				(id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, id_orden_compra, lote, id_maquila, id_transferencia, id_almacen)
						SELECT 2,$user_id,id_sucursal, now(),now(),'Movimiento por modificación en apartado',{$id_ped_apart}, -1, '', -1, -1, IF($i=1,almacen_externo,$ro[0])
						FROM sys_sucursales WHERE id_sucursal=$user_sucursal";
				$eje_1=mysql_query($sql)or die("Error al insertar la cabecera del movimiento de almacen!!!".mysql_error());
				if($i==0){//guardamos el id de movimiento interno
					$id_mov_int=mysql_insert_id();
				}else if($i==1){//guardamos el id de movimiento externo
					$id_mov_ext=mysql_insert_id();
				}
			}
		}//fin de for i
	//insertamos el detalle de los movimientos de almacen
		$sql_1="INSERT INTO ec_movimiento_detalle ( id_movimiento_almacen_detalle, id_movimiento, 
             id_producto, cantidad, cantidad_surtida, id_pedido_detalle, id_oc_detalle, id_proveedor_producto,
             id_equivalente, sincronizar )
				SELECT 
					null,
					IF( pd.es_externo=1,'{$id_mov_ext}','{$id_mov_int}'),
					IF( prd.id_producto IS NULL, pd.id_producto, prd.id_producto_ordigen ),
					IF( prd.id_producto IS NULL, pd.cantidad, ( pd.cantidad * prd.cantidad ) ),
					IF( prd.id_producto IS NULL, pd.cantidad, ( pd.cantidad * prd.cantidad ) ),
					id_pedido_detalle,
					-1,
					NULL,
					'0',
					'0'
				FROM ec_pedidos_detalle pd
				LEFT JOIN ec_productos_detalle prd
				ON pd.id_producto = prd.id_producto
				WHERE pd.id_pedido=$id_ped_apart
				GROUP BY pd.id_pedido_detalle";
		$eje_1=mysql_query($sql_1)or die("Error al insertar el detalle de la venta modificada!!!\n\n".$sql_1.mysql_error()); 
	//insertamos el detalle de los movimentos de almacen

	/*Fin de cambio Oscar 13.11.2018*/
		mysql_query("COMMIT");

	//sacamos la suma de los pagos para mostrar diferencia
		$sql="SELECT SUM(IF(id_pedido_pago IS NULL,0,monto)) FROM ec_pedido_pagos WHERE id_pedido=$id_ped_apart";
		$eje=mysql_query($sql);
		if(!$eje){
			$error=mysql_error();
			mysql_query("ROLLBACK");
			die("Error al sumar los pagos del pedido!!!\n\n".$sql."\n\n".$error);
		}
		$suma=mysql_fetch_row($eje);

		die("OK|IDP:{$id_ped_apart}|FOLIO:{$folio}|{$temporales_detalle}");//se agrega el envío de detalle temporal Oscar 13.11.2018
	}//fin de if es modificación apartado		

/*fin de cambio 03.09.2018*/





	// Conseguir algunos datos de la sucursal
		$cs = "	SELECT
				descuento,
				prefijo
				FROM sys_sucursales
				WHERE id_sucursal = '{$user_sucursal}'";
		if ($rs = mysql_query($cs))
		{
			if ($dr = mysql_fetch_assoc($rs))
			{
				$descuento = $es_paquete ? $dr["descuento"] : 0;
				$prefijo = $dr["prefijo"];
			}
			mysql_free_result($rs);
		}
		else
		{
			throw new Exception ("No se consiguió descuento/prefijo de la sucursal.");
		}
		
		// Conseguir un nuevo folio para la venta/pedido
		$cs = "	SELECT
				CONCAT(
					'{$prefijo}',
					IF(
						ISNULL(MAX(CAST(REPLACE(folio_{$tipo_folio}, '{$prefijo}', '') AS SIGNED INT))),
						1,
						MAX(CAST(REPLACE(folio_{$tipo_folio}, '{$prefijo}', '') AS SIGNED INT))+1
					)
				) AS folio
				FROM ec_pedidos
				WHERE REPLACE(folio_{$tipo_folio}, '{$prefijo}', '') REGEXP ('[0-9]')
				AND id_sucursal='{$user_sucursal}'
				AND id_pedido <> '{$_GET["idp"]}'";
		if ($rs = mysql_query($cs))
		{
			if ($dr = mysql_fetch_assoc($rs))
			{
				$folio = $dr["folio"];
			}
			mysql_free_result($rs);
		}
		else
		{
			throw new Exception ("No se consiguió un nuevo folio ({$tipo_folio}).");
		}
		
	$autoriza_desc=$_GET['descuento_autorizado'];
/**/
	if(!isset($_GET['descuento_autorizado'])){$tipo_venta=0;}else{$lista_precios_mayoreo=$_GET['lista_precios_mayoreo'];}

	if($lista_precios_mayoreo!='' && $lista_precios_mayoreo!=null){
		$tipo_venta=$lista_precios_mayoreo;
	}else{
		$tipo_venta=0;
	}
/**/
		# Guardar el encabezado
		$cs = "	INSERT INTO ec_pedidos_back
				SET
				id_cliente = '1',
				id_estatus = '2',
				id_moneda = '1',
				fecha_alta = NOW(),
				id_direccion = '-1',
				id_razon_social = '-1',
				direccion='{$autoriza_desc}',
				subtotal = '0',
				iva = '0',
				ieps = '0',
				total = '0',
				pagado = '0',
				surtido = '0',
				enviado = '0',
				id_sucursal = '{$user_sucursal}',
				id_usuario = '{$user_id}', ";
		
		$cs.="folio_nv = '{$folio}',";
		
		$cs.= "	fue_cot = '0',
				facturado = '0',
				id_tipo_envio = '1',
				descuento = '0',
				id_razon_factura = NULL, ";
	/*implementación Oscar 01.03.2019 para marcar el tipo de venta*/
		$cs.="tipo_pedido=$tipo_venta";
	/*Fin de cambio Oscar 01.03.2019*/		
			
		if (!mysql_query($cs)){
			throw new Exception("Imposible almacenar este registro (pedidos). $cs" . mysql_error());
		}
        
		$id_pedido=mysql_insert_id();


		//Actualizamos el folio de nota de venta
		if (!$es_nuevo_registro)
		{
			$sql="	UPDATE ec_pedidos
					SET
					folio_nv='{$folio}'
					WHERE id_pedido = '{$id_pedido}'";
			if (!mysql_query($sql)){
				throw new Exception("Imposible almacenar este registro (pedidos). " . mysql_error());
			}
		}   
        
		
/*Implementacion Oscar 2023 para enlazar los productos tomados de exhibicion*/
		if( isset( $_GET['exhibition_headers'] ) && $_GET['exhibition_headers'] != null 
			&& $_GET['exhibition_headers'] != '' ){
			$sql_exh = "UPDATE ec_temporal_exhibicion SET id_pedido_back = {$id_pedido} WHERE id_temporal_exhibicion IN( {$_GET['exhibition_headers']} )";
			$stm_exh = mysql_query( $sql_exh ) or die( "Error al enlazar registros de exhibicion : " . mysql_error() );
		}
/**/
		# Conseguir el IVA del sistema
		$iva = 0.16;
		$ieps = 0.30;
		$cs = "SELECT 16/100 AS iva, 0 AS ieps";/*FROM ec_conf_gral Modificación Oscar 28.02.2018*/
		if ($rs = mysql_query($cs))
		{
			if ($dr = mysql_fetch_assoc($rs))
			{
				$iva = $dr["iva"];
				$ieps = $dr["ieps"];
			}
			mysql_free_result($rs);
		}
		else
		{
			throw new Exception ("No se consiguió el parámetro IVA/IEPS del sistema.");
		}
		
		$pedido_subtotal = 0.0;
		$pedido_iva = 0.0;
		$pedido_ieps = 0.0;
		$pedido_total = 0.0;
		$pedido_descuento = 0.0;
		
		# Guardar el detalle
		for ($ix=0; $ix<$nitems; ++$ix){		
			# Guardar el registro del detalle 
	/*Implmentación Oscar 07-08-2018 para insertar externos*/
		$sql_tmp="SELECT sp.es_externo FROM sys_sucursales_producto sp WHERE sp.id_sucursal=$user_sucursal AND sp.id_producto='{$_GET["idp{$ix}"]}'";
		$eje=mysql_query($sql_tmp);
		if(!$eje){
			die("Error al consultar si es producto externo");
		}
		$row=mysql_fetch_row($eje);
	/*Fin de cambio 07.08.2018*/	

/*implementacion Oscar 24.10.2019 para poder asignar id_precio al producto 18000 (Ultimas piezas)*/
//if($_GET["idp{$ix}"]=='1808'){
			if($_GET["id_precio{$ix}"]=='' || $_GET["id_precio{$ix}"]=='undefined'){
				$sql="SELECT id_precio FROM sys_sucursales WHERE id_sucursal=$user_sucursal";
				$eje_prec_aux=mysql_query($sql);
				$r_prec_aux=mysql_fetch_row($eje_prec_aux);
				if( $r_prec_aux[0] == null || $r_prec_aux[0] == '' ){
					$r_prec_aux[0] = 1;
				}
				$_GET["id_precio{$ix}"]=$r_prec_aux[0];
			}
/*Fin de cambio Oscar 24.10.2019*/

				$descuento = (!isset($_GET["desc{$ix}"])) ? 0 : $_GET["desc{$ix}"];

			$cs = "	INSERT INTO ec_pedidos_detalle_back
					SET
					id_pedido = '{$id_pedido}',
					id_producto = '{$_GET["idp{$ix}"]}',
					cantidad_surtida = '0', ";
			$cs.="	cantidad = '{$_GET["can{$ix}"]}',
					precio = '{$_GET["pre{$ix}"]}',
					monto = '{$_GET["mon{$ix}"]}',
					iva = '0',
					ieps = '0', 
					descuento='{$descuento}',
					es_externo=$row[0],
					id_precio='{$_GET["id_precio{$ix}"]}'";/*Campo implementado por Oscar 07.08.2018*/
			
				
			if (!mysql_query($cs)){
				throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$cs\n\n" . mysql_error());
			}
            
            $pedido_subtotal+=$_GET["mon{$ix}"];	
            
            		
		}
		
		
       		      

        #$pedido_iva=$pedido_subtotal/(1+$iva);
        #$pedido_total=$pedido_subtotal;
        #$pedido_subtotal=$pedido_total-$pedido_iva;
        
        
        //$pedido_total=$pedido_subtotal-$pedido_descuento;
        //echo '$pedido_descuento:'.$pedido_descuento."|$pedido_subtotal:".$pedido_subtotal."\n\n";
        $pedido_descuento=$pedido_subtotal-$_GET["totalPed"];

       // die('pedido_descuento:'.$pedido_descuento."pedido_subtotal:".$pedido_subtotal."total_ped:".$_GET['totalPed']);
		if ($pedido_descuento<=1)
		{
			$pedido_descuento=0;
			$pedido_total=$pedido_subtotal;
			
		}
		else
		{
			$pedido_total=$_GET["totalPed"];
			//die("entra aqui: ".$pedido_total."---->pedido_descuento".$pedido);
		}

/*DESHABILITADO POR OSCAR 2024-02-13
Implementación Oscar 05.11.2018 para redondear hacia arriba los valores de la cabecera de la tabla temporal de ventas
		$pedido_subtotal=CEIL($pedido_subtotal);
		$pedido_total=CEIL($pedido_total);
		$pedido_descuento=CEIL($pedido_descuento);
/*Fin de cambio Oscar 05.11.2018*/
	
/*Implementación Oscar 06.11.2018 para verificar la autorización del descuento*/
	$sql="SELECT direccion FROM ec_pedidos_back WHERE id_pedido={$id_pedido}";
	$eje=mysql_query($sql)or die("Error al verificar la autorización del descuento!!!\n\n".$sql."\n\n".mysql_error());
	$aut=mysql_fetch_row($eje);
	if($aut[0]!=1){
	/*deshabilitado por Oscar 2024-02-22
		//$pedido_subtotal=CEIL($pedido_subtotal);
		//$pedido_total=CEIL($pedido_subtotal);
		//$pedido_descuento=0;
	*/
		$pedido_subtotal=$pedido_subtotal;
		$pedido_total=$pedido_subtotal;
		$pedido_descuento=0;
	//insertamos el error
		$sql="INSERT INTO sys_bitacora_errores ( id_error, id_sucursal, descripcion, fecha_error, id_usuario, observaciones, visto, id_equivalente, sincronizar ) VALUES 
		(null,$user_sucursal,'Se activó un descuento sin autorizacion en pedido $id_pedido, folio: $folio',now(),$user_id,'','0', 0, 1 )";
		$eje=mysql_query($sql)or die("Error al insertar error!!!".$sql."\n\n".mysql_error());
	}
/*Fin de cambio Oscar 06.11.2018*/

		# Actualizar los valores pendientes del encabezado  $pedido_total//OSCAR 2024-02-13
		$cs = "UPDATE ec_pedidos_back SET " .
			"subtotal = '{$pedido_subtotal}', " .
			"iva = '0', " .
			"ieps = '0', " .
			"total = '{$_GET['totalPed']}', " .
			"descuento = '{$pedido_descuento}' " .
			"WHERE id_pedido = '{$id_pedido}' ";
		//die($cs);
		if (!mysql_query($cs)){
			throw new Exception("Imposible actualizar la segunda parte del pedido.\n\n$cs\n\n" . mysql_error());
		}
		mysql_query("COMMIT");//autorizamos transacción
/*implementacion Oscar 2023 para imprimir cotizacion*/
		if( isset( $_GET['is_quotation'] ) && $_GET['is_quotation'] == 1 ){
			die( "ok|{$id_pedido}" );
		}
/*fin de cambio Oscar 2023*/	

		echo "OK|IDP:{$id_pedido}|FOLIO:{$folio}";
		
	}catch (Exception $e){
		echo "ERR|" . $e->getMessage();
		mysql_query("ROLLBACK");//cancelamos transacción
		mysql_close();
		exit ();
	}
?>