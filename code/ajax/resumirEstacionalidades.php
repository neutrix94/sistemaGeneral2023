<?php
	include('../../conectMin.php');
	mysql_query("BEGIN");//abrimos la transaccion
//eliminamos el detalle anterior
	$sql="DELETE FROM ec_historico_estacionalidad_resumen WHERE 1";
	$eje=mysql_query($sql);
	if(!$eje){
		$error=mysql_error();
		mysql_query("ROLLBACK");//cancelamos la transaccion
		die("Error al elimiar el historico anterior!!!".$error);
	}

	$sql="INSERT INTO ec_historico_estacionalidad_resumen
			SELECT 
				null,
    			e.id_sucursal,
   				c.id_categoria,
    			e.id_estacionalidad,
    			COUNT(ep.id_estacionalidad_producto),
    			SUM(ep.maximo),
    			now(),
    			'0000-00-00',
    			1
		FROM ec_estacionalidad e
		LEFT JOIN ec_estacionalidad_producto ep ON ep.id_estacionalidad=e.id_estacionalidad
		LEFT JOIN ec_productos p ON p.id_productos=ep.id_producto
		LEFT JOIN ec_categoria c ON c.id_categoria=p.id_categoria
		WHERE p.id_productos>0 
		AND ep.maximo>0
		AND e.id_sucursal>1
		AND e.es_alta=1
		AND c.id_categoria IS NOT NULL
		GROUP by e.id_sucursal,p.id_categoria,e.id_estacionalidad
		ORDER by e.id_sucursal";
	$eje=mysql_query($sql);
	if(!$eje){
		$error=mysql_error();
		mysql_query("ROLLBACK");//cancelamos la transaccion
		die("Error al insertar el historico de estacionalidades!!!".$error);
	}

	mysql_query("COMMIT");//autorizamos la transaccion
	
	die('ok');
?>