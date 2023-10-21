<?php
	
	extract($_GET);
	extract($_POST);
	
	include("../../../conect.php");
	
	//-------------- OBTENIENDO DATOS DE CATEGORIAS ------------------//
	$query = "SELECT
			-1 AS id_categoria,
			'----- Elige categoria -----' AS nombre
			FROM  ec_categoria
			WHERE 1
			UNION
			SELECT
			id_categoria,
			nombre
			FROM ec_categoria 
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
	//---------------------- OBTENIENDO DATOS DE FAMILIAS ----------------//

	$query = "SELECT 
			DISTINCT(-1) AS id_subcategoria,
			'Todos' AS nombre
			FROM ec_subcategoria";
	$result  = mysql_query($query) or die('Categorias: '.mysql_error());
	$vals2   = array();
	$textos2 = array();
	while($fila = mysql_fetch_row($result))
	{	
		array_push($vals2,$fila[0]);
		array_push($textos2,$fila[1]);
	}
	mysql_free_result($result);
	$smarty->assign('vals2',$vals2);
	$smarty->assign('textos2',$textos2);
	//---------------------- OBTENIENDO DATOS DE Subtipos ----------------//

	$query = "SELECT 
			DISTINCT(0) AS id_subtipos,
			'----- Elige un tipo -----' AS nombre
			FROM ec_subtipos
			WHERE 1";
	$result  = mysql_query($query) or die('Categorias: '.mysql_error());
	$vals3   = array();
	$textos3 = array();
	while($fila = mysql_fetch_row($result))
	{	
		array_push($vals3,$fila[0]);
		array_push($textos3,$fila[1]);
	}
	mysql_free_result($result);
	$smarty->assign('vals3',$vals3);
	$smarty->assign('textos3',$textos3);
//oscar 2023
	$query = "SELECT 
				id_sucursal,
				nombre
			FROM sys_sucursales WHERE id_sucursal > 0";
	$result  = mysql_query($query) or die('sucursales: '.mysql_error());
	$stores_ids   = array();
	$stores_names = array();
		array_push($stores_ids,0);
		array_push($stores_names,'-- Elige una sucursal --');
	while( $fila = mysql_fetch_row($result) ){	
		array_push($stores_ids,$fila[0]);
		array_push($stores_names,$fila[1]);
	}
	mysql_free_result($result);
	$smarty->assign('stores_ids',$stores_ids);
	$smarty->assign('stores_names',$stores_names);

	$smarty->assign('store_id',$user_sucursal);//oscar 2023/10/20
//			die( 'here' );
	$smarty->display("especiales/EtiquetasTermicas/etiquetasTermicas.tpl");
	
?>