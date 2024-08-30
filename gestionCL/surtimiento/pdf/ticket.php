<?php

	# Incluyendo librerias necesarias #
    require "./code128.php";

    $pdf = new PDF_Code128('P','mm',array(80,258));
    $pdf->SetMargins(4,10,4);
    $pdf->AddPage();
    
    $pdf->SetFont('Arial','B',14);
    $pdf->SetTextColor(0,0,0);
    $pdf->MultiCell(0,5,iconv("UTF-8", "ISO-8859-1",strtoupper("SURTIMIENTO")),0,'C',false);
    $pdf->SetFont('Arial','',9);
    // $pdf->MultiCell(0,5,iconv("UTF-8", "ISO-8859-1","RUC: 0000000000"),0,'C',false);
    // $pdf->MultiCell(0,5,iconv("UTF-8", "ISO-8859-1","Direccion San Salvador, El Salvador"),0,'C',false);
    // $pdf->MultiCell(0,5,iconv("UTF-8", "ISO-8859-1","Teléfono: 00000000"),0,'C',false);
    // $pdf->MultiCell(0,5,iconv("UTF-8", "ISO-8859-1","Email: correo@ejemplo.com"),0,'C',false);

    $pdf->Ln(1);
    $pdf->Cell(0,5,iconv("UTF-8", "ISO-8859-1","------------------------------------------------------"),0,0,'C');
    $pdf->Ln(5);

    $pdf->MultiCell(0,5,iconv("UTF-8", "ISO-8859-1","Estado de México"),0,'C',false);
    $pdf->MultiCell(0,5,iconv("UTF-8", "ISO-8859-1","Fecha: ".date("d/m/Y", strtotime(date('d-m-Y')))." ".date("h:s A")),0,'C',false);
    $pdf->MultiCell(0,5,iconv("UTF-8", "ISO-8859-1","Surtidor: X"),0,'C',false);
    //$pdf->MultiCell(0,5,iconv("UTF-8", "ISO-8859-1","Cajero: Carlos Alfaro"),0,'C',false);
    $pdf->SetFont('Arial','B',10);
    $pdf->MultiCell(0,5,iconv("UTF-8", "ISO-8859-1",strtoupper("No. Pedido: X")),0,'C',false);
    $pdf->SetFont('Arial','',9);


    /* INICIO PRODUCTO */
    $pdf->Ln(5);
    $pdf->Cell(0,5,iconv("UTF-8", "ISO-8859-1","-------------------------------------------------------------------"),0,0,'C');
    $pdf->Ln(5);

    $pdf->MultiCell(0,4,iconv("UTF-8", "ISO-8859-1","LUCES LED 50 PIEZAS"),0,'C',false);
    $pdf->Cell(20,5,iconv("UTF-8", "ISO-8859-1","Cant. solicitada"),0,0,'C');
    $pdf->Cell(30,5,iconv("UTF-8", "ISO-8859-1","Cant. surtida"),0,0,'C');
    $pdf->Cell(20,5,iconv("UTF-8", "ISO-8859-1","Cant. no surtida"),0,0,'C');

    $pdf->Ln(3);
    //$pdf->Cell(72,5,iconv("UTF-8", "ISO-8859-1","-------------------------------------------------------------------"),0,0,'C');
    $pdf->Ln(3);

    $pdf->Cell(20,5,iconv("UTF-8", "ISO-8859-1","5"),0,0,'C');
    $pdf->Cell(30,5,iconv("UTF-8", "ISO-8859-1","4"),0,0,'C');
    $pdf->Cell(20,5,iconv("UTF-8", "ISO-8859-1","1"),0,0,'C');
    /* FIN PRODUCTO */




    /* INICIO PRODUCTO */
    $pdf->Ln(5);
    $pdf->Cell(0,5,iconv("UTF-8", "ISO-8859-1","-------------------------------------------------------------------"),0,0,'C');
    $pdf->Ln(5);

    $pdf->MultiCell(0,4,iconv("UTF-8", "ISO-8859-1","ARBOL 1M ALTURA"),0,'C',false);
    $pdf->Cell(20,5,iconv("UTF-8", "ISO-8859-1","Cant. solicitada"),0,0,'C');
    $pdf->Cell(30,5,iconv("UTF-8", "ISO-8859-1","Cant. surtida"),0,0,'C');
    $pdf->Cell(20,5,iconv("UTF-8", "ISO-8859-1","Cant. no surtida"),0,0,'C');

    $pdf->Ln(3);
    //$pdf->Cell(72,5,iconv("UTF-8", "ISO-8859-1","-------------------------------------------------------------------"),0,0,'C');
    $pdf->Ln(3);
    /*----------  Detalles de la tabla  ----------*/
    $pdf->Cell(20,5,iconv("UTF-8", "ISO-8859-1","5"),0,0,'C');
    $pdf->Cell(30,5,iconv("UTF-8", "ISO-8859-1","4"),0,0,'C');
    $pdf->Cell(20,5,iconv("UTF-8", "ISO-8859-1","1"),0,0,'C');
    /* FIN PRODUCTO */




    /* INICIO PRODUCTO */
    $pdf->Ln(5);
    $pdf->Cell(0,5,iconv("UTF-8", "ISO-8859-1","-------------------------------------------------------------------"),0,0,'C');
    $pdf->Ln(5);

    # Tabla de productos #
    $pdf->MultiCell(0,4,iconv("UTF-8", "ISO-8859-1","ARBOL 1M ALTURA"),0,'C',false);
    $pdf->Cell(20,5,iconv("UTF-8", "ISO-8859-1","Cant. solicitada"),0,0,'C');
    $pdf->Cell(30,5,iconv("UTF-8", "ISO-8859-1","Cant. surtida"),0,0,'C');
    $pdf->Cell(20,5,iconv("UTF-8", "ISO-8859-1","Cant. no surtida"),0,0,'C');

    $pdf->Ln(3);
    //$pdf->Cell(72,5,iconv("UTF-8", "ISO-8859-1","-------------------------------------------------------------------"),0,0,'C');
    $pdf->Ln(3);
    $pdf->Cell(20,5,iconv("UTF-8", "ISO-8859-1","5"),0,0,'C');
    $pdf->Cell(30,5,iconv("UTF-8", "ISO-8859-1","4"),0,0,'C');
    $pdf->Cell(20,5,iconv("UTF-8", "ISO-8859-1","1"),0,0,'C');
    /* FIN PRODUCTO */
    

    $pdf->Ln(9);

    # Codigo de barras #
    $pdf->Code128(5,$pdf->GetY(),"COD000001V0001",70,20);
    $pdf->SetXY(0,$pdf->GetY()+21);
    $pdf->SetFont('Arial','',14);
    $pdf->MultiCell(0,5,iconv("UTF-8", "ISO-8859-1","COD000001V0001"),0,'C',false);
    
    # Nombre del archivo PDF #
    if (isset($_GET['savePath']) && !empty($_GET['savePath'])) {
        $savePath = $_GET['savePath'] . '/Ticket_Nro_1.pdf';
        // Verifica si el directorio existe
        $directory = dirname($savePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);  // Crea el directorio con permisos adecuados
        }


        //$save_path = $_GET['savePath'] . DIRECTORY_SEPARATOR . $filename;
        //$save_path = $_GET['savePath']. DIRECTORY_SEPARATOR . $filename;
        $pdf->Output("F", $savePath); // Guarda el PDF en la ruta especificada
    } else {
        $pdf->Output("I", $filename, true); // Muestra el PDF en el navegador
    }
    //$pdf->Output("I","Ticket_Nro_1.pdf",true);