DROP TRIGGER IF EXISTS insertaDetalleMovimiento;
DELIMITER $$
CREATE TRIGGER insertaDetalleMovimiento
AFTER INSERT ON ec_detalle_movimiento
FOR EACH ROW
BEGIN
DECLARE id_sucursal INTEGER(11);
DECLARE tipo INTEGER(2);
DECLARE inventario_actual INTEGER(11);
DECLARE bitacora TEXT;

SELECT
mi.id_sucursal,
tm.afecta
INTO
id_sucursal,
tipo
FROM ec_movimiento_inventario mi
LEFT JOIN ec_tipos_movimiento tm ON mi.id_tipo_movimiento = tm.id_movimiento
WHERE mi.id_movimiento_inventario = new.id_movimiento_inventario;
/*Bitacora de calculos*/
SELECT sp.inventario INTO inventario_actual FROM ec_sucursal_producto sp WHERE sp.id_producto = new.id_producto AND sp.id_sucursal = id_sucursal;
SET bitacora = CONCAT('Inserta | Inventario actual ec_sucursal_producto : ' , inventario_actual ,
' | ', 'Movimiento : ' , (new.cantidad * tipo) ,
' | ' , 'Inventario Final : ' , ( inventario_actual + (new.cantidad * tipo) ),
' | id de cabecera : ', new.id_movimiento_inventario, ' | id detalle movimiento : ', new.id_detalle_movimiento  );

INSERT INTO bitacora_inventarios(id_producto, cantidad, descripcion)
VALUES(new.id_producto, new.cantidad, bitacora);

/**/

/*UPDATE ec_sucursal_producto sp
SET
sp.inventario = (sp.inventario + (new.cantidad * tipo))
WHERE sp.id_sucursal = id_sucursal
AND sp.id_producto = new.id_producto;*/

UPDATE ec_sucursal_producto sp
SET
sp.inventario = (select sum(afecta * cantidad) from ec_detalle_movimiento movDet
 inner join ec_movimiento_inventario alm on alm.id_movimiento_inventario=movDet.id_movimiento_inventario
 inner join ec_tipos_movimiento tipMon on alm.id_tipo_movimiento=tipMon.id_movimiento
where id_producto=new.id_producto AND alm.id_sucursal=id_sucursal
                       /*where id_producto=1828 AND alm.id_sucursal=1*/
group by id_producto)
WHERE sp.id_sucursal = id_sucursal
AND sp.id_producto = new.id_producto;

END $$