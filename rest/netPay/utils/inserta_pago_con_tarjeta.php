<?php
    if( trim($message_) == 'Transaccion exitosa' && $transType == 'A' ){
        //consulta los datos en relacion al numero de serie de la terminal
          $sql = "";
         // if( isset( $traceability['smart_accounts'] ) && $traceability['smart_accounts'] == true ){
            
            $sql = "SELECT
                    t.id_terminal_integracion AS affiliation_id,
                    cc.id_caja_cuenta AS bank_id,
                    (SELECT
                    id_pedido FROM ec_pedidos
                    WHERE folio_nv = '{$traceability['folio_venta']}'
                    LIMIT 1
                    ) AS sale_id
                  FROM ec_terminales_integracion_smartaccounts t
                  LEFT JOIN ec_caja_o_cuenta cc
                  ON t.id_caja_cuenta = cc.id_caja_cuenta
                  WHERE t.numero_serie_terminal = '{$terminalId}'
                  AND t.store_id = '{$traceability['store_id_netpay']}'";
      
         /* }else{
          //  die( "here 1" );
            $sql = "SELECT 
                      a.id_afiliacion AS affiliation_id,
                      cc.id_caja_cuenta AS bank_id,
                      (SELECT 
                        id_pedido FROM ec_pedidos 
                        WHERE folio_nv = '{$traceability['folio_venta']}' 
                        LIMIT 1
                      ) AS sale_id
                    FROM ec_afiliaciones a
                    LEFT JOIN ec_caja_o_cuenta cc
                    ON a.id_banco = cc.id_caja_cuenta
                    WHERE a.numero_serie_terminal = '{$terminalId}'";
          }*/
        //  die( $sql );
          $stm = $link->query( $sql ); 
        /*Logger*/
          if( $log_id != null ){
            $steep_log_id = $Logger->insertLoggerSteepRow( $log_id, "Recupera datos para insertar el cobro del cajero", $sql );
          }
          if( $link->error ){
            if( $log_id != null ){
              $steep_log_error = $Logger->insertErrorSteepRow( $steep_log_id, 'ec_afiliaciones / ec_caja_o_cuenta', 'N/A', $sql, $link->error );
            }
            die( "Error al recuperar datos para insertar el cobro del cajero {$link->error}" );
          }
          $row = $stm->fetch_assoc();
          //consulta entre interno y externo
              $sql = "SELECT
                        ROUND( ax.internal/ax.total, 6 ) AS internal_porcent,
                        ROUND( ax.external/ax.total, 6 ) AS external_porcent
                      FROM(
                        SELECT
                          SUM( pd.monto ) AS total,
                          SUM( IF( sp.es_externo = 0, pd.monto-pd.descuento, 0 ) ) AS internal,
                          SUM( IF( sp.es_externo = 1, pd.monto-pd.descuento, 0 ) ) AS external
                        FROM ec_pedidos_detalle pd
                        LEFT JOIN sys_sucursales_producto sp
                        ON pd.id_producto = sp.id_producto
                        AND sp.id_sucursal = {$traceability['id_sucursal']}
                        WHERE pd.id_pedido = '{$row['sale_id']}'
                      )ax";
            $stm = $link->query( $sql );
          /*<Logger>*/
            if( $log_id != null ){
              $steep_log_id = $Logger->insertLoggerSteepRow( $log_id, "Consulta porcentajes de pagos", $sql );
            }
            if( $link->error ){
              if( $log_id != null ){
                $steep_log_error = $Logger->insertErrorSteepRow( $steep_log_id, 'ec_pedidos_detalle / sys_sucursales_producto', 'N/A', $sql, $link->error );
              }
              die( "Error al consultar porcentajes de pagos : {$sql} {$link->error}" );
            }
          /*</Logger>*/
      //die( "here 1.5" );
            $payment_row = $stm->fetch_assoc();//pagos de saldo a favor Oscar 2024-02-15
        
            $Payments = new Payments( $link, $traceability['id_sucursal'], $Logger );
            $Payments->insertPaymentsDepending( $amount, $row['sale_id'], $traceability['id_cajero'], $traceability['id_sesion_cajero'], 0, -1, $log_id );// $pago_por_saldo_a_favor
            if( $traceability['id_devolucion_relacionada'] != 0 && $traceability['id_devolucion_relacionada'] != '' && $traceability['id_devolucion_relacionada'] != null ){
              $Payments->reinsertaPagosPorDevolucionCaso2( $row['sale_id'], $traceability['id_cajero'], $traceability['id_sesion_cajero'], 'n/a', 0, 0, $log_id );
            }
            $internal_payment_id = '0';
            $external_payment_id = '0';
          //inserta pago interno    
            if( $payment_row['internal_porcent'] > 0 ){
              $sql = "INSERT INTO ec_pedido_pagos ( id_pedido, id_tipo_pago, fecha, hora, monto, referencia, id_moneda, tipo_cambio, 
              id_nota_credito, id_cxc, es_externo, id_cajero, id_sesion_caja )
              VALUES( {$row['sale_id']}, 7, NOW(), NOW(), ( {$amount}*{$payment_row['internal_porcent']} ), '', 1, 1, -1, -1, 0, 
                '{$traceability['id_cajero']}', '{$traceability['id_sesion_cajero']}' )";
              $stm = $link->query( $sql );
              /*<Logger>*/
                if( $log_id != null ){
                  $steep_log_id = $Logger->insertLoggerSteepRow( $log_id, "Inserta el pago de la venta", $sql );
                }
                if( $link->error ){
                  if( $log_id != null ){
                    $steep_log_error = $Logger->insertErrorSteepRow( $steep_log_id, 'ec_pedido_pagos', 'N/A', $sql, $link->error );
                  }
                  die( "Error al insertar el pago de la venta : {$sql} {$link->error}" );
                }
              /*</Logger>*/
              $sql = "SELECT MAX( id_pedido_pago ) AS last_sale_payment_id FROM ec_pedido_pagos LIMIT 1";
              $aux_stm = $link->query( $sql );  
              /*<Logger>*/
                if( $log_id != null ){
                  $steep_log_id = $Logger->insertLoggerSteepRow( $log_id, "Consulta el id del pago insertado (interno)", $sql );
                }
                if( $link->error ){
                  if( $log_id != null ){
                    $steep_log_error = $Logger->insertErrorSteepRow( $steep_log_id, 'ec_pedido_pagos', 'N/A', $sql, $link->error );
                  }
                  die( "Error al consultar el id del pago insertado (interno) : {$link->error}" );
                }
              /*</Logger>*/
              $aux_row = $aux_stm->fetch_assoc();
              $internal_payment_id = $aux_row['last_sale_payment_id'];
             //$internal_payment_id = $link->insert_id;
            }
          //inserta pago externo    
            if( $payment_row['external_porcent'] > 0 ){//aqui se modificó error de netPay ( solo externos ) Oscar 30-01-2024 desde development2024
              $sql = "INSERT INTO ec_pedido_pagos ( id_pedido, id_tipo_pago, fecha, hora, monto, referencia, id_moneda, tipo_cambio, 
              id_nota_credito, id_cxc, es_externo, id_cajero, id_sesion_caja )
              VALUES( {$row['sale_id']}, 7, NOW(), NOW(), ( {$amount}*{$payment_row['external_porcent']} ), '', 1, 1, -1, -1, 1, 
                '{$traceability['id_cajero']}', '{$traceability['id_sesion_cajero']}' )";
              $stm = $link->query( $sql );
            /*<Logger>*/
              if( $log_id != null ){
                $steep_log_id = $Logger->insertLoggerSteepRow( $log_id, "Inserta el pago del pedido ( externo )", $sql );
              }
              if( $link->error ){
                if( $log_id != null ){
                  $steep_log_error = $Logger->insertErrorSteepRow( $steep_log_id, 'ec_pedido_pagos', 'N/A', $sql, $link->error );
                }
                die( "Error al insertar el pago del pedido ( externo ) : {$sql} {$link->error}" );
              }
            /*</Logger>*/
              $sql = "SELECT MAX( id_pedido_pago ) AS last_sale_payment_id FROM ec_pedido_pagos LIMIT 1";
              $aux_stm = $link->query( $sql );
            /*<Logger>*/
              if( $log_id != null ){
                $steep_log_id = $Logger->insertLoggerSteepRow( $log_id, "Consultar el id del pago insertado ( externo )", $sql );
              }
              if( $link->error ){
                if( $log_id != null ){
                  $steep_log_error = $Logger->insertErrorSteepRow( $steep_log_id, 'ec_pedido_pagos', 'N/A', $sql, $link->error );
                }
                die( "Error al consultar el id del pago insertado (externo) : {$link->error}" );
              }
            /*</Logger>*/
              $aux_row = $aux_stm->fetch_assoc();
              $external_payment_id = $aux_row['last_sale_payment_id'];
            }
      
        /*inserta el cobro del pedido
            $sql = "INSERT INTO ec_pedido_pagos ( id_pedido, id_tipo_pago, fecha, hora, monto, referencia, id_moneda, tipo_cambio, 
            id_nota_credito, id_cxc, es_externo )
            VALUES( {$row['sale_id']}, 7, NOW(), NOW(), {$amount}, '', 1, 1, -1, -1, 0 )";
            $stm = $link->query( $sql ) or die( "Error al insertar el cobro del pedido : {$link->error}" );*/
      
      //inserta el cobro del cajero si el cobro fue exitoso
          $sql = "INSERT INTO ec_cajero_cobros( /*1*/id_cajero_cobro, id_sucursal, /*2*/id_pedido, /*3*/id_cajero, /*4*/id_terminal, 
          /*5*/id_banco, /*6*/monto, /*7*/fecha, /*8*/hora, /*9*/observaciones, /*10*/sincronizar, /*11*/id_sesion_caja, /*12*/id_tipo_pago ) 
          VALUES ( /*1*/NULL, '{$traceability['id_sucursal']}', /*2*/'{$row['sale_id']}', /*3*/'{$traceability['id_cajero']}', /*4*/'{$row['affiliation_id']}', 
          /*5*/'{$row['bank_id']}', /*6*/'{$amount}', /*7*/NOW(), /*8*/NOW(), /*9*/'{$orderId}', /*10*/1, 
          /*11*/{$traceability['id_sesion_cajero']}, /*12*/7 )";
      //    error_log( $sql );
          $stm = $link->query( $sql );
        /*<Logger>*/
          if( $log_id != null ){
            $steep_log_id = $Logger->insertLoggerSteepRow( $log_id, "Insertar el cobro del cajero", $sql );
          }
          if( $link->error ){
            if( $log_id != null ){
              $steep_log_error = $Logger->insertErrorSteepRow( $steep_log_id, 'ec_cajero_cobros', 'N/A', $sql, $link->error );
            }
            die( "Error al insertar el cobro del cajero : {$link->error}" );
          }
        /*</Logger>*/
          $sql = "SELECT MAX( id_cajero_cobro ) AS last_id FROM ec_cajero_cobros LIMIT 1";
          $last_id_stm = $link->query( $sql );
          $last_id_row = $last_id_stm->fetch_assoc();
          $payment_id = $last_id_row['last_id'];//$link->insert_id;
      //actualiza el id de sesion de caja del pedido 
          $sql = "UPDATE ec_pedidos SET id_cajero = {$traceability['id_cajero']}, id_sesion_caja = {$traceability['id_sesion_cajero']} WHERE id_pedido = {$row['sale_id']}";
          $stm_pedido = $link->query( $sql );
          /*<Logger>*/
            if( $log_id != null ){
              $steep_log_id = $Logger->insertLoggerSteepRow( $log_id, "Actualiza ids de cajero y sesion de caja desde Webhook", $sql );
            }
            if( $link->error ){
              if( $log_id != null ){
                $steep_log_error = $Logger->insertErrorSteepRow( $steep_log_id, 'ec_cajero_cobros', 'N/A', $sql, $link->error );
              }
              die( "Error al actualizar ids de cajero y sesion de caja desde Webhook : {$link->error}" );
            }
      //actualiza el cajero de los cobros
      //actualiza el id de cajero cobro en la transaccion
            $sql = "UPDATE vf_transacciones_netpay 
                      SET id_cajero_cobro = '{$payment_id}'
                    WHERE id_transaccion_netpay = '{$folioNumber}'";
            $stm = $link->query( $sql );
          /*<Logger>*/
            if( $log_id != null ){
              $steep_log_id = $Logger->insertLoggerSteepRow( $log_id, "Actualiza el cobro del cajero en registro de peticion", $sql );
            }
            if( $link->error ){
              if( $log_id != null ){
                $steep_log_error = $Logger->insertErrorSteepRow( $steep_log_id, 'vf_transacciones_netpay', 'N/A', $sql, $link->error );
              }
              die( "Error al actualizar el cobro del cajero en el registro de peticion : {$link->error}" );
            }
          /*</Logger>*/
        //actualiza en la venta el id de cajero que cobro el pago*/
          if( $row['sale_id'] != null && $row['sale_id'] != '' ){
            $sql="UPDATE ec_pedidos 
                    SET id_cajero = '{$traceability['id_cajero']}' 
                    WHERE id_pedido = {$row['sale_id']}";
            $stm = $link->query( $sql );
          /*<Logger>*/
            if( $log_id != null ){
              $steep_log_id = $Logger->insertLoggerSteepRow( $log_id, "Actualiza el pedido para el cajero", $sql );
            }
            if( $link->error ){
              if( $log_id != null ){
                $steep_log_error = $Logger->insertErrorSteepRow( $steep_log_id, 'ec_pedidos', 'N/A', $sql, $link->error );
              }
              die( "Error al actualizar el pedido para el cajero : {$link->error}" );
            }
          /*</Logger>*/
        //actualiza en el pago el id de cajero que cobro el pago Oscar 2023-01-10*/
          //if( $row['sale_id'] != null && $row['sale_id'] != '' ){
            $sql="UPDATE ec_pedido_pagos 
                    SET id_cajero_cobro = '{$payment_id}' 
                    WHERE id_pedido_pago IN( {$internal_payment_id}, {$external_payment_id} )";
            $stm = $link->query( $sql );
          /*<Logger>*/
            if( $log_id != null ){
              $steep_log_id = $Logger->insertLoggerSteepRow( $log_id, "Actualiza el cobro con el cajero", $sql );
            }
            if( $link->error ){
              if( $log_id != null ){
                $steep_log_error = $Logger->insertErrorSteepRow( $steep_log_id, 'ec_pedido_pagos', 'N/A', $sql, $link->error );
              }
              die( "Error al actualizar el cobro con el cajero : {$link->error}" );
            }
          /*</Logger>*/
          //actualiza el id de cajero que cobro el pago*/
            $sql="UPDATE ec_pedido_pagos 
                    SET id_cajero = '{$traceability['id_cajero']}',
                    fecha = now(),
                    hora = now() 
                    WHERE id_pedido = {$row['sale_id']}
                    AND id_cajero=0";
            $stm = $link->query( $sql );
          /*<Logger>*/
            if( $log_id != null ){
              $steep_log_id = $Logger->insertLoggerSteepRow( $log_id, "Actualiza el pago con el cajero", $sql );
            }
            if( $link->error ){
              if( $log_id != null ){
                $steep_log_error = $Logger->insertErrorSteepRow( $steep_log_id, 'ec_pedido_pagos', 'N/A', $sql, $link->error );
              }
              die( "Error al actualizar el pago con el cajero : {$link->error}" );
            }
          /*</Logger>*/
          }
      /*$fp = fopen('data.txt', 'w');
      fwrite($fp, $sql );
      fclose($fp);*/
    }
?>