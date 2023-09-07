<?php
	include("../../../conectMin.php");
	
	//var_dump( $_GET );
	extract($_GET);
    
    if(!isset($sucur))
        $sucur=-1;

		$sql="SELECT
				ax.id,
				ax.product_provider_id,
				ax.orden_lista,
				ax.clave_proveedor,
				ax.nombre,
				ax.nombre_almacen,
				ax.cantidad,
				ax.inventory,
				'ver'
			FROM(
				SELECT
					ipp.id_proveedor_producto AS product_provider_id,
					p.id_productos AS id,
					p.orden_lista,
					CONCAT( provProd.clave_proveedor, ' / ', provProd.presentacion_caja ) AS clave_proveedor,
					p.nombre,
					alm.nombre AS nombre_almacen,
					FORMAT( ipp.inventario, 4) AS cantidad,
					FORMAT( SUM( 
						IF( mdpp.id_movimiento_detalle_proveedor_producto IS NULL 
							OR mdpp.id_almacen != alm.id_almacen, 
							0, 
							( tm.afecta * mdpp.cantidad ) 
						) 
					), 4 )AS inventory
				FROM ec_productos p
				LEFT JOIN ec_inventario_proveedor_producto ipp ON ipp.id_producto = p.id_productos
				LEFT JOIN ec_almacen alm ON ipp.id_almacen = alm.id_almacen
				LEFT JOIN ec_proveedor_producto provProd ON provProd.id_proveedor_producto = ipp.id_proveedor_producto
				LEFT JOIN sys_sucursales_producto sp ON sp.id_producto = p.id_productos
				LEFT JOIN ec_movimiento_detalle_proveedor_producto mdpp
				ON mdpp.id_proveedor_producto = provProd.id_proveedor_producto
				AND mdpp.id_sucursal = '{$user_sucursal}'
				AND IF( '$sucur' != '-1', mdpp.id_almacen = '$sucur',  mdpp.id_almacen > 0 )
				LEFT JOIN ec_tipos_movimiento tm
				ON tm.id_tipo_movimiento = mdpp.id_tipo_movimiento
				WHERE sp.id_sucursal = '{$user_sucursal}'
				/*AND sp.estado_suc = 1 Deshabilitado por Oscar 23-mayo-2023 para mosrtrar todos los productos sin importar si estan habilitados o no en la sucursal*/
				AND ipp.id_sucursal = '{$user_sucursal}'"
				. ( $sucur != -1 ? " AND ipp.id_almacen = '{$sucur}'" : "" )
				. (isset($nombre) && strlen($nombre)>0 ? " AND p.nombre LIKE '%{$nombre}%' " : "").

				( ! isset($nombre) && strlen($nombre)<=0 ? " AND p.id_productos = 0":  "" ) ." ";/*implementacion Oscar 2023*/
			//ampliamos las coincidencias de búsqueda
			if( $valor!='' && $valor!=null ){	
				$sql.=" AND( (";
				$arr=explode(" ", $valor);
				for( $i=0; $i < sizeof( $arr ); $i++ ){
					if( $arr[$i] != '' && $arr[$i] != null ){
						if( $i > 0 ){
							$sql .= " AND ";
						}
						$sql .= "p.nombre LIKE '%".$arr[$i]."%'";
					}
				}//fin de for $i
				$sql.=") OR p.orden_lista LIKE '%{$valor}%' ) ";
			}

			$sql .=	" GROUP BY ipp.id_inventario_proveedor_producto
				ORDER BY p.orden_lista, p.nombre ASC
			)ax 
			WHERE 1 ".
		  (isset($cantmayora) && strlen($cantmayora)>0 ? " AND cantidad > '{$cantmayora}' " : "")." ".
		  (isset($cantmenora) && strlen($cantmenora)>0 ? " AND cantidad < '{$cantmenora}' " : "")." 
		  	ORDER BY {$orderGRC} {$sentidoOr}";

	//die( $sql ); 
	
	if( isset( $ini ) && isset( $fin ) ){
		//Conseguimos el número de datos real
		$resultado=mysql_query($sql) or die(mysql_error() . " Consulta:\n$sql\n\nDescripcion:\n".mysql_error());
		$numtotal=mysql_num_rows($resultado);
		//Añadimos el limit para el paginador
		$sql.=" LIMIT $ini, $fin";
	}	 
	//Buscamos los datos de la consulta final
	$res=mysql_query($sql) or die(mysql_error() . " Error en:\n$sql\n\nDescripcion:\n".mysql_error());
	
	$num=mysql_num_rows($res);		
	
	echo "exito";
	for($i=0;$i<$num;$i++){
		$row=mysql_fetch_row($res);
		echo "|";
		for($j=0;$j<sizeof($row);$j++){	
			if($j > 0)
				echo "~";
			if($j == 0)
				echo base64_encode($row[$j]);
			else	
				echo $row[$j];
		}	
	}

	//Enviamos en el ultimo dato los datos del listado, numero de datos y datos que se muestran
	if(isset($ini) && isset($fin))
		echo "|$numtotal~$num";
	
?>