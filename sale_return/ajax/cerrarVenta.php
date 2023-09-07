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

/*implementación de Oscar 2019 para saber si la sucursal es multicajero*/
	$sql="SELECT IF(multicajero=1,0,(SELECT id_cajero FROM ec_sesion_caja WHERE id_sucursal=$user_sucursal AND hora_fin='00:00:00') ) 
	FROM ec_configuracion_sucursal WHERE id_sucursal=$user_sucursal";
	$eje=mysql_query($sql)or die("Error al consultar si la sucursal es multicajero!!!\n".mysql_error());
	$r_c=mysql_fetch_row($eje);
	$id_cajero=$r_c[0];
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

		//Insertamos la cabecera del pedido
		$sql="	INSERT INTO ec_pedidos
			  	(
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
					/*19*/pagado,
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
					/*30*/'$corr',
					/*31*/'$face',
					/*32*/0,
					/*33(implementado el 17-01-2018)*/0,
					/*34*/'0000-00-00 00:00:00',
					/*35*/NOW(),
			/*Implementación Oscar 01.03.2019 para el nuevo cmapo de tipo de venta*/
					/*36*/tipo_pedido,
					/*37*/-1,/*id_status_agrupacion*/
			/*fin de camnbio Oscar 01.03.2019*/
					/*38*/$id_cajero,/*id de cajero*/
					/*39*/$saldo_favor,/*saldo a favor por devolucion*/
					/*40*/'0'/*( venta validada )oscar 2022*/
					/*A PARTIR DE AQUI SON MODIFICACIONES DE IVAN....
					/*33	-1,
					/*34	-1,
					/*35	0,
					/*36	0*/
					FROM ec_pedidos_back
					WHERE id_pedido=$id_pedido
				)";

		$res=mysql_query($sql);

		if(!$res){
			throw new Exception("No se pudo insertar la nota de venta\n\n".mysql_error()."\n\n".$sql);	
		}

		$id_pedido_r=mysql_insert_id();
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
				(
					SELECT
					null,
					$id_pedido_r,
					id_producto,
					cantidad,
					precio,
					monto,
					iva,
					ieps,
					cantidad_surtida,
					descuento,
					0,/*implementado el 17-1-2018*/
					es_externo,
					id_precio/*implementado el 25.03.2019*/
					FROM ec_pedidos_detalle_back
					WHERE id_pedido=$id_pedido
				)";
		$res=mysql_query($sql);
		
		if(!$res){
			die($sql);
			throw new Exception("No se pudo insertar la nota de venta\n\n" . mysql_error());
		}
	//Insertamos el movimiento de almacen producto x producto
		$sql="SELECT
				pd.id_producto AS id_prod,
				pd.cantidad AS can_s,
				p.es_maquilado AS maquilado,
				pd.es_externo/*Implementado por oscar 07.08.2018*/
				FROM ec_pedidos_detalle_back pd
				LEFT JOIN ec_productos p on pd.id_producto=p.id_productos
				WHERE id_pedido=$id_pedido";
		//echo $sql;
		$res=mysql_query($sql);
		if(!$res){
			die($sql);
			throw new Exception("No se pudo insertar la nota de venta\n\n" . mysql_error());
		}
		$num=mysql_num_rows($res);
		for($i=0;$i<$num;$i++){
			$row=mysql_fetch_assoc($res);
			extract($row);
	//Buscamos el almacen correspondiente
			$sql="	SELECT
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
									VALUES('$id_mov','$dA[0]','$suma','$suma',-1,-1)";
								$ejeSqlAux=mysql_query($sqlAux)or die('ERRROR!!!'.$sqlAux);
								//echo $sqlAux.'<br>';
							}
						}
					}else if($maquilado==0){
						//echo'no maquilado';
			//Insertamos detalle   
						$sql="INSERT INTO ec_movimiento_detalle(id_movimiento, id_producto, cantidad, cantidad_surtida, id_pedido_detalle, id_oc_detalle)
								VALUES('$id_mov','$id_prod',$can_s,$can_s,'-1','-1')";
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
            	//	echo 'pago_interno:'.$pago_interno."|pago_externo:".$pago_externo;
		    /*implementación Oscar 08.08.2018*/
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
					/**/
						$cs.=",id_cajero=".$id_cajero;
					/**/ 
            			if(!mysql_query($cs)){
							throw new Exception("Imposible almacenar registro (pago). <br><br>$cs<br><br>" . mysql_error());
    					}
            		}
    				
            	}//fin de for $ij
            /*fin de cambio*/
            }        
        //capturamos el id
            $id_pago=mysql_insert_id();
		}//fin de for $ix
        
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
        
        if(!$res)
            throw new Exception("Imposible almacenar registro (pago). <br><br>$sql<br><br>" . mysql_error());
        
        $row=mysql_fetch_row($res);
        
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
	mysql_query("commit");
	echo 'ok|'.$id_pedido_r."|";
/*implementacion Oscar 02.11.2018 para validar que no pase de la pantalla de cerrar ventas si el monto de pagos es menor al monto de la venta*/
	$sql="SELECT
				p.total,
				ROUND(SUM(IF(pp.monto is null,0,pp.monto))),
				/*pp.monto,*/
				p.pagado
			FROM ec_pedidos p 
			LEFT JOIN ec_pedido_pagos pp ON p.id_pedido=pp.id_pedido
		WHERE p.id_pedido=$id_pedido_r";
	$eje=mysql_query($sql)or die("Error al comparar detalles de pago y monto de pago!!!");
	$r=mysql_fetch_row($eje);
	if($r[0]>$r[1] && $r[2]==1){//si el monto es mayor a los pagos y la nota está como pagada
		//insertamos el error
		$sql="INSERT INTO sys_bitacora_errores VALUES(null,$user_sucursal,
			'Se guardo apartado como pagado en el pedido $id_pedido_r, folio: $folio Monto: $ $r[0] Pagos: $ $r[1]',now(),$user_id,'',0)";
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
	
/*Implementacion Osaccr 25.06.2019 para insertar la referencia de las devoluciones en el pedido*/
	if(isset($id_devoluciones)){
		$sql="UPDATE ec_pedidos SET id_devoluciones='$id_devoluciones' WHERE id_pedido=$id_pedido_r";
		$eje=mysql_query($sql)or die("Error al actualizar los ids de devolucion para este pedido!!!\n".mysql_error());
	}
/*Fin de cambio Oscar 25.06.2019*/

//echo '|<img src="../include/barcode/barcode.php?filepath=../../img/codigos_barra/'.$folio.'.png&text='.$folio.'&size=60&orientation=horizontal&codeType=Code30&print=true">';
	echo '|'.$folio;//
?>
