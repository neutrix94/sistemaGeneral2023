<?php
	if(!include('../../conectMin.php')){
		die("Sin archivo de Conexion");
	}
//recibimos variables del buscador
	extract($_POST);
//echo $clave;//clave:txt_busc,tabla:ta,no_t:nt,id:id_gr
//consultamos parámetros para la busqueda
	$sql="SELECT 
			gd.datosDB,
			gd.on_change,
			g.campo_enfoque,
			g.consulta_coinc,
			g.campo_coinc
		FROM sys_grid_detalle gd 
		LEFT JOIN sys_grid g ON g.id_grid=gd.id_grid
		WHERE g.id_grid=$id AND (gd.datosDB!=null OR gd.datosDB!='')";
	$eje=mysql_query($sql) or die("Error al buscar datos de consulta\n\n".mysql_error()."\n\n".$sql);
	$r=mysql_fetch_row($eje);
	
//Generamos consulta de buscador
	//$LLAVE=$clave;
	$enfo=explode("|",$r[2]);
//si es validacion de productos en grid;regresamos campo que contiene id de elemento en lista
	if($flag!=null&&$flag==-2){
		die("ok|".$enfo[1]."|".$enfo[0]);
	}
	$sql=$r[0];
/*implementacion Oscar 2023 para correccion de error en buscador de movimiento almacen*/
	if( $flag!=null&&$flag==1 && $id == 9 ){
		 
		$sql=str_replace(" OR p.id_productos = "," OR pp.id_proveedor_producto = ", $sql);
			//$sql=str_replace("%","", $sql);
	}
/**/
//Implementacion Oscar 16-09-2020 para reemplazar valor de la llave principal
	$sql = str_replace('$LLAVE_PRINCIPAL', $llave_principal, $sql);//se cambia la llave principal en consulta base
	$r[3] = str_replace('$LLAVE_PRINCIPAL', $llave_principal, $r[3]);//se cambia la llave principal consula coincidencias

/**/
	$sql=str_replace('$LLAVE',$clave,$sql);
	$r[1]=str_replace('#',$fil_exist,$r[1]);//remplazamos posición de fila
	$r[1]=str_replace('???',$enfo[0],$r[1]);//remplazamos campo de enfoque
	$sql=str_replace('$PROVEEDOR',$_POST['id_cond'],$sql);//remplazamos campo de enfoque

	//echo 'sql: '.$sql;
		if($flag!=null&&$flag==1){
			$sql=str_replace("LIKE","=", $sql);
			$sql=str_replace("%","", $sql);
		}
//echo $sql;
	$eje=mysql_query($sql)or die("Error al buscar coincidencias!!!\n\n".mysql_error()."\n\n".$sql);
//entra busqueda de presición
	if(mysql_num_rows($eje)<1){
	//dividimos texto de busqueda
		$arr_bus=explode(" ",$clave);
		$condicion=" (";
		for($i=0;$i<sizeof($arr_bus);$i++){
			$condicion.=$r[4]." LIKE '%".$arr_bus[$i]."%'";
			if($i<sizeof($arr_bus)-1){
				$condicion.=" AND ";
			}
		}
		$condicion.=") ";
	//reconstruimos consulta
		$sql=$r[3];
		if($flag!=null&&$flag==1){
			$sql=str_replace("LIKE","=", $sql);
			$sql=str_replace("%","", $sql);
		}
		$sql=str_replace(':::', $condicion,$sql);
		$sql=str_replace('$PROVEEDOR',$_POST['id_cond'],$sql);//remplazamos campo de enfoque
//echo '$sql:'.$sql;
		//echo 'presición'.$sql;
		//$sql=str_replace('$llave', replace, subject)
		$eje=mysql_query($sql)or die("Error al hacer busqueda de presición\n\n".$sql."\n\n".mysql_error());
	}
//termina consulta de presición
	
	echo 'ok|<table width="100%" id="resulta">';//declaramos tabla de resultados
	echo '<tr><td></td></tr>';
	$c=0;//declaramos contador en cero
	if(mysql_num_rows($eje)<=0){
		die('<tr><td>Sin coincidencias!!!</td></tr>');
	}

	while($row=mysql_fetch_row($eje)){//mientras se encuentren resultados de la consulta;
		$c++;//incrementamos contador
		$aux=$r[1];
		$data='';
		for($i=0;$i<sizeof($row);$i++){
			$data.=$row[$i];
			if($i<sizeof($row)-1){
				$data.='°';
			}else{
		//	echo $data;
			}
		}
		$aux=str_replace('~',$data,$aux);
		$aux=str_replace('___',$n_d,$aux);
		if($flag!=null&&$flag==1){
			echo $sql;
			die("|".$aux);
		}	
			//generamos opcion
				echo '<tr>
				<td width=100%><span style="background-color:yellow;">
				<div id="r_'.$c.'" class="opcion" tabindex="'.$c.'" value="'.$c.'" onkeyup="eje(event,'.$c.','.$row[0].');" 
				onclick="insertaBuscador('.$n_d.',\''.$row[0].'°'.$row[1].'\' , \'' . $id .'\', \'' . $grid_nom .'\');">'/*$aux____________--.',\''.$n_d.',\''.$grid_nom*/
					//S	var proc_gd=cambiaDesc(pos, grid, posOri, posFin,dt,num_div);//cambiaDesc('#', 'productosMovimiento', 4, 5,'~','___');
				.$row[1].'</div></td></tr>
				<input type="hidden" value="'.$row[0].'" id="id_'.$c.'">';
			}
	echo '</table>';//cerramos tabla
?>