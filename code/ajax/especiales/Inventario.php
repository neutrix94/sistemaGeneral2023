<?php
	//include("../../conect.php");
	include("../../../conectMin.php");
	
	//print_r($_GET);
	
	
	extract($_GET);
    
    if(!isset($sucur))
        $sucur=-1;
    
    /*$sql = "	SELECT 
    			p.id_productos, 
    			p.nombre,
    			a.nombre,  
    			IF(ISNULL(i.cantidad), 0, i.cantidad) AS cantidad,
    			'ver' 
    			FROM sys_sucursales s 
    			INNER JOIN ec_almacen a ON a.id_sucursal = s.id_sucursal AND a.id_almacen <> '-1' 
    			CROSS JOIN ec_productos p on p.id_productos <> -1 
    			LEFT OUTER JOIN ecv_inventarios i on i.id_sucursal = s.id_sucursal and i.id_almacen = a.id_almacen and i.id_producto = p.id_productos 
    			WHERE s.id_sucursal = '{$user_sucursal}' " .
    			($sucur <> -1 ? "AND a.id_almacen = '{$sucur}' " : " ") .
    			(isset($nombre) && strlen($nombre)>0 ? "AND p.nombre LIKE '%{$nombre}%' " : "") .
    		"GROUP BY s.id_sucursal, a.id_almacen, p.id_productos 
    		HAVING TRUE " .
    		(isset($cantmayora) && strlen($cantmayora)>0 ? "AND cantidad > '{$cantmayora}' " : "") .
    		(isset($cantmenora) && strlen($cantmenora)>0 ? "AND cantidad < '{$cantmenora}' " : "") .
    		"order by {$orderGRC} {$sentidoOr} ";*/

	if($tipo==2){
		$sql="SELECT
		  ax.id,
		  ax.orden_lista,
		  ax.clave,
		  ax.nombre,
		  ax.familia,
		  ROUND( ax.inventory, 4 ) AS inventory,
		  ROUND( ax.cantidad, 4 ) AS cantidad,
		  'ver'
		  FROM(
			SELECT
			p.id_productos AS id,
			p.nombre,
			p.orden_lista,
			p.clave,
			a.nombre AS familia,
			ROUND(SUM(
				IF(
					md.id_movimiento IS NULL,
					0,
					md.cantidad*tm.afecta
				)
			), 4) AS cantidad,
			ap.inventario AS inventory
			FROM ec_productos p
			JOIN ec_movimiento_detalle md ON p.id_productos = md.id_producto
			JOIN ec_movimiento_almacen ma ON md.id_movimiento = ma.id_movimiento_almacen AND ma.id_sucursal = '{$user_sucursal}'
			JOIN ec_almacen a ON ma.id_almacen = a.id_almacen
			JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento = tm.id_tipo_movimiento
			JOIN ec_almacen_producto ap
			ON ap.id_producto = p.id_productos
			AND ap.id_almacen = a.id_almacen 
			WHERE p.id_productos > 0 ";

		//ampliamos las coincidencias de búsqueda
			if($valor!='' && $valor!=null){	
				$sql.=" AND(";
				$arr=explode(" ", $valor);
				for($i=0;$i<sizeof($arr);$i++){
					if($arr[$i]!='' && $arr[$i]!=null){
						if($i>0){
							$sql.=" AND ";
						}
						$sql.="p.nombre LIKE '%".$arr[$i]."%'";
					}
				}//fin de for $i
				$sql.=") OR p.orden_lista LIKE '%{$valor}%'";
			}
			if( $valor =='' || $valor == null ){
				$sql .= " AND p.id_productos = 0"; 
			}
			$sql.=" GROUP BY p.id_productos, a.id_almacen ORDER BY p.orden_lista )ax";

	}else{
		if( $orderGRC == 'id'  ){
			$orderGRC = 'ax.orden_lista';//implementacion Oscar 2022 para ordenar por orden de lista
		}
		$sql="SELECT
		  ax.id,
		  ax.orden_lista,
		  ax.clave,
		  ax.nombre,
		  ax.familia,
		  ax.inventory,
		  ax.cantidad,
		  'ver'
		  FROM(
			SELECT
			p.id_productos AS id,
			p.nombre,
			p.orden_lista,
			p.clave,
			a.nombre AS familia,
			ROUND(SUM(
				IF(
					md.id_movimiento IS NULL,
					0,
					md.cantidad*tm.afecta
				)
			), 2) AS cantidad,
			ap.inventario AS inventory
			FROM ec_productos p
			JOIN ec_movimiento_detalle md ON p.id_productos = md.id_producto
			JOIN ec_movimiento_almacen ma ON md.id_movimiento = ma.id_movimiento_almacen AND ma.id_sucursal = '{$user_sucursal}'
			JOIN ec_almacen a ON ma.id_almacen = a.id_almacen
			JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento = tm.id_tipo_movimiento
			JOIN ec_almacen_producto ap
			ON ap.id_producto = p.id_productos
			AND ap.id_almacen = a.id_almacen 
			WHERE p.id_productos > 0 AND p.id_productos = 0 " . /*implementacion Oscar 2023*/
			(isset($nombre) && strlen($nombre)>0 ? "AND p.nombre LIKE '%{$nombre}%' " : "")." ".
			"GROUP BY p.id_productos, a.id_almacen ORDER BY p.orden_lista
		  )ax
		  WHERE 1 ".
		  (isset($cantmayora) && strlen($cantmayora)>0 ? " AND cantidad > '{$cantmayora}' " : "")." ".
		  (isset($cantmenora) && strlen($cantmenora)>0 ? " AND cantidad < '{$cantmenora}' " : "")." 
		   ORDER BY {$orderGRC} {$sentidoOr}";//ORDER BY ax.orden_lista ASC
	}
	//die( "ORDER BY {$orderGRC} {$sentidoOr}" );

	//(isset($cantmayora) && strlen($cantmayora)>0 ? " AND cantidad > '{$cantmayora}' " : "") ." ".
	//(isset($cantmenora) && strlen($cantmenora)>0 ? " AND cantidad < '{$cantmenora}' " : "") ."
			


	//die($sql);
		  
	//Ponemos el inicio y fin que nos marca el grid
	if(isset($ini) && isset($fin))
	{
		
		//die("??");
		//Conseguimos el número de datos real
		$resultado=mysql_query($sql) or die("Consulta:\n$sql\n\nDescripcion:\n".mysql_error());
		$numtotal=mysql_num_rows($resultado);
		
		//Añadimos el limit para el paginador
		$sql.=" LIMIT $ini, $fin";
	}	  
	
	//Buscamos los datos de la consulta final
	$res=mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
	
	$num=mysql_num_rows($res);		
	
	echo "exito";
	for($i=0;$i<$num;$i++)
	{
		$row=mysql_fetch_row($res);
		
		
		
		echo "|";
		for($j=0;$j<sizeof($row);$j++)
		{	
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