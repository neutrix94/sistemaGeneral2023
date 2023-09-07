<?php


	if($tabla == 'ec_pedidos' && $no_tabla == 3)
	{

		//echo "SI $tipo $no_tabla";

		if($tipo == 1)
		{
			//print_r($datosInvisibles);
			$datosInvisibles[0][4]=1;
		}
	}

/* Implementacion Oscar 21-09-2020 para los campos de venta en linea*/
	if($tabla=="ec_productos" && $no_tabla=="0"){
	//consulta los datos del producto (precio especial)
		$sql_esp_prod = "SELECT
							precio_especial_desde, /*0*/
							precio_especial_hasta, /*1*/
							monto_precio_especial, /*2*/
							producto_nuevo_desde, /*3*/
							producto_nuevo_hasta, /*4*/
							habilitado, /*5*/
							stock_minimo, /*6*/
							imagen_principal, /*7*/
							descripcion, /*8*/
							breve_descripcion,/*9*/
							palabras_clave_busqueda, /*10*/
							metatitulo,/*11*/
							metadescripcion, /*12*/
							producto_solo_facturacion, /*13*/
							-- AF- Cambio I
							porcentaje_descuento_agrupado, /*14*/
							-- AF- Cambio F
							habilitado_magento/*15*/
				FROM ec_producto_tienda_linea
				WHERE id_producto = '$llave'";

		$eje_esp_prod = mysql_query($sql_esp_prod) or die("Error al consultar precio especial del producto : " . mysql_error());
		$r_esp_prod = mysql_fetch_row($eje_esp_prod);
		$smarty->assign("precios_especiales", $r_esp_prod);

/*Implementacion Oscar 22-09-2020 para cargar los formatos de imagenes*/
		$sql_formato_img = "SELECT
							id_formato_imagen,
							formato
				FROM ec_tipos_formatos_imagen
				WHERE 1";

		$eje_formato_img = mysql_query($sql_formato_img) or die("Error al consultar formatos de imagen : " . mysql_error());
		$arreglo_formatos = array();
		while ($r_formato_img = mysql_fetch_row($eje_formato_img)) {
			array_push($arreglo_formatos, $r_formato_img);
		}
		$smarty->assign("formatos_imagenes", $arreglo_formatos);

	}
/**/


?>
