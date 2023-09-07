<?php
    
    header("Content-Type: text/plain;charset=utf-8");
    include("../../conectMin.php");
    extract($_GET);
    mysql_query("BEGIN");
    try{
      //Buscamos si es apartado
        $sql="SELECT pagado FROM ec_pedidos WHERE id_pedido='$id_pedido'";
        //die($sql);
        $rs = mysql_query($sql);
        $row=mysql_fetch_row($rs);
		/*************************No es apartado********************/
        if($row[0] == '1'){
            $ids='';
            $devids=array();
            $devcan=array();
            
            $nuOrd=0;
            $dev=0;
            $totPedN=0;

            for($i=0;$i<$numDet;$i++){
                if($idDetalle[$i] == 'NO'){
                    $nuOrd++;
                    $totPedN+=$monto[$i];
                }    
                if($idDetalle[$i] != 'NO')
                    $dev++;
            }

		    //echo "2<br>";
            
            //Datos pedido
            $sql="SELECT id_cliente FROM ec_pedidos WHERE id_pedido=$id_pedido";
            $rs = mysql_query($sql);
            $row=mysql_fetch_assoc($rs);
            extract($row);
            
            //Insertamos la devolucion
            if($dev > 0){
                 //Insertamos la devolucion
                $sql="INSERT INTO ec_devolucion(id_usuario, id_sucursal, fecha, hora, id_pedido, folio)
                        VALUES($user_id, $user_sucursal, NOW(), NOW(), $id_pedido, 'DEV$id_pedido')";
                if(!mysql_query($sql))
                        throw new Exception("No se pudo actualizar el pedido:\n\n $sql\n\n" . mysql_error());
                
                $idDev=mysql_insert_id();
                
                //Insertamos el detalle de la devolcion
                
                //$tot=0;
                
                //print($idDetalle);
                
                /*for($i=0;$i<$numDet;$i++)
                {
                    if($idDetalle[$i] != 'NO')
                    {*/
                        /*$sql="INSERT INTO ec_devolucion_detalle(id_devolucion, id_producto, cantidad)
                                                         VALUES($idDev, $idProducto[$i], $cantidad[$i])";*/
                        $sql="INSERT INTO ec_devolucion_detalle(id_devolucion, id_producto, cantidad)
                              (
                                    SELECT $idDev, id_producto, cantidad FROM ec_pedidos_detalle WHERE id_pedido=$id_pedido
                              )";


                        if(!mysql_query($sql))
                            throw new Exception("No se pudo actualizar el pedido:\n\n $sql\n\n" . mysql_error());                                 
                    /*}    
                }*/

				//echo "3<br>";
                
                
                /*if($beneficiario != 'CASA DE LAS LUCES')
                {*/
                    //Insertamos los pagos de la devolucion
                   /* for($i=0;$i<$numPag;$i++)
                    {

						if(isset($tipoPago[$i]) && isset($montoPago[$i]))
						{*/
	                        /*$sql="INSERT INTO ec_devolucion_pagos(id_devolucion, id_tipo_pago, monto, referencia, fecha, hora)
    	                                               VALUES($idDev, $tipoPago[$i], $montoPago[$i], '', NOW(), NOW())";*/

                            $sql="INSERT INTO ec_devolucion_pagos(id_devolucion, id_tipo_pago, monto, referencia, fecha, hora)
                                  (
                                    SELECT $idDev, 1, total, '', NOW(), NOW() FROM ec_pedidos WHERE id_pedido=$id_pedido
                                  )";                           
                                                   
        	                if(!mysql_query($sql))
            	                throw new Exception("No se pudo actualizar el pedido:\n\n $sql\n\n" . mysql_error());                                            
						/*}
                    }*/
                //} 
        //Insertamos movimiento de devolucion
          //Buscamos el almacen
                $sql="SELECT
                      a.id_almacen                      
                      FROM ec_almacen a
                      WHERE a.id_sucursal=$user_sucursal
                      AND a.id_almacen <> -1 
                      ORDER BY a.es_almacen DESC, prioridad";
                      
                $res=mysql_query($sql);
                if(!$res)
                    throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                
                $num=mysql_num_rows($res);
                if($num > 0)
                {
                    $row=mysql_fetch_row($res);
                    $id_almacen=$row[0];
                }
                else
                {
                    $id_almacen=-1;
                }

                //echo "5<br>";
                
                $sql="INSERT INTO ec_movimiento_almacen(id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, id_orden_compra, lote, id_maquila, id_transferencia, id_almacen)
                                                         VALUES(1, $user_id, $user_sucursal, NOW(), NOW(), 'DEVOLUCION $idDev', -1, -1, '', -1, -1, $id_almacen)";
                
                if(!mysql_query($sql))
                    throw new Exception("No se pudo actualizar el pedido:\n\n $sql\n\n" . mysql_error());
                
                $id_mov=mysql_insert_id();
                
                $sql="SELECT
                      id_producto,
                      cantidad
                      FROM ec_devolucion_detalle
                      WHERE id_devolucion=$idDev";
                      
                $res=mysql_query($sql);
                if(!$res)
                    throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                
                $num=mysql_num_rows($res);
                
              //insertamos el detalle
                for($i=0;$i<$num;$i++)
                {
                    $row=mysql_fetch_row($res);
                    
                    $sql="INSERT INTO ec_movimiento_detalle(id_movimiento, id_producto, cantidad, cantidad_surtida, id_pedido_detalle, id_oc_detalle)
                                                     VALUES($id_mov, $row[0], $row[1], $row[1], -1, -1)";
                    if(!mysql_query($sql))
                        throw new Exception("No se pudo actualizar el pedido:\n\n $sql\n\n" . mysql_error());                                                     
                }
            }



           //Validamos si quedan productos
             //Insertamos los productos
                    $sql="SELECT
                          id_pedido_detalle,
                          id_producto,
                          cantidad AS cant,
                          precio AS prec,
                          monto AS mont
                          FROM ec_pedidos_detalle
                          WHERE id_pedido=$id_pedido";
                      
                    //echo $sql;                  
                      



                    $res = mysql_query($sql);
                
                    if(!$res)
                        throw new Exception("No se pudo actualizar el pedido:\n\n $sql\n\n" . mysql_error());
                
                    $num=mysql_num_rows($res);
                
                
                    //echo "SI 05 - $num\n";
                    //$totreal=0;
                    
                    for($i=0;$i<$num;$i++)
                    {
                        $row=mysql_fetch_assoc($res);
                    
                        extract($row); 
                    
                        //Buscamos si existe
                        for($j=0;$j<$numDet;$j++)
                        {
                            if($idDetalle[$j] == $id_pedido_detalle)
                            {
                                $cant-=$cantidad[$j];                              
                            }    
                        }
                    
                        if($cant > 0)
                        {
                    
                          $nuOrd++;
                        }                    
                                                                                             
                    }

			

            
            if($nuOrd > 0)
            {
                //Buscamos el folio
                
                $prefijo = "";
                $folio = "";
                
                $cs = "SELECT prefijo FROM sys_sucursales WHERE id_sucursal = '{$user_sucursal}' ";
                if ($rs = mysql_query($cs)) {
                    if ($dr = mysql_fetch_assoc($rs)) {
                        $prefijo = $dr["prefijo"];
                    } mysql_free_result($rs);
                }
                
                $campo_segun_tipo = "folio_nv";
                
                $cs = "SELECT IF(ISNULL(MAX(CAST(REPLACE({$campo_segun_tipo}, '{$prefijo}', '') AS SIGNED INT))), 1, MAX(CAST(REPLACE({$campo_segun_tipo}, '{$prefijo}', '') AS SIGNED INT))+1) AS folio " .
                    "FROM ec_pedidos " .
                    "WHERE REPLACE({$campo_segun_tipo}, '{$prefijo}', '') REGEXP ('[0-9]') " .
                    "AND id_sucursal='{$user_sucursal}' " .
                    "AND id_pedido <> '{$id_pedido}' ";
                #die ($cs);
                if ($rs = mysql_query($cs)) {
                    if ($dr = mysql_fetch_assoc($rs)) {
                        $folio = $prefijo . sprintf("%05d", $dr["folio"]);
                        //die ("OK|FOLIO:{$folio}");
                    } mysql_free_result($rs);
                }
                
                $sql="INSERT INTO ec_pedidos(folio_nv, id_cliente, id_estatus, id_moneda, fecha_alta, id_direccion, id_razon_social, subtotal, iva, ieps, total, pagado, surtido, enviado, id_sucursal, id_usuario, fue_cot, facturado, id_tipo_envio, descuento, id_razon_factura)
                                      VALUES('$folio', $id_cliente, 2, 1, NOW(), -1, -1, $totPedN, 0, 0, $totPedN, 0, 1, 1, $user_sucursal, $user_id, 0, 0, 1, 0, -1)";
                
                 if(!mysql_query($sql))
                            throw new Exception("No se pudo actualizar el pedido:\n\n $sql\n\n" . mysql_error());
                 
                 $id_ped=mysql_insert_id();
                 
                 $totalNV=0;
                 
                  for($i=0;$i<$numDet;$i++)
                    {
                        if($idDetalle[$i] == 'NO')
                        {




                            $sql="INSERT INTO ec_pedidos_detalle(id_pedido, id_producto, cantidad, precio, monto, iva, ieps, cantidad_surtida)
                                                          VALUES($id_ped, $idProducto[$i], $cantidad[$i], $precio[$i], $monto[$i], 0, 0, $cantidad[$i])";

                            $totalNV+=$monto[$i];

                            if(!mysql_query($sql))
                                throw new Exception("No se pudo actualizar el pedido:\n\n $sql\n\n" . mysql_error());                                 
                        }    
                    }



                    //Insertamos los productos
                    $sql="SELECT
                          id_pedido_detalle,
                          id_producto,
                          cantidad AS cant,
                          precio AS prec,
                          monto AS mont
                          FROM ec_pedidos_detalle
                          WHERE id_pedido=$id_pedido";
                      
                    //echo $sql;                  
                      



                    $res = mysql_query($sql);
                
                    if(!$res)
                        throw new Exception("No se pudo actualizar el pedido:\n\n $sql\n\n" . mysql_error());
                
                    $num=mysql_num_rows($res);
                
                
                    //echo "SI 05 - $num\n";
                    //$totreal=0;
                    
                    for($i=0;$i<$num;$i++)
                    {
                        $row=mysql_fetch_assoc($res);
                    
                        extract($row); 
                    
                        //Buscamos si existe
                        for($j=0;$j<$numDet;$j++)
                        {
                            if($idDetalle[$j] == $id_pedido_detalle)
                            {
                                $cant-=$cantidad[$j];                              
                            }    
                        }
                    
                        if($cant > 0)
                        {
                    
                            $sql="INSERT INTO ec_pedidos_detalle(id_pedido, id_producto, cantidad, precio, monto, iva, ieps, cantidad_surtida)
                                                      VALUES($id_ped, $id_producto, $cant, $prec, ".($prec*$cant).", 0, 0, $cant)";
                                                      
                            //echo $sql;
                            //$totreal+=$prec*$cant;
                            $totalNV+=$prec*$cant;   
                                                                          
                            if(!mysql_query($sql))
                                throw new Exception("No se pudo actualizar el pedido:\n\n $sql\n\n" . mysql_error());
                        }                    
                                                                                             
                    }


                    $sql="UPDATE ec_pedidos SET subtotal=$totalNV, total=$totalNV WHERE id_pedido=$id_ped";

                    if(!mysql_query($sql))
                        throw new Exception("No se pudo actualizar el pedido:\n\n $sql\n\n" . mysql_error());
					
					
					//echo "($beneficiario)".strpos($beneficiario, "CASA");
                   //throw new Exception("No se pudo actualizar el pedido:\n\n $sql\n\n" . mysql_error()); 
                    
                    
               /*      if(strpos($beneficiario, "CASA") == 0)
                {*/
                    //Insertamos los pagos de la devolucion
					
					
					//echo "No:".$numPag;
                   /* for($i=0;$i<$numPag;$i++)
                    {*/
                        /*$sql="INSERT INTO ec_pedido_pagos(id_pedido, id_tipo_pago, fecha, hora, monto, referencia, id_moneda, tipo_cambio, id_nota_credito, id_cxc)
                                                   VALUES($id_ped, $tipoPago[$i], NOW(), NOW(), $montoPago[$i], '', 1, 1, -1, -1)";
						//echo $sql;
                        if(!mysql_query($sql))
                            throw new Exception("No se pudo actualizar el pedido:\n\n $sql\n\n" . mysql_error());                                            */
                    /*}
                }*/
                
				
				
				//throw new Exception("No se pudo actualizar el pedido:\n\n $sql\n\n" . mysql_error()); 
                    
                    //Insertamos movimiento de salida
                    
                     $sql="SELECT
                          id_producto,
                          cantidad
                          FROM ec_pedidos_detalle
                          WHERE id_pedido=$id_ped";
                          
                    $res=mysql_query($sql);
                    if(!$res)
                        throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                    
                    $num=mysql_num_rows($res);
                    for($i=0;$i<$num;$i++)
                    {
                            
                        $row=mysql_fetch_row($res);
                        
                        //Buscamos el almacen
                        $sql="SELECT
                              a.id_almacen,
                              a.prioridad,
                              (
                                SELECT
                                SUM(d.cantidad_surtida*tm.afecta)
                                FROM ec_movimiento_detalle d
                                JOIN ec_movimiento_almacen aa ON aa.id_movimiento_almacen = d.id_movimiento
                                JOIN ec_tipos_movimiento tm ON tm.id_tipo_movimiento = aa.id_tipo_movimiento
                                WHERE a.id_almacen = aa.id_almacen
                                AND d.id_producto=$row[0]
                              )
                              FROM ec_almacen a
                              WHERE a.id_sucursal=$user_sucursal
                              AND a.es_almacen=1
                              AND a.id_almacen <> -1 
                              ORDER BY prioridad";
                              
                        $re=mysql_query($sql);
                        if(!$re)
                            throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                        
                        $nu=mysql_num_rows($re);                    
                            
                        $canSur=$row[1];                  
                        
                        for($j=0;$j<$nu;$j++)
                        {
                                
                            $ro=mysql_fetch_row($re);
                            
                            //print_r($row);
                            
                            if($i == 0)
                                $almacenPri=$ro[0];
                            
                            if($ro[2] > 0)
                            {
                                $sql="INSERT INTO ec_movimiento_almacen(id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, id_orden_compra, lote, id_maquila, id_transferencia, id_almacen)
                                                                 VALUES(2, $user_id, $user_sucursal, NOW(), NOW(), '', {$id_ped}, -1, '', -1, -1, $ro[0])";
                            
                                //echo $sql;
                            
                                if (!mysql_query($sql)) throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                            
                                $id_mov=mysql_insert_id();
                                
                                if($canSur > $ro[2])
                                {
                                    $can=$ro[2];
                                    $canSur-=$ro[2];
                                }
                                else
                                {
                                    $can=$canSur;
                                    $canSur=0;
                                }
                                
                                $sql="INSERT INTO ec_movimiento_detalle(id_movimiento, id_producto, cantidad, cantidad_surtida, id_pedido_detalle, id_oc_detalle)
                                                                 VALUES($id_mov, {$row[0]}, $can, $can, -1, -1)";
                                                                 
                                //echo $sql;                                                         
                                if (!mysql_query($sql)) throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                                
                            }
                            else
                            {
                                continue;
                            }    
                                
                            if($canSur <= 0)
                            {
                                $j=$nu;
                                break;                        
                            }   
                        }
        
                        //die("NO");
        
                         //Insertamos el resto en el alamcen primario
                        if($canSur > 0)
                        {
                            
                            if(!isset($almacenPri))
                                $almacenPri=-1;
                            
                            $sql="INSERT INTO ec_movimiento_almacen(id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, id_orden_compra, lote, id_maquila, id_transferencia, id_almacen)
                                                                     VALUES(2, $user_id, $user_sucursal, NOW(), NOW(), '', {$id_ped}, -1, '', -1, -1, $almacenPri)";
                                
                            if (!mysql_query($sql)) throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                                
                            $id_mov=mysql_insert_id();
                            
                            $sql="INSERT INTO ec_movimiento_detalle(id_movimiento, id_producto, cantidad, cantidad_surtida, id_pedido_detalle, id_oc_detalle)
                                                                     VALUES($id_mov, {$row[0]}, $canSur, $canSur, -1, -1)";
                            if (!mysql_query($sql)) throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                        }
                    } 
            }
        }


		/*************************Es apartado********************/


        else
        {
            //die("SI");
            
            //echo "SI 01<br>";
            
            //Realizamos cancelacion
            $sql="INSERT INTO ec_devolucion
                  SELECT
                  NULL,
                  $user_id,
                  $user_sucursal,
                  NOW(),
                  NOW(),
                  $id_pedido,
                  'DEV$id_pedido'
                  FROM ec_pedidos
                  WHERE id_pedido=$id_pedido";
                  
            if(!mysql_query($sql))
                throw new Exception("No se pudo actualizar el pedido:\n\n $sql\n\n" . mysql_error());
                
                
            $idDev=mysql_insert_id();                
                
            //insertamos el detalle
            $sql="INSERT INTO ec_devolucion_detalle
                  SELECT
                  NULL,
                  $idDev,
                  id_producto,
                  cantidad
                  FROM ec_pedidos_detalle
                  WHERE id_pedido=$id_pedido";
                  
                              
            if(!mysql_query($sql))
                throw new Exception("No se pudo actualizar el pedido:\n\n $sql\n\n" . mysql_error());
                
            //insertamos los pagos
            $sql="INSERT INTO ec_devolucion_pagos
                  SELECT
                  NULL,
                  $idDev,
                  id_tipo_pago,
                  monto,
                  referencia,
                  NOW(),
                  NOW()
                  FROM ec_pedido_pagos
                  WHERE id_pedido=$id_pedido";  
                  
            if(!mysql_query($sql))
                throw new Exception("No se pudo actualizar el pedido:\n\n $sql\n\n" . mysql_error());                                 
            
            
            //Insertamos movimiento de devolucion
            //Buscamos el almacen
            $sql="SELECT
                  a.id_almacen                      
                  FROM ec_almacen a
                  WHERE a.id_sucursal=$user_sucursal
                  AND a.id_almacen <> -1 
                  ORDER BY a.es_almacen DESC, prioridad";
                  
            $res=mysql_query($sql);
            if(!$res)
                throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
            
            $num=mysql_num_rows($res);
            if($num > 0)
            {
                $row=mysql_fetch_row($res);
                $id_almacen=$row[0];
            }
            else
            {
                $id_almacen=-1;
            }

			//echo "SI 02<br>";            
            
            $sql="INSERT INTO ec_movimiento_almacen(id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, id_orden_compra, lote, id_maquila, id_transferencia, id_almacen)
                                                     VALUES(1, $user_id, $user_sucursal, NOW(), NOW(), 'DEVOLUCION $idDev', -1, -1, '', -1, -1, $id_almacen)";
            
            if(!mysql_query($sql))
                throw new Exception("No se pudo actualizar el pedido:\n\n $sql\n\n" . mysql_error());
            
            $id_mov=mysql_insert_id();
            
            $sql="SELECT
                  id_producto,
                  cantidad
                  FROM ec_devolucion_detalle
                  WHERE id_devolucion=$idDev";
                  
            $res=mysql_query($sql);
            if(!$res)
                throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
            
            $num=mysql_num_rows($res);
			
			//echo "SI 03 - $num<br>";
            
            for($i=0;$i<$num;$i++)
            {
                $row=mysql_fetch_row($res);
                
                $sql="INSERT INTO ec_movimiento_detalle(id_movimiento, id_producto, cantidad, cantidad_surtida, id_pedido_detalle, id_oc_detalle)
                                                 VALUES($id_mov, $row[0], $row[1], $row[1], -1, -1)";
                if(!mysql_query($sql))
                    throw new Exception("No se pudo actualizar el pedido:\n\n $sql\n\n" . mysql_error());                                                     
            }
            


		    //Verificamos si es necesario realizar un nuevo pedido
		
			$insNuevo=0;
		    
            $sql="SELECT
                  id_pedido_detalle,
                  id_producto,
                  cantidad AS cant,
                  precio AS prec,
                  monto AS mont
                  FROM ec_pedidos_detalle
                  WHERE id_pedido=$id_pedido";
                  
                  
            $res = mysql_query($sql);
            
            if(!$res)
                throw new Exception("No se pudo actualizar el pedido:\n\n $sql\n\n" . mysql_error());
            
            $num=mysql_num_rows($res);

			for($i=0;$i<$num;$i++)
            {
                $row=mysql_fetch_assoc($res);
                
                extract($row); 
                
                //Buscamos si existe
                for($j=0;$j<$numDet;$j++)
                {
                    if($idDetalle[$j] == $id_pedido_detalle)
                    {
                        $cant-=$cantidad[$j];                              
                    }    
                }
                
                if($cant > 0)
                {
					$insNuevo++;  
                }                    
                                                                                         
            }

            //$cadDet="";

			for($i=0;$i<$numDet;$i++)
            {
                if($idDetalle[$i] == 'NO')
                {
                    $insNuevo++;

                }    

                //$cadDet.=",".$idDetalle[$i];
            }


            

			$sql="UPDATE ec_pedidos SET id_estatus=7 WHERE id_pedido=$id_pedido";
				if(!mysql_query($sql))
					throw new Exception("Imposible almacenar registro (Actualizacion de estatus).\n\n$sql\n\n" . mysql_error());
					
					
					

            
            //Realizamos el nuevo pedido
			if($insNuevo > 0)
            {

				
				

            	$prefijo = "";
	            $folio = "";
            
    	        $cs = "SELECT prefijo FROM sys_sucursales WHERE id_sucursal = '{$user_sucursal}' ";
        	    if ($rs = mysql_query($cs)) {
            	    if ($dr = mysql_fetch_assoc($rs)) {
                	    $prefijo = $dr["prefijo"];
	                } mysql_free_result($rs);
    	        }
            
        	    $campo_segun_tipo = "folio_nv";
            
	            $cs = "SELECT IF(ISNULL(MAX(CAST(REPLACE({$campo_segun_tipo}, '{$prefijo}', '') AS SIGNED INT))), 1, MAX(CAST(REPLACE({$campo_segun_tipo}, '{$prefijo}', '') AS SIGNED INT))+1) AS folio " .
    	            "FROM ec_pedidos " .
        	        "WHERE REPLACE({$campo_segun_tipo}, '{$prefijo}', '') REGEXP ('[0-9]') " .
            	    "AND id_sucursal='{$user_sucursal}' " .
                	"AND id_pedido <> '{$id_pedido}' ";
	            #die ($cs);
    	        if ($rs = mysql_query($cs)) {
        	        if ($dr = mysql_fetch_assoc($rs)) {
            	        $folio = $prefijo . sprintf("%05d", $dr["folio"]);
                	    //die ("OK|FOLIO:{$folio}");
	                } mysql_free_result($rs);
    	        }
            
			
				if($beneficiario == 'CLIENTE')
				{
					$totSQL="-$restante";
				}
				else
				{
					$totSQL="+$restante";
				}
            
    	        $sql="INSERT INTO ec_pedidos(folio_nv, id_cliente, id_estatus, id_moneda, fecha_alta, id_direccion, id_razon_social, subtotal, iva, ieps, total, pagado, surtido, enviado, id_sucursal, id_usuario, fue_cot, facturado, id_tipo_envio, descuento, id_razon_factura)
	       	           SELECT
    	   	           '$folio',
       		           id_cliente,
       	    	       2,
       	        	   id_moneda,
	       	           NOW(),
    	   	           id_direccion,
       		           id_razon_social,
        	          (subtotal $totSQL),
           		       0,
           	    	   0,
	           	       (total $totSQL),
    	       	       0,
        	   	       surtido,
           		       enviado,
            	      id_sucursal,
            	      $user_id,
           	    	   fue_cot,
	                  facturado,
    	              id_tipo_envio,
        	          descuento,
            	      id_razon_factura
	                  FROM ec_pedidos
    	              WHERE id_pedido=$id_pedido";
                  
	            if(!mysql_query($sql))
    	            throw new Exception("No se pudo actualizar el pedido:\n\n $sql\n\n" . mysql_error());
            


        	    //echo $sql;
            
	            //echo "SI 04 \n";    
                
    	        $id_ped=mysql_insert_id();
            
            	//Insertamos los pagos
        	    
	            $sql="INSERT INTO ec_pedido_pagos
   		               SELECT
        	           NULL,
    	              $id_ped,
	                  id_tipo_pago,
    	              NOW(),
        	          NOW(),
            	      monto,
                	  referencia,
	                  id_moneda,
   		               tipo_cambio,
   	    	           id_nota_credito,
	                  id_cxc,
					  0
    	              FROM ec_pedido_pagos
            	      WHERE id_pedido=$id_pedido"; 
                  
                  
	             if(!mysql_query($sql))
    	            throw new Exception("No se pudo actualizar el pedido:\n\n $sql\n\n" . mysql_error());
                
                
        	    //Insertamos los productos
	            $sql="SELECT
    	              id_pedido_detalle,
        	          id_producto,
	                  cantidad AS cant,
    	              precio AS prec,
        	          monto AS mont
            	      FROM ec_pedidos_detalle
                	  WHERE id_pedido=$id_pedido";
                  
	            //echo $sql;                  
                  



    	        $res = mysql_query($sql);
            
        	    if(!$res)
            	    throw new Exception("No se pudo actualizar el pedido:\n\n $sql\n\n" . mysql_error());
            
	            $num=mysql_num_rows($res);
            
            
        	    //echo "SI 05 - $num\n";
				$totreal=0;
    	        
	            for($i=0;$i<$num;$i++)
    	        {
        	        $row=mysql_fetch_assoc($res);
                
            	    extract($row); 
                
	                //Buscamos si existe
    	            for($j=0;$j<$numDet;$j++)
        	        {
            	        if($idDetalle[$j] == $id_pedido_detalle)
                	    {
                    	    $cant-=$cantidad[$j];                              
	                    }    
    	            }
                
        	        if($cant > 0)
            	    {
                
	                    $sql="INSERT INTO ec_pedidos_detalle(id_pedido, id_producto, cantidad, precio, monto, iva, ieps, cantidad_surtida)
                                                  VALUES($id_ped, $id_producto, $cant, $prec, ".($prec*$cant).", 0, 0, $cant)";
                                                  
    	                //echo $sql;
						$totreal+=$prec*$cant;
                                                                      
        	            if(!mysql_query($sql))
            	            throw new Exception("No se pudo actualizar el pedido:\n\n $sql\n\n" . mysql_error());
	                }                    
                                                                                         
    	        }



				


				//echo "SI 06 - $numDet\n";

	            //Insertamos los nuevo
            
    	        for($i=0;$i<$numDet;$i++)
        	    {
            	    if($idDetalle[$i] == 'NO')
                	{
	                    $sql="INSERT INTO ec_pedidos_detalle(id_pedido, id_producto, cantidad, precio, monto, iva, ieps, cantidad_surtida)
    	                                              VALUES($id_ped, $idProducto[$i], $cantidad[$i], $precio[$i], $monto[$i], 0, 0, $cantidad[$i])";
                                                  
        	            //echo $sql; 

                        $totreal+=$monto[$i];                                                 
                                                  
            	        if(!mysql_query($sql))
                	        throw new Exception("No se pudo actualizar el pedido:\n\n $sql\n\n" . mysql_error());                                 
	                }    
    	        }
            
                //Actualizamos el total real
                $sql="UPDATE ec_pedidos SET total=$totreal-descuento, subtotal=$totreal WHERE id_pedido=$id_ped";
                if(!mysql_query($sql))
                    throw new Exception("No se pudo actualizar el pedido:\n\n $sql\n\n" . mysql_error());

            
   		         //Insertamos movimiento de salida
                    
        	     $sql="SELECT
            	      id_producto,
                	  cantidad
	                  FROM ec_pedidos_detalle
    	              WHERE id_pedido=$id_ped";
                  
        	    $res=mysql_query($sql);
	            if(!$res)
    	            throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
            
        	    $num=mysql_num_rows($res);
            
	            //echo "SI 07 - $num\n";
            
  	    	    for($i=0;$i<$num;$i++)
    	        {
                    
	                $row=mysql_fetch_row($res);
                
    	            //Buscamos el almacen
        	        $sql="SELECT
            	          a.id_almacen,
                	      a.prioridad,
	                      (
                        SELECT
                        SUM(d.cantidad_surtida*tm.afecta)
                        FROM ec_movimiento_detalle d
                        JOIN ec_movimiento_almacen aa ON aa.id_movimiento_almacen = d.id_movimiento
                        JOIN ec_tipos_movimiento tm ON tm.id_tipo_movimiento = aa.id_tipo_movimiento
                        WHERE a.id_almacen = aa.id_almacen
                        AND d.id_producto=$row[0]
                      )
                      FROM ec_almacen a
                      WHERE a.id_sucursal=$user_sucursal
                      AND a.es_almacen=1
                      AND a.id_almacen <> -1 
                      ORDER BY prioridad";
                      
                $re=mysql_query($sql);
                if(!$re)
                    throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                
                $nu=mysql_num_rows($re);                    
                    
                $canSur=$row[1];                  
                
                for($j=0;$j<$nu;$j++)
                {
                        
                    $ro=mysql_fetch_row($re);
                    
                    //print_r($row);
                    
                    if($i == 0)
                        $almacenPri=$ro[0];
                    
                    if($ro[2] > 0)
                    {
                        $sql="INSERT INTO ec_movimiento_almacen(id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, id_orden_compra, lote, id_maquila, id_transferencia, id_almacen)
                                                         VALUES(2, $user_id, $user_sucursal, NOW(), NOW(), '', {$id_ped}, -1, '', -1, -1, $ro[0])";
                    
                        //echo $sql;
                    
                        if (!mysql_query($sql)) throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                    
                        $id_mov=mysql_insert_id();
                        
                        if($canSur > $ro[2])
                        {
                            $can=$ro[2];
                            $canSur-=$ro[2];
                        }
                        else
                        {
                            $can=$canSur;
                            $canSur=0;
                        }
                        
                        $sql="INSERT INTO ec_movimiento_detalle(id_movimiento, id_producto, cantidad, cantidad_surtida, id_pedido_detalle, id_oc_detalle)
                                                         VALUES($id_mov, {$row[0]}, $can, $can, -1, -1)";
                                                         
                        //echo $sql;                                                         
                        if (!mysql_query($sql)) throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                        
                    }
                    else
                    {
                        continue;
                    }    
                        
                    if($canSur <= 0)
                    {
                        $j=$nu;
                        break;                        
                    }    
                }

                //die("NO");

                 //Insertamos el resto en el alamcen primario
                if($canSur > 0)
                {
                    
                    if(!isset($almacenPri))
                        $almacenPri=-1;
                    
                    $sql="INSERT INTO ec_movimiento_almacen(id_tipo_movimiento, id_usuario, id_sucursal, fecha, hora, observaciones, id_pedido, id_orden_compra, lote, id_maquila, id_transferencia, id_almacen)
                                                             VALUES(2, $user_id, $user_sucursal, NOW(), NOW(), '', {$id_ped}, -1, '', -1, -1, $almacenPri)";
                        
                    if (!mysql_query($sql)) throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                        
                    $id_mov=mysql_insert_id();
                    
                    $sql="INSERT INTO ec_movimiento_detalle(id_movimiento, id_producto, cantidad, cantidad_surtida, id_pedido_detalle, id_oc_detalle)
                                                             VALUES($id_mov, {$row[0]}, $canSur, $canSur, -1, -1)";
                    if (!mysql_query($sql)) throw new Exception("Imposible almacenar registro (detalle de pedido).\n\n$sql\n\n" . mysql_error());
                }

                //throw new Exception("Final: $id_ped|$idDev");
				
				
				
            } 
			
			
			//throw new Exception("Imposible almacenar registro (Actualizacion de estatus).\n\n$sql\n\n" . mysql_error());
			
			//Actualizamos los pagos de la devolucion
				$sql="	SELECT
						p.total,
						(SELECT SUM(monto) FROM ec_devolucion_pagos WHERE id_devolucion=d.id_devolucion),
						(SELECT SUM(monto) FROM ec_pedido_pagos WHERE id_pedido=p.id_pedido)
						FROM ec_devolucion d
						JOIN ec_pedidos p ON d.id_pedido = p.id_pedido
						WHERE p.id_pedido=$id_pedido
						";
						
						
						
				$rap=mysql_query($sql);
				if(!$rap)
					throw new Exception("Imposible almacenar registro (Actualizacion de estatus).\n\n$sql\n\n" . mysql_error());
					
				$roap=mysql_fetch_row($rap);
				
				if($roap[1] == '')
					$roap[1]=0;
					
				if($roap[2] == '')
					$roap[2]=0;	
					
					
				//echo $roap[0]." - ".$roap[1]." - ".$roap[2];	
					
				//throw new Exception("Imposible almacenar registro (Actualizacion de estatus).\n\n$sql\n\n" . mysql_error());	

				if($roap[0] > $roap[1])
				{
					$sql="INSERT INTO ec_devolucion_pagos(id_devolucion, id_tipo_pago, monto, referencia ,fecha, hora)
												   VALUES($idDev, 1, ".($roap[0]-$roap[1]).", '', NOW(), NOW())";
												   
					$rqp=mysql_query($sql);
					if(!$rqp)
					{
						throw new Exception("Imposible almacenar registro (Actualizacion de estatus).\n\n$sql\n\n" . mysql_error());
					}
				}
				
				if($roap[0] > $roap[2])
				{
					$sql="INSERT INTO ec_pedido_pagos(id_pedido, id_tipo_pago, fecha, hora, monto, referencia, id_moneda, tipo_cambio, id_nota_credito, id_cxc, exportado)
												   VALUES($id_pedido, 1, NOW(), NOW(), ".($roap[0]-$roap[2]).", '', 1, 1, -1, -1, 0)";
												   
					$rqp=mysql_query($sql);
					if(!$rqp)
					{
						throw new Exception("Imposible almacenar registro (Actualizacion de estatus).\n\n$sql\n\n" . mysql_error());
					}
					
					$sql="UPDATE ec_pedidos SET pagado=1 WHERE id_pedido=$id_pedido";
					
					$rqp=mysql_query($sql);
					if(!$rqp)
					{
						throw new Exception("Imposible almacenar registro (Actualizacion de estatus).\n\n$sql\n\n" . mysql_error());
					}
				}
				
				//Verificamos que no exista excente
				$sql="	SELECT
						p.total,
						(SELECT SUM(monto) FROM ec_pedido_pagos WHERE id_pedido=p.id_pedido)
						FROM ec_pedidos p
						WHERE p.id_pedido=$id_ped
						";
						
				$rap=mysql_query($sql);
				if(!$rap)
					throw new Exception("Imposible almacenar registro (Actualizacion de estatus).\n\n$sql\n\n" . mysql_error());
					
				$roap=mysql_fetch_row($rap);
				
				if($roap[1] == '')
					$roap[1]=0;
					
				//echo $roap[0]." - ".$roap[1];	
					
				//throw new Exception("Imposible almacenar registro (Actualizacion de estatus).\n\n$sql\n\n" . mysql_error());	

				if($roap[1] > $roap[0])
				{
					$sql="INSERT INTO ec_pedido_pagos(id_pedido, id_tipo_pago, fecha, hora, monto, referencia, id_moneda, tipo_cambio, id_nota_credito, id_cxc, exportado)
												   VALUES($id_ped, 1, NOW(), NOW(), ".($roap[0]-$roap[1]).", '', 1, 1, -1, -1, 0)";
												   
					$rqp=mysql_query($sql);
					if(!$rqp)
					{
						throw new Exception("Imposible almacenar registro (Actualizacion de estatus).\n\n$sql\n\n" . mysql_error());
					}
					
					$sql="UPDATE ec_pedidos SET pagado=1 WHERE id_pedido=$id_ped";
					
					$rqp=mysql_query($sql);
					if(!$rqp)
					{
						throw new Exception("Imposible almacenar registro (Actualizacion de estatus).\n\n$sql\n\n" . mysql_error());
					}
				}
			}
			else
			{
				
			}
            
        }
        
        //die("muajajaja");
    
        mysql_query("COMMIT");
        //mysql_query("ROLLBACK");
        
        echo "exito|$id_ped|$idDev";
        
    }
    catch (Exception $e)
    {
        echo "Error: " . $e->getMessage();
        mysql_query("ROLLBACK");
        mysql_close();
        exit ();
    } 

?>