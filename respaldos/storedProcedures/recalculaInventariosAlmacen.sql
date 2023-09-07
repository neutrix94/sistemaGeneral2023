DROP PROCEDURE IF EXISTS recalculaInventariosAlmacen|
DELIMITER $$
CREATE PROCEDURE recalculaInventariosAlmacen()
BEGIN

START TRANSACTION;

	UPDATE ec_almacen_producto ap
	LEFT JOIN 
	(
	SELECT
		NULL,
		ax.id_almacen,
	    ax.id_productos,
	    SUM( IF(ma.id_movimiento_almacen IS NULL, 0, (md.cantidad*tm.afecta) ) ) as inventario
	FROM
	(
		SELECT
	    	alm.id_almacen,
			p.id_productos,
	    	p.nombre
		FROM ec_productos p
		JOIN ec_almacen alm
		WHERE p.id_productos>0
	    AND alm.id_almacen>0
		GROUP BY alm.id_almacen, p.id_productos  
		ORDER BY alm.id_almacen, p.id_productos
	)ax
	LEFT JOIN ec_movimiento_detalle md 
	ON ax.id_productos = md.id_producto
	LEFT JOIN ec_movimiento_almacen ma 
	ON md.id_movimiento = ma.id_movimiento_almacen
	AND ax.id_almacen = ma.id_almacen 
	LEFT JOIN ec_tipos_movimiento tm 
	ON ma.id_tipo_movimiento = tm.id_tipo_movimiento
	GROUP BY ax.id_almacen, ax.id_productos
	)ax_2
	ON ap.id_producto = ax_2.id_productos
	AND ap.id_almacen = ax_2.id_almacen

	SET ap.inventario = ax_2.inventario;

COMMIT;

END $$