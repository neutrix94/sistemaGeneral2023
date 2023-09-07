<?php
	include('../../../../conectMin.php');
//extraemos datos por post
	extract($_POST);
if($id_proveedor==''){
if($filt_deshab!=1){
	$filtro_deshabilitados=" AND habilitado=1";
}else{
	$filtro_deshabilitados="";
}

//base de consulta
	$sql="SELECT id_productos,nombre
			FROM ec_productos
			WHERE id_productos!=-1 AND es_maquilado=0 $filtro_deshabilitados";
//condicionamos consulta
	
	$sql.=" AND( (";
	$ax=explode(" ",$txt);
	for($i=0;$i<sizeof($ax);$i++){
		if($i>0&&$ax[$i]!=''){
			$sql.=" AND ";
		}
		if($ax[$i]!=''){
			$sql.="nombre LIKE '%".$ax[$i]."%'";
		}
	}
	$sql.=") OR orden_lista LIKE '%".$txt."%')";
//	echo "ok|{$sql}";
}//fin de if
/********Implementaci贸n para buscar de acuerdo a la orden de compra****************/
else{
//buscamos coincidencias en la orden de compra
	$sql="SELECT p.id_productos,p.nombre
			FROM ec_productos p
			LEFT JOIN ec_oc_detalle ocd ON p.id_productos=ocd.id_producto
			WHERE p.habilitado=1 AND p.es_maquilado=0 AND ocd.id_orden_compra=$id_oc";// ocd.id_proveedor=$id_proveedor"
//condicionamos consulta
	$sql.=" AND(";
	$ax=explode(" ",$txt);
	for($i=0;$i<sizeof($ax);$i++){
		if($i>0&&$ax[$i]!=''){
			$sql.=" AND ";
		}
		if($ax[$i]!=''){
			$sql.="p.nombre LIKE '%".$ax[$i]."%'";
		}
	}
	$sql.=") OR p.orden_lista LIKE '%".$txt."%'";	
}
	$eje=mysql_query($sql)or die("Error al buscar coincidencias\n\n".$sql."\n\n".mysql_error());
	echo 'ok|';
	$num=mysql_num_rows($eje);
		//die('$num'.$num);
	if($num<1){//si no hay coincidencias;
		die("Sin coincidencias!!!");
	}
//	echo $sql;
	echo '<table width="100%" id="resulta">';
			$contador=0;
			while($row=mysql_fetch_row($eje)){
				$contador++;//incrementamos contador
				echo '<tr class="opcion" id="tr_1_'.$contador.'" onclick="busca_prod_grid('.$row[0].');" onkeyup="valida_opc_busc(event,'.$contador.','.$row[0].');">
				<td width=100% id="r_'.$contador.'" tabindex="'.$contador.'">
				'.$row[1].'</td><td id="id_'.$contador.'" style="display:none;">'.$row[1].'</td>
				</tr>';
			}//cierra while
			echo '</table>';

?>