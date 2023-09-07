<?php
	include('../../../../conectMin.php');
	$fl=$_POST['flag'];
	/*buscador por folios*/
	if($fl=='buscador'){
		$clave=$_POST['valor'];
		$sql="SELECT id_pedido,folio_nv,pagado FROM ec_pedidos WHERE folio_nv LIKE '%$clave%' AND id_sucursal=$user_sucursal";
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
				p.id_pedido,
				p.folio_nv,
				SUM(IF(pp.id_pedido_pago IS NULL OR pp.id_cajero!=0,0,pp.monto)) as pagosPendientes,
				REPLACE(p.id_devoluciones,'~',',') as idsDevoluciones
			FROM ec_pedidos p
			LEFT JOIN ec_pedido_pagos pp ON p.id_pedido=pp.id_pedido
			WHERE p.id_pedido=$clave";
		$eje=mysql_query($sql) or die("Error al consultar los datos del pedido!!!\n".mysql_error());
		$r=mysql_fetch_row($eje);
	//checamos si hay devoluciones que dependan de este pedido y no esten pagadas
		$condicion_devoluciones='IN('.$r[3].')';
		//die($condicion_devoluciones);
		$sql="SELECT SUM(IF(id_devolucion_pago IS NULL OR id_cajero!=0,0,monto)) FROM ec_devolucion_pagos WHERE id_devolucion $condicion_devoluciones";
		$eje=mysql_query($sql)or die("Error al consultar las devoluciones relacionadas a esta nota!!!\n".mysql_error().$sql);
		$rd=mysql_fetch_row($eje);
		if($rd[0]==''){$rd[0]=0;}
		die('ok|'.$r[0].'|'.$r[1].'|'.$r[2].'|'.$rd[0]);
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
	//die('pedido:'.$monto_efectivo);
	//efectivo
		if( $monto_efectivo!='' && $monto_efectivo!=0 ){
		//insertamos el pago en efectivo
			$sql="INSERT INTO ec_cajero_cobros VALUES(null,$id_pedido,$user_id,-1,-1,$monto_efectivo,now(),now(),'',1)";
			$eje=mysql_query($sql);
			if(!$eje){
				$error=mysql_error();
				mysql_query("ROLLBACK");//cancelamos la transacción
				die("Error al insertar cobro en efectivo\n".mysql_error());
			}
			$monto_total_pagos+=$monto_efectivo;
		}

	//pagos con tarjetas
		//die($tarjetas);
		$arr_tarjetas=explode("°",$tarjetas);
		for($i=0;$i<sizeof($arr_tarjetas)-1;$i++){
			$arr=explode("~",$arr_tarjetas[$i]);
			//echo 'enttra';
			$sql="INSERT INTO ec_cajero_cobros VALUES(null,$id_pedido,$user_id,'$arr[0]',-1,'$arr[1]',now(),now(),'',1)";
			$eje=mysql_query($sql);
			if(!$eje){
				$error=mysql_error();
				mysql_query("ROLLBACK");//cancelamos la transacción
				die("Error al insertar los cobros con tarjetas\n".$error."\n".$sql);
			}
			$monto_total_pagos+=$arr[1];
		}//fin de for i

	//pagos con cheque/transferencia
		$arr_cheques=explode("°",$cheques);
		for($i=0;$i<sizeof($arr_cheques)-1;$i++){
			$arr=explode("~",$arr_cheques[$i]);
			$sql="INSERT INTO ec_cajero_cobros VALUES(null,$id_pedido,$user_id,-1,$arr[0],$arr[1],now(),now(),'$arr[2]',1)";
			$eje=mysql_query($sql);
			if(!$eje){
				$error=mysql_error();
				mysql_query("ROLLBACK");//cancelamos la transacción
				die("Error al insertar los cobros con cheques\n".mysql_error());
			}
			$monto_total_pagos+=$arr[1];
		}//fin de for i
	/*actualizamos el id de cajero que cobro el pago*/
		$sql="UPDATE ec_pedidos SET id_cajero=$user_id WHERE id_pedido=$id_pedido";
		$eje=mysql_query($sql);
		if(!$eje){
			$error=mysql_error();
			mysql_query("ROLLBACK");//cancelamos la transacción
			die("Error al actualizar el pedido para este cajero\n".mysql_error());
		}
	/*actualizamos el id de cajero que cobro el pago*/
		$sql="UPDATE ec_pedido_pagos SET id_cajero=$user_id,fecha=now(),hora=now() WHERE id_pedido=$id_pedido AND id_cajero=0";
		$eje=mysql_query($sql);
		if(!$eje){
			$error=mysql_error();
			mysql_query("ROLLBACK");//cancelamos la transacción
			die("Error al actualizar el pedido para este cajero\n".mysql_error());
		}
	/*actualizamos los pagos de devoluciones que pertenezcan al pedido*/
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
		include('ticket_pagos.php');
	die('ok|');
	}
?>