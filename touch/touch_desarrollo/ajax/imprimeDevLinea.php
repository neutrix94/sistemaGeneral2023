<?php

    #header("Content-Type: text/plain;charset=utf-8");
    
    //include("../../conectMin.php");

    mysql_set_charset("utf8");
    if (function_exists("mb_internal_encoding")) mb_internal_encoding ('utf-8');
    
    define('FPDF_FONTPATH','../../include/fpdf153/font/');
    
    include("../../include/fpdf153/fpdf.php");
    
    $totReal=0;
    
    extract($_GET);
    
    
    if(!isset($_GET["noImp"]))
        $_GET["noImp"]=1;
    
    //$id_dev = $_GET["id_dev"];  
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
           IF(ISNULL(folio),
           folio, folio) AS folio,
           IF(ISNULL(folio), 'DEVOLUCION', 'DEVOLUCION') AS tipofolio,
           (SELECT If(SUM(monto) IS NULL, 0, SUM(monto)) FROM ec_devolucion_pagos WHERE id_devolucion=ec_devolucion.id_devolucion) AS subtotal,
           0 AS iva,
           0 AS ieps,
           (SELECT If(SUM(monto) IS NULL, 0, SUM(monto)) FROM ec_devolucion_pagos WHERE id_devolucion=ec_devolucion.id_devolucion) AS total,
           0 AS descuento,
           1 AS pagado,
           '' as folioA
           FROM ec_devolucion
           WHERE id_devolucion = '{$id_dev}' ";
           
      //die($cs);           
    if ($rs = mysql_query($cs,$local))
    {
        if ($dr = mysql_fetch_assoc($rs))
        {
            /*print_r($dr);
            die();*/    
            
            $tipofolio = $dr["tipofolio"];
            $folio = $dr["folio"];
            $total = $dr["total"];
            $subtotal = $dr["subtotal"];
            $total = $dr["total"];
            $pagado = $dr["pagado"];
            $descuento = $dr["descuento"];
            $folioA = "A$prefijo".$dr["folioA"];
        } mysql_free_result($rs);
    }
    
    $cs = "SELECT
           P.id_productos AS id_producto,
           P.nombre AS producto,
           PD.cantidad AS cantidad,
           dp.precio AS precio,
           (PD.cantidad*dp.precio)-((PD.cantidad*dp.precio)*(IF(pe.descuento=0,0,(pe.descuento*100/pe.subtotal))/100)) AS monto,
           /*dp.descuento AS desc_prod,*/
           IF(dp.descuento=0,0,dp.descuento/dp.cantidad) AS descuento,
           IF(pe.descuento<=0,0,pe.descuento) AS porc_desc
           FROM ec_productos P
           INNER JOIN ec_devolucion_detalle PD ON PD.id_producto = P.id_productos
           INNER JOIN ec_devolucion d  ON PD.id_devolucion = d.id_devolucion
           INNER JOIN ec_pedidos_detalle dp  ON d.id_pedido = dp.id_pedido AND dp.id_producto = PD.id_producto
           LEFT JOIN ec_pedidos pe ON pe.id_pedido=dp.id_pedido
           WHERE PD.id_devolucion = '{$id_dev}' ";

           
   // die($cs);
    if ($rs = mysql_query($cs,$local))
    {
        while ($dr = mysql_fetch_assoc($rs))
        {
            // Concatenar precio unitario en la descripcion
            $dr["producto"] .= " \${$dr["precio"]}";
        //concatenación de Descuento
            if($dr["porc_desc"]>0 && $dr["descuento"]==0){
               $dr["producto"].=" Descuento: -\$".round(($dr["producto"]*$dr["cantidad"])-$dr["monto"],2);
            }
            if($dr["descuento"]>0){
                $dr["monto"]=($dr["precio"]-$dr["descuento"])*$dr["cantidad"];
                $dr["producto"].=" Descuento: -\$".round($dr["descuento"]*$dr["cantidad"],2);
                //echo "(".$dr["precio"]."-".$dr["descuento"].")"."*".$dr["cantidad"].'='.$dr["monto"]."\n\n";
            }
            $lineas_productos += ceil(strlen($dr["producto"])/32.0);

            array_push($productos, $dr);
        }
        mysql_free_result($rs);
    }
    
    
    class TicketPDF extends FPDF {
        // Members
        var $sucursal = "";
        var $pedido = "";
        var $inicio = 32;
    
        // Constructor
        function TicketPDF($orientation='P', $unit='mm', $size, $sucursal='', $pedido='', $inicio=32) {
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

	$bF=10;
    
    $ticket = new TicketPDF("P", "mm", array(80,129+$lineas_productos*6+($total!=$subtotal?12:0)), "{$sucursal}", "{$folio}", 32);
    $ticket->AliasNbPages();
    $ticket->AddPage();
    
    $ticket->Image("../img/logo-casa-fondo-blanco.png", 28, 5, 22);
	
	$ticket->SetFont('Arial','B',$bF+4);
	$ticket->SetXY(7, 40);
	$ticket->Cell(66, 6, utf8_decode("CASA DE LAS LUCES"), "" ,0, "C");
	
	$ticket->SetFont('Arial','',$bF+1);
	$ticket->SetXY(7, $ticket->GetY()+4);
	$ticket->Cell(66, 6, utf8_decode("Sucursal {$ticket->sucursal}"), "" ,0, "C");
	
	$ticket->SetFont('Arial','',$bF+2);
	
	$ticket->SetXY(7, $ticket->GetY()+4);
	$ticket->Cell(66*0.6, 6, utf8_decode("{$tipofolio}"), "" ,0, "C");
	
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
	
	$ticket->SetXY(7, $ticket->GetY()+6);
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
        $totReal+=$producto["monto"];
        
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
	
	
	$ticket->SetXY(7, $ticket->GetY()+6);
	//$ticket->SetXY(7, $ticket->GetY()+5);
	$ticket-> MultiCell(66,5, utf8_decode("Favor de revisar su producto, en esta mercancía no aplican cambios ni devoluciones"), 0 ,'J', false);
	
	
	
      $ar = fopen("../../leyenda_ticket/leyenda.txt","r") or die ('No se pudo abrir el archivo');

    while(!feof($ar))
    {
        $linea=fgets($ar);
        $lineasalto=nl2br($linea);
    }
    fclose($ar);
    $acotado = substr($linea,0,165);
    $ticket->SetXY(10, $ticket->GetY()+4);
    $ticket-> MultiCell(60,5, utf8_decode($acotado), 0 ,'J', false);
    #$ticket->Output();
    if($printPan == 1) {
       $ticket->Output();
    } else {
       $ticket->Output("../../cache/ticket/ticket_".$user_sucursal."_" . date("YmdHis") . "_" . strtolower($tipofolio) . "_devolucion_" . $folio . "_1.pdf", "F");
       //$ticket->Output("../../cache/ticket/ticket_".$user_sucursal."_" . date("YmdHis") . "_" . strtolower($tipofolio) . "_devolucion_" . $folio . "_2.pdf", "F");
       //header ("location: index.php?scr=home"); 
    }   
    
  echo 'finaliza archivo 1';      
   // exit (0);

?>