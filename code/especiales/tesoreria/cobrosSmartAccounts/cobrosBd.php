<?php
	include('../../../../conectMin.php');
	$fl=$_POST['flag'];
	/*buscador por folios*/
	if($fl=='buscador'){
		$clave=$_POST['valor'];
		$sql="SELECT id_pedido,folio_nv,pagado FROM ec_pedidos WHERE folio_nv = '{$clave}' AND id_sucursal=$user_sucursal";
		$eje=mysql_query($sql) or die("Error al buscar coincidencias por folio!!!\n".mysql_error());
		echo 'ok|';
		if(mysql_num_rows($eje)<=0){
			die("Sin coincidencias!!!");
		}
		echo '<table width="100%" border="0">';
		$c=0;
	//listamos resultados
		while($r=mysql_fetch_row($eje)){
			$c++;//incrementamos contador
			echo '<tr id="opc_'.$c.'" tabindex="'.$c.'" onclick="carga_pedido('.$r[0].','.$r[2].');" onkeyup="valida_tca_opc(event,'.$c.');" onfocus="marca('.$c.');" onblur="desmarca('.$c.');">';
				echo '<td class="opc_buscador">'.$r[1].'</td>';
			echo '<tr>';
		}
		die('</table>');
	}
//flag:'carga_datos',valor:id
	if($fl=='carga_datos'){
		$clave=$_POST['valor'];
	//checamos los pagos pendientes de cobrar
		$sql="SELECT
				p.id_pedido AS id_venta,/*0*/
				p.folio_nv AS folio_venta,/*1*/
				IF( p.pagado = 0 AND pp.id_pedido_pago IS NULL, p.monto_pago_inicial, p.total ) AS pagos_pendientes,/*OR pp.id_cajero != 0*/
				REPLACE( p.id_devoluciones, '~', ',' ) AS devoluciones_relacionadas,/*3*/
				/*( SELECT
					SUM( IF( cc.id_cajero_cobro IS NULL, 0, cc.monto ) )
					FROM ec_cajero_cobros cc
					WHERE cc.id_pedido = p.id_pedido 
				)*/ 
				SUM( IF( pp.id_pedido_pago IS NULL or pp.referencia != '', 0, pp.monto ) ) AS pagos_registrados/*Oscar 2023/10/10*//*4*/,
				p.total AS total_nota/*5*/
			FROM ec_pedidos p
			LEFT JOIN ec_pedido_pagos pp 
			ON p.id_pedido = pp.id_pedido
		/*Oscar 2023/10/10*/
			WHERE p.id_pedido = {$clave}
			GROUP BY p.id_pedido";
		$eje=mysql_query($sql) or die("Error al consultar los datos del pedido!!!\n".mysql_error());
		$r=mysql_fetch_assoc($eje);
	//checamos si hay devoluciones que dependan de este pedido y no esten pagadas
		$condicion_devoluciones = "IN('{$r['devoluciones_relacionadas']}')";
		$caso = 1;//no cobrada
		$tiene_devolucion = 0;
		if( $r['pagos_registrados'] == '' || $r['pagos_registrados'] == null ){
			$r['pagos_registrados'] = '0';
		}
		if( $r['pagos_registrados'] < $r['total_nota'] && $r['pagos_registrados'] > 0 ){//pagos < total_venta ( pagada parcialmente )
			$caso = 2;
		}else if( $r['pagos_registrados'] >= $r['total_nota'] ){//pagos >= total_venta ( pagada completamente )
			$caso = 3;
			if( $r['pagos_registrados'] > $r['total_nota'] ){
				$tiene_devolucion = 1;
			}
		}
	/*verifica si tiene una devolucion relacionada y el status de esta*/
		//die($condicion_devoluciones);
		$sql="SELECT 
					IF( d.id_devolucion IS NULL, 0, d.id_devolucion ) As id_devolucion,
					ROUND( SUM( IF( d.id_devolucion IS NULL, 0, d.monto_devolucion ) ) ) AS monto_devolucion,
					ROUND( SUM( IF( dp.id_devolucion IS NULL ,0, dp.monto ) ) ) AS pagos_devolucion,
					IF( d.id_devolucion IS NULL, '', d.status ) AS status
				FROM ec_devolucion d
				LEFT JOIN ec_devolucion_pagos dp
				ON dp.id_devolucion = d.id_devolucion 
				WHERE d.id_pedido = {$r['id_venta']} 
				GROUP BY d.id_devolucion";//die($sql);
		$eje = mysql_query($sql)or die("Error al consultar las devoluciones relacionadas a esta nota!!!\n".mysql_error().$sql);
		if( mysql_num_rows( $eje ) > 0 ){
			$rd = mysql_fetch_assoc( $eje );
			if( $rd['status'] != 3 && $rd['status'] != '' ){
				die( "No se puede hacer un cobro sobre una nota con devolucion pendiente, finaliza la devolucion y vuelve a intentar! {$rd[1]}" );
			}
		}
		if( $r['pagos_pendientes'] <= $r['pagos_registrados'] ){
//verifica si hay una devolucion ligada al pedido sin cajero
			$sql = "SELECT 
						d.id_devolucion AS id_devolucion,
						d.folio AS folio_devolucion, 
						SUM( dp.monto ) AS monto_pagos_devolucion,
						d.observaciones,
						SUM( d.monto_devolucion ) AS monto_devolucion
					FROM ec_devolucion d 
					LEFT JOIN ec_devolucion_pagos dp
					ON d.id_devolucion = dp.id_devolucion
					WHERE d.id_pedido = '{$clave}'
					/*AND d.id_cajero = 0
					AND d.id_sesion_caja = 0*/
					GROUP BY d.id_pedido";
//die( $sql );
			$return_stm = mysql_query( $sql ) or die( "Error al consultar si hay una devolucion pendiente : " . mysql_error() );
			if( mysql_num_rows( $return_stm ) > 0 ){
				$return_row = mysql_fetch_assoc( $return_stm );
				if( $return_row['observaciones'] == 'Dinero regresado al cliente' 
					&& $return_row['monto_devolucion'] > $return_row['monto_pagos_devolucion'] ){
					$pending_ammount = $return_row['monto_devolucion'] - $return_row['monto_pagos_devolucion'];
					//die( 'here' );
					//die( "ok|{$return_row[0]}|{$return_row[1]}|0|{$pending_ammount}" );//{$return_row[2]}
					$resp = json_encode( array( 'id_venta'=>$r['id_venta'], 'folio_venta'=>$r['folio_venta'], 
						'total_venta'=>$r['total_nota'],'pagos_cobrados'=>$r['pagos_registrados'], 
						'id_devolucion'=>$return_row['id_devolucion'], 'monto_devolucion'=>$return_row['monto_devolucion'], 
						'monto_pagos_devolucion'=>$return_row['monto_pagos_devolucion'] ) );
					die( "ok|{$resp}" );//{$return_row[2]}
				}
			}
			//die( "was_payed|Esta nota ya fue pagada exitosamente!" );
		}
		$r['pagos_pendientes'] = ( $r['pagos_pendientes'] - $r['pagos_registrados'] );
		//die( 'here' );
		if($rd[0]==''){$rd[0]=0;}
		

	//caso 1 ( no cobrada )
		switch ( $caso ) {
			case 1:
				//$return_row['monto_devolucion'] = $return_row['monto_devolucion'] - $return_row['monto_pagos_devolucion'];
				$r['pagos_registrados'] = $r['pagos_registrados'] - $return_row['monto_pagos_devolucion'];
					$resp = json_encode( array( 'id_venta'=>$r['id_venta'], 'folio_venta'=>$r['folio_venta'], 
						'total_venta'=>$r['total_nota'],'pagos_cobrados'=>$r['pagos_registrados'], 
						'id_devolucion'=>$return_row['id_devolucion'], 'monto_devolucion'=>$return_row['monto_devolucion'], 
						'monto_pagos_devolucion'=>$return_row['monto_pagos_devolucion'] ) );
					die( "ok|{$resp}" );//{$return_row[2]}
			break;
			
			case 2:
				//$return_row['monto_devolucion'] = $return_row['monto_devolucion'] - $return_row['monto_pagos_devolucion'];
				$r['pagos_registrados'] = $r['pagos_registrados'] - $return_row['monto_pagos_devolucion'];
					$resp = json_encode( array( 'id_venta'=>$r['id_venta'], 'folio_venta'=>$r['folio_venta'], 
						'total_venta'=>$r['total_nota'],'pagos_cobrados'=>$r['pagos_registrados'], 
						'id_devolucion'=>$return_row['id_devolucion'], 'monto_devolucion'=>$return_row['monto_devolucion'], 
						'monto_pagos_devolucion'=>$return_row['monto_pagos_devolucion'] ) );
					die( "ok|{$resp}" );//{$return_row[2]}
			break;
			case 3:
				//$return_row['monto_devolucion'] = $return_row['monto_devolucion'] - $return_row['monto_pagos_devolucion'];
					$r['pagos_registrados'] = $r['pagos_registrados'] - $return_row['monto_pagos_devolucion'];
					$resp = json_encode( array( 'id_venta'=>$r['id_venta'], 'folio_venta'=>$r['folio_venta'], 
						'total_venta'=>$r['total_nota'],'pagos_cobrados'=>$r['pagos_registrados'], 
						'id_devolucion'=>$return_row['id_devolucion'], 'monto_devolucion'=>$return_row['monto_devolucion'], 
						'monto_pagos_devolucion'=>$return_row['monto_pagos_devolucion'] ) );
					die( "ok|{$resp}" );//{$return_row[2]}
				//die('ok|'.$r['id_venta'].'|'.$r['folio_venta'].'|'.$r['pagos_pendientes'].'|0');
			break;
			
			default:
				die( "Error : no entra en ningun caso controlado!" );
			break;
		}
	}
//flag:'cobrar',efe:efectivo,camb:cambio,tar:tarjetas,chq:cheques,id_venta:id_corte
//die($fl);
	if($fl=='cobrar'){
		mysql_query("BEGIN");//maarcamos el inicio de la transacción
		$id_pedido=$_POST['id_venta'];
		$tarjetas=$_POST['tar'];	
		$cheques=$_POST['chq'];	
		$monto_efectivo=$_POST['efe'];
		$recibido=$_POST['recib'];
		$cambio=$_POST['camb'];
		$monto_total_pagos=0;
		$session_id = $_POST['session_id'];
	//die('pedido:'.$monto_efectivo);
	//efectivo
		/*if( $monto_efectivo!='' && $monto_efectivo!=0 ){
		//insertamos el pago en efectivo
			$sql="INSERT INTO ec_cajero_cobros ( id_cajero_cobro, id_pedido, id_cajero, id_afiliacion, id_banco, 
					monto, fecha, hora, observaciones, sincronizar ) 
				VALUES(null,$id_pedido,$user_id,-1,-1,$monto_efectivo,now(),now(),'',1)";
			$eje=mysql_query($sql);
			if(!$eje){
				$error=mysql_error();
				mysql_query("ROLLBACK");//cancelamos la transacción
				die("Error al insertar cobro en efectivo\n".mysql_error());
			}
			$monto_total_pagos+=$monto_efectivo;
		}*/

	//pagos con tarjetas
		//die($tarjetas);
		/*$arr_tarjetas=explode("°",$tarjetas);
		for($i=0;$i<sizeof($arr_tarjetas)-1;$i++){
			$arr=explode("~",$arr_tarjetas[$i]);
			//echo 'enttra';
			$sql="INSERT INTO ec_cajero_cobros ( id_cajero_cobro, id_pedido, id_cajero, id_afiliacion, id_banco, 
					monto, fecha, hora, observaciones, sincronizar ) 
				VALUES(null,$id_pedido,$user_id,'$arr[0]',-1,'$arr[1]',now(),now(),'',1)";
			$eje=mysql_query($sql);
			if(!$eje){
				$error=mysql_error();
				mysql_query("ROLLBACK");//cancelamos la transacción
				die("Error al insertar los cobros con tarjetas\n".$error."\n".$sql);
			}
			$monto_total_pagos+=$arr[1];
		}//fin de for i*/

	/*pagos con cheque/transferencia
		$arr_cheques=explode("°",$cheques);
		for($i=0;$i<sizeof($arr_cheques)-1;$i++){
			$arr=explode("~",$arr_cheques[$i]);
			$sql="INSERT INTO ec_cajero_cobros ( id_cajero_cobro, id_pedido, id_cajero, id_afiliacion, id_banco, 
					monto, fecha, hora, observaciones, sincronizar ) 
				VALUES(null,$id_pedido,$user_id,-1,$arr[0],$arr[1],now(),now(),'$arr[2]',1)";
			$eje=mysql_query($sql);
			if(!$eje){
				$error=mysql_error();
				mysql_query("ROLLBACK");//cancelamos la transacción
				die("Error al insertar los cobros con cheques\n".mysql_error());
			}
			$monto_total_pagos+=$arr[1];
		}//fin de for i*/
	/*actualizamos el id de cajero que cobro el pago
		$sql="UPDATE ec_pedidos SET id_cajero = {$user_id} WHERE id_pedido = {$id_pedido}";
		$eje=mysql_query($sql);
		if(!$eje){
			$error=mysql_error();
			mysql_query("ROLLBACK");//cancelamos la transacción
			die("Error al actualizar el pedido para este cajero\n".mysql_error());
		}*/
	/*actualizamos el id de cajero que cobro el pago
		$sql="UPDATE ec_pedido_pagos SET id_cajero = {$user_id}, fecha = now(), hora = now() 
		WHERE id_pedido = {$id_pedido} AND id_cajero = 0";
		$eje=mysql_query($sql);
		if(!$eje){
			$error=mysql_error();
			mysql_query("ROLLBACK");//cancelamos la transacción
			die("Error al actualizar el pedido para este cajero\n".mysql_error());
		}*/
	//actualiza devolucion si es el caso
		$sql_dev = "SELECT id_devolucion FROM ec_devolucion WHERE id_pedido = {$id_pedido} AND id_sesion_caja = 0";
		//die( $sql );
		$stm_dev = mysql_query( $sql_dev ) or die( "Error al consultar las devoluciones pendientes : " . mysql_error() );
	//
		while ( $row_dev = mysql_fetch_assoc( $stm_dev ) ) {
			$sql_dev = "UPDATE ec_devolucion SET id_cajero = {$user_id}, id_sesion_caja = {$session_id} 
			WHERE id_devolucion = {$row_dev['id_devolucion']}";
			//die( $sql_dev );
			$stm_update = mysql_query( $sql_dev ) or die( "Error al actualizar la sesion de caja de devolucion : " . mysql_error() );
			$sql_dev = "UPDATE ec_devolucion_pagos SET id_cajero = {$user_id}, id_sesion_caja = {$session_id} 
			WHERE id_devolucion = {$row_dev['id_devolucion']} AND id_cajero = 0 AND id_sesion_caja = 0";
			$stm_update = mysql_query( $sql_dev ) or die( "Error al actualizar la sesion de caja de ddevolucion pago : " . mysql_error() );
			//die( "here" );
		}

	/*actualizamos los pagos de devoluciones que pertenezcan al pedido
		$sql="SELECT
				REPLACE(p.id_devoluciones,'~',',') as idsDevoluciones
			FROM ec_pedidos p
			WHERE p.id_pedido=$id_pedido";
		$eje=mysql_query($sql) or die("Error al consultar los ids de devoluciones!!!\n".mysql_error());
		$r=mysql_fetch_row($eje);
	//checamos si hay devoluciones que dependan de este pedido y no esten pagadas
		$condicion_devoluciones='IN('.$r[0].')';
		//die($condicion_devoluciones);
		$sql="UPDATE ec_devolucion_pagos SET id_cajero=$user_id,fecha=now(),hora=now() WHERE id_cajero=0 AND id_devolucion $condicion_devoluciones";
		$eje=mysql_query($sql)or die("Error al actualizar pagos de las devoluciones relacionadas a esta nota!!!\n".mysql_error().$sql);
		mysql_query("COMMIT");//autorizamos la transacción
		include('ticket_pagos.php');*/
		mysql_query("COMMIT");//autorizamos la transacción
	die('ok|');
	}
?>