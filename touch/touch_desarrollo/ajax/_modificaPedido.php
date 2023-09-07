<?php
    
    header("Content-Type: text/plain;charset=utf-8");
    
    include("../../conectMin.php");
    
    
    extract($_GET);
    
    
    mysql_query("BEGIN");
    try
    {
    
        $ids='';
        $devids=array();
        $devcan=array();
        
        $nuOrd=0;
        $dev=0;
        $totPedN=0;
        
        
        for($i=0;$i<$numDet;$i++)
        {
            if($idDetalle[$i] == 'NO')
            {
                $nuOrd++;
                $totPedN+=$monto[$i];
            }    
            if($idDetalle[$i] != 'NO')
                $dev++;
        }
        
        //Datos pedido
        $sql="SELECT id_cliente FROM ec_pedidos WHERE id_pedido=$id_pedido";
        $rs = mysql_query($sql);
        $row=mysql_fetch_assoc($rs);
        extract($row);
        
        //Insertamos la devolucion
        if($dev > 0)
        {
             //Insertamos la devolucion
            $sql="INSERT INTO ec_devolucion(id_usuario, id_sucursal, fecha, hora, id_pedido, folio)
                                     VALUES($user_id, $user_sucursal, NOW(), NOW(), $id_pedido, 'DEV$id_pedido')";
            if(!mysql_query($sql))
                    throw new Exception("No se pudo actualizar el pedido:\n\n $sql\n\n" . mysql_error());
            
            $idDev=mysql_insert_id();
            
            //Insertamos el detalle de la devolcion
            
            //$tot=0;
            
            //print($idDetalle);
            
            for($i=0;$i<$numDet;$i++)
            {
                if($idDetalle[$i] != 'NO')
                {
                    $sql="INSERT INTO ec_devolucion_detalle(id_devolucion, id_producto, cantidad)
                                                     VALUES($idDev, $idProducto[$i], $cantidad[$i])";
                    if(!mysql_query($sql))
                        throw new Exception("No se pudo actualizar el pedido:\n\n $sql\n\n" . mysql_error());                                 
                }    
            }
            
            
            if($beneficiario != 'CASA DE LAS LUCES')
            {
                //Insertamos los pagos de la devolucion
                for($i=0;$i<$numPag;$i++)
                {
                    $sql="INSERT INTO ec_devolucion_pagos(id_devolucion, id_tipo_pago, monto, referencia, fecha, hora)
                                               VALUES($idDev, $tipoPago[$i], $montoPago[$i], '', NOW(), NOW())";
                                               
                    if(!mysql_query($sql))
                        throw new Exception("No se pudo actualizar el pedido:\n\n $sql\n\n" . mysql_error());                                            
                }
            }    
        }
        
        if($nuOrd > 0)
        {
            //Buscamos el folio
            
            $prefijo = "";
            $folio = "";
            
            $cs = "SELECT prefijo FROM sys_sucursales WHERE id_sucursal = '{$user_sucursal}' ";
            if ($rs = mysql_query($cs)) {
                if ($dr = mysql_fetch_assoc($rs)) {
                    $prefijo = $dr["prefijo"];
                } mysql_free_result($rs);
            }
            
            $campo_segun_tipo = "folio_nv";
            
            $cs = "SELECT IF(ISNULL(MAX(CAST(REPLACE({$campo_segun_tipo}, '{$prefijo}', '') AS SIGNED INT))), 1, MAX(CAST(REPLACE({$campo_segun_tipo}, '{$prefijo}', '') AS SIGNED INT))+1) AS folio " .
                "FROM ec_pedidos " .
                "WHERE REPLACE({$campo_segun_tipo}, '{$prefijo}', '') REGEXP ('[0-9]') " .
                "AND id_sucursal='{$user_sucursal}' " .
                "AND id_pedido <> '{$id_pedido}' ";
            #die ($cs);
            if ($rs = mysql_query($cs)) {
                if ($dr = mysql_fetch_assoc($rs)) {
                    $folio = $prefijo . sprintf("%05d", $dr["folio"]);
                    //die ("OK|FOLIO:{$folio}");
                } mysql_free_result($rs);
            }
            
            $sql="INSERT INTO ec_pedidos(folio_nv, id_cliente, id_estatus, id_moneda, fecha_alta, id_direccion, id_razon_social, subtotal, iva, ieps, total, pagado, surtido, enviado, id_sucursal, id_usuario, fue_cot, facturado, id_tipo_envio, descuento, id_razon_factura)
                                  VALUES('$folio', $id_cliente, 7, 1, NOW(), -1, -1, $totPedN, 0, 0, $totPedN, 1, 1, 1, $user_sucursal, $user_id, 0, 0, 1, 0, -1)";
            
             if(!mysql_query($sql))
                        throw new Exception("No se pudo actualizar el pedido:\n\n $sql\n\n" . mysql_error());
             
             $id_ped=mysql_insert_id();
             
             
              for($i=0;$i<$numDet;$i++)
                {
                    if($idDetalle[$i] == 'NO')
                    {
                        $sql="INSERT INTO ec_pedidos_detalle(id_pedido, id_producto, cantidad, precio, monto, iva, ieps, cantidad_surtida)
                                                      VALUES($id_ped, $idProducto[$i], $cantidad[$i], $precio[$i], $monto[$i], 0, 0, $cantidad[$i])";
                        if(!mysql_query($sql))
                            throw new Exception("No se pudo actualizar el pedido:\n\n $sql\n\n" . mysql_error());                                 
                    }    
                }
                
                
                
                 if($beneficiario == 'CASA DE LAS LUCES')
            {
                //Insertamos los pagos de la devolucion
                for($i=0;$i<$numPag;$i++)
                {
                    $sql="INSERT INTO ec_pedido_pagos(id_pedido, id_tipo_pago, fecha, hora, monto, referencia, id_moneda, tipo_cambio, id_nota_credito, id_cxc)
                                               VALUES($id_ped, $tipoPago[$i], NOW(), NOW(), $montoPago[$i], '', 1, 1, -1, -1)";
                                               
                    if(!mysql_query($sql))
                        throw new Exception("No se pudo actualizar el pedido:\n\n $sql\n\n" . mysql_error());                                            
                }
            } 
        }
        
        
   
    
        mysql_query("COMMIT");
        //mysql_query("ROLLBACK");
        
        echo "exito|$id_ped|$idDev";
        
    }
    catch (Exception $e)
    {
        echo "Error: " . $e->getMessage();
        mysql_query("ROLLBACK");
        mysql_close();
        exit ();
    } 

?>