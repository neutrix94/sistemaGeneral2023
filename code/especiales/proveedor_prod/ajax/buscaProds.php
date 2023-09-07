<?php
	include("../../../../conectMin.php");
	extract($_POST);
//buscador de orden de lista, id de grid
	//die($clave);
	if($fl==1){
		$sql="SELECT nombre,
					id_productos,
					orden_lista
				FROM ec_productos 
				WHERE 
				orden_lista LIKE '%$clave%' 
				OR (";
	//afinamos busqueda
		$ax=explode(" ",$clave);
		for($i=0;$i<sizeof($ax);$i++){
			if($ax[$i]!="" && $i>0){
				$sql.=" AND ";
			}
			if($ax[$i]!=""){
				$sql.="nombre LIKE '%".$ax[$i]."%'";
			}
		}
		$sql.=")";
		$eje=mysql_query($sql)or die("Error al buscar coincidencias!!!\n\n".$sql."\n\n".mysql_error());
	//formamos opciones
		$c=0;
		echo 'ok|<table width="100%">';
			while($r=mysql_fetch_row($eje)){
				$c++;
				echo '<tr onclick="empate_manual('.$c.','.$posicion.')" onkeyup="resalta_opc(event,'.$c.');">';
					echo '<td id="opc_gr_'.$c.'" tabindex="'.$c.'">'.$r[2]."~".$r[0].
					'<input type="hidden" id="datos_opc_'.$c.'" value="'.$r[1].'~'.$r[2].'~'.$r[0].'"></td>';
				echo '</tr>';
			}
		echo '</table>';
		return;
	}
//buscador general
	$sql="SELECT
				p.id_productos,
				p.nombre,
				p_p.id_proveedor_producto
				FROM ec_proveedor_producto p_p
				LEFT JOIN ec_productos p ON p_p.id_producto=p.id_productos
				WHERE p.id_producto=$clave OR (";
//conformamos busqueda mas exacta
	$arr=explode(" ",$clave);
	for($i=0;$i<sizeof($arr);$i++){
		if($arr[$i]!="" && $i>0){
			$sql.=" AND ";
		}
		if($arr[$i]!=""){
			$sql.="p.nombre LIKE '%$arr[0]%'";
		}
	}
	$sql.=")";
$eje=mysql_query($sql)or die("Error al buscar coincidencias del producto!!!\n\n".$sql."\n\n".mysql_error());
?>