<?php
    #header("Content-Type: text/plain;charset=utf-8");
    
    define('FPDF_FONTPATH','../include/fpdf153/font/');
    
    include("../include/fpdf153/fpdf.php");
    
    $id_pedido = $_GET["idp"];  
    $sucursal = "";
    $folio = "";
    $prefijo = "";
    $subtotal = "0";
    $total = "0";
    $productos = array();
    $vendedor = "N/A";
    $lineas_productos = 0;
    $tipofolio = "PEDIDO";
    
    $cs = "SELECT CONCAT(nombre, ' ', apellido_paterno) AS vendedor FROM sys_users WHERE id_usuario = '{$user_id}' ";
    if ($rs = mysql_query($cs)) {
        if ($dr = mysql_fetch_assoc($rs)) {
            $vendedor = $dr["vendedor"];
        } mysql_free_result($rs);
    }
    
    
    $cs = "SELECT nombre AS sucursal, prefijo FROM sys_sucursales WHERE id_sucursal = '{$user_sucursal}' ";
    if ($rs = mysql_query($cs)) {
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
    if ($rs = mysql_query($cs))
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
    }
    
    $cs = "SELECT P.id_productos AS id_producto, P.nombre AS producto, PD.cantidad, PD.precio, PD.monto  FROM ec_productos P " .
        "INNER JOIN ec_pedidos_detalle PD ON PD.id_producto = P.id_productos " .
        "WHERE PD.id_pedido = '{$id_pedido}' ";
    if ($rs = mysql_query($cs)) {
        while ($dr = mysql_fetch_assoc($rs)) {
            $lineas_productos += ceil(strlen($dr["producto"])/35.0);
            array_push($productos, $dr);
        } mysql_free_result($rs);
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
            $this->SetMargins(15, 0, 15);
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
    
    
    $ticket = new TicketPDF("P", "mm", array(235,540+$lineas_productos*15+($total!=$subtotal?40:0)), "{$sucursal}", "{$folio}", 32);
    $ticket->AliasNbPages();
    $ticket->AddPage();
    
    $ticket->Image("img/logo-casa-fondo-blanco.png", 29.5, 5, 150);
    
    $ticket->SetFont('Arial','B',45);
    $ticket->SetXY(15, 260);
    $ticket->Cell(187, 12, utf8_decode("CASA DE LAS LUCES"), "" ,0, "C");
        
    $ticket->SetFont('Arial','B',35);
    $ticket->SetXY(15, $ticket->GetY()+15);
    $ticket->Cell(187, 15, utf8_decode("Sucursal {$ticket->sucursal}"), "" ,0, "C");
        
    $ticket->SetFont('Arial','B',40);
    
    $ticket->SetXY(20, $ticket->GetY()+20);
    $ticket->Cell(80, 15, utf8_decode("{$tipofolio}"), "" ,0, "C");
        
    $ticket->SetFont('Arial','B',35);
    $ticket->SetX(120.5);
    $ticket->Cell(70, 15, utf8_decode("{$ticket->pedido}"), "" ,0, "C");
        
    $ticket->SetXY(20, $ticket->GetY()+18);
    $ticket->Cell(175, 15, "", "TB" ,0, "C");
    
    $ticket->SetXY(20, $ticket->GetY() + 18);
    $ticket->SetFont('Arial','B',32);
    $ticket->Cell(177, 15, utf8_decode("FECHA Y HORA DE EMISIÓN:"), "" ,0, "C");
    
    $ticket->SetXY(20, $ticket->GetY() + 15);
    $ticket->Cell(177, 15, utf8_decode(date("d/m/Y H:i:s")), "" ,0, "C");
    
    $ticket->SetFont('Arial','',25);
    
    $ticket->SetXY(20, $ticket->GetY() + 15);
    $ticket->Cell(80, 15, utf8_decode("VENDEDOR:  {$vendedor}"), "" ,0, "L");
    
    $ticket->SetXY(20, $ticket->GetY()+20);
    $ticket->Cell(177, 10, "", "TB" ,0, "C");
    
    $ticket->SetFont('Arial','',25);
    
    $ticket->SetXY(20, $ticket->GetY() + 20);
    $ticket->Cell(80, 15, utf8_decode("DESCRIPCIÓN"), "B" ,0, "L");
    $ticket->SetX(100);
    $ticket->Cell(97, 15, utf8_decode("PRECIO"), "B" ,0, "R");
    
    $ticket->SetFont('Arial','',20);
    $ticket->SetXY(20, $ticket->GetY() + 20);
    
    foreach ($productos as $producto)
    {
        $y = $ticket->GetY();
        $ticket->SetXY(162, $y);
        $ticket->MultiCell(35, 15, "$ " . number_format($producto["monto"], 2), "", "R", false);
        $ticket->SetXY(20, $y);
        $ticket->MultiCell(145, 15, utf8_decode("{$producto["producto"]}"), "", "L", false);
    }

    $ticket->SetXY(100, $ticket->GetY()+15);
    $ticket->Cell(97, 10, "", "T" ,0, "C");
    
    if($total != $subtotal)
    {
    
        $ticket->SetXY(103.5, $ticket->GetY() + 15);
        $ticket->Cell(50, 15, utf8_decode("Subtotal"), "" ,0, "L");
        
        $ticket->SetXY(103.5, $ticket->GetY());
        $ticket->Cell(50, 15, "$ " . number_format($subtotal, 2), "" ,0, "R");
        
        
        $ticket->SetXY(103.5, $ticket->GetY() + 15);
        $ticket->Cell(50, 15, utf8_decode("Descuento"), "" ,0, "L");
    
        $ticket->SetXY(103.5, $ticket->GetY());
        $ticket->Cell(50, 15, "$ " . number_format($descuento, 2), "" ,0, "R");
        
    }
    
        
    
    /*$ticket->SetXY(103.5, $ticket->GetY() + 7);
    $ticket->Cell(50, 6, utf8_decode("IVA"), "" ,0, "L");
    
    $ticket->SetXY(103.5, $ticket->GetY());
    $ticket->Cell(50, 6, "$ " . number_format(0, 2), "" ,0, "R");*/
    
    $ticket->SetXY(103.5, $ticket->GetY() + 15);
    $ticket->Cell(50, 15, utf8_decode("Total"), "" ,0, "L");
    
    $ticket->SetXY(100, $ticket->GetY());
    $ticket->Cell(97, 15, "$ " . number_format($total, 2), "" ,0, "R");
    
    if($pagado == 1)
    {
    
       $ticket->SetXY(20, $ticket->GetY() + 20);
       $ticket->Cell(177, 15, utf8_decode("Para cualquier aclaración, presentar su ticket."), "" ,0, "C");
    }
    else
    {
        $ticket->SetXY(20, $ticket->GetY() + 15);
        $ticket->Cell(177, 15, utf8_decode("FOLIO APARTADO: ".$folioA), "", 0, 'C');
        
        $ticket->SetXY(20, $ticket->GetY() + 20);
        //$ticket->Cell(80, 6, utf8_decode("Fecha límite para recoger y y liquidar sus apartados 10 de Diciembre"), "" ,0, "L");
        $ticket->Cell(177, 12, utf8_decode("Fecha límite para recoger y liquidar sus apartados"), "", 0, 'C');
        
        $ticket->SetXY(20, $ticket->GetY() + 12);
        $ticket->Cell(177, 12, utf8_decode("10 de Diciembre."), "", 0, 'C');
        
        $ticket->SetXY(20, $ticket->GetY() + 12);
        $ticket->Cell(177, 12, utf8_decode("No hay cambios ni devoluciones."), "", 0, 'C');
    }   
    
    #$_SERVER["DOCUMENT_ROOT"] . dirname($_SERVER["PHP_SELF"])
    
    
    $ticket->Output();
    
    /*$ticket->Output("../cache/tickets/ticket_" . date("YmdHis") . "_" . strtolower($tipofolio) . "_" . $folio . ".pdf", "F");
    
    header ("location: index.php?scr=home");
        
    exit (0);*/
?>