<?php

    include("../../conectMin.php");
    
    header("Content-Type: text/plain;charset=utf-8");
    mysql_set_charset("utf8");
    if (function_exists("mb_internal_encoding")) mb_internal_encoding ('utf-8');
    
    $es_regalo = isset($_GET["re"]) ? $_GET["re"] : "0";
    $es_pedido = isset($_GET["pe"]) ? $_GET["pe"] : "0";
    $es_paquete = isset($_GET["pa"]) ? $_GET["pa"] : "0";
    $id_pedido = isset($_GET["idp"]) ? $_GET["idp"] : "0";
    $folio = "0";
    $nitems = $_GET["nitems"];
    $descuento = 0;
    $prefijo = "";
    $es_nuevo_registro = !($id_pedido > 0);
    $tipo_folio = $es_pedido ? "pedido" : "nv";
    
    try
    {
        mysql_query("BEGIN");
        
        
        # Guardar el encabezado
        $cs = "INSERT INTO ec_autorizacion(fecha, hora, autorizado, id_sucursal, id_vendedor)
                                    VALUES(NOW(), NOW(), 0, $user_sucursal, $user_id)";
        
            
        if (!mysql_query($cs)) throw new Exception("Imposible almacenar este registro (pedidos). " . mysql_error());
        
        $id_autorizacion=mysql_insert_id();
        
        //throw new Exception("Items: $nitems");
        
        for ($ix=0; $ix<$nitems; ++$ix)
        {
            $existe_detalle = false;
            $id_detalle = "0";
            
            
            
            #Consigue el precio
            $precio = 0.0;
            $cs = "SELECT IF(ISNULL(PD.precio_oferta), 0, PD.precio_oferta) AS precio_oferta, IF(ISNULL(PD.precio_venta), 0, PD.precio_venta) AS precio_venta, IF(ISNULL(P.precio_venta), 0, P.precio_venta) AS precio_default FROM ec_productos P  " .
                "left outer join ec_precios_detalle PD ON PD.id_producto = P.id_productos " .
                "left outer join sys_sucursales S ON S.id_precio = PD.id_precio AND S.id_sucursal = '{$user_sucursal}' " .
                "WHERE P.id_productos = '{$_GET["idp{$ix}"]}' ";
            
            if ($rs = mysql_query($cs)) {
                if ($dr = mysql_fetch_assoc($rs)) {
                    $precio = number_format($dr["precio_oferta"] > 0 ? $dr["precio_oferta"] : ($dr["precio_venta"] > 0 ? $dr["precio_venta"] : $dr["precio_default"]), 2);
                } mysql_free_result($rs);
            } else {
                throw new Exception ("No se consiguió el precio de un producto del detalle del pedido.");
            }
            
            $precio_original = $precio;
            $precio_descuento = $precio * $descuento;
            $precio_final = $precio_original - $precio_descuento;
            
            $monto = $precio_final * $_GET["can{$ix}"];
            $monto_descuento = $precio_descuento * $_GET["can{$ix}"];
            
            $subtotal_iva = $monto * $iva;
            $subtotal_ieps = $monto * $ieps;
                
            $pedido_subtotal += $monto;
            $pedido_iva += $subtotal_iva;
            $pedido_ieps += $subtotal_ieps;
            $pedido_total += ($monto + $subtotal_iva);
            $pedido_descuento += $monto_descuento;


            $pre=$_GET["pre{$ix}"];

            
            # Guardar el registro del detalle 
            $cs = "INSERT INTO ec_autorizacion_detalle(id_autorizacion, id_producto, cantidad, precio)
                                                 VALUE('{$id_autorizacion}', '{$_GET["idp{$ix}"]}', '{$_GET["can{$ix}"]}', {$pre})";
            
            
            
                
            if (!mysql_query($cs)) throw new Exception("Imposible almacenar registro (detalle de pedido). " . mysql_error());           
        }
        
        
        //Insertamos la alerta
        
        //contenido.php?aab9e1de16f38176f86d7a92ba337a8d=ZWNfYXV0b3JpemFjaW9u&a1de185b82326ad96dec8ced6dad5fbbd=MQ==&a01773a8a11c5f7314901bdae5825a190=MTE3&bnVtZXJvX3RhYmxh=MA==
        
        $sql="INSERT INTO ec_alerta(nombre, fecha, hora, tipo, id_autorizacion)
                             VALUES('Hay pendiente una autorización de regalo del vendedor $user_name', NOW(), NOW(), 'code/general/contenido.php?aab9e1de16f38176f86d7a92ba337a8d=ZWNfYXV0b3JpemFjaW9u&a1de185b82326ad96dec8ced6dad5fbbd=MQ==&a01773a8a11c5f7314901bdae5825a190=".base64_encode($id_autorizacion)."&bnVtZXJvX3RhYmxh=MA==', '{$id_autorizacion}')";
        if (!mysql_query($sql)) throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
        
        
        $id_alerta=mysql_insert_id();
               
       //Insertamos al usuario encargado
       $sql="SELECT id_encargado FROM sys_sucursales WHERE id_sucursal=$user_sucursal";
       
       $res=mysql_query($sql);
       if(!$res)
            throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
       
       $row=mysql_fetch_row($res);
       
       $sql="INSERT INTO ec_alerta_registro(id_alerta, id_usuario, descripcion, visto)
                                     VALUES($id_alerta, $row[0], '', 0)";
                                     
       if (!mysql_query($sql)) throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
       

        
        mysql_query("COMMIT");
        
        echo "{$id_autorizacion}";
        
    }
    catch (Exception $e)
    {
        echo "ERR|" . $e->getMessage();
        mysql_query("ROLLBACK");
        mysql_close();
        exit ();
    }    


?>