<?php


    extract($_GET);
    
    $detas=explode("," ,$dets);
    $cants=explode("," ,$cans);
    
    if($dets == '')
    {
        $dets="-945";
    }

	$totdev=0;

    $sql="SELECT
          p.orden_lista,
          p.nombre,
          0,
          CONCAT('$', FORMAT(precio, 0)),
          '-',
          0,
          '-',
          precio,
          0,
          pd.id_pedido_detalle,
          0,
          p.id_productos,
          pd.id_pedido_detalle
          FROM ec_pedidos_detalle pd
          JOIN ec_productos p ON pd.id_producto = p.id_productos
          WHERE pd.id_pedido=$id_pedido
          AND pd.id_pedido_detalle IN($dets)";
          
          
    $res=mysql_query($sql) or die("Error en:<br>$sql<br><br>Descripcion:<br>".mysql_error());
    
    $num=mysql_num_rows($res);
              
    $prods=Array();
    
    for($i=0;$i<$num;$i++)
    {
        $row=mysql_fetch_row($res);
        
        
        for($j=0;$j<sizeof($detas);$j++)
        {
            if($detas[$j] == $row[12])
            {
                $row[2]=$cants[$j];
                $row[5]="$".number_format($cants[$j]*$row[7], 0);
                $row[8]=$cants[$j]*$row[7];
				$totdev+=$cants[$j]*$row[7];
            }
        }
        
        array_push($prods, $row);
    }


	$totres=0;

    $sql="SELECT
		  id_pedido_detalle,
          precio,
          cantidad,
		  monto
          FROM ec_pedidos_detalle pd
          WHERE pd.id_pedido=$id_pedido";
          
          
    $res=mysql_query($sql) or die("Error en:<br>$sql<br><br>Descripcion:<br>".mysql_error());
    
    $num=mysql_num_rows($res);
    $nver=0;          
        
    for($i=0;$i<$num;$i++)
    {
        $row=mysql_fetch_row($res);
        $nver=0;
        
        for($j=0;$j<sizeof($detas);$j++)
        {
			//echo "$detas[$j] = $row[0]<br>";
            if($detas[$j] == $row[0])
            {
				$totres+=($row[2]-$cants[$j])*$row[1];
				
				$nver++;
            }
			
        }

		if($nver == 0)
			$totres+=$row[3];
			
		//echo $totres."<br>";		
        

    }



	//Buscamos el total pagado por el cliente en el pedido
	$sql="	SELECT
			SUM(monto)
			FROM ec_pedido_pagos
			WHERE id_pedido=$id_pedido";
	$re=mysql_query($sql) or die("Error en:<br>$sql<br><br>Descripcion:<br>".mysql_error());

	$ro=mysql_fetch_row($re);
	
	$pagado=$ro[0];
	
	//echo "Pagado: $pagado, Totaldev: $totdev, Totalrestante: $totres";
	
	
	


?>