<?php
/****************************Version Oscar 22.11.2019***************************/
    
    include("../../conectMin.php");

//die('here');    
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
    
    extract($_GET);
    
    //Buscamos el numero de abono
    #$sql="SELECT id_pago FROM ec_pedido_pagos WHERE id";
    
    $lineas_productos = 0;
    
    if(!isset($noImp))
        $noImp=1;
    
    
    $cs = "SELECT nombre AS sucursal, prefijo FROM sys_sucursales WHERE id_sucursal = '{$user_sucursal}' ";
    if ($rs = mysql_query($cs)) {
        if ($dr = mysql_fetch_assoc($rs)) {
            $sucursal = $dr["sucursal"];
            $prefijo = $dr["prefijo"];
        } mysql_free_result($rs);
    }
    
    $sql="SELECT
          p.folio_nv,
          p.folio_abono,
          p.total,
          pp.monto,
          tp.nombre AS tipoPago,
          p.id_pedido,
          pp.fecha,
          pp.hora,
          c.nombre AS cliente,
          (SELECT SUM(monto) FROM ec_pedido_pagos WHERE id_pedido=p.id_pedido AND (referencia='' OR referencia=null)) AS pagado,
          p.total-(SELECT SUM(monto) FROM ec_pedido_pagos WHERE id_pedido=p.id_pedido AND (referencia='' OR referencia=null)) AS resta
          FROM ec_pedido_pagos pp
          JOIN ec_pedidos p ON pp.id_pedido = p.id_pedido
          JOIN ec_tipos_pago tp ON pp.id_tipo_pago = tp.id_tipo_pago
          JOIN ec_clientes c ON p.id_cliente = c.id_cliente
          WHERE pp.id_pedido_pago=$id_pago";
    
    $res=mysql_query($sql) or die(mysql_error());
    
    if(mysql_num_rows($res) <= 0) 
        die("No encontrado");
        
    $row=mysql_fetch_assoc($res);
    
    extract($row);   
    
    $folio_abono="A$prefijo".$folio_abono;  
    
    
    //Buscamos el numero de abono
    $sql="SELECT id_pedido_pago FROM ec_pedido_pagos WHERE id_pedido=$id_pedido 
/*implementación Oscar 28.02.2019 para que no se salten folios de abonos cuando hay pago interno y externo*/
    GROUP BY id_pedido,DATE_FORMAT(CONCAT(fecha,' ',hora),'%Y-%m-%d %H:%i')
    /*GROUP BY CONCAT(fecha,' ',hora) */
/*Fin de cambio Oscar 28.02.2019*/
    ORDER BY id_pedido_pago";
    $res=mysql_query($sql) or die(mysql_error());
    $num=mysql_num_rows($res);
    for($i=0;$i<$num;$i++)
    {
        $row=mysql_fetch_row($res);
        if($row[0] == $id_pago)
            break;
    }            
    
    $abono=$i/*+1 Se quita la suma (Oscar 28.02.2019)*/;
    
//Pagos (Modificado por Oscar 09.08.2018 para agrupar los pagos internos con los externos y que salga un solo monto por pago realizado)
    $sql="SELECT
    tp.nombre,
    SUM(pp.monto),
    pp.fecha
    FROM ec_pedido_pagos pp
    JOIN ec_tipos_pago tp ON pp.id_tipo_pago = tp.id_tipo_pago
    WHERE pp.id_pedido=$id_pedido AND (referencia='' OR referencia=null)
    GROUP BY pp.id_pedido,DATE_FORMAT(CONCAT(pp.fecha,' ',pp.hora),'%Y-%m-%d %H:%i:%s')
    ORDER BY pp.id_pedido_pago";
    $res=mysql_query($sql) or die(mysql_error());
    $num=mysql_num_rows($res);
//fin de cambio    
    
    class TicketPDF extends FPDF {
        // Members
        var $sucursal = "";
        var $pedido = "";
        var $inicio = 32;
    
        // Constructor
        function TicketPDF($orientation='P', $unit='mm', $size, $sucursal='', $pedido='', $inicio=32) {
            parent::FPDF($orientation, $unit, $size);
                
            $this->AddFont('Arial');
            $this->SetMargins(15, 0, 15);
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
        }
    
        function AcceptPageBreak() {
            $x = $this->GetX();
            $this->AddPage();
            $this->SetXY($x, 1);
            #$this->SetY($this->inicio);
            return false;
        }
    }
    
    
    $ticket = new TicketPDF("P", "mm", array(80,35+125+$num*6), "{$sucursal}", "{$folio}", 32);
    $ticket->AliasNbPages();
    $ticket->AddPage();

    $bF=10;
    
    $ticket->Image("../img/logo-casa-fondo-blanco.png", 28, 5, 22);
    
    $ticket->SetFont('Arial','B',$bF+4);
    $ticket->SetXY(7, 40);
    $ticket->Cell(66, 6, utf8_decode("CASA DE LAS LUCES"), "" ,0, "C");
    
    $ticket->SetFont('Arial','',$bF+1);
    $ticket->SetXY(7, $ticket->GetY()+4);
    $ticket->Cell(66, 6, utf8_decode("Sucursal {$ticket->sucursal}"), "" ,0, "C");
        
    $ticket->SetFont('Arial','',$bF+2);
    
    $ticket->SetXY(7, $ticket->GetY()+5);
    $ticket->Cell(66, 6, utf8_decode("ABONO: $abono"), "" ,0, "L");
    
    $ticket->SetXY(7, $ticket->GetY()+4);
    $ticket->Cell(66, 6, utf8_decode("APARTADO: $folio_abono"), "" ,0, "L");
    
    $ticket->SetXY(7, $ticket->GetY()+4);
    $ticket->Cell(66, 6, utf8_decode("NOTA DE VENTA: $folio_nv"), "" ,0, "L");
    
    $ticket->SetXY(7, $ticket->GetY()+4);
    $ticket->Cell(66, 6, utf8_decode("TOTAL DE COMPRA: $".number_format($total)), "" ,0, "L");
    
    $ticket->SetXY(7, $ticket->GetY()+4);
    $ticket->Cell(66, 6, utf8_decode("CLIENTE: $cliente"), "" ,0, "L");
    
    $ticket->SetFont('Arial','',$bF);
    
    $ticket->SetXY(7, $ticket->GetY()+6);
    $ticket->Cell(60, 6, utf8_decode("TIPO PAGO"), "B" ,0, "L");
    
    $ticket->SetX(35);
    $ticket->Cell(20, 6, utf8_decode("FECHA"), "" ,0, "L");
    
    $ticket->SetX(43);
    $ticket->Cell(30, 6, utf8_decode("MONTO"), "" ,0, "R");
    
    $ticket->SetXY(20, $ticket->GetY()+1);
    
    $ticket->SetFont('Arial','',$bF-2);
    
//implementación de Oscar para unificar pagos
    $pago_final=0;
    for($i=0;$i<$num;$i++) {
        $row=mysql_fetch_row($res);
        
        $ticket->SetXY(7, $ticket->GetY()+6);
        $ticket->Cell(66, 6, utf8_decode($row[0]), "" ,0, "L");
        
        $ticket->SetX(35);
        $ticket->Cell(20, 6, $row[2], "" ,0, "L");
        
        $ticket->SetX(43);
        $ticket->Cell(30, 6, "$ " . number_format($row[1], 2), "" ,0, "R");

        if($i==$num-1){
        //capturamos el último pago
            $pago_final=number_format($row[1]);
        }
//fin de cambio
    }//fin de for $i


//
    $ticket->SetXY(7, $ticket->GetY()+6);
    $ticket->Cell(66, 1, "", "T" ,0, "C");
    
    $ticket->SetXY(40, $ticket->GetY()+1);
    $ticket->Cell(30, 6, utf8_decode("Pagado"), "" ,0, "L");
        
    $ticket->SetXY(43, $ticket->GetY());
    $ticket->Cell(30, 6, "$ " . number_format($pagado, 2), "" ,0, "R");
    
    $ticket->SetXY(7, $ticket->GetY()+6);
    $ticket->Cell(30, 6, utf8_decode("Resta"), "" ,0, "L");
    
    $ticket->SetXY(20, $ticket->GetY());
    $ticket->Cell(30, 6, "$ " . number_format($resta, 2), "" ,0, "R");
        
    $ticket->SetXY(7, $ticket->GetY()+7);
    $ticket->Cell(66, 6, utf8_decode("Fecha de pago: $fecha"), "" ,0, "C");
    
    $ticket->SetXY(7, $ticket->GetY()+4);
    $ticket->Cell(66, 6, utf8_decode("Hora de pago: $hora"), "" ,0, "C");
//monto del pago
    $ticket->SetFont('Arial','B',$bF+3);
    $ticket->SetXY(0, $ticket->GetY()+4);
    $ticket->Cell(70, 6, utf8_decode("Monto de pago: $".$pago_final), "" ,0, "L");//$monto se cambia por Oscar 09.08.2018
    
    $ticket->SetFont('Arial','',$bF-2);
    $ticket->SetXY(7, $ticket->GetY()+4);
    $ticket->Cell(66, 6, utf8_decode("Forma de pago: $tipoPago"), "" ,0, "C");
    $ar = fopen("../../leyenda_ticket/leyenda.txt","r") or die ('No se pudo abrir el archivo');

    while(!feof($ar))
    {
        $linea=fgets($ar);
    }
    fclose($ar);
    $acotado = substr($linea,0,165);
    $ticket->SetXY(10, $ticket->GetY()+4);
    $ticket-> MultiCell(60,5, utf8_decode($acotado), 0 ,'J', false);

/*implementacion Oscar 28.05.2019 para meter el código de barras*/
    if(file_exists("../../img/codigos_barra/".$folio_nv.".png")){
        $ticket->SetXY(5, $ticket->GetY());
        $ticket->Image("../../img/codigos_barra/".$folio_nv.".png", 15, $ticket->GetY()+5,46);
    }
/*Fin de cambio Oscar 28.05.2019*/
    
    #$ticket->Output();
    /*
    if($printPan == 1) {
       $ticket->Output();
    }else{
/*Implementación Oscar 28.02.2019 para imprimir # de tickets de abono de acuerdo a la configuración Adicional de la sucursal*
        $sql="SELECT no_tickets_abono FROM ec_configuracion_sucursal WHERE id_sucursal=$user_sucursal";
        $eje=mysql_query($sql)or die("Error al consultar el número de Tickets de Abono!!!\n\n".mysql_error()."\n\n".$sql);
        $num=mysql_fetch_row($eje);*/
        $numtkt=2;
        for($j=1;$j<=$numtkt;$j++){
    /*implementación Oscar 25.01.2019 para la sincronización de tickets*/
            $nombre_ticket="ticket_".$user_sucursal."_" . date("YmdHis") . "_abono_" . $folio_abono . "_".$j.".pdf";
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
        }
/*Fin de cambio Oscar 28.02.2019*/
    /*fin de cambio Oscar 25.01.2019*/    
        header ("location: ../index.php?scr=home");
    //}//fin de else    
  
    exit (0);

?>