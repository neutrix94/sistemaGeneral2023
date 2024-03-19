<?php
/*version casa 1.2*/
	define('FPDF_FONTPATH','../../../../include/fpdf153/font/');
	include("../../../../include/fpdf153/fpdf.php");
	include("../../../../conect.php");
	include("../../../../conexionMysqli.php");
	//consulta el año actual
	$data = array();
	$pendientes_pago = array();
	$espacio_ventas_pendientes_cobro = 0;

	$sql = "SELECT DATE_FORMAT( NOW(), '%Y' ) AS year";
	$stm = $link->query( $sql ) or die( "Error al consultar el año actual : {$this->link->error}" );
	$current_year = $stm->fetch_assoc();
	$sql = "SELECT 
				folio_nv AS folio, 
				total AS amount,
				DATE_FORMAT( fecha_alta, '%d/%m/%Y' )AS date
			FROM ec_pedidos 
			WHERE venta_validada = 0 
			AND fecha_alta LIKE '%{$current_year['year']}%'
			AND id_sucursal = {$sucursal_id}";//
	$stm = $link->query( $sql ) or die( "Error al consultar ventas pendientes de validar : {$link->error}" );
	$sales_number = $stm->num_rows;
//implementacion Oscar 2024-02-23 para listar ventas pendientes de validar
	$sql_2 = "SELECT
				ax.dateTime,
				ax.folio, 
				ax.amount,
				ax.username,
				ax.pagado
			FROM(
				SELECT 
					p.fecha_alta AS dateTime,
					p.folio_nv AS folio, 
					p.total AS amount,
					CONCAT( u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno ) AS username,
					SUM( IF( cc.id_cajero_cobro IS NULL, 0, cc.monto ) ) AS pagado
				FROM ec_pedidos p
				LEFT JOIN ec_cajero_cobros cc
				ON cc.id_pedido = p.id_pedido
				LEFT JOIN sys_users u
				ON u.id_usuario = p.id_usuario
				WHERE p.pagado = 1 
				AND p.fecha_alta LIKE '%{$current_year['year']}%'
				AND p.id_sucursal = {$sucursal_id}
				GROUP BY p.id_pedido
			)ax
			WHERE ROUND( ax.pagado ) < ROUND( ax.amount )";//
			//die($sql_2);
	$stm_2 = $link->query( $sql_2 ) or die( "Error al consultar las ventas pendientes de pago : {$link->error}" );
	$sales_without_payment = $stm_2->num_rows;
	if( $sales_without_payment > 0 ){
		$espacio_ventas_pendientes_cobro = ( $sales_without_payment * 8.8 ) + 20;
	}
	if( $_POST['flag'] == 'seek_pending_to_validate' ){
		$resp = "ok|";
		if( $sales_without_payment > 0 ){//ventas pendientes de pago

			$resp .= "<div class=\"row\" style=\"background-color : white; max-height : 60%; overflow : auto; position : relative; top : 100px;\">
						<div class=\"col-2\"></div>
						<div class=\"col-8\">
							<h5 class=\"text-danger\">Las siguientes ventas estan pendientes de pago : </h5>
							<table class=\"table table-striped table-bordered\">
								<thead class=\"btn-warning\" style=\"position : sticky; top : 0;\">
									<tr>
										<th class=\"text-center\">#</th>
										<th class=\"text-center\">Fecha/hora</th>
										<th class=\"text-center\">Folio</th>
										<th class=\"text-center\">Monto</th>
										<th class=\"text-center\">Vendedor</th>
									</tr>
								</thead>
								<tbody>";
			$counter = 0;
			while( $row_2 = $stm_2->fetch_assoc() ){
				$counter ++;
				$resp .= "<tr>
							<td class=\"text-center\">{$counter}</td>
							<td class=\"text-center\">{$row_2['dateTime']}</td>
							<td class=\"text-center\">{$row_2['folio']}</td>
							<td class=\"text-center\">$ {$row_2['amount']}</td>
							<td class=\"text-center\">{$row_2['username']}</td>
						</tr>";
			}
			$resp .= "</tbody></table>
			</div><br><br>";

		}
		
		if( $sales_number > 0 ){

			$resp .= "<div class=\"row\" style=\"background-color : white; max-height : 60%; overflow : auto; position : relative; top : 100px;\">
						<div class=\"col-2\"></div>
						<div class=\"col-8\">
							<h5 class=\"text-warning\">Las siguientes ventas estan pendientes de validar : </h5>
							<table class=\"table table-striped table-bordered\">
								<thead class=\"btn-warning\" style=\"position : sticky; top : 0;\">
									<tr>
										<th class=\"text-center\">#</th>
										<th class=\"text-center\">Folio</th>
										<th class=\"text-center\">Monto</th>
										<th class=\"text-center\">Fecha</th>
									</tr>
								</thead>
								<tbody>";
			$counter = 0;
			while( $row = $stm->fetch_assoc() ){
				$counter ++;
				$resp .= "<tr>
							<td class=\"text-center\">{$counter}</td>
							<td class=\"text-center\">{$row['folio']}</td>
							<td class=\"text-center\">$ {$row['amount']}</td>
							<td class=\"text-center\">{$row['date']}</td>
						</tr>";
			}
			$resp .= "</tbody></table></div>
						<div class=\"col-2\">
							<!--i class=\"icon-down-open btn btn-light\" 
								style=\"position : fixed; top : 65% !important; border-radius : 50%; border : 1px solid green; \"></i-->
						</div>
						<div class=\"col-2\"></div>
						<div class=\"col-8 text-center\">
							<button 
									type=\"button\" 
									class=\"btn btn-success\"
									onclick=\"print_pending_ticket();\"
								><i class=\"icon-ok-circle\">Aceptar y Continuar</i></button>
						</div>
			</div>";
		}
		die( $resp );
	}else{
//	
		while( $row = $stm->fetch_assoc() ){
			$counter ++;
			array_push( $data, $row);
		}
		while( $row_2 = $stm_2->fetch_assoc() ){
			$counter ++;
			array_push( $pendientes_pago, $row_2);
		}
	}

/*implementación Oscar 25.01.2019 para sacar rutas de tickets*/
    $archivo_path = "../../../../conexion_inicial.txt";
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

//calculamos tmaño de gastos
	$dG=explode("~",$gastos);
	$tam_gastos=sizeof($dG)*15;
 	//die($fechaFin);   
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
	
	//aqui cambia largo Oscar 26-11-2017
	$ticket = new TicketPDF("P", "mm", array( 80, ( 80 + ( $sales_number * 8.8 ) + $espacio_ventas_pendientes_cobro ) ) , "{$sucursal_id}", "{$folio}", 10);
	//echo 'res:'.$resAprox;
	$ticket->AliasNbPages();
	$ticket->AddPage();
	
	$bF=10;
//genearmos encabezado
	$ticket->SetFont('Arial','B',$bF+4);
	$ticket->SetXY(8, 5);//$ticket->GetY()+3
	$ticket->Cell(60, 6, utf8_decode("Ventas Pendientes de validar: ".$folio_sesion_caja), "" ,0, "C");

	$ticket->SetFont('Arial','B',$bF);
	$ticket->SetXY(8, $ticket->GetY()+5);
	$ticket->Cell(60, 6, utf8_decode("Fecha y hora :"), "" ,0, "L");

	$sql = "SELECT NOW()";
	$eje_crte=mysql_query($sql)or die("Error al consultar datos del corte de caja!!!");
	$r_crte=mysql_fetch_row($eje_crte);
	$ticket->SetXY(7, $ticket->GetY()+3);
	$ticket->Cell(66, 6, utf8_decode($r_crte[0]), "" ,0, "L");
	//$ticket->Cell(66, 6, utf8_decode($consultaHora), "" ,0, "C");

//sucursal
	$sql="SELECT nombre FROM sys_sucursales WHERE id_sucursal=$sucursal_id";
	$eje=mysql_query($sql) or die('Error: ' . $sql . ' - ' . mysql_error());
	$rw=mysql_fetch_row($eje);
	$ticket->SetFont('Arial','',$bF);
	$ticket->SetXY(7, $ticket->GetY()+4);
	$ticket->Cell(66, 6, utf8_decode("Sucursal: ".$rw[0]), "" ,0, "C");

	$ticket->SetFont('Arial','B',$bF+2);
	$ticket->SetXY(7, $ticket->GetY()+4);
	$ticket->Cell(66, 6, utf8_decode($datos_cajero), "" ,0, "C");
	
	$ticket->SetFont('Arial','',$bF);
	
	$ticket->SetXY(7, $ticket->GetY()+4.5);
	$ticket->Cell(66, 6, utf8_decode(), "" ,0, "C");
/*Implementacion Oscar 2024-02-23 para imprimir*/
//encabezado
	$ticket->SetXY(4, $ticket->GetY()+2);
	$ticket->Cell(62, 6, utf8_decode( "Fec/hr" ), "" ,0, "C");
	$ticket->SetXY(7, $ticket->GetY());
	$ticket->Cell(62, 6, utf8_decode( "Folio" ), "" ,0, "C");
	$ticket->SetXY(7, $ticket->GetY());
	$ticket->Cell(62, 6, utf8_decode( "Monto" ), "" ,0, "C");
	$ticket->SetXY(10, $ticket->GetY());
	$ticket->Cell(62, 6, utf8_decode( "Vendedor" ), "" ,0, "R");
	foreach ($pendientes_pago as $key => $value) {
		# code...
	}
/*Fin de cambio Oscar 2024-02-23*/
//	$nombre_ticket="ticket_".$user_sucursal."_".date("YmdHis")."_".strtolower($tipofolio)."_".$folio."_$noImp.pdf";
	$nombre_ticket="ticket_".$user_sucursal."_".date("YmdHis")."_".$folio_sesion_caja.".pdf";
	$sql = "SELECT 
				p.folio_nv AS folio, 
				p.total AS amount,
				DATE_FORMAT( p.fecha_alta, '%d/%m/%Y' ) AS date,
				CONCAT( 'Vendedor : ', u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno )
			FROM ec_pedidos p
			LEFT JOIN sys_users u
			ON u.id_usuario = p.id_usuario
			WHERE p.venta_validada = 0 
			AND p.fecha_alta LIKE '%{$current_year['year']}%'
			AND p.id_sucursal = {$sucursal_id}";//
	$eje=mysql_query($sql) or die( "Error\n{$sql} " . mysql_error() );
//encabezado
	$ticket->SetXY(4, $ticket->GetY()+2);
	$ticket->Cell(62, 6, utf8_decode( "Folio" ), "" ,0, "L");
	$ticket->SetXY(7, $ticket->GetY());
	$ticket->Cell(62, 6, utf8_decode( "Monto" ), "" ,0, "C");
	$ticket->SetXY(10, $ticket->GetY());
	$ticket->Cell(62, 6, utf8_decode( "Fecha" ), "" ,0, "R");
	
	while($rw=mysql_fetch_row($eje)){
		$ticket->SetFont('Arial','',$bF-2);
		$ticket->SetXY(4, $ticket->GetY()+6);
		$ticket->Cell(62, 6, utf8_decode($rw[0]), "" ,0, "L");
		$ticket->SetXY(7, $ticket->GetY());
		$ticket->Cell(62, 6, utf8_decode($rw[1]), "" ,0, "C");
		$ticket->SetXY(10, $ticket->GetY());
		$ticket->Cell(62, 6, utf8_decode($rw[2]), "" ,0, "R");

		$ticket->SetFont('Arial','', $bF-3 );
		$ticket->SetXY(4, $ticket->GetY()+3);
		$ticket->Cell(62, 6, utf8_decode($rw[3]), "" ,0, "L");
	}
    /*instancia clases*/
	include( '../../../../conexionMysqli.php' );
	include( '../../../../code/especiales/controladores/SysArchivosDescarga.php' );
	$SysArchivosDescarga = new SysArchivosDescarga( $link );
	include( '../../../../code/especiales/controladores/SysModulosImpresionUsuarios.php' );
	$SysModulosImpresionUsuarios = new SysModulosImpresionUsuarios( $link );
	include( '../../../../code/especiales/controladores/SysModulosImpresion.php' );
	$SysModulosImpresion = new SysModulosImpresion( $link );

	$absolute_path = $_POST['absolute_path'];
	$ruta_salida = '';
	$ruta_salida = $SysModulosImpresionUsuarios->obtener_ruta_modulo_usuario( $user_id, 1 );//ventas pedientes de validar
	if( $ruta_salida == 'no' ){
		$ruta_salida = "cache/" . $SysModulosImpresion->obtener_ruta_modulo( $user_sucursal, 1 );//ventas pedientes de validar
	}
    $ticket->Output("{$abslute_path}{$ruta_salida}/{$nombre_ticket}", "F");

/*Sincronización remota de tickets*/
	if( $user_tipo_sistema == 'linea' ){/*registro sincronizacion impresion remota*/
		$registro_sincronizacion = $SysArchivosDescarga->crea_registros_sincronizacion_archivo( 'pdf', $nombre_ticket, $ruta_or, $ruta_salida, $user_sucursal, $user_id );
	}else{//impresion por red local
		//die("HERE : {$absolute_path}");
		$enviar_por_red = $SysArchivosDescarga->crea_registros_sincronizacion_archivo_por_red_local( 1, 'pdf', $nombre_ticket, '', $ruta_salida, $user_sucursal,  $user_id, 
		$carpeta_path, $absolute_path, 'alert("Impresion de cotizacion exitosa!");close_emergent();' );
	}
    die( 'ok|' );

?>