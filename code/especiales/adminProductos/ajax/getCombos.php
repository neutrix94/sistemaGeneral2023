<?php
	if(include('../../../../conect.php')){
		//echo 'si';
	}else{
		die('ERROR!!!'."\n".'Falta archivo de conexion');
	}
	extract($_POST);
//iniciamos transaccion
	mysql_query('begin');
	if(isset($flag)){
		if($flag==1){
			$sql="SELECT id_subcategoria,nombre FROM ec_subcategoria WHERE id_categoria=$dato";
		}
		if($flag==2){
			$sql="SELECT id_subtipos,nombre FROM ec_subtipos WHERE id_tipo=1 OR id_tipo=$dato";
		}

		$eje=mysql_query($sql);
		if(!$eje){
			mysql_query('rollback');
			die("Error al consultar informacion en categorias, subcategoria o subtipo\n".mysql_error()."\n".$sql);
		}
		$nR=mysql_num_rows($eje);
		if($nR<1){
			echo '1|-1~No Aplica';
			return false;
		//	die();
		}
		$respuesta=$nR.'|';
		while($rw=mysql_fetch_row($eje)){
			$respuesta.=$rw[0].'~'.$rw[1].'|';
		}
		//echo $respuesta;//regresamos resultados
		//die();
	}else{
		$eje=mysql_query($sql);
		if(!$eje){
			mysql_query('rollback');
			die('Error al consultar combo!!!'."\n".mysql_error()."\n".$sql);
		}
	//contamos resultados
		$nR=mysql_num_rows($eje);
		if($nR<=0){
			die('ok|(vacio)');
		}
		$respuesta='ok';
		while($row=mysql_fetch_row($eje)){
			$respuesta.='|'.$row[0].'~'.$row[1];
		}
	}
//regresamos respuesta
	echo $respuesta;

?>