<?php
//creamos menu para evitar duplicados
	extract($_GET);
//si la variable llega desde ajax
	if($id_transfer_ajax!=''){
		menu(1,$id_transfer_ajax);
	}else{
	}


function menu($opcion,$dato,$flag){
	//echo "entra al menu\nflag: ".$flag;
	if($opcion==''){
		die('Error al accesar al menu de sinconizacion de Transferencias');
		return false;
	}else{
		include('conexionSincronizar.php');
		$verificar="SELECT en_proceso FROM ec_sincronizacion WHERE id_sincronizacion=2";
		$verif=mysql_query($verificar,$linea);
		if(!$verif){
			//die("Error al checar estado de Servidor");
			return "Error al checar estado de Servidor";
		}
		$r=mysql_fetch_row($verif);
		if($r[0]==1){
			return 'servidor ocupado';
		}
		switch($opcion){
		//si es subir transferencia
			case 1:
				subeTransfer($dato,$flag);
				//return false;
			break;	
		//si es actualizar transferencia
			case 2:
				actualizaTransLinea($dato);
			break;
		//creamos transferencvia en base a resolucion
			case 3:
				$resolucion=creaResolucion($dato);
			break;
		}//fin de switch
	}
	return 'ok';
}
function creaResolucion(){//recibe id
	return 'ok';

}
function subeTransfer($idT,$flag){
	require('conexionSincronizar.php');//incluimos libreria de conexion para sincronizacion
//consultamos la ultima actualizacion registrada localmente para no ser duplicada en la sincronizacion

//echo 'pasa aqui en subirTransfer';
	$noDuplicar=marcaSincronizacion($local,2);
//extraemos datos de la transferencia localmente
//	echo "FLAG:\n".$flag;
	if($flag==1){
//		echo 'el flag es 1';
		$res=',es_resolucion ';
		$resDato=",'1'";
	}
	$sqlT="SELECT id_transferencia,
	/*1*/	id_usuario,
	/*2*/	folio,
	/*3*/	fecha,
	/*4*/	hora,
	/*5*/	id_sucursal_origen,
	/*6*/	id_sucursal_destino,
	/*7*/	observaciones,
	/*8*/	id_razon_social_venta,
	/*9*/	id_razon_social_compra,
	/*10*/	facturable,
	/*11*/	porc_ganancia,
	/*12*/	id_almacen_origen,
	/*13*/	id_almacen_destino,
	/*14*/	id_tipo,
	/*15*/	id_estado,
	/*16*/	id_sucursal"
	/*17*/	.$res."
			FROM ec_transferencias
			WHERE id_transferencia=$idT";
	$ejecutaT=mysql_query($sqlT,$local);
	if(!$ejecutaT){
		die('Error al consultar datos desde la bd local'.$sqlT);
	}
//checamos numero de resultados
	$nT=mysql_num_rows($ejecutaT);
//si el numero de registros es mayor a cero insertamos datos de la transferencia en linea
	if($nT<=0){
		ECHO 'NO HAY RESULTADOS POR SUBIR';//si no hay resultados no hacemos nada
		return false;
	}
/*
//ponemos en modo ocupado al servidor
	$ocupa=ocuparServidor($linea);
	if($ocupa!='ok'){  
		mysql_query('rollback',$local);
		mysql_query('rollback',$linea);
		die("Error al poner servidor en linea en proceso de sincronizacion");
	}
*/
//marcamos inicio de transacciones
	mysql_query("BEGIN",$linea);
	mysql_query("BEGIN",$local);

//llamamos metodo para actualizar en proceso de sincronizacion de transferencia a activo
	$inicia=marcaInicio($local,2);
	if($inicia!='actualizado'){
		$libera=liberarServidor($linea);
		mysql_query("ROLLBACK",$local);
		mysql_query("ROLLBACK",$linea);
		die('Error al poner en sincronizacion de transferencias en proceso'.$inicia);
	}
		while($datosT=mysql_fetch_row($ejecutaT)){
			$generaT="INSERT INTO ec_transferencias
								(id_usuario,folio,fecha,hora,id_sucursal_origen,id_sucursal_destino,observaciones,
								id_razon_social_venta,id_razon_social_compra,facturable,porc_ganancia,id_almacen_origen,id_almacen_destino,
								id_tipo,id_estado,id_sucursal".$res.")
							VALUES('$datosT[1]','$datosT[2]','$datosT[3]','$datosT[4]','$datosT[5]','$datosT[6]','$datosT[7]',
									'$datosT[8]','$datosT[9]','$datosT[10]','$datosT[11]','$datosT[12]','$datosT[13]','$datosT[14]','1','$datosT[16]'".$resDato.")";
//echo $generaT;
			$subeT=mysql_query($generaT,$linea);
			if(!$subeT){
				$libera=liberarServidor($linea);
				mysql_query("ROLLBACK",$linea);
				mysql_query("ROLLBACK",$local);
				die('Error al insertar la transferencia en linea<br>'.$generaT.'<br>'.mysql_error($linea));
			}
			/*
			mysql_query('commit',$local);
			mysql_query('commit',$linea);
			die('ok');
			*/
	//guardamos el id global de la transferencia
		$nuevo=mysql_insert_id($linea);
		$transfer++;//incrementamos contador de transferencias subidas
		//extraemos detalle de transferencia localmente
			$generaDetalleT="SELECT
								id_producto_or,
								id_producto_de,
								cantidad,
								id_presentacion,
								cantidad_presentacion,
								cantidad_salida,
								cantidad_salida_pres,
								cantidad_entrada,
								cantidad_entrada_pres,
								resolucion,
								referencia_resolucion
							FROM ec_transferencia_productos
							WHERE id_transferencia=$datosT[0]";
								///echo $generaDetalleT;
			$ejecutaDetalleT=mysql_query($generaDetalleT,$local);
			if(!$ejecutaDetalleT){
				$libera=liberarServidor($linea);
				mysql_query("ROLLBACK",$linea);
				mysql_query("ROLLBACK",$local);
				die('Error al extraer detalles de la transferencia<br>'.$generaDetalleT.mysql_error($local));
			}
			$detallesT=0;
			while($ro=mysql_fetch_row($ejecutaDetalleT)){
				$sqlDeTrans="INSERT INTO ec_transferencia_productos
								SET	
									id_transferencia=$nuevo,
									id_producto_or='".$ro[0]."',
									id_producto_de='".$ro[1]."',
									cantidad='".$ro[2]."',
									id_presentacion='".$ro[3]."',
									cantidad_presentacion='".$ro[4]."',
									cantidad_salida='".$ro[5]."',
									cantidad_salida_pres='".$ro[6]."',
									cantidad_entrada='".$ro[7]."',
									cantidad_entrada_pres='".$ro[8]."',
									resolucion='".$ro[9]."',
									referencia_resolucion='".$ro[10]."'";
				$subeDeTrans=mysql_query($sqlDeTrans,$linea);
				if(!$subeDeTrans){
					$libera=liberarServidor($linea);
					mysql_query("ROLLBACK",$linea);
					mysql_query("ROLLBACK",$local);
					die("Error al insertar los movimientos\n".$sqlDeTrans."\n".mysql_error($linea));
				}
				$detallesT++;//incrementamos contador de detales insertados en linea
			}//fin de while
			
		//actualizamos la transferencia a estado 3 para activar el trigger y hacer movimientos (SALIDA DE TRANSFERENCIA)
			$actSt="UPDATE ec_transferencias SET id_estado=3 WHERE id_transferencia=$nuevo";
			$ejeActSt=mysql_query($actSt,$linea);
			if(!$ejeActSt){
				$libera=liberarServidor($linea);
				mysql_query("ROLLBACK",$linea);
				mysql_query("ROLLBACK",$local);
				die("Error al actualizar estado de transferencia a SALIDA DE TRANSFERENCIA en linea\n".mysql_error($linea)."\n".$actSt);
			}
			//actualizamos id global de la transferencia localmente
			$actGlobal="UPDATE ec_transferencias SET id_global=$nuevo, ultima_sincronizacion='$noDuplicar' WHERE id_transferencia=$datosT[0]";
			$ejeActGlob=mysql_query($actGlobal,$local);
		
			if(!$ejeActGlob){
				$libera=liberarServidor($linea);
				mysql_query("ROLLBACK",$linea);
				mysql_query("ROLLBACK",$local);
				die("El status de la transferencia no fue actualizado como SALIDA DE TRANSFERENCIA en linea\n".mysql_error($local)."\n".$actSt);
			}
		}//fin de WHILE para insertar o actualizar transferencias en linea
	//actualizamos fecha de ultima sincronizacion de transferencias
			$campo='alta';
			$nuevaFecha=sacaFecha($linea,$campo,$nuevo);
			$final=cierraTrans($local,2,$nuevaFecha);
			if($final!='cerrado'){
				$libera=liberarServidor($linea);
				mysql_query("ROLLBACK",$local);
				mysql_query("ROLLBACK",$linea);
				die('Error al cierre de transferencia');
			}else{
				mysql_query("COMMIT",$linea)or die('Error al guardar transacciones en linea<br>'.mysql_error($linea));
				mysql_query("COMMIT",$local)or die('Error al guardar transacciones en linea<br>'.mysql_error($local));
				$libera=liberarServidor($linea);
				if($libera=='ok'){
					echo 'SI';
					return 'ok';//si no hay fallas retornamos OK	
				}else{
					die('Error al finalizar la transferencia!!!');
				}
			}
	}//fin de funcion
function verificarServidor(){
//checamos que el servidor este disponible
	$verificar="SELECT en_proceso FROM ec_sincronizacion WHERE id_sincronizacion=2";
	$verif=mysql_query($verificar,$linea);
	if(!$verif){
		return false;
		//die("Error al checar estado de Seridor");
	}
	$r=mysql_fetch_row($verif);
	if($r[0]==1){
		return 'servidor ocupado';
	}else{
		return 'servidor libre';
	}
}

function ocuparServidor($linea){
//Aqui ponemos servidor en ocupado
	$ocu="UPDATE ec_sincronizacion SET en_proceso=1 WHERE id_sincronizacion=2";
	$ocupa=mysql_query($ocu,$linea);
	if(!$ocupa){
		mysql_query('rollback',$local);
		mysql_query('rollback',$linea);
		return 'no';
	}
	return 'ok';
}

function liberarServidor($linea){
//Aqui ponemos servidor en ocupado
	$ocu="UPDATE ec_sincronizacion SET en_proceso=0 WHERE id_sincronizacion=2";
	$ocupa=mysql_query($ocu,$linea);
	if(!$ocupa){
		mysql_query('rollback',$local);
		mysql_query('rollback',$linea);
		return 'no';
	}
	return 'ok';
}

function marcaSincronizacion($conexion,$idS){
	$sql="SELECT ultima_sincronizacion FROM ec_sincronizacion WHERE id_sincronizacion=$idS";
	$eje=mysql_query($sql,$conexion);
	if(!$eje){
		mysql_query('rollback'.$conexion);
		return "Error";
	}
	$sinc=mysql_fetch_row($eje);
	return $sinc[0];
}

function marcaInicio($conexion,$idS){
	//mysql_query("BEGIN",$conexion);
	$sql="UPDATE ec_sincronizacion SET en_proceso=1 WHERE id_sincronizacion=$idS";
	$eje=mysql_query($sql,$conexion);
	if(!$eje){
	mysql_query("rollback",$conexion);
		//die("No se pudo actualizar el estado de sincronizacion en proceso\n".mysql_error($conexion)."\n".$sql);
		return 'error!!!';
	}
	return 'actualizado';
}

function cierraTrans($conexion,$idS,$dato){//Actualizamos el registro
		//mysql_query("BEGIN",$conexion);
		$sqlCierra="UPDATE ec_sincronizacion SET ultima_sincronizacion='$dato',en_proceso=0 WHERE id_sincronizacion=$idS";
		//return $sqlCierra;
		$ok=mysql_query($sqlCierra, $conexion)or die('Error al actualizar fecha meter rollback');
		if(!$ok){
			mysql_query('rollback',$conexion);
			//die('No se pudo actualizar el status de sincronizacion de transferencias localmente<br>'.$sqlCierra);
			die('Error');
			return false;
		}
		return 'cerrado';
	}
function getDateTime($conexion){//obtenemos hora y fecha
		$s="SELECT DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')";
		$r=mysql_query($s,$conexion);
		$rs=mysql_fetch_row($r);
		return $rs[0];
	}
function sacaFecha($conexion,$campo,$idT){
	//include('conexionSincronizar.php');
	$sql="SELECT $campo FROM ec_transferencias WHERE id_transferencia=$idT";
	$eje=mysql_query($sql,$conexion);
	if(!$eje){
		die('Error');
		//die('No se puede consultar la fecha de alta o actulizacion de la transferencia guardada!!!'.mysql_error($conexion).'<br>'.$sql);
	}
	$actual=mysql_fetch_row($eje);
	//die($sql."\n".$actual[0]);
	return $actual[0];
}

function actualizaTransLinea($idTrans){
	include('conexionSincronizar.php');
//actualizamos la transferencia como TERMINADA en linea
	$actTerm="UPDATE ec_transferencias SET id_estado=6 WHERE id_transferencia=$idTrans";
	$ejeTerm=mysql_query($actTerm,$linea);
	if(!$ejeTerm){
		echo 'La transferencia no pudo ser finalizada en linea, verifique su conexión a internet e intente manualmete';
	}
	$campo='ultima_actualizacion';
	$dato=sacaFecha($linea,$campo,$idTrans);
	//echo 'fecha ult act=: '.$dato;
	if($dato=='No se puede consultar la fecha de alta o actulizacion de la transferencia guardada!!!'){
		die('error al sacar fecha');
	}
	$cerrar=cierraTrans($local,2,$dato);
	if($cerrar!='cerrado'){
		die('Error al actualizar fecha de sincronizacion de transferencias');
	}
	return 'ok';
}

?>