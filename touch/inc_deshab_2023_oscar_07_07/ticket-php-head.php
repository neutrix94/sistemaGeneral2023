<?php
	#header("Content-Type: text/plain;charset=utf-8");	
	define('FPDF_FONTPATH','../../include/fpdf153/font/');
	
	include("../../include/fpdf153/fpdf.php");
    //include("../../conect.php");
    
    
    if(!isset($_GET["noImp"]))
        $_GET["noImp"]=1;
	if(!isset($id_pedido)){
		$id_pedido = $_GET["idp"];
	}

//die('id_pedido_local:'.$id_pedido);
	$sucursal = "";
	$folio = "";
	$prefijo = "";
	$subtotal = "0";
	$total = "0";
	$productos = array();
	$pagos = array();
	$vendedor = "N/A";
	$lineas_productos = 0;
	$lineas_pagos = 0;
	$total_pagos = "0";
	$tipofolio = "PEDIDO";
	
	$cs = "SELECT CONCAT(nombre, ' ', apellido_paterno) AS vendedor FROM sys_users WHERE id_usuario = '{$user_id}' ";
	if ($rs = mysql_query($cs,$local)) {
		if ($dr = mysql_fetch_assoc($rs)) {
			$vendedor = $dr["vendedor"];
		} mysql_free_result($rs);
	}
	
	
	$cs = "SELECT nombre AS sucursal, prefijo FROM sys_sucursales WHERE id_sucursal = '{$user_sucursal}' ";
	if ($rs = mysql_query($cs,$local)) {
		if ($dr = mysql_fetch_assoc($rs)) {
			$sucursal = $dr["sucursal"];
			$prefijo = $dr["prefijo"];
		} mysql_free_result($rs);
	}
	
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
	       folio_abono as folioA
	       FROM ec_pedidos
	       WHERE id_pedido = '{$id_pedido}' ";
	  
	if ($rs = mysql_query($cs,$local))
	{
		if ($dr = mysql_fetch_assoc($rs))
		{
			$tipofolio = $dr["tipofolio"];
			$folio = $dr["folio"];
			$total = $dr["total"];
			$subtotal = $dr["subtotal"];
            $total = $dr["total"];
            $pagado = $dr["pagado"];
            $descuento = $dr["descuento"];
            $folioA = "A$prefijo".$dr["folioA"];
		} mysql_free_result($rs);
	}else{
		echo 'Error!!!\n\n'.mysql_error();
	}
	
	$cs = "SELECT
	       P.id_productos AS id_producto,
	       P.nombre AS producto,
	       PD.cantidad,
	       PD.precio,
	       PD.monto
	       FROM ec_productos P
	       INNER JOIN ec_pedidos_detalle PD ON PD.id_producto = P.id_productos
	       WHERE PD.id_pedido = '{$id_pedido}' ";
	       
	       
	if ($rs = mysql_query($cs,$local))
	{
		while ($dr = mysql_fetch_assoc($rs))
		{
			// Concatenar precio unitario en la descripción
			$dr["producto"] .= " \${$dr["precio"]}";
			$lineas_productos += ceil(strlen($dr["producto"])/32.0);
			array_push($productos, $dr);
		}
		mysql_free_result($rs);
	}
	
	$cs = "SELECT TP.nombre, monto FROM ec_pedido_pagos PP
			INNER JOIN ec_tipos_pago TP ON PP.id_tipo_pago = TP.id_tipo_pago
			WHERE PP.id_pedido = '{$id_pedido}'
			ORDER BY TP.nombre";
	
	if ($rs = mysql_query($cs,$local))
	{
		while ($dr = mysql_fetch_assoc($rs))
		{
			// Concatenar precio unitario en la descripción
			++$lineas_pagos;
			$total_pagos += $dr["monto"];
			array_push($pagos, $dr);
		}
		mysql_free_result($rs);
	}
	
	class TicketPDF extends FPDF {
		// Members
		var $sucursal = "";
		var $pedido = "";
		var $inicio = 32;
	
		// Constructor
		function TicketPDF($orientation='P', $unit='mm', $size, $sucursal='', $pedido='', $inicio=10) {
			parent::FPDF($orientation, $unit, $size);
				
			$this->AddFont('Arial');
			$this->SetMargins(6, 0, 6);
			$this->SetDisplayMode("real", "continuous");
			#$this->SetAutoPageBreak(false);
			$this->SetAutoPageBreak(true, -5);
				
			$this->sucursal = utf8_decode($sucursal);
			$this->pedido = utf8_decode($pedido);
			$this->inicio = $inicio;
		}
	
		// Cabecera de página
		function Header(){
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
	
	
	$ticket = new TicketPDF("P", "mm", array(80,90+$lineas_productos*6+($total!=$subtotal?12:0)+($pagado>0?14:30)+(count($pagos)>0?($lineas_pagos+1)*6:0)), "{$sucursal}", "{$folio}", 10);
	$ticket->AliasNbPages();
	$ticket->AddPage();
	
	$bF=10;
	
	$ticket->Image("../img/logo-casa-fondo-blanco.png", 28, 5, 22);
	
	$ticket->SetFont('Arial','B',$bF);
	$ticket->SetXY(7, 40);
	$ticket->Cell(66, 6, utf8_decode("CASA DE LAS LUCS"), "" ,0, "C");
	
	$ticket->SetFont('Arial','',$bF);
	$ticket->SetXY(7, $ticket->GetY()+4);
	$ticket->Cell(66, 6, utf8_decode("Sucursal {$ticket->sucursal}"), "" ,0, "C");
	
	$ticket->SetFont('Arial','',$bF);
	
	$ticket->SetXY(7, $ticket->GetY()+4);
	$ticket->Cell(66*0.6, 6, utf8_decode("{$tipofolio}"), "" ,0, "C");
	echo 'pedido: '.$id_pedido_local;

	$ticket->SetX(7+66*0.6);
	$ticket->Cell(66*0.4, 6, utf8_decode("{$ticket->pedido}"), "" ,0, "C");
	
	$ticket->SetXY(7, $ticket->GetY()+6);
	$ticket->Cell(66, 3, "", "TB" ,0, "C");
	
	$ticket->SetFont('Arial','',$bF);
	
	$ticket->SetXY(7, $ticket->GetY()+3);
	$ticket->Cell(66, 6, utf8_decode("FECHA Y HORA DE EMISIÓN:"), "" ,0, "C");
	
	$ticket->SetXY(7, $ticket->GetY()+4.5);
	$ticket->Cell(66, 6, utf8_decode(date("d/m/Y H:i:s")), "" ,0, "C");
	
	$ticket->SetXY(7, $ticket->GetY()+4);
	$ticket->Cell(66, 6, utf8_decode("VENDEDOR:  {$vendedor}"), "" ,0, "L");
	
	$ticket->SetXY(7, $ticket->GetY()+5.5);
	$ticket->Cell(66, 3, "", "TB" ,0, "C");
	
	$ticket->SetFont('Arial','',$bF);
	
	$ticket->SetXY(7, $ticket->GetY()+3);
	$ticket->Cell(66*0.63, 6, utf8_decode("DESCRIPCIÓN"), "B" ,0, "L");
	
	$ticket->SetXY(7+66*0.63, $ticket->GetY());
	$ticket->Cell(66*0.12, 6, utf8_decode("CANT"), "B" ,0, "L");
	
	$ticket->SetXY(7+66*0.75, $ticket->GetY());
	$ticket->Cell(66*0.25, 6, utf8_decode("PRECIO"), "B" ,0, "R");
	
	$ticket->SetFont('Arial','',$bF-2);
	$ticket->SetXY(7, $ticket->GetY()+8);
	
	foreach ($productos as $producto) {
	$y = $ticket->GetY();
	
		$ticket->SetXY(7+66*0.75, $y);
		$ticket->MultiCell(66*0.25, 4, "$ " . number_format($producto["monto"], 2), "", "R", false);
	
		$ticket->SetXY(7+66*0.63, $y);
		$ticket->MultiCell(66*0.12, 4, $producto["cantidad"], "", "C", false);
	
		$ticket->SetXY(7, $y);
		$ticket->MultiCell(66*0.63, 4, utf8_decode("{$producto["producto"]}"), "", "L", false);

	}

	$ticket->SetY($ticket->GetY()-2);
	$ticket->SetXY(7+66*0.40, $ticket->GetY()+3);
	$ticket->Cell(66*0.32, 2, "", "T" ,0, "C");
	
	$ticket->SetXY(7+66*0.75, $ticket->GetY());
	$ticket->Cell(66*0.25, 2, "", "T" ,0, "C");
	$ticket->SetY($ticket->GetY()-5);
	
	if($total != $subtotal) {
		$ticket->SetXY(7+66*0.4, $ticket->GetY()+5);
		$ticket->Cell(66*0.3, 6, utf8_decode("Subtotal"), "" ,0, "L");
		 
		$ticket->SetXY(7+66*0.75, $ticket->GetY());
		$ticket->Cell(66*0.25, 6, "$ " . number_format($subtotal, 2), "" ,0, "R");
	
		$ticket->SetXY(7+66*0.4, $ticket->GetY()+5);
		$ticket->Cell(66*0.3, 6, utf8_decode("Descuento"), "" ,0, "L");
	
		$ticket->SetXY(7+66*0.75, $ticket->GetY());
		$ticket->Cell(66*0.25, 6, "$ " . number_format($descuento, 2), "" ,0, "R");
	}
	
	$ticket->SetXY(7+66*0.4, $ticket->GetY()+5);
	$ticket->Cell(66*0.3, 6, utf8_decode("Total"), "" ,0, "L");
	
	$ticket->SetXY(7+66*0.75, $ticket->GetY());
	$ticket->Cell(66*0.25, 6, "$ " . number_format($total, 2), "" ,0, "R");
	
	/*$ticket->SetY($ticket->GetY()+4);
	$ticket->SetXY(7+66*0.40, $ticket->GetY()+3);
	$ticket->Cell(66*0.32, 2, "", "T" ,0, "C");
	
	$ticket->SetXY(7+66*0.75, $ticket->GetY());
	$ticket->Cell(66*0.25, 2, "", "T" ,0, "C");
	$ticket->SetY($ticket->GetY()-5);*/
	
	if (count($pagos)) {
		$ticket->SetXY(7+66*0.4, $ticket->GetY()+5);
		$ticket->Cell(66*0.3, 6, utf8_decode("Forma de pago"), "" ,0, "L");
			
		$ticket->SetXY(7+66*0.75, $ticket->GetY());
		$ticket->Cell(66*0.25, 6, utf8_decode("Monto"), "" ,0, "R");
		
		$ticket->SetY($ticket->GetY()+3);
		$ticket->SetXY(7+66*0.40, $ticket->GetY()+3);
		$ticket->Cell(66*0.32, 2, "", "T" ,0, "C");
		
		$ticket->SetXY(7+66*0.75, $ticket->GetY());
		$ticket->Cell(66*0.25, 2, "", "T" ,0, "C");
		$ticket->SetY($ticket->GetY()-5);
		
		foreach ($pagos as $pago) {
			$ticket->SetXY(7+66*0.4, $ticket->GetY()+5);
			$ticket->Cell(66*0.3, 6, utf8_decode("{$pago["nombre"]}"), "" ,0, "L");
			
			$ticket->SetXY(7+66*0.75, $ticket->GetY());
			$ticket->Cell(66*0.25, 6, "$ " . number_format($pago["monto"], 2), "" ,0, "R");
		}
		
		$ticket->SetXY(7+66*0.4, $ticket->GetY()+5);
		$ticket->Cell(66*0.3, 6, utf8_decode("Total"), "" ,0, "L");
			
		$ticket->SetXY(7+66*0.75, $ticket->GetY());
		$ticket->Cell(66*0.25, 6, "$ " . number_format($total_pagos, 2), "" ,0, "R");

		$resta = $total - $total_pagos;
		$ticket->SetXY(7+66*0.4, $ticket->GetY()+6);
	    $ticket->Cell(66*0.25, 6, utf8_decode("Resta"), "" ,0, "L");
	    
	    $ticket->SetXY(20+66*0.75, $ticket->GetY());
	    $ticket->Cell(66*0.25, 6, "$ " . number_format($resta, 2), "" ,0, "R");
		}
	
	if($pagado == 0) {
		$ticket->SetXY(7, $ticket->GetY()+8);
		$ticket->Cell(66, 6, utf8_decode("Para cualquier aclaración, presentar su ticket."), "" ,0, "C");

		
	} else {
		$ticket->SetXY(7, $ticket->GetY()+8);
		$ticket->Cell(66, 6, utf8_decode("FOLIO APARTADO: ".$folioA), "", 0, 'C');
		 
		$ticket->SetXY(7, $ticket->GetY()+5);
		$ticket->Cell(66, 6, utf8_decode("Fecha límite para recoger y liquidar sus apartados"), "", 0, 'C');
	
		$ticket->SetXY(7, $ticket->GetY()+5);
		$ticket->Cell(66, 6, utf8_decode("10 de Diciembre."), "", 0, 'C');
	
		$ticket->SetXY(7, $ticket->GetY()+5);
		$ticket->Cell(66, 6, utf8_decode("En apartados NO hay cambios NI devoluciones."), "", 0, 'C');
  
	}


    


   $ticket->Output("../../cache/ticket/ticket_".$user_sucursal."_" . date("YmdHis") . "_" . strtolower($tipofolio) . "_" . $folio . "_1.pdf", "F");
   $ticket->Output("../../cache/ticket/ticket_".$user_sucursal."_" . date("YmdHis") . "_" . strtolower($tipofolio) . "_" . $folio . "_2.pdf", "F");
       
     //header ("location: ../index.php?scr=home"); 

		

?>