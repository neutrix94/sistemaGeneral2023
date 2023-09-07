<?php
//generación del código de barras
	include('include/barcode/barcode.php');
	$barcode_1 =  '00125 PQ8 17421 0006';
	//$barcode_1 = ( isset( $_POST['code'] ) ? trim( $_POST['code'] ) : ''  );
	$filepath="img/codigos_barra/barcode.png";
	barcode( $filepath, $barcode_1,'50','horizontal','code128',true,1);
	echo "<p>Código de barras generado</p>";

//generación del ticket
	define('FPDF_FONTPATH','include/fpdf153/font/');
	include("include/fpdf153/fpdf.php");
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
	
	$ticket = new TicketPDF("P", "mm", array(83,25), "{$sucursal}", "{$folio}", 10);
	$ticket->AliasNbPages();
	$ticket->AddPage();
	if(file_exists("img/codigos_barra/barcode.png")){
    	$ticket->SetXY(0, $ticket->GetY());
    	$ticket->Image("img/codigos_barra/barcode.png", 0, $ticket->GetY()+5,40);

    	$ticket->SetXY(0, $ticket->GetY());
    	$ticket->Image("img/codigos_barra/barcode.png", 43, $ticket->GetY()+5,40);
    }
   	$ticket->Output("./cache/ticket/etiqueta.pdf", "F");
	echo "<p>PDF generado</p>";
?>