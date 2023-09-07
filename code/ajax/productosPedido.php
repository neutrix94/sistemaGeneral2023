<?php


    include("../../conectMin.php" );
    
    extract($_GET);
    
    
    
    if(!isset($id_pedido))
        $id_pedido='';
    
    
    
    if($tipo == 1)
    {

        $sql="SELECT
              pd.id_pedido_detalle,
              pd.id_pedido,
              pd.id_producto,
              p.codigo,
              p.descripcion,
              pd.cantidad,
              pd.precio,
              pd.monto,
              '',
              pd.observaciones
              FROM fl_pedido_detalle pd
              JOIN fl_productos p ON pd.id_producto = p.id_producto
              WHERE id_pedido='$id_pedido'";
              
              
        
       
        //Buscamos los datos de la consulta final
        $res = mysql_query($sql) or die("Error en:\$sql\n\nDescripcion:\n" . mysql_error());
    
        $num = mysql_num_rows($res);
    
        echo "exito";
        for ($i = 0; $i < $num; $i++)
        {
            $row = mysql_fetch_row($res);
            echo "|";
            for ($j = 0; $j < sizeof($row); $j++)
            {
                if ($j > 0)
                    echo "~";
                echo utf8_encode($row[$j]);
            }
        }
    }
    
    if($tipo == 2)
    {
        $ip = sprintf("%u",ip2long($_SERVER['REMOTE_ADDR']));
        $namef=$rootpath."/cache/fl_pedidos_".$id_pedido."_".$ip.".dat";
        $fileant="";
        
        if($iteracion > 0)
        {
            //die();
            $file=fopen($namef,"rt");
            if($file)
            {
                while(!feof($file))
                {
                    $cadaux=fread($file,1000);
                    $fileant.=$cadaux;
                }
            }       
            fclose($file);      
            
        }
        $file = fopen($namef,"wt");
        if($file)
        {           
            $myQuery=array();
                        
            if($iteracion > 0)
            {   
                $myQuery=explode("|",$fileant);
                array_pop($myQuery);            
            }   
            else
            {
                            
                    array_push($myQuery,"DELETE FROM fl_pedido_detalle WHERE id_pedido = '$id_pedido'");
            }               
                
           
            
            for($i=0;$i<$numdatos;$i++)
            {
                  
                if($dato1[$i] == 'NO')
                {
                    $sql="INSERT INTO fl_pedido_detalle(id_pedido, id_producto, precio, cantidad, monto, observaciones) VALUES('$dato2[$i]', '$dato3[$i]', '$dato7[$i]', '$dato6[$i]', '$dato8[$i]', '$dato10[$i]')";
                    array_push($myQuery, $sql); 
                }
                else
                {
                    $sql="UPDATE $tabla SET ";
                    for($j=0;$j<sizeof($campos);$j++)
                    {
                        if($campos[$j] != "NO")
                        {
                            if($j > 0)
                                $sql.=",";
                            $sql.=$campos[$j]."='"; 
                            $aux="dato".($j+1);
                            $ax=$$aux;
                            $sql.=$ax[$i]."'";
                        }   
                    }               
                    $sql.=" WHERE ".$campos[0]."=".$dato1[$i];
                    array_push($myQuery, $sql); 
                    $myQuery[0].=" AND ".$campos[0]." <> ".$dato1[$i];
                }
            }       
            
            for($i=0;$i<sizeof($myQuery);$i++)
                //echo $myQuery[$i];
                fwrite($file,$myQuery[$i]."|"); 
        }
        fclose($file);
        echo "exito|".$namef;
    }
    
?>