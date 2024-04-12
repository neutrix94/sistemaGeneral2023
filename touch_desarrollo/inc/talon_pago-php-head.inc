<?php
	#header("Content-Type: text/plain;charset=utf-8");
	//die('here');
	define('FPDF_FONTPATH','../include/fpdf153/font/');
	
	include("../include/fpdf153/fpdf.php");
/*implementación Oscar 25.01.2019 para sacar rutas de tickets*/
	$archivo_path = "../conexion_inicial.txt";
	$carpeta_path = "";
	if(file_exists($archivo_path)){
		$file = fopen($archivo_path,"r");
		$line=fgets($file);
		fclose($file);
	    $config=explode("<>",$line);
	    $tmp=explode("~",$config[2]);
	    $ruta_or=$tmp[0];
	    $ruta_des=$tmp[1];
	    $tmp_=explode("~",$config[0]);
		$carpeta_path = base64_decode( $tmp_[1] );
	}else{
		die("No hay archivo de configuración!!!");
	}
/*Fin de cambio Oscar 25.01.2018*/
    
	$devolucion = array();
    
    if(!isset($_GET["noImp"])){
        $_GET["noImp"]=1;
    }
	//if( isset( $_GET['id_pedido_original'] ) ){
//consulta si tiene devolucion pendientes de pasar por la pantalla de cobros
		$sql = "SELECT
					d.folio AS folio_devolucion,
					p.folio_nv AS folio_venta_original,
					p.total AS total_actual,
					CONCAT( u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno ) AS vendedor
				FROM ec_devolucion d
				LEFT JOIN ec_devolucion_detalle dd
				ON dd.id_devolucion = d.id_devolucion
				LEFT JOIN ec_pedidos p 
				ON p.id_pedido = d.id_pedido
				LEFT JOIN sys_users u 
				ON u.id_usuario = p.id_usuario
				WHERE d.id_pedido = '{$_GET['id_pedido_original']}'
				AND d.id_cajero = 0
				AND d.id_sesion_caja = 0
				GROUP BY d.id_pedido";
		$stm = mysql_query( $sql ) or die( "Error al consultar si hay devolucion pendiente : " . mysql_error() );
		if( mysql_num_rows( $stm ) <= 0 ){
			$sql = "SELECT
						d.folio AS folio_devolucion,
						p.folio_nv AS folio_venta_original,
						CONCAT( u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno ) AS vendedor
					FROM ec_devolucion d
					LEFT JOIN ec_devolucion_detalle dd
					ON dd.id_devolucion = d.id_devolucion
					LEFT JOIN ec_pedidos p 
					ON p.id_pedido = d.id_pedido
					LEFT JOIN sys_users u 
					ON u.id_usuario = p.id_usuario
					WHERE d.id_pedido = '{$_GET['idp']}'
					AND d.id_cajero = 0
					AND d.id_sesion_caja = 0
					GROUP BY d.id_pedido";//die( $sql );
			$stm = mysql_query( $sql ) or die( "Error al consultar si hay devolucion pendientes : " . mysql_error() );
			$id_pedido_devolucion = $_GET['idp'];
		}else{
			$id_pedido_devolucion = $_GET['id_pedido_original'];
		}
		if( mysql_num_rows( $stm ) > 0 ){
			$row = mysql_fetch_assoc( $stm );
			$devolucion = $row;
		//consulta el detalle
			$dev_detail = "SELECT 
					aux.id_producto,
					aux.producto,
					aux.cantidad,
					aux.precio,
					aux.monto,
					aux.descuento,
					aux.porc_desc,
					IF(s.mostrar_ubicacion=1 Or s.mostrar_alfanumericos=1,1,0) as infoAdicional,
					CONCAT(
					IF(s.mostrar_ubicacion=1 AND sp.ubicacion_almacen_sucursal!='',CONCAT('Ubicación: ',sp.ubicacion_almacen_sucursal,' | '),''),
						IF(s.mostrar_alfanumericos=1,CONCAT('Clave: ',aux.clave),'')
					) as info
				FROM(
					SELECT
					P.id_productos AS id_producto,
					P.nombre AS producto,
					P.clave,
					IF(prd.id_producto IS NULL, dd.cantidad , ROUND( dd.cantidad / prd.cantidad ) ) AS cantidad,
					dp.precio AS precio,
					( IF(prd.id_producto IS NULL, dd.cantidad , ROUND( dd.cantidad / prd.cantidad ) )*dp.precio)
						-((IF(prd.id_producto IS NULL, dd.cantidad , ROUND( dd.cantidad / prd.cantidad ) )*dp.precio)
						*
						(IF(pe.descuento=0,0,(pe.descuento*100/pe.subtotal))/100)) AS monto,
					IF(dp.descuento=0,0,dp.descuento/dp.cantidad) AS descuento,
					(IF(pe.descuento=0,0,(pe.descuento*100/pe.subtotal)/100)*(dd.cantidad*dp.precio)) AS porc_desc
					FROM ec_devolucion_detalle dd 
					INNER JOIN ec_devolucion d  
					ON dd.id_devolucion = d.id_devolucion
					INNER JOIN ec_pedidos_detalle dp  
					ON dp.id_pedido_detalle = dd.id_pedido_detalle
					LEFT JOIN ec_pedidos pe ON pe.id_pedido=dp.id_pedido
					INNER JOIN  ec_productos P
					ON dp.id_producto = P.id_productos
					LEFT JOIN ec_productos_detalle prd
					ON prd.id_producto = dp.id_producto
					WHERE d.id_pedido IN( {$id_pedido_devolucion} )
					AND d.id_sesion_caja = 0
					AND d.id_cajero = 0
				)aux
				LEFT JOIN sys_sucursales_producto sp ON aux.id_producto=sp.id_producto
				JOIN sys_sucursales s ON sp.id_sucursal=s.id_sucursal AND sp.id_sucursal={$user_sucursal}";
			$dev_stm = mysql_query( $dev_detail ) or die( "Error al consultar el detalle de los prouctos devueltos : " . mysql_error() );
			if( mysql_num_rows( $dev_stm ) > 0 ){
				$devolucion['detail'] = array();
				while( $dev_row = mysql_fetch_assoc( $dev_stm ) ){
					array_push( $devolucion['detail'], $dev_row );
				}
				//$dev_row['detail']
			}
		//consulta pagos
			$sql = "SELECT
						ax.total_pedido_pagos,
						SUM( IF( dp.id_devolucion_pago IS NULL, 0, dp.monto ) ) AS total_devolucion_pagos
					FROM(
						SELECT
							p.id_pedido,
							SUM( IF( pp.id_pedido_pago IS NULL, 0, pp.monto ) ) AS total_pedido_pagos
						FROM ec_pedidos p
						LEFT JOIN ec_pedido_pagos pp
						ON pp.id_pedido = p.id_pedido
						WHERE p.id_pedido = {$id_pedido_devolucion}
						GROUP BY p.id_pedido
					)ax
					LEFT JOIN ec_devolucion d
					ON d.id_pedido = ax.id_pedido
					LEFT JOIN ec_devolucion_pagos dp
					ON dp.id_devolucion = d.id_devolucion
					GROUP BY ax.id_pedido";
			$payment_stm = mysql_query( $sql ) or die( "Error al consultar monto pagado : " . mysql_error() );
			$payment_row = mysql_fetch_assoc( $payment_stm );
			$devolucion['pagos_realizados'] = $payment_row['total_pedido_pagos'] - $payment_row['total_devolucion_pagos'];
		}
		//die( "here : {$ruta_or}" );
	//}
//var_dump( $devolucion );
//die(json_encode( $devolucion ));
//implementado por Oscar 29-12-2017
    $diferencia='';
    $s_f_i=0;
    if(isset($_GET["sald_fav_cl"])){
    	$diferencia=1;//
    	$saldo_cliente=$_GET["sald_fav_cl"];//recibimos saldo a favor del cliente

    	if($saldo_cliente==0){
    		$accion="Saldo liquidado";
    		$monto_=0;
    	}
    	if($saldo_cliente<0){
    		$accion="Cobrar:";
    		$monto_=round($saldo_cliente*-1,2);
    		$mensaje_nota="Se Cobró al cliente: $".$saldo_cliente;
    	}
    	if($saldo_cliente>0){
    		$accion="Devolver:";
    		$monto_=round($saldo_cliente,2);
    		$mensaje_nota="Se Regresó al cliente: $".$saldo_cliente;
    	}

    	if($monto_<1){
    		$monto_=0;
    	}	

    	$s_f_i=$_GET["favOrig"];
    }
//fin de cambio
	$id_pedido = $_GET["idp"];	
	$sucursal = "";
	$folio = "";
	$prefijo = "";
	$subtotal = "0";
	$total = "0";
	$productos = array();
	$productosP = array();
	$pagos = array();
	$vendedor = "N/A";
	$lineas_productos = 0;
	$lineas_pagos = 0;
	$total_pagos = "0";
	$tipofolio = "PEDIDO";
//datos del vendedor	
	$cs = "SELECT
				CONCAT(nombre, ' ', apellido_paterno) AS vendedor 
			FROM sys_users 
			WHERE id_usuario = '{$user_id}'";
	if ($rs = mysql_query($cs)) {
		if ($dr = mysql_fetch_assoc($rs)) {
			$vendedor = $dr["vendedor"];
		} mysql_free_result($rs);
	}
//datos de la sucursal
	$cs = "SELECT 
			s.nombre AS sucursal, 
			s.prefijo,
			REPLACE(s.descripcion,'*','\n') as descripcion,
			sc.mostrar_descuento_ticket 
		FROM sys_sucursales s 
		LEFT JOIN ec_configuracion_sucursal sc ON s.id_sucursal = sc.id_sucursal
		WHERE s.id_sucursal = '{$user_sucursal}' ";
	if ($rs = mysql_query($cs)) {
		if ($dr = mysql_fetch_assoc($rs)) {
			$sucursal = $dr["sucursal"];
			$prefijo = $dr["prefijo"];
		//armamos los datos Fiscales
			$datos_fiscales=$dr["descripcion"];
		//ver descuento del ticket
			$show_discount = $dr["mostrar_descuento_ticket"];
		} mysql_free_result($rs);
	}
//datos de la venta
	$cs = "SELECT
	       IF(ISNULL(folio_nv),
	       folio_pedido, folio_nv) AS folio,
	       IF(ISNULL(folio_nv), 'PEDIDO', 'FOLIO') AS tipofolio,
	       subtotal,
	       iva,
	       ieps,
	       total,
	       descuento,
	       pagado,
	       folio_abono as folioA,
	/*implementación Oscar 28.02.2019 para que la hora del ticket sea tomada de la MySQL*/
			DATE_FORMAT(NOW(), '%d/%m/%Y %H:%i:%s') as fecha_ticket
	/*Fin de cambio Oscar 28.02.2019*/
	       FROM ec_pedidos
	       WHERE id_pedido = '{$id_pedido}' ";

	if ($rs = mysql_query($cs)){
		if ($dr = mysql_fetch_assoc($rs)){
			$tipofolio = $dr["tipofolio"];
			$folio = $dr["folio"];
			$total = $dr["total"];
			$subtotal = $dr["subtotal"];
            $total = $dr["total"];
            $pagado = $dr["pagado"];
            $descuentoGen= $dr["descuento"];
            $folioA = "A$prefijo".$dr["folioA"];
	/*implementación Oscar 28.02.2019 para que la hora del ticket sea tomada de la MySQL*/
            $fecha_tkt=$dr["fecha_ticket"];
    /*Fin de cambio Oscar 28.02.2019*/
		} mysql_free_result($rs);
	}

	$cs = "SELECT 
				CONCAT(PP.fecha,' ',TP.nombre,'(pagos)') AS nombre, 
				SUM(monto) AS monto 
			FROM ec_pedido_pagos PP
			INNER JOIN ec_tipos_pago TP ON PP.id_tipo_pago = TP.id_tipo_pago
			WHERE PP.id_pedido = '{$id_pedido}' AND (referencia='' OR referencia=null)
			GROUP BY CONCAT(PP.fecha,' ',PP.hora)
			ORDER BY PP.id_pedido_pago ASC";//TP.nombre
	
	if ( $rs = mysql_query($cs) ) {
		while ( $dr = mysql_fetch_assoc($rs) ){
			// Concatenar precio unitario en la descripción
			++$lineas_pagos;
			$total_pagos += $dr["monto"];
			array_push($pagos, $dr);
		}
		mysql_free_result($rs);
	}
/*implementación de Oscar 19.11.2018 para restar devoluciones*/
	$sql="SELECT 
			SUM( IF(dev.id_devolucion IS NULL,0,IF(referencia='' OR referencia=null,dp.monto,0) ) )
		FROM ec_devolucion_pagos dp 
		LEFT JOIN ec_devolucion dev ON dp.id_devolucion=dev.id_devolucion
		WHERE dev.id_pedido=$id_pedido";
	$eje=mysql_query($sql)or die("Error al calcular monto de devoluciones!!!\n\n".$sql."\n\n".mysql_error());
	$res_dev=mysql_fetch_row($eje);
	
	$monto_devolucion=$res_dev[0];//aqui capturamos el monto de la devolucion
//indicador de lineas de devolución
	$lineas_dev=0;
	if($monto_devolucion>0){
		$lineas_dev=5;
	}
/*fin de cambio 06.09.2018*/
	
	class TicketPDF extends FPDF {
		// Members
		var $sucursal = "";
		var $pedido = "";
		var $inicio = 32;
	
		// Constructor
		function TicketPDF($orientation='P', $unit='mm', $size, $sucursal='', $pedido='', $inicio=10) {
			parent::FPDF($orientation, $unit, $size);
				
			$this->AddFont('Arial');
			$this->SetMargins(7, 0, 7);
			$this->SetDisplayMode("real", "continuous");
			#$this->SetAutoPageBreak(false);
			$this->SetAutoPageBreak(true, -5);
				
			$this->sucursal = utf8_decode($sucursal);
			$this->pedido = utf8_decode($pedido);
			$this->inicio = $inicio;
		}
	
		// Cabecera de página
		function Header() {
		}
	
		function Footer() {
			//$this->SetY(-15);
			//$this->SetFont('Arial','I',8);
			// Número de página
			//$this->Cell(0,10, utf8_decode('Página ').$this->PageNo().'/{nb}',0,0,'R');
		}
	
		function AcceptPageBreak() {
			$x = $this->GetX();
			$this->AddPage();
			//$this->SetXY($x, $this->inicio);
			$this->SetXY($x, 1);
			#$this->SetY($this->inicio);
			return false;
		}
	}
	$espacio_devolucion = ( $devolucion['folio_devolucion'] != null ? 30 : 0 );
	//+40+130
	$ticket = new TicketPDF("P", "mm", array(80,80+$espacio_devolucion+$lineas_dev+50+$lineas_productos*6+($total!=$subtotal?12:0)+($pagado>0?14:30)+(count($pagos)>0?($lineas_pagos+1)*6:0)+40+40), "{$sucursal}", "{$folio}", 10);
	$ticket->AliasNbPages();
	$ticket->AddPage();
	
	$bF=10;
	if($tv==1){
		$ticket->SetFont('Arial','B',$bF+4);
		$ticket->SetXY(5, $ticket->GetY()+1);
		$ticket->Cell(66, 3, utf8_decode("VENTA POR MAYOREO"), "" ,0, "C");
	}
/*implementacion Oscar 2021 para mostrar descuento de ticket*/
	if( ($total!=$subtotal) && $show_discount == 1 ){
		$ticket->SetFont('Arial','B',$bF);
		$ticket->SetXY(15, $ticket->GetY()+1);
		$ticket->Cell(66, 3, utf8_decode("DESCUENTO : "), "" ,0, "C");

		$ticket->SetFont('Arial','B',$bF+4);
		$ticket->SetXY(32, $ticket->GetY()+1);
		$ticket->Cell(66, 3, utf8_decode(" $ {$descuentoGen}"), "" ,0, "C");
	}
/*fin de cambio Oscar 2021*/

/*implementación de Oscar 10.09.2018 para */
	if( $_GET['es_apartado'] == 1 ){//si es apartado
		$ticket->SetFont('Arial','B',$bF+4);
		$ticket->SetXY(5, $ticket->GetY()+2);
		$ticket->Cell(66, 6, utf8_decode("REIMPRESIÓN DE TICKET"), "" ,0, "C");

		$ticket->SetXY(5, $ticket->GetY()+4);
		$ticket->Cell(66, 6, utf8_decode("POR CAMBIO"), "" ,0, "C");
	}
/*fin de cambio 10.09.2018*/

	$ticket->SetXY(5, $ticket->GetY()+6);
	$ticket->Cell(66, 3, "", "TB" ,0, "C");
	
	$ticket->SetFont('Arial','',$bF-2);
	$ticket->SetXY(5, $ticket->GetY()+4);
	//$ticket->MultiCell(66, 6, utf8_decode('datos:'.$datos_fiscales), "" ,0, "C");
//$ticket->MultiCell(66, 4, utf8_decode($datos_fiscales), "", "C", false);
	
	$ticket->SetFont('Arial','B',$bF+4);
	$ticket->SetXY(5, $ticket->GetY()+6);
	$ticket->Cell(66, 6, utf8_decode("VENDEDOR:"), "" ,0, "C");
	$ticket->SetXY(5, $ticket->GetY()+8);
	$ticket->SetFont('Arial','B',$bF+8);
	$ticket->Cell(66, 6, utf8_decode("{$vendedor}"), "" ,0, "C");

	$ticket->SetFont('Arial','B',$bF);
	$ticket->SetXY(5, $ticket->GetY()+12);
	$ticket->Cell(66, 6, utf8_decode("FOLIO : {$ticket->pedido}"), "" ,0, "C");
	$ticket->SetXY(5, $ticket->GetY()+4);
	//$ticket->Cell(66*0.6, 6, utf8_decode("{$tipofolio}"), "" ,0, "C");
	
	//$ticket->SetX(5+66*0.6);
	//$ticket->Cell(66, 6, utf8_decode("{$ticket->pedido}"), "" ,0, "C");
	
	$ticket->SetXY(5, $ticket->GetY()+6);
	$ticket->Cell(66, 3, "", "TB" ,0, "C");
	
	$ticket->SetFont('Arial','',$bF);
	
	$ticket->SetXY(5, $ticket->GetY()+3);
	$ticket->Cell(66, 6, utf8_decode("FECHA Y HORA DE EMISIÓN:"), "" ,0, "C");
	
	$ticket->SetXY(5, $ticket->GetY()+4.5);
/*implementación Oscar 28.02.2019 para que la hora del ticket sea tomada de la MySQL*/
	$ticket->Cell(66, 6, utf8_decode("Estado de México ") . utf8_decode($fecha_tkt), "" ,0, "C");
/*Fin de cambio Oscar 28.02.2019*/
	
	
	$ticket->SetXY(5, $ticket->GetY()+5.5);
	$ticket->Cell(66, 3, "", "TB" ,0, "C");
	
/*implementacion Oscar 2024-03-07 para adjuntar devolucion en ticket de talon de pago*/
	if( sizeof($devolucion) > 0 ){
		$ticket->SetFont('Arial','B',$bF+8);
		$ticket->SetXY(5, $ticket->GetY()+6);
		$ticket->Cell(66, 6, utf8_decode("DEVOLUCION:"), "" ,0, "C");
		$ticket->SetFont('Arial','B',$bF );
		$ticket->SetXY(5, $ticket->GetY()+8);
		$ticket->Cell(66, 6, utf8_decode("Folio devolución : {$devolucion['folio_devolucion']}"), "" ,0, "L");
		$ticket->SetFont('Arial','B',$bF );
		$ticket->SetXY(5, $ticket->GetY()+4);
		$ticket->Cell(66, 6, utf8_decode("Folio pedido : {$devolucion['folio_venta_original']}"), "" ,0, "L");
	//tabla de productos devueltos
		$ticket->SetXY(7, $ticket->GetY()+5.5);
		$ticket->Cell(66, 3, "", "TB" ,0, "C");
		
		$ticket->SetFont('Arial','',$bF);
		
		$ticket->SetXY(7, $ticket->GetY()+6);
		$ticket->Cell(66*0.63, 6, utf8_decode("DESCRIPCIÓN"), "B" ,0, "L");
		$ticket->SetXY(7+66*0.63, $ticket->GetY());
		$ticket->Cell(66*0.12, 6, utf8_decode("CANT"), "B" ,0, "L");
		$ticket->SetXY(7+66*0.75, $ticket->GetY());
		$ticket->Cell(66*0.25, 6, utf8_decode("PRECIO"), "B" ,0, "R");		
		$ticket->SetFont('Arial','',$bF-2);
		$ticket->SetXY(7, $ticket->GetY()+8);
  
		$total_de_devolucion=0;
		foreach ($devolucion['detail'] as $producto) {
				$y = $ticket->GetY();
				
				$ticket->SetXY(7+66*0.75, $y);
				$totReal+=$producto["monto"];
				
				$ticket->MultiCell(66*0.25, 4, "$ " . number_format($producto["monto"], 2), "", "R", false);
				
				$ticket->SetXY(7+66*0.63, $y);
				$ticket->MultiCell(66*0.12, 4, $producto["cantidad"], "", "C", false);
				
				$ticket->SetXY(7, $y);
				$ticket->MultiCell(66*0.63, 4, utf8_decode("{$producto["producto"]}"), "", "L", false);
		/*implementación Oscar 10.10.2018 para imprimir ubicación y clave_proveedor en ticket*/
			if($producto['infoAdicional']==1){
				$ticket->SetFont('Arial','',$bF-3.5);
				$ticket->SetXY(5,($ticket->GetY()-1.5));
				$ticket->MultiCell(66*0.63, 4, utf8_decode("{$producto["info"]}"), "", "L", false);
			}
			$ticket->SetFont('Arial','',$bF-2);
		/*fin de cambio 10.10.2018*/
			$total_de_devolucion+=$producto["monto"];
		}
		/*$ticket->SetXY(7+66*0.40, $ticket->GetY()+3);
		$ticket->Cell(66*0.32, 2, "", "T" ,0, "C");*/
		$ticket->SetFont('Arial','',$bF);
		$ticket->SetXY(7+66*0.40, $ticket->GetY()+3);
		$ticket->Cell(100*0.40, 2, "", "T" ,0, "C");
		$ticket->SetXY(7+66*0.40, $ticket->GetY()+1);
		$ticket->Cell(100*0.40, 6, "Total Devolucion : $ " . number_format(round($total_de_devolucion,2), 2), "" ,0, "R");  
		//die( "here" );
	}else{
		//die( "not_here" );
	}
/*Fin de implementacion Oscar 2024-03-07 para adjuntar devolucion en ticket de talon de pago*/

	$ticket->SetFont('Arial','',$bF);

	/*$ticket->SetY($ticket->GetY()-2);
	$ticket->SetXY(5+66*0.40, $ticket->GetY()+3);
	$ticket->Cell(66*0.32, 2, "", "T" ,0, "C");
	
	$ticket->SetXY(5+66*0.75, $ticket->GetY());
	$ticket->Cell(66*0.25, 2, "", "T" ,0, "C");
	$ticket->SetY($ticket->GetY()-5);*/
	
//apartado de descuentos
	if($total != $subtotal) {
		$ticket->SetXY(5+66*0.4, $ticket->GetY()+5);
		$ticket->Cell(66*0.3, 6, utf8_decode("Subtotal"), "" ,0, "L");
		 
		$ticket->SetXY(5+66*0.75, $ticket->GetY());
		$ticket->Cell(66*0.25, 6, "$ " . number_format($subtotal, 2), "" ,0, "R");
	
//AQUI ENTRA DETALLE DE DESCUENTOS
	//DESCUENTO POR PRODUCTOS
		$ticket->SetXY(5+66*0.4, $ticket->GetY()+5);
		$ticket->Cell(66*0.3, 6, utf8_decode("Desc en prod:"), "" ,0, "L");

		$ticket->SetXY(5+66*0.75, $ticket->GetY());
		$ticket->Cell(66*0.25, 6, "-$ ".number_format($descProds, 2), "" ,0, "R");
	//DECUENTO DIRECTO EN MONTO
		$ticket->SetXY(5+66*0.4, $ticket->GetY()+5);
		$ticket->Cell(66*0.3, 6, utf8_decode("Descuento directo:"), "" ,0, "L");

		$ticket->SetXY(5+66*0.75, $ticket->GetY());
		$ticket->Cell(66*0.25, 6, "-$ ".number_format(($descuentoGen-$descProds), 2), "" ,0, "R");
	//DESCUENTO GENERAL
		$ticket->SetXY(5+66*0.4, $ticket->GetY()+5);
		$ticket->Cell(66*0.25, 6, utf8_decode("Descuento Total:"), "" ,0, "L");
	
		$ticket->SetXY(5+66*0.75, $ticket->GetY());
		$ticket->Cell(66*0.25, 6, "-$ " . number_format($descuentoGen, 2), "" ,0, "R");
	}
	
	$ticket->SetFont('Arial','',$bF-2);
	if($pagado == 1) {
			if( $devolucion['pagos_realizados'] > 0 ){
				$ticket->SetFont('Arial','B',$bF+2);
				$ticket->SetXY(5, $ticket->GetY()+6);
				$ticket->Cell(66, 6, utf8_decode("TOTAL PAGADO : $ {$devolucion['pagos_realizados']}"), "" ,0, "L");
				$ticket->SetXY(5, $ticket->GetY()+6);
				$ticket->Cell(66, 6, utf8_decode("NUEVO TOTAL : {$devolucion['total_actual']}"), "" ,0, "L");
			}
		if($diferencia!=''){
		//
			$ticket->SetFont('Arial','B',$bF+20);
			$ticket->SetXY(5, $ticket->GetY()+8);
			$ticket->Cell(66, 6, utf8_decode("TOTAL"), "" ,0, "C");
				
			$ticket->SetXY(5, $ticket->GetY() + 9);
			$ticket->Cell(66, 6, "$ " . number_format( abs( $total ), 2), "" ,0, "C");
			
		//
			//$ticket->SetXY(5, $ticket->GetY()+8);
			$ticket->SetXY(5, $ticket->GetY()+15);
			$ticket->Cell(5, 6, utf8_decode("Saldo a favor:"), "" ,0, "L");

			$ticket->SetXY(5, $ticket->GetY()+15);/*+66*0.75*/
			$ticket->Cell(66, 6, "$ " . number_format( abs( $s_f_i ), 2), "" ,0, "C");/*66*0.25*/

		//
			$ticket->SetFont('Arial','B',30);
			$ticket->SetXY(5, $ticket->GetY()+15);
			$ticket->Cell(66, 6, utf8_decode($accion), "" ,0, "C");

			$ticket->SetXY(5, $ticket->GetY()+15);
			$ticket->Cell(66, 6, "$ ".number_format( abs( $s_f_i - $total ), 2), "" ,0, "C");//C R

		}else{
			$ticket->SetFont('Arial','B',30);
			$ticket->SetXY(5, $ticket->GetY()+15);
			$ticket->Cell(66, 6, utf8_decode("TOTAL"), "" ,0, "C");

			$ticket->SetXY(5, $ticket->GetY()+15);
			$ticket->Cell(66, 6, "$ " . number_format( abs( $total ), 2), "" ,0, "C");
				
			/*$ticket->SetXY(5+66*0.75, $ticket->GetY() + 8);
			$ticket->Cell(66, 6, "$ " . number_format($total_pagos-$monto_devolucion, 2), "" ,0, "C");*/

			$V=new EnLetras();
			$ticket->SetFont('Arial','',$bF-2);
			$ticket->SetXY(5, $ticket->GetY()+8);
			$ticket->Cell(66, 6, utf8_decode($V->ValorEnLetras( abs( $total ), "Pesos")), "" ,0, "L");
		}
	//
		//$ticket->SetFont('Arial','',$bF-2);
		//$ticket->SetXY(5, $ticket->GetY()+8);
		//$ticket->Cell(66, 6, utf8_decode("Para cualquier aclaración, presentar su ticket."), "" ,0, "C");
	//fin de cambio

	}else{
		$resta = $total - $_GET['initial_payment'];
	//initial_payment
		$ticket->SetFont('Arial','B',25);
		$ticket->SetXY(5, $ticket->GetY()+15);
		$ticket->Cell(66, 6, utf8_decode("Monto de Abono"), "" ,0, "C");

		$ticket->SetFont('Arial','B',32);
		$ticket->SetXY(5, $ticket->GetY()+15);
		$ticket->Cell(66, 6, "$ " . number_format( abs( $_GET['initial_payment'] ), 2), "" ,0, "C");
			
		/*$ticket->SetXY(5+66*0.75, $ticket->GetY() + 8);
		$ticket->Cell(66, 6, "$ " . number_format($total_pagos-$monto_devolucion, 2), "" ,0, "C");*/

		$V=new EnLetras();
		$ticket->SetFont('Arial','',$bF-2);
		$ticket->SetXY(5, $ticket->GetY()+8);
		$ticket->Cell(66, 6, utf8_decode($V->ValorEnLetras( abs( $total ), "Pesos")), "" ,0, "L");

		$ticket->SetFont('Arial','B',$bF+5);
		$ticket->SetXY(2, $ticket->GetY()+6);
	    $ticket->Cell(66*0.40, 6, utf8_decode("Resta"), "" ,0, "L");

		$ticket->SetXY(25, $ticket->GetY());
		$ticket->Cell(66*0.35, 6, "$" . number_format( abs( $resta ), 2), "" ,0, "R");
	/*
		$ticket->SetXY(5, $ticket->GetY()+6);
	    $ticket->Cell(66*0.25, 6, utf8_decode("Resta"), "" ,0, "L");
	  
		$ticket->SetXY(20, $ticket->GetY());
		$ticket->Cell(66*0.25, 6, "$" . number_format($resta, 2), "" ,0, "R");
	*/
		
		$ticket->SetFont('Arial','',$bF-2);
		$ticket->SetXY(5, $ticket->GetY()+5);
		$ticket->Cell(66, 6, utf8_decode("Fecha límite para recoger y liquidar sus apartados"), "", 0, 'C');
	
		$ticket->SetXY(5, $ticket->GetY()+4);
		$ticket->Cell(66, 6, utf8_decode("10 de Diciembre."), "", 0, 'C');
	
		$ticket->SetXY(5, $ticket->GetY()+5);
		$ticket->Cell(66, 6, utf8_decode("En apartados NO hay cambios NI devoluciones."), "", 0, 'C');
  		
  		$ticket->SetFont('Arial','B',$bF+3);
		$ticket->SetXY(1, $ticket->GetY()+8);
		$ticket->Cell(66, 6, utf8_decode("FOLIO APARTADO: ".$folioA), "", 0, 'C');
	
	}	

    //generacion de codigo de barras 
        include('../include/barcode/barcode.php');
        $barcode_name = str_replace(' ', '', $folio );
        $barcodePath = "../img/codigos_barra/{$barcode_name}.png";
	    if( file_exists( $barcodePath ) ){
	   		unlink( $barcodePath );
	    }
        barcode( $barcodePath, $folio, '60', 'horizontal', 'code128', true, 1);
    //se incrustra el codigo de barras en el ticket
        //$ticket->Image( $barcodePath, 6, $ticket->GetY()+10, 70);

	    if(file_exists("../img/codigos_barra/".$folio.".png")){
	    	$ticket->SetXY(5, $ticket->GetY()+8);
	    	$ticket->Image("../img/codigos_barra/".$folio.".png", 15, $ticket->GetY()+5,46);
	    }

  		$ticket->SetFont('Arial','B',$bF+3);
		$ticket->SetXY(1, $ticket->GetY()+40);
		$ticket->Cell(66, 6, utf8_decode("PASE A CAJA A PAGAR"), "", 0, 'C');
    if($printPan == 1) {
    	ob_end_clean();
	   $ticket->Output();
    }else{
	/*Implementación Oscar 07.03.2019 para finalzar el satus de la devolución*/
		if(isset($_GET["id_pedido_original"]) || $_GET['es_apartado']==1){
			$sql="UPDATE ec_devolucion SET status=3,observaciones='$mensaje_nota' WHERE id_pedido=$id_pedido_original";
			$eje=mysql_query($sql)or die("Error al actualizar el status de la devolución\n\n".mysql_error()."\n\n".$sql);
		}
/*implementacion Oscar 2024-02-01 para ruta especifica de ticket*/
	/*instancia clases*/
		include( '../conexionMysqli.php' );
		include( '../code/especiales/controladores/SysArchivosDescarga.php' );
		$SysArchivosDescarga = new SysArchivosDescarga( $link );
		include( '../code/especiales/controladores/SysModulosImpresionUsuarios.php' );
		$SysModulosImpresionUsuarios = new SysModulosImpresionUsuarios( $link );
		include( '../code/especiales/controladores/SysModulosImpresion.php' );
		$SysModulosImpresion = new SysModulosImpresion( $link );

	/*implementación Oscar 17.09.2018 para impresión de tickets de acuerdo a la configuración de la sucursal*/
    	$sql="SELECT IF($pagado=1,ticket_venta,ticket_apartado) FROM sys_sucursales WHERE id_sucursal=$user_sucursal";
    	$eje=mysql_query($sql)or die("Error al consultar número de impresiones!!!\n\n".$sql."\n\n".mysql_error());	
    	$numero=mysql_fetch_row($eje);
    //itera el numero de impresiones
    	for($cont=1;$cont<=$numero[0];$cont++){
    		$nombre_ticket="ticket_".$user_sucursal."_" . date("YmdHis") . "_" . strtolower($tipofolio) . "_" . $folio . "_".$cont.".pdf";
			$ruta_salida = '';
			$ruta_salida = $SysModulosImpresionUsuarios->obtener_ruta_modulo_usuario( $user_id, 2 );//talon de pago
			if( $ruta_salida == 'no' ){
				$ruta_salida = "cache/" . $SysModulosImpresion->obtener_ruta_modulo( $user_sucursal, 2 );//talon de pago
			}
	    	$ticket->Output( "../{$ruta_salida}/{$nombre_ticket}", "F" );

        /*Sincronización remota de tickets*/
    		if( $user_tipo_sistema == 'linea' ){/*registro sincronizacion impresion remota*/
				$registro_sincronizacion = $SysArchivosDescarga->crea_registros_sincronizacion_archivo( 'pdf', $nombre_ticket, $ruta_or, $ruta_salida, $user_sucursal, $user_id );
    		}else{//impresion por red local
				$enviar_por_red = $SysArchivosDescarga->crea_registros_sincronizacion_archivo_por_red_local( 2, 'pdf', $nombre_ticket, '', $ruta_salida, $user_sucursal, $user_id, 
				$carpeta_path, '../', 'location="index.php?";' );
			}
    	}//fin de for $cont
	/*fin de cambio Oscar 17.09.2018*/
       	die( "ok" );
       	//header ("location: index.php?scr=evaluation"); 
    }
		
	exit (0);

class EnLetras
{
  var $Void = "";
  var $SP = " ";
  var $Dot = ".";
  var $Zero = "0";
  var $Neg = "Menos";

	function ValorEnLetras($x, $Moneda ) 
	{
    $s="";
    $Ent="";
    $Frc="";
    $Signo="";
        
    if(floatVal($x) < 0)
     $Signo = $this->Neg . " ";
    else
     $Signo = "";
    
    if(intval(number_format($x,2,'.','') )!=$x) //<- averiguar si tiene decimales
      $s = number_format($x,2,'.','');
    else
      $s = number_format($x,0,'.','');
       
    $Pto = strpos($s, $this->Dot);
        
    if ($Pto === false)
    {
      $Ent = $s;
      $Frc = $this->Void;
    }
    else
    {
      $Ent = substr($s, 0, $Pto );
      $Frc =  substr($s, $Pto+1);
    }

    if($Ent == $this->Zero || $Ent == $this->Void)
       $s = "Cero ";
    elseif( strlen($Ent) > 7)
    {
       $s = $this->SubValLetra(intval( substr($Ent, 0,  strlen($Ent) - 6))) . 
             "Millones " . $this->SubValLetra(intval(substr($Ent,-6, 6)));
    }
    else
    {
      $s = $this->SubValLetra(intval($Ent));
    }

    if (substr($s,-9, 9) == "Millones " || substr($s,-7, 7) == "Millón ")
       $s = $s . "de ";

    $s = $s . $Moneda;

    if($Frc != $this->Void)
    {
       //$s = $s . " Con " . $this->SubValLetra(intval($Frc)) . "Centavos";
       $s = $s . " " . $Frc . "/100";
    }
    return ($Signo . $s . " M.N.");
   
}

function SubValLetra($numero) 
{
    $Ptr="";
    $n=0;
    $i=0;
    $x ="";
    $Rtn ="";
    $Tem ="";

    $x = trim("$numero");
    $n = strlen($x);

    $Tem = $this->Void;
    $i = $n;
    
    while( $i > 0)
    {
       $Tem = $this->Parte(intval(substr($x, $n - $i, 1). 
                           str_repeat($this->Zero, $i - 1 )));
       If( $Tem != "Cero" )
          $Rtn .= $Tem . $this->SP;
       $i = $i - 1;
    }

    
    //--------------------- GoSub FiltroMil ------------------------------
    $Rtn=str_replace(" Mil Mil", " Un Mil", $Rtn );
    while(1)
    {
       $Ptr = strpos($Rtn, "Mil ");       
       If(!($Ptr===false))
       {
          If(! (strpos($Rtn, "Mil ",$Ptr + 1) === false ))
            $this->ReplaceStringFrom($Rtn, "Mil ", "", $Ptr);
          Else
           break;
       }
       else break;
    }

    //--------------------- GoSub FiltroCiento ------------------------------
    $Ptr = -1;
    do{
       $Ptr = strpos($Rtn, "Cien ", $Ptr+1);
       if(!($Ptr===false))
       {
          $Tem = substr($Rtn, $Ptr + 5 ,1);
          if( $Tem == "M" || $Tem == $this->Void)
             ;
          else          
             $this->ReplaceStringFrom($Rtn, "Cien", "Ciento", $Ptr);
       }
    }while(!($Ptr === false));

    //--------------------- FiltroEspeciales ------------------------------
    $Rtn=str_replace("Diez Un", "Once", $Rtn );
    $Rtn=str_replace("Diez Dos", "Doce", $Rtn );
    $Rtn=str_replace("Diez Tres", "Trece", $Rtn );
    $Rtn=str_replace("Diez Cuatro", "Catorce", $Rtn );
    $Rtn=str_replace("Diez Cinco", "Quince", $Rtn );
    $Rtn=str_replace("Diez Seis", "Dieciseis", $Rtn );
    $Rtn=str_replace("Diez Siete", "Diecisiete", $Rtn );
    $Rtn=str_replace("Diez Ocho", "Dieciocho", $Rtn );
    $Rtn=str_replace("Diez Nueve", "Diecinueve", $Rtn );
    $Rtn=str_replace("Veinte Un", "Veintiun", $Rtn );
    $Rtn=str_replace("Veinte Dos", "Veintidos", $Rtn );
    $Rtn=str_replace("Veinte Tres", "Veintitres", $Rtn );
    $Rtn=str_replace("Veinte Cuatro", "Veinticuatro", $Rtn );
    $Rtn=str_replace("Veinte Cinco", "Veinticinco", $Rtn );
    $Rtn=str_replace("Veinte Seis", "Veintiseís", $Rtn );
    $Rtn=str_replace("Veinte Siete", "Veintisiete", $Rtn );
    $Rtn=str_replace("Veinte Ocho", "Veintiocho", $Rtn );
    $Rtn=str_replace("Veinte Nueve", "Veintinueve", $Rtn );

    //--------------------- FiltroUn ------------------------------
    If(substr($Rtn,0,1) == "M") $Rtn = "Un " . $Rtn;
    //--------------------- Adicionar Y ------------------------------
    for($i=65; $i<=88; $i++)
    {
      If($i != 77)
         $Rtn=str_replace("a " . Chr($i), "* y " . Chr($i), $Rtn);
    }
    $Rtn=str_replace("*", "a" , $Rtn);
    return($Rtn);
}

function ReplaceStringFrom(&$x, $OldWrd, $NewWrd, $Ptr)
{
  $x = substr($x, 0, $Ptr)  . $NewWrd . substr($x, strlen($OldWrd) + $Ptr);
}


function Parte($x)
{
    $Rtn='';
    $t='';
    $i='';
    Do
    {
      switch($x)
      {
         Case 0:  $t = "Cero";break;
         Case 1:  $t = "Un";break;
         Case 2:  $t = "Dos";break;
         Case 3:  $t = "Tres";break;
         Case 4:  $t = "Cuatro";break;
         Case 5:  $t = "Cinco";break;
         Case 6:  $t = "Seis";break;
         Case 7:  $t = "Siete";break;
         Case 8:  $t = "Ocho";break;
         Case 9:  $t = "Nueve";break;
         Case 10: $t = "Diez";break;
         Case 20: $t = "Veinte";break;
         Case 30: $t = "Treinta";break;
         Case 40: $t = "Cuarenta";break;
         Case 50: $t = "Cincuenta";break;
         Case 60: $t = "Sesenta";break;
         Case 70: $t = "Setenta";break;
         Case 80: $t = "Ochenta";break;
         Case 90: $t = "Noventa";break;
         Case 100: $t = "Cien";break;
         Case 200: $t = "Doscientos";break;
         Case 300: $t = "Trescientos";break;
         Case 400: $t = "Cuatrocientos";break;
         Case 500: $t = "Quinientos";break;
         Case 600: $t = "Seiscientos";break;
         Case 700: $t = "Setecientos";break;
         Case 800: $t = "Ochocientos";break;
         Case 900: $t = "Novecientos";break;
         Case 1000: $t = "Mil";break;
         Case 1000000: $t = "Millón";break;
      }

      If($t == $this->Void)
      {
        $i = $i + 1;
        $x = $x / 1000;
        If($x== 0) $i = 0;
      }
      else
         break;
           
    }while($i != 0);
   
    $Rtn = $t;
    Switch($i)
    {
       Case 0: $t = $this->Void;break;
       Case 1: $t = " Mil";break;
       Case 2: $t = " Millones";break;
       Case 3: $t = " Billones";break;
    }
    return($Rtn . $t);
}
}
?>