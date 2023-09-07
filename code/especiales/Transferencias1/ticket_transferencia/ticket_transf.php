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
//asignamos valores a variables
	$nombre_usuario_transferencia=$r[0];
	$encabezado=$r[1];
	$fecha_trans=$r[2];
	$fecha_actual=$r[3];
	$sucursal_origen=$r[4];
	$sucursal_destino=$r[5];
	$folio_tr=$r[6];
	$impresa=$r[7];
	$folio_transf_original=$r[8];
	$es_resolucion_transf=$r[9];
	if($impresa==1){
		$lineas_productos+=.8;
	}
//declaración de arreglos
	$productos = array();
	$productosP = array();
	$pagos = array();
	$lineas_productos = 0;
	$lineas_pagos = 0;
	$total_pagos = "0";
	$tipofolio = "PEDIDO";
//extraemos datos de impresión
	$sql="SELECT 
		CONCAT('EMITIDO POR: ',u.nombre,' ',u.apellido_paterno),/*,' desde ',s.nombre*/
		CONCAT('Fecha de emisión: ',NOW())
		FROM sys_users u
		JOIN sys_sucursales s ON s.id_sucursal=$user_sucursal
		WHERE u.id_usuario=$user_id";
	$eje=mysql_query($sql)or die("Error al consultar datos de emisión de ticket!!!\n\n".mysql_error()."\n\n".$sql);
	$r=mysql_fetch_row($eje);
	$usuario_emision=$r[0];
	$fecha_emision=$r[1];

if($es_resolucion_transf==1){
/*Implementación Oscar 10.05.2019 para sacar el id de la transferencia original*/
	$sql="SELECT REPLACE(t.folio,CONCAT('RESOLUCION',s.prefijo),'')
		FROM ec_transferencias t 	
		LEFT JOIN sys_sucursales s ON t.id_sucursal=s.id_sucursal
		WHERE t.id_transferencia=$id_transfer";
	$eje_or=mysql_query($sql)or die("Error al consultar el id de la transferencia original");
	$id_original=mysql_fetch_row($eje_or);
}

//extraemos detalle de productos en transferencia
	$sql="SELECT
			/*0*/CONCAT(p.orden_lista,' ',p.nombre) as producto,
			/*1*/CONCAT( 
				IF(p.clave!='' AND p.clave!='.',CONCAT('Clave: ',p.clave),''), 
				IF(p.ubicacion_almacen!='',CONCAT(' Ubicación: ',p.ubicacion_almacen),'')
			) as clave_ubic,
			/*2*/tp.cantidad as cantidad,
			/*3*/IF(t.es_resolucion=1,(SELECT se_queda FROM ec_transferencia_productos WHERE id_transferencia='$id_original[0]' AND id_producto_or=p.id_productos LIMIT 1),0) AS se_queda,
			/*4*/IF(t.es_resolucion=1,(SELECT faltante FROM ec_transferencia_productos WHERE id_transferencia='$id_original[0]' AND id_producto_or=p.id_productos LIMIT 1),0) AS faltante,
			/*5*/IF(t.es_resolucion=1,(SELECT se_regresa FROM ec_transferencia_productos WHERE id_transferencia='$id_original[0]' AND id_producto_or=p.id_productos LIMIT 1),0) AS se_regresa
			FROM ec_productos p 
			LEFT JOIN ec_transferencia_productos tp ON p.id_productos=tp.id_producto_or
			LEFT JOIN ec_transferencias t ON tp.id_transferencia=t.id_transferencia
			LEFT JOIN sys_sucursales_producto sp ON t.id_sucursal_destino=sp.id_sucursal AND p.id_productos=sp.id_producto
			WHERE tp.id_transferencia=$id_transfer
			ORDER BY p.ubicacion_almacen,p.orden_lista ASC";

/*	$sql="SELECT
			ax.producto,
			ax.clave_ubic,
			IF($folio_transf_original=0 OR tp1.cantidad IS NULL,ax.cantidad,(tp1.cantidad-ax.cantidad))
		FROM(SELECT
				CONCAT(p.orden_lista,' ',p.nombre) as producto,
				CONCAT( 
					IF(p.clave!='' AND p.clave!='.',CONCAT('Clave: ',p.clave),''), 
					IF(p.ubicacion_almacen!='',CONCAT(' Ubicación: ',p.ubicacion_almacen),'')) as clave_ubic,
				tp.cantidad_salida as cantidad
			FROM ec_productos p 
			LEFT JOIN ec_transferencia_productos tp ON p.id_productos=tp.id_producto_or
			LEFT JOIN ec_transferencias t ON tp.id_transferencia=t.id_transferencia
			LEFT JOIN sys_sucursales_producto sp ON t.id_sucursal_destino=sp.id_sucursal AND p.id_productos=sp.id_producto
			WHERE tp.id_transferencia=$id_transfer
			GROUP BY p.id_productos
			ORDER BY p.ubicacion_almacen,p.orden_lista ASC
			)ax
			JOIN ec_transferencia_productos tp1 ON ax.producto=tp1.id_producto_or AND tp1.id_transferencia IN ($folio_transf_original)
			GROUP BY ax.producto";*/


	$eje=mysql_query($sql)or die("Error al consultar el detalle de la transferencia/resolución\n\n".mysql_error()."\n".$id_transfer."\n\n".$sql);
//asignamos el detalle de la transferencia al arreglo
	while ($dr = mysql_fetch_assoc($eje)){
	// Concatenar precio unitario en la descripción
		//$dr["producto"] .= " \${$dr["precio"]}";
		$lineas_productos += ceil(strlen($dr["producto"])/32.0);
		array_push($productos, $dr);
	   	
	   	if($dr['clave_ubic']!='' && $dr['clave_ubic']!=null){
			$lineas_productos+=.4;
		}	
	}//fin de while $dr
	mysql_free_result($rs);
/*si es resolución
	if($es_resolucion==1){
		$in=" tp.id_productos IN";
		for($i=0;$i<sizeof($productos);$i++){

		}//fin de for i
	}
*/
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