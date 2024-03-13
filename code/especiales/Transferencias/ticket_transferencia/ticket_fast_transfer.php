<?php
//recibe variables
   $flag=$_GET['fl'];
   $id_transfer=$_GET['id_transf'];
   $boxes_limit = $_GET['limit'];
   $limit_counter = $_GET['limit_counter'];
//incluye librerias
   include("../../../../conectMin.php");
   include( "../../../../conexionMysqli.php" );
	define('FPDF_FONTPATH','../../../../include/fpdf153/font/');
	include("../../../../include/fpdf153/fpdf.php");
/*Extraemos rutas de tickets*/
	$carpeta_path = "";
	$archivo_path = "../../../../conexion_inicial.txt";
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

	$stm = $link->query( $sql ) or die( "Error al consultar los datos de la transferencia : {$link->error}" );

	$row = $stm->fetch_assoc();

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
	
	
	$ticket = new TicketPDF("P", "mm", array(80,(240+$lineas_productos*15+30+10)), "{$sucursal}", "{$folio}", 10);/*por 9*/
	
	$ticket->AliasNbPages();
	$ticket->AddPage();
	$bF=10;

//cinta superior
	$ticket->SetFont('Arial','B',$bF+2);
	$ticket->SetXY(5, 4);
	$ticket->Cell(66, 3, "", "TB" ,0, "C");
	
	$ticket->SetXY(5, $ticket->GetY()+30);
	$ticket->Cell(70, 6, utf8_decode( "PEGAR DIUREX AQUI : " ), "" ,0, "C");

	$ticket->SetXY(5, $ticket->GetY()+30);
	$ticket->SetXY(5, $ticket->GetY());
	$ticket->Cell(66, 3, "", "TB" ,0, "C");
//titulo de transferencia
	$ticket->SetFont('Arial','B',$bF+8);
	$ticket->SetXY(5, $ticket->GetY()+5);
		$ticket->Cell(70, 6, utf8_decode( "TÍTULO : " ), "" ,0, "C");
	if( strlen( $row['transfer_title'] ) > 25 ){
		$parts = part_word( $row['transfer_title'] );
		$ticket->SetXY(5, $ticket->GetY()+5);
		$ticket->Cell(70, 6, utf8_decode( $parts[0] ), "" ,0, "C");
		$ticket->SetXY(5, $ticket->GetY()+5);
		$ticket->Cell(70, 6, utf8_decode( $parts[1] ), "" ,0, "C");
	}else{
		$ticket->SetXY(5, $ticket->GetY()+5);
		$ticket->Cell(70, 6, utf8_decode( $row['transfer_title'] ), "" ,0, "C");
	}
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
	$row['transfer_folio'] = trim( $row['transfer_folio'] );// 
	$aux_name = str_replace(' ', '', $row['transfer_folio'] );
	$barcodePath = "../../../../img/codigos_barra/{$aux_name}.png";
	barcode( $barcodePath, $row['transfer_folio'], '60', 'horizontal', 'code128', false, 1);

  	if( file_exists("../../../../img/codigos_barra/{$aux_name}.png") ){
    	$ticket->SetXY(5, $ticket->GetY()+10);
    	$ticket->Image("../../../../img/codigos_barra/{$aux_name}.png", 5, $ticket->GetY()+5,70);
   }
//

//numero de caja
	$ticket->SetFont('Arial','B',$bF + 30);
	$ticket->SetXY(3, $ticket->GetY()+35);
	$ticket->Cell(70, 6, utf8_decode("CAJA" ), "" ,0, "C");
	$ticket->SetXY(3, $ticket->GetY()+15);
	$ticket->Cell(70, 6, utf8_decode("{$limit_counter} DE {$boxes_limit}" ), "" ,0, "C");


//fecha, hora de creacion
	$ticket->SetFont('Arial','B',$bF);
	$ticket->SetXY( 3, $ticket->GetY()+10 );
	$ticket->Cell( 33, 6, utf8_decode( "CREACION : " ), "" ,0, "C");
	$ticket->SetXY( 34, $ticket->GetY() );
	$ticket->Cell( 33, 6, utf8_decode( "AUTORIZACIÓN :" ), "" ,0, "C");

//fecha, hora de creacion
	$ticket->SetFont('Arial','B',$bF-2);
	$ticket->SetXY( 3, $ticket->GetY()+7 );
	$ticket->Cell( 33, 6, utf8_decode( $row['creation_date'] ), "" ,0, "C");
	$ticket->SetXY( 34, $ticket->GetY() );
	$ticket->Cell( 33, 6, utf8_decode( $row['last_update_time'] ), "" ,0, "C");

//Usuario
	$ticket->SetFont('Arial','B',$bF);
	$ticket->SetXY( 3, $ticket->GetY()+4 );
	//$ticket->SetFont('Arial','B',$bF);
	$ticket->SetXY(3, $ticket->GetY()+4);
	$ticket->Cell(70, 6, utf8_decode("Creada por : " . $row['user_name'] ), "" ,0, "C"); 
	

//cinta superior
	$ticket->SetFont('Arial','B',$bF+2);
	$ticket->SetXY(5, $ticket->GetY()+8);
	$ticket->Cell(66, 3, "", "TB" ,0, "C");
	
	$ticket->SetXY(5, $ticket->GetY()+30);
	$ticket->Cell(70, 6, utf8_decode( "PEGAR DIUREX AQUI : " ), "" ,0, "C");

	$ticket->SetXY(5, $ticket->GetY()+30);
	$ticket->SetXY(5, $ticket->GetY());
	$ticket->Cell(66, 3, "", "TB" ,0, "C");
//	$contador=0;

	
	if($printPan == 1) {
		$ticket->Output();
	}else{
/*actualizamos la transferencia como impresa*/
	if($impresa==0){
		//mysql_query("BEGIN");
		$sql="UPDATE ec_transferencias SET impresa=1 WHERE id_transferencia=$id_transfer";
		$eje=mysql_query($sql)or die("Error al actualizar la transferencia como impresa\n\n".mysql_error()."\n\n".$sql);
	}
/*implementacion Oscar 2024-02-01 para ruta especifica de ticket*/
    /*instancia clases*/
        include( '../../../../conexionMysqli.php' );
        include( '../../../../code/especiales/controladores/SysArchivosDescarga.php' );
        $SysArchivosDescarga = new SysArchivosDescarga( $link );
        include( '../../../../code/especiales/controladores/SysModulosImpresionUsuarios.php' );
        $SysModulosImpresionUsuarios = new SysModulosImpresionUsuarios( $link );
        include( '../../../../code/especiales/controladores/SysModulosImpresion.php' );
        $SysModulosImpresion = new SysModulosImpresion( $link );
/**/
/*implementación Oscar 17.09.2018 para impresión de tickets de acuerdo a la configuración de la sucursal*/
    	$sql="SELECT no_tickets_resolucion FROM ec_configuracion_sucursal WHERE id_sucursal=$user_sucursal";
    	$eje=mysql_query($sql)or die("Error al consultar número de impresiones!!!\n\n".$sql."\n\n".mysql_error());	
    	$numero=mysql_fetch_row($eje);
    	for($cont=1;$cont<=1;$cont++){
    		$nombre_ticket="ticket_".$user_sucursal."_" . date("YmdHis") . "_" . strtolower($tipofolio) . "_" . $folio . "_".$limit_counter.".pdf";

    		$ruta_salida = '';
      	$ruta_salida = $SysModulosImpresionUsuarios->obtener_ruta_modulo_usuario( $user_id, 11 );//Transferencias rapidas
			if( $ruta_salida == 'no' ){
				$ruta_salida = "cache/" . $SysModulosImpresion->obtener_ruta_modulo( $user_sucursal, 11 );//Transferencias rapidas
			}
			$ticket->Output( "../../../../{$ruta_salida}/{$nombre_ticket}", "F" );
		/*Sincronización remota de tickets*/
			if( $user_tipo_sistema == 'linea' ){/*registro sincronizacion impresion remota*/
				$registro_sincronizacion = $SysArchivosDescarga->crea_registros_sincronizacion_archivo( 'pdf', $nombre_ticket, $ruta_or, $ruta_salida, $user_sucursal, $user_id );
			}else{//impresion por red local
				$enviar_por_red = $SysArchivosDescarga->crea_registros_sincronizacion_archivo_por_red_local( 11, 'pdf', $nombre_ticket, '', $ruta_salida, $user_sucursal, $user_id, $carpeta_path );
			}
    	}
	if($impresa==0){
		//mysql_query("COMMIT");
	}
   	echo 'ok|'.$nombre_ticket;
   }
//funcion para partir texto en 2
	function part_word( $txt ){
		$size = strlen( $txt );
		$half = round( $size / 2 );
		$words = explode(' ', $txt );
		$resp = array( '','');
		$chars_counter = 0;
		$middle_word = "";
		foreach ($words as $key => $word) {
			$is_middle = 0;
			if( $key > 0 ){
				$chars_counter ++;//espacio
				if( $chars_counter == $half ){
					$is_middle = 1;
				}
			}
			for( $i = 0; $i < strlen( $word ); $i ++ ){
				$chars_counter ++;//palabras
				if( $chars_counter == $half || $is_middle == 1){
					$middle_word = $word;
					$is_middle = 1;
				}
			}
			if( $middle_word == '' ){
				$resp[0] .= ( $resp[0] != '' ? ' ' : '' );
				$resp[0] .= $word;
			}else if( $middle_word != '' && $is_middle == 0 ){
				$resp[1] .= ( $resp[1] != '' ? ' ' : '' );
				$resp[1] .= $word;
			}
			$is_middle = 0;
		}
		if( strlen( "{$resp[0]} {$middle_word}" ) < strlen( "{$middle_word} {$resp[1]}" )  ){//asigna palabra intermedia a primera parte
			$resp[0] = "{$resp[0]} {$middle_word}";
		}else{//asigna palabra intermedia a segunda parte
			$resp[1] = "{$middle_word} {$resp[1]}";
		}
		return $resp;
	}

?>