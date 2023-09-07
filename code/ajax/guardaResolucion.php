<?php

include("../../conectMin.php");//incluimos libreria de conexion

/*Implementación Oscar 12.04.2019 para que no se finalice la transferencia hasta que se de en el botón de aceptar*/
if(isset($_GET['finalizar_resolucion'])){
	$id=$_GET['id'];
	$usuario_resolucion=$_GET['clave'];
//die('here');	
/*Implementación Oscar 25.02.2019 para validar contraseña de usuario*/
	/*if(isset($_GET['flag']) && $_GET['flag']){
		$password=md5($_GET['clave']);
		$sql="SELECT id_usuario FROM sys_users WHERE id_usuario=$user_id AND contrasena='$password'";
		$eje=mysql_query($sql)or die("Error al verificar el password de usuario!!!\n\n".$sql."\n\n".mysql_error());
		if(mysql_num_rows($eje)==1){/*die('ok');*}else{die('La contraseña es incorrecta!!!');}
	}*/
/*Fin de cambio Oscar 25.02.019*/
/*********************************************************************************************************************************************************
*																																					 	 *
*							A PARTIR DE AQUI EMPIEZA PORCESO DE OSCAR PARA SUBIR TRANSFERENCIAS DESDE LA RESOLUCIÓN										 *
*																																						 *
*********************************************************************************************************************************************************/

//rectificamos que no hay resolucinoes pendientes en la transferencia//Buscamos si aun hay elementos a resolver
//actualizamos la transferencia a TERMINADA				
		$sql="UPDATE ec_transferencias SET id_estado=6,observaciones=CONCAT(observaciones,' -RESOLUCIÓN : ','$usuario_resolucion'), ultima_actualizacion=DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s') WHERE id_transferencia=$id";		
		$res=mysql_query($sql);
		if(!$res){
			echo "Error en:\n$sql\n\nDescripcion:\n".mysql_error();
			mysql_query("ROLLBACK");
			die();
		}
	
	mysql_query("BEGIN");//marcamos el inicio de la transacción

	$rec="	SELECT
			COUNT(1)
			FROM ec_transferencia_productos tp
			JOIN ec_productos p ON tp.id_producto_or = p.id_productos
			JOIN ec_productos_presentaciones pp ON tp.id_presentacion = pp.id_producto_presentacion
			WHERE tp.id_transferencia=$id
			AND tp.cantidad_salida <> tp.cantidad_entrada
			AND tp.resolucion = 0";
	//die($rec);
	$ejeRec=mysql_query($rec);
	if(!$res){
		mysql_query("ROLLBACK");
		die("Error al extraer informacion de la transferencia\n".mysql_error()."\n".$rec);
	}
	
	$row=mysql_fetch_row($ejeRec);
	
	if($row[0]>0){
		//echo 'aun hay productos en resolucion';
	}else{
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//buscamos los productos con resolucion
		$reT="SELECT id_producto_or,calculo_resolucion/*,resolucion,referencia_resolucion,cantidad_entrada*/
				FROM ec_transferencia_productos WHERE id_transferencia=$id AND calculo_resolucion!=0";
		$ejT=mysql_query($reT);
		if(!$ejT){
			$error=mysql_error();
			mysql_query("ROLLBACK");
			die('Error al buscar los productos que entraron en resolucion..!!!'."\n".$reT."\n".$error);
		}
		$nuD=mysql_num_rows($ejT);
		if($nuD<=0){
			//sin accion
		}else{
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//extraenmos datos de sucursales y almacenes de manera local
		$dT="SELECT /*0*/id_sucursal_origen,/*1*/id_sucursal_destino,/*2*/id_almacen_origen,/*3*/id_almacen_destino
					FROM ec_transferencias WHERE id_transferencia=$id";
		$ejDT=mysql_query($dT);
		if(!$ejDT){
			$error=mysql_error();
			mysql_query("ROLLBACK");
			die('Error al extraer los datos de la transferencia en consulta local!!!'."\n".$error."\n".$ejDT);
		}
		$tr=mysql_fetch_row($ejDT);
	//generamos el folio
		$fol="SELECT prefijo from sys_sucursales WHERE id_sucursal=$sucursal_id";
		$ejeFol=mysql_query($fol);
		$pref=mysql_fetch_row($ejeFol);
		$folio='RESOLUCION'.$pref[0].$id;

	/*Implementación Oscar 2021*/
		$sql = "SELECT id_transferencia FROM ec_transferencias WHERE folio = '{$folio}'";
		$eje_comp = mysql_query( $sql ) or die("Error al buscar si la resolución ya existe : " . mysql_error());
		if( mysql_num_rows($eje_comp) > 0 ){
	//envia correo de notificación
			$sql="SELECT 
					smtp_server, 
					puerto, 
					smtp_user, 
					smtp_pass, 
					correo_envios 
				FROM ec_conf_correo 
				WHERE id_configuracion = 1";
			$eje_mail = mysql_query( $sql ) or die ("Error al consultar parámetros del correo : " . mysql_error() ); 
			
			die('ok|');//regresa ok para detener el proceso
		}
	/*Fin de cambio Oscar 2021*/
	//generamos consulta
			$sql="INSERT INTO ec_transferencias(id_usuario,folio,fecha,hora,id_sucursal_origen,id_sucursal_destino,observaciones,
				id_razon_social_venta,id_razon_social_compra,facturable,porc_ganancia,id_almacen_origen,id_almacen_destino,id_tipo,
				id_estado,id_sucursal,es_resolucion)
				VALUES('$user_id','$folio',NOW(),NOW(),'$tr[1]','$tr[0]','$id','-1','1','0','0','$tr[3]','$tr[2]','5','1',
					'$sucursal_id','1')";
			$regTrnsf=mysql_query($sql);
			if(!$regTrnsf){
				$error=mysql_error();
				mysql_query("ROLLBACK");
				die("Error al insertar cabecera de transferencia!!!\n".$error."\n".$sql);
			}
		//insertamos productos
			if($regTrnsf){
		//capturamos el id de la transferencia
				$nuevoID=mysql_insert_id();
			//recorremos los productoas en resolucion y los insertamos en detalle de transferencia
				while($res=mysql_fetch_row($ejT)){

					$sqlProd="INSERT INTO ec_transferencia_productos(id_transferencia,id_producto_or,id_presentacion,cantidad_presentacion,cantidad,id_producto_de)
					 VALUES('$nuevoID','$res[0]','-1','$res[1]','$res[1]','$res[0]')";
//echo $sqlProd;
					$inserta=mysql_query($sqlProd) or die(mysql_error()."\nID:".$res[0]);//EJECUTAMOS CONSULTA
					if(!$inserta){
						$error=mysql_error();
						mysql_query("ROLLBACK");
						die("Error al insertar detalle de transferencia localmente!!!\n".$error."\n".$sqlProd);
					}
					$sqlProd="";//RESETEAMOS VARIABLE DE CONSULTA	
			}//finaliza while del detalle de transferencia	
		}
		//die('aq');
	/*Implementacion Oscar 09.05.2019*/
			$sql="SELECT
					GROUP_CONCAT(CONCAT('Se regresan ',tp.se_regresa,' piezas de ',p.nombre ) SEPARATOR '\n')
				FROM ec_productos p
				RIGHT JOIN ec_transferencia_productos tp ON p.id_productos=tp.id_producto_or
				WHERE tp.id_transferencia=$id
				AND tp.se_regresa!=0";
			//die($sql);
			$eje_obs=mysql_query($sql)or die("Error al consultar productos que se regresan en observaciones!!!\n".mysql_error());
			$row_obs=mysql_fetch_row($eje_obs);
		//	die('observaciones: '.$row_obs[0]);
	/*Fin de cambio Oscar 09.05.2019*/
		//Actualizamos la salida de productos
				$mov="UPDATE ec_transferencia_productos SET cantidad_salida=cantidad, cantidad_salida_pres=cantidad_presentacion
				WHERE id_transferencia=$nuevoID";	
				$mueve=mysql_query($mov);
				if(!$mueve){
					$error=mysql_error();
					mysql_query("ROLLBACK");
					die('Error al actualizar la salida de transferencia en el detalle'."\n".$error."\n".$mov);
				}
		//actualizamos a salida de transferencia para activar trigger de movimientos de salida
				$consAct="UPDATE ec_transferencias SET id_estado=2, observaciones='$row_obs[0]' WHERE id_transferencia=$nuevoID";
				$act=mysql_query($consAct);
				if(!$act){
					$error=mysql_error();
					mysql_query("ROLLBACK");
					die('No se pudo actualizar el status de la transferencia a status Pendiente de Surtir!!!');
				}

		//actualizamos a salida de transferencia para dejar en terminada
				$consAct="UPDATE ec_transferencias SET id_estado=6 WHERE id_transferencia=$nuevoID";
				$act=mysql_query($consAct);
				if(!$act){
					$error=mysql_error();
					mysql_query("ROLLBACK");
					die('No se pudo actualizar el status de la transferencia a Terminada');
				}else{	
		/*Implementación de Oscar 25.02.2019 para impresión del ticket de resoluciones*/
				//consultamos número de tickets de resolución
					$sql="SELECT no_tickets_resolucion FROM ec_configuracion_sucursal WHERE id_sucursal=$user_sucursal";
					$eje=mysql_query($sql);
					if(!$eje){
						die("No se pudo consultar el número de Tickets de Resolución de transferencia\n\n".mysql_error()."\n\n".$sql);
					}
					$no_tickets=mysql_fetch_row($eje);
		/*fin de cambio Oscar 25.02.2019*/
					//echo 'Si se cambio el estado de transferencia a 3';
					mysql_query("COMMIT");
					die('ok|'.$nuevoID."|".$no_tickets[0]);
					echo "Se ha actualizado el inventario conforme a las resoluciones elegidas";
				}
		} 
	}	
}
/**************************/

/**/
	if(isset($_POST['es_buscador']) && $_POST['es_buscador']=='2'){
	//recibimos la variables por POST
		$id_transferencia=$_POST['id_trans'];
		$id_producto=$_POST['id'];
		$cantidad_resolucion=$_POST['nva_cant'];
	//buscamos si el producto esta en la transferencia original
		$sql="SELECT 
				id_producto_or 
			FROM ec_transferencia_productos 
			WHERE id_transferencia={$id_transferencia}
			AND id_producto_or={$id_producto}";
		$eje=mysql_query($sql)or die("Error al consultar si el producto existe en la transferencia!!!\n\n".mysql_error());

	//si el producto no existe en la transferencia original
		if(mysql_num_rows($eje)<=0){
			$sql="INSERT INTO ec_transferencia_productos VALUES(null,{$id_transferencia},{$id_producto},{$id_producto},0,-1,0,0,0,{$cantidad_resolucion},
			{$cantidad_resolucion},0,0,0,0,0,0)";
			$eje=mysql_query($sql)or die("Error al insertar el nuevo prducto en la transferencia original!!!\n\n".mysql_error()."\n\n".$sql);
		}else{
			$sql="UPDATE ec_transferencia_productos SET cantidad_entrada={$cantidad_resolucion}, cantidad_entrada_pres={$cantidad_resolucion}
					WHERE id_transferencia={$id_transferencia} AND id_producto_or=$id_producto";
			$eje=mysql_query($sql)or die("Error al actualizar la contidad recibida en la transferencia!!!\n\n".mysql_error()."\n\n".$sql);
			
		}
		die('ok');//regresamos respuesta para recargar la página
	}

/**/


/*implementación Oscar 10.04.2019 para la resolución de transferencias*/
	if(isset($_POST['es_buscador']) && $_POST['es_buscador']==1){
		$clave=explode(" ", $_POST['txt']);
	//realizamos busqueda por coincidencia
		$sql="SELECT 
				p.id_productos,
				p.nombre 
			FROM ec_productos p
			LEFT JOIN sys_sucursales_producto sp ON p.id_productos=sp.id_producto
			LEFT JOIN sys_sucursales s ON sp.id_sucursal=sp.id_sucursal 
			WHERE sp.id_sucursal=IF('{$user_sucursal}' = '-1', 1, {$user_sucursal} ) AND sp.estado_suc=1
			AND (";

		for($i=0;$i<sizeof($clave);$i++){
			if($i>0){
				$sql.=" AND ";
			}
			$sql.="p.nombre like '%".$clave[$i]."%'";
		}

		$sql.=") GROUP BY p.id_productos";//cerramos el and del WHERE
	//ejecutamos la consulta
		$eje=mysql_query($sql)or die("Error al consultar cincidencias de productos!!\n\n".mysql_error());
		if(mysql_num_rows($eje)<=0){
			die ('ok|<p>Sin coincidencias!!!</p>');
		}
		echo 'ok|<table width="100%">';
		$c=0;//decaramos contador en cero
		while($r=mysql_fetch_row($eje)){
			$c++;//incrementamos contador
			echo '<tr tabindex="'.$c.'" id="resultado_'.$c.'" onfocus="resalta_opc('.$c.');" onblur="regresa_color_opc('.$c.');" onkeyup="valida_tca_opc('.$c.',event);"
			onclick="buscar_prod_grid('.$r[0].');">';
				//echo '<td style="display:none;">'.$r[0].'</td>';//id del producto
				echo '<td width="100%" style="padding:10px;">'.$r[1].'</td>';//nombre del producto
			echo '</tr>';
		}
		echo '</table>';
		//die($sql);
		die('');
	}
/*Fin de cambio Oscar 10.04.2019*/
	
	extract($_GET);//extraemos variables
	//die('aqui');
//iniciamos transaccion
	mysql_query("BEGIN");
/*vemos los tipo de casos*/
	$tmp_calc_res=0;
	
	if($se_queda>0){
		$tmp_calc_res=$se_queda*-1;
	}else if($faltante>0){
		$tmp_calc_res=$faltante;
	}
//	die("se_regresa:".$se_regresa);
/**/
	if ( $id_transferencia == 1 ) {

	}
/*Cambio Oscar 2021 para guardar resolución cuando no hay productos*/
	if ($id_transferencia == null){
		die( 'ok|' );
	}
/*Fin de cambio OScar 2021*/
//Actualizamos el valor
	$sql="UPDATE ec_transferencia_productos SET resolucion=$tipo,cantidad_entrada=$cant_recibida,cantidad_entrada_pres=$cant_recibida,
	se_queda=$se_queda,faltante=$faltante,se_regresa=$se_regresa,calculo_resolucion=$tmp_calc_res WHERE id_transferencia_producto=$id_transferencia";		
	$res=mysql_query($sql);
	if(!$res){
		echo "Error en:\n$sql\n\nDescripcion:\n".mysql_error();
		mysql_query("ROLLBACK");
		die();
	}
//Buscamos la transferencia
	$sql="SELECT id_transferencia FROM ec_transferencia_productos WHERE id_transferencia_producto=$id_transferencia";
	$res=mysql_query($sql);
	if(!$res){
		echo "Error en:\n$sql\n\nDescripcion:\n".mysql_error();
		mysql_query("ROLLBACK");
		die();
	}
	$row=mysql_fetch_row($res);
//asignamos id de transferencia a avariable id
	$id=$row[0];	
		
//Buscamos si aun hay elementos a resolver
	$sql="	SELECT
			COUNT(1)
			FROM ec_transferencia_productos tp
			JOIN ec_productos p ON tp.id_producto_or = p.id_productos
			JOIN ec_productos_presentaciones pp ON tp.id_presentacion = pp.id_producto_presentacion
			WHERE tp.id_transferencia=$id
			AND tp.cantidad_salida <> tp.cantidad_entrada
			AND tp.resolucion = 0";
	$res=mysql_query($sql);
	if(!$res){
		echo "Error en:\n$sql\n\nDescripcion:\n".mysql_error();
		mysql_query("ROLLBACK");
		die();
	}
	
	$row=mysql_fetch_row($res);
	
	if($row[0]>0){	
		echo "Se ha registrado la resolución elegida";
	}else{
		$sql="	SELECT
				id_transferencia_producto,
				id_transferencia,
				cantidad_salida,
				cantidad_entrada,
				cantidad_salida-cantidad_entrada,
				resolucion,
				id_producto_or
				FROM ec_transferencia_productos
				WHERE id_transferencia=$id
				AND cantidad_salida <> cantidad_entrada";
				
		$res=mysql_query($sql);
		if(!$res){
			echo "Error en:\n$sql\n\nDescripcion:\n".mysql_error();
			mysql_query("ROLLBACK");
			die();
		}		
		$num=mysql_num_rows($res);
		
		for($i=0;$i<$num;$i++){
			$row=mysql_fetch_row($res);
			
			switch($row[5]){
				case 0:
					die("Error al intentar guardar los datos");
					break;
		//devolucion del producto
				case 1:

				break;
		//mantiene producto
				case 2:
				
				break;
				
				case 3:
		//Borramos el excedente
				//echo 'entra al caso 3';
					if($row[4] < 0){
						$sql="	UPDATE ec_transferencia_productos
								SET 
								cantidad_entrada=cantidad_salida
								WHERE id_transferencia_producto=$row[0]";
							
						$re=mysql_query($sql);
						if(!$res){
							echo "Error en:\n$sql\n\nDescripcion:\n".mysql_error();
							mysql_query("ROLLBACK");
							die();
						}
					}
				break;			
						
			}//fin de switch
		}//fin de for
	}	
	mysql_query("COMMIT");	

?>