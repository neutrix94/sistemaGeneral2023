<?php
	//header("Content-type: application/pdf");
	//librerias de funcion	
	include("../../include/fpdf153/nclasepdf.php");	
	//CONECCION Y PERMISOS A LA BASE DE DATOS
	include("../../conectMinTrans.php");
	
	$mesNombre=array(
						1=>'Enero',
						2=>'Febrero',
						3=>'Marzo',
						4=>'Abril',
						5=>'Mayo',
						6=>'Junio',
						7=>'Julio',
						8=>'Agosto',
						9=>'Septiembre',
						10=>'Octubre',
						11=>'Noviembre',
						12=>'Diciembre'						
					);
	$impDoc=0;$fpdf=true;
	//extract($_GET);
	//extract($_POST);
	
	
	
	
	
	
	
	$orient_doc="P";
	$unid_doc="cm";
	$alto_doc=27.9;
	$ancho_doc=21.6;
	$ftam=10;
	$tamano_doc=array($ancho_doc,$alto_doc);
	$ypag=27.9;
	$impDoc++;
	include("../pdf/transferencia.php");
	$tipoimpresion="Requisicion";

	
	
	if($impDoc>0)
	{
		//grabaBitacora('8','','0','0',$_SESSION["USR"]->userid,'0',$tipoimpresion,'');
		//Determina el nombre del archivo temporal

		/*$sqlTrans="	SELECT
				t.folio AS ID,
				s.nombre AS 'Suc. origen',
				s2.nombre AS 'Suc. destino',
				CONCAT(t.fecha, ' ', t.hora) AS 'Fecha',
				a.nombre,
				a2.nombre
				FROM ec_transferencias t
				JOIN sys_sucursales s ON t.id_sucursal_origen = s.id_sucursal
				JOIN sys_sucursales s2 ON t.id_sucursal_destino = s2.id_sucursal
				JOIN ec_estatus_transferencia e ON t.id_estado = e.id_estatus
				JOIN ec_almacen a ON t.id_almacen_origen = a.id_almacen
				JOIN ec_almacen a2 ON t.id_almacen_destino = a2.id_almacen
				WHERE t.id_transferencia=$id";
			
		$resTrans=mysql_query($sqlTrans);
		if(!$resTrans)
			die("Error en:<br>$sql<br><br>Descripcion:<br>".mysql_error());		
		
		$rowTrans=mysql_fetch_row($resTrans);	

		//$file=basename(tempnam(getcwd(),'tmp'));

		$file = $rowTrans[0] . "pdf"*/

		//Salva elPDF en un archivo
		//$pdf->Output("../pdf/transferencias/$file"."php");
		$pdf->Output("../pdf/transferencias/$folioTrans");
		//Redireccionamiento por Javascript
		//echo "<HTML><SCRIPT>document.location='getpdf.php?f=$file';</SCRIPT></HTML>";
	}/**/
?>