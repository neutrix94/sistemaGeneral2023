DROP TRIGGER IF EXISTS insertaGastos|
DELIMITER $$
CREATE TRIGGER insertaGastos
AFTER INSERT ON ec_gastos
FOR EACH ROW
BEGIN
	DECLARE id_suc INT(11);
    DECLARE id_usuario_eq INT(11);
    DECLARE id_cajero_eq INT(11);
   
    SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
	SELECT id_equivalente INTO id_usuario_eq FROM sys_users WHERE id_usuario=new.id_usuario;
    SELECT id_equivalente INTO id_cajero_eq FROM sys_users WHERE id_usuario=new.id_cajero;
   
    IF(new.sincronizar!=0)
    THEN
    	INSERT INTO ec_sincronizacion_registros VALUES(null,id_suc,IF(id_suc=-1,new.id_sucursal,-1),'ec_gastos',new.id_gastos,1,3,
        CONCAT("INSERT INTO ec_gastos SET ",
                "id_gastos=null,",
                "id_usuario='",id_usuario_eq,"',",    
                "id_sucursal='",new.id_sucursal,"',",   
                "fecha='",new.fecha,"',", 
                "hora='",new.hora,"',",  
                "id_concepto='",new.id_concepto,"',",
                "monto='",new.monto,"',", 
                "id_cajero='",id_cajero_eq,"',"
                "observaciones='",new.observaciones,"',",     
                "id_equivalente='",new.id_gastos,"',",        
                "sincronizar=0",
                "___UPDATE ec_gastos SET sincronizar=0 WHERE id_equivalente='",new.id_gastos,"' AND id_sucursal='",new.id_sucursal,"'"
        ),
        0,1,CONCAT('Se agregó un gasto por concepto de ',(SELECT nombre FROM ec_conceptos_gastos WHERE id_concepto=new.id_concepto)),now(),0,0,
        'id_gastos');
    END IF;
END $$