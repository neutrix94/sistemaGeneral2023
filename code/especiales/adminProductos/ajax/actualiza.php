<?php
	if(include('../../../../conect.php')){
		//echo 'archivo de conexion encontrado';
	}else{
		die('error|archivo de conexion no encontrado!!!');
	}
	mysql_query('begin');//marcamos inicio de transaccion
//extraemos variables enviadas por POST
	extract($_POST);
//checamos si es nuevo registro
	if(isset($flag) && $flag=='agregar'){
		$info=explode("|",$inf);
		
		$sql="INSERT INTO ec_productos(/*1*/clave,/*2*/nombre,/*3*/id_categoria,/*4*/id_subcategoria,/*5*/id_subtipo,/*6*/precio_venta,/*7*/precio_compra,/*8*/es_maquilado,
									/*9*/nombre_etiqueta,/*10*/orden_lista,/*11*/ubicacion_almacen,/*12*/codigo_barras_1,/*13*/codigo_barras_2,/*14*/codigo_barras_3,
									/*15*/codigo_barras_4,/*16*/habilitado,/*17*/omitir_alertas,/*18*/muestra_paleta)
							VALUES(/*1*/'$info[1]',/*2*/'$info[0]',/*3*/'$info[4]',/*4*/'$info[5]',/*5*/'$info[14]',/*6*/'$info[6]',/*7*/'$info[7]',/*8*/'$info[8]',
									/*9*/'$info[9]',/*10*/'$info[2]',/*11*/'$info[3]',/*12*/'$info[10]',/*13*/'$info[11]',/*14*/'$info[12]',/*15*/'$info[13]',
									/*16*/'$info[15]',/*17*/'$info[16]',/*18*/'$info[17]')";
		//echo $sql;
		//return false;	
		$eje=mysql_query($sql);
		if(!$eje){
			mysql_query('rollback');
			die("Error al insertar producto\n".mysql_error()."\n".$sql);
		}
	}else{
		echo 'sql:'.$sql;
//ejecutamos consulta
		$eje=mysql_query($sql);
		if(!$eje){
			mysql_query('rollback');
			die('ERORR al actualizar!!!'."\n".mysql_error()."\n".$sql);
		}
	}//fin de else
//regresamos ok si todo es correcto
	mysql_query('commit');//aprobamos transaccion
	echo 'ok';
?>