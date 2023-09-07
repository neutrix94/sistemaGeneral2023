<?php

 	include("../../../conectMin.php");
		require_once('../../../include/fpdf/fpdf.php');

 extract($_GET);

	$query= "SELECT
			ci.titular,
			ci.telefono_fijo,
			'',
			vs.es_xv,
			vs.con_iva,
			vs.nombre_evento,
			ci.festejado_empresa,
			ci.telefono_celular,
			ci.correo,
			ci.fecha_evento,
			vs.hora
			FROM
			tf_venta_servicios vs
			JOIN tf_clientes c
			JOIN tf_citas ci
			WHERE
			vs.id_cliente = c.id_cliente
			AND vs.id_cita = ci.id_cita
			AND vs.id_venta_servicio = $id"; 
	$res = mysql_query($query) or die ("No se pudo realizar la consulta: \n\n$query\n\n".mysql_error());
	$fila = mysql_fetch_row($res);

	  $query = "SELECT
				'',
				'',
				vs.total
				FROM
			tf_venta_servicios vs
				WHERE
			 vs.id_venta_servicio = '$id'";

	$res      = mysql_query($query) or die ("No se pudo realizar la consulta: \n\n$query\n\n".mysql_error());
	$row1     = mysql_fetch_row($res);	
	$subtotal = $row1[2]/1.16;
	$sub =      $row1[2]-$subtotal;
	$total    = number_format($row1[2],2);		
 
 class PDF extends FPDF{

 	function LoadData($id,$tipo)
    {
   
    //------------------------------- Datos Servicios ------------------------------//
    if($tipo == 0)
    {
    		$query = "SELECT
				s.nombre,
				ds.cantidad,
				CONCAT('$ ',FORMAT(ds.precio,2)) as precio
				FROM
				tf_detalles_servicio ds
				JOIN tf_venta_servicios vs
				JOIN tf_servicios s
				JOIN tf_clientes c
				WHERE
				ds.id_venta_servicio = vs.id_venta_servicio
				AND ds.id_servicio = s.id_servicio
				AND vs.id_cliente = c.id_cliente
				AND ds.id_venta_servicio = '$id'" ;

    }
    if($tipo == 1)
    {
    	$query = "SELECT  
				  b.nombre
				  FROM tf_detalle_bailarin db
				  JOIN bailarines b
				  WHERE db.id_trabajador = b.id
				  AND db.id_venta_servicio = '$id'";

    }
    if($tipo == 2)
    {
    	$query = "SELECT 
				d.nombre,
				de.hora_ini,
				de.hora_fin
				FROM tf_detalle_ensayos de
				JOIN tf_dias d
				WHERE
				de.id_dia=d.id_dia
				AND de.id_venta_servicio='$id'";

    }

      if($tipo == 3)
    {
    	$query = "SELECT 
				c.nombre
				FROM tf_detalle_coreografia dc
				JOIN tf_coreografias c 
				WHERE dc.id_coreografia = c.id_coreografia
				AND dc.id_venta_servicio =  '$id'";

    }			
	   
	  $data = array();
	  $res = mysql_query($query) or die ("No se pudo realizar la consulta: \n\n$query\n\n".mysql_error());
	  	while($fila = mysql_fetch_row($res))
	  	{
	  		
	  		array_push($data,$fila);
	  	}	
	   
	    return $data;
    }
		function BasicTable($header, $data)
		{
		    // Cabecera
		    foreach($header as $col)
		        $this->Cell(40,7,$col,1);
		    $this->Ln();
		    // Datos
		     foreach($data as $row)
    {
        foreach($row as $col)
            $this->Cell(40,6,$col,1);
            $this->Ln();
    }
		}


function ImprovedTable($header,$data,$tipo)
{
    
   
    $this->SetFillColor(236,36,136);
    $this->SetTextColor(255);
    $this->SetDrawColor(236,36,136);
    $this->SetLineWidth(.3);
    $this->SetFont('','B');
    if($tipo == 0 || $tipo == 2)
    {	
    	$w = array(100, 35, 45);
	}
	if($tipo == 1 )
    {	
    	$w = array(180);
	}
	if($tipo == 3 )
    {	
    	$w = array(180);
	}

    // Cabeceras
    for($i=0;$i<count($header);$i++)
        $this->Cell($w[$i],7,utf8_decode($header[$i]),1,0,'C',true);
    $this->Ln();
    // Datos
    // Restauración de colores y fuentes
    $this->SetFillColor(224,235,255);
    $this->SetTextColor(0);
    $this->SetFont('');
    // Datos
    $fill = false;
    if($tipo == 0 || $tipo == 2)
    {  	
	    foreach($data as $row)
	    {
	        $this->Cell($w[0],6,utf8_decode($row[0]),'LR');
	        $this->Cell($w[1],6,$row[1],'LR',0,'C');
	        $this->Cell($w[2],6,$row[2],'LR',0,'C');
	        $this->Ln();
	    }
	    $this->Cell(array_sum($w),0,'','T');
    }
     if($tipo == 1)
    {  	
	    foreach($data as $row)
	    {
	        $this->Cell($w[0],6,utf8_decode($row[0]),'LR',0,'C');
	       $this->Ln();
	    }
	    $this->Cell(array_sum($w),0,'','T');
    }
     if($tipo == 3)
    {  	
	    foreach($data as $row)
	    {
	        $this->Cell($w[0],6,utf8_decode($row[0]),'LR',0,'C');
	        $this->Ln();
	    }
	    $this->Cell(array_sum($w),0,'','T');
    }
    // Línea de cierre
    
}

function Header($tipo)
{
    // Select Arial bold 15
	if($tipo == 1)
		$titulo = '"'.'XV años'.'"';
	else
		$titulo = '"'.'Otros Servicios'.'"';
    $this->SetFont('Arial','B',15);
    // Move to the right
    $this->Cell(80);
    // Framed title

    $this->Cell(30,10,utf8_decode($titulo),0,0,'C');

    $this->Image('images/logo.png',10,10,-100);
    // Line break
    $this->Ln(20);
}
}

 
$pdf = new PDF();


//inserto la cabecera poniendo una imagen dentro de una celda
//Títulos de las columnas
	
	$pdf->AddPage();
	$pdf->Header($fila[3]);
	$pdf->SetFont('Arial','',10);
	$pdf->SetXY(10,35);
	$pdf->Cell(100,12,"No. Cotizacion: ". $id);
	$pdf->Cell(100,12,"Fecha: ". date('d/m/Y'));
	$pdf->SetXY(10,48);
	$pdf->Cell(100,12,"Nombre: ".utf8_decode($fila[0]));
	$pdf->Cell(100,12,"Celular: ".$fila[7]);
	$pdf->SetXY(10,61);
	$pdf->Cell(100,12,"Telefono: ".$fila[1]);
	$pdf->Cell(100,12,"Correo: ".$fila[8]);
	$pdf->SetXY(10,75);
	$pdf->Cell(100,12,"Festejado/empresa: ".utf8_decode($fila[6]));
	$pdf->Cell(100,12,"Evento: ".utf8_decode($fila[5]));
	$pdf->SetXY(10,90);
	$pdf->Cell(100,12,"Fecha del evento: ".utf8_decode($fila[9]));
	$pdf->Cell(100,12,"Hora del evento: ".utf8_decode($fila[9]));
	$pdf->Ln(20);
	
if($fila[3] == '0')
{	
	$pdf->SetFont('Arial','B',20);
	$pdf->Cell(100,12,"Detalle de Servicios");
	$header = array('Servicio', 'Cantidad', 'Monto');
	$data   = $pdf->LoadData($id,0);
	$pdf->SetFont('Arial','',14);
	$pdf->ln(10);
	$pdf->ImprovedTable($header,$data,0);
	$pdf->Ln(7);
	

}
if($fila[3]== '1')
{
	$pdf->Ln(10);
	$pdf->SetFont('Arial','B',20);
	$pdf->Cell(100,12,"Detalle de Bailarines");
		$pdf->Ln(10);
	$header = array('Bailarines');
	$data   = $pdf->LoadData($id,1);
	$pdf->SetFont('Arial','',14);
	$pdf->Ln(10);
	$pdf->ImprovedTable($header,$data,1);
	$pdf->Ln(5);
	$pdf->SetFont('Arial','B',20);
	$pdf->Cell(100,12,"Detalle de Ensayos");
	$pdf->Ln(10);
	$header ="";
	$header = array('Dia','Hora Inicio','Hora Fin');
	$data   = $pdf->LoadData($id,2);
	$pdf->SetFont('Arial','',14);
	$pdf->ImprovedTable($header,$data,2);
	$pdf->Ln(7);
	$pdf->SetFont('Arial','B',20);
	$pdf->Cell(100,12,utf8_decode("Detalle de Coreografías"));
	$pdf->Ln(10);
$header ="";
	$header = array('Coreografías');
	$data   = $pdf->LoadData($id,3);
	$pdf->SetFont('Arial','',14);
	$pdf->ImprovedTable($header,$data,3);
	$pdf->Ln(7);

}


	//$pdf->SetXY(10,220);
	$pdf->Cell(100,35);
$pdf->SetFillColor(236,36,136);
	$pdf->SetTextColor(255);
	$pdf->SetDrawColor(236,36,136);
	$pdf->SetLineWidth(.3);

if($fila[4] == 1)
{
	$pdf->Cell(35,8,"Subtotal",1,0,'R',true);
    $pdf->SetFillColor(224,235,255);
    $pdf->SetTextColor(0);
    $pdf->SetFont('');
$pdf->Cell(45,8,"$ ".number_format($subtotal,2),1,0,'R');
$pdf->Ln();
$pdf->Cell(100,35);
$pdf->SetFillColor(236,36,136);
$pdf->SetTextColor(255);
$pdf->SetDrawColor(236,36,136);
$pdf->SetLineWidth(.3);
$pdf->Cell(35,8,"IVA",1,0,'R',true);
$pdf->SetFillColor(224,235,255);
    $pdf->SetTextColor(0);
    $pdf->SetFont('');
$pdf->Cell(45,8,"$ ".number_format($sub,2),1,0,'R');
$pdf->Ln();
$pdf->Cell(100,35);
$pdf->SetFillColor(236,36,136);
$pdf->SetTextColor(255);
$pdf->SetDrawColor(236,36,136);
$pdf->SetLineWidth(.3);
$pdf->Cell(35,8,"Total",1,0,'R',true);
    $pdf->SetFillColor(224,235,255);
    $pdf->SetTextColor(0);
    $pdf->SetFont('');
    $pdf->Cell(45,8,"$ ".$total ,1,0,'R');
}
if($fila[4]==0)
{
	$pdf->Cell(35,8,"Total",1,0,'R',true);
    $pdf->SetFillColor(224,235,255);
    $pdf->SetTextColor(0);
    $pdf->SetFont('');
    $pdf->Cell(45,8,"$ ".$total ,1,0,'R');
}




$pdf->output();

?>
