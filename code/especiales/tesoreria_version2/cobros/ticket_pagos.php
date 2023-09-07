<?php
    define('FPDF_FONTPATH','../../../../include/fpdf153/font/');
    if(isset($_GET['es_venta']) && $_GET['es_venta']==1){
        include('../../../../conectMin.php');
    }
    
    include("../../../../include/fpdf153/fpdf.php");
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
    extract($_GET);

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
//nombre del cajero que cobra
    $sql="SELECT CONCAT('CAJERO: ',nombre,' ',apellido_paterno,' ',apellido_materno) FROM sys_users WHERE id_usuario=$user_id";
    $eje=mysql_query($sql)or die("Error al consultar los datos del cajero!!!\n".mysql_error());
    $r=mysql_fetch_row($eje);
    $datos_cajero=$r[0];

//datos de la nota de venta
    $eje=mysql_query("SELECT folio_nv,current_date(),current_time(),total FROM ec_pedidos WHERE id_pedido=$id_pedido")or die("Error al consultar el folio del pedido");
    $r=mysql_fetch_row($eje);
    $folio_nv=$r[0];
    $fecha=$r[1];
    $hora=$r[2];
    $total=$r[3];
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
    GROUP BY pp.id_pedido,DATE_FORMAT(CONCAT(pp.fecha,' ',pp.hora),'%Y-%m-%d %H:%i')
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
    
    
    $ticket = new TicketPDF("P", "mm", array(80,100+$num*6), "{$sucursal}", "{$folio}", 32);
    $ticket->AliasNbPages();
    $ticket->AddPage();

	$bF=10;//tamaño de fuente 

    $ticket->Image("../../../../touch/img/logo-casa-fondo-blanco.png", 5, 5, 18);
    $ticket->Image("../../../../img/especiales/pagado.jpg", 10, 40, 50);
    
    $ticket->SetFont('Arial','B',$bF+4);
    $ticket->SetXY(14, 9);
    $ticket->Cell(66, 6, utf8_decode("CASA DE LAS LUCES"), "" ,0, "C");
    
    $ticket->SetFont('Arial','',$bF+1);
    $ticket->SetXY(14, $ticket->GetY()+8);
    $ticket->Cell(66, 6, utf8_decode("Sucursal: {$ticket->sucursal}"), "" ,0, "C");
    
    $ticket->SetXY(22, $ticket->GetY()+6);
    $ticket->Cell(66, 6, utf8_decode("NOTA DE VENTA: $folio_nv"), "" ,0, "L");
    
    $ticket->SetXY(22, $ticket->GetY()+6);
    $ticket->Cell(66, 6, utf8_decode("TOTAL DE COMPRA: $".number_format($total)), "" ,0, "L");
    
    /*
    $ticket->Image("../../../../touch/img/logo-casa-fondo-blanco.png", 28, 5, 22);
    
    $ticket->SetFont('Arial','B',$bF+4);
    $ticket->SetXY(7, 40);
    $ticket->Cell(66, 6, utf8_decode("CASA DE LAS LUCES"), "" ,0, "C");
    
    $ticket->SetFont('Arial','',$bF+1);
    $ticket->SetXY(7, $ticket->GetY()+4);
    $ticket->Cell(66, 6, utf8_decode("Sucursal {$ticket->sucursal}"), "" ,0, "C");
    
    $ticket->SetXY(7, $ticket->GetY()+4);
    $ticket->Cell(66, 6, utf8_decode("NOTA DE VENTA: $folio_nv"), "" ,0, "L");
    
    $ticket->SetXY(7, $ticket->GetY()+4);
    $ticket->Cell(66, 6, utf8_decode("TOTAL DE COMPRA: $".number_format($total)), "" ,0, "L");
    
    */
    $ticket->SetFont('Arial','',$bF);
    
    $ticket->SetXY(7, $ticket->GetY()+6);
    $ticket->Cell(60, 6, utf8_decode("TIPO PAGO"), "B" ,0, "L");
    
    $ticket->SetX(35);
    $ticket->Cell(20, 6, utf8_decode(""), "" ,0, "L");
    
    $ticket->SetX(43);
    $ticket->Cell(30, 6, utf8_decode("MONTO"), "B" ,0, "R");
    
    $ticket->SetXY(20, $ticket->GetY()+1);
    
    $ticket->SetFont('Arial','',$bF-2);

//enlistamos los cobros con tarjetas
    $arr_tarjetas=explode("°",$tarjetas);
    for($i=0;$i<sizeof($arr_tarjetas)-1;$i++){

        $arr=explode("~",$arr_tarjetas[$i]);
        if($arr[1]>0){
            $ticket->SetXY(7, $ticket->GetY()+6);
            $ticket->Cell(66, 6, utf8_decode('Tarjeta'), "" ,0, "L");
        
            $ticket->SetX(35);
            $ticket->Cell(20, 6, $row[2], "" ,0, "L");
        
            $ticket->SetX(43);
            $ticket->Cell(30, 6, "$ " . number_format($arr[1], 2), "" ,0, "R");
        }
    }//fin de for i

//enlistamos los cobros con cheque/transferencia
    $arr_cheques=explode("°",$cheques);
    for($i=0;$i<sizeof($arr_cheques)-1;$i++){
        $arr=explode("~",$arr_cheques[$i]);
        $ticket->SetXY(7, $ticket->GetY()+6);
        $ticket->Cell(66, 6, utf8_decode($arr[2]), "" ,0, "L");
        
        $ticket->SetX(35);
        $ticket->Cell(20, 6, $row[2], "" ,0, "L");
        
        $ticket->SetX(43);
        $ticket->Cell(30, 6, "$ " . number_format($arr[1], 2), "" ,0, "R");
    }//fin de for i
//pago en efectivo
    if($monto_efectivo!='' && $monto_efectivo!=0 ){
        $ticket->SetXY(7, $ticket->GetY()+6);
        $ticket->Cell(66, 6, utf8_decode("Efectivo"), "" ,0, "L");
        
        $ticket->SetX(35);
        $ticket->Cell(20, 6, $row[2], "" ,0, "L");
        
        $ticket->SetX(43);
        $ticket->Cell(30, 6, "$ " . number_format($monto_efectivo, 2), "" ,0, "R");
    
    }

    $ticket->SetFont('Arial','B',$bF+5);
    $ticket->SetXY(7, $ticket->GetY()+6);
    $ticket->Cell(66, 1, "", "T" ,0, "C");
    $ticket->SetXY(43, $ticket->GetY());
    $ticket->Cell(30, 6, "$ " . number_format($monto_total_pagos, 2), "" ,0, "R");

//recibido y cambio en caso de haber sido capturado este valor
    if($recibido!=0){

        $ticket->SetFont('Arial','',$bF);
        
        $ticket->SetXY(40, $ticket->GetY()+6);
        $ticket->Cell(30, 6, utf8_decode("Recibido"), "" ,0, "L");
        
        $ticket->SetXY(43, $ticket->GetY());
        $ticket->Cell(30, 6, "$ " . number_format($recibido, 2), "" ,0, "R");
    
    //
        $ticket->SetFont('Arial','b',$bF+2);
        
        $ticket->SetXY(40, $ticket->GetY()+4);
        $ticket->Cell(30, 6, utf8_decode("Cambio"), "" ,0, "L");
        
        $ticket->SetXY(45, $ticket->GetY());
        $ticket->Cell(30, 6, "$ " . number_format($cambio, 2), "" ,0, "R");
    }


    $ticket->SetFont('Arial','',$bF);

    $ticket->SetXY(7, $ticket->GetY()+7);
    $ticket->Cell(66, 6, utf8_decode("Fecha de pago: $fecha"), "" ,0, "C");
    
    $ticket->SetXY(7, $ticket->GetY()+4);
    $ticket->Cell(66, 6, utf8_decode("Hora de pago: $hora"), "" ,0, "C");
    
    $ticket->SetFont('Arial','',$bF-2);
    $ticket->SetXY(7, $ticket->GetY()+4);

    $ar = fopen("../../../../leyenda_ticket/leyenda.txt","r") or die ('No se pudo abrir el archivo');

    while(!feof($ar))
    {
        $linea=fgets($ar);
    }
    fclose($ar);
    $acotado = substr($linea,0,165);
    $ticket->SetXY(10, $ticket->GetY()+4);
    $ticket-> MultiCell(60,5, utf8_decode($acotado), 0 ,'J', false);
    
//datos del cajero
    $ticket->SetFont('Arial','B',$bF);
    $ticket->SetXY(43, $ticket->GetY()+2);
    $ticket->Cell(25, 6, utf8_decode($datos_cajero), "" ,0, "R");
    #$ticket->Output();
    
    if($printPan == 1) {
       $ticket->Output();
    }else{
/*Implementación Oscar 28.02.2019 para imprimir # de tickets de abono de acuerdo a la configuración Adicional de la sucursal*
        $sql="SELECT no_tickets_abono FROM ec_configuracion_sucursal WHERE id_sucursal=$user_sucursal";
        $eje=mysql_query($sql)or die("Error al consultar el número de Tickets de Abono!!!\n\n".mysql_error()."\n\n".$sql);
        $num=mysql_fetch_row($eje);*/
        for($j=1;$j<=1;$j++){
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
            $ticket->Output("../../../../cache/ticket/".$nombre_ticket, "F");
        }
/*Fin de cambio Oscar 28.02.2019*/
    /*fin de cambio Oscar 25.01.2019*/    
//        header ("location: ../index.php?scr=home");
    }//fin de else    
    
    exit (0);

?>