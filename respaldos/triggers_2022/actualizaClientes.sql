DROP TRIGGER IF EXISTS actualizaClientes|
DELIMITER $$
CREATE TRIGGER actualizaClientes
BEFORE UPDATE ON ec_clientes
FOR EACH ROW
BEGIN
  DECLARE store_id INT(11);
  SELECT id_sucursal INTO store_id FROM sys_sucursales WHERE acceso = 1;

  IF( new.sincronizar = 1 )
  THEN
    INSERT INTO sys_sincronizacion_registros ( id_sincronizacion_registro, sucursal_de_cambio, id_sucursal_destino,   
       datos_json, fecha, tipo, status_sincronizacion )
    SELECT 
      NULL,
      store_id,
      id_sucursal,
      CONCAT('{', 
        '"table_name" : "ec_clientes",',
        '"action_type" : "update",',
        '"primary_key" : "folio_unico",',
        '"primary_key_value" : "', new.folio_unico, '",',
        '"nombre" : "', new.nombre, '",',
        '"telefono" : "', new.telefono, '",',
        '"telefono_2" : "', new.telefono_2, '",',
        '"movil" : "', new.movil, '",',
        '"contacto" : "', new.contacto, '",',
        '"email" : "', new.email, '",',
        '"dias_credito" : "', new.dias_credito, '",',
        '"maximo_adeudo" : "', new.maximo_adeudo, '",',
        '"es_cliente" : "', new.es_cliente, '",',
        '"id_sucursal" : "', new.id_sucursal, '",',
        '"monto_desc" : "', new.monto_desc, '",',
        '"porc_desc" : "', new.porc_desc, '",',
        '"min_compra_desc" : "', new.min_compra_desc, '",',
        '"fecha_alta" : "', new.fecha_alta, '",',
        '"folio_unico" : "', new.folio_unico, '",',
        '"sincronizar" : "0"',
        '}'
       ),
      NOW(),
      1,
      1
    FROM sys_sucursales 
    WHERE id_sucursal = IF( store_id = -1, new.id_sucursal, -1 );
  END IF;
  SET new.sincronizar = 0;
END $$
