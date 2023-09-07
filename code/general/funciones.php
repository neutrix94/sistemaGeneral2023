<?php


	function Muestraperror($smarty, $nombre, $no = "No aplica", $descripcion, $consulta="no aplica", $archivo)
	{
	
		$smarty->assign("nombre", $nombre);
		$smarty->assign("no", $no);
		$smarty->assign("descripcion", $descripcion);
		$smarty->assign("consulta", $consulta);
		$smarty->assign("archivo", $archivo);
		
		$smarty->display("general/perror.tpl");		
		
		die();
	}


	/*
	
		Lista de errores
		
		1. Url no validas
		2. Error en consulta SQL de busqueda
		3. Error en consulta SQL de ejecucion
	
	*/

	function Muestraerror($smarty, $nombre, $no = "No aplica", $descripcion, $consulta="no aplica", $archivo)
	{
		//Titulos por No error
		if($nombre == '' && $no == 1)
			$nombre="URL no valida";
		if($nombre == '' && $no == 2)
			$nombre="Error al buscar los datos";	
		if($nombre == '' && $no == 3)
			$nombre="Error al ejecutar la consulta";		
		
		//Descripciones por No error
		if($descripcion == '' && $no == 1)
			$descripcion="La dirección web no es valida o carece de un parametro vital para su funcionamiento";
		if($descripcion == '' && $no == 2)
			$descripcion="No se logro tener acceso a los datos requeridos para visualizar esta pantalla";	
		if($descripcion == '' && $no == 3)
			$descripcion="La consulta SQL es incorrecta";		
	
		$smarty->assign("nombre", $nombre);
		$smarty->assign("no", $no);
		$smarty->assign("descripcion", $descripcion);
		$smarty->assign("consulta", $consulta);
		$smarty->assign("archivo", $archivo);
		
		$smarty->display("general/error.tpl");		
		
		die();
	}
	
	function getCombo($sql)
	{
		//echo '<br>'.$sql;
		$respuesta=array();
		$ids=array();
		$vals=array();
		$resGC=mysql_query($sql);
		if(!$resGC)
			return $respuesta;
		$numGC=mysql_num_rows($resGC);
		for($i=0;$i<$numGC;$i++)
		{
			$rowGC=mysql_fetch_row($resGC);
			array_push($ids, $rowGC[0]);
			array_push($vals, $rowGC[1]);
		}
		
		array_push($respuesta, $ids);
		array_push($respuesta, $vals);
		//print_r($respuesta);
		return $respuesta;
	}


?>