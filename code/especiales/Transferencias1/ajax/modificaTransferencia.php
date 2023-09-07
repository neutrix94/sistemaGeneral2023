<?php
	include('../../../../conectMin.php');	//extract($_POST);
	$fl=$_POST['fl'];

/*Implementación Oscar 23.10.2018 para activar productos de transferencia*/
	if($fl=='activa'){
		$sucursal_destino=$_POST['sucursal'];
		$id=$_POST['id'];
		$sql="UPDATE sys_sucursales_producto SET estado_suc=1 WHERE id_sucursal=$sucursal_destino AND id_producto IN(";
		$arr=explode("|",$id);
		for($i=0;$i<=sizeof($arr);$i++){
			if($arr[$i]!='' && $arr[$i]!=null){
				if($i>0 && $i<sizeof($arr)){
					$sql.=",";
				}
				$sql.=$arr[$i];
			}
		}//fin de for $i
		$sql.=")";
	//reemplazamos posibles errores
		$sql=str_replace(",)", ")", $sql);
		$sql=str_replace("(,", "(", $sql);
		$sql=str_replace(",,", ",", $sql);
		$eje=mysql_query($sql);
		if(!$eje){
			die("Error al activar productos!!!".$sql."\n\n".mysql_error());
		}
		die('ok|');
	}//fin de si es activar 
/*fin de cambio 23.10.2018*/

//implementación de Oscar 28.05.2018 para generar archivo de exportación CSV
	if($fl==1){
	//recibimos datos
		$info=$_POST['datos'];
	//creamos el nombre del archivo
		$nombre="transferencia.csv";
	//generamos descarga
		header('Content-Type: aplication/octect-stream');
		header('Content-Transfer-Encoding: Binary');
		header('Content-Disposition: attachment; filename="'.$nombre.'"');
		echo(utf8_decode($info));
		//echo "<script languaje='javascript' type='text/javascript'>window.close();</script>";//cerramos ventana
		die('');//<script>window.close();</script>
	}//termina proceso de desacarga csv

//implementación de Oscar 30.09.2019 para generar archivo de exportación CSV en orden de almacen matriz
	if($fl=='orden_almacen'){
	//recibimos datos
		$info=$_POST['datos'];
	//consultamos los datos iniciales de la tranasferencia
		$sql="SELECT id_sucursal_origen,id_sucursal_destino,id_almacen_origen,id_almacen_destino FROM ec_transferencias WHERE id_transferencia=$info";
		$eje=mysql_query($sql)or die("Error al consultar los datos de la cabecera de la tranasferencia!!!<br>".mysql_error()."<br>".$sql);
		$r=mysql_fetch_row($eje);
		$suc_or=$r[0];
		$suc_des=$r[1];
		$alm_or=$r[2];
		$alm_des=$r[3];

		$sql="SELECT
				ax.id_productos,/*0*/
				ax.orden_lista,/*1*/
				ax.clave,/*2*/
				ax.nombre,/*3*/
				IF(ma.id_movimiento_almacen IS NULL OR ma.id_almacen!=$alm_or,0,(md.cantidad*tm.afecta)) as invOrigen,/*4*/
				IF(ma.id_movimiento_almacen IS NULL OR ma.id_almacen!=$alm_des,0,(md.cantidad*tm.afecta)) as invDestino,/*5*/
				ax.maximo,/*6*/
				ax.cantidad,/*7*/
				ax.ubicacion_almacen,/*8*/
				ax.ubicacion_almacen_sucursal/*9*/				
			FROM(
			SELECT
				p.id_productos,/*0*/
				p.orden_lista,/*1*/
				REPLACE(p.clave,',','*') as clave,/*2*/
				p.nombre,/*3*/
				ep.maximo,/*6*/
				tp.cantidad,/*7*/
				REPLACE(p.ubicacion_almacen,',','*') as ubicacion_almacen,/*8*/
				sp.ubicacion_almacen_sucursal/*9*/
			FROM ec_transferencias t 
			RIGHT JOIN ec_transferencia_productos tp ON tp.id_transferencia=t.id_transferencia
			LEFT JOIN ec_productos p ON tp.id_producto_or=p.id_productos
			LEFT JOIN sys_sucursales_producto sp ON sp.id_producto=p.id_productos
			LEFT JOIN sys_sucursales s ON s.id_sucursal=sp.id_sucursal/*
			AND s.id_sucursal=$suc_des*/	
			LEFT JOIN ec_estacionalidad e ON e.id_estacionalidad=s.id_estacionalidad
			LEFT JOIN ec_estacionalidad_producto ep ON ep.id_producto=p.id_productos
			AND ep.id_estacionalidad=e.id_estacionalidad
			WHERE t.id_transferencia=$info
			AND s.id_sucursal=$suc_or
			GROUP BY tp.id_producto_or
			ORDER BY p.ubicacion_almacen,p.orden_lista ASC
			)ax
			LEFT JOIN ec_movimiento_detalle md ON ax.id_productos=md.id_producto
			LEFT JOIN ec_movimiento_almacen ma ON ma.id_movimiento_almacen=md.id_movimiento
			LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
			GROUP BY ax.id_productos
			ORDER BY ax.ubicacion_almacen,ax.orden_lista ASC";
		//die($sql);
		$eje=mysql_query($sql)or die("Error al obtener datos de exportación ordenados!!!<br>".mysql_error()."<br>".$sql);
		$tam=mysql_num_rows($eje);
	//creamos el nombre del archivo
		$nombre="Transferencia.csv";
		$info="Id producto,Orden de lista,Clave Proveedor,Nombre,Inv Origen,Inv Destino,Estacionalidad,Pedido,Ubicacion Matriz,Ubicación Destino,num cj\n";
		for($i=0;$i<$tam;$i++){
			$r=mysql_fetch_row($eje);
			$info.=$r[0].",".$r[1].",".$r[2].",".$r[3].",".$r[4].",".$r[5].",".$r[6].",".$r[7].",".$r[8].",".$r[9].",".($i+1);
			if($i<($tam-1)){
				$info.="\n";//salto de linea
			}
		}
//		die($tam);
	//generamos descarga
		header('Content-Type: aplication/octect-stream');
		header('Content-Transfer-Encoding: Binary');
		header('Content-Disposition: attachment; filename="'.$nombre.'"');
		echo(utf8_decode($info));
		//echo "<script languaje='javascript' type='text/javascript'>window.close();</script>";//cerramos ventana
		die('');//<script>window.close();</script>
	}//termina proceso de desacarga csv

//formato en limpio
	if($fl=="formato_limpio"){
	//creamos el nombre del archivo
		$nombre="formatoDeTransferencia.csv";
		$info='Id producto,Orden de lista,Clave Proveedor,Nombre,Inv Origen,Inv Destino,Estacionalidad,Pedido,Ubicacion Matriz,Ubicación Destino';
	//generamos descarga
		header('Content-Type: aplication/octect-stream');
		header('Content-Transfer-Encoding: Binary');
		header('Content-Disposition: attachment; filename="'.$nombre.'"');
		echo(utf8_decode($info));
		//echo "<script languaje='javascript' type='text/javascript'>window.close();</script>";//cerramos ventana
		die('');//<script>window.close();</script>
	}

/*fin de implementación 30.09.2019*/

//verificar contraseña del encargado
	if($fl==2){
		$password=md5($_POST['password']);//encriptamos el password
		$sql="SELECT 
				s.id_encargado
			FROM sys_sucursales s
			LEFT JOIN sys_users u ON s.id_encargado=u.id_usuario
			WHERE s.id_sucursal=$user_sucursal AND u.contrasena='$password'";
		$eje=mysql_query($sql)or die("Error al verificar la contraseña del encargado!!!\n\n".$sql."\n\n".mysql_error());
		if(mysql_num_rows($eje)==1){
			die('ok|');
		}else{
			die('no|');
		}
	}

	if($fl==3){
		//id_producto:id_prod,id_tipo:tipo_mov,id_almacen:id_alm,diferencia:dif
		mysql_query("BEGIN");//abrimos la trnsacción
	//insertamos la cabecera del movimiento
		$id_almacen=$_POST['id_almacen'];
		$id_tipo=$_POST['id_tipo'];
		$sql="INSERT INTO ec_movimiento_almacen VALUES(NULL,$id_tipo,$user_id,$user_sucursal,NOW(),NOW(),'AJUSTE DESDE TRANSFERENCIA',-1,-1,'',-1,-1,$id_almacen,0,0,'0000-00-00 00:00:00',NULL)";
		$eje=mysql_query($sql);
		if(!$eje){
			$error=mysql_error();
			mysql_query("ROLLBACK");//cancelamos la transacción
			die("Error al insertar la cabecera del detalle por ajuste de inventario!!!\n\n".$sql."\n\n".$error);
		}
		$id_mov=mysql_insert_id();//guardamos el id insertado en la cabecera
	//pasamos la cantidad a postiivo en caso de que esta sea negativa
		if($_POST['diferencia']<0){
			$diferencia=$_POST['diferencia']*-1;
		}else{
			$diferencia=$_POST['diferencia'];
		}
		$id_producto=$_POST['id_producto'];
	//insertamos el detalle del movimiento
		$sql="INSERT INTO ec_movimiento_detalle VALUES(NULL,$id_mov,$id_producto,$diferencia,$diferencia,-1,-1)";
		$eje=mysql_query($sql);
		if(!$eje){
			$error=mysql_error();
			mysql_query("ROLLBACK");//cancelamos la transacción
			die("Error al insertar el detalle del ajuste de inventario!!!\n\n".$sql."\n\n".$error);
		}
		mysql_query("COMMIT");//autorizamos la transacción
		die('ok|');
	}

//modificación de máximo de estacionalidad
	if($fl==4){
	//recibimos la variables por POST
		$nvo_valor=$_POST['valor'];//nuevo valor del máximo de estacionalidad
		$id_registro=$_POST['id'];//id del registro de estacionalidad producto
		$id_prod=$_POST['prod'];//id del producto
		$sql="UPDATE ec_estacionalidad_producto SET maximo=$nvo_valor WHERE id_estacionalidad_producto=$id_registro";
		$eje=mysql_query($sql)or die("Error al actualizar el máximo de la estacionalidad!!!\n\n".$sql."\n\n".mysql_error());

	/*implementación Oscar 10.09.2018 para actualizar estacionalidad final si se trata de estacionalidad final*/
		$sql="SELECT
				est.es_alta,/*0*/
                s.id_sucursal,/*1*/
                s.factor_estacionalidad_final,/*2*/
                (SELECT id_estacionalidad FROM ec_estacionalidad WHERE id_sucursal=s.id_sucursal AND es_alta=0)/*3*/
            FROM ec_estacionalidad_producto estProd
            LEFT JOIN ec_estacionalidad est ON estProd.id_estacionalidad=est.id_estacionalidad
            JOIN sys_sucursales s ON est.id_sucursal=s.id_sucursal 
            WHERE estProd.id_estacionalidad_producto=$id_registro";
       
       	$eje=mysql_query($sql)or die("Error al consultar el tipo de estacionalidad!!!".mysql_error());
        $res=mysql_fetch_row($eje);
    	if($res[0]==1){
    	/*actualizamos la estacionalidad dependiente*/
    		$dato=round($nvo_valor*$res[2]);
            //actualizamos la estacionalidad final
            $sql="UPDATE ec_estacionalidad_producto SET
                    maximo=$dato
                WHERE id_estacionalidad=$res[3] AND id_producto=$id_prod";
        //die($sql);
	        if(!mysql_query($sql)){
                $error=mysql_error();
                mysql_query("ROLLBACK");
                die("Error al actualizar la estacionalidad dependiente!!!".$sql."\n\n".$error);
            }
    	}
    /*fin de cambio Oscar 10.09.2018*/
    
		die("ok|");
	}//fin de actualización de estacionalidad
?>