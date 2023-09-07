<?php
	include('../../conectMin.php');//incluimos la librería de conexión

/*implementacion Oscar 26.10.2019 para validacion de sesion de caja al querer insertar un nuevo gasto*/
	if(isset($_GET['fl']) && $_GET['fl']=='sesion_caja'){
	//
		$sql="SELECT count(*) FROM ec_sesion_caja WHERE fecha=DATE_FORMAT(now(),'%Y-%m-%d') AND id_sucursal=$user_sucursal AND hora_fin='00:00:00'";
		$eje=mysql_query($sql)or die("Error al verificar que haya sesión de caja abierta!!!\n".mysql_error());
		$r=mysql_fetch_row($eje);
		if($r[0]<1){
			die("No hay ninguna sesion de caja\nInicie sesion de caja para poder registrar gastos!!!");
		}else{
			die('ok');
		}
	}
/*Fin de cambio Oscar 26.10.2019*/

//armamos la consulta
	$sql="SELECT 
			t.folio,
			t.fecha,
			s_orig.nombre,
			s_dest.nombre,
			est.nombre
			FROM ec_transferencias t
			LEFT JOIN sys_sucursales s_orig on t.id_sucursal_origen=s_orig.id_sucursal
			LEFT JOIN sys_sucursales s_dest on t.id_sucursal_destino=s_dest.id_sucursal
			LEFT JOIN ec_estatus_transferencia est ON t.id_estado=est.id_estatus
		WHERE t.id_estado<=4
		AND IF(t.es_resolucion=0,(t.id_sucursal_origen=$user_sucursal OR t.id_sucursal_destino=$user_sucursal),t.id_sucursal_destino=$user_sucursal)";
	$eje=mysql_query($sql)or die("Error al consultar Transferencias Pendienetes!!!\n\n".$sql."\n\n".mysql_error());
//regresamos respuesta si no hay transferencias pendientes
	if(mysql_num_rows($eje)<1){
		die('ok|ok');
	}
//creamos botón para cerrar emergente
	$res='<button type="button" class="bot_crra" style="position:absolute;top:11%;right:18.5%;" onclick="document.getElementById(\'emergenteAutorizaTransfer\').style.display=\'none\';">X</button>';
//creamos tabla de referencia de transferencias
	$res.='<p align="center" style="font-size:30px;color:white;"><b>Las siguientes Transferencias están Pendientes:</b></p>';
	$res.='<div style="height:300px;overflow:auto;background:white;width:90%;"><table width="100%">';
		$res.='<tr>';
			$res.='<th align="center">Folio</th>';
			$res.='<th align="center">Fecha</th>';
			$res.='<th align="center">Origen</th>';
			$res.='<th align="center">Destino</th>';
			$res.='<th align="center">Estatus</th>';
		$res.='<tr class="fila">';
		while($r=mysql_fetch_row($eje)){
			$res.='<tr class="tr_1">';
				$res.='<td>'.$r[0].'</td>';
				$res.='<td>'.$r[1].'</td>';
				$res.='<td>'.$r[2].'</td>';
				$res.='<td>'.$r[3].'</td>';
				$res.='<td>'.$r[4].'</td>';
			$res.='<tr>';
		}

	$res.='</table></div>';
//generamos botón para continuar
	$res.='<br><button type="button" class="bt_continua" style="left:-10%;position:relative;" onclick="location.href=\'../especiales/Transferencias_desarrollo/transf.php\';">Continuar de todas formas</button>';	
	$res.='<button type="button" class="bt_continua" onclick="document.getElementById(\'emergenteAutorizaTransfer\').style.display=\'none\';">Cancelar</button><br><br>';
//generamos el estilo
	$res.='<style>';
		$res.='th{padding:10px;background:red;color:white;}';//estilo del encabezado
		$res.='.fila{padding:6px;background:red;color:white;}';//estilo de las filas
		$res.='.fila:hover{padding:10px;background:rgba(0,0,225,.8);color:whie;}';//hover de las filas
		$res.='.bot_crra{padding:15px;border-radius:6px;background:red;color:white;position:absolute;top:20px;right:5%;}';//estilo de boton cerrar
		$res.='.bt_continua{padding:10px;border-radius:8px;}';//botón para continuar
		$res.='.tr_1{height:30px;}';
		$res.='.tr_1:hover{background:rgba(0,225,0,.6);}';
	$res.='</style>';
	echo 'ok|'.$res;
?>