DROP TRIGGER IF EXISTS insertaPlantillasEtiquetas|
DELIMITER $$
CREATE TRIGGER insertaPlantillasEtiquetas
AFTER INSERT ON sys_plantillas_etiquetas
FOR EACH ROW
BEGIN
	INSERT INTO sys_sucursales_plantillas_etiquetas ( id_sucursal_plantilla_etiqueta, id_sucursal,
		id_plantilla, tipo_codigo_plantilla, habilitado )
    SELECT
    	NULL,
    	id_sucursal,
    	new.id_plantilla_etiquetas,
    	'EPL',
    	1
    FROM sys_sucursales
    WHERE id_sucursal > 0;
END $$