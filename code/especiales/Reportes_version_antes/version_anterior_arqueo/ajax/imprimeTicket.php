<?php
	extract($_POST);
	define('FPDF_FONTPATH','../../../../include/fpdf153/font/');
	include("../../../../include/fpdf153/fpdf.php");
	include("../../../../conect.php");
	/*implementación Oscar 25.01.2019 para sacar rutas de tickets*/
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
/*Fin de cambio Oscar 25.01.2018*/

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
	$ticket = new TicketPDF("P", "mm", array(80,(120+$resAprox*9)+5), "{$sucursal}", "{$folio}", 10);
	//echo 'res:'.$resAprox;
	$ticket->AliasNbPages();
	$ticket->AddPage();
	
	$bF=10;
	
	//$ticket->Image("../../../../img/img_casadelasluces/logocasadelasluces-easy.png", 28, 5, 22);
//rango de fechas del arqueo
	if($fechaFin==-1){
		$fecha1=date("Y-m-d");
		$complemeto="del ".$fecha1;
	}else{
		$ax=explode("|",$fechaFin);
		$fecha1=$ax[0];
		$fecha2=$ax[1];
		$complemeto="del ".$fecha1." al ".$fecha2;
	}
	//sacamos hora
		if($horaFin==0){
			$consultaHora="Rango de Hora: de 00:00 a 23:59";
			$hr1="00:00:00";
			$hr2="23:59:00";
		}else{
			$ax1=explode("~",$horaFin);
			$ax2=explode("|",$ax1[0]);
			$hr1=$ax2[0].":".$ax2[1].":00";
			$consultaHora="Rango de Hora: ".$hr1." a ";
			$ax2=explode("|",$ax1[1]);
			$hr2=$ax2[0].":".$ax2[1].":00";
			$consultaHora.=$hr2;
		}

//genearmos encabezado
	$ticket->SetFont('Arial','B',$bF);
	
	$ticket->SetXY(8, 5);//$ticket->GetY()+3
	$ticket->Cell(60, 6, utf8_decode("Fecha y hora de corte:"), "" ,0, "R");
	$ticket->SetXY(10, $ticket->GetY()+3);//
	$ticket->Cell(58, 6, utf8_decode(date("d/m/Y H:i:s")), "" ,0, "R");
	

	$ticket->SetXY(7, 20);
	$ticket->Cell(66, 6, utf8_decode("CASA DE LAS LUCES"), "" ,0, "C");

	$ticket->SetFont('Arial','B',8);
	$ticket->SetXY(7, 25);
	$ticket->Cell(66, 6, utf8_decode("Arqueo de caja ".$complemeto), "" ,0, "C");
	$ticket->SetXY(7, $ticket->GetY()+5);
	$ticket->Cell(66, 6, utf8_decode($consultaHora), "" ,0, "C");

//nombre de sucursal
	$sql="SELECT nombre from sys_sucursales WHERE id_sucursal=$sucursal_id";
	$eje=mysql_query($sql) or die('Error: '.mysql_error());
	$rw=mysql_fetch_row($eje);
	$ticket->SetFont('Arial','',$bF);
	$ticket->SetXY(7, $ticket->GetY()+4);
	$ticket->Cell(66, 6, utf8_decode("Sucursal: ".$rw[0]), "" ,0, "C");
	
	$ticket->SetFont('Arial','',$bF);
	
	$ticket->SetXY(7, $ticket->GetY()+4.5);
	$ticket->Cell(66, 6, utf8_decode(), "" ,0, "C");

//detalles de dinero
	/*ineas*/
	$ticket->SetXY(7, $ticket->GetY()+5);
	$ticket->Cell(66, 5, "", "TB" ,0, "C");
	$ticket->SetXY(7, $ticket->GetY());
	$ticket->Cell(66, 6, utf8_decode("Ingresos"), "" ,0, "C");
/*sepramos los datos*/
	$dat=explode("|",$datos);

	$ticket->SetFont('Arial','',$bF);

/*separación de ingresos (implementado por Oscar 15.08.2018)*/
	
	$ticket->SetXY(10, $ticket->GetY()+5);
	$ticket->Cell(60, 6, utf8_decode("Ingreso Interno: $ ".round($dat[7])), "" ,0, "R");//-$dat[6])
	$ticket->SetFont('Arial','',$bF);

	if($dat[6]>0){
		$ticket->SetXY(10, $ticket->GetY()+5);
		$ticket->Cell(60, 6, utf8_decode("Ingreso Externo: $ ".round($dat[6])), "" ,0, "R");
		//$ticket->SetFont('Arial','',$bF);
	}
/*Fin de cambio*/

	$ticket->SetFont('Arial','B',$bF);
	$ticket->SetXY(10, $ticket->GetY()+5);
	$ticket->Cell(60, 6, utf8_decode("Total de Ingresos: ".$dat[0]), "" ,0, "R");
	
	$ticket->SetFont('Arial','',$bF);
	$ticket->SetXY(10, $ticket->GetY()+5);
	$ticket->Cell(60, 6, utf8_decode("Tarjeta 1:".$dat[1]), "" ,0, "R");
	$ticket->SetXY(10, $ticket->GetY()+5);
	$ticket->Cell(60, 6, utf8_decode("Tarjeta 2:".$dat[2]), "" ,0, "R");
	
	$ticket->SetFont('Arial','B',$bF);
	$ticket->SetXY(10, $ticket->GetY()+5);
	$ticket->Cell(60, 6, utf8_decode("Ingreso en Efectivo: ".$dat[3]), "" ,0, "R");
	$ticket->SetFont('Arial','',$bF);

/*
	if($dat[6]>0){
		$ticket->SetXY(7, $ticket->GetY()+5);
		$ticket->Cell(66, 5, "", "TB" ,0, "C");
		$ticket->SetXY(7, $ticket->GetY());
		$ticket->Cell(66, 6, utf8_decode("Separación de Ingresos"), "" ,0, "C");
	//total de ingresos
		$ticket->SetFont('Arial','B',$bF);//B
		$ticket->SetXY(10, $ticket->GetY()+5);
		$ticket->Cell(60, 6, utf8_decode("Ingreso en Efectivo: $".$dat[3]), "" ,0, "R");
		//$ticket->SetFont('Arial','',$bF);

		$ticket->SetXY(10, $ticket->GetY()+5);
		$ticket->Cell(60, 6, utf8_decode("Ingreso Externo: $".round($dat[6])), "" ,0, "R");
		//$ticket->SetFont('Arial','',$bF);

		$ticket->SetXY(10, $ticket->GetY()+5);
		$ticket->Cell(60, 6, utf8_decode("Ingreso total-Ingreso Externo: $".($dat[0]-$dat[6])), "" ,0, "R");
		$ticket->SetFont('Arial','',$bF);
	}*/

//gastos
	/*ineas*/
	$ticket->SetXY(7, $ticket->GetY()+10);
	$ticket->Cell(66, 5, "", "TB" ,0, "C");
	$ticket->SetXY(7, $ticket->GetY()-1);
	$ticket->Cell(66, 6, utf8_decode("Detalle de Gastos"), "" ,0, "C");

	$ticket->SetXY(4, $ticket->GetY()+6);
	$ticket->Cell(60, 6, utf8_decode("Fecha"), "" ,0, "L");
	$ticket->SetXY(7, $ticket->GetY());
	$ticket->Cell(60, 6, utf8_decode("Tipo"), "" ,0, "C");
	$ticket->SetXY(10, $ticket->GetY());
	$ticket->Cell(60, 6, utf8_decode("Monto"), "" ,0, "R");
	$dG=explode("~",$gastos);
	//print_r($dG);
	for($i=0;$i<sizeof($dG)-1;$i++){
		
		$detG=explode("|",$dG[$i]);
		$ticket->SetFont('Arial','',8);
		$aux=calculaTam($detG[1]);
		$aux2=explode("|",$aux);

		$ticket->SetXY(4, $ticket->GetY()+4);
		$ticket->Cell(60*.25, 6, utf8_decode($detG[0]), "" ,0, "L");
		$ticket->SetXY(20,$ticket->GetY());
		$ticket->Cell(60*.5, 6, utf8_decode($aux2[0]), "" ,0, "L");
		$ticket->SetXY(22, $ticket->GetY());
		$ticket->Cell(60*.80, 6, utf8_decode($detG[2]), "" ,0, "R");
		if(sizeof($aux2)>1){
			for($j=1;$j<sizeof($aux2);$j++){
				$ticket->SetXY(4, $ticket->GetY()+4);
				$ticket->Cell(60*.25, 6, utf8_decode(""), "" ,0, "L");
				$ticket->SetXY(20,$ticket->GetY());
				$ticket->Cell(60*.5, 6, utf8_decode($aux2[$j]), "" ,0, "L");
				$ticket->SetXY(22, $ticket->GetY());
				$ticket->Cell(60*.80, 6, utf8_decode(""), "" ,0, "R");
			}
		}

	}
	function calculaTam($texto){
    	$respuesta="";
    	$cont=0;
    	$tam=strlen($texto);
    	for($i=0;$i<=$tam-1;$i++){ 
			$cont++;
			$respuesta.=$texto[$i];
    		if($cont==22){
    			$respuesta.="|";
    			$cont=0;
    		}
    	}
    	//$respuesta.="|";
    	//echo $respuesta;
    	return $respuesta; 
     
	} 

	$ticket->SetFont('Arial','B',$bF);
	$ticket->SetXY(10, $ticket->GetY()+10);
	$ticket->Cell(60, 6, utf8_decode("Total de Gastos: ".$dat[4]), "" ,0, "R");
//efectivo en caja
	$ticket->SetXY(10, $ticket->GetY()+5);
	$ticket->Cell(60, 6, utf8_decode("Efectivo en Caja: $".$dat[5]), "" ,0, "R");
	
	$ticket->SetFont('Arial','',$bF);
//descuentos
	$ticket->SetXY(7, $ticket->GetY()+10);
	$ticket->Cell(66, 5, "", "TB" ,0, "C");
	$ticket->SetXY(7, $ticket->GetY()-1);
	$ticket->Cell(66, 6, utf8_decode("Detalle de Descuentos"), "" ,0, "C");

	$ticket->SetXY(4, $ticket->GetY()+6);
	$ticket->Cell(60, 6, utf8_decode("Folio"), "" ,0, "L");
	$ticket->SetXY(7, $ticket->GetY());
	$ticket->Cell(60, 6, utf8_decode("Monto Total"), "" ,0, "C");
	$ticket->SetXY(10, $ticket->GetY());
	$ticket->Cell(60, 6, utf8_decode("Descuento"), "" ,0, "R");

	$sql="SELECT folio_nv,subtotal,descuento FROM ec_pedidos WHERE subtotal!=total";
	if($fechaFin==-1){
		$fecha1=date("Y-m-d");
		$ax=explode("-",$fecha1);
	//auxiliar de fecha 1
		$fechaLim1=$ax[0]."-".$ax[1]."-".($ax[2]+1);
		if($fecha=='2017-11-30'){
			$fechaLim1='2017-12-01';
		}
		$condicion2=" AND ((fecha_alta>='".$fecha1.' $hr1'."') AND (fecha_alta<='".$fechaLim1.' $hr2'."'))";
	}
//modificacion del 14-12-2017
	if($fechaFin==1){
		$fecha1=date("Y-m-d");
		$ax=explode("-",$fecha1);
	//auxiliar de fecha 1
		$fechaLim1=$ax[0]."-".$ax[1]."-".($ax[2]-1);
		if($fecha=='2017-12-01'){
			$fechaLim1='2017-11-30';
		}
		$condicion2=" AND ((fecha_alta>='".$fechaLim1.' $hr1'."') AND (fecha_alta<='".$fecha1.' $hr2'."'))";
	}//fin de modificacion

	if($fechaFin!=-1 && $fechaFin!=1){
	//auxiliar de fecha 1
		$ax=explode("-",$fecha1);
		$fechaLim1=$ax[0]."-".$ax[1]."-".($ax[2]+1);
		if($fecha1=='2017-11-30'){
			$fechaLim1='2017-12-01';
		}
		$ax=explode("-",$fecha2);
		$fechaLim2=$ax[0]."-".$ax[1]."-".($ax[2]+1);
		if($fecha2=='2017-11-30'){
			$fechaLim2='2017-12-01';
		}
		$condicion2=" AND ((fecha_alta>='".$fecha1.' $hr1'."') ".//OR (fecha_alta<='".$fechaLim1.' 03:00:00'."'))
		"AND (fecha_alta<='".$fechaLim2.' $hr2'."'))";
	}
	$sql.=$condicion2;
	//echo $sql;
	$eje1=mysql_query($sql)or die("Error!!!\n".mysql_error());
	$sql.=$condicion2;
//echo $sql;

	$nombre_ticket="ticket_".$user_sucursal."_".date("YmdHis")."_".strtolower($tipofolio)."_".$folio."_$noImp.pdf";
	$eje=mysql_query($sql) or die("Error\n".mysql_error());
	while($rw=mysql_fetch_row($eje)){
		$ticket->SetXY(4, $ticket->GetY()+4);
		$ticket->Cell(62, 6, utf8_decode($rw[0]), "" ,0, "L");
		$ticket->SetXY(7, $ticket->GetY());
		$ticket->Cell(62, 6, utf8_decode($rw[1]), "" ,0, "C");
		$ticket->SetXY(10, $ticket->GetY());
		$ticket->Cell(62, 6, utf8_decode($rw[2]), "" ,0, "R");
	}
	$ticket->SetXY(4, $ticket->GetY()+4);
	$ticket->Cell(62, 6, "", "" ,0, "L");
	$ticket->SetXY(4, $ticket->GetY()+4);
	$ticket->Cell(62, 6, "", "" ,0, "L");

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
    $ticket->Output("../../../../cache/ticket/".$nombre_ticket, "F");
    /*fin de cambio Oscar 25.01.2019*/

   //$ticket->Output($nombre_tkt, "F");
    echo 'ok';  
     //header ("location: ../index.php?scr=home"); 
?>