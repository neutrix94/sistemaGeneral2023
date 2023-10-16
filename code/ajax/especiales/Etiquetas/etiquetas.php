<?php
	include("../../../../conexionMysqli.php");
	$texto=$_POST['texto'];
	$query = "SELECT
			  p.id_productos,
			  CONCAT(p.orden_lista,'|',p.nombre) 
			  FROM ec_productos p
			  LEFT JOIN ec_proveedor_producto pp
			  ON p.id_productos = pp.id_producto
			  WHERE p.nombre NOT IN( 'Libre', '0' ) 
			  AND p.orden_lista NOT IN( '0' )
			  AND ( (";
//amplia exactitud de búsqueda
	$arr=explode(" ",$texto);
	for($i=0;$i<sizeof($arr);$i++){
		if($arr[$i]!=''){
			if($i>0){
				$query.=" AND ";
			}
			$query.="p.nombre LIKE '%".$arr[$i]."%'";
		}
	}
	$query.=")";//cerramos el paréntesis del WHERE
	$query .= " OR p.orden_lista = '{$texto}'";
	$query .= " OR pp.clave_proveedor = '{$texto}'";
	$query .= " OR ( pp.codigo_barras_pieza_1 = '{$texto}' 
					OR pp.codigo_barras_pieza_2 = '{$texto}'
					OR pp.codigo_barras_pieza_3 = '{$texto}') )";

	$query .= " GROUP BY p.id_productos";die( $query );

	$result  = $link->query($query) or die('Prod: '.mysql_error());		  

	while( $fila = $result->fetch_row() ){
		$data[]= array(
						'id_pr'     => $fila[0],
						'nombre'    => $fila[1]
					);
	}
	echo json_encode($data);
?>