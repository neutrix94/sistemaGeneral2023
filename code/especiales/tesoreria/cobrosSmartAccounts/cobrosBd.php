<?php
	include('../../../../conectMin.php');
	$fl=$_POST['flag'];
	/*buscador por folios*/
	if($fl=='buscador'){
		$clave=$_POST['valor'];
		$sql="SELECT 
				id_pedido,
				folio_nv,pagado 
			FROM ec_pedidos 
			WHERE folio_nv = '{$clave}' 
			AND id_sucursal=$user_sucursal";
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
		echo '</table>';
	//implementacion Oscar 2024-02-23 para refrescar id de sesion
		$sql = "SELECT 
			id_sesion_caja,
			hora_fin
		FROM ec_sesion_caja 
		WHERE id_cajero = {$user_id}
		ORDER BY id_sesion_caja DESC
		LIMIT 1";
		$stm = mysql_query( $sql ) or die( "Error al consultar la sesion del cajero : "  . mysql_error());
		if( mysql_num_rows( $stm ) <= 0 ){
			die("|sin_sesion");
		}else{
			$row = mysql_fetch_assoc( $stm );
			if( $row['hora_fin'] != '00:00:00' ){
				die("|sin_sesion");
			}
			die("|{$row['id_sesion_caja']}");
		}
		die('');
	}
	
	if($fl=='carga_datos'){
$CONSULTAS_SQL = array();
		$clave=$_POST['valor'];
		$monto_saldo_a_favor = 0;
		$monto_saldo_tomado = 0;
		$id_venta_origen = 0;
	//consulta si tiene saldo a favor
		$sql = "SELECT
				/*SUM( monto_devolucion_interna + monto_devolucion_externa )*/
				SUM(saldo_a_favor) AS monto_saldo_a_favor,
				id_pedido_original AS id_venta_origen
			FROM ec_pedidos_relacion_devolucion
			WHERE id_pedido_relacionado = {$clave}
			AND id_sesion_caja_pedido_relacionado = 0";
$CONSULTA_SALDO_FAVOR = $sql;
		$tmp = "";
		$stm_1 = mysql_query( $sql ) or die( "Error al consultar relacion de pedidos y devolucion : " . mysql_error() );
		if( mysql_num_rows( $stm_1 ) > 0 ){
			$tmp = mysql_fetch_assoc( $stm_1 );
			$monto_saldo_a_favor = $tmp['monto_saldo_a_favor'];//( $tmp['monto_saldo_a_favor'] == null || $tmp['monto_saldo_a_favor'] == '' ? 0 : $tmp['monto_saldo_a_favor'] == null );
			//var_dump( $tmp );
		//	die(  'Here'.$monto_saldo_a_favor );
		}
$CONSULTAS_SQL[] = array( "CONSULTA_SALDO_FAVOR"=>$CONSULTA_SALDO_FAVOR, "resultado"=>$tmp );
	//consulta si tiene pedido relacionado
		$sql = "SELECT
				id_pedido_original AS id_venta_origen
			FROM ec_pedidos_relacion_devolucion
			WHERE id_pedido_relacionado = {$clave}";
$CONSULTA_VENTA_RELACIONADA = $sql;
$CONSULTAS_SQL[] = array( "CONSULTA_VENTA_RELACIONADA"=>$CONSULTA_VENTA_RELACIONADA );
		$stm_1 = mysql_query( $sql ) or die( "Error al consultar relacion de pedidos y devolucion : " . mysql_error() );
		if( mysql_num_rows( $stm_1 ) > 0 ){
			$tmp = mysql_fetch_assoc( $stm_1 );
			$id_venta_origen = $tmp['id_venta_origen'];
		//var_dump( $tmp );
		}
	//consulta si tiene saldo tomado
		$sql = "SELECT
				/*SUM( monto_devolucion_interna + monto_devolucion_externa )*/
				SUM(saldo_a_favor) AS monto_saldo_tomado,
				id_pedido_original AS id_venta_origen
			FROM ec_pedidos_relacion_devolucion
			WHERE id_pedido_original = {$clave}
			AND id_sesion_caja_pedido_relacionado = 0";
$CONSULTA_SALDO_TOMADO = $sql;
$CONSULTAS_SQL[] = array( "CONSULTA_SALDO_TOMADO"=>$CONSULTA_SALDO_TOMADO );
		$stm_1 = mysql_query( $sql ) or die( "Error al consultar relacion de pedidos y devolucion : " . mysql_error() );
		if( mysql_num_rows( $stm_1 ) > 0 ){
			$tmp = mysql_fetch_assoc( $stm_1 );
			$monto_saldo_tomado = $tmp['monto_saldo_tomado'];//( $tmp['monto_saldo_a_favor'] == null || $tmp['monto_saldo_a_favor'] == '' ? 0 : $tmp['monto_saldo_a_favor'] == null );
			//var_dump( $tmp );
		}

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
				SUM( IF( pp.id_pedido_pago IS NULL , 0, pp.monto ) ) AS pagos_registrados/*Oscar 2023/10/10 or pp.referencia != ''*//*4*/,
				p.total AS total_nota/*5*/
			FROM ec_pedidos p
			LEFT JOIN ec_pedido_pagos pp 
			ON p.id_pedido = pp.id_pedido
		/*Oscar 2023/10/10*/
			WHERE p.id_pedido = {$clave}
			GROUP BY p.id_pedido";
$CONSULTA_PAGOS_PENDIENTES_DE_COBRAR = $sql;
		$eje=mysql_query($sql) or die("Error al consultar los datos del pedido!!!\n".mysql_error());
		$r=mysql_fetch_assoc($eje);

$CONSULTAS_SQL[] = array( "CONSULTA_PAGOS_PENDIENTES_DE_COBRAR"=>$CONSULTA_PAGOS_PENDIENTES_DE_COBRAR, "RESULTADO"=>$r );
		$sql = "SELECT 
				ROUND( total_venta, 2 ) AS total_venta,
				ROUND( monto_venta_mas_ultima_devolucion, 2 ) AS monto_venta_mas_ultima_devolucion
			FROM ec_pedidos_referencia_devolucion
			WHERE id_pedido = {$clave}";//die( $sql );
		$reference_stm = mysql_query( $sql ) or die( "Error al consultar la referencia de la venta y devolucion  : " . mysql_error() );
		$reference_row = mysql_fetch_assoc( $reference_stm );
		$r['total_real'] = round( $r['total_nota'], 2 );
		$r['total_nota'] = $reference_row['monto_venta_mas_ultima_devolucion'];

$CONSULTA_REFERENCIA_DEVOLUCION = $sql;
$CONSULTAS_SQL[] = array( "CONSULTA_REFERENCIA_DEVOLUCION"=>$CONSULTA_REFERENCIA_DEVOLUCION );
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
		$sql="SELECT 
					IF( d.id_devolucion IS NULL, 0, d.id_devolucion ) As id_devolucion,
					ROUND( SUM( IF( d.id_devolucion IS NULL, 0, d.monto_devolucion ) ), 2 ) AS monto_devolucion,
					ROUND( SUM( IF( dp.id_devolucion IS NULL ,0, dp.monto ) ), 2 ) AS pagos_devolucion,
					IF( d.id_devolucion IS NULL, '', d.status ) AS status
				FROM ec_devolucion d
				LEFT JOIN ec_devolucion_pagos dp
				ON dp.id_devolucion = d.id_devolucion 
				WHERE d.id_pedido = {$r['id_venta']} 
					AND d.id_cajero = 0
					AND d.id_sesion_caja = 0
				GROUP BY d.id_devolucion";//die($sql);
$CONSULTA_VERIFICA_STATUS_DEVOLUCION = $sql;
$CONSULTAS_SQL[] = array( "CONSULTA_VERIFICA_STATUS_DEVOLUCION"=>$CONSULTA_VERIFICA_STATUS_DEVOLUCION );
		$eje = mysql_query($sql)or die("Error al consultar las devoluciones relacionadas a esta nota!!!\n".mysql_error().$sql);
		if( mysql_num_rows( $eje ) > 0 ){
			$rd = mysql_fetch_assoc( $eje );
			if( $rd['status'] != 3 && $rd['status'] != '' ){
				die( "No se puede hacer un cobro sobre una nota con devolucion pendiente, finaliza la devolucion y vuelve a intentar! {$rd[1]}" );
			}
		}
		//if( $r['pagos_pendientes'] <= $r['pagos_registrados'] ){
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
					AND d.id_cajero = 0
					AND d.id_sesion_caja = 0
					GROUP BY d.id_pedido";
$CONSULTA_DEVOLUCION_SIN_CAJERO = $sql;
$CONSULTAS_SQL[] = array( "CONSULTA_DEVOLUCION_SIN_CAJERO"=>$CONSULTA_DEVOLUCION_SIN_CAJERO );
//die( $sql );
			$return_stm = mysql_query( $sql ) or die( "Error al consultar si hay una devolucion pendiente : " . mysql_error() );
			if( mysql_num_rows( $return_stm ) > 0 ){
				$return_row = mysql_fetch_assoc( $return_stm );
				if( $return_row['observaciones'] == 'Dinero regresado al cliente' 
					&& $return_row['monto_devolucion'] > $return_row['monto_pagos_devolucion'] ){
					$pending_ammount = $return_row['monto_devolucion'] - $return_row['monto_pagos_devolucion'];
				}
			}
//die( $sql );
			//die( "was_payed|Esta nota ya fue pagada exitosamente!" );
		//}


//verifica si hay una devolucion ligada al pedido sin cajero
		$sql = "SELECT 
					SUM( dp.monto ) AS monto_pagos_devolucion
				FROM ec_devolucion d 
				LEFT JOIN ec_devolucion_pagos dp
				ON d.id_devolucion = dp.id_devolucion
				WHERE d.id_pedido = '{$clave}'
				GROUP BY d.id_pedido";
$CONSULTA_DEVOLUCION_RELACIONADA = $sql;
		$return_stm = mysql_query( $sql ) or die( "Error al consultar pagos de devolucion : " . mysql_error() );
		$return_row_2 = "";
		if( mysql_num_rows( $return_stm ) > 0 ){
			$return_row_2 = mysql_fetch_assoc( $return_stm );
			$return_row['monto_pagos_devolucion'] = $return_row_2['monto_pagos_devolucion'];
			/*if( $return_row['observaciones'] == 'Dinero regresado al cliente' 
				&& $return_row['monto_devolucion'] > $return_row['monto_pagos_devolucion'] ){
				$pending_ammount = $return_row['monto_devolucion'] - $return_row['monto_pagos_devolucion'];
			}*/
		}
$CONSULTAS_SQL[] = array( "CONSULTA_DEVOLUCION_RELACIONADA"=>$CONSULTA_DEVOLUCION_RELACIONADA, "resultado"=>$return_row_2 );

		$return_row['monto_devolucion'] = ( $return_row['monto_devolucion'] == '' || $return_row['monto_devolucion'] == null ? 0 : $return_row['monto_devolucion'] );
		$return_row['monto_pagos_devolucion'] = ( $return_row['monto_pagos_devolucion'] == '' || $return_row['monto_pagos_devolucion'] == null ? 0 : $return_row['monto_pagos_devolucion'] );
		$r['pagos_pendientes'] = $r['total_nota'] - ( $r['pagos_registrados'] - $return_row['monto_pagos_devolucion'] - $monto_saldo_tomado ) - $return_row['monto_devolucion'] - $monto_saldo_a_favor;
		//die( 'here' );
		if($rd[0]==''){
			$rd[0]=0;
		}
		$r['pagos_registrados'] = $r['pagos_registrados'] - $return_row['monto_pagos_devolucion'];
		$FORMULA_PAGOS_COBRADOS = "{$r['pagos_registrados']} = {$r['pagos_registrados']} - {$return_row['monto_pagos_devolucion']}";
		$resp = json_encode( 
				array( 'id_venta'=>$r['id_venta'], 
					'folio_venta'=>$r['folio_venta'], 
					'total_venta'=>round( $r['total_nota'], 2 ),
					'pagos_cobrados'=>round( $r['pagos_registrados'], 2 ), 
					'FORMULA_PAGOS_COBRADOS'=>$FORMULA_PAGOS_COBRADOS,
					
					'id_devolucion'=>$return_row['id_devolucion'], 
					'monto_devolucion'=>round( $return_row['monto_devolucion'], 2 ), 
					'monto_pagos_devolucion'=>round( $return_row['monto_pagos_devolucion'], 2 ),
					'monto_saldo_a_favor'=>round( $monto_saldo_a_favor, 2 ),
					'pagos_pendientes'=>( ($r['pagos_pendientes'] >= -1 && $r['pagos_pendientes'] <= 1) ? '0' : $r['pagos_pendientes'] ), 
					'FORMULA_PAGOS_PENDIENTES'=>"(pagos_pendientes){$r['pagos_pendientes']} = (total_nota){$r['total_nota']} - ( (pagos_registrados){$r['pagos_registrados']} - (monto_pagos_devolucion){$return_row['monto_pagos_devolucion']} - (monto_saldo_tomado){$monto_saldo_tomado} ) - (monto_devolucion){$return_row['monto_devolucion']} - (monto_saldo_a_favor){$monto_saldo_a_favor}",
					
					'total_real'=>$r['total_real'],
					'id_venta_origen'=>$id_venta_origen,
					'monto_saldo_tomado'=>$monto_saldo_tomado,
					'CONSULTAS'=>$CONSULTAS_SQL
				)
			);
		die( "ok|{$resp}" );
	}
	
	if($fl=='cobrar'){
		mysql_query("BEGIN");//marcamos el inicio de la transacción
		$id_pedido=$_POST['id_venta'];
		$tarjetas=$_POST['tar'];	
		$cheques=$_POST['chq'];	
		$monto_efectivo=$_POST['efe'];
		$recibido=$_POST['recib'];
		$cambio=$_POST['camb'];
		$monto_total_pagos=0;
		$session_id = $_POST['session_id'];
	//actualiza devolucion si es el caso
		$sql_dev = "SELECT id_devolucion FROM ec_devolucion WHERE id_pedido = {$id_pedido} AND id_sesion_caja = 0";

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
		}

		$sql = "UPDATE ec_pedidos_referencia_devolucion SET monto_venta_mas_ultima_devolucion = total_venta WHERE id_pedido = {$id_pedido}";
		$stm_update = mysql_query( $sql ) or die( "Error al actualizar el campo total_venta_mas_ultima_devolucion : " . mysql_error() );
		mysql_query("COMMIT");//autorizamos la transacción
	die('ok|');
	}
?>