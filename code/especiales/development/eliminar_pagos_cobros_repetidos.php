<?php
    include( '../../../conexionMysqli.php' );
    $archivo = "log_eliminacion_cobros_pagos.txt";

    $archivoAbierto = fopen($archivo, "a");
    if ($archivoAbierto) {
        fwrite($archivoAbierto, "Comienza Proceso de eliminacion de pagos repetidos\n");
        fclose($archivoAbierto);
    } else {
        die( "No se pudo abrir el archivo." );
    }
    
//consulta los pedidos pagos repetidos por folio unico
        $sql="SELECT 
                folio_unico
            FROM ec_pedido_pagos
            WHERE folio_unico IS NOT NULL
            AND folio_unico != 'AGRUPACION'
            GROUP BY folio_unico
            HAVING COUNT(*) > 1";
        //$eje=mysql_query($sql)or die("Error al consultar las afiliaciones para este cajero!!!<br>".mysql_error());
        $stm = $link->query( $sql ) or die( "Error al consultar los pedidos pagos repetidos : {$sql} : {$link->error}" );
        echo "Consulta los pedidos pagos repetidos por folio unico : <br>{$sql}<br>";
        $archivoAbierto = fopen($archivo, "a");
        if ($archivoAbierto) {
            fwrite($archivoAbierto, "Consulta pagos repetidos :\n{$sql}\n");
            fclose($archivoAbierto);
        } else {
            die( "No se pudo abrir el archivo." );
        }
        
        $link->autocommit(false);

        while( $row = $stm->fetch_assoc() ){
            $sql = "SELECT id_pedido_pago FROM ec_pedido_pagos WHERE folio_unico = '{$row['folio_unico']}'";
            $stm2 = $link->query( $sql ) or die( "Error al consultar los registros por eliminar : {$sql} : {$link->error}" );
            echo "Consulta pagos repetidos independientemente. : <br>{$sql}<br>";
            $archivoAbierto = fopen($archivo, "a");
            if ($archivoAbierto) {
                fwrite($archivoAbierto, "Consulta pagos repetidos independientemente.\n");
                fclose($archivoAbierto);
            } else {
                die( "No se pudo abrir el archivo." );
            }
            $row_counter = 0;
            while( $row2 = $stm2->fetch_assoc() ){
                if( $row_counter > 0 ){
                    $sql = "DELETE FROM ec_pedido_pagos WHERE id_pedido_pago = {$row2['id_pedido_pago']}";
                    $stm3 = $link->query( $sql ) or die( "Error al eliminar registro de cobro : {$sql} : {$link->error}" );
                    echo "Elimina pago repetido independientemente : <br>{$sql}<br>";
                    $archivoAbierto = fopen($archivo, "a");
                    if ($archivoAbierto) {
                        fwrite($archivoAbierto, "Elimina Pago ({$row2['id_pedido_pago']}).\n");
                        fclose($archivoAbierto);
                    } else {
                        die( "No se pudo abrir el archivo." );
                    }
                }else{
                    echo "No elimina pago repetido ({$row2['id_pedido_pago']})<br>";
                    $archivoAbierto = fopen($archivo, "a");
                    if ($archivoAbierto) {
                        fwrite($archivoAbierto, "El pago no se elimina ({$row2['id_pedido_pago']}).\n");
                        fclose($archivoAbierto);
                    } else {
                        die( "No se pudo abrir el archivo." );
                    }
                }
                $row_counter ++;
            }
        }
    //consulta los pedidos pagos repetidos por folio unico
        $sql="SELECT 
                folio_unico
            FROM ec_pedido_pagos
            WHERE folio_unico IS NOT NULL
            AND folio_unico != 'AGRUPACION'
            GROUP BY folio_unico
            HAVING COUNT(*) > 1";
        //$eje=mysql_query($sql)or die("Error al consultar las afiliaciones para este cajero!!!<br>".mysql_error());
        $stm = $link->query( $sql ) or die( "Error al consultar los pedidos pagos repetidos : {$sql} : {$link->error}" );
        echo "Consulta los pedidos pagos repetidos por folio unico : <br>{$sql}<br>";
        $archivoAbierto = fopen($archivo, "a");
        if ($archivoAbierto) {
            fwrite($archivoAbierto, "Consulta pagos repetidos :\n{$sql}\n");
            fclose($archivoAbierto);
        } else {
            die( "No se pudo abrir el archivo." );
        }
        
        $link->autocommit(false);

        while( $row = $stm->fetch_assoc() ){
            $sql = "SELECT id_pedido_pago FROM ec_pedido_pagos WHERE folio_unico = '{$row['folio_unico']}'";
            $stm2 = $link->query( $sql ) or die( "Error al consultar los registros por eliminar : {$sql} : {$link->error}" );
            echo "Consulta pagos repetidos independientemente. : <br>{$sql}<br>";
            $archivoAbierto = fopen($archivo, "a");
            if ($archivoAbierto) {
                fwrite($archivoAbierto, "Consulta pagos repetidos independientemente. {$sql}\n");
                fclose($archivoAbierto);
            } else {
                die( "No se pudo abrir el archivo." );
            }
            $row_counter = 0;
            while( $row2 = $stm2->fetch_assoc() ){
                if( $row_counter > 0 ){
                    $sql = "DELETE FROM ec_pedido_pagos WHERE id_pedido_pago = {$row2['id_pedido_pago']}";
                    $stm3 = $link->query( $sql ) or die( "Error al eliminar registro de cobro : {$sql} : {$link->error}" );
                    echo "Elimina pago repetido independientemente : <br>{$sql}<br>";
                    $archivoAbierto = fopen($archivo, "a");
                    if ($archivoAbierto) {
                        fwrite($archivoAbierto, "Elimina Pago ({$row2['id_pedido_pago']}).\n");
                        fclose($archivoAbierto);
                    } else {
                        die( "No se pudo abrir el archivo." );
                    }
                }else{
                    echo "No elimina pago repetido ({$row2['id_pedido_pago']})<br>";
                    $archivoAbierto = fopen($archivo, "a");
                    if ($archivoAbierto) {
                        fwrite($archivoAbierto, "El pago no se elimina ({$row2['id_pedido_pago']}).\n");
                        fclose($archivoAbierto);
                    } else {
                        die( "No se pudo abrir el archivo." );
                    }
                }
                $row_counter ++;
            }
        }

        
    //consulta los pedidos pagos repetidos por folio unico
        $sql="SELECT 
            folio_unico
        FROM ec_devolucion_pagos
        WHERE folio_unico IS NOT NULL
        AND folio_unico != 'AGRUPACION'
        GROUP BY folio_unico
        HAVING COUNT(*) > 1";
        //$eje=mysql_query($sql)or die("Error al consultar las afiliaciones para este cajero!!!<br>".mysql_error());
        $stm = $link->query( $sql ) or die( "Error al consultar los pedidos pagos repetidos : {$sql} : {$link->error}" );
        echo "Consulta los pedidos pagos repetidos por folio unico : <br>{$sql}<br>";
        $archivoAbierto = fopen($archivo, "a");
        if ($archivoAbierto) {
        fwrite($archivoAbierto, "Consulta pagos repetidos :\n{$sql}\n");
        fclose($archivoAbierto);
        } else {
        die( "No se pudo abrir el archivo." );
        }

        $link->autocommit(false);

        while( $row = $stm->fetch_assoc() ){
        $sql = "SELECT id_devolucion_pago FROM ec_devolucion_pagos WHERE folio_unico = '{$row['folio_unico']}'";
        $stm2 = $link->query( $sql ) or die( "Error al consultar los registros por eliminar : {$sql} : {$link->error}" );
        echo "Consulta pagos devolucion repetidos independientemente. : <br>{$sql}<br>";
        $archivoAbierto = fopen($archivo, "a");
        if ($archivoAbierto) {
            fwrite($archivoAbierto, "Consulta pagos devolucion repetidos independientemente {$sql}.\n");
            fclose($archivoAbierto);
        } else {
            die( "No se pudo abrir el archivo." );
        }
        $row_counter = 0;
        while( $row2 = $stm2->fetch_assoc() ){
            if( $row_counter > 0 ){
                $sql = "DELETE FROM ec_devolucion_pagos WHERE id_devolucion_pago = {$row2['id_devolucion_pago']}";
                $stm3 = $link->query( $sql ) or die( "Error al eliminar registro de cobro : {$sql} : {$link->error}" );
                echo "Elimina pago repetido independientemente : <br>{$sql}<br>";
                $archivoAbierto = fopen($archivo, "a");
                if ($archivoAbierto) {
                    fwrite($archivoAbierto, "Elimina devolucion Pago ({$row2['id_devolucion_pago']}).\n");
                    fclose($archivoAbierto);
                } else {
                    die( "No se pudo abrir el archivo." );
                }
            }else{
                echo "No elimina devolucion pago repetido ({$row2['id_devolucion_pago']})<br>";
                $archivoAbierto = fopen($archivo, "a");
                if ($archivoAbierto) {
                    fwrite($archivoAbierto, "El pago devolucion no se elimina ({$row2['id_devolucion_pago']}).\n");
                    fclose($archivoAbierto);
                } else {
                    die( "No se pudo abrir el archivo." );
                }
            }
            $row_counter ++;
        }
        }

    //consulta pagos repetidos de NetPay
        $sql = "SELECT observaciones FROM ec_cajero_cobros WHERE id_terminal > 0 GROUP BY observaciones HAVING COUNT(*) > 1";
        $stm = $link->query($sql ) or die( "Error al consultar cajero cobros repetidos : {$sql} : {$link->error}" );
        echo "Consulta pagos repetidos : <br>{$sql}<br>";
        while( $row = $stm->fetch_assoc() ){
            $sql = "SELECT id_cajero_cobro FROM ec_cajero_cobros WHERE observaciones = '{$row['observaciones']}'";
            $stm2 = $link->query($sql ) or die( "Error al consultar cajero cobros repetidos : {$sql} : {$link->error}" );
            echo "Consulta pagos repetidos de manera independiente : <br>{$sql}<br>";
            $row_counter = 0;
            while( $row2 = $stm2->fetch_assoc() ){//echo 'here';
                if( $row_counter > 0 ){
                //elimina cajero cobro
                    $sql = "DELETE FROM ec_cajero_cobros WHERE id_cajero_cobro = {$row2['id_cajero_cobro']}";
                    $stm3 = $link->query($sql ) or die( "Error al consultar cajero cobro repetido : {$sql} : {$link->error}" );
                    echo "Elimina cajero cobro repetido de manera independiente : <br>{$sql}<br>";
                    if ($archivoAbierto) {
                        fwrite($archivoAbierto, "Elimina registro de cobro multiplicado en NetPay : {$row2['id_cajero_cobro']}.\n");
                        fclose($archivoAbierto);
                    } else {
                        die( "No se pudo abrir el archivo." );
                    }
                //elimina pago
                    $sql = "DELETE FROM ec_pedido_pagos WHERE id_cajero_cobro = {$row2['id_cajero_cobro']}";
                    $stm4 = $link->query($sql ) or die( "Error al eliminar pago de cajero cobro repetido : {$sql} : {$link->error}" );
                    echo "Elimina pago repetido de manera independiente : <br>{$sql}<br>";
                    if ($archivoAbierto) {
                        fwrite($archivoAbierto, "Elimina registro de pago cajero cobro multiplicado en NetPay : {$row2['id_cajero_cobro']}.\n");
                        fclose($archivoAbierto);
                    } else {
                        die( "No se pudo abrir el archivo." );
                    }
                }else{
                    echo "No elimina cobro repetido ({$row2['id_cajero_cobro']})<br>";
                    if ($archivoAbierto) {
                        fwrite($archivoAbierto, "No se elimino el cobro de NetPay ({$row2['id_cajero_cobro']}).\n");
                        fclose($archivoAbierto);
                    } else {
                        die( "No se pudo abrir el archivo." );
                    }
                }
                $row_counter ++;
            }
        }
        $link->commit();
        $link->autocommit(true);

?>