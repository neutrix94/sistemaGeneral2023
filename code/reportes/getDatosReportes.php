<?php
	include("../../conectMin.php");
	
	extract($_GET);
	//die($extras);
	echo "exito|";
	//die($filt_externos);
	//echo "de: ".$fecdel."\n\n a :".$fecal;
//Buscamos las cabeceras
	$sql="SELECT
	      campo, 
	      display
	      FROM sys_reportes_columnas
	      WHERE id_reporte=$id_reporte";

/*modificación Oscar 06.06.2018 para desaparecer columnas de reporte de inventarios*/
		if($id_reporte==34&&$id_sucursal!=-1){
			$condicion_inventario=" AND id_reporte_columna IN(209,210,211,";//inicializamos variable que contendrá que columnas aplican	
			$arr_suc=explode("~",$id_sucursal);//separamos el arreglo de sucursales
			for($i=0;$i<=sizeof($arr_suc)-1;$i++){
				if($arr_suc[$i]!=''||$arr_suc[$i]!=null){
				//condicionamos de acuerdo a la sucursal

				$extras="";//reiniciados los extras
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
						$condicion_inventario.="249";//tultepec

					$condicion_inventario.=",";
				}
			}//fin de for i
			$sql.=$condicion_inventario."221)";
		}
	$sql.=" ORDER BY orden";//concatenamos el orden de las columnas en la consulta
/*fin de cambio*/
	  //die($sql);

	$res=mysql_query($sql) or die("Error 0 en:<br>$sql<br><br>Descripcion:<br>".mysql_error().$sql);
	$num=mysql_num_rows($res);
	$columnas=Array();
	echo "<tr>";//abrimos fila
	for($i=0;$i<$num;$i++)
	{
		$row=mysql_fetch_row($res);
		echo "<td class='headerReporte' align=\"center\">".$row[1]."</td>";
	}	  
	echo "</tr>";
	
	//Buscamos los datos generales
	$sql="SELECT
	      consulta,
	      campo_fecha,
	      ver_sumatorias,
	      consulta_sum,
		  campoSucursal
	      FROM sys_reportes
	      WHERE id_reporte=$id_reporte";
		  
	$res=mysql_query($sql) or die("Error 1 en:<br>$sql<br><br>Descripcion:<br>".mysql_error().$sql);
	
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
//die($sql);
/***********************************************************************************************************************************/
/**********************************************************FILTROS DE FECHAS********************************************************/
/***********************************************************************************************************************************/

	$condiciones="";
/*fecha normal*/
	if($tipoFec == '1' && $campoFecha != 'NO'){
		if($id_reporte==35){
			$extras.=" AND ped.fecha_alta LIKE '%".date('Y-m-d')."%'";//implementación Oscar 20.08.2018
			$condiciones_35.=" AND dev.fecha='".date('Y-m-d')."'";
		}else{
			$condiciones.=" AND $campoFecha >= '".date('Y-m-d')."' AND $campoFecha <= '".date('Y-m-d')."'";
		}
	}

/*fecha de la última semana*/
	if($tipoFec == '2' && $campoFecha != 'NO'){		
		$semana=date("W");
		$year=date("Y");
		//echo $semana;
		for($mes=1;$mes<=12;$mes++){
			//echo "bulto $mes<br>";
    		$limite = date('t',mktime(0,0,0,$mes,1,$year));
    		for($dia=1;$dia<$limite;$dia++){
        		if(date('W',mktime(0, 0, 0, $mes  , $dia, $year)) == $semana){
        			//echo "bulo $dia-$mes<br>";
            		if(date('N',mktime(0, 0, 0, $mes  , $dia, $year)) == 1){
                		//echo 'Lunes '.date('d-m-Y',mktime(0, 0, 0, $mes  , $dia, 2010)).' y Domingo '.date('d-m-Y',mktime(0, 0, 0, $mes  , $dia+6, 2010));
                	/*implementación Oscar 20.08.2018*/
                		if($id_reporte==35){
                			$extras.=" AND ped.fecha_alta BETWEEN '".date('Y-m-d',mktime(0, 0, 0, $mes  , $dia, $year))." 01:00:00' AND '".date('Y-m-d',mktime(0, 0, 0, $mes  , $dia+6, $year))."  23:59:59'";
                			$condiciones_35.=" AND dev.fecha BETWEEN '".date('Y-m-d',mktime(0, 0, 0, $mes  , $dia, $year))."' AND '".date('Y-m-d',mktime(0, 0, 0, $mes  , $dia+6, $year))."'";
                		}else{
                			$condiciones.=" AND $campoFecha >= '".date('Y-m-d',mktime(0, 0, 0, $mes  , $dia, $year))."' AND $campoFecha <= '".date('Y-m-d',mktime(0, 0, 0, $mes  , $dia+6, $year))."'";
                		}
                	/*fin de cambio 20.08.2018*/
            		}
        		}
    		}//fin de for $dia
		}//fin de for $mes  		
	}

/*fecha por este mes*/
	if($tipoFec == '3' && $campoFecha != 'NO'){
		$mes=date("m");
		$year=date("Y");
	/*implementación Oscar 20.08.2018*/
     	if($id_reporte==35){
			$extras.=" AND ped.fecha_alta BETWEEN '$year-$mes-1 01:00:00' AND '$year-$mes-31 23:59:59'";
			$condiciones_35.=" AND dev.fecha BETWEEN '$year-$mes-1' AND '$year-$mes-31'";
		}else{
			$condiciones.=" AND $campoFecha >= '$year-$mes-1' AND $campoFecha <= '$year-$mes-31'";			
		}
	/*fin de cambio 20.08.2018*/
	}

/*fecha personalizada*/
	if($tipoFec == '4' && $campoFecha != 'NO'){
		//implementacion de Oscar 23-12-2017
			if($id_reporte==40){
				if($fecdel==''||$fecal==''||$fecdel=='' && $fecal==''){
				}else{
					$condiciones.=" AND $campoFecha >= '$fecdel' AND $campoFecha <= '$fecal'";
					//echo 'condic: '.$condiciones;
				}
			}else{
			/*implementación Oscar 20.08.2018*/
				if($id_reporte==35){
					$extras.=" AND ped.fecha_alta BETWEEN '".$fecdel." 01:00:00' AND '".$fecal." 23:59:59'";//implementación Oscar 20.08.2018
					$condiciones_35.=" AND dev.fecha BETWEEN '".$fecdel."' AND '".$fecal."'";
				}else{
					$condiciones.=" AND $campoFecha >= '$fecdel' AND $campoFecha <= '$fecal'";
				}
			/*fin de cambio 20.08.2018*/				
			}
		//fin de cambio
	}

/*fecha ayer*/
	if($tipoFec == '6' && $campoFecha != 'NO'){
		$hoy=date("Y-m-d");
	/*implementación Oscar 20.08.2018*/
     	if($id_reporte==35){
			$extras.=" AND ped.fecha_alta BETWEEN 'DATE_ADD('$hoy', INTERVAL -1 DAY) 01:00:00' AND 'DATE_ADD('$hoy', INTERVAL -1 DAY) 23:59:59'";
			$condiciones_35.=" AND dev.fecha BETWEEN 'DATE_ADD('$hoy', INTERVAL -1 DAY)' AND 'DATE_ADD('$hoy', INTERVAL -1 DAY)'";			
		}else{
			$condiciones.=" AND $campoFecha >= DATE_ADD('$hoy', INTERVAL -1 DAY) AND $campoFecha <= DATE_ADD('$hoy', INTERVAL -1 DAY)";
		}
	}

/*fecha de últimos 7 días*/
	if($tipoFec == '7' && $campoFecha != 'NO'){
		$hoy=date("Y-m-d");
	/*implementación Oscar 20.08.2018*/
     	if($id_reporte==35){
			$extras.=" AND ped.fecha_alta >= 'DATE_ADD('$hoy', INTERVAL -7 DAY) 11:59:59'";
			$condiciones_35.=" AND dev.fecha >= 'DATE_ADD('$hoy', INTERVAL -7 DAY)'";			
		}else{
			$condiciones.=" AND $campoFecha >= DATE_ADD('$hoy', INTERVAL -7 DAY)";
		}
	/*fin de cambio 20.08.2018*/	
	}

/*fecha de los últimos 30 días*/
	if($tipoFec == '9' && $campoFecha != 'NO'){
		$hoy=date("Y-m-d");
	/*implementación Oscar 20.08.2018*/
     	if($id_reporte==35){
			$extras.=" AND ped.fecha_alta >= 'DATE_ADD('$hoy', INTERVAL -30 DAY) 11:59:59'";
			$condiciones_35.=" AND dev.fecha >= 'DATE_ADD('$hoy', INTERVAL -30 DAY)'";	
		}else{
			$condiciones.=" AND $campoFecha >= DATE_ADD('$hoy', INTERVAL -30 DAY)";			
		}
	/*fin de cambio 20.08.2018*/	
	}

/*fecha de el mes pasado*/
	if($tipoFec == '10' && $campoFecha != 'NO'){
		$mes=date("m");
		$year=date("Y");
		$mes--;
		if($mes <= 0){
			$mes=12;
			$year--;
		}
	/*implementación Oscar 20.08.2018*/
     	if($id_reporte==35){	
			$extras.=" AND ped.fecha_alta BETWEEN '$year-$mes-1' AND '$year-$mes-31'";
			$condiciones_35.=" AND dev.fecha BETWEEN '$year-$mes-1' AND '$year-$mes-31'";
		}else{
			$condiciones.=" AND $campoFecha >= '$year-$mes-1' AND $campoFecha <= '$year-$mes-31'";			
		}
	/*fin de cambio 20.08.2018*/
	}

/*fecha de los últimos 90 días*/
	if($tipoFec == '11' && $campoFecha != 'NO'){
		$hoy=date("Y-m-d");
	/*implementación Oscar 20.08.2018*/
     	if($id_reporte==35){
			$extras.=" AND ped.fecha_alta >= DATE_ADD('$hoy', INTERVAL -90 DAY) 01:00:00";
			$condiciones_35.=" AND dev.fecha >= DATE_ADD('$hoy', INTERVAL -90 DAY)";
		}else{
			$condiciones.=" AND $campoFecha >= DATE_ADD('$hoy', INTERVAL -90 DAY)";			
		}
	/*fin de cambio 20.08.2018*/
	}

/***********************************************************************************************************************************/
/********************************************************FILTROS DE SUCURSAL********************************************************/
/***********************************************************************************************************************************/
	if($id_sucursal != -1){
		/*if($id_reporte==11){
			$condiciones.=" AND sp.id_sucursal='$id_sucursal'";
		}*/
	//implementación de Oscar 01.06.2018
		if($id_reporte==33){
			$condicion_anidada=" AND $campoSucursal = $id_sucursal";
		}
	//fin de cambio 01.06.2018
		if($id_reporte!=34 && $id_reporte!=35){//se agrega condición para evitar que se filtre el reporte de todos los inventarios
			$condiciones.=" AND $campoSucursal = $id_sucursal";
		}
	/*implementación Oscar 20.10.2018*/
		if($id_reporte==35){
			$extras.=" AND ped.id_sucursal=".$id_sucursal;
			$condiciones_35.=" AND dev.id_sucursal=".$id_sucursal;
		}
	/*fin de cambio*/
	}



/*Implementación de Oscar 06.06.2018 para el reporte de todos los inventarios*/
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
		//	die($filtro_tipo_almacen);
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
		//	die($filtro_tipo_almacen);
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
			SUM(IF(ma.id_sucursal=11,IF(md.cantidad IS NULL$cond_mov,0,md.cantidad*tm.afecta),0)) AS invTultepec,",$sql);
		}
	}
/*Fin de cambio 06.06.2018*/	
	/*
	

	*/
	
	
	$condiciones.=" $extras";

/*Implementación de Oscar 17.08.2018 para filtrar por folio de ajustes de inventario*/
	if($id_reporte==40 && $folio_ajuste!='' && $folio_ajuste!=null){
		$condiciones.=" AND ma.observaciones='".$folio_ajuste."'";
	}
/*Fin de cambio*/

	//echo "<br><br>$sql";
/*Implementación de Oscar 01.06.2018 para filtrar reporte 33 por sucursal*/
	if($id_reporte==33){
		$sql=str_replace("YY",$condicion_anidada, $sql);
	}
/*Fin de cambio 01.06.2018*/

/*implementación Oscar 20.08.2018 para reporte de todas las ventas*/
//die($condiciones_35);
	if($id_reporte==35){
		//die("condiciones: ".$extras);
		$sql=str_replace('YYY',$extras,$sql);
		$sql=str_replace('_ZZZ_',$condiciones_35,$sql);
	}
/*finde cambio 20.08.2018*/
	$sql=str_replace('$fecIni', $fecdel, $sql);
	$sql=str_replace('$fecFin', $fecal, $sql);
	$sql=str_replace("XXX", $condiciones, $sql);
	
	//echo "<br><br>$sql";
	
	
	$sql.=" ".$adicionales;
	
//die($fecdel."~".$tipoFec);	
//die($sql);
		
	$res=mysql_query($sql) or die("Error 2 en:<br>$sql<br><br>Descripcion:<br>".mysql_error().$sql);

echo $sql;
	
	$num=mysql_num_rows($res);
	
	if($id_reporte == 32)
		$num=0;
	
	for($i=0;$i<$num;$i++)
	{
		$row=mysql_fetch_row($res);
		echo "<tr>";
		
		for($j=0;$j<sizeof($row);$j++)
		{
			echo "<td class='datos'>".$row[$j]."</td>";
		}
		
		echo "</tr>";
	}
	
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
		$total_final=0;
		for($i=0;$i<$num;$i++)
		{
			$row=mysql_fetch_row($res);
/*Modificación Oscar 26.02.2018; se cambia la tabla ec_empleado e por sys_users u y el prefijo de los campos correspondientes*/
			$sq="SELECT
					ax1.Nombre,
					ax1.fecha,
					ax1.hora_entrada,
					ax1.hora_salida,
					CONCAT(
						ax1.Horas,' <b>(',
						IF(ROUND((ax1.Horas*60) DIV 60)<10,
							CONCAT('0',ROUND((ax1.Horas*60) DIV 60)),
							ROUND((ax1.Horas*60) DIV 60)
						),
						':',
						IF(ROUND((ax1.Horas*60) % 60)<10,
							CONCAT('0',ROUND((ax1.Horas*60) % 60)),
							ROUND((ax1.Horas*60) % 60)
						),
						' hrs)</b>'
					)as horasTrabajadas,
					ax1.sueldo,
					ax1.detalleHoras,
					CONCAT(
						IF(ROUND(ax1.tiempoSinTrabajar DIV 60)<10,
							CONCAT('0',ROUND(ax1.tiempoSinTrabajar DIV 60)),
							ROUND(ax1.tiempoSinTrabajar DIV 60)
						),
						':',
						IF(ROUND(ax1.tiempoSinTrabajar % 60)<10,
							CONCAT('0',ROUND(ax1.tiempoSinTrabajar % 60)),
							ROUND(ax1.tiempoSinTrabajar % 60)
						),
						' hrs'
					)as tiempoSinTrabajar
				FROM(
					SELECT DISTINCT
					ax.Nombre,/*nombre*/
					ax.fecha,/*fecha*/
					ax.hora_entrada,/*hora_entrada*/
					ax.hora_salida,/*hora_salida*/
					/*u.pago_hora,*/
					ax.Horas,/*horas*/
					IF(u.pago_dia>0 AND ax.Horas>=u.minimo_horas, u.pago_dia,u.pago_hora*ax.Horas) as sueldo,
					ax.detalleHoras,
					(ROUND(TIME_FORMAT(TIMEDIFF(ax.hora_salida, ax.hora_entrada), '%H') +(TIME_FORMAT(TIMEDIFF(ax.hora_salida, ax.hora_entrada), '%i')/60),2)
					-ax.Horas)*60 as tiempoSinTrabajar
					/*Deshab*
					ROUND(TIMEDIFF(ax.hora_salida,ax.hora_entrada))-ax.Horas)*/
					FROM
					(SELECT
						CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS Nombre,
						rn.fecha,
						u.id_usuario AS ID,
						MIN(rn.hora_entrada) AS hora_entrada,
						MAX(rn.hora_salida) AS hora_salida,
						SUM(ROUND(TIME_FORMAT(TIMEDIFF(rn.hora_salida, rn.hora_entrada), '%H') +(TIME_FORMAT(TIMEDIFF(rn.hora_salida, rn.hora_entrada), '%i')/60),2)) Horas,
					/*Deshabilitado por Oscar 08.11.2018 (Ya no sirve para nada lo dejo comentado por si en un futuro se quiere agarrar este cálculo anterior de Base)*/
					/*SUM(ROUND((TIME_FORMAT(TIMEDIFF(rn.hora_salida, rn.hora_entrada), '%H') +(TIME_FORMAT(TIMEDIFF(rn.hora_salida, rn.hora_entrada), '%i')/60))*u.pago_hora)) Sueldo*/
						GROUP_CONCAT('DE  ',rn.hora_entrada,'  A  ',rn.hora_salida SEPARATOR '<br>') as detalleHoras
						FROM ec_registro_nomina rn
						JOIN sys_users u ON rn.id_empleado = u.id_usuario
						WHERE rn.id_empleado = ".$row[0]."
						$condiciones
						GROUP BY rn.fecha, u.id_usuario
					)ax
					LEFT JOIN sys_users u on ax.ID=u.id_usuario
					ORDER BY ax.fecha, ax.hora_entrada
				)ax1";
/*Fin de modificación 26.02.2018*/

			$re=mysql_query($sq) or die(mysql_error());
			
			$nu=mysql_num_rows($re);
			$tot=0;
			$TotSueldo=0;

			for($j=0;$j<$nu;$j++)
			{
				$ro=mysql_fetch_row($re);
				
				echo "<tr>";
				if($j == 0)
					echo "<td class='datos'>".$ro[0]."</td>";
				else
					echo "<td class='datos'>&nbsp;</td>";
						
				echo "<td class='datos'>".$ro[1]."</td>";
				echo "<td class='datos'>".$ro[2]."</td>";
				echo "<td class='datos'>".$ro[3]."</td>";
				echo "<td class='datos'>".$ro[4]."</td>";
				echo "<td class='datos'>".$ro[5]."</td>";
				echo "<td class='datos'>".$ro[6]."</td>";
				echo "<td class='datos' style=\"padding:15px;\">".$ro[7]."</td>";
				
				$tot+=$ro[4];
				$TotSueldo+=$ro[5];
				
				echo "</tr>";	
				
			}
			
			echo "<tr>";
			
			$tgral+=$tot;
			$dgral+=round($tot*$ro[6]);
			
			echo "<td class='datos'><b>Total ".$ro[0]."</b></td>";		
			echo "<td class='datos'>&nbsp;</td>";
			echo "<td class='datos'>&nbsp;</td>";
			echo "<td class='datos'>&nbsp;</td>";
			echo "<td class='datos'><b>$tot</b></td>";
			echo "<td class='datos'><b>$TotSueldo</b></td>";
			/*echo "<td class='datos'><b>".($tot*$ro[6])."</b></td>";*/
					
					
			echo "</tr>";	
			
			$total_final+=$TotSueldo;
			
		}
		
		echo "<tr class='filaSumatoria'>";
		echo "<td class='sumatoriasRep'>&nbsp;</td>";
		echo "<td class='sumatoriasRep'>&nbsp;</td>";
		echo "<td class='sumatoriasRep'>&nbsp;</td>";
		echo "<td class='sumatoriasRep'>TOTAL</td>";
		echo "<td class='sumatoriasRep'>$tgral</td>";
		echo "<td class='sumatoriasRep'>$total_final</td>";
		echo "<td class='sumatoriasRep'></td>";
		echo "<td class='sumatoriasRep'></td>";
		echo "</tr><tr class='filaFinal'><td>&nbsp;</td></tr>";		
				
	}
	


	//Sumatorias
	if($sums == '1')
	{
		$sql=$consulta_sum;
	
//echo 'Sumatoria: '.$sql;
		
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
		if($tipoFec == '4' && $campoFecha != 'NO'){
		//implementacion de Oscar 23-12-2017
			if($id_reporte==40){
				if($fecdel==''||$fecal==''||$fecdel=='' && $fecal==''){
				}else{
					$condiciones.=" AND $campoFecha >= '$fecdel' AND $campoFecha <= '$fecal'";
					//echo 'condic: '.$condiciones;
				}
			}else{
				$condiciones.=" AND $campoFecha >= '$fecdel' AND $campoFecha <= '$fecal'";
			}
		//fin de cambio
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
		//die("id_sucursal: ".$id_sucursal);
		if($id_sucursal != -1){
			/*if($id_reporte==11){
				$condiciones.=" AND sp.id_sucursal='$id_sucursal'";
			}else{*/
				$condiciones.=" AND $campoSucursal = $id_sucursal";
			//}
		}
		
		$condiciones.=" $extras";
		
//echo $sql;
		
		$sql=str_replace("XXX", $condiciones, $sql);
		
		$sql.=" $adicionales";

		
//echo '<br>con adic='.$sql;
			
		$res=mysql_query($sql) or die("Error en 3:<br>$sql<br><br>Descripcion:<br>".mysql_error().$sql);
		
		$row=mysql_fetch_row($res);
		
		echo "<tr class='filaSumatoria'>";
		for($i=0;$i<sizeof($row);$i++)
		{
			if($row[$i] != '')
				echo "<td class='sumatoriasRep'>".$row[$i]."</td>";
			else	
				echo "<td class='sumatoriasRep'>&nbsp;</td>";
		}
		echo "</tr><tr class='filaFinal'><td>&nbsp;</td></tr>";
	}

?>