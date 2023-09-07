<?php	
	//echo "0-";
	extract($_GET);
	extract($_POST);
	
	include("../../conect.php");

	if($id_reporte != 99 || $id_reporte != 98 )
	{
		$sql="SELECT
			  titulo
			  FROM sys_reportes
			  WHERE id_reporte='$id_reporte'";
			  
		$res=mysql_query($sql) or die("Error en:<br>$sql<br><br>Descripcion:<br>".mysql_error());
		
		if(mysql_num_rows($res) > 0)
		{
			$row=mysql_fetch_row($res);
			
			$smarty->assign('titulo',$row[0]);
			$smarty->assign('id_reporte',$id_reporte);
		}
	}
	
	if($id_reporte == 98)
	{
		$sql="	SELECT
				id_productos,
				nombre
				FROM ec_productos
				WHERE id_productos > 0
				AND habilitado=1
				ORDER BY nombre";

		$result = mysql_query($sql) or die('Productos: '.mysql_error());
		$vals   = array(-1);
		$textos = array('-Cualquiera-');
		while($fila = mysql_fetch_row($result))
		{	
			array_push($vals,$fila[0]);
			array_push($textos,$fila[1]);
		}
		mysql_free_result($result);
		$smarty->assign('proval',$vals);
		$smarty->assign('protxt',$textos);		  	
		
		$sql="	SELECT
				id_colores,
				nombre
				FROM ec_colores
				ORDER BY nombre";

		$result = mysql_query($sql) or die('Productos: '.mysql_error());
		$vals   = array();
		$textos = array();
		while($fila = mysql_fetch_row($result))
		{	
			array_push($vals,$fila[0]);
			array_push($textos,$fila[1]);
		}
		mysql_free_result($result);
		$smarty->assign('colval',$vals);
		$smarty->assign('coltxt',$textos);		  	
	

	
	}
	
	
	if($id_reporte == 99)
	{
		$sql="SELECT -1 AS id_reporte,
				'----- Elige un reporte -----' AS titulo
				UNION 
		SELECT
			 id_reporte,
			 titulo
			 FROM sys_reportes
			 WHERE id_reporte >= 18
			 AND id_reporte <= 23";

		$result = mysql_query($sql) or die('Categorias: '.mysql_error());
		$vals   = array();
		$textos = array();
		while($fila = mysql_fetch_row($result))
		{	
			array_push($vals,$fila[0]);
			array_push($textos,$fila[1]);
		}
		mysql_free_result($result);
		$smarty->assign('titulo','Reporte de ventas filtrable');
		$smarty->assign('vals',$vals);
		$smarty->assign('textos',$textos);		  	
		
	}
	if($id_reporte == 98)
	{
		$sql="SELECT -1 AS id_reporte,
				'----- Elige un reporte -----' AS titulo
				UNION 
			SELECT
			 id_reporte,
			 titulo
			 FROM sys_reportes
			 WHERE id_reporte >= 24
			 AND id_reporte <= 29";

		$result = mysql_query($sql) or die('Categorias: '.mysql_error());
		$vals   = array();
		$textos = array();
		while($fila = mysql_fetch_row($result))
		{	
			array_push($vals,$fila[0]);
			array_push($textos,$fila[1]);
		}

		mysql_free_result($result);

		$smarty->assign('titulo','Reporte de compras filtrable');
		$smarty->assign('vals',$vals);
		$smarty->assign('textos',$textos);		  	

	}

	
	
	//Buscamos las conceptos
	
	$sql="	SELECT
			id_concepto,
			nombre
			FROM ec_conceptos_gastos
			ORDER BY nombre";
	
	$res=mysql_query($sql);
	$num=mysql_num_rows($res);
	
	
	$conval=array();
	$contxt=array();
	
	
	//echo $es_admin;
	
	for($i=0;$i<$num;$i++)		
	{
		$row=mysql_fetch_row($res);
		
		if($i == 0)
		{
			array_push($conval, -1);
			array_push($contxt, "-Cualquiera-");
		}
		
		array_push($conval, $row[0]);
		array_push($contxt, $row[1]);
	}	
	
	$smarty->assign("conval", $conval);
	$smarty->assign("contxt", $contxt);
	
	
	//Buscamos las sucursales
	$sql="	SELECT
			s.id_sucursal,
			s.nombre,
			u.administrador
			FROM sys_users u
			JOIN sys_sucursales s ON IF(u.administrador, 1, s.id_sucursal = $user_sucursal)
			WHERE u.id_usuario = $user_id";
			
			
	$res=mysql_query($sql);
	$num=mysql_num_rows($res);
	
	
	$sucval=array();
	$suctxt=array();
	
	
	echo $es_admin;
	
	for($i=0;$i<$num;$i++)		
	{
		$row=mysql_fetch_row($res);
		
		if($row[2] == '1' && $i == 0)
		{
			array_push($sucval, -1);
			array_push($suctxt, "-Cualquiera-");
		}
		
		array_push($sucval, $row[0]);
		array_push($suctxt, $row[1]);
	}	
	
	$smarty->assign("sucval", $sucval);
	$smarty->assign("suctxt", $suctxt);	  
	
	
	
	//Buscamos las categorias
	
	$sql="	SELECT
			id_categoria,
			nombre
			FROM ec_categoria
			ORDER BY nombre";
	
	$res=mysql_query($sql);
	$num=mysql_num_rows($res);
	
	
	$catval=array();
	$cattxt=array();
	
	
	echo $es_admin;
	
	for($i=0;$i<$num;$i++)		
	{
		$row=mysql_fetch_row($res);
		
		if($i == 0)
		{
			array_push($catval, -1);
			array_push($cattxt, "-Cualquiera-");
		}
		
		array_push($catval, $row[0]);
		array_push($cattxt, $row[1]);
	}	
	
	$smarty->assign("catval", $catval);
	$smarty->assign("cattxt", $cattxt);	
/*Implementación de Oscar 17.08.2018 para filtrar reportes por folio*/
	if($id_reporte==40 && isset($folio)){
		$smarty->assign("folio_ajuste",$folio);
	}

/**/

	$smarty->assign("id_reporte", $id_reporte);

		
	$smarty->display('reportes_mod.tpl');
	
?>