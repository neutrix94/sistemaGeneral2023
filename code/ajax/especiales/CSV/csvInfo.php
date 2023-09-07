<?php
	include("../../../../conectMin.php");

	//php_track_vars;

	extract($_GET);
	extract($_POST);
	
 //------------- CCONSULTA A BD PARA TRAER DATOS ASOCIADOS CON CONTENIDO ---------------//   
    if($fechas[2] != 3)
    {    
       $query = "SELECT 
				pp.id_pedido AS ID,
				fecha AS Fecha,
				hora AS Hora,
				SUM(monto) AS Monto,
                p.folio_nv,
                IF(pagado = 1,'Pagado','Sin pagar'),
                c.nombre,
                tp.nombre
				FROM ec_pedido_pagos pp
				JOIN ec_tipos_pago tp ON pp.id_tipo_pago = tp.id_tipo_pago
                JOIN ec_pedidos p ON  pp.id_pedido=p.id_pedido
                JOIN ec_clientes c ON p.id_cliente = c.id_cliente
				WHERE fecha >= '$fechas[0]'
				AND fecha <= '$fechas[1]'
                AND pp.id_tipo_pago = $fechas[2]
                AND  p.id_sucursal=$user_sucursal/*implementado por Oscar 15.11.2018 para solo genear listado de ventas de sucursal*/
                GROUP BY pp.id_pedido";
    }
    else
    {
        $query = "SELECT 
                pp.id_pedido AS ID,
                fecha AS Fecha,
                hora AS Hora,
                SUM(monto) AS Monto,
                p.folio_nv,
                IF(pagado = 1,'Pagado','Sin pagar'),
                c.nombre,
                tp.nombre
                FROM ec_pedido_pagos pp
                JOIN ec_tipos_pago tp ON pp.id_tipo_pago = tp.id_tipo_pago
                JOIN ec_pedidos p ON  pp.id_pedido=p.id_pedido
                JOIN ec_clientes c ON p.id_cliente = c.id_cliente
                WHERE fecha >= '$fechas[0]'
                AND fecha <= '$fechas[1]'
                AND  p.id_sucursal=$user_sucursal/*implementado por Oscar 15.11.2018 para solo genear listado de ventas de sucursal*/         
                GROUP BY pp.id_pedido";
    }    
                    
        $result   = mysql_query($query) or die (mysql_error());
        //echo $query;
        while($fila=mysql_fetch_row($result))
        {
            $data[] = array(
                            'id'      =>  $fila[0],
                            'fecha'   =>  $fila[1],
                            'hora'    =>  $fila[2],
                            'monto'   =>  $fila[3],
                            'folio'   =>  $fila[4],
                            'estatus' =>  $fila[5],
                            'cliente' =>  $fila[6],
                            'exportado' =>  $fila[7]  
                        );

        }

        echo json_encode($data);
 

?>

