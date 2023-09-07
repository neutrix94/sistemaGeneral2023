DROP TRIGGER IF EXISTS actualizaEstacionalidadProducto|
DELIMITER $$
CREATE TRIGGER actualizaEstacionalidadProducto
BEFORE UPDATE ON ec_estacionalidad_producto
FOR EACH ROW
BEGIN
DECLARE id_suc INT(11);
DECLARE estActiva int(11);
DECLARE sucActiva int(11);
DECLARE tipo int(11);
DECLARE dependiente int(11);

DECLARE categoria_producto INT(11);
DECLARE excluye_factores TINYINT(1);
DECLARE factor_urgente float(8,2);
DECLARE factor_medio float(8,2);
DECLARE factor_final float(8,2);
DECLARE factor_minimo_surtir float(8,2);

 SELECT s.id_sucursal,
        s.id_estacionalidad,
        est.es_alta
  INTO sucActiva,estActiva,tipo
  FROM sys_sucursales s
  LEFT JOIN ec_estacionalidad est 
  ON s.id_sucursal=est.id_sucursal
  WHERE est.id_estacionalidad=new.id_estacionalidad;

  SELECT 
    id_categoria, 
    excluir_factores_por_categoria 
    INTO 
    categoria_producto, 
    excluye_factores 
  FROM ec_productos WHERE id_productos = new.id_producto;

  SELECT factor INTO factor_urgente FROM ec_factores_estacionalidad_categorias WHERE id_categoria = categoria_producto AND id_tipo_factor = 1;
  SELECT factor INTO factor_medio FROM ec_factores_estacionalidad_categorias WHERE id_categoria = categoria_producto AND id_tipo_factor = 2; 
  SELECT factor INTO factor_final FROM ec_factores_estacionalidad_categorias WHERE id_categoria = categoria_producto AND id_tipo_factor = 3; 
  SELECT factor INTO factor_minimo_surtir FROM ec_factores_estacionalidad_categorias WHERE id_categoria = categoria_producto AND id_tipo_factor = 4; 

  IF( excluye_factores = 0 )
  THEN
    IF( old.maximo!=new.maximo )
    THEN
      SET new.minimo=IF(FLOOR(factor_urgente*new.maximo)<=0,0,FLOOR(factor_urgente*new.maximo));
      SET new.medio=ROUND(factor_medio*new.maximo);
    END IF;


    IF(new.id_estacionalidad=estActiva AND new.minimo != old.minimo)
      THEN
         UPDATE sys_sucursales_producto sp SET sp.minimo_surtir=IF( FLOOR( factor_minimo_surtir*new.maximo ) <=0 , 0,FLOOR( factor_minimo_surtir * new.maximo ) ),sp.sincronizar=0
            WHERE sp.id_producto=new.id_producto
            AND sp.id_sucursal=sucActiva;
     END IF;
  END IF;

   SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;

IF(new.id_estacionalidad!=old.id_estacionalidad OR new.id_producto!=old.id_producto OR new.minimo!=old.minimo OR new.medio!=old.medio OR new.maximo!=old.maximo)
THEN
IF(new.sincronizar!=0)
   THEN
    INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_estacionalidad_producto',new.id_estacionalidad_producto,2,6,
     CONCAT("UPDATE ec_estacionalidad_producto SET ",
             "id_estacionalidad='",new.id_estacionalidad,"',",
             "id_producto='",new.id_producto,"',",
             "minimo='",new.minimo,"',",  
             "medio='",new.medio,"',",
             "maximo='",new.maximo,"',",
             "alta='",new.alta,"',",
             "ultima_modificacion='",new.ultima_modificacion,"',",
             "sincronizar=0 WHERE id_estacionalidad='",new.id_estacionalidad,"' AND id_producto='",new.id_producto,"'"
     ),
     0,0,CONCAT('Se actualizó la estacionalidad del producto ',(SELECT nombre FROM ec_productos WHERE id_productos=new.id_producto)),now(),0,0,'id_estacionalidad_producto'
       FROM sys_sucursales WHERE id_sucursal IN(IF(id_suc=-1,(SELECT id_sucursal FROM ec_estacionalidad WHERE id_estacionalidad=new.id_estacionalidad),-1));
 END IF;
END IF;
   SET new.sincronizar=1;
END $$