<?php
	include("../../../../conectMin.php");

	//php_track_vars;

	extract($_GET);
	extract($_POST);
	 $filaCSV='';
	 $j = 0;
     $cant = count($arr);
     //print_r($arr);

 //------------- CCONSULTA A BD PARA TRAER DATOS ASOCIADOS CON CONTENIDO ---------------//
       
          $query = "SELECT 
                pp.id_pedido AS ID,
                fecha AS Fecha,
                hora AS Hora,
                SUM(monto) AS Monto,
                p.folio_nv AS Folio,
                IF(pagado = 1,'Pagado','Sin pagar') AS Estatus,
                c.nombre,
                'SI' AS Exportado,
                pp.id_tipo_pago AS Forma
                FROM ec_pedido_pagos pp
                JOIN ec_tipos_pago tp ON pp.id_tipo_pago = tp.id_tipo_pago
                JOIN ec_pedidos p ON  pp.id_pedido=p.id_pedido
                JOIN ec_clientes c ON p.id_cliente = c.id_cliente
                WHERE p.id_sucursal=$user_sucursal AND pp.id_pedido = $arr[0]";

  
        

         if($cant > 1)
         {       
             for($i=1;$i<$cant;$i++)
             {
                $query.=" OR pp.id_pedido = $arr[$i]";
             }  
          }
          
       
         $query .= " GROUP BY pp.id_pedido";  
     
     
      
        $result   = mysql_query($query) or die (mysql_error());
        $num      = mysql_num_fields($result);
        $numR = mysql_num_rows($result);
 if($numR != 0)
{        
        $headers = array();
        for($i=0;$i<$num;$i++)
        {	
        	if($j == ($num-1))
        	{
        		$filaCSV .= mysql_field_name($result,$i)."\n";
        	}
        	else
        	{
        		$filaCSV.=mysql_field_name($result,$i).',';
        	}
        	
        	$j+=1;
        }
//----------------------------------------------------------------------------------------// 
        $j =0;
        while($fila = mysql_fetch_row($result))
        {
        	$j= 0;
        	for($i=0;$i<$num;$i++)
        	{
        		if($j == ($num-1))
        	{
        		$filaCSV .="$fila[$i]"."\n";
        	}
        	else
        	{
        		$filaCSV.="$fila[$i]".',';
        	}
        	
        	$j+=1;
        	}
        }
  //------------------------- CAMBIANDO EXPORTADO A 1 -----------------------------------//
     mysql_query('BEGIN');
     for($j=0;$j<$cant;$j++)
     {
        $sql = "UPDATE ec_pedido_pagos SET exportado = 1 WHERE id_pedido_pago = $arr[$j]";
        $res   = mysql_query($sql) or die (mysql_error());
     }
     if($res)
     {
     
        mysql_query('COMMIT');
     }
      
            $rutaArchivo = "../../../../csv/reporte_".date('YmdHis').".csv";

        	//echo $filaCSV;
			$f = fopen($rutaArchivo,"a");


fwrite($f,$filaCSV);
fclose($f);
echo $rutaArchivo;
}
/*else
{
    
            echo "No hay Datos correspondientes al periodo que requieres";
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            
            header("Content-type: atachment/vnd.ms-excel");
            header("Content-Disposition: atachment; filename=\"nventa.csv\";");
            header("Content-transfer-encoding: binary\n");
            echo $out;

}*/

		
	




?>

