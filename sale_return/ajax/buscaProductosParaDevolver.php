<?php
	include('../../conectMin.php');
	extract($_GET);
	if($tipo=="todos"){
		$sql="SELECT pd.id_producto,/*0*/
				p.orden_lista,/*1*/
				CONCAT( p.nombre, '<br>Clave Prov : <b>', pp.clave_proveedor, '</b>' ),/*2*/
				pd.precio,/*3*/
				IF(pd.descuento=0,pd.precio,(pd.precio-(pd.descuento/pd.cantidad))) AS precio,/*4*/
				IF(pe.descuento=0,0,(pe.descuento*100/pe.subtotal)) AS descuento_porc,/*5*/
				p.es_maquilado,/*6*/
				pd.descuento,/*7*/
				(pd.precio-(pd.descuento/pd.cantidad)) AS precioDescuento,/*8*/
				pd.descuento/pd.cantidad AS decuentoProd,/*9*/
				pd.cantidad,/*10*/
				IF( p.es_maquilado = 0, 
                  SUM( pvu.piezas_validadas ),
                  (SELECT
                      ROUND( ( SUM( pvu.piezas_validadas ) * 1 ) / cantidad )
                    FROM ec_productos_detalle
                    WHERE id_producto = p.id_productos
                  )
                ) AS validated_pieces,/*11*/
				pvu.id_proveedor_producto,/*12*/
				pd.id_pedido_detalle/*13*/
		FROM ec_pedidos_detalle pd
		LEFT JOIN ec_pedidos_validacion_usuarios pvu
		ON pvu.id_pedido_detalle = pd.id_pedido_detalle
		JOIN ec_pedidos pe ON pe.id_pedido=pd.id_pedido
		JOIN ec_productos p ON p.id_productos=pd.id_producto
		LEFT JOIN ec_proveedor_producto pp
		ON pp.id_proveedor_producto = pvu.id_proveedor_producto
		WHERE pe.id_pedido='$id_pedido'
		/*AND p.orden_lista='$orden_lista'*/
		AND pd.cantidad>0
		AND pvu.piezas_validadas > 0/*modificacion Oscar 2023 para que no salgan productos en 0 en devolucion proveedor producto*/
		GROUP BY pvu.id_pedido_detalle, pvu.id_proveedor_producto";/*modificacion Oscar 2023 para separar por proveedor producto*/

		$eje=mysql_query($sql);
		if(!$eje){
			die("Error\n\n".$sql."\n\n".mysql_error());
		}
		echo 'exito~';
		while($rw=mysql_fetch_row($eje)){
		//sacamos descuento correspondiente
			$descuento=round($rw[4]*($rw[5]/100)*$rw[11],2);
			$montoDesc=round(($rw[4]*$rw[11])-$descuento,2);
			if($rw[7]>0){
				$descuento=round($rw[9]*$rw[11],2);
				$montoDesc=round($rw[8]*$rw[11],2);
				$rw[3]=round($rw[8],2);
			}
			echo $rw[0]."|".$rw[1]."|".$rw[2]."|".$rw[3]."|".$descuento."|".$montoDesc."|".$rw[6]."|".$rw[5]."|".$rw[11]."|".$rw[12]."|".$rw[13]."~";
		}//fin de while
	}else{
	//$orden_lista,$id_pedido,$cantidad;
		$sql="SELECT pd.id_producto,/*0*/
				p.orden_lista,/*1*/
				CONCAT( p.nombre, '<br>Clave Prov : <b>', pp.clave_proveedor, '</b>' ),/*2*/
				pd.precio,/*3*/
				IF(pd.descuento=0,pd.precio,(pd.precio-(pd.descuento/pd.cantidad))) AS precio,/*4*/
				IF(pe.descuento=0,0,(pe.descuento*100/pe.subtotal)) AS descuento_porc,/*5*/
				p.es_maquilado,/*6*/
				pd.descuento,/*7*/
				(pd.precio-(pd.descuento/pd.cantidad)) AS precioDescuento,/*8*/
				pd.descuento/pd.cantidad AS decuentoProd,/*9*/
				'', /*10*/
				IF( p.es_maquilado = 0, 
                  SUM( pvu.piezas_validadas ),
                  (SELECT
                      ROUND( ( SUM( pvu.piezas_validadas ) * 1 ) / cantidad )
                    FROM ec_productos_detalle
                    WHERE id_producto = p.id_productos
                  )
                ) AS validated_pieces, /*11*/
				pvu.id_proveedor_producto,/*12*/
				pd.id_pedido_detalle,/*13*/
				p.es_ultimas_piezas/*14 Oscar 2023 Excluir de la logica de busqueda por escaneo los productos con el indicador de ultimas piezas ( que siempre salga como resultados de busqueda )*/
		FROM ec_pedidos_detalle pd
		LEFT JOIN ec_pedidos_validacion_usuarios pvu
		ON pvu.id_pedido_detalle = pd.id_pedido_detalle
		JOIN ec_pedidos pe ON pe.id_pedido=pd.id_pedido
		JOIN ec_productos p ON p.id_productos=pd.id_producto
		LEFT JOIN ec_proveedor_producto pp
		ON pp.id_proveedor_producto = pvu.id_proveedor_producto
		WHERE pe.id_pedido='$id_pedido'
		AND p.orden_lista='$orden_lista'
		AND pd.cantidad>0
		AND pp.id_proveedor_producto = {$product_provider}
		AND pd.id_pedido_detalle = {$_GET['id_pedido_detalle']}
		GROUP BY pd.id_pedido_detalle";//implementados por Oscar 28-12-2017
		
		$eje=mysql_query($sql);
		if(!$eje){
			die("Error\n\n".mysql_error()."\n\n".$sql);
		}
		$rw=mysql_fetch_row($eje);
	//sacamos descuento correspondiente
		$descuento=round($rw[4]*($rw[5]/100)*$cantidad,2);
		$montoDesc=round(($rw[4]*$cantidad)-$descuento,2);
		if($rw[7]>0){
			$descuento=round($rw[9]*$cantidad,2);
			$montoDesc=round($rw[8]*$cantidad,2);
			$rw[3]=round($rw[8],2);
		}
/*Oscar 2023 Excluir de la logica de busqueda por escaneo los productos con el indicador de ultimas piezas ( que siempre salga como resultados de busqueda )*/
		if( $rw[14] == 1 ){
			die( 'is_last_pieces|' );
		}
/*fin de cambio Oscar 2023*/		
	//id_producto/ordenLista/cantidadDev/
	//////confir//id_product//ordn_lsta//nombre//prec_orig//descuento_corr//mont_con_desc//maquila//%_desc
		echo 'exito|'.$rw[0]."|".$rw[1]."|".$rw[2]."|".$rw[3]."|".$descuento."|".$montoDesc."|".$rw[6]."|".$rw[5]."|".$rw[12]."|".$rw[13];
	//echo "|\n\ndescuento:".$descuento;
	}//fin de else
?>