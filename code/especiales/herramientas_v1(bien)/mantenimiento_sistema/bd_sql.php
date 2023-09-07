<?php
	$fl=$_POST['flag'];
//1. Insercion de procedures
	if($fl=='procedures_inserta'){	
	//1.1. Incluye archivo /conexionDoble.php
		include('../../../../conexionDoble.php');
		$s=$hostLocal;
		$bd=$nombreLocal;
		$u=$userLocal;
		$p=$passLocal;

		$conexion_sqli=new mysqli($s,$u,$p,$bd);
		if($conexion_sqli->connect_errno){
			die("sin conexion");
		}else{
			//echo "conectado";
		}

		$cadena_arreglo="";
		$fp = fopen("../../../../respaldos/procedures.sql", "r")or die("Error");
		while (!feof($fp)){
		 	$linea = fgets($fp);
		 	$cadena_arreglo.=$linea;
		}
		fclose($fp);
	//echo $cadena_arreglo;
		//$cadena_arreglo=str_replace("DELIMITER $$", "", $cadena_arreglo);
		$arreglo_procedure=explode("|", $cadena_arreglo);
		for($i=0;$i<sizeof($arreglo_procedure);$i++){
	//		echo "Array: ".$arreglo_procedure[$i]."\n";
			$arreglo_procedure[$i]=str_replace("DELIMITER $$", "", $arreglo_procedure[$i]);
			$arreglo_procedure[$i]=str_replace("$$", "", $arreglo_procedure[$i]);
			$eje=mysqli_multi_query($conexion_sqli,$arreglo_procedure[$i]);
			if(!$eje){
				die("Error con mysqli!!!".mysqli_error($conexion_sqli));
			}
		}
		die('ok|');
	}
/*Fin de cambio Oscar 20.12.2019*/

//2. Incluye archivo /conectMin.php
	include('../../../../conectMin.php');
	$dato=$_POST['valor'];
	$id_agrupacion=$_POST['tipo_agrupacion'];
	$tipo_mantenimiento=$_POST['tipo'];

//3. Calculo de dias
	if($fl=='obtener_dias'){
		$fcha=$_POST['fecha'];
		$sql="SELECT TIMESTAMPDIFF(DAY,'$fcha',CURRENT_DATE())";
		$eje=mysql_query($sql)or die("Error al calcular los días de diferencia entre fechas!!!".mysql_error());
		$r=mysql_fetch_row($eje);
		die($r[0]);
	}

//4. Actualizacion de configuracion de agrupamiento
		switch($fl){
			case 'por_dia':
				$sql="UPDATE sys_configuracion_sistema SET minimo_agrupar_ma_dia='$dato' WHERE id_configuracion_sistema=1";
			break;
			case 'por_ano':
				$sql="UPDATE sys_configuracion_sistema SET minimo_agrupar_ma_ano='$dato' WHERE id_configuracion_sistema=1";
			break;
			case 'por_anteriores':
				$sql="UPDATE sys_configuracion_sistema SET minimo_agrupar_ma_anteriores='$dato' WHERE id_configuracion_sistema=1";
			break;
			case 'por_dia_vta':
				$sql="UPDATE sys_configuracion_sistema SET minimo_agrupar_vtas_dias='$dato' WHERE id_configuracion_sistema=1";
			break;
			case 'por_ano_vta':
				$sql="UPDATE sys_configuracion_sistema SET minimo_agrupar_vtas_ano='$dato' WHERE id_configuracion_sistema=1";
			break;
			case 'por_anteriores_vta':
				$sql="UPDATE sys_configuracion_sistema SET minimo_agrupar_vtas_anteriores='$dato' WHERE id_configuracion_sistema=1";
			break;
			case 'eliminar_sin_uso':
				$sql="UPDATE sys_configuracion_sistema SET minimo_eliminar_reg_no_usados='$dato' WHERE id_configuracion_sistema=1";
			break;
			case 'eliminar_alertas_inventario':
				$sql="UPDATE sys_configuracion_sistema SET minimo_eliminar_reg_sin_inventario='$dato' WHERE id_configuracion_sistema=1";
				//die($sql);
			break;

	/*Implementacion Oscar 20-09-2020 para recalcular inventarios de ec_almacen_producto por medio del boton*/
			case "recalcula_inventario_almacen" : 
				$sql = "CALL recalculaInventariosAlmacen()";
			break;
/*Fin de cambio*/
		}
//die($sql);
/**/ 

//5. Ejecucion de procedures
	if($fl=='procedure'){
		if($id_agrupacion==2){
			$sql="CALL parametrosAgrupaMovimientosAlmacen($id_agrupacion,$dato);";//flag:'procedure',valor:dato_nvo,tipo_agrupacion:flag
		}

		if($id_agrupacion==3){
			$sql="CALL parametrosAgrupaMovimientosAlmacenPorAno($id_agrupacion,$dato);";//flag:'procedure',valor:dato_nvo,tipo_agrupacion:flag
		}

		if($id_agrupacion==4){
			$sql="CALL agrupaMovimientosAlmacen($id_agrupacion,$dato);";//flag:'procedure',valor:dato_nvo,tipo_agrupacion:flag
		}	

		if($tipo_mantenimiento=='vta'){
		//	die('here');
			switch ($id_agrupacion) {
				case 2:
					$sql="CALL parametrosAgrupaVentas($id_agrupacion,$dato);"; 
					break;
				case 3:
					$sql="CALL parametrosAgrupaVentasPorAno($id_agrupacion,$dato);"; 
					break;
				case 4:
					$sql="CALL agrupaVentas($id_agrupacion,$dato);";
					break;
				case 5:
					$sql="CALL eliminaRegistrosMantenimiento($dato);";
					break;

				case 8:
					$sql="CALL eliminaRegistrosProductosSinInventario($dato);";
					break;
			}
		}	
	}
//tipo
//	mysql_query("BEGIN");//marcamos el inicio de transaccion
	$eje=mysql_query($sql);
	if(!$eje){
		$error=mysql_error();
//		mysql_query("ROLLBACK");//cancemos transaccion
		die($error);
	}

/*Implementacion Oscar 20-09-2020 para recalcular inventarios de ec_almacen_producto*/
	if($fl=='procedure'){
		$sql = "CALL recalculaInventariosAlmacen()";
		$eje = mysql_query($sql);
		if( !$eje ){
			$error=mysql_error();
			die("Error al recalcular el inventario de almacenes por producto : " . $error);
		}
	}
/*Fin de cambio*/

//	mysql_query("COMMIT");//autorizamos transaccion
	die('ok');
?>