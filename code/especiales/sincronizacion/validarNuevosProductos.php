<?php
	include('conexionSincronizar.php');
	if($indicador=="sin conexion"){
		die('ok');
	}
	include('sincronizaTransferencia.php');
//checamos que no haya productos nuevos por sincronizar
	$ultimaSincGeneral=marcaSincronizacion($local,1);
//marcamos en proceso de transferencia
	$inicia=marcaInicio($local,2);
	if($inicia!='actualizado'){
		//mysql_query('rollback',$local);
		//mysql_query('rollback',$linea);
		die('NO|Error al poner BD en proceso de transferencia');
		return false;
	}
//die('truena');
//iniciamos la sincronizacion
	$sql="SELECT id_productos FROM ec_productos WHERE alta>'$ultimaSincGeneral'";
	$eje=mysql_query($sql,$linea);
	if(!$eje){
		//mysql_query('rollback',$local);
		//mysql_query('rollback',$linea);
		die("Error al buscar nuevos productos por bajar\n".mysql_error($linea)."\n".$sql);
	}
	$nP=mysql_num_rows($eje);
	if($nP>0){
		die("hay pendientes");
	}
	echo 'ok';
?>