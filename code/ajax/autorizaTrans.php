<?php
	include("../../conectMin.php");
/*Deshabilitado por Oscar 2023; este proceso se cambia a la clase fastTransfers en la ruta : code/especiales/Transferencias_desarrollo/ajax/fastTransfers.php
	if( isset( $_GET['fl_transfer'] ) && $_GET['fl_transfer'] == 'finishTransfer' ){
		$resp = "";
		$action = "";
		include("../../conexionMysqli.php");
		//mysql_query( "BEGIN" );
		$link->autocommit( false );
		$transfer_id = $_GET["transfer_id"];
		$sql = "SELECT 
					id_estado AS status,
					id_tipo AS type
				FROM ec_transferencias
				WHERE id_transferencia = {$transfer_id}";
		$stm = $link->query( $sql ) or die( "Error al consultar datos de transferencia : {$link->error}" );
		$transfer = $stm->fetch_assoc();
		//var_dump( $transfer );return null;
		if( $transfer['status'] == 9 ){
			$action = "La transferencia ya fue terminada anteriormente";
		}else if( $transfer['type'] != 10 && $transfer['type'] != 11 && $transfer['type'] != 6 ){
			$action = "Este tipo de transferencia no puede ser finalizado desde esta pantalla";
		}else{
		//actualiza las cantidades recibidas
			$sql = "UPDATE ec_transferencia_productos SET 
						cantidad_piezas_recibidas = cantidad,
						total_piezas_recibidas = cantidad
					WHERE id_transferencia = {$transfer_id}";
			$update = $link->query( $sql ) or die( "Error al actualizar piezas recibidas en detalle de transferencia : {$link->error} {$sql}" );

			if( $transfer['status'] == 1 ){
				$sql = "UPDATE ec_transferencias SET id_estado = 2 WHERE id_transferencia = {$transfer_id}";
				$stm = $link->query( $sql ) or die( "Error al actualizar transferencia a Autorizada : {$link->error}" );

			}

			$sql = "UPDATE ec_transferencias SET id_estado = 9 WHERE id_transferencia = {$transfer_id}";
			$stm = $link->query( $sql ) or die( "Error al actualizar transferencia a Recibida : {$link->error}" );
			$action = "La transferencia fue Finalizada exitosamente";

			//$link->autocommit( true );
		}
		$resp = "<link rel=\"stylesheet\" type=\"text/css\" href=\"../../css/bootstrap/css/bootstrap.css\">
					<div class=\"row\">
						<div class=\"col-12 text-center\">
							<h5>{$action}</h5>
							<br>
							<button 
								type=\"button\"
								class=\"btn btn-success\"
								onclick=\"close_emergent();\"
							>
								<i>Aceptar</i>
							</button>
							<br>
						</div>
					</div>";

		die( $resp );
	}

	if( isset( $_GET['fl_transfer'] )  && $_GET['fl_transfer'] = 'updateTransfer' ){
		$resp = "";
		$action = "";
		$new_status = $_GET['transfer_status'];
		$obserations = "";
		if( isset( $_GET['observations'] ) &&  $_GET['observations'] != '' ){
			$obserations = ", observaciones = CONCAT( observaciones, ' ', '{$_GET['observations']}' ) ";
		}
		include("../../conexionMysqli.php");
		//mysql_query( "BEGIN" );
		$transfer_id = $_GET["transfer_id"];
		$sql = "SELECT 
					id_estado AS status,
					id_tipo AS type
				FROM ec_transferencias
				WHERE id_transferencia = {$transfer_id}";
		$stm = $link->query( $sql ) or die( "Error al consultar datos de transferencia : {$link->error}" );
		$transfer = $stm->fetch_assoc();
		//var_dump( $transfer );return null;
		if( $transfer['status'] == 9 ){
			$action = "La transferencia ya fue terminada anteriormente";
		}else if( $transfer['status'] != 9 && $transfer['status'] != 1 && $new_status != 5 && $new_status != 7 ){
			$action = "Solo se pueden actualizar las transferencias pendientes de autorizar.";
		}else if( $transfer['status'] == 1 || $transfer['status'] == 5 ){
		//die( $transfer['type'] . '///' . $transfer['status'] );
			if( $transfer['type'] == 10 || $transfer['type'] == 11 || $transfer['type'] == 6 ){
				$link->autocommit( false );
				if( $transfer['status'] >= 2 && $new_status == 2 ){
					$resp = "<link rel=\"stylesheet\" type=\"text/css\" href=\"../../css/bootstrap/css/bootstrap.css\">
							<div class=\"row\">
								<div class=\"col-12 text-center\">
									<h5>La transferencia ya habia sido Autorizada exitosamente, no se puede volver a autorizar!</h5>
									<br>
									<button 
										type=\"button\"
										class=\"btn btn-success\"
										onclick=\"close_emergent();\"
									>
										<i>Aceptar</i>
									</button>
									<br>
								</div>
							</div>";

					die( $resp );

				}
				$sql = "UPDATE ec_transferencias SET id_estado = '2' {$obserations} WHERE id_transferencia = {$transfer_id}";
				$stm = $link->query( $sql ) or die( "Error al actualizar transferencia a Autorizada : {$link->error} {$sql}" );
				if( ( $transfer['type'] == 11 || $transfer['type'] == 6 ) && $new_status == 2 ){
					$resp = "<link rel=\"stylesheet\" type=\"text/css\" href=\"../../css/bootstrap/css/bootstrap.css\">
							<div class=\"row\">
								<div class=\"col-12 text-center\">
									<h5>Transferencia Puesta en SALIDA exitosamente!</h5>
									<br>
									<button 
										type=\"button\"
										class=\"btn btn-success\"
										onclick=\"close_emergent();\"
									>
										<i>Aceptar</i>
									</button>
									<br>
								</div>
							</div>";

					die( $resp );
				}

				if( $transfer['type'] == 10 ){
					$sql = "UPDATE ec_transferencias SET id_estado = 9 WHERE id_transferencia = {$transfer_id}";
					$stm = $link->query( $sql ) or die( "Error al actualizar transferencia a Recibida : {$link->error}" );
					$action = "La transferencia fue Finalizada exitosamente";
				}else if( $transfer['type'] == 11 || $transfer['type'] == 6 ){
				//inserta el bloque de validacion
					$sql = "INSERT INTO ec_bloques_transferencias_validacion SET
							 id_bloque_transferencia_validacion = NULL,
							 fecha_alta = NOW(),
							 validado = '0'";
					$insert_block = $link->query( $sql ) or die( "Error al insertar cabecera del bloque de validación : {$link->error}" );
					$block_id = $link->insert_id;
				//inserta detalle del bloque de validacion
					$sql = "INSERT INTO ec_bloques_transferencias_validacion_detalle SET 
							id_bloque_transferencia_validacion_detalle = NULL,
							id_bloque_transferencia_validacion = {$block_id},
							id_transferencia = {$transfer_id},
							fecha_alta = NOW(),
							invalidado = '0'";
					$insert_block_detail = $link->query( $sql ) or die( "Error al insertar detalle del bloque de validación : {$link->error}" );
				//inserta los escaneos de recepcion
					$sql = "INSERT INTO ec_transferencias_validacion_usuarios 
							(/*1*id_transferencia_validacion,/*2*id_transferencia_producto,/*3*id_usuario,
							/*4*id_producto,/*5*d_proveedor_producto,/*6*cantidad_cajas_validadas,
							/*7*cantidad_paquetes_validados,/*8*cantidad_piezas_validadas,/*9*fecha_validacion,
							/*10*id_status,/*11*validado_por_nombre )
							SELECT 
								/*1*NULL,
								/*2*tp.id_transferencia_producto,
								/*3*{$user_id},
								/*4*tp.id_producto_or,
								/*5*tp.id_proveedor_producto,
								/*6*0,
								/*7*0,
								/*8*tp.cantidad,
								/*9*NOW(),
								/*10*1,
								/*11*0
							FROM ec_transferencia_productos tp
							WHERE tp.id_transferencia = {$transfer_id}";
					//die( $sql );
					$stm_ins = $link->query( $sql ) or die( "Error al insertar el detalle de escaneso de recepcion : {$link->error}" );
				//actualiza los codigos unicos
					$sql = "UPDATE ec_transferencia_codigos_unicos 
								SET id_transferencia = NULL,
								id_bloque_transferencia_validacion = {$block_id}
							WHERE id_transferencia = {$transfer_id}";
					$stm_upd = $link->query( $sql ) or die( "Error al actualizar codigos unicos : {$link->error}" );
				

				//actualiza la transferencia a salida de transferencia*
					$sql = "UPDATE ec_transferencias 
								SET id_estado = {$new_status} 
								{$obervations}
								WHERE id_transferencia = {$transfer_id}";
					$stm = $link->query( $sql ) or die( "Error al actualizar transferencia a Salida de Transferencia : {$link->error}" );
					$action = "La transferencia fue puesta en SALIDA exitosamente";
				}
				$link->autocommit( true );
			}else if( $transfer['type'] != 10 && $transfer['type'] != 11 && $transfer['type'] != 6 ){
				$action = "Este tipo de transferencia no puede ser modificado desde esta pantalla";
			}
		}
		$resp = "<link rel=\"stylesheet\" type=\"text/css\" href=\"../../css/bootstrap/css/bootstrap.css\">
				<div class=\"row\">
					<div class=\"col-12 text-center\">
						<h5>{$action}</h5>
						<br>
						<button 
							type=\"button\"
							class=\"btn btn-success\"
							onclick=\"close_emergent();\"
						>
							<i>Aceptar</i>
						</button>
						<br>
					</div>
				</div>";

		die( $resp );
	}
*/
	extract($_POST);
	extract($_GET);	

if(isset($_POST['autoriza_transferencia'])){
	$sql="	SELECT
			administrador
			FROM sys_users
			WHERE id_usuario=$user_id";
	$res=mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
	$row=mysql_fetch_row($res);
	if($row[0] == '0'){
		die("No cuenta con los permisos para realizar esta acción");
	}	
	$sql="SELECT
			id_estado
			FROM ec_transferencias
			WHERE id_transferencia=$id_transferencia";			
	$res=mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
	$row=mysql_fetch_row($res);
	
	if($row[0] != '1'){
		die("La transferencia ya ha sido autorizada");
	}

	if($user_tipo_sistema=='local'){
		$sql="SELECT permite_transferencias FROM sys_sucursales WHERE id_sucursal=$user_sucursal";
		$eje=mysql_query($sql)or die("Error al consultar si la transferencia se puede hacer localmente!!!");
		$r=mysql_fetch_row($eje);
		if($r[0]==0){
			die("No es posible continuar con el proceso de transferencia localmente.\nContacte al administrador para continuar!!!");
		}
	}
	//die('aqui');	
		
	//MAMG	
	//$sql="UPDATE ec_transferencias SET id_estado=$nval, ultima_actualizacion=DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s') WHERE id_transferencia=$id_transferencia";	

	$sql="UPDATE ec_transferencias SET id_estado=2, ultima_actualizacion=DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s') WHERE id_transferencia=$id_transferencia";	
	mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
	$sqlSal = "SELECT id_transferencia_producto, id_transferencia,cantidad, cantidad_presentacion FROM ec_transferencia_productos WHERE id_transferencia = $id_transferencia";
	$resSal=mysql_query($sqlSal) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
	$numTrans = mysql_num_rows($resSal);
	for ($iSal = 0; $iSal < $numTrans; $iSal++){
		$rowSal=mysql_fetch_row($resSal);
		$sqlU = "UPDATE ec_transferencia_productos SET cantidad_salida =" . $rowSal[2] . ", cantidad_salida_pres =" .$rowSal[3] . " where id_transferencia=" .$rowSal[1] . " and id_transferencia_producto=".$rowSal[0] ;
		mysql_query($sqlU) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
	}
	$id=$id_transferencia;
/*implementación Oscar 18.04.2019 para meter la transferencia directamente salida de transferecnia si la sucursal origen es diferente de Matriz*/
	$sql="SELECT id_sucursal_origen FROM ec_transferencias WHERE id_transferencia=$id_transferencia";
	$eje=mysql_query($sql)or die("Error al consultar dato de sucursal origen de la transferencia!!!");
	$r=mysql_fetch_row($eje);
	//die("4r[0]: ".$r[0]);
	if($r[0]>1){//si el origen no es Matriz

	//cambiamos la transferencia a status de salida de transferencia directamente
		$sql="UPDATE ec_transferencias SET id_estado=4 WHERE id_transferencia=$id_transferencia";
		$eje=mysql_query($sql)or die("Error al actualizar status de la transferencia directamente a salida por ser de sucursal a sucursal!!!");
	}
/*Fin de cambio Oscar 18.04.2019*/
//aqui imprime el documento
	require("imprimeDocTrans.php");
//aqui envia el email	
	/*
		comentado por Oscar 2021 porque mandaba muchos correos y ya no se usa
		require("enviaMailTrans.php");
	*/

	die(" Se ha cambiado el estatus de la transferencia exitosamente");
}

/**/
	$sql="SELECT id_estado,id_sucursal_destino,id_sucursal_origen FROM ec_transferencias WHERE id_transferencia=$id_transferencia";
	$eje=mysql_query($sql)or die("Eror al consultar el status de la transferencia!!!\n\n".$sql."\n\n".mysql_error());
	$row=mysql_fetch_row($eje);

//si la transferencia no ah sido autorizada
	if($row[0]==1){
		die('ok|0');
	}
//si es para poner en proceso de surtimiento y el origen es matriz
	if($row[0]==2 && $row[2]==1){
		if($autorizacion==''||$autorizacion==null){
			if($user_sucursal==$row[1]){
				die("La transferencia solo puede ser puesta en proceso de surtimiento desde Matriz!!!");
			}
			die('ok|pedir_pass|Ingrese el nombre de quien surtirá la Transferencia para continuar con el proceso|white');
		}else{
			$sql="UPDATE ec_transferencias SET id_estado=3,observaciones=CONCAT(observaciones,'\n-Surtida por: ','$autorizacion',' a las ',(SELECT NOW())) WHERE id_transferencia=$id_transferencia";
			$eje=mysql_query($sql)or die("Error al poner transferencia en Surtimiento\n".mysql_error());
			die("ok|1|La transferencia fue puesta en status de Surtimiento!!!");
		}
	}
//si es para poner en salida de transferencia y el origen es matriz
	if($row[0]==3){
		if($autorizacion==''||$autorizacion==null){
			if($user_sucursal==$row[1]){
				die("La transferencia solo puede ser puesta en Salida de Transferencia desde Matriz!!!");
			}
			die('ok|pedir_pass|Ingrese el nombre de quién revisa y pone en salida la Transferencia |yellow');
		}else{
			$sql="UPDATE ec_transferencias SET id_estado=4,observaciones=CONCAT(observaciones,'\n-Puesta en salida por: ','$autorizacion',' a las ',(SELECT NOW())) WHERE id_transferencia=$id_transferencia";
			$eje=mysql_query($sql)or die("Error al poner transferencia en Salida");
			die("ok|1|La transferencia fue puesta en status de Salida!!!");	
		}
	}

//si es recepción
	if($row[0]==4){
		if($row[1]!=$user_sucursal  && $user_sucursal!=-1){
			die("Las transferencias solo pueden ser recibidas desde la sucursal de destino");
		}
		$url_respuesta="code/general/contenido.php?aab9e1de16f38176f86d7a92ba337a8d=ZWNfdHJhbnNmZXJlbmNpYXM=&a1de185b82326ad96dec8ced6dad5fbbd=MQ==&a01773a8a11c5f7314901bdae5825a190=";
		$url_respuesta.=base64_encode($id_transferencia);
		$url_respuesta.="&bnVtZXJvX3RhYmxh=Mg==";
		die('ok|2|'.$url_respuesta);
	}
//si etá en resolución de transferencias
	if($row[0]==5){
		if($row[1]!=$user_sucursal && $user_sucursal!=-1){
			die("Las transferencias solo pueden ser recibidas desde la sucursal de destino");
		}
		$url_respuesta="code/especiales/resolucionTransferencias.php?a1de185b82326ad96dec8ced6dad5fbbd=MQ==&a01773a8a11c5f7314901bdae5825a190=";
		$url_respuesta.=base64_encode($id_transferencia);
		die('ok|2|'.$url_respuesta);
	}
	
//actualizar a salida de Transferencia
	if($row[0]==7){
		$sql = "UPDATE ec_transferencias SET id_estado = 8 WHERE id_transferencia = '{$id_transferencia}'";
		$eje=mysql_query($sql)or die("Error al poner transferencia en Salida");
		die( 'ok|7|Transferencia actualizada a Salida exitosamente!' );
	}

?>