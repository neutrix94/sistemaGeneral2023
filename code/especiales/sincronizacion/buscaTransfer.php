<?php
	extract($_GET);
	//echo 'llega: '.$suc;
	if(include('sincronizaTransferencia.php')){
		include('conexionSincronizar.php');//incluimos archvo de conexion
	//consultamos la ultima sincronizacion conocida
		$ultimaSinc=marcaSincronizacion($local,2);
	}else{
		die('No se encuentra el archivo de conexion!!!');
	}
	
//checamos que no se este descargando transferencia
	$sq="SELECT en_proceso FROM ec_sincronizacion WHERE id_sincronizacion=2";
	$eje=mysql_query($sq,$linea);
	if(!$eje){
		die("Error al verificar estado de sincronizacion de transferencias!!!\n".mysql_error($local)."\n".$sq);
	}
	$re=mysql_fetch_row($eje);
//si esta en proceso de sincronizacion no hace nada
	//echo 'respuesta: '.$re[0];
	if($re[0]==1){
		echo 'NO';
		return false;
	}
//sacamos el id con mayor valor
	$rev="SELECT MAX(id_global) FROM ec_transferencias";
	$ejeRev=mysql_query($rev,$local);
	if(!$ejeRev){
		die('Error al consultar el id_global mas alto localmente!!!');
	}
	$idMax=mysql_fetch_row($ejeRev);//extraemos informacion
	$aux=$idMax[0];//guardamos en variable auxiliar
	if($idMax[0]=''){//si el valor es vacio
		$idMax[0]=0;//le asignamos cero
	}else{//de lo contrario
		$idMax[0]=$aux;//le asignamos el valor resultante de la consulta
	}
//checamos si hay transferencias por descargar
	$sq="SELECT id_transferencia from ec_transferencias 
				WHERE (id_sucursal_origen=$suc OR id_sucursal_destino=$suc)
				AND(id_estado=3 AND id_transferencia>".$idMax[0].")
				OR (id_estado=6 AND (ultima_actualizacion>'$ultimaSinc' OR alta>'$ultimaSinc'))";
	$ej=mysql_query($sq,$linea);
	if(!$ej){
		die("Error al buscar transferencias por descargar\n".mysql_error($linea)."\n".$sq);
	}
	$nT=mysql_num_rows($ej);
//si no hay resultados devolvemos NO y terminamos busqueda
	if($nT<=0){
		echo 'NO';
		return false;
	}
//checamos que no haya productos nuevos por sincronizar
	$ultimaSincGeneral=marcaSincronizacion($local,1);
//marcamos en proceso de transferencia
	$inicia=marcaInicio($linea,2);
	if($inicia!='actualizado'){
		//mysql_query('rollback',$local);
		//mysql_query('rollback',$linea);
		die('NO|Error al poner BD en proceso de transferencia');
		return false;
	}
/*
//aqui ponemos en ocupado al servidor
	$ocupar=ocuparServidor($linea);
	if($ocupar!='ok'){
		mysql_query('rollback',$local);
		mysql_query('rollback',$linea);
		die("Error al poner servidor en modo ocupado");
	}
*/
//marcamos inicio de transaccion
	mysql_query('begin',$local);
	mysql_query('begin',$linea);
//iniciamos la sincronizacion
	$sql="SELECT id_productos FROM ec_productos WHERE alta>'$ultimaSincGeneral'";
	$eje=mysql_query($sql,$linea);
	if(!$eje){
		mysql_query('rollback',$local);
		mysql_query('rollback',$linea);
		die("Error al buscar nuevos productos por bajar\n".mysql_error($linea)."\n".$sql);
	}
	$nP=mysql_num_rows($eje);
	if($nP>0){
		die("Hay productos no existentes, sincroniza manualmente");
	}
	$tA=0;//contador de transferencias actualizadas
	$tI=0;//contador de transferencias insertadas
//declaramos variable donde se guardran los id's;
	$ids='';
//recoremos el arreglo para insertar o actualizar las transferencias
	while($rw=mysql_fetch_row($ej)){

//checamos si la transferencia existe en la BD
	$ch="SELECT id_transferencia FROM ec_transferencias WHERE id_global=$rw[0]";
	$cont=mysql_num_rows($ch);
	if($cont>0){
		//die('NO|ya existe');
	}else{
	//buscamos datos de transferencias por descargar
		$sqlBT="SELECT
		/*0*/	id_transferencia,
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
		/*16*/	id_sucursal,
		/*17*/	es_resolucion
				FROM ec_transferencias
				WHERE id_transferencia=$rw[0]";
		$consultaBajarT=mysql_query($sqlBT,$linea);
//echo "\nconsulta;\n".$sqlBT;
		if(!$consultaBajarT){
			mysql_query('rollback',$local);
			mysql_query('rollback',$linea);
			die("Error al descargar datso de transferencia\n".mysql_error($linea)."\n".$sq);
		}
		$bajaTrans=mysql_num_rows($consultaBajarT);//echo'<tr><td>Transferencias por bajar:</td><td>'.$bajaTrans.'</td>';
		$daTB=mysql_fetch_row($consultaBajarT);
	//si es actualizar a status TERMINADA y NO es resolucion
		if($daTB[15]==6 & $daTB[17]==''){
	//echo"\nHere update\n";
				//actualizamos localmente
				$sql="UPDATE ec_transferencias SET id_estado=6 WHERE id_global=$daTB[0]";
				$eje=mysql_query($sql,$local);
				if(!$eje){//echo '<br>Status de transferencia Actualizado Localmente';
					mysql_query('rollback',$local);
					mysql_query('rollback',$linea);
					die("Error al actualizar el status de la transferencia a Terminado en Linea\n".mysql_error($local)."\n".$genert);
				}else{
			//actualizamos la hora de ultima sincronizacin de transferencias localmente
					$campo='ultima_actualizacion';
					$fec=sacaFecha($linea,$campo,$daTB[0]);
					if($fec=="Error"){
						mysql_query('rollback',$local);
						mysql_query('rollback',$linea);
						die('Error al sacar fecha de linea');
					}
					$ok=cierraTrans($local,2,$fec);
					if($ok=="Error"){
						mysql_query('rollback',$local);
						mysql_query('rollback',$linea);
						die('Error al cerrar transferencia');
					}
				}
			}
		//si es nueva insercion o transferencia por resolucion
			if($daTB[15]==3|| $daTB[15]==6 & $daTB[17]==1){
//echo"\nHere is inserting...\n";
				$ids.=$daTB[0]."|";
				$tI++;
				$sql="INSERT INTO ec_transferencias(id_global,id_usuario,folio,fecha,hora,id_sucursal_origen,id_sucursal_destino,observaciones,
									id_razon_social_venta,id_razon_social_compra,facturable,porc_ganancia,id_almacen_origen,id_almacen_destino,
									id_tipo,id_estado,id_sucursal)
						VALUES('$daTB[0]','$daTB[1]','$daTB[2]','$daTB[3]','$daTB[4]','$daTB[5]','$daTB[6]','$daTB[7]',
								'$daTB[8]','$daTB[9]','$daTB[10]','$daTB[11]','$daTB[12]','$daTB[13]','$daTB[14]','1','$daTB[16]')";
				$eje=mysql_query($sql,$local);
				if(!$eje){
					mysql_query('rollback',$local);
					mysql_query('rollback',$linea);
					die("no se insertó transferencia localmente\n".mysql_error($local)."\n".$sql);
				}
			//consultamos el id de la transferencia insertado localmente
				$idTransferLocal=mysql_insert_id($local);
		//	echo 'id_trsn:local: '.$idTransferLocal;
				$transBajadas++;
				$genDetalleT="SELECT id_producto_or,
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
								WHERE id_transferencia=$daTB[0]";					
				$obtDetalleT=mysql_query($genDetalleT,$linea);

				if(!$obtDetalleT){
					mysql_query('rollback',$local);
					mysql_query('rollback',$linea);
					die("Error al extrar los detalles de la transferencia!!!\n".mysql_error($linea)."\n".$genDetalleT);//lenviamos error generado en linea
				}
				$detallesTB=0;
			//comenmzamos a recorrer arreglo para insertar el detalle de la transferencia localmente
				while($ro=mysql_fetch_row($obtDetalleT)){
				//Insertamos el detalle de transferencia localmente
					$sql="INSERT INTO ec_transferencia_productos
							SET	
							id_transferencia=$idTransferLocal,
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
					//echo'<br>'.$sqlDeTrans;
					$eje2=mysql_query($sql,$local);
					if(!$eje2){
						mysql_query('rollback',$local);
						mysql_query('rollback',$linea);
						die("Error al insertar el detalle de la transferencia localmente\n".mysql_error($local)."\n".$sql);
					}
					$detallesTB++;
				}//finaliza while de insertar detalle

		//actualizamos el status de la transferencia localmente
				$actSt="UPDATE ec_transferencias SET id_estado=3 WHERE id_global=$daTB[0]";//actualizamos status para activar trigger
				$ejeActSt=mysql_query($actSt,$local);
				if(!$ejeActSt){
					mysql_query('rollback',$local);
					mysql_query('rollback',$linea);
					die("Error al actualizar el status de la transferencia a SALIDA DE TRANSFERENCIA localmente\n".mysql_error($local)."\n".$actSt);
				}
			//si es resolucion actualizamos a TERMINADA
				if($daTB[15]==6 && $daTB[17]==1){
				//echo 'is here';
					$sql="UPDATE ec_transferencias SET id_estado=6 WHERE id_global=$daTB[0]";
					$eje=mysql_query($sql,$local);
					if(!$eje){
						mysql_query('rollback',$local);
						mysql_query('rollback',$linea);
						die("Error al actualizar transferencia por resolucion a TERMINADA\n".mysql_error($local)."\n".$sql);
					}
				}
			//actualizamos la fecha de sincronizacion de transferencias
				$campo='alta';
				if($daTB[17]==1){
					$campo='ultima_actualizacion';
				}
				$fec=sacaFecha($linea,$campo,$rw[0]);//sacamos fecha
				$verifica=cierraTrans($local,2,$fec);//
				if($verifica!='cerrado'){
					mysql_query('rollback',$local);
					mysql_query('rollback',$linea);
					die("Error!!!\nNo se pudo actualizar la fecha de sincronización de transferencias");
				}
				//die('fecha:');
			}//fin de segundo if					
		}//fin de else(DESDE ARRIBA)	
	}//fin de while
//regresamos el resultado
	mysql_query('commit',$local);
	mysql_query('commit',$linea);
//liberasmos servidor
	$liberar=liberarServidor($linea);
	if($liberar!='ok'){
		die("Error al liberar servidor de transferencias");
	}
//comprobamos que la transferencia no se duplique
	//echo 'id´s:'.$ids;
	$id=explode("|",$ids);
	extract($id);
	for($i=0;$i<$tI;$i++){
		//echo "\n".$i.':'.$id[$i]."\n";
		$ch="SELECT id_transferencia FROM ec_transferencias WHERE id_global=$id[$i]";
		$ejeCh=mysql_query($ch,$local);
		if(!$ejeCh){
			die('Error al checar transferencias');
		}
	}


	echo'SI|'.$fec.'|'.$daTB[2];
?>