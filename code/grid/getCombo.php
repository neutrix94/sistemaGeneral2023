<?php
//1. Incluye archivo conectMin.php
	include("../../conectMin.php");
//2. Hace extract de variables por metodo GET
	/*if($_GET["special_fl"] == "$TAG_PREFIX" ){

		$sql_aux = "SELECT prefijo_codigos_unicos AS prefix FROM sys_configuracion_sistema LIMIT 1";
		$stm = mysql_query( $sql_aux ) or die( "Error al consultar el prefijo de los codigos unicos : " . mysql_error() );
		$aux_row = mysql_fetch_assoc( $stm );
		die( $aux_row['prefix'] );
	}//die( 'here : ' . $_GET["special_fl"] );*/
	extract($_GET);
	//print_r($_GET);
//3. Consulta el query para armar combo
	$sql="SELECT datosdb, tipo, depende, id_grid FROM `sys_grid_detalle` WHERE id_grid_detalle='$id'";
	$res=mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
	$row=mysql_fetch_row($res);
	$tipo=$row[1];
	$sql=str_replace('$LLAVE',$llave,$row[0]);	
	$sql=str_replace('$SUCURSAL',$sucursal_id,$row[0]);	

	$sql_aux = "SELECT prefijo_codigos_unicos AS prefix FROM sys_configuracion_sistema LIMIT 1";
	$stm = mysql_query( $sql_aux ) or die( "Error al consultar el prefijo de los codigos unicos : " . mysql_error() );
	$aux_row = mysql_fetch_assoc( $stm );
	$consulta=str_replace('$TAG_PREFIX', $aux_row['prefix'], $consulta);

/*3.1. Modificacion Oscar 2020 para actualizar combo dependiente en grids*/
	$sql = str_replace('$LLAVE', $llave, $sql);

/*3.2. Modificacion Oscar 2020 para filtrar atributos por categoria por categoria*/
	if(isset($categoria) && $id == 459){
		$sql = str_replace('$CATEGORIA', " AND id_categoria = " . $categoria, $sql);
	}elseif($id == 459){
		$sql = str_replace('$CATEGORIA', '', $sql);
	}

//4. Proceso cuando existen dependencias
	if(isset($nodependencias))
	{
		for($i=0;$i<$nodependencias;$i++)
		{
			$nomDep="llaveaux".$i;
			$sql=str_replace('$_LLAVE'.$i,$$nomDep,$sql);
		}	
	}
//5. Proceso para llave auxiliar
	if(isset($llaveaux))
	{
		$strConsulta="SELECT campo_tabla,display FROM sys_grid_detalle WHERE orden='".$row[2]."' AND id_grid='".$row[3]."'";
		$res=mysql_query($strConsulta) or die("Error en:\n$strConsulta\n\nDescripcion:\n".mysql_error());
		$rowDep=mysql_fetch_row($res);
		mysql_free_result($res);
		if($rowDep[0]=="NO")
			$rowDep[0]=$rowDep[1];
		$pos=strpos(strtoupper($sql),"WHERE")+4;
		$sqla=substr($sql,0,$pos+1);
		$sqlb=substr($sql,$pos+1);
		$sql=$sqla."(".$sqlb.")";		
		
		$sql.=" AND ".$rowDep[0]."='".$llaveaux."'";
	}
	//echo $sql;
//6. Ejecuta consulta final y regresa resultados
	$res=mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());;	
	$num=mysql_num_rows($res);
	/*if($num <= 0)
		die("No se encontraron datos\n$sql");*/
	if($tipo == 'combo')
	{
		echo "exito|$num";
		for($i=0;$i<$num;$i++)
		{
			$row=mysql_fetch_row($res);
			echo "|".$row[0]."~".$row[1];
		}
	}
	else
	{
		echo "exito|$num";
		for($i=0;$i<$num;$i++)
		{
			$row=mysql_fetch_row($res);
			echo "|".$row[0].":".$row[1];
		}
	}
	
?>