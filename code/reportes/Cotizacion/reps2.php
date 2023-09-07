<?php

 	include("../../../conectMin.php");
		require_once('../../../include/fpdf/fpdf.php');

 extract($_GET);

class PDF extends FPDF
{

		function LoadData($id,$tipo)
		{
			if($tipo == 1)
			{
				$data = array();
				$query = "SELECT
						id_venta AS Folio,
						fecha AS Fecha,
						hora AS Hora,
						CONCAT('$',FORMAT(total,2)) AS Total
						FROM tf_ventas v
						WHERE 1
						ORDER BY fecha DESC, hora DESC";

			}
			$resultado = mysql_query($query)	or die ("Ventas: ".mysql_error());
				while($fila = mysql_fetch_row($resultado))
				{
					array_push($data,$fila);
				}
				return $data;	
		}


		function ImprovedTable($header, $data)
		{
			$this->SetFillColor(204,204,204);
		    $this->SetTextColor(0);
		    $this->SetFont('');
		    // Anchuras de las columnas
		    $w = array(40, 35, 45, 40);
		    // Cabeceras
		    $this->Cell(60);
		    for($i=0;$i<count($header);$i++)
		        $this->Cell($w[$i],7,$header[$i],1,0,'C',true);
		    $this->Ln();
		    // Datos
		    $this->Cell(60);
		    foreach($data as $row)
		    {
		        $this->Cell($w[0],6,$row[0],'1');
		        $this->Cell($w[1],6,$row[1],'1');
		        $this->Cell($w[2],6,$row[2],'1');
		        $this->Cell($w[3],6,$row[3],'1',0,'R');
			   $this->Ln();
			   $this->Cell(60);
		    }
		    // Línea de cierre
		    $this->Cell(array_sum($w),0,'','T');
		}

		function Header($tipo)
		{
		    // Select Arial bold 15
			if($tipo == 1)
				$titulo = '"'.''.'"';
		

		    $this->SetFont('Arial','',15);
		    // Move to the right
		    $this->Cell(130);
		    // Framed title

		    $this->Cell(30,10,utf8_decode($titulo),0,0,'C');

		    $this->Image('images/logo.png',10,10,-100);
		    // Line break
		    $this->Ln(30);
		}



}

$pdf = new PDF('L');
// Títulos de las columnas

// Carga de datos

$pdf->SetFont('Arial','',10);
$pdf->AddPage();
$pdf->Header(1);
$header = array('Fecha', 'Nombre','Clase','Profesor');
$data =$pdf->LoadData(0,1);
 $pdf->Cell(60);
$pdf->SetFont('Arial','',10); 
$pdf->SetFillColor(204,204,204);
$pdf->SetTextColor(0);
$pdf->SetDrawColor(0);
$pdf->SetLineWidth(.3);  
$pdf->Cell(45,5,"Sucursal",1,0,'C',true);
$pdf->Cell(70,5,"Responsable",1,0,'C',true);
$pdf->Cell(45,5,"Fecha",1,0,'C',true);
$pdf->Ln();
$pdf->SetFillColor(255);
$pdf->SetTextColor(0);
$pdf->SetDrawColor(0);
$pdf->SetLineWidth(.3);  
 $pdf->Cell(60);
$pdf->Cell(45,5,$sucursal_name,1,0,'C',true);
$pdf->Cell(70,5,utf8_decode($user_fullname),1,0,'C',true);
$pdf->Cell(45,5,date('y-m-d'),1,0,'C',true);
$pdf->Ln(15);
$pdf->ImprovedTable($header,$data);
$pdf->Ln();
$pdf->Cell(135,35);
$pdf->SetFillColor(204,204,204);
$pdf->SetTextColor(0);
$pdf->SetDrawColor(0);
$pdf->SetLineWidth(.3);
$pdf->Cell(45,8,"Total",1,0,'C',true);
$pdf->SetFillColor(224,235,255);
$pdf->SetTextColor(0);
 $pdf->SetFont('');
 $pdf->Cell(40,8,$total=obtenerTotal() ,1,0,'R');

$pdf->output();

function obtenerTotal()
{
		$query = "SELECT
				  CONCAT('$',FORMAT(SUM(total),2)) AS Total
				  FROM tf_ventas v
				  WHERE 1
				  ORDER BY fecha DESC, hora DESC";

			
			$resultado = mysql_query($query)	or die ("Ventas: ".mysql_error());
			$fila = mysql_fetch_row($resultado);
			
	return $fila[0];
}
?>
