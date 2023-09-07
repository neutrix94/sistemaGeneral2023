DROP TRIGGER IF EXISTS insertaProductosSinInventario|
DELIMITER $$
CREATE TRIGGER insertaProductosSinInventario
AFTER INSERT ON ec_productos_sin_inventario
FOR EACH ROW
BEGIN
    DECLARE id_suc INT(11);
    DECLARE id_user_eq INT(11);
	IF(new.sincronizar=1)
	THEN
    	SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
    	SELECT id_equivalente INTO id_user_eq FROM sys_users WHERE id_usuario=new.id_usuario;
    	
    	    INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_productos_sin_inventario',new.id_prod_sin_inv,1,6,
    		    CONCAT("INSERT INTO ec_productos_sin_inventario SET ",
    		    	"id_prod_sin_inv=null,",
    		    	"id_producto='",new.id_producto,"',",
    		    	"id_sucursal='",new.id_sucursal,"',",
    		    	"id_usuario='",id_user_eq,"',",
    		    	"alta='",new.alta,"',",
    		    	"observaciones='",new.observaciones,"',",
    		    	"sincronizar=0___UPDATE ec_productos_sin_inventario SET sincronizar=0 WHERE id_prod_sin_inv=0"
       			),
        		0,0,CONCAT('Se agregó registro ',new.id_prod_sin_inv),now(),0,0,'id_prod_sin_inv'
        	FROM sys_sucursales WHERE IF(id_suc=-1,id_sucursal=new.id_sucursal,id_sucursal=-1);
    END IF;
END $$