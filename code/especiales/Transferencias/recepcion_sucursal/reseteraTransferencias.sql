/Elimina el detalle de escaneos resolucion/
DELETE FROM ec_bloques_transferencias_resolucion_escaneos
WHERE id_bloque_transferencia_resolucion IN( 
	SELECT 
    	id_bloque_transferencia_resolucion
    FROM ec_bloques_transferencias_resolucion
    WHERE id_bloque_transferencia_recepcion IN(
    	SELECT
        	btrd.id_bloque_transferencia_recepcion
       	FROM ec_bloques_transferencias_recepcion_detalle btrd
        LEFT JOIN ec_bloques_transferencias_validacion btv
        ON btrd.id_bloque_transferencia_validacion = btv.id_bloque_transferencia_validacion
        LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
        ON btv.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
        WHERE btvd.id_transferencia IN( 7087,7069,7077, 7070 )
    )
);
/Elimina el detalle de resolucion del bloque/
DELETE FROM ec_bloques_transferencias_resolucion_detalle
WHERE id_bloque_transferencia_resolucion IN( 
	SELECT 
		ax.id_bloque_transferencia_resolucion
	FROM(
		SELECT 
	    	id_bloque_transferencia_resolucion
	    FROM ec_bloques_transferencias_resolucion
	    WHERE id_bloque_transferencia_recepcion IN(
	    	SELECT
	        	btrd.id_bloque_transferencia_recepcion
	       	FROM ec_bloques_transferencias_recepcion_detalle btrd
	        LEFT JOIN ec_bloques_transferencias_validacion btv
	        ON btrd.id_bloque_transferencia_validacion = btv.id_bloque_transferencia_validacion
	        LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
	        ON btv.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
	        WHERE btvd.id_transferencia IN( 7087,7069,7077, 7070 )
	    )
	)ax
);
/Elimina resoluciones del bloque/
DELETE FROM ec_bloques_transferencias_resolucion
WHERE id_bloque_transferencia_resolucion IN( 
	SELECT
		ax.id_bloque_transferencia_resolucion
	FROM(
		SELECT 
	    	id_bloque_transferencia_resolucion
	    FROM ec_bloques_transferencias_resolucion
	    WHERE id_bloque_transferencia_recepcion IN(
	    	SELECT
	        	btrd.id_bloque_transferencia_recepcion
	       	FROM ec_bloques_transferencias_recepcion_detalle btrd
	        LEFT JOIN ec_bloques_transferencias_validacion btv
	        ON btrd.id_bloque_transferencia_validacion = btv.id_bloque_transferencia_validacion
	        LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
	        ON btv.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
	        WHERE btvd.id_transferencia IN( 7087,7069,7077, 7070 )
	    )
	)ax
);

/Elimina detalle de recepcion de bloque/
DELETE FROM  
	ec_bloques_transferencias_recepcion_detalle
WHERE id_bloque_transferencia_recepcion IN(
	SELECT
		ax.id_bloque_transferencia_recepcion
	FROM(
		SELECT
	    	btrd.id_bloque_transferencia_recepcion
	   	FROM ec_bloques_transferencias_recepcion_detalle btrd
	    LEFT JOIN ec_bloques_transferencias_validacion btv
	    ON btrd.id_bloque_transferencia_validacion = btv.id_bloque_transferencia_validacion
	    LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
	    ON btv.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
	    WHERE btvd.id_transferencia IN( 7087,7069,7077, 7070 )
	)ax
);

/Elimina bloque de recepcion/
DELETE FROM  
	ec_bloques_transferencias_recepcion
WHERE id_bloque_transferencia_recepcion IN(
	SELECT
    	btrd.id_bloque_transferencia_recepcion
   	FROM ec_bloques_transferencias_recepcion_detalle btrd
    LEFT JOIN ec_bloques_transferencias_validacion btv
    ON btrd.id_bloque_transferencia_validacion = btv.id_bloque_transferencia_validacion
    LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
    ON btv.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
    WHERE btvd.id_transferencia IN( 7087,7069,7077, 7070 )
);

/Elimina escaneos del bloque/
DELETE FROM ec_transferencias_recepcion_usuarios 
WHERE id_transferencia_producto IN ( 
	SELECT 
		tp.id_transferencia_producto
	FROM ec_transferencia_productos tp
	WHERE tp.id_transferencia IN( 7087,7069,7077, 7070 )
);
/resetea cantidades en el detalle de transferencia/
UPDATE ec_transferencia_productos 
	SET cantidad_cajas_recibidas = 0,
	cantidad_paquetes_recibidos = 0,
	cantidad_piezas_recibidas = 0,
	total_piezas_recibidas = 0
WHERE id_transferencia IN( 7087,7069,7077, 7070 );