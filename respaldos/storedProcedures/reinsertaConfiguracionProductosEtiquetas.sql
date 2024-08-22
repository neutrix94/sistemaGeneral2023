DROP PROCEDURE IF EXISTS reinsertaConfiguracionProductosEtiquetas|
DELIMITER $$
create procedure reinsertaConfiguracionProductosEtiquetas() 
BEGIN 
    /*Para comprobar si hay registros pendientes
        SELECT 
            p.id_productos
        FROM ec_productos p
        LEFT JOIN ec_productos_etiquetado_maquila pem
        ON pem.id_producto = p.id_productos
        WHERE pem.id_producto IS NULL;
    */
    INSERT INTO ec_productos_etiquetado_maquila ( id_producto, es_producto_sin_etiqueta, imprimir_caja, Imprimir_paquete, imprimir_piezas_sueltas, imprimir_etiqueta_de_pieza, imprime_etiqueta_sello_roto, auxiliar)
    SELECT 
        p.id_productos,
        0, 
        1, 
        1, 
        1, 
        0, 
        1, 
        0
    FROM ec_productos p
    LEFT JOIN ec_productos_etiquetado_maquila pem
    ON pem.id_producto = p.id_productos
    WHERE pem.id_producto IS NULL
    AND p.id_productos > 0;
END $$