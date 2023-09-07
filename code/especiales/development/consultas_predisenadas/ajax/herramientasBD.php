<?php
/*implementación Oscar 2021 para ejecutar consultas con MYSQLI*/
	include('../../../../../config.inc.php');
	$link = mysqli_connect($dbHost, $dbUser, $dbPassword, $dbName);
	$link->set_charset("utf8");

	$flag=$_POST['fl'];

	if($flag=='guarda_herramienta'){
		
		$id=$_POST['id_herramienta'];
	//id de herramienta
		if($id=='(Automático)'){
			$sql="INSERT INTO sys_herramientas_desarrollo SET id_herramienta_desarrollo = null,";
		}else{
			$sql="UPDATE sys_herramientas_desarrollo SET";
		}
	//titulo
		$tit=$_POST['titulo'];
		$sql.=" titulo='".$tit."',";
	//consulta
		$cons=str_replace("'", "\'", $_POST['consulta']);
		$sql.=" consulta='".$cons."',";
	//descripcion
		$desc=$_POST['descripcion'];
		$sql.=" descripcion='".$desc."',";
	//filtro sucursal
		$filt_suc=str_replace("'", "\'", $_POST['campo_filtro_sucursal']);
		//if($filt_suc!='' && $filt_suc!=null){
			$sql.=" campo_filtro_sucursal='".$filt_suc."',";
		//}
	//filtro de fecha 1
		$filt_fcha1=str_replace("'", "\'", $_POST['campo_filtro_fecha_1']);
		//if($filt_fcha1!='' && $filt_fcha1!=null){
			$sql.=" campo_filtro_fecha1='".$filt_fcha1."',";
		//}
	//filtro de fecha 2
		$filt_fcha2=str_replace("'", "\'", $_POST['campo_filtro_fecha_2']);
		//if($filt_fcha2!='' && $filt_fcha2!=null){
			$sql.=" campo_filtro_fecha2='".$filt_fcha2."',";
		//}
	//filtro de familia
		$filt_fam=str_replace("'", "\'", $_POST['campo_filtro_familia']);
		//if($filt_fam!='' && $filt_fam!=null){
			$sql.=" campo_filtro_familia='".$filt_fam."',";
		//}
	//filtro de tipo
		$filt_tipo=str_replace("'", "\'", $_POST['campo_filtro_tipo']);
		//if($filt_tipo!='' && $filt_tipo!=null){
			$sql.=" campo_filtro_tipo='".$filt_tipo."',";
		//}
	//filtro de subtipo
		$filt_subtipo=str_replace("'", "\'", $_POST['campo_filtro_subtipo']);
		//if($filt_subtipo!='' && $filt_subtipo!=null){
			$sql.=" campo_filtro_subtipo='".$filt_subtipo."',";
		//}
	//filtro de color
		$filt_color=str_replace("'", "\'", $_POST['campo_filtro_color']);
		//if($filt_color!='' && $filt_color!=null){
			$sql.=" campo_filtro_color='".$filt_color."',";
		//}
	//filtro de almacen
		$filt_alm=str_replace("'", "\'", $_POST['campo_filtro_almacen']);
		//if($filt_alm!='' && $filt_alm!=null){
			$sql.=" campo_filtro_almacen='".$filt_alm."',";
		//}
	//filtro externo
		$filt_ext=str_replace("'", "\'", $_POST['campo_filtro_es_externo']);
		//if($filt_ext!='' && $filt_ext!=null){
			$sql.=" campo_filtro_es_externo='".$filt_ext."',";
		//}
	//tipo de consulta (Implementación Oscar 2021)
		$filt_query_type=str_replace("'", "\'", $_POST['query_type']);
		//if( $filt_query_type != '' && $filt_query_type != null ){
			$sql.=" tipo_herramienta='". $filt_query_type ."',";
		//}
	//ruta API (Implementación Oscar 2023)
		$api=str_replace("'", "\'", $_POST['api_route']);
		//if( $filt_query_type != '' && $filt_query_type != null ){
			$sql.=" ruta_api='". $api ."'";
		
	//quita la coma excedente si es el caso
		$sql.="!!!!!";
		$sql=str_replace(",!!!!!", "!!!!!", $sql);
		$sql=str_replace("!!!!!", "", $sql);
	//si es actualización completa la instrucción
		if($id!="(Automático)"){
			$sql.=" WHERE id_herramienta_desarrollo=".$id;
		}
//die( $sql );
		$eje=$link->query($sql)or die("Error al insertar/actualizar herramienta!!!".$link->error."\n".$sql);
		die("Guardado correctamente!!!");
	}
?>