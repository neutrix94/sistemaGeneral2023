<?php
	
	extract($_GET);
	extract($_POST);
	
	include("../../../conect.php");

	$query = "SELECT
			-1 AS id_tipo_pago,
			'----- Elige forma de pago -----' AS nombre
			FROM  ec_tipos_pago
			WHERE 1
			UNION
			SELECT
			id_tipo_pago,
			nombre
			FROM ec_tipos_pago 
			WHERE 1";
	
	$result = mysql_query($query) or die('Categorias: '.mysql_error());
	$vals   = array();
	$textos = array();
	while($fila = mysql_fetch_row($result))
	{	
		array_push($vals,$fila[0]);
		array_push($textos,$fila[1]);
	}
	mysql_free_result($result);
	$smarty->assign('vals',$vals);
	$smarty->assign('textos',$textos);	
			
	$smarty->display("especiales/CSV/csvE.tpl");
	
?>