<?php
//extraemos indicador de tipo de ticket
   $flag=$_GET['fl'];
   $id_transfer=$_GET['id_transf'];
//incluimos librerias
    include("../../../../conectMin.php");
	define('FPDF_FONTPATH','../../../../include/fpdf153/font/');
	include("../../../../include/fpdf153/fpdf.php");
/*Extraemos rutas de tickets*/
	$archivo_path = "../../../../conexion_inicial.txt";
	if(file_exists($archivo_path)){
		$file = fopen($archivo_path,"r");
		$line=fgets($file);
		fclose($file);
	    $config=explode("<>",$line);
	    $tmp=explode("~",$config[2]);
	    $ruta_or=$tmp[0];
	    $ruta_des=$tmp[1];
	}else{
		die("No hay archivo de configuración!!!");
	}
//consultamos datos de la transferencias
	$sql="SELECT 
			/*0*/CONCAT(u.nombre,' ',u.apellido_paterno),
			/*1*/CONCAT('TICKET DE ',IF(t.es_resolucion=1,'RESOLUCIÓN','TRANSFERENCIA')),
			/*2*/CONCAT(t.fecha,' ',t.hora),
			/*3*/NOW(),
			/*4*/s.nombre as origen,
			/*5*/s_1.nombre as destino,
			/*6*/t.folio,
			/*7*/t.impresa,
			/*8*/IF(t.es_resolucion=1,t.observaciones,0) as folio_depende,
			/*9*/t.es_resolucion
		FROM ec_transferencias t
		JOIN sys_sucursales s ON t.id_sucursal_origen=s.id_sucursal
		JOIN sys_sucursales s_1 ON t.id_sucursal_destino=s_1.id_sucursal
		JOIN sys_users u ON t.id_usuario=u.id_usuario
		WHERE t.id_transferencia=$id_transfer";
	$eje=mysql_query($sql)or die("Error al consultar los datos iniciales de la transferencia!!!\n\n".mysql_error()."\n\n".$sql);
	$r=mysql_fetch_row($eje);

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
	
	
	$ticket = new TicketPDF("P", "mm", array(80,(10+$lineas_productos*15+30+10)), "{$sucursal}", "{$folio}", 10);/*por 9*/
	$ticket->AliasNbPages();
	$ticket->AddPage();
	$bF=10;
	if($impresa==1){
		$ticket->SetFont('Arial','B',$bF+6);
		$ticket->SetXY(5, $ticket->GetY()+4);
		$ticket->Cell(66, 6, utf8_decode("REIMPRESIÓN"), "" ,0, "C");
	}
//encabezado
	$ticket->SetFont('Arial','B',$bF+4);
	if($impresa==1){
		$ticket->SetXY(5, $ticket->GetY()+5);
	}else{
		$ticket->SetXY(5, $ticket->GetY()+5);
	}
	$ticket->Cell(66, 6, utf8_decode($encabezado), "" ,0, "C");
	
	$ticket->SetXY(5, $ticket->GetY()+6);
	$ticket->Cell(66, 3, "", "TB" ,0, "C");
  //folio
	$ticket->SetFont('Arial','B',$bF);
	$ticket->SetXY(3, $ticket->GetY()+4);
	$ticket->Cell(66, 6, utf8_decode("FOLIO: ".$folio_tr), "" ,0, "L");
  //fecha, hora
	$ticket->SetFont('Arial','B',$bF);
	$ticket->SetXY(3, $ticket->GetY()+4);
	$ticket->Cell(66, 6, utf8_decode("FECHA: ".$fecha_trans), "" ,0, "L");
  //sucursales
	$ticket->SetFont('Arial','B',$bF);
	$ticket->SetXY(3, $ticket->GetY()+4);
	$ticket->Cell(66, 6, utf8_decode("DE :".$sucursal_origen."    A: ".$sucursal_destino), "" ,0, "L"); 
//Usuario de la remisión
	$ticket->SetFont('Arial','B',$bF);
	$ticket->SetXY(3, $ticket->GetY()+4);
	$ticket->Cell(66, 6, utf8_decode($usuario_emision), "" ,0, "L");  

	$ticket->SetXY(5, $ticket->GetY()+5.5);
	$ticket->Cell(66, 3, "", "TB" ,0, "C");
	
	$ticket->SetFont('Arial','',$bF);
/*implementación Oscar 25.04.2019*/
	$ticket->SetXY(5, $ticket->GetY()+3);
	$ticket->Cell(66*0.12, 9, utf8_decode("#"), "B" ,0, "C");
/*Fin de cambio Oscar 25.04.2019*/

	$ticket->SetXY(13, $ticket->GetY()+3);
	$ticket->Cell(66*0.75, 6, utf8_decode("DESCRIPCIÓN"), "B" ,0, "L");
	
	$ticket->SetXY(5+66*0.63, $ticket->GetY());
	$ticket->Cell(66*0.12, 6, utf8_decode(""), "B" ,0, "L");
	
	$ticket->SetXY(5+66*0.75, $ticket->GetY());
	$ticket->Cell(66*0.25, 6, utf8_decode("CANT"), "B" ,0, "R");
	
	$ticket->SetFont('Arial','',$bF-2);
	$ticket->SetXY(5, $ticket->GetY()+8);
	
	$contador=0;
if($es_resolucion_transf==0){
	foreach ($productos as $producto) {
		
		$contador++;

	    $y = $ticket->GetY();	
		$ticket->SetXY(5+66*0.75, $y);
		$ticket->MultiCell(66*0.25, 4,$producto["cantidad"], "", "R", false);
	
		$ticket->SetXY(5+66*0.75, $y);
		$ticket->MultiCell(66*0.12, 4, $producto[""], "", "C", false);
		
		$ticket->SetXY(5, $y);
		$ticket->MultiCell(66*0.12, 4, utf8_decode($contador), "", "C", false);
		//$ticket->Cell(66*0.08, 9, , "B" ,0, "L");

		$ticket->SetXY(13, $y);
		$ticket->MultiCell(66*0.75, 4, utf8_decode("{$producto["producto"]}"), "", "L", false);

		if($producto['clave_ubic']!=''){
			$ticket->SetFont('Arial','',$bF-3.5);
			$ticket->SetXY(13,($ticket->GetY()-1.5));
			$ticket->MultiCell(66*0.75, 4, utf8_decode("{$producto["clave_ubic"]}"), "", "L", false);
			$ticket->SetFont('Arial','',$bF-2);
		}
	
	}//fin de foreach
}else if($es_resolucion_transf==1){

	foreach ($productos as $producto) {
		
		$contador++;

	    $y = $ticket->GetY();

	    if($contador>1){$y=($y+10);}

		$ticket->SetXY(5+66*0.75, $y);
		$ticket->MultiCell(66*0.25, 4,$producto["cantidad"], "", "R", false);
	
		$ticket->SetXY(5+66*0.75, $y);
		$ticket->MultiCell(66*0.12, 4, $producto[""], "", "C", false);
		
		$ticket->SetXY(5, $y);
		$ticket->MultiCell(66*0.12, 4, utf8_decode($contador), "", "C", false);
		//$ticket->Cell(66*0.08, 9, , "B" ,0, "L");

		$ticket->SetXY(13, $y);
		$ticket->MultiCell(66*0.75, 4, utf8_decode("{$producto["producto"]}"), "", "L", false);

		if($producto['clave_ubic']!=''){
			$ticket->SetFont('Arial','',$bF-3.5);
			$ticket->SetXY(13,($ticket->GetY()-1.5));
			$ticket->MultiCell(66*0.75, 4, utf8_decode("{$producto["clave_ubic"]}"), "", "L", false);
			$ticket->SetFont('Arial','',$bF-2);
		}
	    $y = $ticket->GetY();

		$ticket->SetXY(10, $y+1);
		$ticket->Cell(20, 5, utf8_decode("Se queda"), 1, 0, 'C', false);

		$ticket->SetXY(30, $y+1);
		$ticket->Cell(20, 5, utf8_decode("Faltante"), 1, 0, 'C', false);
		
		$ticket->SetXY(50, $y+1);
		$ticket->Cell(20, 5, utf8_decode("Se regresa"), 1, 0, 'C', false);	    

		$y = $ticket->GetY()+5;

		$ticket->SetXY(10, $y);
		$ticket->Cell(20, 5, utf8_decode("{$producto["se_queda"]}"), 1, 0, 'C', false);

		$ticket->SetXY(30, $y);
		$ticket->Cell(20, 5, utf8_decode("{$producto["faltante"]}"), 1, 0, 'C', false);
		
		$ticket->SetXY(50, $y);
		$ticket->Cell(20, 5, utf8_decode("{$producto["se_regresa"]}"), 1, 0, 'C', false);
	}//fin de foreach
}
	
    if($printPan == 1) {
	   $ticket->Output();
    }else{
/*actualizamos la transferencia como impresa*/
	if($impresa==0){
		mysql_query("BEGIN");
		$sql="UPDATE ec_transferencias SET impresa=1 WHERE id_transferencia=$id_transfer";
		$eje=mysql_query($sql)or die("Error al actualizar la transferencia como impresa\n\n".mysql_error()."\n\n".$sql);
	}
/**/
/*implementación Oscar 17.09.2018 para impresión de tickets de acuerdo a la configuración de la sucursal*/
    	$sql="SELECT no_tickets_resolucion FROM ec_configuracion_sucursal WHERE id_sucursal=$user_sucursal";
    	$eje=mysql_query($sql)or die("Error al consultar número de impresiones!!!\n\n".$sql."\n\n".mysql_error());	
    	$numero=mysql_fetch_row($eje);
    	for($cont=1;$cont<=1;$cont++){
    		$nombre_ticket="ticket_".$user_sucursal."_" . date("YmdHis") . "_" . strtolower($tipofolio) . "_" . $folio . "_".$cont.".pdf";
    /*implementación Oscar 25.01.2019 para la sincronización de tickets*/
    		if($user_tipo_sistema=='linea'){
    			$sql_arch="INSERT INTO sys_archivos_descarga SET 
    					id_archivo=null,
    					tipo_archivo='pdf',
    					nombre_archivo='$nombre_ticket',
    					ruta_origen='$ruta_or',
    					ruta_destino='$ruta_des',
    					id_sucursal='$user_sucursal',
    					id_usuario='$user_id',
    					observaciones=''";
    			$inserta_reg_arch=mysql_query($sql_arch)or die("Error al guardar el registro de sincronización del ticket de reimpresión!!!\n\n".mysql_error()."\n\n".$sql_arch);

    		}
    	  	$ticket->Output("../../../../cache/ticket/".$nombre_ticket, "F");
    	}
	if($impresa==0){mysql_query("COMMIT");}
    	echo 'ok|'.$nombre_ticket;
    }

?>