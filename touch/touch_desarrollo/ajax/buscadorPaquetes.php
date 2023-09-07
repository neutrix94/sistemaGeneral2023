<?php
	include("../../conectMin.php");
	extract($_POST);
//die("fl:".$fl);
	if($fl=='carga_img'){
		//die('here');

		//$sube_img_pqt;

		$tmp_img_name=$_FILES['archivo']['name'];

		if(strlen(strstr($tmp_img_name,'.jpg'))>0){
			$formato=".jpg";
		}else if(strlen(strstr($tmp_img_name,'.jpeg'))>0){
			$formato=".jpeg";
		}else if(strlen(strstr($tmp_img_name,'.png'))>0){
			$formato=".png";
		}
		echo $_FILES['archivo']['tmp_name'];
		$target_path ="../../img/paquetes/"."paquete_".$id_paq_img.$formato;//basename( $_FILES['archivo']['name']) 
		$nombre_auxiliar="paquete_".$id_paq_img.$formato;
	//eliminamos la img si ya existia
		if(file_exists($target_path)){
			unlink($target_path);
		}

		if(move_uploaded_file($_FILES['archivo']['tmp_name'], $target_path)) {
    	/*	echo "El archivo ".  basename( $_FILES['uploadedfile']['name']). 
    	" ha sido subido";*/
    		$target_path=str_replace("\\","/", $target_path);
	//actualizamos la imagen en la cabecera del paquete	
    		$sql="UPDATE ec_paquetes SET imagen='$target_path' WHERE id_paquete=$id_paq_img";
    		$eje=mysql_query($sql)or die("Errror al actualizar la ruta de la imagen!!!\n".mysql_error()."\n".$sql);
    	/*implementación Oscar 25.01.2019 para sacar rutas de tickets*/
			$archivo_path = "../../conexion_inicial.txt";
			if(file_exists($archivo_path)){
				$file = fopen($archivo_path,"r");
				$line=fgets($file);
				fclose($file);
	    		$config=explode("<>",$line);
	    		$tmp=explode("~",$config[2]);
	    		$ruta_or=$tmp[0];
	    		$ruta_or=str_replace("cache/ticket/", "img/paquetes/", $ruta_or);
	    		$ruta_des=$tmp[1];
	    		$ruta_des=str_replace("cache/ticket/", "img/paquetes/", $ruta_des);
			}else{
				die("No hay archivo de configuración!!!");
			}
/*Fin de cambio Oscar 25.01.2018*/
    //insertamos la instruccion para que las sucursales bajen la imagen
    		$sql="INSERT INTO sys_archivos_descarga
    				SELECT 
    					null,
    					'imagen',
    					'$nombre_auxiliar',
    					'$ruta_or',
    					'$ruta_des',
    					id_sucursal,
    					'$user_id',
    					'',
    					0
    				FROM sys_sucursales
    				WHERE id_sucursal>0";
    		$eje=mysql_query($sql)or die("Error al insertar la descarga de la imágen!!!".mysql_error()."<br>".$sql);
    		echo 'ok|';
			die('<script>this.close();</script>');
		}else{
   			die("Ha ocurrido un error, trate de nuevo!<br>".$target_path."<br>".$_FILES['archivo']['tmp_name']);
		}
	}
/************************************************************************busqueda de paquetes********************************************************************/
	if($fl==1){
	//armamos consultas
		$sql="SELECT pa.id_paquete,pa.nombre,pa.imagen,pa.descripcion 
				FROM ec_paquetes pa";
	//concatenammos unión de tablas de acuerdo al texto de búsqueda
		if($texto!="muestra_todos"){
			$sql.=" LEFT JOIN ec_paquete_detalle pd ON pa.id_paquete=pd.id_paquete LEFT JOIN ec_productos p ON pd.id_producto=p.id_productos";	
		}
	//filtro de paquetes activos
		$sql.=" WHERE pa.activo=1";
		if($texto!='muestra_todos'){
		//busqueda más exacta por descripcion
		$arr=explode(" ",$texto);
			$sql.=" AND((";
			for($i=0;$i<sizeof($arr);$i++){
				if($arr[$i]!="" && $i>0){
					$sql.=" AND ";
				}
				if($arr[$i]!=""){
					$sql.="p.nombre LIKE '%".$arr[$i]."%'";
				}
			}
			$sql.=")OR(";
			for($i=0;$i<sizeof($arr);$i++){
				if($arr[$i]!="" && $i>0){
					$sql.=" AND ";
				}
				if($arr[$i]!=""){
					$sql.="pa.nombre LIKE '%".$arr[$i]."%'";
				}
			}
			$sql.=")) GROUP BY pa.id_paquete";	
		}
	//ejecutamos consulta
		$eje=mysql_query($sql)or die("Error al consultar coincidencias!!!\n\n".$sql."\n\n".mysql_error());
	//formamos tabla
		echo 'ok|<table class="t_p_1">';
			$c=0;//declaramos contador en 0
			while($r=mysql_fetch_row($eje)){
				$c++;//incrementamos contador
			//foramos fila
				echo '<tr id="fila_'.$c.'">';
					echo '<td value="'.$r[0].'" id="cda_1_'.$c.'" style="display:none;"></td>';
					echo '<td id="cda_2_'.$c.'" width="20%">'.$r[1].'</td>';
					echo '<td id="cda_3_'.$c.'" width="10%">'.$r[2].'</td>';
					echo '<td id="cda_4_'.$c.'" width="40%">'.$r[3].'</td>';
					echo '<td align="center" width="10%"><img src="../img/especiales/ver.png" width="50%" height="30px" onclick="modificar(1,'.$c.','.$r[0].')"></td>';
					echo '<td align="center" width="10%"><img src="../img/especiales/edita.png" width="50%" height="30px" onclick="modificar(2,'.$c.','.$r[0].')"></td>';
					echo '<td align="center" width="10%"><img src="../img/especiales/del.png" width="50%" height="30px" onclick="modificar(3,'.$c.','.$r[0].')"></td>';
				echo '</tr>';
			}
			echo '</table>';
			die("");
	}


/****************************************************************buscador de productos para el detalle de paquetes***************************************************************/
	if($fl==2){
		$sql="SELECT p.id_productos,p.nombre
				FROM ec_productos p
				LEFT JOIN sys_sucursales_producto sp ON p.id_productos=sp.id_producto AND sp.id_sucursal=$user_sucursal
				WHERE p.habilitado=1
				AND sp.estado_suc=1
				AND (p.orden_lista='$texto'
					OR p.id_productos='$texto' OR (";

	//busqueda más exacta
		$arr=explode(" ",$texto);
		for($i=0;$i<sizeof($arr);$i++){
			if($arr[$i]!="" && $i>0){
				$sql.=" AND ";
			}
			if($arr[$i]!=""){
					$sql.="p.nombre LIKE '%".$arr[$i]."%'";
			}
		}
		$sql.=")) ORDER BY p.orden_lista";//cerramos OR, AND
	//ejecutamos consulta
		$eje=mysql_query($sql)or die("Error al consultar coincidencia de productos!!!\n\n".$sql."\n\n".mysql_error());
		$c=0;//declaramos contador en cero
	//formamos tabla
		echo 'ok|<table id="resp_busc_prod" width="100%">';
		while($r=mysql_fetch_row($eje)){
			$c++;//incrementamos contador
		//formamos filas
			echo '<tr id="resp_busc_prod_'.$c.'" class="res_bsc" onclick="asignaValorBuscador('.$c.');" onkeyup="resaltar_opc('.$c.',event);" tabindex="'.$c.'">';
				echo '<td style="display:none;" id="r_b_'.$c.'">'.$r[0].'</td>';
				echo '<td id="n_b_'.$c.'" style="color:black;">'.$r[1].'</td>';
			echo '</tr>';
		}

	}



/**********************************************************agregar nueva fila en detalle de paquete***********************************************************************/
	if($fl==3){
		$sql="SELECT p.id_productos,p.nombre
				FROM ec_productos p
				LEFT JOIN sys_sucursales_producto sp ON p.id_productos=sp.id_producto AND sp.id_sucursal=$user_sucursal
				WHERE p.habilitado=1 
				AND sp.estado_suc=1 
				AND p.id_productos=$id";
	//die($sql);
		$eje=mysql_query($sql)or die("Error al seleccionar datos del producto para la formación de nueva fila!!!\n\n".$sql."\n\n".mysql_error());
		$rw=mysql_fetch_row($eje);
		echo 'ok|';
?>
<!--formamos fila a retornar-->
	<tr id="detalle_<?php echo $cont;?>">
			<td id="c_1_<?php echo $cont;?>" class="ocult">0</td>
			<td id="c_2_<?php echo $cont;?>" class="ocult"><?php echo $rw[0];?></td>
			<td id="c_3_<?php echo $cont;?>" style="color:black;"><?php echo $rw[1];?></td>
			<td>
				<input type="text" id="c_4_<?php echo $cont;?>" value="<?php echo $cantidad;?>" class="caja_editable" onkeyup="document.getElementById('b_d_1_<?php echo $cont;?>').disabled=false;">
			</td>
			<td align="center"><button type="button"><img src="../img/especiales/del.png" onclick="eliminaDetalle(2,<?php echo $cont;?>);" height="30px" width="30px"></button></td>
		</tr>
<?php
	}


/*******************************************************insertar nuevo paquete y su detalle***********************************************************************/
	if($fl==8){
		mysql_query("BEGIN");//marcamos inicio de transacción
		$sql="INSERT INTO ec_paquetes VALUES(null,'$nom',null,'',$status,0,now(),1)";/*Se implementa el 1 el día 03.07.2018 Oscar*/
		$eje=mysql_query($sql);
		if(!$eje){
			$error=mysql_error();
			mysql_query("ROLLBACK");//cancelamos transacción	
			die("Error al insertar cabecerera del paquete!!!\n\n".$sql."\n\n".$error);
		}
		$nvo_id=mysql_insert_id();//capturamos nuevo id del paquete

		$arreglo1=explode("|",$arr);//descomprimimos valores del detalle
		for($i=0;$i<sizeof($arreglo1);$i++){
			$arreglo_tmp=explode("~",$arreglo1[$i]);
			//$sql="INSERT INTO ec_paquete_detalle VALUES(null,$nvo_id,$arreglo_tmp[0],$arreglo_tmp[1],0);";
			$sql="INSERT INTO ec_paquete_detalle
					SELECT
					null,
					$nvo_id,
					$arreglo_tmp[0],
					$arreglo_tmp[1],
					IF(pd.precio_venta is null,0.00,pd.precio_venta)
					FROM ec_precios_detalle pd
					LEFT JOIN sys_sucursales s 
					ON IF((SELECT es_externo 
							FROM sys_sucursales_producto sp 
							WHERE sp.id_producto=$arreglo_tmp[0] 
							AND sp.id_sucursal=$user_sucursal)=0,pd.id_precio=s.id_precio,pd.id_precio=s.lista_precios_externa)
					WHERE s.id_sucursal=$user_sucursal
					AND ($arreglo_tmp[1] BETWEEN pd.de_valor AND pd.a_valor)
					AND pd.id_producto=$arreglo_tmp[0]";
			$eje=mysql_query($sql)or die("Error!!!");
			//echo '1_'.$sql;
			if(!$eje){
				mysql_query("ROLLBACK");//cancelamos transacción
				die("Error al insertar detalle de nuevo paquete!!!\n\n".$sql."\n\n".mysql_error());
			}
		}//fin de for_detalle
	/*consultamos monto total del paquete
		$sql="SELECT (SUM(pd.cantidad_producto*pd.monto_pieza_descuento)-(SUM(pd.cantidad_producto*pd.monto_pieza_descuento)*s.descuento)) 
				FROM ec_paquete_detalle pd
				JOIN sys_sucursales s ON s.id_sucursal=$user_sucursal
				WHERE id_paquete=$nvo_id";
		$eje=mysql_query($sql);
		if(!$eje){
			mysql_query("ROLLBACK");//cancelamos transacción
			die("Error al consultar el monto total del paquete!!!\n\n".$sql."\n\n".mysql_error());			
		}
		$r=mysql_fetch_row($eje);*/
	//consultamos nombres de los productos para agregarlos a la descripción
		$sql="SELECT p.nombre
				FROM ec_productos p 
				RIGHT JOIN ec_paquete_detalle pd ON pd.id_producto=p.id_productos
				WHERE pd.id_paquete=$nvo_id";
		$eje=mysql_query($sql);
		if(!$eje){
			mysql_query("ROLLBACK");
			die("Error al consultar nombre de productos!!!\n\n".$sql."\n\n".mysql_error());
		}	
		$prods="";
	//formamos cadena de productos
		while($row=mysql_fetch_row($eje)){
			$prods.=$row[0]." ".$row[1]." - ";
		}
		$mont_tot=ceil($r[0]);
	//actualizamos el monto total del paquete con descuento y la lista de productos en la descripción
		$sql="UPDATE ec_paquetes SET descripcion='$prods' WHERE id_paquete=$nvo_id";
		$eje=mysql_query($sql);
		if(!$eje){
				mysql_query("ROLLBACK");//cancelamos transacción
				die("Error al actualizar el monto total y descripción del paquete!!!\n\n".$sql."\n\n".mysql_error());			
		}
		mysql_query("COMMIT");//autorizamos transacción
		die('ok|'.$nvo_id.'|'.$mont_tot);//regresamos id asignado al nuevo paquete
	}



/************************************************************************actualizar paquete y su detalle***********************************************************************/
	if($fl==9){
		mysql_query("BEGIN");//iniciamos transacción 	
	//actualizamos cabecerera
		$sql="UPDATE ec_paquetes SET nombre='$nom',activo=$status WHERE id_paquete=$id";
		$eje=mysql_query($sql);
		if(!$eje){
			$error=mysql_error();
			mysql_query("ROLLBACK");//cancelamos transcación
			die("Error al actualizar cabecerera del paquete!!!\n\n".$sql."\n\n".$error);
		}
	//eliminamos el detalle anterior
		$sql="DELETE FROM ec_paquete_detalle WHERE id_paquete=$id";
		$eje=mysql_query($sql);
		if(!$eje){
			mysql_query("ROLLBACK");//cancelamos transcación
			die("Error al eliminar para actualizar detalle del paquete!!!\n\n".$sql."\n\n".mysql_error());
		}
	//actualizamos detalle
		$arreglo1=explode("|",$arr);//descomprimimos valores del detalle
	//recorremos el arreglo por producto
		for($i=0;$i<sizeof($arreglo1);$i++){
		//separamos valores
			$arreglo_tmp=explode("~",$arreglo1[$i]);
		//armamos consulta
			$sql="INSERT INTO ec_paquete_detalle
					SELECT
					null,
					$id,
					$arreglo_tmp[0],
					$arreglo_tmp[1],
					IF(pd.precio_venta is null,0.00,pd.precio_venta)
					FROM ec_precios_detalle pd
					LEFT JOIN sys_sucursales s ON pd.id_precio=s.id_precio
					WHERE s.id_sucursal=$user_sucursal
					AND ($arreglo_tmp[1] BETWEEN pd.de_valor AND pd.a_valor)
					AND pd.id_producto=$arreglo_tmp[0]";

			$eje=mysql_query($sql);
			if(!$eje){
				mysql_query("ROLLBACK");//cancelamos transcación
				die("Error al actualizar detalle de paquete!!!\n\n".$sql."\n\n".mysql_error());
			}
		}//fin de for_detalle	
	/*consultamos monto total del paquete
		$sql="SELECT (SUM(pd.cantidad_producto*pd.monto_pieza_descuento)-(SUM(pd.cantidad_producto*pd.monto_pieza_descuento)*s.descuento)) 
				FROM ec_paquete_detalle pd
				JOIN sys_sucursales s ON s.id_sucursal=$user_sucursal
				WHERE id_paquete=$id";
		$eje=mysql_query($sql);
		if(!$eje){
			mysql_query("ROLLBACK");//cancelamos transacción
			die("Error al consultar el monto total del paquete!!!\n\n".$sql."\n\n".mysql_error());			
		}
		$r=mysql_fetch_row($eje);*/
	//consultamos nombres de los productos para agregarlos a la descripción
		$sql="SELECT 
				pd.cantidad_producto,
				p.nombre
				FROM ec_productos p 
				RIGHT JOIN ec_paquete_detalle pd ON pd.id_producto=p.id_productos
				WHERE pd.id_paquete=$id";
		$eje=mysql_query($sql);
		if(!$eje){
			mysql_query("ROLLBACK");
			die("Error al consultar nombre de productos!!!\n\n".$sql."\n\n".mysql_error());
		}	
		$prods="";
	//formamos cadena de productos
		while($row=mysql_fetch_row($eje)){
			$prods.=$row[0]." ".$row[1]." - ";
		}
		$mont_tot=0;
	//actualizamos el monto total del paquete con descuento y la lista de productos en la descripción
		$sql="UPDATE ec_paquetes SET /*imagen='$mont_tot',*/descripcion='$prods' WHERE id_paquete=$id";
		$eje=mysql_query($sql);
		if(!$eje){
				mysql_query("ROLLBACK");//cancelamos transacción
				die("Error al actualizar el monto total y descripción del paquete!!!\n\n".$sql."\n\n".mysql_error());			
		}
		mysql_query("COMMIT");//autorizamos transacción
		die('ok|'.$id.'|'.$mont_tot);//regresamos id asignado al nuevo paquete y monto
	}



/************************************************************************eliminar paquete y su detalle***********************************************************************/
	if($fl==10){
		mysql_query("BEGIN");//marcamos inicio de transacción
	//eliminamos el detalle
		$sql="DELETE FROM ec_paquete_detalle WHERE id_paquete=$id";
		$eje=mysql_query($sql);
		if(!$eje){
			mysql_query("ROLLBACK");
			die("Error al eliminar detalle de paquete!!!\n\n".$sql."\n\n".mysql_error());
		}
	//eliminamos cabecera de paquete
		$sql="DELETE FROM ec_paquetes WHERE id_paquete=$id";
		$eje=mysql_query($sql);
		if(!$eje){
			mysql_query("ROLLBACK");
			die("Error al eliminar cabecera de paquete!!!\n\n".$sql."\n\n".mysql_error());
		}
		mysql_query("COMMIT");
		die('ok|ok');
	}


/*Impmenetación Oscar 2019 para sucursal-paquetes*/
	if($fl=='sucursal_paqt'){
		$aux=explode("|",$dats);
		mysql_query("BEGIN");//declaramos inicio de transacción
		for($i=0;$i<sizeof($aux)-1;$i++){
			$r=explode("~",$aux[$i]);
			$sql="UPDATE sys_sucursales_paquete SET estado_suc='$r[1]' WHERE id_sucursal_paquete='$r[0]'";
			if(!mysql_query($sql)){
				mysql_error("ROLLBACK");//hacemos ROLLBACK
				die("Error al actualizar los estatus del paquete en sucursal(es)".mysql_error());
			}
		}
		mysql_query("COMMIT");//autorizamos la transacción
		die('ok|');
	}
/*Fin de cambio Oscar 2019*/

/*Implementacion Oscar 01.11.2019´para crear paquete de acuerdo a la transferencia*/
//id:id_pqt,fl:'crea_transfer' pass:pss,ya_tr:tr_gen,obs:observaciones
	if($fl=='crea_transfer'){
	//sacamos el nombre del paquete
		$sql="SELECT nombre FROM ec_paquetes WHERE id_paquete=$id";
		$eje=mysql_query($sql)or die("Error al buscar el nombre del paquete!!!\n".mysql_error());
		$r=mysql_fetch_row($eje);
		$observacion=$r[0];
	//validamos la contraseña si es el caso
		if($ya_tr==1){
			$sql="SELECT s.id_encargado
				FROM sys_sucursales s
				LEFT JOIN sys_users u ON s.id_encargado=u.id_usuario
				WHERE u.contrasena=md5('$pass')
				AND s.id_sucursal=$user_sucursal";
			$eje=mysql_query($sql)or die("Error al validar el password del encargad\n".mysql_error());
			if(mysql_num_rows($eje)!=1){
				die("pss_no|El password del encargado es incorrecto\nVerifique sus datos e intente nuevamente!!!");
			}
			$observacion.='\n'.$obs;
		}
	//insertamos la cabecera de la transferencia
		mysql_query("BEGIN");//marcamos el inicio de la transacción
	//extraemos los almacenes de la sucursal
		$sql="SELECT id_almacen FROM ec_almacen WHERE id_sucursal=$user_sucursal AND es_almacen=1 AND es_externo=0
			UNION
			SELECT id_almacen FROM ec_almacen WHERE id_sucursal=$user_sucursal AND es_almacen=0 AND es_externo=0";
		$eje=mysql_query($sql)or die("Error al consulatr los almacenes!!!\n".mysql_error());
		$r_alm_or=mysql_fetch_row($eje);
		$r_alm_des=mysql_fetch_row($eje);
	//extraemos el prefijo de la sucursal
		$sql="SELECT prefijo FROM sys_sucursales WHERE id_sucursal=$user_sucursal";
		$res=mysql_query($sql);//EJECUTAMOS CONSULTA
		if(!$res){
			$error=mysql_error();
			mysql_query("ROLLBACK");
			die("Error al consultar el prefijo de la sucursal!!!\n\n".$sql."\n\n".$error);
		}
		$row=mysql_fetch_row($res);

	//insertamos la cabecera de la transferencia			
		$sql="INSERT INTO ec_transferencias (/*0*/id_usuario,/*1*/folio,/*2*/fecha,/*3*/hora,/*4*/id_sucursal_origen,/*5*/id_sucursal_destino,/*6*/observaciones,
			/*7*/id_razon_social_venta,/*8*/id_razon_social_compra,/*9*/facturable,/*10*/porc_ganancia,/*11*/id_almacen_origen,/*12*/id_almacen_destino,/*13*/id_tipo,
			/*14*/id_estado,/*15*/id_sucursal)
			VALUES(/*0*/'$user_id',/*1*/'',/*2*/NOW(),/*3*/NOW(),/*4*/'$user_sucursal',/*5*/'$user_sucursal',/*6*/'$observacion',/*7*/'-1',/*8*/'1',/*9*/'0',/*10*/'0',
				/*11*/'$r_alm_or[0]',/*12*/'$r_alm_des[0]',/*13*/'4',/*14*/'1',/*15*/'$sucursal_id')";
		$eje=mysql_query($sql);	
		if(!$eje){
			$error=mysql_error();
			mysql_query("ROLLBACK");
			die("Error al insertar la cabecera de la Transferencias!!!\n\n".$sql."\n\n".$error);
		}

	//capturamos el valor del id de la nueva transferencia
		$nuevo=mysql_insert_id();
		
	//armamos el folio
		$folio="TR".$row[0].date('Ymd').$nuevo;
	//insertamos el detalle de la transferencia
	//consultamos el detalle del paquete
		$sql="SELECT 
				IF(pd.id_producto_ordigen IS NULL,pqtd.id_producto,pd.id_producto_ordigen),
				IF(pd.cantidad IS NULL,pqtd.cantidad_producto,(pqtd.cantidad_producto*pd.cantidad)) 
				FROM ec_paquete_detalle pqtd 
				LEFT JOIN ec_productos_detalle pd ON pd.id_producto=pqtd.id_producto
				LEFT JOIN sys_sucursales_producto sp ON sp.id_producto=pqtd.id_producto
				WHERE pqtd.id_paquete=$id
				AND sp.id_sucursal=$user_sucursal
				AND sp.es_externo=0";
				
		$eje=mysql_query($sql)or die("Error al consultar el detalle del paquete!!!\n".mysql_error()."\n".$sql);
		/*for($i=0;$i<$adic[0];$i++){*/
		while($r_prd=mysql_fetch_row($eje)){
			$sql="INSERT INTO ec_transferencia_productos(id_transferencia, id_producto_or,id_presentacion,cantidad_presentacion,cantidad,
				id_producto_de,referencia_resolucion)
				VALUES('$nuevo','$r_prd[0]','-1','$r_prd[1]','$r_prd[1]','$r_prd[0]','$r_prd[1]')";
			$eje_1=mysql_query($sql);//EJECUTAMOS CONSULTA
			if(!$eje_1){
				$error=mysql_error();
				mysql_query("ROLLBACK");
				die("Error al insertar detalle de la Transferencia!!!\n\n".$sql."\n\n".$error);
			}
		}//fin de for i*/
	//actualizamos el folio de la transferencia
		$sql="UPDATE ec_transferencias SET folio='$folio' WHERE id_transferencia=$nuevo";
		$eje=mysql_query($sql);//EJECUTAMOS CONSULTA
			if(!$eje){
				$error=mysql_error();
				mysql_query("ROLLBACK");
				die("Error al actualizar folio de Transferencia!!!\n\n".$sql."\n\n".$error);
			}
	//marcamos el campo de transferencia 
		$sql="UPDATE ec_paquetes SET trans_generada=1 WHERE id_paquete=$id";
		$eje=mysql_query($sql)or die("Error al actualizar el paquete como transferencia realizada!!!\n".mysql_error()."\n".$sql);
		mysql_query("COMMIT");
		die('ok|Transferencia Realizada exitosamente!!!');
	}
/*insertar nuevo detalle de paquete
	if($fl==7){
		$sql="INSERT INTO ec_paquete_detalle VALUES(null,$id,$id_producto,$cant,0);";
		$eje=mysql_query($sql)or die("Error al insertar nuevo detalle de paquete\n\n".$sql."\n\n".mysql_error());
		die('ok|'.mysql_insert_id());//regresamos id asignado en el nuevo registro
	}*/


?>
	

