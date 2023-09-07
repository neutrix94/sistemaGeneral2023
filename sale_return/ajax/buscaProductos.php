<?php     
    include("../../conectMin.php");
    
    header("Content-Type: text/plain;charset=utf-8");
    if (function_exists("mb_internal_encoding")) mb_internal_encoding ('utf-8');
    mysql_set_charset("utf8");
    
    extract($_GET);
    
    $clave=trim($_GET['clave']);
    
    $noms=explode(" ", $_GET['clave'] );

	$sql = "SELECT
            p.id_productos AS product_id,
			CONCAT(p.orden_lista,' | ',p.nombre,' | ', pp.clave_proveedor, ' - $ ', pd.precio ),
            pp.id_proveedor_producto AS product_provider_id,
            p.orden_lista AS list_order,
            p.nombre AS product_name,
            pp.clave_proveedor AS provider_clue,
            IF( p.es_maquilado = 0, 
              SUM( pvu.piezas_validadas ),
              (SELECT
                  ROUND( ( SUM( pvu.piezas_validadas ) * 1 ) / cantidad )
                FROM ec_productos_detalle
                WHERE id_producto = p.id_productos
              )
            ) AS validated_pieces,
			pp.id_proveedor_producto,
			IF( pp.codigo_barras_pieza_1 = '{$clave}'
				OR pp.codigo_barras_pieza_2 = '{$clave}' 
				OR pp.codigo_barras_pieza_3 = '{$clave}'
				OR pp.codigo_barras_presentacion_cluces_1 = '{$clave}' 
				OR pp.codigo_barras_presentacion_cluces_2 = '{$clave}'
				OR pp.codigo_barras_caja_1 = '{$clave}'
				OR pp.codigo_barras_caja_2 = '{$clave}', 1, 0 ) AS is_per_barcode,
            pvu.id_pedido_detalle
		FROM ec_pedidos_detalle pd 
		LEFT JOIN ec_pedidos_validacion_usuarios pvu
		ON pvu.id_pedido_detalle = pd.id_pedido_detalle
		LEFT JOIN ec_productos p 
		ON p.id_productos = pd.id_producto
		LEFT JOIN ec_proveedor_producto pp
		ON pp.id_proveedor_producto = pvu.id_proveedor_producto
		WHERE pd.id_pedido = {$p} AND ( (";
//afina búsqueda por nombre
    for($i=0;$i<sizeof($noms);$i++){
    	$sql .= ( $i > 0 ? " AND " : "" );
    	$sql .= " p.nombre LIKE '%".$noms[$i]."%'";
	}
    $sql .= ") OR pp.codigo_barras_pieza_1 = '{$clave}' 
    OR pp.codigo_barras_pieza_2 = '{$clave}' 
    OR pp.codigo_barras_pieza_3 = '{$clave}' 
	OR pp.codigo_barras_presentacion_cluces_1 = '{$clave}'  
	OR pp.codigo_barras_presentacion_cluces_2 = '{$clave}' 
	OR pp.codigo_barras_caja_1 = '{$clave}' 
	OR pp.codigo_barras_caja_2 = '{$clave}' 
    OR pp.clave_proveedor = '{$clave}' 
    OR p.orden_lista = '{$clave}' )";
	$sql .= " GROUP BY pd.id_pedido_detalle, pvu.id_proveedor_producto";/*modificacion Oscar 2023 para separar por proveedor producto*/

    $res=mysql_query($sql) or die("Error en:\n\nDescripción: ".mysql_error());
    $num=mysql_num_rows($res);
    echo "exito";	
    //regresamos resultados de productos
    	if($num>0){
		for($i=0;$i<$num;$i++){
        	$row=mysql_fetch_row($res);
        	echo "←";
        	echo $row[0]."~".$row[1] . " | ( {$row[6]} pzs ) | {$row[7]} | {$row[9]}";//
    	}          
    }/*
    if($num+$num_1<=0){
    	echo "←~Sin coincidencias!!!";
    }*/
    //}
?>