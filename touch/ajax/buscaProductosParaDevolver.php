<?php
	include('../../conectMin.php');
	extract($_GET);
	if($tipo=="todos"){
		$sql="SELECT pd.id_producto,/*0*/
				p.orden_lista,/*1*/
				p.nombre,/*2*/
				pd.precio,/*3*/
				IF(pd.descuento=0,pd.precio,(pd.precio-(pd.descuento/pd.cantidad))) AS precio,/*4*/
				IF(pe.descuento=0,0,(pe.descuento*100/pe.subtotal)) AS descuento_porc,/*5*/
				p.es_maquilado,/*6*/
				pd.descuento,/*7*/
				(pd.precio-(pd.descuento/pd.cantidad)) AS precioDescuento,/*8*/
				pd.descuento/pd.cantidad AS decuentoProd,/*9*/
				pd.cantidad/*10*/
		FROM ec_pedidos_detalle pd
		JOIN ec_pedidos pe ON pe.id_pedido=pd.id_pedido
		JOIN ec_productos p ON p.id_productos=pd.id_producto
		WHERE pe.id_pedido='$id_pedido'
		/*AND p.orden_lista='$orden_lista'*/
		AND pd.cantidad>0";//implementados por Oscar 28-12-2017

		$eje=mysql_query($sql);
		if(!$eje){
			die("Error\n\n".$sql."\n\n".mysql_error());
		}
		echo 'exito~';
		while($rw=mysql_fetch_row($eje)){
		//sacamos descuento correspondiente
			$descuento=round($rw[4]*($rw[5]/100)*$rw[10],2);
			$montoDesc=round(($rw[4]*$rw[10])-$descuento,2);
			if($rw[7]>0){
				$descuento=round($rw[9]*$rw[10],2);
				$montoDesc=round($rw[8]*$rw[10],2);
				$rw[3]=round($rw[8],2);
			}
			echo $rw[0]."|".$rw[1]."|".$rw[2]."|".$rw[3]."|".$descuento."|".$montoDesc."|".$rw[6]."|".$rw[5]."|".$rw[10]."~";
		}//fin de while
	}else{
	//$orden_lista,$id_pedido,$cantidad;
		$sql="SELECT pd.id_producto,/*0*/
				p.orden_lista,/*1*/
				p.nombre,/*2*/
				pd.precio,/*3*/
				IF(pd.descuento=0,pd.precio,(pd.precio-(pd.descuento/pd.cantidad))) AS precio,/*4*/
				IF(pe.descuento=0,0,(pe.descuento*100/pe.subtotal)) AS descuento_porc,/*5*/
				p.es_maquilado,/*6*/
				pd.descuento,/*7*/
				(pd.precio-(pd.descuento/pd.cantidad)) AS precioDescuento,/*8*/
				pd.descuento/pd.cantidad AS decuentoProd/*9*/
		FROM ec_pedidos_detalle pd
		JOIN ec_pedidos pe ON pe.id_pedido=pd.id_pedido
		JOIN ec_productos p ON p.id_productos=pd.id_producto
		WHERE pe.id_pedido='$id_pedido'
		AND p.orden_lista='$orden_lista'
		AND pd.cantidad>0";//implementados por Oscar 28-12-2017
		
		$eje=mysql_query($sql);
		if(!$eje){
			die("Error\n\n".$sql."\n\n".mysql_error());
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
	//id_producto/ordenLista/cantidadDev/
	//////confir//id_product//ordn_lsta//nombre//prec_orig//descuento_corr//mont_con_desc//maquila//%_desc
		echo 'exito|'.$rw[0]."|".$rw[1]."|".$rw[2]."|".$rw[3]."|".$descuento."|".$montoDesc."|".$rw[6]."|".$rw[5];
	//echo "|\n\ndescuento:".$descuento;
	}//fin de else
?>