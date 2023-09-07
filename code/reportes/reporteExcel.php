<?php


	include("../../conectMin.php");
	require_once('../../include/PHPExcel/Classes/PHPExcel.php');
	/*Oscar 28.06.2018*/
	//die("perf_user: ".$user_tipo_sistema);
	if($user_tipo_sistema=='linea'){
		//die('linea');
		$datos_csv='';
	}
	/*Fin de cambio*/	

	extract($_GET);
	
	// Crea un nuevo objeto PHPExcel
	$objPHPExcel = new PHPExcel();
	 
	// Establecer propiedades
	$objPHPExcel->getProperties()
	->setCreator("Cattivo")
	->setLastModifiedBy("Cattivo")
	->setTitle("Reporte EasyCount")
	->setSubject("EasyCount")
	->setDescription("Reporte EasyCount")
	->setKeywords("Excel Office 2007 openxml php")
	->setCategory("Reportes");

	// Agregar Informacion
	$objPHPExcel->setActiveSheetIndex(0);
	/*->setCellValue('A1', 'Valor 1')
	->setCellValue('B1', 'Valor 2')
	->setCellValue('C1', 'Total')
	->setCellValue('A2', '10');*/
 	/*$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', 'Valor 1');
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B1', 'Valor 2');*/
 
 
 	//Buscamos las cabeceras
	
	$sql="SELECT
	      campo, 
	      display
	      FROM sys_reportes_columnas
	      WHERE id_reporte=$id_reporte";

/*modificación Oscar 06.06.2018 para desaparecer columnas de reporte de inventarios*/
		if($id_reporte==34&&$id_sucursal!=-1){
			$condiciones="";//reseteamos las posibles condiciones
			$condicion_inventario=" AND id_reporte_columna IN(209,210,211,";//inicializamos variable que contendrá que columnas aplican	
			$arr_suc=explode("~",$id_sucursal);//separamos el arreglo de sucursales
			for($i=0;$i<=sizeof($arr_suc)-1;$i++){
				if($arr_suc[$i]!=''||$arr_suc[$i]!=null){
				//condicionamos de acuerdo a la sucursal

					if($arr_suc[$i]==1)
						$condicion_inventario.="212";//san Miguel

					if($arr_suc[$i]==2)
						$condicion_inventario.="214";//san Miguel
					
					if($arr_suc[$i]==3)
						$condicion_inventario.="213";//trojes
					
					if($arr_suc[$i]==4)
						$condicion_inventario.="215";//casa
					
					if($arr_suc[$i]==5)
						$condicion_inventario.="216";//checo
					
					if($arr_suc[$i]==6)
						$condicion_inventario.="217";//palma
					
					if($arr_suc[$i]==7)
						$condicion_inventario.="218";//joya
					if($arr_suc[$i]==8)
						$condicion_inventario.="219";//lopez
					if($arr_suc[$i]==9)
						$condicion_inventario.="220";//lago
					
					if($arr_suc[$i]==10)
						$condicion_inventario.="248";//centro urbano

					if($arr_suc[$i]==11)
						$condicion_inventario.="249";//lomas verdes
					$condicion_inventario.=",";
				}
			}//fin de rfor i
			$sql.=$condicion_inventario."221)";
		}
	$sql.=" ORDER BY orden";//concatenamos el orden de las columnas en la consulta
/*fin de cambio*/
		  
	$res=mysql_query($sql) or die("Error en:<br>$sql<br><br>Descripcion:<br>".mysql_error());
	$num=mysql_num_rows($res);
	$columnas=Array();
	
	$cols=Array(0=>'A', 1=>'B', 2=>'C', 3=>'D', 4=>'E', 5=>'F', 6=>'G', 7=>'H', 8=>'I', 9=>'J', 10=>'K', 11=>'L', 12=>'M', 13=>'N', 14=>'O');	
	//echo "<tr>";
/*****************************************************************Aqui se forma el encabezado*********************************************************************/
	for($i=0;$i<$num;$i++)
	{
		$row=mysql_fetch_row($res);
	/*modificación de Oscar para que se descarguen reportes desde línea*/
		if($user_tipo_sistema=='linea'){
			$datos_csv.=$row[1];
			if($i<$num-1){
				$datos_csv.=",";//concatenamos coma
			}else{
				$datos_csv.="\n";//concatenamos salto de línea
			}
		}else{
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cols[$i]."1", $row[1]);
		}
	/*fin de modificación*/
	}	  


/***************************Fin de formación de encabezado*******/
	
	//Buscamos los datos generales
	$sql="SELECT
	      consulta,
	      campo_fecha,
	      ver_sumatorias,
	      consulta_sum,
		  campoSucursal
	      FROM sys_reportes
	      WHERE id_reporte=$id_reporte";
		  
	$res=mysql_query($sql) or die("Error en:<br>$sql<br><br>Descripcion:<br>".mysql_error());
	
	$row=mysql_fetch_row($res);
	
	$consulta=$row[0];
	$campoFecha=$row[1];		  
	$sums=$row[2];
	$consulta_sum=$row[3];
	$campoSucursal=$row[4];
	
	$sql=$consulta;
	
	//echo $sql;

	/*Implementación Oscar 17.09.2018 para el reporte #37 (promedio y fecha más alta de ventas)*/
	if($id_reporte==37){
		$sql=str_replace('$fprom_del',$fecha_promedio_del,$sql);
		$sql=str_replace('$fprom_al',$fecha_promedio_al,$sql);
		$sql=str_replace('$fmax_del',$fecha_maxima_del,$sql);
		$sql=str_replace('$fmax_al',$fecha_maxima_al,$sql);
		$sql=str_replace('ZZZ',$extras,$sql);
		if($id_sucursal!=-1){
			$sql=str_replace('XXX',' AND ped.id_sucursal='.$id_sucursal,$sql);
		}else if($id_sucursal==-1) {
			$sql=str_replace('XXX','',$sql);
		}
		//die($sql);
	}
/*fin de cambio Oscar 17.09.2018*/

/*Implementación Oscar 21.09.2018 para que en el reporte 5.11 aparezcan todos los productos*/
	if($id_reporte==11){
		$cond_mov_inv='';
		$cond_mov_entradas='';
		if($id_sucursal!=-1){
			$cond_mov_inv.=' OR ma.id_sucursal!='.$id_sucursal;
			$cond_mov_entradas.=' AND ma.id_sucursal='.$id_sucursal;
		}
		if($id_de_almacen!=-1 && $id_de_almacen!=''){
			$cond_mov_inv.=' OR ma.id_almacen!='.$id_de_almacen;	
			$cond_mov_entradas.=' AND ma.id_almacen='.$id_de_almacen;			
		}	
	/*implementacion Oscar 12.12.2018*/
		if($tipoFec==4 && $fecdel!='' && $fecal!=''){
			$cond_mov_entradas.=" AND (ma.fecha BETWEEN '".$fecdel."' AND '".$fecal."')";
		}
	/*Fin de cambio Oscar 12.12.2018*/
		$sql=str_replace('ZZZ', $cond_mov_inv, $sql);//reemplazamos las condiciones de la consulta de inventario
		$sql=str_replace('YYY', $cond_mov_entradas, $sql);//reemplazamos las condiciones de la consulta de inventario
		$consulta_sum=str_replace('ZZZ', $cond_mov, $consulta_sum);
		//$consulta_sum=str_replace('ZZZ', ' OR ma.id_sucursal!='.$id_sucursal, $consulta_sum);//reemplazamos por suucrsal
		//}else{
			//$sql=str_replace('ZZZ', '', $sql);//dejamos vacío
			//$consulta_sum=str_replace('ZZZ', '', $consulta_sum);//dejamos vacío				
		//}
	//reemplazamos las condiciones de producto
		$sql=str_replace('XXX', $extras, $sql);
		$consulta_sum=str_replace('XXX', $extras, $consulta_sum);
	}
/*fin de cambio 21.09.2018*/


	if($tipoFec == '1' && $campoFecha != 'NO')
	{
		$condiciones.=" AND $campoFecha >= '".date('Y-m-d')."' AND $campoFecha <= '".date('Y-m-d')."'";
	}
	if($tipoFec == '2' && $campoFecha != 'NO')
	{
		
		$semana=date("W");
		$year=date("Y");
		//echo $semana;
		
		for($mes=1;$mes<=12;$mes++)
		{
			//echo "bulto $mes<br>";
    		$limite = date('t',mktime(0,0,0,$mes,1,$year));
    		for($dia=1;$dia<$limite;$dia++)
    		{
        		if(date('W',mktime(0, 0, 0, $mes  , $dia, $year)) == $semana)
        		{
        			//echo "bulo $dia-$mes<br>";
            		if(date('N',mktime(0, 0, 0, $mes  , $dia, $year)) == 1)
            		{
                		//echo 'Lunes '.date('d-m-Y',mktime(0, 0, 0, $mes  , $dia, 2010)).' y Domingo '.date('d-m-Y',mktime(0, 0, 0, $mes  , $dia+6, 2010));
                		$condiciones.=" AND $campoFecha >= '".date('Y-m-d',mktime(0, 0, 0, $mes  , $dia, $year))."' AND $campoFecha <= '".date('Y-m-d',mktime(0, 0, 0, $mes  , $dia+6, $year))."'";
            		}
        		}
    		}
		}  
		
		
	}
	if($tipoFec == '3' && $campoFecha != 'NO')
	{
		$mes=date("m");
		$year=date("Y");
		
		$condiciones.=" AND $campoFecha >= '$year-$mes-1' AND $campoFecha <= '$year-$mes-31'";
	}
	if($tipoFec == '4' && $campoFecha != 'NO')
	{
		$condiciones.=" AND $campoFecha >= '$fecdel' AND $campoFecha <= '$fecal'";
	}
	if($tipoFec == '6' && $campoFecha != 'NO')
	{
		$hoy=date("Y-m-d");
		$condiciones.=" AND $campoFecha >= DATE_ADD('$hoy', INTERVAL -1 DAY) AND $campoFecha <= DATE_ADD('$hoy', INTERVAL -1 DAY)";
	}
	if($tipoFec == '7' && $campoFecha != 'NO')
	{
		$hoy=date("Y-m-d");
		$condiciones.=" AND $campoFecha >= DATE_ADD('$hoy', INTERVAL -7 DAY)";
	}
	if($tipoFec == '9' && $campoFecha != 'NO')
	{
		$hoy=date("Y-m-d");
		$condiciones.=" AND $campoFecha >= DATE_ADD('$hoy', INTERVAL -30 DAY)";
	}
	if($tipoFec == '10' && $campoFecha != 'NO')
	{
		$mes=date("m");
		$year=date("Y");
		
		$mes--;
		
		if($mes <= 0)
		{
			$mes=12;
			$year--;
		}	
		
		$condiciones.=" AND $campoFecha >= '$year-$mes-1' AND $campoFecha <= '$year-$mes-31'";
	}
	if($tipoFec == '11' && $campoFecha != 'NO')
	{
		$hoy=date("Y-m-d");
		$condiciones.=" AND $campoFecha >= DATE_ADD('$hoy', INTERVAL -90 DAY)";
	}
	/*
	if($id_sucursal != -1)
	{
		if($id_reporte==11){
			$condiciones.=" AND sp.id_sucursal='$id_sucursal'";
		}else{
			$condiciones.=" AND $campoSucursal = $id_sucursal";
		}
	}*/
	if($id_sucursal != -1){
		/*if($id_reporte==11){
			$condiciones.=" AND sp.id_sucursal='$id_sucursal'";
		}*/
	//implementación de Oscar 01.06.2018
		if($id_reporte==33){
			$condicion_anidada=" AND $campoSucursal = $id_sucursal";
		}
	//fin de cambo 01.06.2018
		else if($id_reporte!=34){//se agrega condición para evitar que se filtre el reporte de los demás reportes
			$condiciones.=" AND $campoSucursal = $id_sucursal";
		}
	}
	/*Implementación de Oscar 06.06.2018 para el reporte de todos los inventarios*/
//remplazamos el almacen de la sucursal
//remplazamos el almacen de la sucursal
	if($id_reporte==34){
		//die($id_sucursal);
		$campos='';	
		$sucs='';
/*Implementación Oscar 25.08.2018 para filtrar por externos,internos*/
		if($filt_externos==1){$cond_mov=" AND alm.es_externo=0";$cond_mov_sum=" OR alm.es_externo=1 AND alm.es_almacen=1";}
		if($filt_externos==2){$cond_mov=" AND alm.es_externo=1";$cond_mov_sum=" OR alm.es_externo=0 AND alm.es_almacen=1";}
		if($filt_externos==3){$cond_mov=" AND alm.es_almacen=1";}
	//reemplazamos la condición en la suma de todos los inventarios
		$sql=str_replace('ZZZ',$cond_mov_sum, $sql);	
		if($filtro_tipo_almacen==-1){$cond_tip_alm="IN(0,1)";}	
		if($filtro_tipo_almacen==1){$cond_tip_alm="IN(1)";}	
		if($filtro_tipo_almacen==2){$cond_tip_alm="IN(0)";}
		if($id_sucursal!=-1){//si se detecta el filtro por sucursales
/*fin de cambio 25.08.2018*/

		//extraemos los almacenes principales, nombre de la Sucursal
			$sql_1="SELECT 
						a.id_almacen,
						REPLACE(s.nombre,' ',''),
						s.id_sucursal 
					FROM sys_sucursales s 
					LEFT JOIN ec_almacen a ON s.id_sucursal=a.id_sucursal
					WHERE a.es_almacen $cond_tip_alm AND s.id_sucursal IN(";
			for($i=0;$i<=sizeof($arr_suc)-2;$i++){
			//si la sucursal existe
				if($arr_suc[$i]!=''||$arr_suc[$i]!=null){
					$sql_1.=$arr_suc[$i];//concatenamos los ids de las sucursales
					$sucs.=$arr_suc[$i];//creamos el arreglo para el IN del WHERE
				//asignamos coma
					if($i<sizeof($arr_suc)-2){
						$sql_1.=",";
						$sucs.=",";
					}
				}
			}//fin de for $i
		//cerramos el IN de la consulta
			$sql_1.=")";
			$eje_1=mysql_query($sql_1)or die("Error al consultar sucursales y alamcenes!!!\n\n".$sql_1."\n\n".mysql_error());//ejecutamos la consulta
	//formamos columnas correspondientes
			$cont=0;
		/*Implementación Oscar 17.05.2019 para filtrar por tipo de almacen en reporte de Todos los inventarios*/
			if($filtro_tipo_almacen==1){
				$cond_mov.=" AND alm.es_almacen=1";
			}
		/*Fin de Cambio Oscar 17.05.2019*/
			while($r=mysql_fetch_row($eje_1)){
				$cont++;//incrementamos el contador
				$campos.="SUM(IF(ma.id_sucursal=".$r[2].",IF(md.cantidad IS NULL$cond_mov,0,md.cantidad*tm.afecta),0)) AS inv".$r[1].",\n";//
			}
		//hacemos el remplazo de las columnas
			$sql=str_replace("YYY",$campos,$sql);
		//hacemos el remplazo para el WHERE
			$sql=str_replace("XXX"," AND ma.id_sucursal IN(".$sucs.") ",$sql);
		}elseif($id_sucursal==-1){
		/*Implementación Oscar 17.05.2019 para filtrar por tipo de almacen en reporte de Todos los inventarios*/
			if($filtro_tipo_almacen==1){
				$cond_mov.=" AND alm.es_almacen=1";
			}
		/*Fin de Cambio Oscar 17.05.2019*/
		//	die('here');
			/*$sql=str_replace("YYY","SUM(IF(ma.id_almacen=1$cond_mov,IF(md.cantidad IS NULL,0,md.cantidad*tm.afecta),0)) AS invMatriz,
			SUM(IF(ma.id_almacen=6,IF(md.cantidad IS NULL$cond_mov,0,md.cantidad*tm.afecta),0)) AS invSanMiguel,
			SUM(IF(ma.id_almacen=5,IF(md.cantidad IS NULL$cond_mov,0,md.cantidad*tm.afecta),0)) AS invTrojes,
			SUM(IF(ma.id_almacen=7,IF(md.cantidad IS NULL$cond_mov,0,md.cantidad*tm.afecta),0)) AS invCasa,
			SUM(IF(ma.id_almacen=10,IF(md.cantidad IS NULL$cond_mov,0,md.cantidad*tm.afecta),0)) AS invCheco,
			SUM(IF(ma.id_almacen=13,IF(md.cantidad IS NULL$cond_mov,0,md.cantidad*tm.afecta),0)) AS invPalma,
			SUM(IF(ma.id_almacen=16,IF(md.cantidad IS NULL$cond_mov,0,md.cantidad*tm.afecta),0)) AS invJoya,
			SUM(IF(ma.id_almacen=18,IF(md.cantidad IS NULL$cond_mov,0,md.cantidad*tm.afecta),0)) AS invLopez,
			SUM(IF(ma.id_almacen=21,IF(md.cantidad IS NULL$cond_mov,0,md.cantidad*tm.afecta),0)) AS invLago,",$sql);*/
			$sql=str_replace("YYY","SUM(IF(ma.id_sucursal=1,IF(md.cantidad IS NULL$cond_mov,0,md.cantidad*tm.afecta),0)) AS invMatriz,
			SUM(IF(ma.id_sucursal=2,IF(md.cantidad IS NULL$cond_mov,0,md.cantidad*tm.afecta),0)) AS invSanMiguel,
			SUM(IF(ma.id_sucursal=3,IF(md.cantidad IS NULL$cond_mov,0,md.cantidad*tm.afecta),0)) AS invTrojes,
			SUM(IF(ma.id_sucursal=4,IF(md.cantidad IS NULL$cond_mov,0,md.cantidad*tm.afecta),0)) AS invCasa,
			SUM(IF(ma.id_sucursal=5,IF(md.cantidad IS NULL$cond_mov,0,md.cantidad*tm.afecta),0)) AS invCheco,
			SUM(IF(ma.id_sucursal=6,IF(md.cantidad IS NULL$cond_mov,0,md.cantidad*tm.afecta),0)) AS invPalma,
			SUM(IF(ma.id_sucursal=7,IF(md.cantidad IS NULL$cond_mov,0,md.cantidad*tm.afecta),0)) AS invJoya,
			SUM(IF(ma.id_sucursal=8,IF(md.cantidad IS NULL$cond_mov,0,md.cantidad*tm.afecta),0)) AS invLopez,
			SUM(IF(ma.id_sucursal=9,IF(md.cantidad IS NULL$cond_mov,0,md.cantidad*tm.afecta),0)) AS invLago,
			SUM(IF(ma.id_sucursal=10,IF(md.cantidad IS NULL$cond_mov,0,md.cantidad*tm.afecta),0)) AS invCentroUrbano,
			SUM(IF(ma.id_sucursal=11,IF(md.cantidad IS NULL$cond_mov,0,md.cantidad*tm.afecta),0)) AS invLomasVerdes,",$sql);
		}
	}
/*Fin de cambio 06.06.2018*/

	$condiciones.=" $extras";	
	
	//echo $sql;
	
	$sql=str_replace('$fecIni', $fecdel, $sql);
	$sql=str_replace('$fecFin', $fecal, $sql);
	$sql=str_replace("XXX", $condiciones, $sql);
	
	//echo $sql;
		
	$sql.=" $adicionales";	
		
		
	$res=mysql_query($sql) or die("Error en:<br>$sql<br><br>Descripcion:<br>".mysql_error());
	
	$num=mysql_num_rows($res);
	for($i=0;$i<$num;$i++){
		$row=mysql_fetch_row($res);
		for($j=0;$j<sizeof($row);$j++){
		/*Oscar 28.06.2018*/
			if($user_tipo_sistema=='linea'){
				$datos_csv.=str_replace(",","|",$row[$j]);
				if($j<sizeof($row)-1){
					$datos_csv.=",";
				}else{
					$datos_csv.="\n";
				}
			}else{
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cols[$j].($i+2), $row[$j]);
			}
	/*Fin de cambio*/
		}//fin de for $j
	}//fin de for $i
	
	
	if($id_reporte == 32)
	{
		$sql="	SELECT DISTINCT
				id_empleado
				FROM ec_registro_nomina rn
				WHERE 1 ".$condiciones;
				
				
		//die($sql);		
				
		$res=mysql_query($sql) or die(mysql_error());
		
		$num=mysql_num_rows($res);
		$tgral=0;
		$dgral=0;
		$fReal=0;
		
		for($i=0;$i<$num;$i++)
		{
			$row=mysql_fetch_row($res);
			
			
			
			
			$sq="	SELECT DISTINCT
					CONCAT(e.nombre, ' ', e.apellido_paterno, ' ', e.apellido_materno),
					rn.fecha,
					rn.hora_entrada,
					rn.hora_salida,
					/*FLOOR(
						(
							TIME_FORMAT
							(
								TIMEDIFF(rn.hora_salida, rn.hora_entrada), '%H')+TIME_FORMAT(TIMEDIFF(rn.hora_salida, rn.hora_entrada), '%i'
							)*0.016
						)/0.5
					),
					ROUND(FLOOR(
						(
							TIME_FORMAT
							(
								TIMEDIFF(rn.hora_salida, rn.hora_entrada), '%H')+TIME_FORMAT(TIMEDIFF(rn.hora_salida, rn.hora_entrada), '%i'
							)*0.016
						)/0.5
					)*e.sueldo),*/
					ROUND(TIME_FORMAT(TIMEDIFF(rn.hora_salida, rn.hora_entrada), '%H') +(TIME_FORMAT(TIMEDIFF(rn.hora_salida, rn.hora_entrada), '%i')/60),2) Horas,
					ROUND((TIME_FORMAT(TIMEDIFF(rn.hora_salida, rn.hora_entrada), '%H') +(TIME_FORMAT(TIMEDIFF(rn.hora_salida, rn.hora_entrada), '%i')/60))*e.sueldo) Sueldo,
					e.sueldo
					FROM ec_registro_nomina rn
					JOIN ec_empleado e ON rn.id_empleado = e.id_empleado
					WHERE rn.id_empleado = ".$row[0]."
					$condiciones
					ORDER BY rn.fecha, rn.hora_entrada";
					
			$re=mysql_query($sq) or die(mysql_error());
			
			$nu=mysql_num_rows($re);
			$tot=0;			
			$TotSueldo=0;
			
			for($j=0;$j<$nu;$j++)
			{
				$ro=mysql_fetch_row($re);
				
				/*echo "<tr>";
				if($j == 0)
					echo "<td class='datos'>".$ro[0]."</td>";
				else
					echo "<td class='datos'>&nbsp;</td>";
						
				echo "<td class='datos'>".$ro[1]."</td>";
				echo "<td class='datos'>".$ro[2]."</td>";
				echo "<td class='datos'>".$ro[3]."</td>";
				echo "<td class='datos'>".$ro[4]."</td>";
				echo "<td class='datos'>".$ro[5]."</td>";*/
				
				if($j == 0){
					$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cols[0].($fReal+2), $ro[0]);
				}else{
					$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cols[0].($fReal+2), '');	
				}
				
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cols[1].($fReal+2), $ro[1]);	
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cols[2].($fReal+2), $ro[2]);	
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cols[3].($fReal+2), $ro[3]);	
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cols[4].($fReal+2), $ro[4]);	
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cols[5].($fReal+2), $ro[5]);	
				
				$tot+=$ro[4];
				$TotSueldo+=$ro[5];
				
				$fReal++;
				
				//echo "</tr>";	
				
			}
			
			//echo "<tr>";
			
			$tgral+=$tot;
			$dgral+=round($tot*$ro[6]);
			
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cols[0].($fReal+2), "Total ".$ro[0]);	
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cols[4].($fReal+2), $tot);				
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cols[5].($fReal+2), $TotSueldo);	
			/*$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cols[5].($fReal+2), $tot*$ro[6]);	*/
			
			/*echo "<td class='datos'><b>Total ".$ro[0]."</b></td>";		
			echo "<td class='datos'>&nbsp;</td>";
			echo "<td class='datos'>&nbsp;</td>";
			echo "<td class='datos'>&nbsp;</td>";
			echo "<td class='datos'><b>$tot</b></td>";
			echo "<td class='datos'><b>".($tot*$ro[6])."</b></td>";
					
					
			echo "</tr>";	*/
			
			$fReal++;
			
			
		}
		
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cols[3].($fReal+2), "TOTAL");	
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cols[4].($fReal+2), $tgral);	
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cols[5].($fReal+2), $dgral);
		
		/*echo "<tr class='filaSumatoria'>";
		echo "<td class='sumatoriasRep'>&nbsp;</td>";
		echo "<td class='sumatoriasRep'>&nbsp;</td>";
		echo "<td class='sumatoriasRep'>&nbsp;</td>";
		echo "<td class='sumatoriasRep'>TOTAL</td>";
		echo "<td class='sumatoriasRep'>$tgral</td>";
		echo "<td class='sumatoriasRep'>$dgral</td>";
		echo "</tr><tr class='filaFinal'><td>&nbsp;</td></tr>";		*/
				
	}
	
	
	
	$numFil=$num+1;


	//Sumatorias
	if($sums == '1')
	{
		$sql=$consulta_sum;
	
		//echo $sql;
		
		$condiciones="";
		
		if($tipoFec == '1' && $campoFecha != 'NO')
		{
			$condiciones.=" AND $campoFecha >= '".date('Y-m-d')."' AND $campoFecha <= '".date('Y-m-d')."'";
		}
		if($tipoFec == '2' && $campoFecha != 'NO')
		{
			
			$semana=date("W");
			$year=date("Y");
			//echo $semana;
			
			for($mes=1;$mes<=12;$mes++)
			{
				//echo "bulto $mes<br>";
	    		$limite = date('t',mktime(0,0,0,$mes,1,$year));
	    		for($dia=1;$dia<$limite;$dia++)
	    		{
	        		if(date('W',mktime(0, 0, 0, $mes  , $dia, $year)) == $semana)
	        		{
	        			//echo "bulo $dia-$mes<br>";
	            		if(date('N',mktime(0, 0, 0, $mes  , $dia, $year)) == 1)
	            		{
	                		//echo 'Lunes '.date('d-m-Y',mktime(0, 0, 0, $mes  , $dia, 2010)).' y Domingo '.date('d-m-Y',mktime(0, 0, 0, $mes  , $dia+6, 2010));
	                		$condiciones.=" AND $campoFecha >= '".date('Y-m-d',mktime(0, 0, 0, $mes  , $dia, $year))."' AND $campoFecha <= '".date('Y-m-d',mktime(0, 0, 0, $mes  , $dia+6, $year))."'";
	            		}
	        		}
	    		}
			}  
			
			
		}
		if($tipoFec == '3' && $campoFecha != 'NO')
		{
			$mes=date("m");
			$year=date("Y");
			
			$condiciones.=" AND $campoFecha >= '$year-$mes-1' AND $campoFecha <= '$year-$mes-31'";
		}
		if($tipoFec == '4' && $campoFecha != 'NO')
		{
			$condiciones.=" AND $campoFecha >= '$fecdel' AND $campoFecha <= '$fecal'";
		}
		if($tipoFec == '6' && $campoFecha != 'NO')
		{
			$hoy=date("Y-m-d");
			$condiciones.=" AND $campoFecha >= DATE_ADD('$hoy', INTERVAL -1 DAY) AND $campoFecha <= DATE_ADD('$hoy', INTERVAL -1 DAY)";
		}
		if($tipoFec == '7' && $campoFecha != 'NO')
		{
			$hoy=date("Y-m-d");
			$condiciones.=" AND $campoFecha >= DATE_ADD('$hoy', INTERVAL -7 DAY)";
		}
		if($tipoFec == '9' && $campoFecha != 'NO')
		{
			$hoy=date("Y-m-d");
			$condiciones.=" AND $campoFecha >= DATE_ADD('$hoy', INTERVAL -30 DAY)";
		}
		if($tipoFec == '10' && $campoFecha != 'NO')
		{
			$mes=date("m");
			$year=date("Y");
			
			$mes--;
			
			if($mes <= 0)
			{
				$mes=12;
				$year--;
			}	
			
			$condiciones.=" AND $campoFecha >= '$year-$mes-1' AND $campoFecha <= '$year-$mes-31'";
		}
		if($tipoFec == '11' && $campoFecha != 'NO')
		{
			$hoy=date("Y-m-d");
			$condiciones.=" AND $campoFecha >= DATE_ADD('$hoy', INTERVAL -90 DAY)";
		}
		
		if($id_sucursal != -1)
	{
		if($id_reporte==11){
			$condiciones.=" AND sp.id_sucursal='$id_sucursal'";
		}else{
			$condiciones.=" AND $campoSucursal = $id_sucursal";
		}
	}
		
		$condiciones.=" $extras";
		
		//echo $sql;
		
		$sql=str_replace("XXX", $condiciones, $sql);
		
		//echo $sql;
		
		
		$sql.=" $adicionales";
		
		//die($sql);	
		$res=mysql_query($sql) or die("Error en:<br>$sql<br><br>Descripcion:<br>".mysql_error());
		
		$row=mysql_fetch_row($res);

		//echo "<tr>";
		for($i=0;$i<sizeof($row);$i++)
		{
			/*if($row[$i] != '')
				echo "<td class='sumatoriasRep'>".round($row[$i], 2)."</td>";
			else	
				echo "<td class='sumatoriasRep'>&nbsp;</td>";*/
			
			if($user_tipo_sistema=='local'){
				if($row[$i] != ''){	
					$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cols[$i].($numFil+1),$row[$i]);
				}else{
					$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cols[$i].($numFil+1), "");
				}
			}else if($user_tipo_sistema=='linea'){
				$datos_csv.=$cols[$i];
				if($i<sizeof($row)-1){
					$datos_csv.=',';//concatenamos coma
				}else{
					$datos_csv.="\n";//concatenamos salto de línea
				}
			}	
		}//fin de for
		//echo "</tr>";
	}

 	if($user_tipo_sistema=='local'){
		// Renombrar Hoja
		$objPHPExcel->getActiveSheet()->setTitle('Reporte');
 
		// Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
		$objPHPExcel->setActiveSheetIndex(0);
 
		// Se modifican los encabezados del HTTP para indicar que se envia un archivo de Excel.
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="reporte.xlsx"');
		header('Cache-Control: max-age=0');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;
	}else{
		$nombre="reporte.csv";
	//generamos descarga
		header('Content-Type: aplication/octect-stream');
		header('Content-Transfer-Encoding: Binary');
		header('Content-Disposition: attachment; filename="'.$nombre.'"');
		echo(utf8_decode($datos_csv));
	}
	
?>