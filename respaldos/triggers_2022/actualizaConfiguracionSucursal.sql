DROP TRIGGER IF EXISTS actualizaConfiguracionSucursal|
DELIMITER $$
CREATE TRIGGER actualizaConfiguracionSucursal
BEFORE UPDATE ON ec_configuracion_sucursal
FOR EACH ROW
BEGIN
  DECLARE id_suc INT(11);

  SELECT id_sucursal INTO id_suc FROM sys_sucursales WHERE acceso=1;
  IF(new.sincronizar=1)
     THEN
      INSERT INTO ec_sincronizacion_registros SELECT null,id_suc,id_sucursal,'ec_configuracion_sucursal',new.id_configuracion_sucursal,2,1,
         CONCAT("UPDATE ec_configuracion_sucursal SET ",
                 "id_sucursal='",new.id_sucursal,"',",
                 "no_tickets_resolucion='",new.no_tickets_resolucion,"',",          
                 "imprime_ubicacion_pdf_transf='",new.imprime_ubicacion_pdf_transf,"',",    
                 "no_tickets_abono='",new.no_tickets_abono,"',",
                 "sucursal_impresion_local=",new.sucursal_impresion_local,",",      
                 "solicitar_asistencia_iniciar_sesion='",new.solicitar_asistencia_iniciar_sesion,"',",
                 "multicajero='",new.multicajero,"',",
                 "pide_password_asistencia_login='",new.pide_password_asistencia_login,"',", 
                 
                 "permite_abrir_caja_linea='",new.permite_abrir_caja_linea,"',",
                 "permite_ventas_linea='",new.permite_ventas_linea,"',",
                 "mostrar_descuento_ticket='",new.mostrar_descuento_ticket,"',",
                 "solicitar_password_inventario_insuficiente='",new.solicitar_password_inventario_insuficiente,"',",
                 "pedir_password_devolucion='",new.pedir_password_devolucion,"',",
                 "ofrecer_productos='",new.ofrecer_productos,"',",
                 
                 "sincronizar=0 WHERE id_configuracion_sucursal='",new.id_configuracion_sucursal,"'"
         ),
         1,0,CONCAT('Se agregó la configuracion para sucursal ',new.id_sucursal),now(),0,0,'id_configuracion_sucursal'
         FROM sys_sucursales WHERE id_sucursal>0;
     END IF;
     SET new.sincronizar=1;
  END $$