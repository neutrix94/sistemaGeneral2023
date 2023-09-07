<?php
	include("../../../conectMin.php");
	$flag=$_POST['fl'];
//descarga de CSV
	if ( $flag == 'download_data' ){
		//echo $_POST['datos'];
		$nombre="exclusionesTransferencia.csv";
	//generamos descarga
		header('Content-Type: aplication/octect-stream');
		header('Content-Transfer-Encoding: Binary');
		header('Content-Disposition: attachment; filename="'.$nombre.'"');
		echo( utf8_decode( $_POST['datos'] ) );
		die('');
	}
//modificar la observación
	if($flag=="modifica"){
		$id_registro=$_POST['id'];
		$txt=$_POST['dato'];
		$sql="UPDATE ec_exclusiones_transferencia SET observaciones='$txt' WHERE id_exclusion_transferencia=$id_registro";
		$eje=mysql_query($sql)or die("Error al actualizar la observación\n\n".$sql."\n\n".mysql_error());
		die('ok|');
	}

//eliminar un registro
	if($flag=='eliminar'){
		$id_registro=$_POST['id'];
		$sql="DELETE FROM ec_exclusiones_transferencia WHERE id_exclusion_transferencia=$id_registro";
		$eje=mysql_query($sql)or die("Error al eliminar registro\n\n".$sql."\n\n".mysql_error());
		die('ok');
	}
//buscar productos
	if($flag=='busca'){
		$txt=$_POST['clave'];
		$sql="SELECT id_productos,nombre FROM ec_productos WHERE id_productos>1 AND (id_productos='$txt' OR orden_lista='$txt'";
	//agudisamos la búsqueda
		$aux=explode(" ",$txt);
		for($i=0;$i<sizeof($aux);$i++){
			if($aux[$i]!='' && $aux[$i]!=null){
				if($i==0){
					$sql.=" OR (";
				}else{
					$sql.=" AND ";
				}
				$sql.="nombre LIKE '%".$aux[$i]."%'";
			}
			if($i==sizeof($aux)-1){
				$sql.=")";//cerramos el OR
			}
		}//fin de for $i
		$sql.=")";//cerramos la condicion and
		
		$eje=mysql_query($sql)or die("Error al buscar productos!!\n\n".$sql."\n\n".mysql_error());
		echo 'ok|<table width="100%">';
		$cont=0;//declaramos contador en 0
		while($r=mysql_fetch_row($eje)){
			$cont++;//incrementamos contador
			echo '<tr id="resultado_'.$cont.'" tabindex="'.$cont.'" onclick="validaProducto('.$r[0].');" onkeyup="valida_mov_resultados(event,'.$cont.','.$r[0].');">';
				echo '<td width="100%">'.$r[1].'</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
	if($flag=='agrega'){
	//recibimos las variables
		$id_producto=$_POST['id'];
		$c=$_POST['contador'];
	//insertamos el registro
		$sql="INSERT INTO ec_exclusiones_transferencia VALUES(null,'$id_producto',-1,'',now(),now(),1)";
		$eje=mysql_query($sql)or die("Error al insertar la exclusión del producto!!!\n\n".$sql."\n\n".mysql_error());
		$id_nvo=mysql_insert_id();//guardamos el id
	//reconsulamos los datos
		$sql="SELECT 
			et.id_exclusion_transferencia,/*0*/
			et.id_producto,/*1*/
			p.orden_lista,/*2*/
			p.nombre,/*3*/
			et.observaciones,/*4*/
			CONCAT(et.fecha,'<br>',et.hora),/*5*/
			SUM( IF( 
					ma.id_movimiento_almacen IS NULL OR ma.id_almacen != 1, 
					0, 
					( md.cantidad * tm.afecta ) 
				) 
			)/*6*/
		FROM ec_exclusiones_transferencia et
		LEFT JOIN ec_productos p ON et.id_producto=p.id_productos
		LEFT JOIN ec_movimiento_detalle md ON md.id_producto = et.id_producto
		LEFT JOIN ec_movimiento_almacen ma ON ma.id_movimiento_almacen = md.id_movimiento
		LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento = tm.id_tipo_movimiento
		WHERE et.id_exclusion_transferencia=$id_nvo";
		$eje=mysql_query($sql)or die("Error al consultar los datos del nuevo producto excluido!!!\n\n".$sql."\n\n".mysql_error());
		$r=mysql_fetch_row($eje);
	//regresamos la fila
		if($c%2==0){
			$color="#E6E8AB";
			}else{
				$color="#BAD8E6";
			}
			echo 'ok|<tr id="fila_'.$c.'" style="background:'.$color.';" tabindex="'.$c.'" onclick="resalta_fila('.$c.');">';
				echo '<td class="oculto" id="0_'.$c.'">'.$r[0].'</td>';
				echo '<td class="oculto" id="1_'.$c.'">'.$r[1].'</td>';
				echo '<td width="15%" id="2_'.$c.'" align="center">'.$r[2].'</td>';
				echo '<td width="25%" id="3_'.$c.'">'.$r[3].'</td>';
				echo '<td width="5%" id="6_'.$c.'">'.$r[6].'</td>';
				echo '<td width="25%" id="4_'.$c.'" onclick="edita_celda('.$c.');">'.$r[4].'</td>';
				echo '<td width="15%" id="5_'.$c.'" align="center">'.$r[5].'</td>';
				echo '<td width="14%" align="center"><a href="javascript:elimina('.$c.');"><img src="../../../img/especiales/delete.png" width="40px;"></a></td>';
			echo '</tr>';
			/*echo 'ok|<tr style="background:'.$color.';" id="fila_'.$c.'" tabindex="'.$c.'">';
				echo '<td class="oculto" id="0_'.$c.'">'.$r[0].'</td>';
				echo '<td class="oculto" id="1_'.$c.'">'.$r[1].'</td>';
				echo '<td width="15%" id="2_'.$c.'" align="center">'.$r[2].'</td>';
				echo '<td width="30%" id="3_'.$c.'">'.$r[3].'</td>';
				echo '<td width="25%" id="4_'.$c.'" onclick="edita_celda('.$c.');">'.$r[4].'</td>';
				echo '<td width="15%" id="5_'.$c.'" align="center">'.$r[5].'</td>';
				echo '<td width="14%" align="center"><a href="javascript:elimina('.$c.');"><img src="../../../img/especiales/delete.png" width="40px;"></a></td>';
			echo '</tr>';
	*/}

?>