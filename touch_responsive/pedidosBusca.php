<?php

    include("../conectMin.php");
    //include("../conexionDoble.php");

    extract($_GET);
    
    header("Content-Type: text/plain;charset=utf-8");
    mysql_set_charset("utf8");
    if (function_exists("mb_internal_encoding")) mb_internal_encoding ('utf-8');
    
//sacamos el año actual
    $a_fech=date("Y");//implementación Oscar 31-05-2018

/***************Pagos y apartados***************/
    if($tipo == 1){
        $cs = "SELECT nombre AS sucursal, prefijo FROM sys_sucursales WHERE id_sucursal = '{$user_sucursal}' ";
        if ($rs = mysql_query($cs/*,$local*/))
        {
            if ($dr = mysql_fetch_assoc($rs))
            {
                $sucursal = $dr["sucursal"];
                $prefijo = $dr["prefijo"];
            }
            mysql_free_result($rs);
        }
               

        $sql="SELECT
              p.id_pedido,
              IF(p.folio_nv IS NULL, p.folio_pedido, p.folio_nv),
              CONCAT('A$prefijo', folio_abono),
              p.fecha_alta,
              c.nombre,
              p.total,
              total-(SELECT IF(SUM(monto) IS NULL, 0, SUM(monto)) FROM ec_pedido_pagos WHERE id_pedido = p.id_pedido AND (referencia='' OR referencia=null)),
        /*implementación Oscar 05.09.2018*            
              (SELECT 
                  SUM(IF(dev.id_devolucion is null,0,pd.monto))
                FROM ec_devolucion_pagos pd
                LEFT JOIN ec_devolucion dev ON pd.id_devolucion=dev.id_devolucion
                WHERE dev.id_pedido=p.id_pedido),
        /*fin de cambio*/
              (SELECT GROUP_CONCAT(CONCAT(pd.cantidad, ' ', pr.nombre) SEPARATOR ',') FROM ec_pedidos_detalle pd JOIN ec_productos pr ON pd.id_producto = pr.id_productos WHERE pd.id_pedido=p.id_pedido)
              FROM ec_pedidos p
              JOIN ec_clientes c ON p.id_cliente = c.id_cliente
              WHERE pagado=0
              AND p.id_pedido > 0
              AND p.id_sucursal = $user_sucursal
 			  AND
			  (
				p.id_estatus <> 7
				OR
				(SELECT COUNT(1) FROM ec_devolucion WHERE id_pedido = p.id_pedido) <= 0
			  )";
      /*implementación de Oscar para buscador 31.08.2018*/
        if($id_pedido != ''){
          $sql.=" AND p.id_pedido=$id_pedido";
        }else if($clave!=''){
            $sql.=" AND (c.nombre LIKE '%".$clave."%'";
            $sql.=" OR p.total like '%$clave%'";
            $sql.=" OR IF(p.folio_nv IS NULL, p.folio_pedido, p.folio_nv) LIKE '%".$clave."%'";
            $sql.=" OR CONCAT('A$prefijo', folio_abono) like '%".$clave."%'";
            $sql.=" OR p.fecha_alta LIKE '%".$clave."%')";
        }
      /*Implementación Oscar para sacar pedidos del año en curso 31.05.2018*/
        $sql.=" AND p.fecha_alta LIKE '%".$a_fech."%'";
      /*Fin de cambio*/
        $sql.=" ORDER BY p.id_pedido DESC LIMIT 30";//modificación Oscar 30.05.2018 para mostrar má de 30 pedidos          
              
        $res=mysql_query($sql/*,$local*/) or die("Error en:\n".mysql_error()."$sql");      
        
        $num=mysql_num_rows($res);
        
        echo "exito";
        
        for($i=0;$i<$num;$i++)
        {
            $row=mysql_fetch_row($res);
            
            echo "|";
            
            for($j=0;$j<sizeof($row);$j++)
            {
                if($j > 0)
                    echo "~";
                echo $row[$j];
            }
        }
        
    }//fin de fi tipo==1 (pagos y apartados)
    
    if($tipo == 2)
    {
        $sql="SELECT
              id_pedido_pago,
              id_pedido,
              id_tipo_pago,
              fecha,
              monto
              FROM ec_pedido_pagos pp
              WHERE id_pedido='$id_pedido'
              ";
        $res=mysql_query($sql/*,$local*/) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());      
        
        $num=mysql_num_rows($res);
        
        echo "exito";
        
        for($i=0;$i<$num;$i++)
        {
            $row=mysql_fetch_row($res);
            
            echo "|";
            
            for($j=0;$j<sizeof($row);$j++)
            {
                if($j > 0)
                    echo "~";
                echo $row[$j];
            }
        }          
              
    }
    
    if($tipo == 3)
    {
        
        $sql="SELECT
              (p.total-(SELECT IF(SUM(monto) IS NULL, 0, SUM(monto)) FROM ec_pedido_pagos WHERE id_pedido=p.id_pedido AND(referencia='' OR referencia=null) )),
              p.id_pedido
              FROM ec_pedidos p
              WHERE p.id_pedido='$id_pedido'
              AND p.id_pedido > 0";
              
         $res=mysql_query($sql/*,$local*/) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());      
         
         $num=mysql_num_rows($res);
        
        if($num > 0)
        {
        
            $row=mysql_fetch_row($res);
         
            
            
            for($j=0;$j<sizeof($row);$j++)
            {
                if($j > 0)
                    echo "|";
                echo round($row[$j]);
            } 
        }
    }
    
    //curl 'http://192.168.100.100/Casadelasluces/touch/pedidosBusca.php?tipo=4&id_pedido=23&dato1[0]=48&dato2[0]=23&dato3[0]=1&dato4[0]=19634.6&dato5[0]=null&dato1[1]=NO&dato2[1]=$LLAVE&dato3[1]=1&dato4[1]=15000&dato5[1]=&iteracion=0&numdatos=2' -X POST -H 'Host: 192.168.100.100' -H 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:24.0) Gecko/20100101 Firefox/24.0' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' -H 'Accept-Language: es-MX,es-ES;q=0.8,es-AR;q=0.7,es;q=0.5,en-US;q=0.3,en;q=0.2' -H 'Accept-Encoding: gzip, deflate' -H 'Referer: http://192.168.100.100/Casadelasluces/touch/index.php?scr=pagos' -H 'Cookie: casadelasluces=1%7C1'
    
/*************Pagos y apartados************/
    if($tipo == 4){
        $ids=array();//declaramos el arreglo que guardará los ids de pagos
        
        for($i=0;$i<$numdatos;$i++){
          /*implementación Oscar 09.08.2018*/
            $sql="SELECT count(id_pedido_detalle) FROM ec_pedidos_detalle WHERE es_externo=1 AND id_pedido=$id_pedido";
            $eje=mysql_query($sql)or die("Error al consultar si el pedido contiene productos externos");
            $r=mysql_fetch_row($eje);
            $hay_externos=$r[0];
            if($hay_externos>0){
            //buscamos cantidad total de productos de Pedro y externos
              $sql="SELECT
                      aux.interno/aux.total,
                      aux.externo/aux.total
                    FROM(
                      SELECT 
                        SUM(monto-descuento) as total,
                        SUM(IF(es_externo=1,monto-descuento,0)) as externo,
                        SUM(IF(es_externo=0,monto-descuento,0))as interno 
                      FROM ec_pedidos_detalle WHERE id_pedido=$id_pedido
                    )aux";
              $eje=mysql_query($sql)or die("Error al consultar porcentajes de pagos interno y externo!!!\n\n".$sql."\n\n".mysql_error());
              $respuesta=mysql_fetch_row($eje);
            //guardamos los porcentajes correspondientes a la nota final
              $pago_interno=$respuesta[0];
              $pago_externo=$respuesta[1];
              $cont=1;
            }else{
            //guardamos lo porcentajes sin productos externos
              $pago_interno=1;
              $pago_externo=0;
              $cont=0;
            }
          /*fin de cambio*/    

        /*implementación de Oscar 28.06.2019 para saber si la sucursal es multicajero*/
          $sql="SELECT IF(multicajero=1,0,(SELECT id_cajero FROM ec_sesion_caja WHERE id_sucursal=$user_sucursal AND hora_fin='00:00:00' 
            AND fecha=date_format(now(), '%Y-%m-%d')) ) 
            FROM ec_configuracion_sucursal WHERE id_sucursal=$user_sucursal";
          $eje=mysql_query($sql)or die("Error al consultar si la sucursal es multicajero!!!\n".mysql_error());
          $r_c=mysql_fetch_row($eje);
          $id_cajero=$r_c[0];
      /*Fin de cambio Oscar 28.06.2019*/

          mysql_query("BEGIN");
  //die("contador:".$cont);
            for($j=0;$j<=$cont;$j++){
              if($dato1[$i] == 'NO'){  
                if($j==0||$j==1&&$pago_externo>0){
                  $cs="INSERT INTO ec_pedido_pagos SET
                  id_pedido = '$id_pedido',
                  id_tipo_pago = '{$dato3[$i]}',
                  fecha = CURDATE(),
                  hora = CURTIME(),";
                  if($j==0){
                    $cs.="monto=".round($dato5[$i]*$pago_interno,2).",";
                  }else{
                    $cs.="monto=".round($dato5[$i]*$pago_externo,2).",";
                  }
                //monto = '{$_GET["mon{$ix}"]}',
                $cs.="referencia = '',
                id_moneda = '1',
                tipo_cambio = '1',
                id_nota_credito = '-1',
                id_cxc = '-1',";
          //marcamos si el pago es externo o no
            if($j==0){
              $cs.="es_externo=0";//interno
            }else{
              $cs.="es_externo=1";//externo
            }
        /*implementacion Oscar 28.06.2019*/
            $cs.=",id_cajero=".$id_cajero;
        /*Fin de cambio Oscar 28.06.2019*/
                }
                //$sql="INSERT INTO ec_pedido_pagos(id_pedido, id_tipo_pago, fecha, hora, monto, referencia, id_moneda, tipo_cambio, id_nota_credito, id_cxc)
                  //      VALUES($id_pedido, ".$dato3[$i].", NOW(), NOW(), ".$dato5[$i].", '', 1, 1, -1, -1)";                           
                $eje=mysql_query($cs);
                if(!$eje){
                  $error=mysql_error();
                  mysql_query("ROLLBACK");
                  die("Error en:\n$cs\n\nDescripcion:\n".$error);           
                }
              //agregamos al arreglo de ids los ids de los pagos
                $idp=mysql_insert_id();                
                array_push($ids, $idp);
              }
            }//fin de for i
        }
        
        $sql="UPDATE ec_pedidos p SET pagado=IF((p.total-(SELECT ROUND(SUM(IF(monto IS NULL, 0,monto))) FROM ec_pedido_pagos WHERE id_pedido=p.id_pedido AND (referencia='' OR referencia=null))) <= 0, 1, 0) WHERE p.id_pedido=$id_pedido";
        mysql_query($sql/*,$local*/) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
        
        mysql_query("COMMIT");

        echo "exito";
        
        for($i=0;$i<sizeof($ids);$i++)
        {
            echo "|".$ids[$i];
        }
    }
  //contamos el número de pedidos del año en curso
    
    if($tipo == 5)
    {
    //sacamos el año en curso
     $a_fech=date("Y");
     $sql="SELECT count(*) FROM ec_pedidos WHERE fecha_alta LIKE '%$a_fech%'";
     $eje=mysql_query($sql)or die("Error al calcular el total de registros del año "+$a_fech."\n\n".$sql."\n\n".mysql_error());
     $num=mysql_fetch_row($eje);
     $total_pags=ceil($num[0]/30);//calculamos el total de páginas redondeado hacia arriba
    //calculamos el inicio del LIMIT
      if($pag==1){//si es la primera página le asignamos limite inicial en cero
        $inicio=0;
      }else{//de lo contrario sacamos el cálculo para el inicio del límite
        $nicio=($pag*30)-1;
      }
    //calculamos el final del LIMIT
      if($pag==1){
        $fin=29;
      }else{
        $fin=(($pagina+1)*30)-1;
      }

    
        $sql="SELECT
              p.id_pedido,
              IF(p.folio_nv IS NULL, p.folio_pedido, p.folio_nv),
              c.nombre,
              p.fecha_alta,
              p.total
              FROM ec_pedidos p
              JOIN ec_clientes c ON p.id_cliente = c.id_cliente
              WHERE p.id_pedido > 0
              AND p.id_sucursal = $user_sucursal
			  AND
			  (
				p.id_estatus <> 7
				OR
				(SELECT COUNT(1) FROM ec_devolucion WHERE id_pedido = p.id_pedido) <= 0)";
    /*implementación de Oscar para buscador 31.08.2018*/
        if($id_pedido != ''){
          $sql.=" AND p.id_pedido=$id_pedido";
        }else if($folio!=''){
            $sql.=" and (c.nombre LIKE '%".$folio."%'";
            $sql.=" OR p.total like '%$folio%'";
            $sql.=" OR IF(p.folio_nv IS NULL, p.folio_pedido, p.folio_nv) LIKE '%".$folio."%'";
            $sql.=" OR p.fecha_alta LIKE '%".$folio."%')";
        }
      /*Implementación Oscar para sacar pedidos del año en curso 31.05.2018*/
        $sql.=" AND p.fecha_alta LIKE '%".$a_fech."%'";
      /*Fin de cambio*/
        $sql.=" ORDER BY p.id_pedido DESC LIMIT 30";//modificación Oscar 30.05.2018 para mostrar má de 30 pedidos           
              
        $res=mysql_query($sql/*,$local*/) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());      
        
        $num=mysql_num_rows($res);
        
        echo "exito";
        
        for($i=0;$i<$num;$i++)
        {
            $row=mysql_fetch_row($res);
            
            echo "|";
            
            for($j=0;$j<sizeof($row);$j++)
            {
                if($j > 0)
                    echo "~";
                echo $row[$j];
            }
        }
        
    }
    
    if($tipo == 6)
    {
        $sql="SELECT
              pd.id_pedido_detalle,
              pd.id_pedido,
              pd.id_producto,
              '0',
              p.orden_lista,
              p.nombre,
              pd.cantidad,
              pd.precio,
              pd.monto
              FROM ec_pedidos_detalle pd
              JOIN ec_productos p ON pd.id_producto = p.id_productos
              WHERE pd.id_pedido='$id_pedido'";
              
        $res=mysql_query($sql/*,$local*/) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());      
        
        $num=mysql_num_rows($res);
        
        echo "exito";
        
        for($i=0;$i<$num;$i++)
        {
            $row=mysql_fetch_row($res);
            
            echo "|";
            
            for($j=0;$j<sizeof($row);$j++)
            {
                if($j > 0)
                    echo "~";
                echo $row[$j];
            }
        }              
    }

?>