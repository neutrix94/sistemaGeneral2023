<?php
	php_track_vars;

	extract($_GET);
	extract($_POST);
	
	//CONECCION Y PERMISOS A LA BASE DE DATOS
	$soloConecar='SI';
	include("../../conectMin.php");
	
	echo "si|";
	//include("../../code/general/funciones.php");
	
	if(!isset($opcion))
	{
		if($tabla=='0'){
			echo "-- Seleccionar --";
			die();
		}
		
		// buscamos de la configuracion de menus 
		
		//$SQLCampos="SELECT campo, nombre FROM sys_config_encabezados where tabla='".$tabla."' and visible=1 AND en_base=1";
		
		//seleccionamos los datos
		$SQLCampos="SELECT campo, display FROM sys_catalogos WHERE tabla='$tabla'";
						
		//$SQLCampos = "SHOW COLUMNS FROM ".$tabla;
		
		//echo $SQLCampos;
		
		$ref = mysql_query($SQLCampos) or die(mysql_error());
		$arrCampos = array();
		while($linea = mysql_fetch_assoc($ref))
		{
			//array_push($arrCampos,$linea['Field']);
			array_push($arrCampos,$linea['campo'].'~'.iconv("ISO-8859-1","UTF-8",$linea['display']));
		
		}
		$cadena = implode("|",$arrCampos);
		echo $cadena;
		
		mysql_free_result($ref);
		//echo iconv("ISO-8859-1","UTF-8",$cadena);    iconv("ISO-8859-1","UTF-8",$linea['nombre'])
	}
	else
	{
		if($tabla=='ejercicios'){
			if($ejercicio=='0')
				$sqlWhere = "1";
			else
				$sqlWhere = ("id_ejercicio=".$ejercicio);
			$SQLCampos = "SELECT CONCAT(id_periodo,' : ','Del ',fecha_inicio,' al ',fecha_final,'->',Periodo) as periodo FROM `erp_periodos` WHERE ".$sqlWhere;
			$ref = mysql_query($SQLCampos) or die(mysql_error());
			$arrCampos = array();
			while($linea = mysql_fetch_assoc($ref))
				array_push($arrCampos,$linea['periodo']);
			$cadena = implode("|",$arrCampos);
		}
		else
		{
			$SQLCuentas = "SELECT CONCAT(id_cuenta_contable,'->',nombre_de_la_cuenta) AS cuenta FROM `erp_cuentas_contables` WHERE id_cuenta_superior = '".$tabla."'";
			$ref = mysql_query($SQLCuentas) or die(mysql_error());
			$valuesCuentas = array();
			if(mysql_num_rows($ref)>0)
				array_push($valuesCuentas,"-- Todas --");
			while($linea = mysql_fetch_array($ref))
				array_push($valuesCuentas,$linea[0]);
			$cadena = implode("|",$valuesCuentas);
			
		}
		echo $cadena;
	}
	die();
?>