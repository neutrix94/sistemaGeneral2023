<?php
    include("../../../conectMin.php");
    include("../../../conexionMysqli.php");
	# Incluyendo librerias necesarias #
    require "./code128.php";

    $pdf = new PDF_Code128('P','mm',array(80,258));

    $inputJSON = file_get_contents('php://input');
    error_log( "PARÁMETROS ENVIADOS" );
    error_log( $inputJSON );

    $inputData = json_decode( $inputJSON, true );

    if( $inputData ){
        $folioPedido = $inputData['result']["detalle"]["folioPedido"];
        $vendedor = $inputData['result']["detalle"]["vendedor"];
        $productosParciales = $inputData['result']["detalle"]["surtidoParcial"];

        $pdf->SetMargins(4,10,4);
        $pdf->AddPage();
        
        $pdf->SetFont('Arial','B',14);
        $pdf->SetTextColor(0,0,0);
        $pdf->MultiCell(0,5,strtoupper("TICKET DE SURTIMIENTO"),0,'C',false);
        $pdf->SetFont('Arial','',9);
         
        $pdf->Ln(1);
        $pdf->Cell(0,5,"-------------------------------------------------------------------",0,0,'C');
        $pdf->Ln(10);
    
        $pdf->SetFont('Arial','B',20);
        $pdf->MultiCell(0,5,strtoupper("PEDIDO: " .$folioPedido),0,'C',false);
        $pdf->SetFont('Arial','',9);
        $pdf->Ln(5);
    
        $pdf->SetFont('Arial','B',20);
        $pdf->MultiCell(0,5,strtoupper("VENDEDOR:"),0,'C',false);
        $pdf->SetFont('Arial','',9);
        $pdf->Ln(5);
    
        $pdf->SetFont('Arial','B',20);
        $pdf->MultiCell(0,5,strtoupper( $vendedor ),0,'C',false);
        $pdf->SetFont('Arial','',9);
        $pdf->Ln(5);
    
        //date_default_timezone_set('America/Mexico_City');
        $pdf->SetFont('Arial','B',12);
        $pdf->MultiCell(0,5,"Fecha/Hora: ".date("d/m/Y", strtotime(date('d-m-Y'))." ".date("h:s A"))." ".date('H:i'),0,'C',false);
        $pdf->Ln(5);
        //$pdf->MultiCell(0,5,iconv("UTF-8", "ISO-8859-1","Cajero: Carlos Alfaro"),0,'C',false);
        $pdf->SetFont('Arial','B',12);
        $pdf->MultiCell(0,5,"Surtido por: Bodeguero 1",0,'C',false);
    
        $pdf->SetFont('Arial','',9);
        $pdf->Ln(2);
        $pdf->Cell(0,5,"-------------------------------------------------------------------",0,0,'C');
        $pdf->Ln(2);
    
        $pdf->Ln(2);
        $pdf->Cell(0,5,"-------------------------------------------------------------------",0,0,'C');
        $pdf->Ln(5);
    
        $pdf->SetFont('Arial','B',14);
        $pdf->MultiCell(0,5,"PRODUCTOS QUE NO SE",0,'C',false);
        $pdf->MultiCell(0,5,"SURTIERON COMPLETOS",0,'C',false);
        
    
        $pdf->SetFont('Arial','',9);
        
        $pdf->Ln(5);
    
        /* INICIO PRODUCTO */
        if( count($productosParciales) > 0 ){
            for ($i=0; $i < count($productosParciales); $i++) { 

                $pdf->MultiCell(0,4, $productosParciales[$i]['nombre'] ,0,'C',false);
                $pdf->SetFillColor(200, 200, 200);
                $widths = [20, 20, 20];  // Anchos de las 3 columnas
                $height = 8;  // Altura de las filas
            
                $xPos = ($pdf->GetPageWidth() - array_sum($widths)) / 2;
                // Encabezados de la tabla (sin fondo)
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->SetX($xPos);
                // Encabezados de la tabla
                $pdf->Cell($widths[0], $height, 'Pedido', 0, 0, 'C');
                $pdf->Cell($widths[1], $height, 'Surtido', 0, 0, 'C');
                $pdf->Cell($widths[2], $height, 'Faltante', 0, 1, 'C');
            
                // Primera fila
                $pdf->SetFont('Arial', '', 9);
                $pdf->SetX($xPos);
                $pdf->Cell($widths[0], $height, $productosParciales[$i]['solicitado'] , 0, 0, 'C', true);
                $pdf->Cell($widths[1], $height, $productosParciales[$i]['surtido'] , 0, 0, 'C', true);
                $pdf->Cell($widths[2], $height, $productosParciales[$i]['faltante'] , 0, 0, 'C', true);
                $pdf->Ln(12);
            }
        }
    
        $pdf->Ln(5);
        $pdf->SetFont('Arial','B',12);
        $pdf->MultiCell(0,5,"No olvides ajustar la nota de venta",0,'C',false);
    
        $pdf->SetFont('Arial','',9);
        $pdf->Ln(2);
        $pdf->Cell(0,5,"-------------------------------------------------------------------",0,0,'C');
        $pdf->Ln(2);
    
        $pdf->Ln(2);
        $pdf->Cell(0,5,"-------------------------------------------------------------------",0,0,'C');
        $pdf->Ln(5);
        
        $directory = getFileRoute( $sucursal_id, $user_id, '17', $link );
        error_log('DIRECTORYY:'.$directory);
    
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);  // Crea el directorio con permisos adecuados
        }
        
        $pdf->Output("F", $directory . "/ticket_surtimiento.pdf"); // Guarda el PDF en la ruta especificada
        
    }
    

    
    function getFileRoute( $store_id, $user_id, $module_id, $link ){
        //if( ! include( '../../../especiales/controladores/SysModulosImpresionUsuarios.php' ) ){
        if( ! include( '../../../code/especiales/controladores/SysModulosImpresionUsuarios.php' ) ){
            die( "No se pudo incluir la libreria de descargar de archivos : 'SysModulosImpresionUsuarios'" );
        }
        $SysModulosImpresionUsuarios = new SysModulosImpresionUsuarios( $link );
        if( ! include( '../../../code/especiales/controladores/SysModulosImpresion.php' ) ){
            die( "No se pudo incluir la libreria de descargar de archivos : 'SysModulosImpresion'" );
        }
        $SysModulosImpresion = new SysModulosImpresion( $link );
        $ruta_salida = '';
        $ruta_salida = $SysModulosImpresionUsuarios->obtener_ruta_modulo_usuario( $user_id, $module_id );//etiqueta empaquetado pieza
        if( $ruta_salida == 'no' ){
            $ruta_salida = "cache/" . $SysModulosImpresion->obtener_ruta_modulo( $store_id, $module_id );//etiqueta empaquetado pieza
        }
        return "../../../{$ruta_salida}";
    }
    
