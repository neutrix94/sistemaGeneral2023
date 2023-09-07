<?php

    header("Content-Type: text/plain;charset=utf-8");

    //include("../../conectMin.php");
    
    include("../../conectMin.php");

    extract($_GET);
//$id_lista_prc
    $id_lista_precio=$_GET['id_lista_prc'];

	if(!isset($tipo))
		$tipo='nuevo';
    
    
    function validaPromo($c, $ps, $cs, $prod, $t,$suc,$id_lista_precio)
    {
     //echo $suc."-";
/*implementación de Oscar 07.09.2018 para que solo se tomen las listas de precios de la sucursal*/
      $eje=mysql_query("SELECT id_precio,lista_precios_externa FROM sys_sucursales WHERE id_sucursal=$suc")or die("Error al buscar id´s de listas\n\n".$sql);
      $listas=mysql_fetch_row($eje);
     // $condicion="AND id_precio IN(IF(sp.es_externo=0,".$listas[0].",".$listas[1]."))";
      //$condicion_1="AND pd.id_precio IN(IF(sp.es_externo=0,".$listas[0].",".$listas[1]."))";

      $condicion="AND id_precio IN(".$id_lista_precio.")";
      $condicion_1="AND pd.id_precio IN(".$id_lista_precio.")";
/*fin de cambio 07.09.2018*/

        $precios=array();
        $pros="";
        $cf=$c;
        $nver=0;
        $nver2=0;        
        
        for($i=0;$i<sizeof($ps);$i++){
            if($pros != '')
                $pros.=",";
            
            $precios[$ps[$i]]='';
            
            $pros.=$ps[$i];
        }
        
        if($pros == ''){
            return Array();
        }
          
        //Buscamos el precio base del prod deacuerdo al producto original
        /*$sql="SELECT
				        precio_venta
				      FROM ec_precios_detalle
				      WHERE $c >= de_valor
				      AND $c <= a_valor
				      AND id_producto = $prod
				      AND de_valor > 1 
            $condicion";*/
    /*implementacion Oscar 07.09.2018 para corrección de error de precios cuando se tiene en más de una lista*/
          $sql="SELECT
                pd.precio_venta
              FROM ec_precios_detalle pd
              LEFT JOIN sys_sucursales_producto sp ON pd.id_producto=sp.id_producto
              WHERE $c >= pd.de_valor
              AND $c <= pd.a_valor
              AND pd.id_producto = $prod
              AND pd.de_valor > 1 
              AND sp.id_sucursal=$suc
              $condicion_1";
    /*fin de cambio*/
				
		

    //die($sql);		
				
        $res=mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
        $num=mysql_num_rows($res);
        if($num > 0){
            $row=mysql_fetch_row($res);
            $precioIni=round($row[0], 2);
        }else{
            $precioIni=-1;
        }
        
        //Buscamos si todos los productos tienen oferta para la cantidad
        
        $sql="SELECT
        p.id_productos,
        IF((SELECT/*buscamos si tiene un precio*/
            1
            FROM ec_precios_detalle
            WHERE $c >= de_valor
            AND $c <= a_valor
            AND id_producto = p.id_productos
            AND de_valor > 1 $condicion
            LIMIT 1
          )IS NULL,0,1) as tiene_precio,
        IF((SELECT/*buscamos si teiene precio; si tiene precio toma el valor del detalle de precios, si no marca 'NO' como respuesta*/
            1
            FROM ec_precios_detalle
            WHERE $c >= de_valor
            AND $c <= a_valor
            AND id_producto = p.id_productos
            AND de_valor > 1 $condicion
            LIMIT 1
          ) IS NULL,
          'NO',
          (
            SELECT
            precio_venta
            FROM ec_precios_detalle
            WHERE $c >= de_valor
            AND $c <= a_valor
            AND id_producto = p.id_productos $condicion
            LIMIT 1
          )
        ) as precio1,
        (
                  SELECT/*buscamos precio de oferta*/
          precio_venta
          FROM ec_precios_detalle
          WHERE $c >= de_valor
          AND $c <= a_valor
          AND id_producto = p.id_productos
          AND de_valor > 1 $condicion
          LIMIT 1
        ),
        IF(/*si el precio no existe en lista, entonces,tomamos el precio de mayoreo*/
          (
                    SELECT
            precio_venta
            FROM ec_precios_detalle
            WHERE $c >= de_valor
            AND $c <= a_valor
            AND id_producto = p.id_productos
            $condicion
            LIMIT 1 
          ) IS NULL,
          p.precio_venta_mayoreo,
          (
                    SELECT
            precio_venta
            FROM ec_precios_detalle
            WHERE $c >= de_valor
            AND $c <= a_valor
            AND id_producto = p.id_productos
            $condicion
            LIMIT 1 
          )
        )
        FROM ec_productos p
        LEFT JOIN sys_sucursales_producto sp ON p.id_productos=sp.id_producto
        WHERE p.id_productos IN($pros) AND sp.id_sucursal=$suc";
        
        //echo $sql;
        //echo "\n";
        
        
        
        $res=mysql_query($sql) or die("Error en:\n$sql\n\nDescripcion:\n".mysql_error());
        $num=mysql_num_rows($res);
        
        //echo 'ya consultó';
      //Validamos que tengan ofertas en cantidad actual
        for($i=0;$i<$num;$i++){
            $row=mysql_fetch_row($res);
            if($row[1] == '0'){
				      if($t == 'eliminar'){
                $precios[$row[0]]=$row[4];
              }else{
                $precios[$row[0]]='NO';
              }    
              $nver++;
                //break;
            }   
        }//fin dde for $i
       
       if($nver > 0)
       {
           //echo "NO OFERTA\n";
           $proN=array();
           $cansN=array();
           $canN=$c;
           
           for($i=0;$i<sizeof($ps);$i++)
           {
               if($precios[$ps[$i]] == '')
               {
                   array_push($proN, $ps[$i]);
                   array_push($cansN, $cs[$i]);
               }
               else
                $canN-=$cs[$i]; 
           }
           
           $Nprecios=validaPromo($canN, $proN, $cansN, $prod, $t,$suc,$id_lista_precio);
           
           //print_r($Nprecios);
           
           for($i=0;$i<sizeof($proN);$i++)
           {
               $precios[$proN[$i]]=$Nprecios[$proN[$i]];
           }
       }
        
       else{
         //Validamos contra precio
            for($i=0;$i<$num;$i++)
            {
                mysql_data_seek($res, $i);
                $row=mysql_fetch_row($res);
                
                $row[3]=round($row[3], 2);
                
                //echo $row[3]." - ".$precioIni."\n";
                
                if($row[3] != $precioIni)
                {
					if($t == 'eliminar'){
						$precios[$row[0]]=$row[4];
          }
					else{
            $precios[$row[0]]='NO';
          }        
            $nver2++;
                    //break;
                }    
                
            }//fin de for $i
            if($nver2 > 0)
           {
               
               //echo "NO CANTIDAD\n";
               
               $proN=array();
               $cansN=array();
               $canN=$c;
               
               for($i=0;$i<sizeof($ps);$i++){
                  if($precios[$ps[$i]] == ''){
                       array_push($proN, $ps[$i]);
                       array_push($cansN, $cs[$i]);
                  }else{
                    $canN-=$cs[$i]; 
                  }
               }//fin de for i
               
               $Nprecios=validaPromo($canN, $proN, $cansN, $prod, $t,$suc,$id_lista_precio);
               
               //print_r($Nprecios);
               
               for($i=0;$i<sizeof($proN);$i++)
               {
                   $precios[$proN[$i]]=$Nprecios[$proN[$i]];
               }
           }
           else
           {
               for($i=0;$i<$num;$i++){
                    mysql_data_seek($res, $i);
                    
                    $row=mysql_fetch_row($res);
                    
                    $precios[$row[0]]=$row[2];
                       
                    
                }//fin de for i        
           }
           
       }
       //print_r($precios);
       //echo "\n";
       return $precios;  
        
    }//fin de la función validarMontos
    
    
    
    //COnvetimos en arrays
    
    $productos=explode(",", $prods);
    $cantidades=explode(",", $cans);
    

	
    
    $vars=validaPromo($can, $productos, $cantidades, $prodAct, $tipo,$user_sucursal,$id_lista_precio);
    
    
    echo "exito|".sizeof($productos);
     
    for($i=0;$i<sizeof($productos);$i++)
    {
        echo "|";
        
        //$precio=floor($vars[$productos[$i]]);
        $precio=$vars[$productos[$i]];
        
        if($vars[$productos[$i]] == 'NO')
            echo "NO";
        else
            echo "$".number_format($precio);
        
        echo "~";
        
        
        if($vars[$productos[$i]] == 'NO')
            echo "NO";
        else
            echo "$".number_format($precio*$cantidades[$i]);
        
        
        echo "~";
        
        echo $precio;
        
        
        echo "~";
        
        echo $precio*$cantidades[$i];
        
    } 
    
    
    


?>