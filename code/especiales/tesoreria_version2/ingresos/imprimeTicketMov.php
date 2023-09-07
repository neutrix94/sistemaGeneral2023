<?php
    define('FPDF_FONTPATH','../../../../include/fpdf153/font/'); 
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
    //echo 'here';
/*Fin de cambio Oscar 25.01.2018*/
    extract($_GET);

    $lineas_productos = 0;
    
    if(!isset($noImp)){
        $noImp=1;
    }
    
    $cs = "SELECT nombre AS sucursal, prefijo FROM sys_sucursales WHERE id_sucursal = '{$user_sucursal}' ";
    if ($rs = mysql_query($cs)) {
        if ($dr = mysql_fetch_assoc($rs)) {
            $sucursal = $dr["sucursal"];
            $prefijo = $dr["prefijo"];
        } mysql_free_result($rs);
    }
    $cs = "SELECT folio AS folio_mov FROM ec_movimiento_banco WHERE id_movimiento_banco = '$id_nvo_mov' ";
    if ($rs = mysql_query($cs)) {
        if ($dr = mysql_fetch_assoc($rs)) {
            $folio_mov = $dr["folio_mov"];
        } mysql_free_result($rs);
    }
    if($es_traspaso==1){
        $titulo="TRASPASO ENTRE CAJAS";
    }else{
        $titulo="MOVIMIENTO DE CAJA";
    }

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
    
    
    $ticket = new TicketPDF("P", "mm", array(80,60+$num*6), "{$sucursal}", "{$folio_mov}", 32);
    $ticket->AliasNbPages();
    $ticket->AddPage();

	$bF=10;//tamaño de fuente 

    $ticket->Image("../../../../touch/img/logo-casa-fondo-blanco.png", 5, 5, 18);
    //$ticket->Image("../../../../img/especiales/pagado.jpg", 10, 40, 50);
    
    $ticket->SetFont('Arial','B',$bF+4);
    $ticket->SetXY(14, 9);
    $ticket->Cell(66, 6, utf8_decode($titulo), "" ,0, "C");
    
    $ticket->SetFont('Arial','',$bF+1);
    $ticket->SetXY(14, $ticket->GetY()+8);
    $ticket->Cell(66, 6, utf8_decode("SUCURSAL: {$ticket->sucursal}"), "" ,0, "C");
    
    $ticket->SetXY(22, $ticket->GetY()+6);
    $ticket->Cell(66, 6, utf8_decode("FOLIO: $folio_mov"), "" ,0, "L");
    
    $ticket->SetXY(22, $ticket->GetY()+6);
    $ticket->Cell(66, 6, utf8_decode("MONTO: $".number_format($monto)), "" ,0, "L");


    $ticket->SetFont('Arial','',$bF);    
    $ticket->SetXY(7, $ticket->GetY()+6);
    $ticket->Cell(60, 6, utf8_decode("CAJA"), "B" ,0, "L");
    
    $ticket->SetX(35);
    $ticket->Cell(20, 6, utf8_decode("CONCEPTO"), "" ,0, "L");
    
    $ticket->SetX(43);
    $ticket->Cell(30, 6, utf8_decode("MONTO"), "B" ,0, "R");
    
    $ticket->SetXY(20, $ticket->GetY()+1);

    $ticket->SetFont('Arial','',$bF-2);

//datos del movimiento
    $eje=mysql_query("SELECT 
                        mb.folio,/*0*/
                        mb.monto,/*1*/
                        conc.nombre,/*2*/
                        cc.nombre,/*3*/
                        CONCAT('USUARIO: ',u.nombre,' ',u.apellido_paterno),/*4*/
                        mb.fecha,/*5*/
                        mb.observaciones/*6*/
                    FROM ec_movimiento_banco mb
                    LEFT JOIN ec_concepto_movimiento conc ON conc.id_concepto_movimiento=mb.id_concepto
                    LEFT JOIN ec_caja_o_cuenta cc ON cc.id_caja_cuenta=mb.id_caja
                    LEFT JOIN sys_users u ON u.id_usuario=mb.id_usuario
                    WHERE IF('$es_traspaso'=1,mb.id_traspaso_banco='$id_nvo_mov',mb.id_movimiento_banco='$id_nvo_mov')"
                    )or die("Error al consultar los datos del movimiento de caja!!!".
                    mysql_error());

    /*$r=mysql_fetch_row($eje);*/
while($r=mysql_fetch_row($eje)){
    $folio_mov=$r[0];
    $monto=$r[1];
    $concepto=$r[2];
    $nombre_caja=$r[3];
    $nombre_usuario=$r[4];
    $fecha_movimiento=$r[5];
    $observaciones_movimiento=$r[6];

//detalle del movimiento
    $ticket->SetXY(7, $ticket->GetY()+6);
    $ticket->Cell(66, 6, utf8_decode($nombre_caja), "" ,0, "L");

    $ticket->SetX(35);
    $ticket->Cell(20, 6, $concepto,"" ,0, "C");

    $ticket->SetX(43);
    $ticket->Cell(30, 6, "$ " . number_format($monto, 2), "" ,0, "R");
}

//datos del movimiento
    $ticket->SetFont('Arial','',$bF);
    $ticket->SetXY(7, $ticket->GetY()+7);
    $ticket->Cell(66, 6, utf8_decode("Fecha del movimiento: ".$fecha_movimiento), "" ,0, "C");
    
//datos del usuario
    $ticket->SetFont('Arial','B',$bF);
    $ticket->SetXY(43, $ticket->GetY()+4);
    $ticket->Cell(25, 6, utf8_decode($nombre_usuario), "" ,0, "R");
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
            $nombre_ticket="ticket_".$user_sucursal."_" . date("YmdHis") . "_movimiento_" . $folio_mov . "_".$j.".pdf";
            if($user_tipo_sistema=='linea'){
                $sql_arch="INSERT INTO sys_archivos_descarga SET 
                        id_archivo=null,
                        tipo_archivo='pdf',
                        nombre_archivo='$nombre_ticket',
                        ruta_origen='$ruta_or',
                        ruta_destino='$ruta_des',
                    /*Modificación Oscar 03.03.2019 para tomar el destino local de impresión de ticket configurado en la sucursal*/
                        id_sucursal=(SELECT sucursal_impresion_local FROM ec_configuracion_sucursal WHERE id_sucursal='1'),
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
    
//    exit (0);

?>