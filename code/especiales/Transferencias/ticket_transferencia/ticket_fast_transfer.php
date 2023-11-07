<?php
//extraemos indicador de tipo de ticket
   $flag=$_GET['fl'];
   $id_transfer=$_GET['id_transf'];
   $boxes_limit = $_GET['limit'];
   $limit_counter = $_GET['limit_counter'];
//incluimos librerias
   include("../../../../conectMin.php");
   include( "../../../../conexionMysqli.php" );
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

	$sql = "SELECT
					t.titulo_transferencia AS transfer_title,
					t.folio AS transfer_folio,
					alm1.nombre AS origin_warehouse,
					alm2.nombre AS destinity_warehouse,
					CONCAT( u.nombre,' ',u.apellido_paterno,' ',u.apellido_materno ) AS user_name,
					CONCAT( t.fecha, ' ', t.hora ) AS creation_date,
					t.ultima_actualizacion AS last_update_time
			FROM ec_transferencias t
			LEFT JOIN ec_almacen alm1
			ON t.id_almacen_origen = alm1.id_almacen
			LEFT JOIN ec_almacen alm2
			ON t.id_almacen_destino = alm2.id_almacen
			LEFT JOIN sys_users u ON t.id_usuario = u.id_usuario
			WHERE t.id_transferencia = {$id_transfer}";
//die( $sql );
	$stm = $link->query( $sql ) or die( "Error al consultar los datos de la transferencia : {$link->error}" );
//die( 'ok' );
	$row = $stm->fetch_assoc();
//die( 'here : ' . $row['user_name'] );
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
	
	
	$ticket = new TicketPDF("P", "mm", array(80,(90+$lineas_productos*15+30+10)), "{$sucursal}", "{$folio}", 10);/*por 9*/
	
	$ticket->AliasNbPages();
	$ticket->AddPage();
	$bF=10;
/*	if($impresa==1){
		$ticket->SetFont('Arial','B',$bF+6);
		$ticket->SetXY(5, $ticket->GetY()+4);
		$ticket->Cell(66, 6, utf8_decode("REIMPRESIÓN"), "" ,0, "C");
	}

	transfer_title
	transfer_folio
	origin_warehouse
	destinity_warehouse
	user_name
	creation_date
	last_update_time
*/
//encabezado
	$ticket->SetFont('Arial','B',$bF+2);
	if($impresa==1){
		$ticket->SetXY(5, $ticket->GetY()+5);
	}else{
		$ticket->SetXY(5, $ticket->GetY()+5);
	}
//titulo de transferencia
	$ticket->SetXY(5, $ticket->GetY()+5);
	$ticket->Cell(70, 6, utf8_decode( "TÍTULO : " ), "" ,0, "C");
	$ticket->SetXY(5, $ticket->GetY()+5);
	$ticket->Cell(70, 6, utf8_decode( $row['transfer_title'] ), "" ,0, "C");
	
	/*$ticket->SetXY(5, $ticket->GetY()+6);
	$ticket->Cell(66, 3, "", "TB" ,0, "C");*/
//folio
	$ticket->SetFont('Arial','B',$bF);
	$ticket->SetXY(3, $ticket->GetY()+7);
	$ticket->Cell(70, 6, utf8_decode("FOLIO: " . $row['transfer_folio'] ), "" ,0, "C");

//almacen origen
	$ticket->SetFont('Arial','B',$bF);
	$ticket->SetXY(3, $ticket->GetY()+7);
	$ticket->Cell(70, 6, utf8_decode("DE :".$row['origin_warehouse'] ), "" ,0, "C"); 
//almacen destino
	$ticket->SetFont('Arial','B',$bF);
	$ticket->SetXY(3, $ticket->GetY()+7);
	$ticket->Cell(70, 6, utf8_decode("A :".$row['destinity_warehouse'] ), "" ,0, "C"); 

//nota
	$ticket->SetFont('Arial','B',$bF);
	$ticket->SetXY(3, $ticket->GetY()+7);
	$ticket->Cell(70, 6, utf8_decode("NOTA : No pegar cinta sobre" ), "" ,0, "C");
	$ticket->SetFont('Arial','B',$bF);
	$ticket->SetXY(3, $ticket->GetY()+4);
	$ticket->Cell(70, 6, utf8_decode("el código de barras" ), "" ,0, "C");

//
	include('../../../../include/barcode/barcode.php');
	$row['transfer_folio'] = str_replace(' ', '', $row['transfer_folio'] );
	$barcodePath = "../../../../img/codigos_barra/{$row['transfer_folio']}.png";
	barcode( $barcodePath, $row['transfer_folio'], '60', 'horizontal', 'code128', false, 1);

  	if( file_exists("../../../../img/codigos_barra/{$row['transfer_folio']}.png") ){
    	$ticket->SetXY(5, $ticket->GetY()+10);
    	$ticket->Image("../../../../img/codigos_barra/{$row['transfer_folio']}.png", 5, $ticket->GetY()+5,70);
   }
//

//numero de caja
	$ticket->SetFont('Arial','B',$bF + 5);
	$ticket->SetXY(3, $ticket->GetY()+30);
	$ticket->Cell(70, 6, utf8_decode("Caja {$limit_counter} de {$boxes_limit}" ), "" ,0, "C");


//fecha, hora de creacion
	$ticket->SetFont('Arial','B',$bF);
	$ticket->SetXY( 3, $ticket->GetY()+7 );
	$ticket->Cell( 33, 6, utf8_decode( "CREACION : " ), "" ,0, "C");
	$ticket->SetXY( 34, $ticket->GetY() );
	$ticket->Cell( 33, 6, utf8_decode( "AUTORIZACIÓN :" ), "" ,0, "C");

//fecha, hora de creacion
	$ticket->SetFont('Arial','B',$bF-2);
	$ticket->SetXY( 3, $ticket->GetY()+7 );
	$ticket->Cell( 33, 6, utf8_decode( $row['creation_date'] ), "" ,0, "C");
	$ticket->SetXY( 34, $ticket->GetY() );
	$ticket->Cell( 33, 6, utf8_decode( $row['last_update_time'] ), "" ,0, "C");

//Usuario de la remisión
	$ticket->SetFont('Arial','B',$bF);
	$ticket->SetXY( 3, $ticket->GetY()+4 );
	//$ticket->SetFont('Arial','B',$bF);
	$ticket->SetXY(3, $ticket->GetY()+4);
	$ticket->Cell(70, 6, utf8_decode("Creada por : " . $row['user_name'] ), "" ,0, "C"); 
	
	$contador=0;

	
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
    		$nombre_ticket="ticket_".$user_sucursal."_" . date("YmdHis") . "_" . strtolower($tipofolio) . "_" . $folio . "_".$limit_counter.".pdf";
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
    	/*if(isset($_GET['num_tickets'])){
    		for($j=0;$j<$_GET['num_tickets'];$j++){
				$ticket->Output("../../../../cache/ticket/".$j."_".$nombre_ticket, "F");    			
    		}
    	}else{*/
    	  	$ticket->Output("../../../../cache/ticket/".$nombre_ticket, "F");
    	//}
    /*fin de cambio Oscar 25.01.2019*/
    	}
/*fin de cambio Oscar 17.09.2018*/
	if($impresa==0){mysql_query("COMMIT");}
    echo 'ok|'.$nombre_ticket;
       //$ticket->Output("../../cache/ticket/ticket_".$user_sucursal."_" . date("YmdHis") . "_" . strtolower($tipofolio) . "_" . $folio . "_2.pdf", "F");
     //  header ("location: index.php?scr=home"); 
    }

	//exit (0);

?>