<?php
	include('../../../../conectMin.php');
	//flag:'buscador',dato:txt
	$fl=$_POST['flag'];

	if($fl=='buscador'){
		$coinc=$_POST['dato'];
		$sql="SELECT id_movimiento_banco,folio FROM ec_movimiento_banco WHERE folio like '%$coinc%'";
		$eje=mysql_query($sql)or die("Error al consultar coincidencias de movimientos!!!\n".mysql_error());
		echo 'ok|';

		if(mysql_num_rows($eje)<=0){
			die('Sin coincidencias!!!');
		}

		$respuesta='<table style="width:100%;">';
		while($r=mysql_fetch_row($eje)){
			$respuesta.='<tr onclick="carga_movimiento('.$r[0].');">';
				$respuesta.='<td class="opcion_resultado">'.$r[1].'</td>';
			$respuesta.='</tr>';	
		}
		$respuesta.='</table>';
		die($respuesta);
	}

	//flag:'carga_mov',id_mov:id
	if($fl=='carga_mov'){
		$id=$_POST['id_mov'];
		$sql="SELECT id_movimiento_banco,folio,fecha,id_caja,monto,observaciones,id_concepto 
		FROM ec_movimiento_banco WHERE id_movimiento_banco=$id AND id_concepto!=5 AND id_concepto!=6";
		$eje=mysql_query($sql) or die("Error al consultar los datos del movimiento!!!\n".mysql_error());
		$r=mysql_fetch_row($eje);
		die('ok|'.$r[0].'|'.$r[1].'|'.$r[2].'|'.$r[3].'|'.$r[4].'|'.$r[5].'|'.$r[6]);
	}

	if($fl=='inserta'){
		//id:id_mov,conc:concepto,val:valor,observacion:observ,pss:pssword,id_caja:caja_cta
		$id_mov=$_POST['id'];
		$concepto=$_POST['conc'];
		$monto=$_POST['val'];
		$obs=$_POST['observacion'];
		$pass=md5($_POST['pss']);
		$id_caja_cuenta=$_POST['id_caja'];
	//verificamos que el password del usuario sea el correcto
		$sql="SELECT count(*) from sys_users WHERE id_usuario=$user_id AND contrasena='$pass'";
		$eje=mysql_query($sql)or die("Error al consultar coincidencias de contraseña de usuario!!!\n".mysql_error());
		$r=mysql_fetch_row($eje);
		if($r[0]!=1){
			die("La contraseña es incorrecta!!!");
		}
	mysql_query("BEGIN");//declaramos el inicio de la transaccion
	//si es actualizacion
		if($id_mov!=0){
			$sql="UPDATE ec_movimiento_banco SET id_caja=$id_caja_cuenta,id_concepto=$concepto,monto=$monto,observaciones='$obs',id_usuario_modifica='$user_id' WHERE id_movimiento_banco=$id_mov";
			$eje=mysql_query($sql)or die("Error al modificar el movimiento de caja!!!\n".mysql_error());
			mysql_query("COMMIT");
			die('ok');
		}
	//si es incersión

		$sql="SELECT prefijo FROM sys_sucursales WHERE id_sucursal='$user_sucursal'";
		$eje=mysql_query($sql);
		if(!$eje){
			$error=mysql_error();
			mysql_query("ROLLBACK");//cancelamos la transaccion
			die("Error al consultar el prefijo de la sucursal!!!\n".$error);
		}
		$r=mysql_fetch_row($eje);
		$prefijo=$r[0];

		if($concepto==5){//si es traspaso entre cajas
			$arr_caja=explode("~",$id_caja_cuenta);

			$sql="INSERT INTO ec_traspasos_bancos VALUES(null,$arr_caja[0],$arr_caja[1],$monto,'','$obs','$user_id','$user_sucursal',1)";
			$eje=mysql_query($sql);
			if(!$eje){
				$error=mysql_error();
				mysql_query("ROLLBACK");//cancelamos la transaccion
				die("Error al insertar el traspaso en la tabla de traspasos entre cajas o cuentas!!!\n".$error."\n".$sql);
			}
			$id_nvo_mov=mysql_insert_id();
			$es_traspaso=1;
		}

	//insertamos solo el movimeinto de banco o caja
		else{
			$sql="INSERT INTO ec_movimiento_banco VALUES(null,$id_caja_cuenta,-1,$concepto,$user_id,$monto,'',now(),-1,-1,-1,'$obs',-1,0,1)";
			$eje=mysql_query($sql);
			if(!$eje){
				$error=mysql_error();
				mysql_query("ROLLBACK");//cancelamos la transaccion
				die("Error al insertar el movimiento de caja o cuenta!!!\n".$error);
			}
			$id_nvo_mov=mysql_insert_id();
		}
	mysql_query("COMMIT");//autorizamos la transaccion
	//if($id_nvo_mov!='' && $id_mov!=null){
		include('imprimeTicketMov.php');
	//}
	
	die('ok');
	}
?>