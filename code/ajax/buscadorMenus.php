<?php
	include('../../conectMin.php');
	$clave=explode(' ',$_GET['clave_coincidencia']);
	$sub_url=$_GET['posicion'];
	$id_perfil_usuario;
	$sql="SELECT
			m.id_menu,
			m.nombre,
			IF(m.es_listado,
				CONCAT('$sub_url','code/general/listados.php?tabla=',TO_BASE64(m.tabla_relacionada),'&no_tabla=',TO_BASE64(m.no_tabla)),
				CONCAT('$sub_url',m.liga)
			) as link
		FROM sys_menus m
		LEFT JOIN sys_permisos sp ON m.id_menu=sp.id_menu
		WHERE sp.id_perfil=$perfil_usuario
		AND (sp.ver=1 OR sp.modificar=1 OR sp.eliminar=1 OR sp.nuevo=1 OR sp.imprimir=1 OR sp.generar=1)
		AND (";
//agudizamos busqueda por coincidencia
	for($i=0;$i<sizeof($clave);$i++){
		if($i>0){
			$sql.=' AND ';
		}
		$sql.="m.nombre like '%".$clave[$i]."%'";
	}
	$sql.=')';
	$eje=mysql_query($sql)or die("Error al consultar coincidencias en menus!!!<br>".mysql_error());
	if(mysql_num_rows($eje)<=0){
		die('Sin coincidencias!!!');
	}
//listamos menus
	$c=0;
	echo '<table width="100%;">';
	while($r=mysql_fetch_row($eje)){
		$c++;//incrementamos contador
		echo '<tr id="res_menu_'.$c.'" tabindex="'.$c.'" onclick="redirecciona_menu_por_busqueda(\''.$r[2].'\');" ';
		echo 'onfocus="resalta_menu(this);" onblur="regresa_color_menu(this);" onkeyup="valida_tca_res_menu(event,'.$c.' );">';
			echo '<td style="padding:10px;">';
				echo $r[1];
			echo '</td>';
		echo '</tr>';
	}
	echo '</table>';


?>