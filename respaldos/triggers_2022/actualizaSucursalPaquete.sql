DROP TRIGGER IF EXISTS actualizaSucursalPaquete|
DELIMITER $$
CREATE TRIGGER actualizaSucursalPaquete
AFTER UPDATE ON sys_sucursales_paquete
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
    DECLARE id_pedido_equivalente INT(11);
    DECLARE id_suc_destino INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	IF(new.estado_suc!=old.estado_suc)
    THEN
	
        INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'sys_sucursales_paquete',new.id_sucursal_paquete,2,1,
        CONCAT("UPDATE sys_sucursales_paquete SET estado_suc='",new.estado_suc,"' WHERE id_sucursal='",new.id_sucursal,"' AND id_paquete='",new.id_paquete,"'"),
        0,0,CONCAT('Se modificó la configuración de sucursal paquete',new.id_sucursal_paquete),now(),0,0,'id_sucursal_paquete'
        FROM sys_sucursales WHERE IF(id_suc=-1,id_sucursal=new.id_sucursal,id_sucursal=-1) ORDER BY id_sucursal;
	END IF;
END $$