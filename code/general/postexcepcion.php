<?php
//1. Detecta el tipo de sistema operativo para la sincronizacion (ya no se usa)
	if($tabla == 'ec_sincronizacion')
	{
		if($accion == 'actualizar')
		{

			//Detectamos el sistema operativo
			$sOp=php_uname() ;

			if(strstr($sOp, "Linux"))
				$sOp="Linux";

			if($sOp == "Linux")
			{
				}




		}
	}

//2. Implementacion Oscar 11-08-2020 para insertar productos en nuevo almacen
	if($tabla=='ec_almacen' && $no_tabla==0 && $accion=='insertar' ){
		$sql = "INSERT INTO ec_almacen_producto (id_almacen, id_producto)
					SELECT
						$llave,
						id_productos
					FROM ec_productos
					WHERE id_productos>0";
		$res=mysql_query($sql);
        if(!$res){
            mysql_query("ROLLBACK");
            Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
        }
	}

//3. Implementacion Oscar 11-08-2020 para insertar productos en almacen cuando se inserta un nuevo producto
	if( $tabla == 'ec_productos' && $no_tabla == 0 && ($accion=='insertar' || $accion == 'actualizar') ){
		if( $accion == 'insertar'){
			$sql = "INSERT INTO ec_almacen_producto (id_almacen, id_producto)
					SELECT
						id_almacen,
						$llave
					FROM ec_almacen
					WHERE id_almacen>0";
			$res=mysql_query($sql);
        	if(!$res){
        	    mysql_query("ROLLBACK");
            	Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
        	}
        }
    //implementacion Oscar 2023 para insertar el producto en la tabla de configuracion de etiquetas
   		if( $accion == 'insertar'){
			$sql = "INSERT INTO ec_productos_etiquetado_maquila (id_producto ) VALUES ( {$llave} )";
			$res=mysql_query($sql);
        	if(!$res){
        	    mysql_query("ROLLBACK");
            	Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
        	}
        }

    //3.1. Implementacion Oscar 22-09-2020 para actualizar el campo es maquilado
    	$sql = "UPDATE ec_productos SET es_maquilado = IF( '$id_tipo_producto' = '3', '1', '0') WHERE id_productos = '$llave'";
    	$res=mysql_query($sql);
        if(!$res){
        	    mysql_query("ROLLBACK");
            	Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
        }

    //3.2. Implementacion Oscar 21-09-2020 para insertar / actualizar tabla de ec_producto_venta_linea
    	if( $pe_del != '' || $pe_al != '' || $monto_esp_product != '' || $pn_del != '' || $pn_al != '' ||
    		$producto_tienda_web_habilitado != '' || $producto_tienda_web_stock_minimo ||  $nombre_img_principal != '' ||
    		$producto_tienda_web_descripcion != '' || $producto_tienda_web_descripcion_breve != '' || $producto_tienda_web_palabras_clave != '' ||
    		$producto_tienda_web_metatitulo != '' || $producto_tienda_web_metadescripcion != '' || $producto_solo_facturacion != ''){
    	//consulta si el registro ya existe y si no lo inserta
    		$sql = "SELECT id_producto FROM ec_producto_tienda_linea WHERE id_producto = '$llave' ";
    		$res=mysql_query($sql);
        	if(!$res){
        	    mysql_query("ROLLBACK");
            	Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
        	}

        	$num = mysql_num_rows($res);

        	if($num == 0){
        		$sql = "INSERT INTO ec_producto_tienda_linea (id_producto, precio_especial_desde, precio_especial_hasta,
        			monto_precio_especial, producto_nuevo_desde, producto_nuevo_hasta, habilitado, stock_minimo, imagen_principal,
        			descripcion, breve_descripcion, palabras_clave_busqueda, metatitulo, metadescripcion, producto_solo_facturacion, 
        			porcentaje_descuento_agrupado, habilitado_magento)
					VALUES(
							'$llave' ,
							IF('$pe_del' != '', '$pe_del', NULL),
							IF('$pe_al' != '', '$pe_al', NULL),
							IF('$monto_esp_product' != '', '$monto_esp_product', NULL),
							IF('$pn_del' != '', '$pn_del', NULL),
							IF('$pn_al' != '', '$pn_al', NULL),
							IF('$producto_tienda_web_habilitado' = 'on', 1, 0),
							IF('$producto_tienda_web_stock_minimo' != '', '$producto_tienda_web_stock_minimo', NULL),
							IF('$nombre_img_principal' != '', '$nombre_img_principal', NULL),
	        				IF('$producto_tienda_web_descripcion' != '', '$producto_tienda_web_descripcion', NULL),
	        				IF('$producto_tienda_web_descripcion_breve' != '', '$producto_tienda_web_descripcion_breve', NULL),
	        				IF('$producto_tienda_web_palabras_clave' != '', '$producto_tienda_web_palabras_clave', NULL),
	        				IF('$producto_tienda_web_metatitulo' != '', '$producto_tienda_web_metatitulo', NULL),
	        				IF('$producto_tienda_web_metadescripcion' != '', '$producto_tienda_web_metadescripcion', NULL),
							IF('$producto_solo_facturacion' = 'on', '1', '0'),
							'$porcentaje_descuento_agrupado',
							IF('$habilitado_en_magento' = 'on', '1', '0')
						)";
        	}else{
        		$sql = "UPDATE ec_producto_tienda_linea
        				SET
	        				precio_especial_desde = IF('$pe_del' != '', '$pe_del', NULL),
	        				precio_especial_hasta = IF('$pe_al' != '', '$pe_al', NULL),
	        				monto_precio_especial = IF('$monto_esp_product' != '', '$monto_esp_product', NULL),
	        				producto_nuevo_desde = IF('$pn_del' != '', '$pn_del', NULL),
	        				producto_nuevo_hasta = IF('$pn_al' != '', '$pn_al', NULL),
	        				habilitado = IF('$producto_tienda_web_habilitado' = 'on', 1, 0),
	        				stock_minimo = IF('$producto_tienda_web_stock_minimo' != '', '$producto_tienda_web_stock_minimo', NULL),
	        				imagen_principal = IF('$nombre_img_principal' != '', '$nombre_img_principal', NULL),
	        				descripcion = IF('$producto_tienda_web_descripcion' != '', '$producto_tienda_web_descripcion', NULL),
	        				breve_descripcion = IF('$producto_tienda_web_descripcion_breve' != '', '$producto_tienda_web_descripcion_breve', NULL),
	        				palabras_clave_busqueda = IF('$producto_tienda_web_palabras_clave' != '', '$producto_tienda_web_palabras_clave', NULL),
	        				metatitulo = IF('$producto_tienda_web_metatitulo' != '', '$producto_tienda_web_metatitulo', NULL),
	        				metadescripcion = IF('$producto_tienda_web_metadescripcion' != '', '$producto_tienda_web_metadescripcion', NULL),
							producto_solo_facturacion = IF('$producto_solo_facturacion' = 'on', '1', '0'),
							porcentaje_descuento_agrupado = '$porcentaje_descuento_agrupado',
							habilitado_magento = IF('$habilitado_en_magento' = 'on', '1', '0')
	        			WHERE id_producto = '$llave'";
        	}

        	$res=mysql_query($sql);
        	if(!$res){
        	    mysql_query("ROLLBACK");
            	Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
        	}

    	}else{
    	//elimina el registro del producto en la tabla ec_producto_venta_linea
    		$sql = "DELETE FROM ec_producto_tienda_linea WHERE id_producto = '$llave' ";
    		$res=mysql_query($sql);
        	if(!$res){
        	    mysql_query("ROLLBACK");
            	Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
        	}
    	}

	}

//4. Implementacion de Oscar 30.08.2019 para cambiar el precio de compra /descuento en la tabla de productos durante la recepcion de un orden de compra
	if($tabla=='ec_oc_recepcion' && $no_tabla==0 && ($accion=='insertar' || $accion=='actualizar') ){//die("accion:".$accion);
	//consultamos el id de proveedor de la remision
		$sql="SELECT id_proveedor FROM ec_oc_recepcion WHERE id_oc_recepcion=$llave";
		$res=mysql_query($sql);
        if(!$res){
            mysql_query("ROLLBACK");
            Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
        }
        $row=mysql_fetch_row($res);
        $id_proveedor=$row[0];
/*MODIFICACIONES OSCAR 2023 PARA CORRGIR ERROR QUE MOVIA EL NUMERO DE PIEZAS POR CAJA*/ 
   //consultamos los detalles de la remision
        $sql="SELECT 
        		/*0*/id_producto,
        		/*1*/presentacion_caja,
        		/*2*/precio_pieza,
        		/*3*/porcentaje_descuento, 
        		/*4*/id_proveedor_producto 
        	FROM ec_oc_recepcion_detalle WHERE id_oc_recepcion=$llave";
		$res=mysql_query($sql);
        if(!$res){
            mysql_query("ROLLBACK");
            Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
        }
    //actualizamos los proveedores producto
        while($row=mysql_fetch_row($res)){
        	$precio_caja=$row[1]*$row[2];
        	$sql="UPDATE ec_proveedor_producto 
        			SET precio=$precio_caja,presentacion_caja=$row[1],precio_pieza=$row[2] 
        		WHERE id_proveedor_producto = {$row[4]}";
        	$eje=mysql_query($sql);
        	if(!$eje){
            	mysql_query("ROLLBACK");
            	Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
        	}

    /*FIN DE CAMBIO*/
/*implementacion Oscar 2023/09/25 Que se actualice el precio en relacion al grid de remisiones de proveedor*/
		$sql = "UPDATE ec_productos SET precio_compra = '{$row['2']}' WHERE id_productos = {$row[0]}";
		$eje=mysql_query($sql);
		if(!$eje){
			mysql_query("ROLLBACK");
			Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
		}
/*fin de cambio Oscar 2023/09/25*/

/*FIN DE CAMBIOS OSCAR 2023*/
        //si tiene descuento
        	if($row[3]>0){
        		$sql="UPDATE ec_productos SET precio_venta_mayoreo=$row[3] WHERE id_productos=$row[0]";
        		$eje=mysql_query($sql);
        		if(!$eje){
            		mysql_query("ROLLBACK");
            		Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
        		}
        	}
        }
    /*ALERTA PARA AVISAR SI HAY PROVEEDORES PRODUCTO CON CODIGOS DE CAJA DIFERENTES*/
    	$sql_verify = "SELECT
					    ax.product_name AS producto,
					    ax.product_provider_id AS id_proveedor_producto,
					    ax.pieces_per_box AS piezas_por_caja,
					    ax.box_barcode_1 AS codigo_caja_1,
					    ax.pieces_box_barcode_cj1 AS piezas_codigo_caja_1,
					    ax.box_barcode_2 AS codigo_caja_2,
					    ax.pieces_box_barcode_cj2 AS piezas_codigo_caja_2
					FROM
					(
					    SELECT
					        p.nombre AS product_name,
					        pp.id_proveedor_producto AS product_provider_id,
					        pp.presentacion_caja AS pieces_per_box,
					       
					        pp.codigo_barras_caja_1 AS box_barcode_1,
					        
					        SUBSTRING_INDEX(
					             SUBSTRING_INDEX(pp.codigo_barras_caja_1, ' ', 2 ),
					         'CJ' , -1) AS pieces_box_barcode_cj1,
					        
					        pp.codigo_barras_caja_2 AS box_barcode_2,
					        
					        SUBSTRING_INDEX(
					             SUBSTRING_INDEX(pp.codigo_barras_caja_2, ' ', 2),
					         'CJ', -1 ) AS pieces_box_barcode_cj2
					        
					    FROM ec_proveedor_producto pp
					    LEFT JOIN ec_productos p
					    ON p.id_productos = pp.id_producto
					    WHERE pp.id_proveedor_producto > 0
					    GROUP BY pp.id_proveedor_producto
					)ax
					WHERE ( ax.pieces_per_box != ax.pieces_box_barcode_cj1 AND ax.pieces_box_barcode_cj1 != '' )
					OR ( ax.pieces_per_box != ax.pieces_box_barcode_cj2 AND ax.pieces_box_barcode_cj2 != '' )
					GROUP BY ax.product_provider_id";
		$stm = mysql_query( $sql_verify ) or die( "Error al validar los codigos de barras contra el numero de piezas de prov prod : " . mysql_error() );
		if( mysql_num_rows( $stm ) > 0 ){
			$resp = "<table>
						<tr>
							<th colspan=\"4\">Error, no se pudo guardar porque hay diferencias entre los codigos de barras y piezas por caja : </th>
						</tr>
						<tr>
							<th>Producto</th>
							<th>Piezas por caja</th>
							<th>CB CAJA 1</th>
							<th>CB CAJA 2</th>
						</tr>";
			while ( $validation = mysql_fetch_assoc( $stm ) ) {
				$resp .= "<tr>
							<td>{$validation['nombre']}</td>
							<td>{$validation['piezas_por_caja']}</td>
							<td>{$validation['codigo_caja_1']}</td>
							<td>{$validation['codigo_caja_2']}</td>
						</tr>";
			}
			die( $resp );
		}
	}
/*Fin de cambio OScar 30.08.2019*/

//5. Implementacion de Oscar 28.08.2019 para generar la clave unica del usuario para la credencial
	if($tabla == 'sys_users' && ($no_tabla == 0)){
        if($accion == 'insertar')
        {
    		$sql="UPDATE sys_users SET codigo_barras_usuario=CONCAT('".$llave."',DATE_FORMAT(NOW(), '%Y%m%d%h%i%s')),id_equivalente=id_usuario WHERE id_usuario=$llave";
    		$res=mysql_query($sql);
            if(!$res)
            {
                mysql_query("ROLLBACK");
                Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
            }
    	}
	}

//6. Inserta los movimientos de almacen despues de guardar una nota de venta
    if($tabla == 'ec_pedidos' && ($no_tabla == 0 || $no_tabla == 1))
    {
        if($accion == 'insertar')
        {
            $sql="SELECT surtir_aut_pedidos, (
			      	SELECT id_almacen
			      	FROM ec_almacen
			      	WHERE es_almacen AND id_almacen>-1 AND id_sucursal = $user_sucursal
			      	HAVING MIN(prioridad)
			      ) FROM ec_conf_pedidos";
            $res=mysql_query($sql);
            if(!$res)
            {
                mysql_query("ROLLBACK");
                Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
            }

            $row=mysql_fetch_row($res);

            if($row[0] == '1')
            {
                $sql="INSERT INTO ec_movimiento_almacen(id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, id_orden_compra, lote, id_maquila, id_transferencia, id_almacen)
                                             VALUES(2, $user_id, $user_sucursal, NOW(), NOW(), '', $llave, -1, '', -1, -1,$row[1])";


                $res=mysql_query($sql);
                if(!$res)
                {
                    mysql_query("ROLLBACK");
                    Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
                }

                $id_me=mysql_insert_id();


                //Insertamos los productos
                $sql="INSERT INTO ec_movimiento_detalle(id_movimiento, id_producto, cantidad, cantidad_surtida, id_pedido_detalle, id_oc_detalle)

                                                    SELECT
                                                    $id_me,
                                                    id_producto,
                                                    cantidad,
                                                    cantidad,
                                                    id_pedido_detalle,
                                                    -1
                                                    FROM ec_pedidos_detalle
                                                    WHERE id_pedido='$llave'
                                                 ";

                $res=mysql_query($sql);
                if(!$res)
                {
                    mysql_query("ROLLBACK");
                    Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
                }

                $sql="UPDATE ec_pedidos SET id_estatus=5 WHERE id_pedido='$llave'";

                $res=mysql_query($sql);
                if(!$res)
                {
                    mysql_query("ROLLBACK");
                    Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
                }

            }
        }
    }

//7. Actualiza ultima_actualizacion despues de modificar precios de venta de productos
	if($tabla == 'ec_precios')
    {
		if($accion == 'actualizar')
		{
			$sql="UPDATE ec_precios SET ultima_actualizacion=NOW() WHERE id_precio=$llave";
			mysql_query($sql) or die("Error en: $sql");
		}
	}

//8. inserta movimmientos de almacen en devolucion de transferncia (ya no se usa)
	if($tabla == 'ec_devolucion_transferencia')
	{

		if($accion == 'insertar')
		{

			$sql="SELECT prefijo FROM sys_sucursales WHERE id_sucursal=$user_sucursal";

			$res=mysql_query($sql);
			if(!$res)
			{
				mysql_query("ROLLBACK");
				Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
			}
			$row=mysql_fetch_row($res);


			$folio="DT".$row[0].date('Ymd').$llave;

			$sql="UPDATE ec_devolucion_transferencia SET folio='$folio' WHERE id_devolucion_transferencia='$llave'";
			$res=mysql_query($sql);
			if(!$res)
			{
				mysql_query("ROLLBACK");
				Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
			}


			//Actualizamos el inventario


			//Salida del almacen y sucursal

			$sql="	INSERT INTO ec_movimiento_almacen
					(
						SELECT
						null,
						6,
						$user_id,
						t.id_sucursal_destino,
						NOW(),
						NOW(),
						CONCAT('SALIDA POR DEVOLUCION DE TRANSFERENCIA ', dt.folio),
						-1,
						-1,
						'',
						-1,
						-1,
						t.id_almacen_destino,
						NULL,
						'0000-00-00 00:00:00',
						NOW()
						FROM ec_devolucion_transferencia dt
						JOIN ec_transferencias t ON dt.id_transferencia = t.id_transferencia
						WHERE id_devolucion_transferencia=$llave
					)";

			$res=mysql_query($sql);
			if(!$res)
			{
				mysql_query("ROLLBACK");
				Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
			}

			$id_mov=mysql_insert_id();

			//Insertamos el detalle
			$sql="	INSERT INTO ec_movimiento_detalle
					(
						SELECT
						null,
						'$id_mov',
						id_producto,
						cantidad,
						cantidad,
						-1,
						-1
						FROM ec_transferencia_producto_dev
						WHERE id_transferencia = $llave
					)";

			$res=mysql_query($sql);
			if(!$res)
			{
				mysql_query("ROLLBACK");
				Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
			}


			$id_al=0;

			//Buscamos el primer almacen de no ventas
			$sql="	SELECT
					id_almacen
					FROM ec_almacen
					WHERE id_sucursal=$id_sucursal_destino
					AND es_almacen=0
					ORDER BY prioridad";

			$res=mysql_query($sql);
			if(!$res)
			{
				mysql_query("ROLLBACK");
				Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
			}
			if(mysql_num_rows($res) > 0)
			{
				$row=mysql_fetch_row($res);
				$id_al=$row[0];
			}
			//Buscamos el almacen de origen
			else
			{
				$sql="	SELECT
						id_almacen_origen
						FROM ec_transferencias
						WHERE id_transferencia=$id_transferencia";
				$res=mysql_query($sql);
				if(!$res)
				{
					mysql_query("ROLLBACK");
					Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
				}
				$row=mysql_fetch_row($res);
				$id_al=$row[0];

			}

			//Insertamos el movimiento de almacen destino

			$sql="	INSERT INTO ec_movimiento_almacen
					(
						SELECT
						null,
						5,
						$user_id,
						t.id_sucursal_origen,
						NOW(),
						NOW(),
						CONCAT('ENTRADA POR DEVOLUCION DE TRANSFERENCIA ', dt.folio),
						-1,
						-1,
						'',
						-1,
						-1,
						$id_al,
						NULL,
						'0000-00-00',
						NOW()
						FROM ec_devolucion_transferencia dt
						JOIN ec_transferencias t ON dt.id_transferencia = t.id_transferencia
						WHERE id_devolucion_transferencia=$llave
					)";

			$res=mysql_query($sql);
			if(!$res)
			{
				mysql_query("ROLLBACK");
				Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
			}

			$id_mov=mysql_insert_id();


			//Insertamos el detalle
			$sql="	INSERT INTO ec_movimiento_detalle
					(
						SELECT
						null,
						$id_mov,
						id_producto,
						cantidad,
						cantidad,
						-1,
						-1
						FROM ec_transferencia_producto_dev
						WHERE id_transferencia = $llave
					)";

			$res=mysql_query($sql);
			if(!$res)
			{
				mysql_query("ROLLBACK");
				Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
			}

		}
	}


//9. Actualiza el folio de la trnasferencia y hace los movimientos de almacen de la transferencia (ya no se usa)
	if($tabla == 'ec_transferencias')
	{

		if($accion == 'actualizar' || $accion == 'insertar')
		{
			$sql="UPDATE ec_transferencias SET ultima_actualizacion=DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s') WHERE id_transferencia=$llave";
			$res=mysql_query($sql);
			if(!$res)
			{
				mysql_query("ROLLBACK");
				Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
			}
		}


		if($accion == 'insertar')
		{
			$sql="SELECT prefijo FROM sys_sucursales WHERE id_sucursal=$user_sucursal";

			$res=mysql_query($sql);
			if(!$res)
			{
				mysql_query("ROLLBACK");
				Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
			}
			$row=mysql_fetch_row($res);


			$folio="TR".$row[0].date('Ymd').$llave;

			$sql="UPDATE ec_transferencias SET folio='$folio' WHERE id_transferencia='$llave'";
			$res=mysql_query($sql);
			if(!$res)
			{
				mysql_query("ROLLBACK");
				Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
			}


			if($id_tipo == '5')
			{


				//Actualizamos el detalle

				$sql="UPDATE ec_transferencia_productos SET cantidad_salida=cantidad, cantidad_salida_pres=cantidad_presentacion WHERE id_transferencia=$llave";

				$res=mysql_query($sql);
				if(!$res)
				{
					mysql_query("ROLLBACK");
					Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
				}


		//Insertamos el movimiento de salida de inventario
				$sql="INSERT INTO ec_movimiento_almacen(id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, id_orden_compra, lote, id_maquila, id_transferencia, id_almacen)
												 VALUES(6, $user_id, $id_sucursal_origen, NOW(), NOW(), 'SALIDA DE TRANSFERENCIA', -1, -1, '', -1, $llave, $id_almacen_origen)";


				$res=mysql_query($sql);
				if(!$res)
				{
					mysql_query("ROLLBACK");
					Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
				}

				$id_ms=mysql_insert_id();

				//Insertamos los productos
				$sql="INSERT INTO ec_movimiento_detalle(id_movimiento, id_producto, cantidad, cantidad_surtida, id_pedido_detalle, id_oc_detalle)

											 	SELECT
											 	$id_ms,
											 	id_producto_or,
											 	cantidad_salida,
											 	cantidad_salida,
											 	-1,
											 	-1
											 	FROM ec_transferencia_productos
											 	WHERE id_transferencia='$llave'
											 ";
											 die($sql);

				$res=mysql_query($sql);
				if(!$res)
				{
					mysql_query("ROLLBACK");
					Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
				}


				//Actualizamos el estatus de la transferencia

				$sql="UPDATE ec_transferencias SET id_estado=3 WHERE id_transferencia=$llave";

				$res=mysql_query($sql);
				if(!$res)
				{
					mysql_query("ROLLBACK");
					Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
				}



			}



		}

		if($accion == 'actualizar' && $no_tabla == 1){
	//Actualizamos el estatus de la transferencia
			$sql="UPDATE ec_transferencias SET id_estado=3 WHERE id_transferencia=$llave";

			$res=mysql_query($sql);
			if(!$res){
				mysql_query("ROLLBACK");
				Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
			}else{
				mysql_query("COMMIT");
				//include('../especiales/sincronizacion/sincronizaTransferencia.php');
				if(subeTransfer($llave)){
					//echo 'ok';
				}
			}
		}


		if($accion == 'actualizar' && ($no_tabla == 2 || $no_tabla == 3)){
	//Buscamos si existe algun caso de resolucion
			$sql="	SELECT
					COUNT(1)
					FROM ec_transferencia_productos
					WHERE id_transferencia=$llave
					AND cantidad_salida <> cantidad_entrada";


			$res=mysql_query($sql);
			if(!$res)
			{
				mysql_query("ROLLBACK");
				Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
			}

			$row=mysql_fetch_row($res);

			if($row[0] <= 0){
				$sql="UPDATE ec_transferencias SET id_estado=6 WHERE id_transferencia=$llave";
				$res=mysql_query($sql);
				if(!$res){
					mysql_query("ROLLBACK");
					Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
				}else{
					mysql_query("COMMIT");//guardamos la transaccion
					//$sql1="SELECT id_global from ec_transferencias  where id_transferencia=$llave";
					//$ejeAux=mysql_query($sql1) or die(mysql_error().'<br>'.$sql1);
					//$idGT=mysql_fetch_row($ejeAux);
			//actualizamos sincronizacion en linea
						//if (include('../especiales/sincronizacion/sincronizaTransferencia.php')){
							//echo 'id_transfer global: '.$idGT[0];
						//	menu(2,$idGT[0]);
							//actualizaTransLinea($idGT[0]);
						//}else{
							//Muestraerror($smarty, "", "3", mysql_error(), $sql1, "contenido.php");
						//}
				}
			}else{//actualizamos a resolucion
		//Actualizamos el estatus de la transferencia
				$sql="UPDATE ec_transferencias SET id_estado=5 WHERE id_transferencia=$llave";
				$res=mysql_query($sql);
				if(!$res){
					mysql_query("ROLLBACK");
					Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
				}

			}//termina else

		}
	}


//10. Implementacion Oscar 13.09.2019 para actualizar el detalle de recepeciones y ordenes de compra cuando tiene el precio en 0
	if($tabla == "ec_productos")
	{

		if($campos2[4][10] == '1')
		{
			$campos2[6][11]="barra_tres";
			$campos2[6][8]=1;
		}

		if($campos2[5][10] == '1')
		{
			$campos2[7][11]="barra_tres";
			$campos2[7][8]=1;
		}

		$sql_per_esp="SELECT IF(ver=1 OR modificar=1,1,0) FROM sys_permisos WHERE id_perfil=$perfil_usuario AND id_menu=194";
		$eje=mysql_query($sql_per_esp);
		if(!$eje){
			mysql_query("ROLLBACK");
			Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
		}
		$r_perm=mysql_fetch_row($eje);

		if($r_perm[0]==1){
			$sql="SELECT id_proveedor,precio_pieza,presentacion_caja FROM ec_proveedor_producto WHERE id_producto=$llave";
			$eje_1=mysql_fetch_row($sql);
			while($r_1=mysql_fetch_row($eje_1)){
				$sql=" UPDATE ec_oc_recepcion_detalle rd
    				INNER JOIN ec_oc_recepcion r ON rd.id_oc_recepcion=r.id_oc_recepcion
    				SET rd.precio_pieza=$r_1[1],rd.monto=(rd.piezas_recibidas*$r[1])
    				WHERE r.id_proveedor=$r_1[0]
    				AND rd.id_producto=$llave
    				AND rd.precio_pieza=0;";
				$eje_2=mysql_query($sql);
				if(!$eje_2){
					mysql_query("ROLLBACK");
					Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
				}
			}//fin de while
		}

	/*Fin de cambio Oscar 13.09.2019*/
	}

//11. Consulta si la sucursal es multifacturacion (ya no se usa)
	if($tabla == 'ec_pedidos' && $no_tabla == 3)
	{
		if($tipo == 1)
		{

			$campos[1][11]="barra_tres";
			$campos[1][8]=1;
			$campos[23][8]=1;


			$campos[3][11]="barra_tres";
			$campos[3][8]=1;
			$campos[4][11]="barra_tres";
			$campos[4][8]=1;


			$campos[5][10]=date('Y-m-d');


			$sql="SELECT multifacturacion, id_razon_social FROM sys_sucursales WHERE id_sucursal=".$sucursal_id;

			$res=mysql_query($sql);
            if(!$res)
            {
                mysql_query("ROLLBACK");
                Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
            }

            $row=mysql_fetch_row($res);
            if($row[0] == 1)
            {

            }
            else
             {

                 $campos[4][8]=0;
                 $campos[4][11]="barra";
                 $campos[4][10]=$row[1];
             }

		}
	}

//12. Actualiza cuentas por pagar (ya no se usa)
	if($tabla == 'ec_cuentas_por_pagar')
	{
		if($accion == 'actualizar')
		{
			//buscamos el total abonado
			$sql="SELECT
			      IF(SUM(monto) IS NULL, 0, SUM(monto)),
			      MAX(fecha)
			      FROM ec_oc_pagos
			      WHERE id_oc=$id_oc";
			$res=mysql_query($sql);
			if(!$res)
			{
				mysql_query("ROLLBACK");
				Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
			}

			$row=mysql_fetch_row($res);
			$abono=$row[0];
			$fecmax=$row[1];

			$sql="UPDATE ec_cuentas_por_pagar
			      SET
			      abonado=$abono,
			      fecha_ultimo_pago='$fecmax'
			      WHERE id_cxp=$llave";

			if(!mysql_query($sql))
			{
				mysql_query("ROLLBACK");
				Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
			}

			$campos[3][10]=$abono;
		}
	}

//13. Actualiza cuentas por cobrar (ya no se usa)
	if($tabla == 'ec_cuentas_por_cobrar')
	{
		if($accion == 'actualizar')
		{
			//buscamos el total abonado
			$sql="SELECT
			      IF(SUM(monto) IS NULL, 0, SUM(monto)),
			      MAX(fecha)
			      FROM ec_pedido_pagos
			      WHERE id_pedido=$id_pedido";
			$res=mysql_query($sql);
			if(!$res)
			{
				mysql_query("ROLLBACK");
				Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
			}

			$row=mysql_fetch_row($res);
			$abono=$row[0];
			$fecmax=$row[1];

			$sql="UPDATE ec_cuentas_por_cobrar
			      SET
			      abonado=$abono,
			      fecha_ultimo_cobro='$fecmax'
			      WHERE id_cxc=$llave";

			if(!mysql_query($sql))
			{
				mysql_query("ROLLBACK");
				Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
			}

			$campos[3][10]=$abono;
		}
	}

//14. Actualiza los códigos de barras de acuerdo al proveedor-producto ( Oscar 2022 )
	if ( $tabla == 'ec_productos' && ( $accion == 'actualizar' || $accion == 'insertar') ){
		$sql = "UPDATE ec_proveedor_producto pp
				LEFT JOIN ec_productos p ON pp.id_producto = p.id_productos SET 
				pp.codigo_barras_pieza_1 = 
					IF( pp.codigo_barras_pieza_1 = '' OR pp.codigo_barras_pieza_1 IS NULL,
					CONCAT(
						IF( pp.id_proveedor_producto <= 9,
							CONCAT( '0000', pp.id_proveedor_producto ),
							IF( pp.id_proveedor_producto <= 99,
								CONCAT( '000', pp.id_proveedor_producto ),
								IF( pp.id_proveedor_producto <= 999,
									CONCAT( '00', pp.id_proveedor_producto ),
									IF( pp.id_proveedor_producto <= 9999,
										CONCAT( '0', pp.id_proveedor_producto ),
										pp.id_proveedor_producto
									)
								)
							)
						),  
						' ', 
						p.orden_lista 
					), 
					pp.codigo_barras_pieza_1 ),
				pp.codigo_barras_presentacion_cluces_1 = 
					IF( (pp.codigo_barras_presentacion_cluces_1 = '' OR pp.codigo_barras_presentacion_cluces_1 IS NULL ) /*AND pp.solo_pieza = 0*/,
						IF( pp.piezas_presentacion_cluces > 1,
							CONCAT( IF( pp.id_proveedor_producto <= 9,
									CONCAT( '0000', pp.id_proveedor_producto ),
									IF( pp.id_proveedor_producto <= 99,
										CONCAT( '000', pp.id_proveedor_producto ),
										IF( pp.id_proveedor_producto <= 999,
											CONCAT( '00', pp.id_proveedor_producto ),
											IF( pp.id_proveedor_producto <= 9999,
												CONCAT( '0', pp.id_proveedor_producto ),
												pp.id_proveedor_producto
											)
										)
									)
								), 

								' PQ', pp.piezas_presentacion_cluces, ' ', p.orden_lista ),
							''
						), 
						IF(/* pp.solo_pieza = 0 AND*/ pp.piezas_presentacion_cluces > 0 AND pp.piezas_presentacion_cluces != '', pp.codigo_barras_presentacion_cluces_1, '') 
					),
				pp.codigo_barras_caja_1 = 
					IF( (pp.codigo_barras_caja_1 = '' OR pp.codigo_barras_caja_1 IS NULL)/*AND pp.solo_pieza = 0*/,
						IF(	pp.presentacion_caja > 0,
							CONCAT(  IF( pp.id_proveedor_producto <= 9,
									CONCAT( '0000', pp.id_proveedor_producto ),
									IF( pp.id_proveedor_producto <= 99,
										CONCAT( '000', pp.id_proveedor_producto ),
										IF( pp.id_proveedor_producto <= 999,
											CONCAT( '00', pp.id_proveedor_producto ),
											IF( pp.id_proveedor_producto <= 9999,
												CONCAT( '0', pp.id_proveedor_producto ),
												pp.id_proveedor_producto
											)
										)
									)
								),  
									' CJ', pp.presentacion_caja, ' ', p.orden_lista),
							''
						),
						IF(/*pp.solo_pieza = 0 AND*/ pp.presentacion_caja > 0 AND pp.presentacion_caja != '', pp.codigo_barras_caja_1, '' )
					)
				WHERE pp.id_producto = '{$llave}'";
		//die('here : ' . $sql);
		if(!mysql_query($sql)){
			mysql_query("ROLLBACK");
			Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
		}
	}

/*15. Actualiza prefijo de etiquetas de proveedores producto ( Oscar 2022 )
	if($tabla == 'sys_configuracion_sistema' && $accion == 'actualizar'){
		$sql = "UPDATE ec_proveedor_producto 
					SET prefijo_codigos_unicos = (SELECT prefijo_codigos_unicos FROM sys_configuracion_sistema  WHERE id_configuracion_sistema = 1),
					contador_cajas = 0,
					contador_paquetes = 0
				WHERE 1";
    	if(!mysql_query($sql)){
			mysql_query("ROLLBACK");
			Muestraerror($smarty, "", "3", mysql_error(), "Error al actualizar los prefijos de proveedor producto : {$sql}", "contenido.php");
		}
    }
*/
	/*
Código deshabiltado por Oscar 30.08.2019 porque si se deja duplicaría movimientos de almacen

	if($tabla == 'ec_ordenes_compra' && $no_tabla == 1)
    {
        if($accion == 'insertar')
        {
        	/*fin de cambio Oscar 08.11.2018*
            $sql="	SELECT
					sutir_oc_aut,
					(
			      		SELECT
						id_almacen
				      	FROM ec_almacen
				      	WHERE es_almacen=1
						AND id_almacen > -1
						AND id_sucursal = $user_sucursal

					)
					FROM ec_conf_oc";
            $res=mysql_query($sql);
            if(!$res)
            {
                mysql_query("ROLLBACK");
                Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
            }

            $row=mysql_fetch_row($res);


			if($row[1] == '')
				Muestraerror($smarty, "", "SN", "No hay un almac&eacute;n primo configurado para esta sucursal", "NA", "postexcepcion.php");

            if($row[0] == '1')
            {
                $sql="INSERT INTO ec_movimiento_almacen(id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, id_orden_compra, lote, id_maquila, id_transferencia, id_almacen)
                                             VALUES(1, $user_id, $user_sucursal, NOW(), NOW(), '', -1, $llave, '', -1, -1, $row[1])";


                $res=mysql_query($sql);
                if(!$res)
                {
                    mysql_query("ROLLBACK");
                    Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
                }

                $id_me=mysql_insert_id();


                //Insertamos los productos
                $sql="INSERT INTO ec_movimiento_detalle(id_movimiento, id_producto, cantidad, cantidad_surtida, id_pedido_detalle, id_oc_detalle)

                                                    SELECT
                                                    $id_me,
                                                    id_producto,
                                                    cantidad,
                                                    cantidad,
                                                    -1,
                                                    id_oc_detalle
                                                    FROM ec_oc_detalle
                                                    WHERE id_orden_compra='$llave'
                                                 ";

                $res=mysql_query($sql);
                if(!$res)
                {
                    mysql_query("ROLLBACK");
                    Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
                }

                $sql="UPDATE ec_ordenes_compra SET id_estatus_oc=4 WHERE id_orden_compra='$llave'";

                $res=mysql_query($sql);
                if(!$res)
                {
                    mysql_query("ROLLBACK");
                    Muestraerror($smarty, "", "3", mysql_error(), $sql, "contenido.php");
                }

            }
        }
    }*/

?>
