DROP TRIGGER IF EXISTS after_update_lista_precios_magento|
DELIMITER $$
CREATE TRIGGER after_update_lista_precios_magento
AFTER UPDATE ON ec_precios
FOR EACH ROW
BEGIN
    IF new.grupo_cliente_magento is not null THEN
        INSERT INTO ec_sync_magento(tipo,id_registro,estatus)
        VALUES('TierPrice', new.id_precio, 1);
    END IF;
END $$