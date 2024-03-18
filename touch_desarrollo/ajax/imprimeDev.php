<?php
    mysql_set_charset("utf8");
  
    if (function_exists("mb_internal_encoding")) mb_internal_encoding ('utf-8');
    
    define('FPDF_FONTPATH','../../include/fpdf153/font/');
    
    include("../../include/fpdf153/fpdf.php");
    $devoluciones = array();
  
/*implementación Oscar 25.01.2019 para sacar rutas de tickets*/
  $archivo_path = "../../conexion_inicial.txt";
  $carpeta_path = "";
  if(file_exists($archivo_path)){
    $file = fopen($archivo_path,"r");
    $line=fgets($file);
    fclose($file);
    $config=explode("<>",$line);
    $tmp=explode("~",$config[2]);
    $ruta_or=$tmp[0];
    $ruta_des=$tmp[1];
    $tmp_=explode("~",$config[0]);
    $carpeta_path = base64_decode( $tmp_[1] );
  }else{
    die("No hay archivo de configuración!!!");
  }
/*Fin de cambio Oscar 25.01.2018*/
    $totReal=0;
    
    extract($_GET);
  if(isset($id_pedido_original)){
      include("../../conectMin.php");
  }
/*implementación Oscar 2021 para validar contraseña al devolver efectivo*/
  if ( isset( $password_encargado ) ){
    $sql="SELECT 
            COUNT(u.id_usuario) 
          FROM sys_users u 
          LEFT JOIN sys_sucursales suc 
          ON u.id_usuario = suc.id_encargado
          WHERE suc.id_sucursal = {$user_sucursal} 
          AND u.contrasena = md5( '{$password_encargado}' )";
             // die($sql);
    $eje=mysql_query($sql)or die("Error al consultar verificación de usuario!!!\n\n".$sql."\n\n".mysql_error());
    $r=mysql_fetch_row($eje);
    if($r[0]==0){
    //si no se encuentran coincidencias
      die('no');
    }
  }
/*Fin de cambio Oscar 2021*/

/**/
/*implementación Oscar 25.06.2019 para guardar el id de las devoluciones en la cabecera del pedido y tener referencia en la pantalla de cobros*/
  if(isset($ids_devoluciones)){
    $sql="UPDATE ec_pedidos SET id_devoluciones='$ids_devoluciones' WHERE id_pedido=$id_pedido_original";
    $eje=mysql_query($sql)or die("Error al actualizar el id de devoluciones en el pedido!!!\n".mysql_error());
  }
/*Fin de cambio Oscar 25.06.2019*/
    
    if(!isset($_GET["noImp"]))
        $_GET["noImp"]=1;
    
    //$id_dev = $_GET["id_dev"];  
    $sucursal = "";
    $folio = "";
    $prefijo = "";
    $subtotal = "0";
    $total = "0";
    $productos = array();
    $pagos = array();
    $vendedor = "N/A";
    $lineas_productos = 0;
    $lineas_pagos = 0;
    $total_pagos = "0";
    $tipofolio = "PEDIDO";
    if(isset($monto_devolucion)){
      $tipofolio=" DEVOLVER EFECTIVO";
    }
    
    $cs = "SELECT CONCAT(nombre, ' ', apellido_paterno) AS vendedor FROM sys_users WHERE id_usuario = '{$user_id}' ";
    if ($rs = mysql_query($cs)) {
        if ($dr = mysql_fetch_assoc($rs)) {
            $vendedor = $dr["vendedor"];
        } mysql_free_result($rs);
    }
//
    $cs = "SELECT nombre AS sucursal, prefijo FROM sys_sucursales WHERE id_sucursal = '{$user_sucursal}' ";
    if ($rs = mysql_query($cs)){
        if ($dr = mysql_fetch_assoc($rs)){
            $sucursal = $dr["sucursal"];
            $prefijo = $dr["prefijo"];
        }mysql_free_result($rs);
    }
    //si fue devolución de productos internos
        if($id_dev_interna!=-1){
            $cs_complemento="=".$id_dev_interna;
        }
    //si fue devolución de productos externos
       if($id_dev_externa!=-1){
            $cs_complemento="=".$id_dev_externa;
       } 
//monto
  if(!isset($monto_devolucion)){     
    $monto_query="(SELECT If(SUM(monto) IS NULL, 0, SUM(monto)) FROM ec_devolucion_pagos WHERE id_devolucion=ec_devolucion.id_devolucion) AS total,";
    
    //si se realizó devolución de productos interno y externos
       if($id_dev_interna!=-1&&$id_dev_externa!=-1){
            $cs_complemento=" IN(".$id_dev_interna.",".$id_dev_externa.")";
        //cambiamos la consulta del monto
            $monto_query="(SELECT If(SUM(monto) IS NULL, 0, SUM(monto)) FROM ec_devolucion_pagos WHERE id_devolucion IN(".$id_dev_interna.",".$id_dev_externa.")) AS total,";
       }
  //sE CONSULTAN DATOS DE LA CABECERA DE DEVOLUCIÓN
    $cs = "SELECT
           IF(ISNULL(folio),
           folio, folio) AS folio,
           IF(ISNULL(folio), 'DEVOLUCION', 'DEVOLUCION') AS tipofolio,
           (SELECT If(SUM(monto) IS NULL, 0, SUM(monto)) FROM ec_devolucion_pagos WHERE id_devolucion=ec_devolucion.id_devolucion) AS subtotal,
           0 AS iva,
           0 AS ieps,
           $monto_query
           0 AS descuento,
           1 AS pagado,
           '' as folioA
           FROM ec_devolucion
           WHERE id_devolucion";
        
        //concatenamos la condición a la consulta
            $cs.=$cs_complemento;
   //         die("dev_int=".$id_dev_interna."|dev_ext=".$id_dev_externa."|".$cs);
           
      //die($cs);           
    if ($rs = mysql_query($cs)){
        if ($dr = mysql_fetch_assoc($rs)){
            /*print_r($dr);
            die();*/    
            
            $tipofolio = $dr["tipofolio"];
            $folio = $dr["folio"];
            $total = $dr["total"];
            $subtotal = $dr["subtotal"];
            $total = $dr["total"];
            $pagado = $dr["pagado"];
            $descuento = $dr["descuento"];
            $folioA = "A$prefijo".$dr["folioA"];
        } mysql_free_result($rs);
    }
/*************Buscamos los productos***************/
    $cs = "SELECT 
            aux.id_producto,
            aux.producto,
            aux.cantidad,
            aux.precio,
            aux.monto,
            aux.descuento,
            aux.porc_desc,
            IF(s.mostrar_ubicacion=1 Or s.mostrar_alfanumericos=1,1,0) as infoAdicional,
            CONCAT(
              IF(s.mostrar_ubicacion=1 AND sp.ubicacion_almacen_sucursal!='',CONCAT('Ubicación: ',sp.ubicacion_almacen_sucursal,' | '),''),
                IF(s.mostrar_alfanumericos=1,CONCAT('Clave: ',aux.clave),'')
            ) as info
          FROM(
            SELECT
              P.id_productos AS id_producto,
              P.nombre AS producto,
              P.clave,
              IF(prd.id_producto IS NULL, dd.cantidad , ROUND( dd.cantidad / prd.cantidad ) ) AS cantidad,
              dp.precio AS precio,
              ( IF(prd.id_producto IS NULL, dd.cantidad , ROUND( dd.cantidad / prd.cantidad ) )*dp.precio)
                -((IF(prd.id_producto IS NULL, dd.cantidad , ROUND( dd.cantidad / prd.cantidad ) )*dp.precio)
                *
                (IF(pe.descuento=0,0,(pe.descuento*100/pe.subtotal))/100)) AS monto,
              IF(dp.descuento=0,0,dp.descuento/dp.cantidad) AS descuento,
              (IF(pe.descuento=0,0,(pe.descuento*100/pe.subtotal)/100)*(dd.cantidad*dp.precio)) AS porc_desc
            FROM ec_devolucion_detalle dd 
            INNER JOIN ec_devolucion d  
            ON dd.id_devolucion = d.id_devolucion
            INNER JOIN ec_pedidos_detalle dp  
            ON dp.id_pedido_detalle = dd.id_pedido_detalle
            LEFT JOIN ec_pedidos pe ON pe.id_pedido=dp.id_pedido
            INNER JOIN  ec_productos P
            ON dp.id_producto = P.id_productos
            LEFT JOIN ec_productos_detalle prd
            ON prd.id_producto = dp.id_producto
            WHERE dd.id_devolucion";
        //si fue devolución de productos internos
           if($id_dev_interna!=-1){
                $cs_complemento="=".$id_dev_interna;
           }
        //si fue devolución de productos externos
           if($id_dev_externa!=-1){
                $cs_complemento="=".$id_dev_externa;
           }
        //si se realizó devolución de productos interno y externos
           if($id_dev_interna!=-1&&$id_dev_externa!=-1){
                $cs_complemento=" IN(".$id_dev_interna.",".$id_dev_externa.")";
           }
        
        //concatenamos la condición a la consulta
            $cs.=$cs_complemento;
//            die("dev_int=".$id_dev_interna."|dev_ext=".$id_dev_externa."|".$cs);
        $cs.=")aux
            LEFT JOIN sys_sucursales_producto sp ON aux.id_producto=sp.id_producto
            JOIN sys_sucursales s ON sp.id_sucursal=s.id_sucursal AND sp.id_sucursal=".$user_sucursal;

    //extraemos el folio del pedido original
        $qry_fol_pd=mysql_query("SELECT p.folio_nv,p.pagado FROM ec_pedidos p
        LEFT JOIN ec_devolucion dev ON p.id_pedido=dev.id_pedido WHERE dev.id_devolucion $cs_complemento LIMIT 1")or die("Error al consultar folio de pedido!!!\n\n".mysql_error());
        $row_fol=mysql_fetch_row($qry_fol_pd);
        $folio_pedido_original=$row_fol[0];
        $pedido_pagado=$row_fol[1];

//die($cs);
  
    if ($rs = mysql_query($cs)){
        while ($dr = mysql_fetch_assoc($rs))
        {
            // Concatenar precio unitario en la descripcion
            $dr["producto"] .= " \${$dr["precio"]}";
        //concatenación de Descuento
            if($dr["porc_desc"]>0 && $dr["descuento"]<=0){
               $dr["producto"].=" Descuento: -\$".round($dr["porc_desc"],2);
            }
            if($dr["descuento"]>0){
                $dr["monto"]=($dr["precio"]-$dr["descuento"])*$dr["cantidad"];
                $dr["producto"].=" Descuento: -\$".round($dr["descuento"]*$dr["cantidad"],2);
                //echo "(".$dr["precio"]."-".$dr["descuento"].")"."*".$dr["cantidad"].'='.$dr["monto"]."\n\n";
            }
            $lineas_productos += ceil(strlen($dr["producto"])/32.0);

            array_push($productos, $dr);
    /*implementación Oscar 10.10.2018 para imprimir ubicación y calve_proveedor en ticket*/
          if($dr['info']==1){
            $lineas_productos++;
          }
    /*Fin de cambio 10.10.2018*/

        }
        mysql_free_result($rs);
    }
  }//fin de si no existe variable de devolucion
/*implementacion Oscar 25.06.2019 para generar folio del ticket original devolucion*/
  else{
  //consulta si tiene devolucion pendientes de pasar por la pantalla de cobros
    $sql = "SELECT
      d.folio AS folio_devolucion,
      p.folio_nv AS folio_venta_original,
      CONCAT( u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno ) AS vendedor
    FROM ec_devolucion d
    LEFT JOIN ec_devolucion_detalle dd
    ON dd.id_devolucion = d.id_devolucion
    LEFT JOIN ec_pedidos p 
    ON p.id_pedido = d.id_pedido
    LEFT JOIN sys_users u 
    ON u.id_usuario = p.id_usuario
    WHERE d.id_pedido = '{$_GET['id_pedido_original']}'
    AND d.id_cajero = 0
    AND d.id_sesion_caja = 0
    GROUP BY d.id_pedido";
    $stm = mysql_query( $sql ) or die( "Error al consultar si hay devolucion pendiente : " . mysql_error() );
    
    if( mysql_num_rows( $stm ) > 0 ){
      $id_pedido_devolucion = $_GET['id_pedido_original'];
      $row = mysql_fetch_assoc( $stm );
      $devolucion = $row;
    //consulta el detalle
      $dev_detail = "SELECT 
        aux.id_producto,
        aux.producto,
        aux.cantidad,
        aux.precio,
        aux.monto,
        aux.descuento,
        aux.porc_desc,
        IF(s.mostrar_ubicacion=1 Or s.mostrar_alfanumericos=1,1,0) as infoAdicional,
        CONCAT(
        IF(s.mostrar_ubicacion=1 AND sp.ubicacion_almacen_sucursal!='',CONCAT('Ubicación: ',sp.ubicacion_almacen_sucursal,' | '),''),
          IF(s.mostrar_alfanumericos=1,CONCAT('Clave: ',aux.clave),'')
        ) as info
      FROM(
        SELECT
        P.id_productos AS id_producto,
        P.nombre AS producto,
        P.clave,
        IF(prd.id_producto IS NULL, dd.cantidad , ROUND( dd.cantidad / prd.cantidad ) ) AS cantidad,
        dp.precio AS precio,
        ( IF(prd.id_producto IS NULL, dd.cantidad , ROUND( dd.cantidad / prd.cantidad ) )*dp.precio)
          -((IF(prd.id_producto IS NULL, dd.cantidad , ROUND( dd.cantidad / prd.cantidad ) )*dp.precio)
          *
          (IF(pe.descuento=0,0,(pe.descuento*100/pe.subtotal))/100)) AS monto,
        IF(dp.descuento=0,0,dp.descuento/dp.cantidad) AS descuento,
        (IF(pe.descuento=0,0,(pe.descuento*100/pe.subtotal)/100)*(dd.cantidad*dp.precio)) AS porc_desc
        FROM ec_devolucion_detalle dd 
        INNER JOIN ec_devolucion d  
        ON dd.id_devolucion = d.id_devolucion
        INNER JOIN ec_pedidos_detalle dp  
        ON dp.id_pedido_detalle = dd.id_pedido_detalle
        LEFT JOIN ec_pedidos pe ON pe.id_pedido=dp.id_pedido
        INNER JOIN  ec_productos P
        ON dp.id_producto = P.id_productos
        LEFT JOIN ec_productos_detalle prd
        ON prd.id_producto = dp.id_producto
        WHERE d.id_pedido IN( {$id_pedido_devolucion} )
        AND d.id_sesion_caja = 0
        AND d.id_cajero = 0
      )aux
      LEFT JOIN sys_sucursales_producto sp ON aux.id_producto=sp.id_producto
      JOIN sys_sucursales s ON sp.id_sucursal=s.id_sucursal AND sp.id_sucursal={$user_sucursal}";
      $dev_stm = mysql_query( $dev_detail ) or die( "Error al consultar el detalle de los prouctos devueltos : " . mysql_error() );
      if( mysql_num_rows( $dev_stm ) > 0 ){
        $devolucion['detail'] = array();
        while( $dev_row = mysql_fetch_assoc( $dev_stm ) ){
          array_push( $devolucion['detail'], $dev_row );
        }
        //$dev_row['detail']
      }
		//consulta pagos
			$sql = "SELECT
            SUM( IF( pp.id_pedido_pago IS NULL, 0, pp.monto ) ) AS total_pedido_pagos,
            SUM( IF( dp.id_devolucion_pago IS NULL, 0, dp.monto ) ) AS total_devolucion_pagos
          FROM ec_pedidos p
          LEFT JOIN ec_pedido_pagos pp
          ON pp.id_pedido = p.id_pedido
          LEFT JOIN ec_devolucion d
          ON d.id_pedido = p.id_pedido
          LEFT JOIN ec_devolucion_pagos dp
          ON dp.id_devolucion = d.id_devolucion
          WHERE p.id_pedido = {$id_pedido_devolucion}
          GROUP BY p.id_pedido";
      $payment_stm = mysql_query( $sql ) or die( "Error al consultar monto pagado : " . mysql_error() );
      $payment_row = mysql_fetch_assoc( $payment_stm );
      $devolucion['pagos_realizados'] = $payment_row['total_pedido_pagos'] - $payment_row['total_devolucion_pagos'];
    }
  //extraemos el folio del pedido original
    $qry_fol_pd=mysql_query("SELECT p.folio_nv,p.pagado FROM ec_pedidos p WHERE p.id_pedido IN($id_pedido_original) LIMIT 1")or die("Error al consultar folio de pedido!!!\n\n".mysql_error());
    $row_fol=mysql_fetch_row($qry_fol_pd);
    $folio_pedido_original=$row_fol[0];
    $pedido_pagado=$row_fol[1];
  //   die($folio_pedido_original);
/*Fin de cambio Oscar 25.06.2019*/
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
            $this->SetMargins(7, 0, 7);
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

	$bF=10;//TAMAÑO DE FUENTE
    
    $ticket = new TicketPDF("P", "mm", array(80,/*129*/30+89+30+$lineas_productos*6+($total!=$subtotal?12:0)), "{$sucursal}", "{$folio}", 32);
    $ticket->AliasNbPages();
    $ticket->AddPage();
    
    //$ticket->Image("../img/logo-casa-fondo-blanco.png", 28, 5, 22);
	
	$ticket->SetFont('Arial','B',$bF+10);
	$ticket->SetXY(20, /*40*/5);
  $ticket->Cell(66*0.6, 6, utf8_decode("{$tipofolio}"), "" ,0, "C");
	//$ticket->Cell(66, 6, utf8_decode("CASA DE LAS LUCES"), "" ,0, "C");
	if(!isset($monto_devolucion)){
	 $ticket->SetFont('Arial','',$bF+1);
	 $ticket->SetXY(0, $ticket->GetY()+6);
	 $ticket->Cell(66, 6, utf8_decode("Folio devolución: "), "" ,0, "C");
	
	 $ticket->SetFont('Arial','',$bF+2);
	 $ticket->SetXY(/*7+66*0.35*/0,$ticket->GetY()+5);
	 $ticket->Cell(66*0.8, 6, utf8_decode("{$ticket->pedido}"), "" ,0, "C");

/*Implementación del folio de venta Oscar 31.08.2018*/
    $ticket->SetFont('Arial','',$bF+1);
    $ticket->SetXY(0, $ticket->GetY()+6);
    $ticket->Cell(66, 6, utf8_decode("Folio de pedido: "), "" ,0, "C");
  
    $ticket->SetFont('Arial','',$bF+2);
    $ticket->SetXY(/*7+66*0.35*/0,$ticket->GetY()+5);
    $ticket->Cell(66*0.8, 6, utf8_decode($folio_pedido_original), "" ,0, "C");

    $ticket->SetXY(5, $ticket->GetY()+5);
    $ticket->Cell(66, 6, utf8_decode("TOTAL PAGADO : {$devolucion['pagos_realizados']}"), "" ,0, "C");
    $ticket->SetFont('Arial','B',$bF+15);
    $ticket->SetXY(5, $ticket->GetY()+5);
    $ticket->Cell(66, 6, utf8_decode("NUEVO TOTAL : {$devolucion['total_actual']}"), "" ,0, "C");
/*Fin de cambio Oscar 31.08.2018*/
  }//fin de si no existe monto de devolución
	
	$ticket->SetXY(7, $ticket->GetY()+6);
	$ticket->Cell(66, 3, "", "TB" ,0, "C");
	
	$ticket->SetFont('Arial','',$bF);
	
	$ticket->SetXY(7, $ticket->GetY()+3);
	$ticket->Cell(66, 6, utf8_decode("FECHA Y HORA DE EMISIÓN:"), "" ,0, "C");
	
	$ticket->SetXY(7, $ticket->GetY()+4.5);
	$ticket->Cell(66, 6, utf8_decode(date("d/m/Y H:i:s")), "" ,0, "C");
	
	$ticket->SetXY(7, $ticket->GetY()+4);
	$ticket->Cell(66, 6, utf8_decode("VENDEDOR:  {$vendedor}"), "" ,0, "L");

  $ticket->SetXY(7, $ticket->GetY()+4);
  $ticket->Cell(66, 6, utf8_decode("FOLIO:  {$folio_pedido_original}"), "" ,0, "C");
	
	$ticket->SetXY(7, $ticket->GetY()+5.5);
	$ticket->Cell(66, 3, "", "TB" ,0, "C");
	
	$ticket->SetFont('Arial','',$bF);
	
	$ticket->SetXY(7, $ticket->GetY()+6);
	$ticket->Cell(66*0.63, 6, utf8_decode("DESCRIPCIÓN"), "B" ,0, "L");
	
	$ticket->SetXY(7+66*0.63, $ticket->GetY());
	$ticket->Cell(66*0.12, 6, utf8_decode("CANT"), "B" ,0, "L");
	
	$ticket->SetXY(7+66*0.75, $ticket->GetY());
	$ticket->Cell(66*0.25, 6, utf8_decode("PRECIO"), "B" ,0, "R");
	
	$ticket->SetFont('Arial','',$bF-2);
	$ticket->SetXY(7, $ticket->GetY()+8);
  
  $total_de_devolucion=0;
  foreach ($productos as $producto) {
        $y = $ticket->GetY();
        
        $ticket->SetXY(7+66*0.75, $y);
        $totReal+=$producto["monto"];
        
        $ticket->MultiCell(66*0.25, 4, "$ " . number_format($producto["monto"], 2), "", "R", false);
        
        $ticket->SetXY(7+66*0.63, $y);
        $ticket->MultiCell(66*0.12, 4, $producto["cantidad"], "", "C", false);
        
        $ticket->SetXY(7, $y);
        $ticket->MultiCell(66*0.63, 4, utf8_decode("{$producto["producto"]}"), "", "L", false);
  /*implementación Oscar 10.10.2018 para imprimir ubicación y clave_proveedor en ticket*/
      if($producto['infoAdicional']==1){
        $ticket->SetFont('Arial','',$bF-3.5);
        $ticket->SetXY(5,($ticket->GetY()-1.5));
        $ticket->MultiCell(66*0.63, 4, utf8_decode("{$producto["info"]}"), "", "L", false);
      }
      $ticket->SetFont('Arial','',$bF-2);
  /*fin de cambio 10.10.2018*/
      
      $total_de_devolucion+=$producto["monto"];
  }
//itera los productos de la devolucion
  foreach ($devolucion['detail'] as $producto) {
        $y = $ticket->GetY();
        
        $ticket->SetXY(7+66*0.75, $y);
        $totReal+=$producto["monto"];
        
        $ticket->MultiCell(66*0.25, 4, "$ " . number_format($producto["monto"], 2), "", "R", false);
        
        $ticket->SetXY(7+66*0.63, $y);
        $ticket->MultiCell(66*0.12, 4, $producto["cantidad"], "", "C", false);
        
        $ticket->SetXY(7, $y);
        $ticket->MultiCell(66*0.63, 4, utf8_decode("{$producto["producto"]}"), "", "L", false);
  /*implementación Oscar 10.10.2018 para imprimir ubicación y calve_proveedor en ticket*/
      if($producto['infoAdicional']==1){
        $ticket->SetFont('Arial','',$bF-3.5);
        $ticket->SetXY(5,($ticket->GetY()-1.5));
        $ticket->MultiCell(66*0.63, 4, utf8_decode("{$producto["info"]}"), "", "L", false);
      }
      $ticket->SetFont('Arial','',$bF-2);
  /*fin de cambio 10.10.2018*/
      
      $total_de_devolucion+=$producto["monto"];
  }

    $ticket->SetY($ticket->GetY()-2);
    $ticket->SetXY(7+66*0.40, $ticket->GetY()+3);
    $ticket->Cell(66*0.32, 2, "", "T" ,0, "C");
    
    $ticket->SetXY(7+66*0.75, $ticket->GetY());
    $ticket->Cell(66*0.25, 2, "", "T" ,0, "C");
    $ticket->SetY($ticket->GetY()-5);

    $ticket->SetFont('Arial','B',$bF+2);
    $ticket->SetXY(7+66*0.4, $ticket->GetY()+5);
    $sql="SELECT id_pedido FROM ec_pedidos ";
    $ticket->Cell(66*0.3, 6, utf8_decode("Total"), "" ,0, "L");
//die("here : { $devolucion['pagos_realizados']}");
    if( $devolucion['pagos_realizados'] > 0 ){
      $ticket->SetFont('Arial','B',$bF+2);
      $ticket->SetXY(5, $ticket->GetY()+6);
      $ticket->Cell(66, 6, utf8_decode("TOTAL PAGADO : $ {$devolucion['pagos_realizados']}"), "" ,0, "L");
      $ticket->SetXY(5, $ticket->GetY()+6);
      $ticket->Cell(66, 6, utf8_decode("NUEVO TOTAL : {$devolucion['total_actual']}"), "" ,0, "L");
    }
    
    $ticket->SetXY(7+66*0.75, $ticket->GetY());
    if(!isset($monto_devolucion)){
    if($pedido_pagado==1){
      //$ticket->Cell(66*0.25, 6, "$ " . number_format(round($total,2), 2), "" ,0, "R");
      $ticket->Cell(66*0.25, 6, "$ " . number_format(round($total_de_devolucion,2), 2), "" ,0, "R");
    }else{
      //$ticket->Cell(66*0.25, 6, "$ " . number_format(round($total_de_devolucion,2), 2), "" ,0, "R"); 
      $ticket->Cell(66*0.25, 6, "$ " . number_format(round($total_de_devolucion,2), 2), "" ,0, "R");    
    }
    }else{
        //$ticket->Cell(66*0.25, 6, "$ " . number_format(round($monto_devolucion,2), 2), "" ,0, "R");
        $ticket->Cell(66*0.25, 6, "$ " . number_format(round($monto_devolucion,2), 2), "" ,0, "R");    
    }

    $ticket->SetFont('Arial','',$bF);
    $ticket->SetXY(7, $ticket->GetY()+6);
    if(!isset($monto_devolucion)){
      $ticket-> MultiCell(66,5, utf8_decode("Favor de revisar su producto, en esta mercancía no aplican cambios ni devoluciones"), 0 ,'J', false);
    }
    $ar = fopen("../../leyenda_ticket/leyenda.txt","r") or die ('No se pudo abrir el archivo');

    while(!feof($ar))
    {
        $linea=fgets($ar);
        $lineasalto=nl2br($linea);
    }
    fclose($ar);
    $acotado = substr($linea,0,165);
    $ticket->SetXY(10, $ticket->GetY()+4);
    $ticket-> MultiCell(60,5, utf8_decode($acotado), 0 ,'J', false);
/*implementación Oscar 25.06.2019 para el código de barras en el ticket*/
    
    //generacion de codigo de barras 
      include('../../include/barcode/barcode.php');
      $barcode_name = str_replace(' ', '', $folio_pedido_original );
      $barcodePath = "../../img/codigos_barra/{$barcode_name}.png";
      if( file_exists( $barcodePath ) ){
        unlink( $barcodePath );
      }
      barcode( $barcodePath, $barcode_name, '60', 'horizontal', 'code128', true, 1);
      if( file_exists( $barcodePath ) ){
        $ticket->SetXY(5, $ticket->GetY()+10);
        $ticket->Image( $barcodePath, 15, $ticket->GetY()+5,46);
      }
  //  echo $folio_pedido_original;
/*Fin de cambio oscar 25.06.2019*/    #$ticket->Output();
    if($printPan == 1) {
       $ticket->Output();
    }else{
/*implementacion Oscar 2024-02-01 para ruta especifica de ticket*/
    /*instancia clases*/
        include( '../../conexionMysqli.php' );
        include( '../../code/especiales/controladores/SysArchivosDescarga.php' );
        $SysArchivosDescarga = new SysArchivosDescarga( $link );
        include( '../../code/especiales/controladores/SysModulosImpresionUsuarios.php' );
        $SysModulosImpresionUsuarios = new SysModulosImpresionUsuarios( $link );
        include( '../../code/especiales/controladores/SysModulosImpresion.php' );
        $SysModulosImpresion = new SysModulosImpresion( $link );

        $nombre_ticket="ticket_".$user_sucursal."_".date("YmdHis")."_".strtolower($tipofolio)."_devolucion_".$folio."_1.pdf";
        $nombre_ticket=str_replace(" ","", $nombre_ticket);
        
        $tipo_modulo = 4;
        if( isset( $_GET['id_pedido_original'] ) ){
          $tipo_modulo = 2;
        }

        $ruta_salida = '';
        $ruta_salida = $SysModulosImpresionUsuarios->obtener_ruta_modulo_usuario( $user_id, $tipo_modulo );//Devolución antes de validación
        if( $ruta_salida == 'no' ){
            $ruta_salida = "cache/" . $SysModulosImpresion->obtener_ruta_modulo( $user_sucursal, $tipo_modulo );//Devolución antes de validación
        }
        $ticket->Output( "../../{$ruta_salida}/{$nombre_ticket}", "F" );
        /*Sincronización remota de tickets*/
    		if( $user_tipo_sistema == 'linea' ){/*registro sincronizacion impresion remota*/
          $registro_sincronizacion = $SysArchivosDescarga->crea_registros_sincronizacion_archivo( 'pdf', $nombre_ticket, $ruta_or, $ruta_salida, $user_sucursal, $user_id );
        }else{//impresion por red local
          $enviar_por_red = $SysArchivosDescarga->crea_registros_sincronizacion_archivo_por_red_local( $tipo_modulo, 'pdf', $nombre_ticket, '', $ruta_salida, $user_sucursal, $user_id, 
          $carpeta_path, '../', 'location="index.php?";' );
        }
        if(isset($id_pedido_original) && $flag_tkt=='devuelve_efectivo'){
            $sql="UPDATE ec_devolucion SET status=3,observaciones='Dinero regresado al cliente' WHERE id_pedido=$id_pedido_original";
            $eje=mysql_query($sql)or die("Error al actualizar el status de la devolución\n\n".mysql_error()."\n\n".$sql);
           //echo $sql;
        }
    }
?>