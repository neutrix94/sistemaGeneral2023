<?php
	define('FPDF_FONTPATH','../../../../include/fpdf153/font/');
	include("../../../../include/fpdf153/fpdf.php");
	include("../../../../conect.php");
/*implementación Oscar 18.06.2019 para guardar el detalle de la sesion de caja*/
	//tar:tarjetas,cheq_trans:cheques,fecha:fF,hrs:horas,corte:id_corte,pss:password,fcha_corte:fecha_ultimo_corte
	$id_sesion_cajero=$_POST['corte'];
	$monto_en_efectivo=$_POST['efectivo'];
	$arr_tarjetas=explode("°",$_POST['tar']);
	$arr_cheques=explode("°",$_POST['cheq_trans']);
	$fecha_corte=$_POST['fecha'];
//separamos los ingresos
	$arr_ingresos=explode("|",$_POST['arr_ing']);
	$contador_ingresos=0;
	//die($arr_ing[0]."---".$arr_ing[1]);
	$total_ingresos=$arr_ingresos[0]+$arr_ingresos[1];

//obtenemos el folio del arqueo de caja
	$sql="SELECT 
				folio,
				fecha,
				hora_inicio,
				IF(DATE_FORMAT(now(),'%Y-%m-%d')>fecha AND DATE_FORMAT(now(),'%H:%i:%s')<hora_inicio,1,0) as insertaSegundoCorte/*implemntacion para insertar el segundo corte*/
		FROM ec_sesion_caja 
		WHERE id_sesion_caja=$id_sesion_cajero";
	
	$eje=mysql_query($sql)or die("Error al consultar la sesion de caja!!!\n".mysql_error()."\n".$sql);
	$r=mysql_fetch_row($eje);
	$folio_sesion_caja=$r[0];
	$fecha_inicio=$r[1];
	$hora_inicio=$r[2];
	$insertar_corte=$r[3];
	
//nombre del cajero que cobra
    $sql="SELECT CONCAT('CAJERO: ',nombre,' ',apellido_paterno,' ',apellido_materno) FROM sys_users WHERE id_usuario=$user_id";
    $eje=mysql_query($sql)or die("Error al consultar los datos del cajero!!!\n".mysql_error());
    $r=mysql_fetch_row($eje);
    $datos_cajero=$r[0];

//fecha del corte
	$complemento=" del ".$fecha_corte;
//variable del total de pagos	
	$total_pagos_corte=0;
	$total_gastos=0;
	mysql_query("BEGIN");//marcamos el inicio de la trasnacción


//checamos si el corte ya habia sido validado para eliminar el detalle y sobreescribir
	$sql="SELECT IF(hora_fin!='00:00:00',1,0) FROM ec_sesion_caja WHERE id_sesion_caja=$id_sesion_cajero";
	$eje=mysql_query($sql);
	if(!$eje){
		$error=mysql_error();
		mysql_query("ROLLBACK");//cancelamos la transacción
		die("Error al ver si el corte ya habia sido insertado anteriormente!!!".$error);
	}
	
	$r=mysql_fetch_row($eje);
	//die("r[0]=".$r[0]);
	if($r[0]=='1'){
		$sql="DELETE FROM ec_sesion_caja_detalle WHERE id_corte_caja=$id_sesion_cajero";
		$eje=mysql_query($sql);
		if(!$eje){
			$error=mysql_error();
			mysql_query("ROLLBACK");//cancelamos la transacción
			die("Error al eliminar el detalle anterior!!!".$error);
		}	
	}


//insertamos las tarjetas
	for($i=0;$i<sizeof($arr_tarjetas)-1;$i++){//insertamos las tarjetas
		$arr=explode("~", $arr_tarjetas[$i]);
		//$sql="INSERT INTO ec_sesion_caja_detalle VALUES(null,$id_sesion_cajero,$arr[0],'-1',$arr[1],0,'',now(),1)";
		$sql="INSERT INTO ec_sesion_caja_detalle
				SELECT
					null,
					$id_sesion_cajero,
					$arr[0],
					id_banco,
					$arr[1],
					0,
					'',
					now(),
					0,
					$user_sucursal,
					1
				FROM ec_afiliaciones
				WHERE id_afiliacion=$arr[0]";
		$eje=mysql_query($sql);
		if(!$eje){
			$error=mysql_error();
			mysql_query("ROLLBACK");//cancelamos la transacción
			die("Error al insertar el detalle de tarjetas en la sesión de caja\n".$error."\n".$sql);
		}
		$total_pagos_corte+=$arr[1];
		$contador_ingresos++;
	}

//insertamos lo cheques
	for($i=0;$i<sizeof($arr_cheques)-1;$i++){//insertamos las tarjetas
		$arr=explode("~", $arr_cheques[$i]);
		$sql="INSERT INTO ec_sesion_caja_detalle VALUES(null,$id_sesion_cajero,'-1',$arr[0],$arr[1],0,'$arr[2]',now(),0,$user_sucursal,1)";
		$eje=mysql_query($sql);
		if(!$eje){
			$error=mysql_error();
			mysql_query("ROLLBACK");//cancelamos la transacción
			die("Error al insertar el detalle de cheques/transferencias en la sesión de caja\n".$error.$sql);
		}
		$total_pagos_corte+=$arr[1];
		$contador_ingresos++;
	}

/*insertamos el efectivo*/
	$sql="INSERT INTO ec_sesion_caja_detalle VALUES(null,$id_sesion_cajero,'-1','-1',$monto_en_efectivo,0,'efectivo',now(),0,$user_sucursal,1)";
	$eje=mysql_query($sql);
	if(!$eje){
		$error=mysql_error();
		mysql_query("ROLLBACK");//cancelamos la transacción
		die("Error al insertar el detalle de tarjetas en la sesión de caja\n".$error);
	}
	$total_pagos_corte+=$monto_en_efectivo;
	$contador_ingresos++;
	$contador_ingresos=$contador_ingresos*3;

/*cerramos la sesión de la caja*/
	$sql="UPDATE ec_sesion_caja 
			SET hora_fin=IF(hora_fin='00:00:00',
							IF('$insertar_corte'='1',/*aqui se condiciona la hora por si se paso del dia en que se inicio la sesion de caja*/
								'23:59:59',
								now()
							),
							hora_fin
						),
			total_monto_ventas=$total_ingresos,
			observaciones=REPLACE(observaciones,'1___','')/*quitamos el indicador de corte pendiente de imprimir*/ 
			WHERE id_sesion_caja=$id_sesion_cajero";
	$eje=mysql_query($sql);
	if(!$eje){
		$error=mysql_error();
		mysql_query("ROLLBACK");//cancelamos la transacción
		die("Error al cerrar las sesión de caja!!!\n".$error);
	}
/*implementacion Oscar 21.11.2019 para meter el segundo corte cuando la hora es mayor a las 12:00:00*/
	if($insertar_corte==1){
	//consultamos el tipo de sistema
		$sql="SELECT id_sucursal FROM sys_sucursales WHERE acceso=1";
		$eje=mysql_query($sql);
		if(!$eje){
			$error=mysql_error();
			mysql_query("ROLLBACK");//cancelamos la transacción
			die("Error al consultar el tipo de sistema!!!".$error);
		}
		$r_suc=mysql_fetch_row($eje);
	//Generamos el Folio
		$sql="SELECT
				CONCAT(
					IF((SELECT suc.id_sucursal FROM sys_sucursales suc WHERE suc.acceso=1)=-1,'LNA',''),
					'SC',
					s.prefijo,
					IF(
						ISNULL(MAX(CAST(REPLACE(folio, CONCAT(IF($r_suc[0]=-1,'LNA',''),'SC',s.prefijo), '') AS SIGNED INT))),
						1,
						MAX(CAST(REPLACE(folio, CONCAT(IF($r_suc[0]=-1,'LNA',''),'SC',s.prefijo), '') AS SIGNED INT))+1
					)
				) AS folio
				FROM ec_sesion_caja sc
				LEFT JOIN sys_sucursales s ON sc.id_sucursal=s.id_sucursal
				WHERE REPLACE(folio,CONCAT(IF($r_suc[0]=-1,'LNA',''),'SC',s.prefijo), '') REGEXP ('[0-9]')
				AND s.id_sucursal='$user_sucursal'";
		$eje_1=mysql_query($sql);
		if(!$eje_1){
			$error=mysql_error();
			mysql_query("ROLLBACK");//cancelamos la transacción
			die("Error al construir el folio de sesión de caja!!!\n".$error);
		}
		$fol=mysql_fetch_row($eje_1);
		$folio=$fol[0];	
	//insertamos el registro de sesion de caja
		$sql="INSERT INTO ec_sesion_caja VALUES(null,$user_id,$sucursal_id,'$folio',now(),'00:00:01','00:00:00',0,0,0,0,1,-1,'1___')";	
		$eje=mysql_query($sql);
		if(!$eje){
			$error=mysql_error();
			mysql_query("ROLLBACK");//cancelamos la transacción
			die("Error al insertar la segunda sesión de caja por ventas despues del día!!!\n".$error);
		}
	}
/*Fin de cambio Oscar 21.11.2019*/

	mysql_query("COMMIT");//autorizamos la transacción

	//reconsultamos los datos de hora y fecha para filtrar descuentos
	$sql="SELECT CONCAT(fecha,' ',hora_inicio),CONCAT(fecha,' ',hora_fin) FROM ec_sesion_caja WHERE id_sesion_caja=$id_sesion_cajero";
	//die($sql);
	$eje_rangos=mysql_query($sql)or die("Error al consultar los rangos de fechas de sesion de cajero!!!\n".mysql_error()."\n".$sql);
	$row_rango=mysql_fetch_row($eje_rangos);

	$sql="SELECT folio_nv,subtotal,descuento FROM ec_pedidos WHERE subtotal!=total AND (fecha_alta BETWEEN'$row_rango[0]' AND '$row_rango[1]')";
	//echo $sql;
	$eje_desc=mysql_query($sql)or die("Error!!!\n".mysql_error());

	$tam_descuentos=mysql_num_rows($eje_desc)*9;
	
	extract($_POST);
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
	$ticket = new TicketPDF("P", "mm", array(80, (120+$resAprox*9)+5+$contador_ingresos+$tam_gastos+$tam_descuentos) , "{$sucursal}", "{$folio}", 10);
	//echo 'res:'.$resAprox;
	$ticket->AliasNbPages();
	$ticket->AddPage();
	
	$bF=10;
	/*sacamos hora
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
		}*/

//genearmos encabezado
	$ticket->SetFont('Arial','B',$bF+4);
	$ticket->SetXY(8, 5);//$ticket->GetY()+3
	$ticket->Cell(60, 6, utf8_decode("Folio: ".$folio_sesion_caja), "" ,0, "R");

	$ticket->SetFont('Arial','B',$bF);
	$ticket->SetXY(8, $ticket->GetY()+5);
	$ticket->Cell(60, 6, utf8_decode("Fecha y hora de corte:"), "" ,0, "L");
	/*$ticket->SetXY(10, $ticket->GetY()+3);//
	$ticket->Cell(58, 6, utf8_decode(date("d/m/Y H:i:s")), "" ,0, "");*/
	
/*Implementacion Oscar 04.12.2019 para meter el intervalo del corte de caja*/
	$sql="SELECT CONCAT('Apertura: ',fecha,' ',hora_inicio),CONCAT('Cierre     : ',fecha,' ',hora_fin) 
	FROM ec_sesion_caja WHERE id_sesion_caja=$id_sesion_cajero";
	$eje_crte=mysql_query($sql)or die("Error al consultar datos del corte de caja!!!");
	$r_crte=mysql_fetch_row($eje_crte);
	$ticket->SetXY(7, $ticket->GetY()+3);
	$ticket->Cell(66, 6, utf8_decode($r_crte[0]), "" ,0, "L");

	$ticket->SetXY(7, $ticket->GetY()+3);
	$ticket->Cell(66, 6, utf8_decode($r_crte[1]), "" ,0, "L");
/*Fin de cambio Oscar 04.12.2019*/


	$ticket->SetXY(7, 20);
	$ticket->Cell(66, 6, utf8_decode("CASA DE LAS LUCES"), "" ,0, "C");

	$ticket->SetFont('Arial','B',8);
	$ticket->SetXY(7, 25);
	$ticket->Cell(66, 6, utf8_decode("Arqueo de caja ".$complemeto), "" ,0, "C");

	$ticket->SetXY(7, $ticket->GetY()+5);
	//$ticket->Cell(66, 6, utf8_decode($consultaHora), "" ,0, "C");

//sucursal
	$sql="SELECT nombre FROM sys_sucursales WHERE id_sucursal=$sucursal_id";
	$eje=mysql_query($sql) or die('Error: '.mysql_error());
	$rw=mysql_fetch_row($eje);
	$ticket->SetFont('Arial','',$bF);
	$ticket->SetXY(7, $ticket->GetY()+2);
	$ticket->Cell(66, 6, utf8_decode("Sucursal: ".$rw[0]), "" ,0, "C");

	$ticket->SetFont('Arial','B',$bF+2);
	$ticket->SetXY(7, $ticket->GetY()+4);
	$ticket->Cell(66, 6, utf8_decode($datos_cajero), "" ,0, "C");
	
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
	$ticket->Cell(60, 6, utf8_decode("Ingreso Interno: $ ".round($arr_ingresos[0])), "" ,0, "R");//-$dat[6])
	$ticket->SetFont('Arial','',$bF);
	//echo $arr_ingresos[0];

	if($arr_ingresos[1]>0){
		$ticket->SetXY(10, $ticket->GetY()+5);
		$ticket->Cell(60, 6, utf8_decode("Ingreso Externo: $ ".round($arr_ingresos[1])), "" ,0, "R");
		//$ticket->SetFont('Arial','',$bF);
	//echo $arr_ingresos[1];
	}
/*Fin de cambio*/

	$ticket->SetFont('Arial','B',$bF);
	$ticket->SetXY(10, $ticket->GetY()+5);
	$ticket->Cell(60, 6, utf8_decode("Total de Ingresos: ".$total_ingresos), "" ,0, "R");
	
//tarjetas
	for($i=0;$i<sizeof($arr_tarjetas)-1;$i++){//insertamos las tarjetas
		$arr=explode("~", $arr_tarjetas[$i]);
		$sql="SELECT no_afiliacion FROM ec_afiliaciones WHERE id_afiliacion=$arr[0]";
		$eje=mysql_query($sql)or die("Error al consultar las afiliaciones de tarjetas!!!\n".mysql_error());
		$r=mysql_fetch_row($eje);
		$ticket->SetFont('Arial','',$bF);
		$ticket->SetXY(10, $ticket->GetY()+5);
		$ticket->Cell(60, 6, utf8_decode($r[0].": ".$arr[1]), "" ,0, "R");
	}

//cheques/transferencias
	for($i=0;$i<sizeof($arr_cheques)-1;$i++){//insertamos las tarjetas
		$arr=explode("~", $arr_cheques[$i]);
		$ticket->SetFont('Arial','',$bF);
		$ticket->SetXY(10, $ticket->GetY()+5);
		$ticket->Cell(60, 6, utf8_decode($arr[2].": ".$arr[1]), "" ,0, "R");
	}

	$ticket->SetFont('Arial','B',$bF);
	$ticket->SetXY(10, $ticket->GetY()+5);
	$ticket->Cell(60, 6, utf8_decode("Ingreso en Efectivo: ".$subt_in_efe), "" ,0, "R");
	$ticket->SetFont('Arial','',$bF);

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
		$total_gastos+=$detG[2];
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
	$ticket->Cell(60, 6, utf8_decode("Total de Gastos: ".$total_gastos), "" ,0, "R");
//efectivo en caja
	$ticket->SetXY(10, $ticket->GetY()+5);
	$ticket->Cell(60, 6, utf8_decode("Efectivo en Caja: $".$monto_en_efectivo), "" ,0, "R");//-$total_gastos
	
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


//	$nombre_ticket="ticket_".$user_sucursal."_".date("YmdHis")."_".strtolower($tipofolio)."_".$folio."_$noImp.pdf";
	$nombre_ticket="ticket_".$user_sucursal."_".date("YmdHis")."_".$folio_sesion_caja.".pdf";

	$eje=mysql_query($sql) or die("Error\n".mysql_error());
	while($rw=mysql_fetch_row($eje_desc)){
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
/*Fin de cambio Oscar 18.06.2019*/


?>