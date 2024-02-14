<?php	
/*version 30.10.2019*/
	header("Content-Type: text/plain;charset=utf-8");
	include("../../include/PHPMailer/PHPMailerAutoload.php");
	include("../../conectMin.php");
	//include("../../conexionDoble.php");
	$es_apartado = isset($_GET["ap"]) ? $_GET["ap"] : "0";
	$id_pedido = isset($_GET["idp"]) ? $_GET["idp"] : "0";
	
	$nitems = $_GET["nitems"];
	$face=$_GET["faceb"];
	$corr=$_GET["cor"];
//declaramos variables...
	$id_pedido_r="";
	$ids_movs="";
	$id_pago="";
/*implementacion Oscar 25.06.2019 para guardar el dato del saldo a favor*/
	if(isset($_GET['a_favor'])){
		$saldo_favor=$_GET['a_favor'];
	}else{
		$saldo_favor=0;
	}
/*Fin de cambio */

/*Implementacion Oscar 2023 para enlazar productos tomados de exhibicion*/
	$exhibition_headers_ids = "";
	$sql = "SELECT 
			GROUP_CONCAT( id_temporal_exhibicion ) AS exhibition_headers_ids
		FROM ec_temporal_exhibicion 
		WHERE id_pedido_back = {$id_pedido}";
	$stm = mysql_query( $sql ) or die( "Error al consultar los temporales de exhibicion : " . mysql_error() );
	if( mysql_num_rows( $stm ) > 0 ){
		$row = mysql_fetch_assoc( $stm );
		$exhibition_headers_ids = $row['exhibition_headers_ids'];
		mysql_free_result($stm);
	}
/**/
/*implementacion Oscar 2023 para saber cual es el tipo de sistema*/
	$sql = "SELECT IF( id_sucursal = -1, 'linea', 'local' ) AS system_type FROM sys_sucursales WHERE acceso = 1";
	$stm = mysql_query( $sql ) or die( "Error al consultar el tipo de sistema : " . mysql_error() );
	$row = mysql_fetch_assoc( $stm );
	$system_type = $row['system_type'];
/**/
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
/*Modificacion Oscar 2023/10/11 para no enlzar directamente el cajero a la venta cuando es unicajero*/
    $id_cajero = 0;
    $id_sesion_caja = 0;
/*fin de cambio Oscar 2203/10/11*/
/**/
	try{
		mysql_query("BEGIN");
		$tipo_folio="nv";
//Conseguir algunos datos de la sucursal
		$cs="SELECT
				descuento,
				prefijo
				FROM sys_sucursales
				WHERE id_sucursal = '{$user_sucursal}'";
		if ($rs = mysql_query($cs)){
			if ($dr = mysql_fetch_assoc($rs)){
				$descuento = $es_paquete ? $dr["descuento"] : 0;
				$prefijo = $dr["prefijo"];
			}
			mysql_free_result($rs);
		}else{
			throw new Exception ("No se consiguió descuento/prefijo de la sucursal.");
		}
	/*Conseguir un nuevo folio para la venta/pedido (solo aplica para linea)*/
		$cs ="SELECT
				CONCAT('LNA',
					'{$prefijo}',
					IF(
						ISNULL(MAX(CAST(REPLACE(folio_{$tipo_folio}, CONCAT('LNA','{$prefijo}'), '') AS SIGNED INT))),
						1,
						MAX(CAST(REPLACE(folio_{$tipo_folio}, CONCAT('LNA','{$prefijo}'), '') AS SIGNED INT))+1
					)
				) AS folio
				FROM ec_pedidos
				WHERE REPLACE(folio_{$tipo_folio}, '{$prefijo}', '') REGEXP ('[0-9]')
				AND id_sucursal='{$user_sucursal}'";
	//print_r($cs);
		if ($rs=mysql_query($cs)){
			if($dr = mysql_fetch_assoc($rs)){
				$folio = $dr["folio"];
			}
			mysql_free_result($rs);
		}else{
			throw new Exception ("No se consiguió un nuevo folio ({$tipo_folio}).");
		}
/**********************Insertamos los datos reales******************/
		$initial_amount = 0;
		if( $es_apartado == 1 ){
			$initial_amount = $_GET['mon0'];
		}
	//Inserta la cabecera del pedido
		$sql="	INSERT INTO ec_pedidos
				( /*1*/id_pedido,/*2*/ folio_pedido, /*3*/folio_nv, /*4*/folio_factura, /*5*/folio_cotizacion, 
					/*6*/id_cliente, /*7*/id_estatus, /*8*/id_moneda, /*9*/fecha_alta, /*10*/fecha_factura,
					/*11*/id_direccion, /*12*/direccion, /*13*/id_razon_social, /*14*/subtotal, /*15*/iva,
					/*16*/ieps, /*17*/total, /*18*/dias_proximo, /*19*/pagado, /*20*/surtido, 
					/*21*/enviado,/*22*/id_sucursal,/*23*/id_usuario,/*24*/fue_cot,/*25*/facturado,
					/*26*/id_tipo_envio,/*27*/descuento,/*28*/id_razon_factura,/*29*/folio_abono,/*30*/correo,
					/*31*/facebook,/*32*/modificado,/*33*/ultima_sincronizacion,/*34*/ultima_modificacion,/*35*/tipo_pedido,
					/*36*/id_status_agrupacion,/*37*/id_cajero,/*38*/id_devoluciones,/*39*/venta_validada, 
					/*40*/id_sesion_caja, /*41*/tipo_sistema, /*42*/monto_pago_inicial )
					SELECT
					/*1*/null,
					/*2*/folio_pedido,
					/*3*/'$folio',
					/*4*/folio_factura,
					/*5*/folio_cotizacion,
					/*6*/id_cliente,
					/*7*/id_estatus,
					/*8*/id_moneda,
					/*9*/NOW(),
					/*10*/fecha_factura,
					/*11*/id_direccion,
					/*12*/direccion,
					/*13*/id_razon_social,
					/*14*/subtotal,
					/*15*/iva,
					/*16*/ieps,
					/*17*/total,
					/*18*/dias_proximo,
					/*19*/IF( '{$es_apartado}' = '0' OR '{$es_apartado}' = '', 1, 0 ),
					/*20*/surtido,
					/*21*/enviado,
					/*22*/id_sucursal,
					/*23*/id_usuario,
					/*24*/fue_cot,
					/*25*/facturado,
					/*26*/id_tipo_envio,
					/*27*/descuento,
					/*28*/id_razon_factura,
					/*29*/folio_abono,
					/*30*/'{$corr}',
					/*31*/'{$face}',
					/*32*/0,
					/*33*/'0000-00-00 00:00:00',
					/*34*/NOW(),
					/*35*/tipo_pedido,
					/*36*/-1,
					/*37*/{$id_cajero},
					/*38*/{$saldo_favor},/*saldo a favor por devolucion*/
					/*39*/'0',/*( venta validada )oscar 2022*/
					/*40*/{$id_sesion_caja},
					/*41*/'{$system_type}',
					/*42*/{$initial_amount}
					FROM ec_pedidos_back
					WHERE id_pedido = {$id_pedido}";

		$res=mysql_query($sql);

		if(!$res){
			throw new Exception("No se pudo insertar la nota de venta\n\n".mysql_error()."\n\n".$sql);	
		}

		$id_pedido_r=mysql_insert_id();
/**/
	if ( $exhibition_headers_ids != "" ){
		$exh_sql = "UPDATE ec_temporal_exhibicion 
						SET id_pedido = {$id_pedido_r} 
					WHERE id_pedido_back = {$id_pedido}";
		$exh_stm = mysql_query( $exh_sql ) or die( "Error al enlazar el id de la venta con los registros de exhibicion : " . mysql_error() );
	}
/**/
//Cambio Oscar 23.02.2018
/*IMPLEMENTACIÓN OSCAR 18.11.2019 PARA NO ACTUALIZAR EL FOLIO DE ACUERDO AL ID SI ES LÍNEA*/
		$qry=mysql_query("SELECT id_sucursal FROM sys_sucursales WHERE acceso=1")or die("Error al consultar el tipo de sistema!!!\n\n".mysql_error());
		$exe=mysql_fetch_row($qry);
		if($exe[0]!=-1){//si es diferente de línea
	//obtenemos el folio
		$sql_fol="INSERT INTO cont_folios_vta VALUES(null,$id_pedido_r)";
		$eje_fol=mysql_query($sql_fol);
		if(!$res){
				throw new Exception("Error al insertar el registro para obtener consecutivo de folio!!!".mysql_error());	
			}
		$cons_fol=mysql_insert_id();
		//actualizamos folio
			$fol="UPDATE ec_pedidos SET folio_nv=CONCAT('{$prefijo}',$cons_fol) WHERE id_pedido=$id_pedido_r";
			$res=mysql_query($fol);
			if(!$res){
				throw new Exception("No se pudo insertar la nota de venta debido a que el folio no se udo actualizar\n\n$fol\n\n" . mysql_error());	
			}
			$folio=$prefijo.$cons_fol;
		}//fin de if es diferente de línea
/*FIN DE CAMBIO 30.08.2018*/
//fin de cambio 23.02.2018
	//Insertamos el detalle del pedido
		$sql="	INSERT INTO ec_pedidos_detalle 
				( /*1*/id_pedido_detalle, /*2*/id_pedido, /*3*/id_producto, /*4*/cantidad, /*5*/precio,
				/*6*/monto,/*7*/iva,/*8*/ieps,/*9*/cantidad_surtida,/*10*/descuento,
				/*11*/modificado,/*12*/es_externo,/*13*/id_precio,/*14*/folio_unico )
				SELECT
					/*1*/null,
					/*2*/{$id_pedido_r},
					/*3*/id_producto,
					/*4*/cantidad,
					/*5*/precio,
					/*6*/monto,
					/*7*/iva,
					/*8*/ieps,
					/*9*/cantidad_surtida,
					/*10*/descuento,
					/*11*/0,/*implementado el 17-1-2018*/
					/*12*/es_externo,
					/*13*/id_precio,/*implementado el 25.03.2019*/
					/*14*/0/*id_ equivalente 2022*/
				FROM ec_pedidos_detalle_back
				WHERE id_pedido = {$id_pedido}";
		$res=mysql_query($sql);
		
		if(!$res){
			//die($sql);
			throw new Exception("No se pudo insertar la nota de venta\n\n" . mysql_error());
		}
	//Insertamos el movimiento de almacen producto x producto
		$sql="SELECT
				pd.id_producto AS id_prod,
				pd.cantidad AS can_s,
				p.es_maquilado AS maquilado,
				pd.es_externo,/*Implementado por oscar 07.08.2018*/
				pd.id_pedido_detalle
				FROM ec_pedidos_detalle pd/*_back*/
				LEFT JOIN ec_productos p on pd.id_producto=p.id_productos
				WHERE id_pedido = '{$id_pedido_r}'";
		//echo $sql;
		$res=mysql_query($sql);
		if(!$res){
			die($sql);
			throw new Exception("No se pudo insertar la nota de venta\n\n" . mysql_error());
		}
		$num=mysql_num_rows($res);
		for($i=0;$i<$num;$i++){
			//$id_pedido_detalle = $row[];
			$row = mysql_fetch_assoc($res);
			extract($row);
	//Buscamos el almacen correspondiente
			$sql="SELECT
					a.id_almacen,
					a.prioridad,
					(
						SELECT
	                    SUM(d.cantidad_surtida*tm.afecta)
    	                FROM ec_movimiento_detalle d
       		            JOIN ec_movimiento_almacen aa ON aa.id_movimiento_almacen = d.id_movimiento
	                    JOIN ec_tipos_movimiento tm ON tm.id_tipo_movimiento = aa.id_tipo_movimiento
    	                WHERE a.id_almacen = aa.id_almacen
        	            AND d.id_producto=$id_prod
					)
					FROM ec_almacen a
					WHERE a.id_sucursal=$user_sucursal
					AND a.id_almacen <> -1 
					ORDER BY es_almacen DESC, prioridad";
      //           die($sql);
			$re=mysql_query($sql);
	        if(!$re){
				die($sql);
				throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
	        }
			$nu=mysql_num_rows($re);
			$canSur=$can_s;
			//Recorremos los almacenes
	//		echo 'num:'.$nu;
			for($j=0;$j<1;$j++){
				$ro=mysql_fetch_row($re);
				if($j == 0){
					$almacenPri=$ro[0];
				}
				//echo 'jkebfkñwebf';
			//Si existe inventario en el almacen (aqui modifique deberia de ser mayor a cero)    
				//echo 'ro2: '.$ro[2];
				//if($ro[2]>-10000){
				//echo'here';
					//Insertamos cabecera
					$sql="	INSERT INTO ec_movimiento_almacen(id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, id_orden_compra, lote, id_maquila, id_transferencia, id_almacen)
							SELECT 2,$user_id,id_sucursal, now(),now(),'',{$id_pedido_r}, -1, '', -1, -1, IF($es_externo=1,almacen_externo,$ro[0])
							FROM sys_sucursales WHERE id_sucursal=$user_sucursal";/*Modificado por oscar 07.08.2018*/
					//VALUES(2, $user_id, $user_sucursal, NOW(), NOW(), '', {$id_pedido_r}, -1, '', -1, -1, $ro[0])
                    //echo $sql;
                    
					if (!mysql_query($sql)){
						
			//die($sql);
						throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
					}
                    
					$id_mov=mysql_insert_id();
				//guardamos ids de movimientos	
					$ids_movs.=$id_mov."|";  

					if($canSur > $ro[2]){
						$can=$ro[2];
						$canSur-=$ro[2];
                    }else{
						$can=$canSur;
						$canSur=0;
					}
			//echo 'hasta aqui';
					if($maquilado==1){
						//echo 'here is maquila';
						$aux="SELECT id_producto_ordigen,cantidad 
								FROM ec_productos_detalle WHERE id_producto=$id_prod";
						$ejeAux=mysql_query($aux) or die('ERROR!!<br>'.$aux);
						$nM=mysql_num_rows($ejeAux);
						if($nM>0){
							while($dA=mysql_fetch_row($ejeAux)){
								$suma=$dA[1]*$can_s;
								//echo 'suma:'.$dA[1].' * '. $can_s.' = '.$suma;
								$sqlAux="INSERT INTO ec_movimiento_detalle(id_movimiento, id_producto, cantidad, cantidad_surtida, id_pedido_detalle, id_oc_detalle)
									VALUES('$id_mov','$dA[0]','$suma','$suma', '{$id_pedido_detalle}' ,-1)";
								$ejeSqlAux=mysql_query($sqlAux)or die('ERRROR!!!'.$sqlAux);
								//echo $sqlAux.'<br>';
							}
						}
					}else if($maquilado==0){
						//echo'no maquilado';
			//Insertamos detalle   
						$sql="INSERT INTO ec_movimiento_detalle(id_movimiento, id_producto, cantidad, cantidad_surtida, id_pedido_detalle, id_oc_detalle)
								VALUES('$id_mov','$id_prod',$can_s,$can_s,'{$id_pedido_detalle}','-1')";
						if (!mysql_query($sql)){
							throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
						}
					}//echo $sql;
            //si no hay inventario
				/*}else{
					continue;
				}*/   
            //Si se ha terminado de surtir        
				if($canSur == 0){
					break;                        
				}
		}

		//buscamos si hay alerta
            $sql="	SELECT
					alertas_resurtimiento,
					p.omitir_alertas
					FROM sys_sucursales s
					JOIN ec_productos p ON p.id_productos={$id_prod}
					WHERE s.id_sucursal=$user_sucursal";
            
            $re=mysql_query($sql);
            if(!$re)
                throw new Exception("Imposible verificar si la sucursal permite alertas.\n\n$sql\n\n" . mysql_error());
                
			$ro=mysql_fetch_row($re);
           
			//Si permite alertas
			if($ro[0] == '1' && $ro[1] == '0'){
            
				$sql="SELECT
                      SUM(d.cantidad_surtida*tm.afecta)
                      FROM ec_movimiento_detalle d
                      JOIN ec_movimiento_almacen aa ON aa.id_movimiento_almacen = d.id_movimiento
                      JOIN ec_tipos_movimiento tm ON tm.id_tipo_movimiento = aa.id_tipo_movimiento
                      WHERE aa.id_sucursal = $user_sucursal
                      AND d.id_producto={$id_prod}";
                      
                $re=mysql_query($sql);
                if(!$re)
                    throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                    
				$ro=mysql_fetch_row($re);
				$existencia=$ro[0];     
                
                
                $sql="	SELECT
						IF(
							ep.minimo IS NULL,
							pr.min_existencia,
							ep.minimo
						)
						FROM ec_productos pr
						JOIN sys_sucursales s ON s.id_sucursal=$user_sucursal
						JOIN ec_estacionalidad e ON e.id_estacionalidad = s.id_estacionalidad
						JOIN ec_estacionalidad_producto ep ON e.id_estacionalidad = ep.id_estacionalidad AND pr.id_productos = ep.id_producto
						WHERE pr.id_productos={$id_prod}";
                      
                $re=mysql_query($sql);
                if(!$re)
                    throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                    
				$ro=mysql_fetch_row($re);
				$min=$ro[0];
				
				
				//throw new Exception("eRROR");
               
				if($existencia <= $min)
				{
                   
					$sql="	INSERT INTO ec_alerta(nombre, fecha, hora, tipo)
							VALUES('Producto con existencia urgente en la sucursal ', NOW(), NOW(), 'code/general/contenido.php?aab9e1de16f38176f86d7a92ba337a8d=ZWNfdHJhbnNmZXJlbmNpYXM=&a1de185b82326ad96dec8ced6dad5fbbd=MA==&a01773a8a11c5f7314901bdae5825a190=bnVsbA==&bnVtZXJvX3RhYmxh=MA==')";
							
							
					if (!mysql_query($sql))
						throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                  	
                  
                    
					$id_alerta=mysql_insert_id();

			//Insertamos al usuario de mercancias
					$sql="SELECT id_encargado FROM sys_sucursales WHERE id_sucursal=$user_sucursal";
                   
					$re=mysql_query($sql);
					if(!$re)
						throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                   
					$ro=mysql_fetch_row($re);
					
					if($ro[0] == '' || $ro[0] == ' ')
						$ro[0]=1;
                   
					$sql="	INSERT INTO ec_alerta_registro(id_alerta, id_usuario, descripcion, visto)
							VALUES($id_alerta, $ro[0], '', 0)";
                                                 
					if (!mysql_query($sql))
						throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                   
					//Buscamos a los administradores
					$sql="SELECT id_usuario FROM sys_users WHERE administrador=1 AND id_usuario NOT IN($ro[0])";
                   
					$re=mysql_query($sql);
					if(!$re)
						throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                   
					$nu=mysql_num_rows($re);
                   
					for($j=0;$j<$nu;$j++)
					{
						$ro=mysql_fetch_row($re);
						
						if($ro[0] == '' || $ro[0] == ' ')
							$ro[0]=1;
                       
						$sql="INSERT INTO ec_alerta_registro(id_alerta, id_usuario, descripcion, visto)
                                                 VALUES($id_alerta, $ro[0], '', 0)";
                                                 
						if (!mysql_query($sql))
							throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
					}
					
				}
            }

			
			
		}//fin de for i	
		
		/*****************************FIN INSERCCION DATOS REALES************************************/
			
	/*imlementación Oscar 08.08.2018*/
		$sql="SELECT count(id_pedido_detalle) FROM ec_pedidos_detalle_back WHERE id_pedido=$id_pedido AND es_externo=1";
		$eje=mysql_query($sql)or die("Error al consultar cuantos productos son externos en el pedido!!!\n\n".$sql."\n\n".mysql_error());
		$r=mysql_fetch_row($eje);
		//if($r[0]>0){
		//buscamos cantidad total de productos de Pedro y externos
			$sql="SELECT
					(aux.interno/aux.total),
					(aux.externo/aux.total)
				FROM(
    				SELECT 
						SUM(monto-descuento) as total,
				    	SUM(IF(es_externo=1,monto-descuento,0)) as externo,
    					SUM(IF(es_externo=0,monto-descuento,0))as interno 
					FROM ec_pedidos_detalle_back WHERE id_pedido=$id_pedido
   				)aux";
			$eje=mysql_query($sql)or die("Error al consultar porcentajes de pagos interno y externo!!!\n\n".$sql."\n\n".mysql_error());
			$respuesta=mysql_fetch_row($eje);
		//guardamos los porcentajes correspondientes a la nota final
			$pago_interno=$respuesta[0];
			$pago_externo=$respuesta[1];
		//}else{
		//guardamos lo porcentajes sin productos externos
		//	$pago_interno=1;
		//	$pago_externo=0;
		//}
	/*Fin de cambio*/

/*deshabilitado por Oscar 2023/10/16		
        
        if($_GET["ap"] == '0')
        {
            $sql="UPDATE ec_pedidos SET pagado=1 WHERE id_pedido={$id_pedido_r}";
            mysql_query($sql);
        }
        
		
		$cs = "DELETE FROM ec_pedido_pagos WHERE id_pedido = '{$id_pedido_r}' ";
		
		if (!mysql_query($cs)){
			throw new Exception("Imposible eliminar entradas obsoletas de pagos. " . mysql_error());
		}
		for ($ix=0; $ix<$nitems; ++$ix)
		{
		    if($_GET["mon{$ix}"] > 0){
            	for($ij=0;$ij<=1;$ij++){
            		if($ij==0&&$pago_interno>0||$ij==1&&$pago_externo>0){
    					$cs="INSERT INTO ec_pedido_pagos SET
							id_pedido = '{$id_pedido_r}',
							id_tipo_pago = '{$_GET["idt{$ix}"]}',
							fecha = CURDATE(),
							hora = CURTIME(),";
						if($ij==0){
							$cs.="monto=".round($_GET["mon{$ix}"]*$pago_interno,2).",";
						}else{
							$cs.="monto=".round($_GET["mon{$ix}"]*$pago_externo,2).",";
						}
						//monto = '{$_GET["mon{$ix}"]}',
						$cs.="referencia = '',
							id_moneda = '1',
							tipo_cambio = '1',
							id_nota_credito = '-1',
							id_cxc = '-1',";
					//marcamos si el pago es externo o no
						if($ij==0){
							$cs.="es_externo=0";//interno
						}else{
							$cs.="es_externo=1";//externo
						}
						$cs.=",id_cajero = {$id_cajero}, id_sesion_caja = {$id_sesion_caja}";
            			if(!mysql_query($cs)){
							throw new Exception("Imposible almacenar registro (pago). <br><br>$cs<br><br>" . mysql_error());
    					}
            		}
    				
            	}//fin de for $ij
            }        
        //capturamos el id
            $id_pago=mysql_insert_id();
		}//fin de for $ix
*/
        //Actualizamos el estatus del pago
        
        $sql="	SELECT
				p.total,
				(
					SELECT
                	IF(SUM(monto) IS NULL, 0, SUM(monto))
                	FROM ec_pedido_pagos
                	WHERE id_pedido = p.id_pedido
				)
				FROM ec_pedidos p
				WHERE p.id_pedido=$id_pedido_r";
        $res=mysql_query($sql);
        
        if(!$res){
            throw new Exception("Imposible almacenar registro (pago). <br><br>$sql<br><br>" . mysql_error());
        }
        
        $row=mysql_fetch_row($res);

/*implementacion Oscar 2023-12-19 para insertar referencia de la nota de venta y a devolucion*/
		$sql = "INSERT INTO ec_pedidos_referencia_devolucion ( id_pedido_referencia_devolucion, id_pedido, total_venta, 
			monto_venta_mas_ultima_devolucion, folio_unico, sincronizar ) VALUES ( NULL, {$id_pedido_r}, {$row[0]}, {$row[0]}, NULL, 1 )";
		$reference_stm = mysql_query( $sql ) or die( "Error al insertar la referencia de la devolucion : " . mysql_error() );
/*fin de cambio Oscar 2023-12-19*/

        $new_total = $row[0];
        if($row[0] <= $row[1]){
            $sql="UPDATE ec_pedidos SET pagado=1 WHERE id_pedido=$id_pedido_r";
            if (!mysql_query($sql))
				throw new Exception("Imposible almacenar registro (pago). <br><br>$sql<br><br>" . mysql_error());
        }else{
    //Creamos el folio de apartado
            $sql="SELECT
                  IF(MAX(folio_abono) IS NULL, 1, MAX(folio_abono)+1)
                  FROM ec_pedidos
                  WHERE id_sucursal=$user_sucursal";
            $res=mysql_query($sql);
        
            if(!$res)   
                throw new Exception("Imposible almacenar registro (pago). <br><br>$sql<br><br>" . mysql_error());
                
            $row=mysql_fetch_row($res);
            $sql="UPDATE ec_pedidos SET folio_abono=$row[0] WHERE id_pedido=$id_pedido_r";
            if (!mysql_query($sql)) throw new Exception("Imposible almacenar registro (pago).<br><br>$sql<br><br>" . mysql_error());
                                  
        }
	}catch (Exception $e) {
		echo "ERR|" . $e->getMessage();
		mysql_query("ROLLBACK");
		mysql_close();
		exit();
	}
	//die( "DEvoluciones : {$_GET['id_devoluciones']}" );
/*Implementacion Oscar 25.06.2019 para insertar la referencia de las devoluciones en el pedido*/
	if( isset( $_GET['id_devoluciones'] ) ){
		$id_devoluciones = $_GET['id_devoluciones'];
		$tmp_devs = str_replace('~', ',', $id_devoluciones );
		$devs_array = explode( '~', $id_devoluciones );
	//consulta id de pedido original 
		$sql = "SELECT id_pedido FROM ec_devolucion WHERE id_devolucion IN( {$tmp_devs} )";
		$stm = mysql_query( $sql ) or die( "Error al consultar el id de la venta original : " . mysql_error() );
		$row = mysql_fetch_assoc( $stm );
	//consulta pagos de venta original
		$sql = "SELECT
					ax.original_sale_id, 
					SUM( pp.monto ) AS payments_total, 
					ax.original_sale_session_id,
					ax.return_amount,
					ax.total
				FROM(
					SELECT 
						p.id_pedido AS original_sale_id, 
						p.id_sesion_caja AS original_sale_session_id,
						SUM( d.monto_devolucion ) AS return_amount,
						p.total
					FROM ec_pedidos p
					LEFT JOIN ec_devolucion d
					ON d.id_pedido = p.id_pedido 
					WHERE d.id_devolucion IN( {$tmp_devs} )
					GROUP BY p.id_pedido
				)ax
				LEFT JOIN ec_pedido_pagos pp 
				ON ax.original_sale_id = pp.id_pedido";
				//die( $sql );
		$stm = mysql_query( $sql ) or die( "Error al consultar informacion del pedido original : " . mysql_error() );
		$row = mysql_fetch_assoc( $stm );
		$return_internal_ammount = 0;
		$return_external_ammount = 0;

//consulta el monto de la devolucion interna
		$sql = "SELECT monto_devolucion, es_externo FROM ec_devolucion WHERE id_devolucion IN( $tmp_devs )";
		$stm_amount = mysql_query( $sql ) or die( "Error al consultar los montos de la devolucion  : " . mysql_error() );
		while ( $row_amount = mysql_fetch_assoc($stm_amount) ) {
			if( $row_amount['es_externo'] == 0 ){
				$return_internal_ammount += $row_amount['monto_devolucion'];
			}else if( $row_amount['es_externo'] == 1 ){
				$return_external_ammount += $row_amount['monto_devolucion'];
			}
		}

/*DESHABILITADO POR OSCAR 2024-12-20*/
	/*consulta los pagos de devolcuiones anteriores
		$sql = "SELECT 
					SUM( IF( dp.id_devolucion_pago IS NULL, 0, dp.monto ) ) AS return_payments
				FROM ec_devolucion_pagos dp
				LEFT JOIN ec_devolucion d
				ON dp.id_devolucion = d.id_devolucion
				WHERE d.id_pedido IN( {$row['original_sale_id']} )";
		//die( $sql );
		$stm_dev = mysql_query( $sql ) or die( "Error al consultar los pagos previos de devoluciones : " . mysql_error() );
		$montos_dev = 0;
		if( mysql_num_rows($stm_dev) > 0 ){ 
			$row_dev = mysql_fetch_assoc( $stm_dev );
			$montos_dev = $row_dev['return_payments'];
		}
		$liquidada = 0;
		$diferencia_entre_pagos = ( $row['payments_total'] - $montos_dev ) - $row['total'];
	//formula para saber si esta liquidada
		if( $diferencia_entre_pagos == 0 || $diferencia_entre_pagos == 1 || $diferencia_entre_pagos == -1 ){
			$liquidada =1;
		}
	//consultamnos sis ;la venta esta liquidada
		if( $liquidada == 1 ){
			$return_internal_ammount = $row['payments_total'] - $row['total'] - $montos_dev;//OSCAR
			if( $return_internal_ammount < 0 ){
				die( "Salio un valor negativo esprando un valor positivo, caso 1 ( liquidada )" );
			}
		}else{
			$return_internal_ammount =  $row['total'] - $row['payments_total'] - $montos_dev;//FERNANDA
			if( $return_internal_ammount < 0 ){
				$return_internal_ammount = abs($return_internal_ammount);
			}else{
				$return_internal_ammount = 0;
			}
		}*/
		//die( "CAlculo : (saldo_a_favor){$return_internal_ammount} = (total_pago){$row['payments_total']} -(total_venta){$row['total']} - (pagos_devolucion){$montos_dev}" );
	//consulta el monto de la devolucion externa
		if( $row['payments_total'] > 0 ){
			//if( $return_internal_ammount < 0 ){
//$return_internal_ammount = abs($return_internal_ammount);
			//inserta la relacion de los pedidos
				$sql = "INSERT INTO ec_pedidos_relacion_devolucion ( /*1*/id_pedido_relacion_devolucion, /*2*/id_pedido_original, /*3*/monto_pedido_original,
				/*4*/id_sesion_caja_pedido_orginal, /*5*/id_devolucion_interna, /*6*/monto_devolucion_interna, /*7*/id_devolucion_externa, 
				/*8*/monto_devolucion_externa, /*9*/id_pedido_relacionado, /*10*/monto_pedido_relacionado, /*11*/id_sesion_caja_pedido_relacionado )
				VALUES ( /*1*/NULL, /*2*/{$row['original_sale_id']}, /*3*/{$row['payments_total']}, /*4*/{$row['original_sale_session_id']}, 
				/*5*/{$devs_array[0]}, /*6*/{$return_internal_ammount}, /*7*/{$devs_array[1]}, /*8*/{$return_external_ammount}, 
				/*9*/{$id_pedido_r}, /*10*/{$new_total}, /*11*/0 )";
				$stm = mysql_query( $sql ) or die( "Error al insertar la relacion entre pedidos : {$sql} " . mysql_error() );
			//}
			$sql="UPDATE ec_pedidos SET id_devoluciones='$id_devoluciones' WHERE id_pedido=$id_pedido_r";
			$eje=mysql_query($sql)or die("Error al actualizar los ids de devolucion para este pedido!!!\n".mysql_error());
		}else{
			$sql="UPDATE ec_pedidos SET id_devoluciones='0' WHERE id_pedido=$id_pedido_r";
			$eje=mysql_query($sql)or die("Error al quitar ids de devolucion para este pedido!!!\n".mysql_error());
		}	
	}
/*Fin de cambio Oscar 25.06.2019*/
	mysql_query("commit");
	echo 'ok|'.$id_pedido_r."|";
/*implementacion Oscar 02.11.2018 para validar que no pase de la pantalla de cerrar ventas si el monto de pagos es menor al monto de la venta*
	$sql="SELECT
				p.total,
				ROUND(SUM(IF(pp.monto is null,0,pp.monto))),
				p.pagado
			FROM ec_pedidos p 
			LEFT JOIN ec_pedido_pagos pp ON p.id_pedido=pp.id_pedido
		WHERE p.id_pedido=$id_pedido_r";
	$eje=mysql_query($sql)or die("Error al comparar detalles de pago y monto de pago!!!");
	$r=mysql_fetch_row($eje);

	if($r[0]>$r[1] && $r[2]==1){//si el monto es mayor a los pagos y la nota está como pagada
		//insertamos el error
		$eje=mysql_query($sql)or die("Error al insertar error!!!".$sql."\n\n".mysql_error());
	//guardaos el error
		$sql="UPDATE ec_pedidos SET pagado=0 WHERE id_pedido=$id_pedido_r";//actualizamos a no pagada la nota de venta
		$eje=mysql_query($sql)or die("Error al marcar la nota de venta como no pagada!!!\n\n".mysql_error());
	//armamos datos de la emergente
		echo '<p align="center" style="font-size:30px;"><b>';
			echo 'Monitoreo en el funcionamiento del sistema, Favor de enviar una fotografía de esta pantalla a sistemas!!!';
			//echo '<br>Monto: $'.$r[0].'<br>Pagos: $'.$r[1];
			echo '<br>Pida al encargado que ingrese su contraseña y el Proceso continuará de forma normal<br>';
			echo '<input type="text" id="pass_enc_1" onkeydown="cambiar(this,event,\'pass_enc\');" style="padding:10px;">';
			echo '<input type="hidden" id="pass_enc" value="">';
			echo '<br><button onclick="recargar_por_error();" style="padding:10px;">Aceptar</button>';
		echo '</b></p>';
	}
/*Fin de cambio 02.11.2018*/
	


//echo '|<img src="../include/barcode/barcode.php?filepath=../../img/codigos_barra/'.$folio.'.png&text='.$folio.'&size=60&orientation=horizontal&codeType=Code30&print=true">';
	echo '|'.$folio;//
?>
