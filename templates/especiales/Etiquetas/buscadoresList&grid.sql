
/***************************************************Modificaciones para meter buscador en grids**********************************************************************/
/*Se agrega campo buscador (bit 1;0 default) en la tabla sys_grid*/
ALTER TABLE `sys_grid` ADD `buscador` BIT(1) NOT NULL DEFAULT b'0' AFTER `funcion_despues_eliminar`;
/*Se agregan campos para búsqueda de precisión y enfoque al insertar*/
ALTER TABLE `sys_grid` ADD `campo_coinc` TEXT NULL DEFAULT NULL AFTER `buscador`, ADD `campo_enfoque` TEXT NULL DEFAULT NULL AFTER `campo_coinc`;
/*Se agrega campo para generar buscador desordenado*/
ALTER TABLE `sys_grid` ADD `consulta_coinc` TEXT NULL DEFAULT NULL AFTER `campo_enfoque`;

/*grid 5 (3.4 ordenes de compra también grid 6,7 pero sin buscador)*/
UPDATE `sys_grid` SET `buscador` = b'1' WHERE `sys_grid`.`id_grid` = 5;
UPDATE `sys_grid` SET `consulta_coinc` = 'SELECT id_productos, nombre FROM ec_productos WHERE \':::\'' WHERE `sys_grid`.`id_grid` = 5; UPDATE `sys_grid` SET `campo_coinc` = 'nombre' WHERE `sys_grid`.`id_grid` = 5;
UPDATE `sys_grid_detalle` SET `on_change` = 'cambiaDesc(\'#\', \'ocproductos\',2 ,3,\'~\',\'___\',\'???\');actPreImp(\'#\',-1);' WHERE `sys_grid_detalle`.`id_grid_detalle` = 27;
UPDATE `sys_grid` SET `campo_enfoque` = '4|2' WHERE `sys_grid`.`id_grid` = 5;

/*grid 8(1.7 Productos maquila)*/
UPDATE `sys_grid` SET `buscador` = b'1' WHERE `sys_grid`.`id_grid` = 8;
UPDATE `sys_grid` SET `campo_enfoque` = '4|2' WHERE `sys_grid`.`id_grid` = 8;
UPDATE `sys_grid` SET `consulta_coinc` = 'SELECT id_productos, \nnombre FROM ec_productos \nWHERE :::' WHERE `sys_grid`.`id_grid` = 8;
UPDATE `sys_grid` SET `campo_coinc` = 'nombre' WHERE `sys_grid`.`id_grid` = 8;
UPDATE `sys_grid_detalle` SET `tipo` = 'texto' WHERE `sys_grid_detalle`.`id_grid_detalle` = 54;
UPDATE `sys_grid_detalle` SET `modificable` = 'N' WHERE `sys_grid_detalle`.`id_grid_detalle` = 54; 
UPDATE `sys_grid_detalle` SET `on_change` = 'cambiaDesc(\'#\', \'productosDetalle\', 2, 3,\'~\',\'___\',\'???\');' WHERE `sys_grid_detalle`.`id_grid_detalle` = 54;

/*grid 9(1.6 Movimientos de almacen)*/
UPDATE `sys_grid` SET `buscador` = b'1' WHERE `sys_grid`.`id_grid` = 9;
UPDATE `sys_grid` SET `campo_enfoque` = '6|4' WHERE `sys_grid`.`id_grid` = 9;
UPDATE `sys_grid` SET `consulta_coinc` = 'SELECT id_productos, nombre FROM ec_productos WHERE :::' WHERE `sys_grid`.`id_grid` = 9;
UPDATE `sys_grid` SET `campo_coinc` = 'nombre' WHERE `sys_grid`.`id_grid` = 9;
UPDATE `sys_grid_detalle` SET `tipo` = 'texto' WHERE `sys_grid_detalle`.`id_grid_detalle` = 59;
UPDATE `sys_grid_detalle` SET `modificable` = 'N' WHERE `sys_grid_detalle`.`id_grid_detalle` = 59;
UPDATE `sys_grid_detalle` SET `on_change` = 'cambiaDesc(\'#\', \'productosMovimiento\', 4, 5,\'~\',\'___\',\'???\');validaProd(\'#\');' WHERE `sys_grid_detalle`.`id_grid_detalle` = 59;
/*grid 10 (1.5 Maquila (solo visualización))
//grid 19 (2.4 Notas de Venta)*/

/*grid 24 (6.9 Precios de Venta) */
UPDATE `sys_grid` SET `buscador` = b'1' WHERE `sys_grid`.`id_grid` = 24;
UPDATE `sys_grid` SET `consulta_coinc` = 'SELECT id_productos, nombre FROM ec_productos WHERE :::' WHERE `sys_grid`.`id_grid` = 24;
SELECT `consulta_coinc` FROM `sys_grid` WHERE `sys_grid`.`id_grid` = 24
UPDATE `sys_grid` SET `campo_coinc` = 'nombre' WHERE `sys_grid`.`id_grid` = 24;
UPDATE `sys_grid` SET `campo_enfoque` = '5|2' WHERE `sys_grid`.`id_grid` = 24;
UPDATE `sys_grid_detalle` SET `datosDB` = 'SELECT id_productos, nombre FROM ec_productos WHERE nombre LIKE \'%$LLAVE%\' OR id_productos=\'$LLAVE\'' WHERE `sys_grid_detalle`.`id_grid_detalle` = 305; UPDATE `sys_grid_detalle` SET `on_change` = 'cambiaDesc(\'#\', \'productosMovimiento\', 4, 5,\'~\',\'___\',\'???\');validaProd(\'#\');' WHERE `sys_grid_detalle`.`id_grid_detalle` = 305;
UPDATE `sys_grid_detalle` SET `modificable` = 'N' WHERE `sys_grid_detalle`.`id_grid_detalle` = 305;	
/*grid 38 (5.4 Estacionalidad)*/
UPDATE `sys_grid` SET `buscador` = b'1' WHERE `sys_grid`.`id_grid` = 38;
UPDATE `sys_grid` SET `consulta_coinc` = 'SELECT id_productos, nombre FROM ec_productos WHERE :::' WHERE `sys_grid`.`id_grid` = 38;
UPDATE `sys_grid` SET `campo_coinc` = 'nombre' WHERE `sys_grid`.`id_grid` = 38;
UPDATE `sys_grid` SET `campo_enfoque` = '6|2' WHERE `sys_grid`.`id_grid` = 38;


/***************************************************Modificaciones para meter buscador en grids**********************************************************************/
/*Se agrega campo buscador (bit 1;0 default) en la tabla sys_grid*/
ALTER TABLE `sys_listados` ADD `buscador` BIT(1) NOT NULL DEFAULT b'0' AFTER `nuevo`;
ALTER TABLE `sys_listados` ADD `consulta_buscador` TEXT NULL DEFAULT NULL AFTER `buscador`, ADD `condiciones_buscador` TEXT NULL DEFAULT NULL AFTER `consulta_buscador`;
/*Configuraciones de grid*/
UPDATE `sys_listados` SET `buscador` = b'1' WHERE `sys_listados`.`id_listado` = 1;
UPDATE `sys_listados` SET `consulta_buscador` = 'CONCAT(nombre, \' \', apellido_paterno)' WHERE `sys_listados`.`id_listado` = 1;
UPDATE `sys_listados` SET `buscador` = b'1' WHERE `sys_listados`.`id_listado` = 2;
UPDATE `sys_listados` SET `consulta_buscador` = 'nombre' WHERE `sys_listados`.`id_listado` = 2; 
UPDATE `sys_listados` SET `buscador` = b'1' WHERE `sys_listados`.`id_listado` = 5;
UPDATE `sys_listados` SET `consulta_buscador` = 'sys_estados.nombre' WHERE `sys_listados`.`id_listado` = 5;
UPDATE `sys_listados` SET `buscador` = b'1' WHERE `sys_listados`.`id_listado` = 9;
UPDATE `sys_listados` SET `consulta_buscador` = 'nombre_comercial' WHERE `sys_listados`.`id_listado` = 9;
UPDATE `sys_listados` SET `buscador` = b'1' WHERE `sys_listados`.`id_listado` = 10;
UPDATE `sys_listados` SET `consulta_buscador` = 'nombre' WHERE `sys_listados`.`id_listado` = 10;
UPDATE `sys_listados` SET `buscador` = b'1' WHERE `sys_listados`.`id_listado` = 11;
UPDATE `sys_listados` SET `consulta_buscador` = 's.nombre' WHERE `sys_listados`.`id_listado` = 11;
UPDATE `sys_listados` SET `buscador` = b'1' WHERE `sys_listados`.`id_listado` = 12;
UPDATE `sys_listados` SET `consulta_buscador` = 'ec_productos.nombre' WHERE `sys_listados`.`id_listado` = 12;
UPDATE `sys_listados` SET `buscador` = b'1' WHERE `sys_listados`.`id_listado` = 13;
UPDATE `sys_listados` SET `consulta_buscador` = 'CONCAT(sys_users.nombre, \' \', sys_users.apellido_paterno)' WHERE `sys_listados`.`id_listado` = 13;
UPDATE `sys_listados` SET `buscador` = b'1' WHERE `sys_listados`.`id_listado` = 14;
UPDATE `sys_listados` SET `consulta_buscador` = '(SELECT GROUP_CONCAT(p.nombre SEPARATOR \', \')' WHERE `sys_listados`.`id_listado` = 14;
UPDATE `sys_listados` SET `buscador` = b'1' WHERE `sys_listados`.`id_listado` = 18;
UPDATE `sys_listados` SET `consulta_buscador` = 'nombre' WHERE `sys_listados`.`id_listado` = 18;
UPDATE `sys_listados` SET `buscador` = b'1' WHERE `sys_listados`.`id_listado` = 24;
UPDATE `sys_listados` SET `consulta_buscador` = '(SELECT GROUP_CONCAT(p.nombre SEPARATOR \', \') FROM ec_oc_detalle ec JOIN ec_productos p ON ec.id_producto=p.id_productos WHERE ec.id_orden_compra=ec_ordenes_compra.id_orden_compra)' WHERE `sys_listados`.`id_listado` = 24;
UPDATE `sys_listados` SET `buscador` = b'1' WHERE `sys_listados`.`id_listado` = 25;
UPDATE `sys_listados` SET `consulta_buscador` = 'nombre' WHERE `sys_listados`.`id_listado` = 25;
UPDATE `sys_listados` SET `buscador` = b'1' WHERE `sys_listados`.`id_listado` = 27;
UPDATE `sys_listados` SET `consulta_buscador` = 'c.nombre' WHERE `sys_listados`.`id_listado` = 27;
UPDATE `sys_listados` SET `buscador` = b'1' WHERE `sys_listados`.`id_listado` = 32;
UPDATE `sys_listados` SET `consulta_buscador` = 'lp.nombre' WHERE `sys_listados`.`id_listado` = 32;
UPDATE `sys_listados` SET `buscador` = b'1' WHERE `sys_listados`.`id_listado` = 45;
UPDATE `sys_listados` SET `consulta_buscador` = 'p.nombre' WHERE `sys_listados`.`id_listado` = 45; 
UPDATE `sys_listados` SET `buscador` = b'1' WHERE `sys_listados`.`id_listado` = 50;
UPDATE `sys_listados` SET `buscador` = b'1' WHERE `sys_listados`.`id_listado` = 52;
UPDATE `sys_listados` SET `buscador` = b'1' WHERE `sys_listados`.`id_listado` = 53;
UPDATE `sys_listados` SET `buscador` = b'1' WHERE `sys_listados`.`id_listado` = 55;
UPDATE `sys_listados` SET `consulta_buscador` = 't.nombre' WHERE `sys_listados`.`id_listado` = 55;
UPDATE `sys_listados` SET `buscador` = b'1' WHERE `sys_listados`.`id_listado` = 56;
UPDATE `sys_listados` SET `buscador` = b'1' WHERE `sys_listados`.`id_listado` = 57;
UPDATE `sys_listados` SET `consulta_buscador` = 't.nombre' WHERE `sys_listados`.`id_listado` = 57;
UPDATE `sys_listados` SET `buscador` = b'1' WHERE `sys_listados`.`id_listado` = 58;
UPDATE `sys_listados` SET `consulta_buscador` = 't.nombre' WHERE `sys_listados`.`id_listado` = 58;
UPDATE `sys_listados` SET `buscador` = b'1' WHERE `sys_listados`.`id_listado` = 63;
UPDATE `sys_listados` SET `consulta_buscador` = 'e.nombre' WHERE `sys_listados`.`id_listado` = 63;
UPDATE `sys_listados` SET `buscador` = b'1' WHERE `sys_listados`.`id_listado` = 64;
UPDATE `sys_listados` SET `consulta_buscador` = 'CONCAT(nombre,\' \',apellido_paterno,\' \',apellido_materno)' WHERE `sys_listados`.`id_listado` = 64;
UPDATE `sys_listados` SET `buscador` = b'1' WHERE `sys_listados`.`id_listado` = 65;
UPDATE `sys_listados` SET `consulta_buscador` = 'CONCAT(e.nombre, \' \', e.apellido_paterno, \' \', e.apellido_materno)' WHERE `sys_listados`.`id_listado` = 65;
UPDATE `sys_listados` SET `buscador` = b'1' WHERE `sys_listados`.`id_listado` = 69;
UPDATE `sys_listados` SET `buscador` = b'1' WHERE `sys_listados`.`id_listado` = 70;
UPDATE `sys_listados` SET `buscador` = b'1' WHERE `sys_listados`.`id_listado` = 72;
