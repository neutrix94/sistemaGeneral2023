<?php
    define('FPDF_FONTPATH','../../include/fpdf153/font/');
    
    include("../../include/fpdf153/fpdf.php");
   // die('.....'.$user_sucursal);
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

    $ticket->SetFont('Arial','B',$bF+4);
    $ticket->SetXY(16, 15);
    $ticket->Cell(66, 6, utf8_decode("CASA DE LAS LUCES"), "" ,0, "C");

    $ticket->SetXY(16, $ticket->GetY()+5);
    $ticket->Cell(66, 6, utf8_decode(date("Y")), "" ,0, "C");

$ticket->Image("../../touch/img/logo-casa-fondo-blanco.png", 5, 5, 18);
  //  $ticket->Image("../../img/especiales/pagado.jpg", 10, 40, 50);

//nombre del usuario    
    $ticket->SetFont('Arial','',$bF+1);
    $ticket->SetXY(5, $ticket->GetY()+12);
    $ticket->Cell(70, 6, utf8_decode("NOMBRE:"), "" ,0, "C");    

    $ticket->SetFont('Arial','B',$bF+1);
    $ticket->SetXY(5, $ticket->GetY()+5);
    $ticket->Cell(70, 6, utf8_decode($r[1]), "" ,0, "C");

//nombre de la sucursal a la que pertenece el usuario
    $ticket->SetFont('Arial','',$bF+1);
    $ticket->SetXY(5, $ticket->GetY()+8);
    $ticket->Cell(70, 6, utf8_decode("SUCURSAL:"), "" ,0, "C");

    $ticket->SetFont('Arial','B',$bF+1);
    $ticket->SetXY(5, $ticket->GetY()+5);
    $ticket->Cell(70, 6, utf8_decode($r[2]), "" ,0, "C");

//puesto del usuario
    $ticket->SetFont('Arial','',$bF+1);
    $ticket->SetXY(5, $ticket->GetY()+8);
    $ticket->Cell(70, 6, utf8_decode("PUESTO:"), "" ,0, "C");

    $ticket->SetFont('Arial','B',$bF+1);
    $ticket->SetXY(5, $ticket->GetY()+5);
    $ticket->Cell(70, 6, utf8_decode($r[3]), "" ,0, "C");

//codigo de barras
    $ticket->Image($filepath, 10, 72, 60);

    if($printPan == 1) {
       $ticket->Output();
    }else{
/*Implementación Oscar 28.02.2019 para imprimir # de tickets de abono de acuerdo a la configuración Adicional de la sucursal*
        $sql="SELECT no_tickets_abono FROM ec_configuracion_sucursal WHERE id_sucursal=$user_sucursal";
        $eje=mysql_query($sql)or die("Error al consultar el número de Tickets de Abono!!!\n\n".mysql_error()."\n\n".$sql);
        $num=mysql_fetch_row($eje);*/
        for($j=1;$j<=1;$j++){
    /*implementación Oscar 25.01.2019 para la sincronización de tickets*/
            $nombre_ticket="ticket_".$user_sucursal."_" . date("YmdHis") . "_credencial_".$j.".pdf";
            if($user_tipo_sistema=='linea'){
                $sql_arch="INSERT INTO sys_archivos_descarga SET 
                        id_archivo=null,
                        tipo_archivo='pdf',
                        nombre_archivo='$nombre_ticket',
                        ruta_origen='$ruta_or',
                        ruta_destino='$ruta_des',
                    /*Modificación Oscar 03.03.2019 para tomar el destino local de impresión de ticket configurado en la sucursal*/
                        id_sucursal=IF('$user_sucursal'='-1','-1',(SELECT sucursal_impresion_local FROM ec_configuracion_sucursal WHERE id_sucursal='$user_sucursal')),
                    /*Fin de Cambio Oscar 03.03.2019*/
                        id_usuario='$user_id',
                        observaciones=''";
                $inserta_reg_arch=mysql_query($sql_arch)or die("Error al guardar el registro de sincronización del ticket de reimpresión!!!\n\n".mysql_error()."\n\n".$sql_arch);

            }
            $ticket->Output("../../cache/ticket/".$nombre_ticket, "F");
        }
/*Fin de cambio Oscar 28.02.2019*/
    /*fin de cambio Oscar 25.01.2019*/    
//        header ("location: ../index.php?scr=home");
    }//fin de else    
    
    exit (0);

?>