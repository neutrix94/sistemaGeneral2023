<?php
	include('../../conectMin.php');
	extract($_GET);
	//die('porcDesc: '.$porcDesc);
	$auxDesc=$porcDesc/100;
	$porcDesc=$auxDesc;
	//datos +="&idp"+i+"="+tds[0].innerHTML+"&can"+i+"="+tds[3].innerHTML+"&prec"+i+"="+tds[4].innerHTML
    //       +"&desc"+i+"="+tds[5].innerHTML+"$montoDev"+i+"="+tds[6].innerHTML;
/***********************************************************************************************************************************************************************
************************************************************************************************************************************************************************
************************************************************************ ACTUALIZAMOS EL TICKET ************************************************************************
************************************************************************************************************************************************************************
***********************************************************************************************************************************************************************/
//iniciamos transaccion
    mysql_query("BEGIN");
    
/*implementacion Oscar 2023 para detonar devolucion de exhibicion*/
    for($i=0;$i<$nitems;$i++){
        $product_id = $_GET["idp{$i}"];
        $sql = "UPDATE ec_temporal_exhibicion te 
                INNER JOIN ec_temporal_exhibicion_proveedor_producto tepp
                ON te.id_temporal_exhibicion = tepp.id_temporal_exhibicion
                SET te.tiene_devolucion = 1, tepp.tiene_devolucion = 1
                WHERE te.id_pedido = {$idp}
                AND tepp.id_producto = {$product_id}";
        $stm = mysql_query( $sql ) or die( "Error al marcar exhibicion con devolucion : " . mysql_error() );
    }
/*fin de cambio Oscar 2023*/

/*implementación de Oscar 2019 para saber si la sucursal es multicajero*/
    $sql="SELECT 
            IF(multicajero=1,0,
                (SELECT 
                    id_cajero 
                FROM ec_sesion_caja 
                WHERE id_sucursal=$user_sucursal 
                AND fecha=DATE_FORMAT(now(),'%Y-%m-%d') 
                AND hora_fin='00:00:00') 
            ), 
        /*implementacion Oscar 2023 para obtener el id de sesion de caja*/
            IF(multicajero=1,0,
                (SELECT 
                    id_sesion_caja 
                FROM ec_sesion_caja 
                WHERE id_sucursal=$user_sucursal 
                AND fecha=DATE_FORMAT(now(),'%Y-%m-%d') 
                AND hora_fin='00:00:00') 
            )
    FROM ec_configuracion_sucursal 
    WHERE id_sucursal=$user_sucursal";
    $eje=mysql_query($sql)or die("Error al consultar si la sucursal es multicajero!!!\n".mysql_error());
    $r_c=mysql_fetch_row($eje);
    $id_cajero=$r_c[0];
    $id_sesion_caja=$r_c[1];
//    die($id_cajero);
/*Find e cambio Oscar 2019*/

//verificamos si el pedido esta como pagado
    $sql="SELECT pagado FROM ec_pedidos WHERE id_pedido=$idp";
    $eje=mysql_query($sql)or die("Error al consultar si el pedido ya está pagado!!!\n\n".mysql_error());
    $pg=mysql_fetch_row($eje);
    $esta_pagado=$pg[0];
//verificamos si hay productos externos
    $sql="SELECT 
                SUM(IF(pd.id_pedido_detalle IS NULL,0,IF(pd.es_externo=0,1,0))) as internos,
                SUM(IF(pd.id_pedido_detalle IS NULL,0,IF(pd.es_externo=1,1,0))) as externos,
                ped.pagado
            FROM ec_pedidos_detalle pd 
            LEFT JOIN ec_pedidos ped ON pd.id_pedido=ped.id_pedido
            WHERE ped.id_pedido='$idp'";

//si ya esta pagado el pedido           
    if($esta_pagado==1){
        $sql.="AND pd.id_producto IN(";       
        for($i=0;$i<$nitems;$i++){
            $sql.=$_GET["idp{$i}"];
            if($i<$nitems-1){
                $sql.=",";//concatenamos coma
            }else{
                $sql.=")";//concatenamos cierre de paréntesis
            }
        }//fin de for $i
    }

    $eje=mysql_query($sql)or die("Error al consultar si hay productos externos por devolver!!!\n\n".$sql."\n\n".mysql_error());
    $dats=mysql_fetch_row($eje);
    $num_internos=$dats[0];
    $num_externos=$dats[1];
    $pedido_pagado=$dats[2];
    $id_dev_interna=-1;
    $id_dev_externa=-1;

/*implementación Oscar 31.08.2018 para prefijo de devolución*/
    $qry=mysql_query("SELECT CONCAT('DEV',prefijo) FROM sys_sucursales WHERE id_sucursal=$user_sucursal")or die("Error al consultar el prefijo de la sucursal!!!\n\n".mysql_error());
    $pref_arr=mysql_fetch_row($qry);
    //$fol_dev='DEV'.$pref_arr[0].$idp;
    $sql_fol="SELECT
                CONCAT('{$pref_arr[0]}',
                    IF(
                        ISNULL(MAX(CAST(REPLACE(folio, '{$pref_arr[0]}', '') AS SIGNED INT))),
                        1,
                        MAX(CAST(REPLACE(folio, '{$pref_arr[0]}', '') AS SIGNED INT))+1
                    )
                ) AS folio
                FROM ec_devolucion
                WHERE REPLACE(folio, '{$pref_arr[0]}', '') REGEXP ('[0-9]')
                AND id_sucursal='{$user_sucursal}'";
    $eje_fol=mysql_query($sql_fol)or die("Error al generar el folio de la devolución!!!\n\n".mysql_error());
    $row_fol=mysql_fetch_row($eje_fol);
    $fol_dev=$row_fol[0];
/*Fin de cambio 31.08.2018*/
//insertamos la(s) cabecera(s) de devolucion
/*Implementación Oscar 14.03.2019 pzra guardadar donde se realizó la devolución*/
    $sql_cons="SELECT id_sucursal FROM sys_sucursales WHERE acceso=1";
    $eje_cons=mysql_query($sql_cons)or die("Error al consultar el tipo de sistema!!!\n\n".mysql_error());
    $res_cons=mysql_fetch_row($eje_cons);
  //  die($res_cons[0]);
/*Fin de cambio Oscar 14.03.2019*/
    for($i=0;$i<=1;$i++){//(da dos vueltas  solamente)
        //deshabiltado Oscar 2023 25 Agosto 2023 para que no duplique cabeceras de devolucion if(($i==0&&$num_internos>0||$i==1&&$num_externos>0)||$pedido_pagado==0){
        if(($i==0&&$num_internos>0||$i==1&&$num_externos>0)){//||$pedido_pagado==0
            $insD="INSERT INTO ec_devolucion(id_devolucion,id_usuario,id_sucursal,fecha,hora,id_pedido,folio,es_externo,observaciones,tipo_sistema,id_status_agrupacion)/*se agrega campo es externo/tipo/sistema*/ 
                    VALUES(NULL,'$user_id','$user_sucursal',NOW(),NOW(),'$idp','$fol_dev',";
            if($i==0){
                $insD.="'0',";
            }else if($i==1){
                $insD.="'1',";
            }
            $insD.="'','".$res_cons[0]."',-1)";
            $dev=mysql_query($insD);
           // echo $insD;return 0;
            if(!$dev){
                $error=mysql_error();
                mysql_query("ROLLBACK");
    	        die("Error al insertar encabezado de la devolución1...\n\n".$insD."\n\n".$error);
            }  
        //guardamos id(s) de la(s) cabecera(s)
            if($i==0){
                $id_dev_interna=mysql_insert_id();
            }else if($i==1){
                $id_dev_externa=mysql_insert_id();
            }
            //$id_dev=mysql_insert_id();//guardamos id de devolucion
        }
    }//fin de for $i
    $id_nvo_mov_int=-1;
    $id_nvo_mov_ext=-1;
//seleccionamos el almacen principal de la sucursal 
    $sql="SELECT id_almacen FROM ec_almacen WHERE es_almacen=1 AND id_sucursal=$user_sucursal";
    $eje=mysql_query($sql)or die("Error al consultar almacén principal!!!\n\n".$sql."\n\n".mysql_error());
    $alm=mysql_fetch_row($eje);
    $id_almacen_principal=$alm[0];//almacén principal de la sucursal

//seleccionamos el almacen externo de la sucursal 
    $sql="SELECT almacen_externo FROM sys_sucursales WHERE id_sucursal=$user_sucursal";
    $eje=mysql_query($sql)or die("Error al consultar almacén principal!!!\n\n".$sql."\n\n".mysql_error());
    $alm=mysql_fetch_row($eje);
    $id_almacen_externo=$alm[0];//almacén principal de la sucursal

//insertamos cabecera del movimiento de almacen de la devolución
    for($i=0;$i<=1;$i++){
        if($i==0&&$num_internos>0||$i==1&&$num_externos>0){
            /*$insMov="INSERT INTO ec_movimiento_almacen ( id_movimiento_almacen, id_tipo_movimiento, id_usuario, id_sucursal,
            fecha, hora, observaciones, id_pedido, id_orden_compra, lote, id_maquila, id_transferencia, id_almacen, 
            status_agrupacion, ultima_sincronizacion, ultima_actualizacion ) 
            VALUES(null,'12','$user_id','$user_sucursal',now(),now(),'DEVOLUCION $fol_dev',-1,-1,'',-1,-1,";
            if($i==0){
                $insMov.=$id_almacen_principal;
            }else if($i==1){
                $insMov.=$id_almacen_externo;
            }
            $insMov.=",-1,null,now() )";*/
            $warehouse_id = ($i==0 ? $id_almacen_principal : $id_almacen_externo );
            $insMov = "CALL spMovimientoAlmacen_inserta ( {$user_id}, 'DEVOLUCION', {$user_sucursal}, {$warehouse_id}, 12, -1, -1, -1, -1, 15, NULL )";
            $eje=mysql_query($insMov) or die("Error al insertar el encabezado de movimiento de almacén con entrada por devolución2....".$insMov.mysql_error());
        //consulta el id de moviento insertado      
            $ma_stm = mysql_query( "SELECT max( id_movimiento_almacen ) AS id_movimiento_almacen FROM ec_movimiento_almacen" ) or die( "Error al recuperar id ma insertado : " . mysql_error() );
            $id_mov = mysql_fetch_assoc( $ma_stm );//mysql_fetch_assoc( $ma_stm );
            if($i==0){
                $id_nvo_mov_int=$id_mov['id_movimiento_almacen'];//capturamos el id asignado al movimiento de devolución
            }else if($i==1){
                $id_nvo_mov_ext=$id_mov['id_movimiento_almacen'];//capturamos el id asignado al movimiento de devolución
            }
        }
    }//fin de for $i

//devolvemos todo si no esta pagado
        if($esta_pagado==0){
    /*implementacion Oscar 01.10.2019 para devolver al producto origen en caso de ser maquila*/
            /*INSERT INTO ec_movimiento_detalle ( id_movimiento_almacen_detalle, id_movimiento, 
            id_producto, cantidad, cantidad_surtida, id_pedido_detalle, id_oc_detalle, id_proveedor_producto,
            id_equivalente, sincronizar )*/
            $ins_mov_det="SELECT 
                            null,
                            IF(pd.es_externo=1,$id_nvo_mov_ext,$id_nvo_mov_int) AS movement_header_id,
                            IF(p.id_producto_ordigen IS NULL,pd.id_producto,p.id_producto_ordigen) AS product_id,
                            IF(p.id_producto_ordigen IS NULL,pd.cantidad,(pd.cantidad*p.cantidad)) AS quantity,
                            IF(p.id_producto_ordigen IS NULL,pd.cantidad,(pd.cantidad*p.cantidad)),
                            -1,
                            -1,
                            NULL,
                            '0',
                            '0'
                        FROM ec_pedidos_detalle pd
                        LEFT JOIN ec_productos_detalle p ON p.id_producto=pd.id_producto
                        WHERE pd.id_pedido=$idp";
            $eje=mysql_query($ins_mov_det);
            if(!$eje){
                $error=mysql_error();
                mysql_query("ROLLBACK");//cancelamos transacción
                die("Error al consultar el detalle de movimiento por devolución 2!!!\n\n".$error."\n\n".$ins_mov_det);
            }
            while( $dev_row = mysql_fetch_assoc($eje) ){//;$stm->fetch_assoc()
                $sql = "CALL spMovimientoAlmacenDetalle_inserta ( {$dev_row['movement_header_id']}, {$dev_row['product_id']}, {$dev_row['quantity']}, 
                    {$dev_row['quantity']}, -1, -1, NULL, 15, NULL )";
                $exc_procedure = mysql_query( $sql ) or die( "Error al llamar procedure spMovimientoAlmacenDetalle_inserta : {$sql} " . mysql_error() );
            }
        }

//Seleccionamos pedido
    $descuentos=0;
    $monto_internos=0;
    $monto_externos=0;

    for($i=0;$i<$nitems;$i++){
    	$descuentos+=$_GET["desc{$i}"];//echo "<br>descuentos:  ".$descuentos."<br>";
    //extraemos datos del detalle del pedido
        $get_product_id = $_GET["idp{$i}"];
        $get_sale_detail = $_GET["pd{$i}"];
        $get_quantity = $_GET["can{$i}"];
        $get_product_provider = $_GET["p_p{$i}"];
    	$sql1="SELECT pd.cantidad,
                        pd.id_pedido_detalle,
                        pd.es_externo,
                        /*ROUND(((pd.precio-pd.descuento)*{$_GET["can{$i}"]})-(({$_GET["can{$i}"]}*pd.precio)*(IF(pe.descuento=0,0,(pe.descuento*100/pe.subtotal))/100))) */
                        ROUND(((pd.monto-pd.descuento)/pd.cantidad)-IF(pd.descuento>0,0,(pd.precio)*(IF(pe.descuento=0,0,(pe.descuento*100/pe.subtotal))/100)),4)*{$get_quantity}
                        FROM ec_pedidos_detalle pd
                        LEFT JOIN ec_pedidos pe ON pd.id_pedido=pe.id_pedido 
                        WHERE pe.id_pedido='$idp' 
                        AND pd.id_producto='{$get_product_id}'
                        AND pd.id_pedido_detalle = '{$get_sale_detail}'";
    	//die($sql1);
        $eje1=mysql_query($sql1)or die("Error al consultar la cantidad de productos a devolver\n\n".$sql1."\n\n".mysql_error());
    	$r=mysql_fetch_row($eje1);
        //sacamos descuento por pieza
            $sql2="SELECT IF(descuento>0,(descuento/cantidad),0) FROM ec_pedidos_detalle WHERE id_pedido_detalle='$r[1]'";
            $eje2=mysql_query($sql2);
            if(!$eje2){
                $error=mysql_error();
                mysql_query("ROLLBACK");//cancelamos transacción
                die("Error al consultar el descuento por producto\n\n".$sql2."\n\n".$error);                
            }
            $des=mysql_fetch_row($eje2); 
        //actualizamos el detalle del pedido
    		$sql2="UPDATE ec_pedidos_detalle SET 
                    cantidad=(cantidad-{$get_quantity}),
                    monto=cantidad*precio,descuento=(IF(descuento=0,0,$des[0]*cantidad)),
                    modificado=1
    		  		WHERE id_pedido_detalle='{$get_sale_detail}'";//-{$_GET["can{$i}"]}
    		$eje2=mysql_query($sql2);
    		if(!$eje2){
                $error=mysql_error();
    			mysql_query("ROLLBACK");//cancelamos transacción
    			die("Error al actualizar el detalle de pedido\n\n".$sql2."\n\n".$error);
    		}

/*implementación Oscar 01.10.2019 para verificar si el producto es maquilado*/
    //comprobamos si el producto es maquilado
            $sql="SELECT id_producto_ordigen,cantidad FROM ec_productos_detalle WHERE id_producto='{$get_product_id}'"; 
            $eje_maq=mysql_query($sql)or die("Error al consultar el origen de la maquila");
            if(mysql_num_rows($eje_maq)==1){
                $r_maq=mysql_fetch_row($eje_maq);
                $get_product_id=$r_maq[0];
                $get_quantity=$r_maq[1]*$get_quantity;
            }
/*fin de cambio Oscar 01.10.2019*/
        $real_product = $get_product_id;
      //  $sql = "";

    //inserta el detalle de la devolucion
    	$ins_det="INSERT INTO ec_devolucion_detalle(id_devolucion_detalle,id_devolucion,id_producto,id_proveedor_producto,cantidad, id_pedido_detalle)
                    SELECT NULL,IF($r[2]=0,$id_dev_interna,$id_dev_externa),'{$real_product}',
                    '{$get_product_provider}','{$get_quantity}', '{$get_sale_detail}'";////VALUES(NULL,'$id_dev','{$get_product_id}','{$get_quantity}')

        $insDD=mysql_query($ins_det);
    	if(!$insDD){
            $error=mysql_error();
    		mysql_query("ROLLBACK");//cancela transacción
    		die("Error al insertar detalle de la devolución\n\n".$ins_det."\n\n".$error);
    	}
    //si la nota esta pagada insertamos el detalle del movimiento por devolución de los productos devueltos
        if($esta_pagado==1){
            /*$ins_mov_det="INSERT INTO ec_movimiento_detalle ( id_movimiento_almacen_detalle, id_movimiento, 
             id_producto, cantidad, cantidad_surtida, id_pedido_detalle, id_oc_detalle, id_proveedor_producto,
             id_equivalente, sincronizar )
            SELECT NULL,
            IF({$r[2]}=0,{$id_nvo_mov_int},{$id_nvo_mov_ext}),
            '{$get_product_id}',
            '{$get_quantity}',
            '{$get_quantity}',
            -1,
            -1,
            '{$get_product_provider}',
            '0',
            '0'";*/
            $movement_header_id = ( $r[2] == 0 ? $id_nvo_mov_int : $id_nvo_mov_ext );
            $ins_mov_det = "CALL spMovimientoAlmacenDetalle_inserta ( {$movement_header_id}, {$get_product_id}, {$get_quantity}, 
                {$get_quantity}, -1, -1, {$get_product_provider}, 15, NULL )";
            //$exc_procedure = mysql_query( $sql ) or die( "Error al mandar llamar procedure spMovimientoAlmacenDetalle_inserta : {$ins_mov_det} " . mysql_error() );
            /*echo $ins_mov_det;
            die("");*/
            $eje=mysql_query($ins_mov_det);
            if(!$eje){
                $error=mysql_error();
                mysql_query("ROLLBACK");//cancelamos transacción
                die("Error al insertar el detalle de movimiento por devolución 1!!!\n\n".$ins_mov_det."\n\n".$error);
            }
            $sql = "SELECT 
                        pv.id_pedido_validacion,
                        pv.piezas_validadas
                    FROM ec_pedidos_validacion_usuarios pv
                    LEFT JOIN ec_pedidos_detalle pd
                    ON pd.id_pedido_detalle = pv.id_pedido_detalle
                    WHERE pd.id_pedido = {$idp}
                    AND pv.id_proveedor_producto = {$get_product_provider}
                    AND pd.id_pedido_detalle = {$get_sale_detail}";
            $stm = mysql_query( $sql ) or die( "Error :  " . mysql_error() . $sql );
            $quantity = $get_quantity;

            $counter = 0;
            $validations_number = mysql_num_rows( $stm );
            while( $row_dev = mysql_fetch_assoc( $stm ) ){
                $counter ++;
                $assign_quanity = 0;
                if( $quantity > $row["piezas_validadas"] && $row["piezas_validadas"] > 0 ){
                    $assign_quanity = $row["piezas_validadas"];
                }if( $quantity < $row["piezas_validadas"] ){
                    $assign_quanity = $quantity;
                    
                }if( $quantity == $row["piezas_validadas"] ){
                    $assign_quanity = $quantity;
                    
                }if( $counter == $validations_number ){
                    $assign_quanity = $quantity;
                }
                if( $assign_quanity > 0 ){
                    $sql_upd = "UPDATE ec_pedidos_validacion_usuarios SET piezas_validadas = ( piezas_validadas - {$assign_quanity} )
                        WHERE id_pedido_validacion = {$row_dev['id_pedido_validacion']}";
//echo $sql;
                    $upd = mysql_query( $sql_upd ) or die( "Error al actualizar validacion : " . mysql_error() );
                }
            }
        }//fin de si esta pagado

    //sumamos los pagos
        if($r[2]==0){//si es producto interno
            $monto_internos+=$r[3];
        }else if($r[2]==1){//si es producto externo
            $monto_externos+=$r[3];
        }

    }//termina el FOR i

/*
    die("ok");
?>
<?php
*/
/*Oscar 2023/120/25 para total en cabecera de internos/externos*/
    if( $monto_internos > 0 ){
        $sql = "UPDATE ec_devolucion SET monto_devolucion = {$monto_internos} WHERE id_devolucion = {$id_dev_interna}";
        mysql_query( $sql ) or die( "Error al actualizar monto en cabecera de devolución interna : " . mysql_error() );
    }
    if( $monto_externos > 0 ){
        $sql = "UPDATE ec_devolucion SET monto_devolucion = {$monto_externos} WHERE id_devolucion = {$id_dev_externa}";
        mysql_query( $sql ) or die( "Error al actualizar monto en cabecera de devolución externa : " . mysql_error() );
    }
/*fin de cambio Oscar 2023/10/25*/


//insertamos el pago de la devolucion
$total_abonado=0;
/*if($pedido_pagado==1){
    for($i=0;$i<=1;$i++){
        if($i==0&&$num_internos>0||$i==1&&$num_externos>0){
            $insPD="INSERT INTO ec_devolucion_pagos(id_devolucion_pago,id_devolucion,id_tipo_pago,monto,referencia,es_externo,fecha,hora,id_cajero, id_sesion_caja )//se agrego campo es externo Oscar 09.08.2018
    		  VALUES(NULL,";
        //id de la devolucion
            if($i==0){
                $insPD.=$id_dev_interna;
            }else if($i==1){
                $insPD.=$id_dev_externa;                    
            }
            $insPD.=",1,";
        //monto de la devolucion
            if($i==0){
                $insPD.=$monto_internos.",'',0";
            }else if($i==1){
                $insPD.=$monto_externos.",'',1";           
            }
            $insPD.=", NOW(), NOW(), {$id_cajero}, {$id_sesion_caja} )";
            $insert=mysql_query($insPD);
            if(!$insert){
                $error=mysql_error();
                mysql_query("ROLLBACK");//cancelamos transacción
                die("Error al insertar el pago de la devolución\n\n".$insPD."\n\n".$error);
            }                
        }//fin de if si son validos
    }//fin de for i
}//fin de if esta pagado   
else{
    $sql="SELECT 
            SUM(IF(pp.es_externo=1,pp.monto,0))-IF(ax.devExternos IS NULL,0,ax.devExternos) as externos,
            SUM(IF(pp.es_externo=0,pp.monto,0))-IF(ax.devInternos is null,0,ax.devInternos )as internos,
            SUM(pp.monto)-IF(ax.totalDev is null,0,ax.totalDev) as total 
        FROM(
            SELECT 
                $idp as id_pedido,
                SUM(IF(dev.id_devolucion is null,0,IF(dp.es_externo=1,dp.monto,0))) as devExternos,
                SUM(IF(dev.id_devolucion is null,0,IF(dp.es_externo=0,dp.monto,0))) as devInternos,
                SUM(IF(dev.id_devolucion IS NULL,0,dp.monto)) as totalDev
                FROM ec_devolucion dev
                LEFT JOIN ec_devolucion_pagos dp ON dev.id_devolucion=dp.id_devolucion
                WHERE dev.id_pedido=$idp
            )ax
        LEFT JOIN ec_pedido_pagos pp ON pp.id_pedido=ax.id_pedido
        WHERE pp.id_pedido=$idp";
// die($sql);
    $eje=mysql_query($sql);
    if(!$eje){
        $error=mysql_error();
        mysql_query("ROLLBACK");//cancelamos transacción
        die("Error al insertar el pago de la devolución\n\n".$sql."\n\n".$error);
    }
    $datos_1=mysql_fetch_row($eje);
//insertamos las devoluciones completas
    //externa
    if($datos_1[0]>0){
        $sql="INSERT INTO ec_devolucion_pagos (id_devolucion_pago,id_devolucion,id_tipo_pago,monto,referencia,es_externo,fecha,hora,id_cajero, id_sesion_caja ) 
        VALUES(null,$id_dev_externa,1,$datos_1[0],'$datos_1[0]',1,now(),now(), {$id_cajero}, {$id_sesion_caja} )";
        $eje=mysql_query($sql);
        if(!$eje){
            $error=mysql_error();
            mysql_query("ROLLBACK");//cancelamos transacción
            die("Error al insertar el pago de la devolución externa\n\n".$sql."\n\n".$error);
        }     
    }
//interna
    if($datos_1[1]>0){
        $sql="INSERT INTO ec_devolucion_pagos (id_devolucion_pago,id_devolucion,id_tipo_pago,monto,referencia,es_externo,fecha,hora,id_cajero, id_sesion_caja ) 
        VALUES(null,$id_dev_interna,1,$datos_1[1],'$datos_1[0]',0,now(),now(), {$id_cajero}, {$id_sesion_caja} )";
        $eje=mysql_query($sql);
        if(!$eje){
            $error=mysql_error();
            mysql_query("ROLLBACK");//cancelamos transacción
            die("Error al insertar el pago de la devolución interna\n\n".$sql."\n\n".$error);
        }     
    }   
    $total_abonado=$datos_1[2];  
//actualizamos los pagos para anularlos en los cálculos
    $sql="UPDATE ec_pedido_pagos SET referencia=monto WHERE id_pedido=$idp";
    $eje=mysql_query($sql);
    if(!$eje){
        $error=mysql_error();
        mysql_query("ROLLBACK");//cancelamos transacción
        die("Error al actualizar la referencia de los pagos!!!\n\n".$sql."\n\n".$error);
    }
}//fin de si no esta pagada la nota de venta*/

//Actualizamos el monto del pedio anterior y generamos el ticket...
    $subTotal="SELECT SUM(monto),SUM(descuento) FROM ec_pedidos_detalle WHERE id_pedido='$idp'";//consultamos las sumas de los productos del pedido
    $calc=mysql_query($subTotal);
    if(!$calc){
    	mysql_query("ROLLBACK");
    	die("Error al calcular el nuevo monto del pedido!!!\n\n");
    }
    $subTotal=mysql_fetch_row($calc);    
//checamos si hay descuento
    if($subTotal[1]==0){
        $descFinal=$subTotal[0]*$porcDesc;
        $auxz=$subTotal[0];
        $subTotal[0]=$auxz;
    }else{
        $descFinal=$subTotal[1];
    }
/**/
    $extra=str_replace("*", "&", $extra);
/*Modificacion Oscar 2024-04-15 para incluir id devolucion en la url guardada*/
    $url_recarga = "../touch_desarrollo/index.php?scr=nueva-venta&s_f_c={$totalDev}{$extra}&abonado={$total_abonado}&id_dev={$id_dev_interna}~{$id_dev_externa}";
/*Fin de cambio Oscar 2024-04-15*/
    $sql="UPDATE ec_devolucion SET observaciones = '$url_recarga' WHERE id_pedido = {$idp}";
    $eje=mysql_query($sql)or die("Error al actualizar observaciones en las devoluciones!!\n\n".mysql_error()."\n\n".$sql);
/**/
//actualizamos monto del pedido y marcamos que este fue modificado
    $actPed="UPDATE ec_pedidos SET descuento='$descFinal',subtotal='$subTotal[0]',total=($subTotal[0]-descuento),modificado=1 WHERE id_pedido='$idp'";
    $actualiza=mysql_query($actPed);
    if(!$actualiza){
    	mysql_query("ROLLBACK");//cancelamos transacción
    	die("Error al actualizar cabecera de Pedido\n\n".$actPed."\n\n".mysql_error());
    }

/*implementacion Oscar 2023-12-19 para actualizar referencia de la nota de venta y a devolucion*/
        $sql = "UPDATE ec_pedidos_referencia_devolucion 
                    SET total_venta = ( total_venta - ( {$monto_externos} + {$monto_internos} ) )
                WHERE id_pedido = {$idp}";
        $reference_stm = mysql_query( $sql ) or die( "Error al actualizar la referencia de la devolucion : " . mysql_error() );
/*fin de cambio Oscar 2023-12-19*/

    if(mysql_query("COMMIT")){//autorizamos transacción
       // if($es_completa==1){
        /*imprimimos el ticket de la devolución
            if(!include('imprimeDev.php')){
    		  die("Error al generar ticket de devolución");
    	   }
        //}*/
    }else{
    	mysql_query("ROLLBACK");//cancelamos transacción
    	die("Se generó un Error al completar la transaccion!!!\n\nActualice la pantalla y vuelva a intentar");
    }


//regresamos el id de la devolución 
    echo 'ok|'.$id_dev."|".$total_abonado."|".$url_recarga."&id_dev=".$id_dev_interna."~".$id_dev_externa;
?>