<?php

	include("../../conectMin.php");
	extract($_GET);

	$sql="	SELECT
			tp.id_transferencia_producto,
			p.orden_lista,
			p.nombre,
			pp.nombre,
			tp.cantidad_salida,
			tp.cantidad_entrada,
			(tp.cantidad_salida_pres-tp.cantidad_entrada_pres) as diferencia,
			IF(tp.cantidad_salida < tp.cantidad_entrada,
				tp.cantidad_entrada-tp.cantidad_salida,
				'0'
			),
			IF(tp.cantidad_salida > tp.cantidad_entrada,
				tp.cantidad_salida-tp.cantidad_entrada,
				'0'
			),
			'0',
			'',
			p.id_productos
			FROM ec_transferencia_productos tp
			JOIN ec_productos p ON tp.id_producto_or = p.id_productos
			JOIN ec_productos_presentaciones pp ON tp.id_presentacion = pp.id_producto_presentacion
			WHERE tp.id_transferencia=$id
			AND tp.cantidad_salida <> tp.cantidad_entrada
			AND tp.resolucion = 0 
			ORDER BY p.ubicacion_almacen,p.orden_lista ASC";
			
			
	//Buscamos los datos de la consulta final
        $res = mysql_query($sql) or die("Error en:\$sql\n\nDescripcion:\n" . mysql_error());
    
        $num = mysql_num_rows($res);
    
        echo "exito";
        for ($i = 0; $i < $num; $i++)
        {
            $row = mysql_fetch_row($res);
            echo "|";
            for ($j = 0; $j < sizeof($row); $j++)
            {
                if ($j > 0)
                    echo "~";
                echo utf8_encode($row[$j]);
            }
        }		


?>