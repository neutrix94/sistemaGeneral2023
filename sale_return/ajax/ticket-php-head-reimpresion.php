<?php
	#header("Content-Type: text/plain;charset=utf-8");
    //die($_GET["id_ped"]);
    include("../../conectMin.php");
	
	define('FPDF_FONTPATH','../../include/fpdf153/font/');
	
	include("../../include/fpdf153/fpdf.php");
/*implementación Oscar 25.01.2019 para sacar rutas de tickets*/
	$archivo_path = "../../conexion_inicial.txt";
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
/*Fin de cambio Oscar 25.01.2018*/
    
    
    if(!isset($_GET["noImp"])){
        $_GET["noImp"]=1;
    }
    $id_pedido=$_GET["id_ped"];
    $id_devolucion=$_GET["id_dev"];
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


//consultamos monto de la devolucion
//	$dev="SELECT monto FROM ec_devolucion_pagos WHERE id_devolucion=$id_devolucion";
//	$eje=mysql_query($dev);
//	if(!$eje){
//		die("Error al consultar monto de la devolucion\n\n".mysql_query());
//	}
	
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
	       P.id_productos AS id_producto,
	       P.nombre AS producto,
	       PD.cantidad,
	       PD.precio,
	       PD.monto,
	       PD.descuento AS descuentoProds,
	    /*implementación Oscar 10.10.2018 para imprimir ubicación y clave_proveedor en ticket*/
	       	IF(s.mostrar_ubicacion=1 OR s.mostrar_alfanumericos=1,1,0) as infoAdicional,
	       	CONCAT(
	       		IF(s.mostrar_ubicacion=1 AND sp.ubicacion_almacen_sucursal!='',CONCAT('Ubicación: ',sp.ubicacion_almacen_sucursal,' | '),''),
	       		IF(s.mostrar_alfanumericos=1,CONCAT('Clave: ',P.clave),'')

	       	)as info
		/*Fin de cambio 10.10.2018*/
	       FROM ec_productos P
	       INNER JOIN ec_pedidos_detalle PD ON PD.id_producto = P.id_productos
	       JOIN sys_sucursales s ON s.id_sucursal=$user_sucursal
	       LEFT JOIN sys_sucursales_producto sp ON s.id_sucursal=sp.id_sucursal AND P.id_productos=sp.id_producto
	       WHERE PD.id_pedido = '{$id_pedido}' AND PD.cantidad>0
	       GROUP BY PD.id_pedido_detalle
	       ORDER BY PD.id_pedido_detalle";
	       
	$descProds=0;
	if ($rs = mysql_query($cs))
	{
	//Sumamos descuentos de productos Oscar(04-11-2017)
		/*while($rw=mysql_fetch_row($rs)){
			$descProds+=$rw[5];
		}*/
	//
		while ($dr = mysql_fetch_assoc($rs)){
			
			if($dr["producto"] == "Pocas piezas"){
				$dr["producto"] .= " \${$dr["precio"]}";
				$lineas_productos += ceil(strlen($dr["producto"])/32.0);
				array_push($productosP,$dr);

			}else{
				// Concatenar precio unitario en la descripción
				$dr["producto"] .= " \${$dr["precio"]}";
				$lineas_productos += ceil(strlen($dr["producto"])/32.0);
				array_push($productos, $dr);
			}
			$descProds+=$dr['descuentoProds'];

	    /*implementación Oscar 10.10.2018 para imprimir ubicación y calve_proveedor en ticket*/
			if($dr['info']==1){
				$lineas_productos+=.8;
			}
		/*Fin de cambio 10.10.2018*/

		}
		mysql_free_result($rs);
	}
	
	$cs = "SELECT TP.nombre, SUM(PP.monto) as monto FROM ec_pedido_pagos PP
			INNER JOIN ec_tipos_pago TP ON PP.id_tipo_pago = TP.id_tipo_pago
			WHERE PP.id_pedido = '{$id_pedido}' AND (PP.referencia=''||PP.referencia=null)
			GROUP by CONCAT(PP.fecha,' ',PP.hora)
			ORDER BY TP.nombre";
	if ($rs = mysql_query($cs)){
		while ($dr = mysql_fetch_assoc($rs))
		{
			// Concatenar precio unitario en la descripción
			++$lineas_pagos;
			$total_pagos += $dr["monto"];
			array_push($pagos, $dr);
		}
		mysql_free_result($rs);
	}

/*implementación de Oscar 06.09.2018 para restar devoluciones*/
	$sql="SELECT SUM( IF(dev.id_devolucion IS NULL,0,IF(referencia='' OR referencia=null,dp.monto,0) ) )
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
	
	
	$ticket = new TicketPDF("P", "mm", array(80,$lineas_dev+130+$lineas_productos*6+($total!=$subtotal?12:0)+($pagado>0?14:30)+(count($pagos)>0?($lineas_pagos+1)*6:0)+30+10), "{$sucursal}", "{$folio}", 10);
	$ticket->AliasNbPages();
	$ticket->AddPage();
	
	$bF=10;
	if($tv==1){
		$ticket->SetFont('Arial','B',$bF+4);
		$ticket->SetXY(5, $ticket->GetY()+1);
		$ticket->Cell(66, 3, utf8_decode("VENTA POR MAYOREO"), "" ,0, "C");
	}

	//$ticket->Image("../img/logo-casa-fondo-blanco.png", 28, 5, 22);
	
	$ticket->SetFont('Arial','B',$bF+4);
	$ticket->SetXY(5, $ticket->GetY()+4);
	$ticket->Cell(66, 6, utf8_decode("Reimpresión de Ticket"), "" ,0, "C");
/*cambio de Oscar 10.08.2018p para quitar leyenda si es reimpresión desde listado*/
	if($_GET['reimpresion']!=1){
		$ticket->SetXY(5, $ticket->GetY()+4);
		$ticket->Cell(66, 6, utf8_decode("por Devolución"), "" ,0, "C");
	}
/*fin de cambio*/

	$ticket->SetXY(5, $ticket->GetY()+7);
	$ticket->Cell(66, 3, "", "TB" ,0, "C");

	//$ticket->SetFont('Arial','',$bF+1);
	//$ticket->SetXY(5, $ticket->GetY()+4);
	//$ticket->Cell(66, 6, utf8_decode("Sucursal {$ticket->sucursal}"), "" ,0, "C");
	
	$ticket->SetFont('Arial','',$bF+2);
	
	$ticket->SetXY(5, $ticket->GetY()+4);
	$ticket->Cell(66*0.6, 6, utf8_decode("{$tipofolio}"), "" ,0, "C");
	
	$ticket->SetX(5+66*0.6);
	$ticket->Cell(66*0.4, 6, utf8_decode("{$ticket->pedido}"), "" ,0, "C");
	
	$ticket->SetXY(5, $ticket->GetY()+6);
	$ticket->Cell(66, 3, "", "TB" ,0, "C");
	
	$ticket->SetFont('Arial','',$bF);
	
	$ticket->SetXY(5, $ticket->GetY()+3);
	$ticket->Cell(66, 6, utf8_decode("FECHA Y HORA DE EMISIÓN:"), "" ,0, "C");
	
	$ticket->SetXY(5, $ticket->GetY()+4.5);
/*implementación Oscar 28.02.2019 para que la hora del ticket sea tomada de la MySQL*/
	$ticket->Cell(66, 6, utf8_decode("Estado de México ") . utf8_decode($fecha_tkt), "" ,0, "C");
/*Fin de cambio Oscar 28.02.2019*/
	
	$ticket->SetXY(5, $ticket->GetY()+4);
	$ticket->Cell(66, 6, utf8_decode("VENDEDOR:  {$vendedor}"), "" ,0, "L");
	
	$ticket->SetXY(5, $ticket->GetY()+5.5);
	$ticket->Cell(66, 3, "", "TB" ,0, "C");
	
	$ticket->SetFont('Arial','',$bF);
	
	$ticket->SetXY(5, $ticket->GetY()+3);
	$ticket->Cell(66*0.63, 6, utf8_decode("DESCRIPCIÓN"), "B" ,0, "L");
	
	$ticket->SetXY(5+66*0.63, $ticket->GetY());
	$ticket->Cell(66*0.12, 6, utf8_decode("CANT"), "B" ,0, "L");
	
	$ticket->SetXY(5+66*0.75, $ticket->GetY());
	$ticket->Cell(66*0.25, 6, utf8_decode("PRECIO"), "B" ,0, "R");
	
	$ticket->SetFont('Arial','',$bF-2);
	$ticket->SetXY(5, $ticket->GetY()+8);
	
	foreach ($productos as $producto) {
	    	   
	    $y = $ticket->GetY();	
		$ticket->SetXY(5+66*0.75, $y);
		$ticket->MultiCell(66*0.25, 4, "$ " . number_format($producto["monto"], 2), "", "R", false);
	
		$ticket->SetXY(5+66*0.63, $y);
		$ticket->MultiCell(66*0.12, 4, $producto["cantidad"], "", "C", false);
	
		$ticket->SetXY(5, $y);
		$ticket->MultiCell(66*0.63, 4, utf8_decode("{$producto["producto"]}"), "", "L", false);

	/*implementación Oscar 10.10.2018 para imprimir ubicación y calve_proveedor en ticket*/
		if($producto['infoAdicional']==1){
			$ticket->SetFont('Arial','',$bF-3.5);
			$ticket->SetXY(5,($ticket->GetY()-1.5));
			$ticket->MultiCell(66*0.63, 4, utf8_decode("{$producto["info"]}"), "", "L", false);
		}
		$ticket->SetFont('Arial','',$bF-2);
	/*fin de cambio 10.10.2018*/

	}
	
	foreach ($productosP as $productoP) {
	    	   
	    $y = $ticket->GetY();	

		$ticket->SetXY(5+66*0.75, $y);
		$ticket->MultiCell(66*0.25, 4, "$ " . number_format($productoP["monto"], 2), "", "R", false);
	
		$ticket->SetXY(5+66*0.63, $y);
		$ticket->MultiCell(66*0.12, 4, $productoP["cantidad"], "", "C", false);
	
		$ticket->SetXY(5, $y);
		$ticket->MultiCell(66*0.63, 4, utf8_decode("{$productoP["producto"]}"), "", "L", false);

	}

	$ticket->SetY($ticket->GetY()-2);
	$ticket->SetXY(5+66*0.40, $ticket->GetY()+3);
	$ticket->Cell(66*0.32, 2, "", "T" ,0, "C");
	
	$ticket->SetXY(5+66*0.75, $ticket->GetY());
	$ticket->Cell(66*0.25, 2, "", "T" ,0, "C");
	$ticket->SetY($ticket->GetY()-5);
	
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

	$ticket->SetFont('Arial','B',$bF);
	$ticket->SetXY(5+66*0.4, $ticket->GetY()+5);
	$ticket->Cell(66*0.3, 6, utf8_decode("Total"), "" ,0, "L");
	
	$ticket->SetXY(5+66*0.75, $ticket->GetY());
	$total=ceil($total);
	$ticket->Cell(66*0.25, 6, "$ " . number_format($total, 2), "" ,0, "R");
	
	/*$ticket->SetY($ticket->GetY()+4);
	$ticket->SetXY(7+66*0.40, $ticket->GetY()+3);
	$ticket->Cell(66*0.32, 2, "DEscuento", "T" ,0, "C");
	
	$ticket->SetXY(7+66*0.75, $ticket->GetY());
	$ticket->Cell(66*0.25, 2, "$descuento", "T" ,0, "C");
	$ticket->SetY($ticket->GetY()-5);*/
	
	$ticket->SetFont('Arial','',$bF-2);
	if (count($pagos)) {
		$ticket->SetXY(5+66*0.4, $ticket->GetY()+5);
		$ticket->Cell(66*0.3, 6, utf8_decode("Forma de pago"), "" ,0, "L");
			
		$ticket->SetXY(5+66*0.75, $ticket->GetY());
		$ticket->Cell(66*0.25, 6, utf8_decode("Monto"), "" ,0, "R");
		
		$ticket->SetY($ticket->GetY()+3);
		$ticket->SetXY(5+66*0.40, $ticket->GetY()+3);
		$ticket->Cell(66*0.32, 2, "", "T" ,0, "C");
		
		$ticket->SetXY(5+66*0.75, $ticket->GetY());
		$ticket->Cell(66*0.25, 2, "", "T" ,0, "C");
		$ticket->SetY($ticket->GetY()-5);
		
		foreach ($pagos as $pago) {
			$ticket->SetXY(5+66*0.4, $ticket->GetY()+5);
			$ticket->Cell(66*0.3, 6, utf8_decode("{$pago["nombre"]}"), "" ,0, "L");
			
			$ticket->SetXY(5+66*0.75, $ticket->GetY());
			$ticket->Cell(66*0.25, 6, "$ " . number_format($pago["monto"], 2), "" ,0, "R");
		}
	/**/
		if($lineas_dev>0){
			$ticket->SetXY(5+66*0.4, $ticket->GetY()+5);
			$ticket->Cell(66*0.3, 6, utf8_decode("- Devoluciones"), "" ,0, "L");
			$ticket->SetXY(5+66*0.75, $ticket->GetY());
			$ticket->Cell(66*0.25, 6, "$ " . number_format($monto_devolucion, 2), "" ,0, "R");

		}
	/**/
		
		$ticket->SetFont('Arial','B',$bF+2);	
		$ticket->SetXY(66*0.25, $ticket->GetY()+5);
		$ticket->Cell(66*0.3, 6, utf8_decode("Total pagado:"), "" ,0, "L");
			
		$ticket->SetXY(5+66*0.75, $ticket->GetY());
		$ticket->Cell(66*0.25, 6, "$ " . number_format(($total_pagos-$monto_devolucion), 2), "" ,0, "R");//_pagos

		$resta = $total - $total_pagos;
		if($resta < 0)
		{
			$resta = 0;
		}	    
	   
		}

		$ticket->SetFont('Arial','',$bF-2);
		/*Se deshabiltan letras Oscar 08.09.2018

		if($pagado==1){
			$ticket->SetFont('Arial','',$bF-2);
			$V=new EnLetras();
			$ticket->SetXY(5, $ticket->GetY()+8);
			$ticket->Cell(66, 6, utf8_decode($V->ValorEnLetras($total, "Pesos")), "" ,0, "L");
		}*/
	if($pagado == 1) {
		$ticket->SetXY(5, $ticket->GetY()+8);
		$ticket->Cell(66, 6, utf8_decode("Para cualquier aclaración, presentar su ticket."), "" ,0, "C");

		
	}else{
		$ticket->SetFont('Arial','B',$bF+5);
		$ticket->SetXY(2, $ticket->GetY()+6);
	    $ticket->Cell(66*0.40, 6, utf8_decode("Resta"), "" ,0, "L");
	  

		$ticket->SetXY(25, $ticket->GetY());
		$ticket->Cell(66*0.35, 6, "$" . number_format($resta, 2), "" ,0, "R");

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

	$ar = fopen("../../leyenda_ticket/leyenda.txt","r") or die ('No se pudo abrir el archivo');
		 
    while(!feof($ar)){
        $linea=fgets($ar);
    }
    fclose($ar);
    $acotado = substr($linea,0,165);

    $ticket->SetFont('Arial','',$bF-2);	
    $ticket->SetXY(10, $ticket->GetY()+6);
    $ticket-> MultiCell(60,5, utf8_decode($acotado), 0 ,'J',false);

    $ticket->SetXY(5, $ticket->GetY()+2);
    $ticket->Cell(66, 6, utf8_decode("Comprobante Simplificado"), "" ,0, "C");

    $ticket->SetXY(5, $ticket->GetY()+4);
    $ticket->Cell(66, 6, utf8_decode("El importe que comprende este comprobante se"), "" ,0, "C");

    $ticket->SetXY(5, $ticket->GetY()+4);
    $ticket->Cell(66, 6, utf8_decode("encuentra incluido en la factura de la venta"), "" ,0, "C");

    $ticket->SetXY(5, $ticket->GetY()+4);
    $ticket->Cell(66, 6, utf8_decode("global del día, mes o bimestre"), "" ,0, "C");

//implementacion de Facebook y codigo Qr Oscar(03-11-2017)

    //$ticket->SetXY(5,$ticket->GetY()+4);$ticket->SetXY(5, $ticket->GetY()+4);
    $ticket->SetXY(5, $ticket->GetY()+4);
    $ticket->Cell(66, 6, utf8_decode("Siguenos en Facebook\nFacebook.com/casa-de-las-luces"), "" ,0, "C");
    $ticket->Image("../codeQr/qr-code.png", 23, $ticket->GetY()+5,38);

    if($printPan == 1) {
	   $ticket->Output();
    }else{
/*implementación Oscar 17.09.2018 para impresión de tickets de acuerdo a la configuración de la sucursal*/
    	$sql="SELECT ticket_reimpresion FROM sys_sucursales WHERE id_sucursal=$user_sucursal";
    	$eje=mysql_query($sql)or die("Error al consultar número de impresiones!!!\n\n".$sql."\n\n".mysql_error());	
    	$numero=mysql_fetch_row($eje);
    	for($cont=1;$cont<=$numero[0];$cont++){
    		$nombre_ticket="ticket_".$user_sucursal."_" . date("YmdHis") . "_" . strtolower($tipofolio) . "_" . $folio . "_".$cont.".pdf";
    		
    /*implementación Oscar 25.01.2019 para la sincronización de tickets*/
    		if($user_tipo_sistema=='linea'){
    			$sql_arch="INSERT INTO sys_archivos_descarga SET 
    					id_archivo=null,
    					tipo_archivo='pdf',
    					nombre_archivo='$nombre_ticket',
    					ruta_origen='$ruta_or',
    					ruta_destino='$ruta_des',
          			/*Modificación Oscar 03.03.2019 para tomar el destino local de impresión de ticket configurado en la sucursal*/
              			id_sucursal=(SELECT sucursal_impresion_local FROM ec_configuracion_sucursal WHERE id_sucursal='$user_sucursal'),
            		/*Fin de Cambio Oscar 03.03.2019*/
    					id_usuario='$user_id',
    					observaciones=''";
    			$inserta_reg_arch=mysql_query($sql_arch)or die("Error al guardar el registro de sincronización del ticket de reimpresión!!!\n\n".mysql_error()."\n\n".$sql_arch);

    		}
    	$ticket->Output("../../cache/ticket/".$nombre_ticket, "F");
    /*fin de cambio Oscar 25.01.2019*/
    	}
/*fin de cambio Oscar 17.09.2018*/
    
       //$ticket->Output("../../cache/ticket/ticket_".$user_sucursal."_" . date("YmdHis") . "_" . strtolower($tipofolio) . "_" . $folio . "_2.pdf", "F");
     //  header ("location: index.php?scr=home"); 
    }
		
	//exit (0);

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