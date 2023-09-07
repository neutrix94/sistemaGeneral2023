<?php
	include('../../conectMin.php');
	$flag=$_GET['fl'];
	if($flag==""||$flag==null){
		$flag=$_POST['fl'];
	}
//exportaciónn de productos
	if($flag==1){
	//formamos consulta
		$sql="SELECT 
			/*0*/	id_productos,
			/*1*/	REPLACE(clave,',','*'),
			/*2*/	REPLACE(ubicacion_almacen,',','*'),
			/*3*/	orden_lista,
			/*4*/	nombre,
			/*5*/   habilitado
				FROM ec_productos 
				WHERE id_productos>1";
	//ejecutamos consulta
		$eje=mysql_query($sql)or die("Error al conseguir lista de productos por exportar!!!\n\n".$sql."\n\n".mysql_error());
	//creamos cadena con datos
		$datos="id_producto,codigo_alfanumerico,ubicacion,orden_lista,nombre, habilitado\n";/*,categoria,subcategoria,precio_mayoreo,precio_compra,marca,minimo_existencias,imagen,observaciones,";
		$datos.="inventariado,es_maquilado,genera_iva,genera_ieps,porcentaje_iva,porcentaje_ieps,desc_gral,nombre_etiqueta,orden_lista,ubicación_almacen,";
		$datos.="codigo_barras_1,codigo_barras_2,codigo_barras_3,codigo_barras_4,id_subtipo,maximo_existencia,id_numero_luces,id_color,id_tamaño,habilitado,";
		$datos.="omitir_alertas,existencia_media,muestra_paleta,es_resurtido,ultima_modificacion\n";*/
		while($r=mysql_fetch_row($eje)){
			/*$datos.=$r[0].",".$r[1].",".$r[2].",".$r[3].",".$r[4].",".$r[5].",".$r[6].",".$r[7].",".$r[8].",".$r[9].",".$r[10].",".$r[11].",".$r[12].",".$r[13].",".$r[14];
			$datos.=",".$r[15].",".$r[16].",".$r[17].",".$r[18].",".$r[19].",".$r[20].",".$r[21].",".$r[22].",".$r[23].",".$r[24].",".$r[25].",".$r[26].",".$r[27].",".$r[28].",";
			$datos.=$r[29].",".$r[30].",".$r[31].",".$r[32].",".$r[33].",".$r[34].",".$r[35]."\n";*/
			$datos.=$r[0].",".$r[1].",".$r[2].",".$r[3].",".$r[4].",".$r[5]."\n";
		}
	//regresamos respuesta
		$nombre="listadoDeProductos.csv";
		header('Content-Type: application/octet-stream');
		header('Content-Transfer-Encoding: Binary');
		header('Content-disposition: attachment; filename="'.$nombre.'"');
		echo (utf8_decode($datos));		
	}//fin de if flag==1

	if($flag==2){
		//die("2 dos 2");
		mysql_query("BEGIN");//marcamos inicio de transaccción
	//declaramos contadores
		$num_actualizar=0;
		$num_insertar=0;
		$arr=explode("|",$_POST['datos']);
	//die($_POST['datos']);
	//recorremos datos
		for($i=0;$i<sizeof($arr);$i++){
			$dato=explode(",",$arr[$i]);
			$sql="SELECT id_productos FROM ec_productos WHERE id_productos=$dato[0]";
			$eje=mysql_query($sql);
			if(!$eje){
				mysql_query("ROLLBACK");//cancelamos transacción
				die("Error al consultar coincidencias del producto!!!\n\n".$sql."\n\n".mysql_error());
			}
			//if(mysql_num_rows($eje)==1){
				$accion="actualizar";
				$sql="UPDATE ec_productos SET ";
			/*}else{
				$accion="insertar";
				$sql="INSERT INTO ec_productos SET id_productos=null,";
			}*/
//die($dato[1]);
			$sql.="clave=(SELECT REPLACE('".$dato[1]."','*',',')),";
			$sql.="ubicacion_almacen=(SELECT REPLACE('".$dato[2]."','*',',')),";
			$sql.="orden_lista='".$dato[3]."'";

			if(mysql_num_rows($eje)==1){
				$sql.=" WHERE id_productos='".$dato[0]."'";
			}
		//ejecutamos consulta
			//die($sql);
			$eje=mysql_query($sql);
			if(!$eje){
				mysql_query("ROLLBACK");//cancelamos transacción
				die("Error al ".$accion." el producto!!!\n\n".mysql_error()."\n\n".$sql);
			}
		//contamos actualizaciones
			if(mysql_affected_rows($eje)==1&&$accion=="actualizar"){
				$num_actualizar+=1;
			}
		//contamos registros insertados
			if(mysql_affected_rows($eje)==1&&$accion=="insertar"){
				$num_insertar+=1;
			}
		}//fin de for i

		mysql_query("COMMIT");//autorizamos transacción
		die('ok|'.$num_actualizar."|".$num_insertar);
	}//fin de if flag==2


				/*	nombre='".utf8_encode($dato[2])."'
					,
					id_categoria='".$dato[3]."',
					id_subcategoria='".$dato[4]."',
					precio_venta_mayoreo='".$dato[5]."',
					precio_compra='".$dato[6]."',
					marca='".$dato[7]."',
					min_existencia='".$dato[8]."',
					imagen='".$dato[9]."',
					observaciones='".$dato[10]."',
					inventariado='".$dato[11]."',
					es_maquilado='".$dato[12]."',
					genera_iva='".$dato[13]."',
					genera_ieps='".$dato[14]."',
					porc_iva='".$dato[15]."',
					porc_ieps='".$dato[16]."',
					desc_gral='".$dato[17]."',
					nombre_etiqueta='".$dato[18]."',
					orden_lista='".$dato[19]."',
				 	ubicacion_almacen='".$dato[20]."',
					codigo_barras_1='".$dato[21]."',
					codigo_barras_2='".$dato[22]."',
					codigo_barras_3='".$dato[23]."',
					codigo_barras_4='".$dato[24]."',
					id_subtipo='".$dato[25]."',
					maximo_existencia='".$dato[26]."',
				 	id_numero_luces='".$dato[27]."',
					id_color='".$dato[28]."',
					id_tamano='".$dato[29]."',
					habilitado='".$dato[30]."',
					omitir_alertas='".$dato[31]."',
					existencia_media='".$dato[32]."',
					muestra_paleta='".$dato[33]."',
					es_resurtido='".$dato[34]."'";//ultima_modificacion*/


			/*3*	id_categoria,
			/*4*	id_subcategoria,
			/*5*	precio_venta_mayoreo,
			/*6*	precio_compra,
			/*7*	marca,
			/*8*	min_existencia,
			/*9*	imagen,
			/*10*	observaciones,
			/*11*	inventariado,
			/*12*	es_maquilado,
			/*13*	genera_iva,
			/*14*	genera_ieps,
			/*15*	porc_iva,
			/*16*	porc_ieps,
			/*17*	desc_gral,
			/*18*	nombre_etiqueta,
			/*19*	orden_lista,
			/*20* 	ubicacion_almacen,
			/*21*	codigo_barras_1,
			/*22*	codigo_barras_2,
			/*23*	codigo_barras_3,
			/*24*	codigo_barras_4,
			/*25*	id_subtipo,
			/*26*	maximo_existencia,
			/*27* 	id_numero_luces,
			/*28*	id_color,
			/*29*	id_tamano,
			/*30*	habilitado,
			/*31*	omitir_alertas,
			/*32*	existencia_media,
			/*33*	muestra_paleta,
			/*34*	es_resurtido,
			/*35*	ultima_modificacion*/
?>