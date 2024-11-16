<?php
	/*if( isset( $_GET['fl_return'] ) ){
		include( '../../../config.inc.php' );
		include( '../../../conectMin.php' );
		include( '../../../conexionMysqli.php' );

		$action = $_GET['fl_return'];
		$SaleReturn = new SaleReturn( $link, $sucursal_id, $user_id );
		switch ( $action ) {
			case 'value':
			
			break;
			
			default:
				# code...
			break;
		}
	}*/
//echo 'here';

	class SaleReturn{

	//conexion	
		private $link;
	//variables del sistema
		private $system_type;
		private $store_id;
		private $teller_id;//id de cajero
		private $teller_session_id;//id sesion de caja
		private $user;
		private $principal_warehouse;
		private $external_warehouse;
		private $store_return_prefix;

	//variables de la nota de venta
		private $was_payed;
		private $internal_counter = 0;
		private $internal_products = array();
		private $external_counter = 0;
		private $external_products = array();
		private $sale_amount;

		private $products_to_return;

		private $auxDesc;//descuento
		private $porcDesc;//porcentaje de descuento
		private $sale_id;

	//variables de la devolucion
		private $internal_return_id =-1;
		private $external_return_id =-1;
		private $internal_return_amount = 0;
		private $external_return_amount = 0;
		private $total_abonado = 0;

	//variables de los movimientos de almacen
	    private $internal_return_movement_id = -1;
	    private $external_return_movement_id = -1;

		function __construct( $connection, $store_id, $user ){
			$this->link = $connection;
			$this->store_id = $store_id;
			$this->user = $user;
			$this->getConfig();
		}

		public function getConfig(){
			$sql="SELECT 
		            IF(multicajero=1,0,
		                (SELECT 
		                    id_cajero 
		                FROM ec_sesion_caja 
		                WHERE id_sucursal = {$this->store_id} 
		                AND fecha=DATE_FORMAT(now(),'%Y-%m-%d') 
		                AND hora_fin='00:00:00') 
		            ), 
		        /*implementacion Oscar 2023 para obtener el id de sesion de caja*/
		            IF(multicajero=1,0,
		                (SELECT 
		                    id_sesion_caja 
		                FROM ec_sesion_caja 
		                WHERE id_sucursal = {$this->store_id} 
		                AND fecha=DATE_FORMAT(now(),'%Y-%m-%d') 
		                AND hora_fin='00:00:00') 
		            )
		    FROM ec_configuracion_sucursal 
		    WHERE id_sucursal = {$this->store_id}";
		   
			//echo $sql;
		    $eje = $this->link->query( $sql )or die( "Error al consultar si la sucursal es multicajero : {$this->link->error}" );
		    $r_c = $eje->fetch_row();
		    $this->teller_id = $r_c[0];
		    $this->teller_session_id = $r_c[1];

		    $sql_cons="SELECT id_sucursal FROM sys_sucursales WHERE acceso=1";
		    $eje_cons = $this->link->query( $sql_cons )or die( "Error al consultar el tipo de sistema : {$this->link->error}" );
		    $res_cons =  $eje_cons->fetch_row();
		    $this->system_type = $res_cons[0];

		//almacen principal de la sucursal 
		    $sql="SELECT id_almacen FROM ec_almacen WHERE es_almacen=1 AND id_sucursal={$this->store_id}";
		    $eje = $this->link->query($sql) or die( "Error al consultar almacén principal : {$this->link->error}" );
		    $alm = $eje->fetch_row();
		    $this->principal_warehouse = $alm[0];//almacén principal de la sucursal

		//almacen externo de la sucursal 
		    $sql="SELECT almacen_externo, 
						CONCAT('DEV',prefijo)  
				FROM sys_sucursales 
				WHERE id_sucursal = {$this->store_id}";
		    $eje = $this->link->query($sql)or die( "Error al consultar almacén principal : {$this->link->error}" );
		    $alm = $eje->fetch_row();
		    $this->external_warehouse = $alm[0];//almacén principal de la sucursal
		    $this->store_return_prefix = $alm[1];
		}

		public function getProductsToReturnSinceValidation( $ticket_id ){
		//
			$this->sale_id = $ticket_id;
			$sql = "SELECT 
						pd.id_pedido_detalle AS sale_detail_id,
						p.id_productos AS product_id,
						p.es_maquilado AS is_maquiled,
						SUM( IF( pvu.id_proveedor_producto IS NULL, pvu.piezas_devueltas, 0 ) ) AS return_quantity,
						sp.es_externo AS is_external
					FROM ec_pedidos_detalle pd
					LEFT JOIN ec_productos p
					ON p.id_productos = pd.id_producto
					LEFT JOIN sys_sucursales_producto sp
					ON sp.id_producto = pd.id_producto
					AND sp.id_sucursal = {$this->store_id}
					LEFT JOIN ec_pedidos_validacion_usuarios pvu
					ON pvu.id_pedido_detalle = pd.id_pedido_detalle
					WHERE pd.id_pedido = {$ticket_id}
					AND pvu.id_proveedor_producto IS NULL/*habilitado por Oscar 3 Noviembre 2022*/
					GROUP BY pd.id_pedido_detalle";
		//die  ( 'error|' . $sql );
			$stm = $this->link->query( $sql ) or die( "Error al consultar el detalle de la venta por devolver : {$this->link->error}" );
			$resp = array();
			while ( $row = $stm->fetch_assoc() ) {
				if( $row['is_external'] == 0 ){
					array_push($this->internal_products, $row );
					//$this->internal_products ++;
				}else{
					array_push($this->external_products, $row );
					//$this->external_products ++;
				}
			}
			$this->internal_counter = sizeof( $this->internal_products );
//echo "c1 : {$this->internal_counter}";
			$this->external_counter = sizeof( $this->external_products );
//echo "c2 : {$this->external_counter}";
			return $resp;
		}

		public function setTicketData( $ticket_id ){
		//verifica si el pedido esta pagado
		    $sql="SELECT pagado FROM ec_pedidos WHERE id_pedido = {$ticket_id}";
		    $eje = $this->link->query($sql)or die("Error al consultar si el pedido ya está pagado : {$this->link->error}");
		    $pg = $eje->fetch_row();
		    $this->was_payed = $pg[0];
		}

		public function getSaleInfo(  ){
		//verificamos si hay productos externos
		    $sql="SELECT 
		                SUM(IF(pd.id_pedido_detalle IS NULL,0,IF(pd.es_externo=0,1,0))) AS internos,
		                SUM(IF(pd.id_pedido_detalle IS NULL,0,IF(pd.es_externo=1,1,0))) AS externos,
		                ped.pagado
		            FROM ec_pedidos_detalle pd 
		            LEFT JOIN ec_pedidos ped ON pd.id_pedido=ped.id_pedido
		            WHERE ped.id_pedido='$idp'
		            GROUP BY pd.id_pedido_detalle";
		//si ya esta pagado el pedido           
		    if($esta_pagado==1){
		        $sql.=" AND pd.id_producto IN(";       
		        for($i=0;$i<$nitems;$i++){
		            $sql.=$_GET["idp{$i}"];
		            if($i<$nitems-1){
		                $sql.=",";//concatenamos coma
		            }else{
		                $sql.=")";//concatenamos cierre de paréntesis
		            }
		        }//fin de for $i
		    }
		    $eje = $this->link->query( $sql )or die("Error al consultar si hay productos externos por devolver : {$this->link->error}");
		    $dats = $eje->fetch_row();
		    $this->internal_counter = $dats[0];
		    $this->external_counter = $dats[1];
		    $this->was_payed = $dats[2];
		}

		public function insertReturnHeader( $ticket_id ){
		    for($i=0;$i<=1;$i++){//(da dos vueltas  solamente)
		        if( ( ($i == 0 && $this->internal_counter > 0) || ($i == 1 && $this->external_counter > 0) ) 
		        	|| $this->was_payed == 0 ){
		            $return_folio = $this->getNewFolio();
		            $insD="INSERT INTO ec_devolucion(id_devolucion,id_usuario,id_sucursal,fecha,hora,id_pedido,folio,es_externo,observaciones,tipo_sistema,id_status_agrupacion)
		                    VALUES(NULL,'{$this->user}','{$this->store_id}',NOW(),NOW(),'{$ticket_id}','{$return_folio}',";

		            if($i==0){
		                $insD.="'0',";
		            }else if($i==1){
		                $insD.="'1',";
		            }
		            $insD.="'','".$this->system_type."',-1)";
//echo $insD;
		            $dev = $this->link->query( $insD ) or die( "Error al insertar encabezado de la devolución " . ( $i == 0 ? 'interna' : 'externa' ) . " : {$this->link->error}" );

		        //guardamos id(s) de la(s) cabecera(s)
		            if($i==0){
		                $this->internal_return_id = $this->link->insert_id;
//echo "\ninserted_id : {$this->internal_return_id}";
		            }else if($i==1){
		                $this->external_return_id = $this->link->insert_id;
//echo "\ninserted_id : {$this->external_return_id}";
		            }
		            //$id_dev=mysql_insert_id();//guardamos id de devolucion
		        }
		    }//fin de for $i
		    return $this->link->insert_id;
		}
		public function getNewFolio(){
		    $sql_fol="SELECT
		                CONCAT('{$this->store_return_prefix}',
		                    IF(
		                        ISNULL(MAX(CAST(REPLACE(folio, '{$this->store_return_prefix}', '') AS SIGNED INT))),
		                        1,
		                        MAX(CAST(REPLACE(folio, '{$this->store_return_prefix}', '') AS SIGNED INT))+1
		                    )
		                ) AS folio
		                FROM ec_devolucion
		                WHERE REPLACE(folio, '{$this->store_return_prefix}', '') REGEXP ('[0-9]')
		                AND id_sucursal = '{$this->store_id}'";
		    $eje_fol = $this->link->query( $sql_fol )or die("Error al generar el folio de la devolución : {$this->link->error}" );
		    $row_fol = $eje_fol->fetch_assoc();
		    $new_folio = $row_fol['folio'];
			return $new_folio;
		}

		public function insertReturnMovementHeader(  ){
			for($i=0;$i<=1;$i++){
		        if( ( $i == 0 && $this->internal_counter > 0 ) || ( $i == 1 && $this->external_counter > 0 ) ){
	//&& $this->was_payed == 1
		            /*$insMov="INSERT INTO ec_movimiento_almacen ( id_movimiento_almacen, id_tipo_movimiento, id_usuario, 
		            	id_sucursal, fecha, hora, observaciones, id_pedido, id_orden_compra, lote, id_maquila, id_transferencia, 
		            	id_almacen, status_agrupacion, ultima_sincronizacion, ultima_actualizacion )
		            VALUES(null,'12','{$this->user}','{$this->store_id}',now(),now(),'DEVOLUCION',-1,-1,'',-1,-1,";
		            $insMov.=",-1,null,now())";*/
//echo "inserta movimiento : " . $insMov;
					$almacen_id = 0;
		            if($i==0){
		                $almacen_id = $this->principal_warehouse;
		            }else if($i==1){
		                $almacen_id = $this->external_warehouse;
		            }
           			$insMov = "CALL spMovimientoAlmacen_inserta ( {$this->user}, 'DEVOLUCION', {$this->store_id}, {$almacen_id}, 12, -1, -1, -1, -1, 14, NULL )";
		            $eje = $this->link->query( $insMov )or die( "error|Error al insertar el encabezado de movimiento de almacén con entrada por devolución : {$this->link->error} {$insMov}");
		            if($i==0){
		                /*$sql = "SELECT LAST_INSERT_ID() AS last_id";
		                $eje_id = $this->link->query( $sql )or die( "error|Error al recuperar id mov alm int: {$this->link->error}");
        				$row_id = $eje_id->fetch_assoc();*/
						$ma_stm = $this->link->query( "SELECT max( id_movimiento_almacen ) AS id_movimiento_almacen FROM ec_movimiento_almacen" ) or die( "Error al recuperar id ma insertado : " . mysql_error() );
						$id_mov = $ma_stm->fetch_assoc();//mysql_fetch_assoc( $ma_stm );
						//$id_mov = $id_mov['id_movimiento_almacen'];
		                $this->internal_return_movement_id = $id_mov['id_movimiento_almacen'];//id asignado al movimiento de devolución
//echo "here 1 : {$this->internal_return_movement_id}";	           
		            }
		            if($i==1){
		                /*$sql = "SELECT LAST_INSERT_ID() AS last_id";
		                $eje_id = $this->link->query( $sql )or die( "error|Error al recuperar id mov alm ext: {$this->link->error}");
        				$row_id = $eje_id->fetch_assoc();*/
						$ma_stm = $this->link->query( "SELECT max( id_movimiento_almacen ) AS id_movimiento_almacen FROM ec_movimiento_almacen" ) or die( "Error al recuperar id ma insertado : " . mysql_error() );
						$id_mov = $ma_stm->fetch_assoc();//mysql_fetch_assoc( $ma_stm );
						//$id_mov = $id_mov['id_movimiento_almacen'];
		                $this->external_return_movement_id = $id_mov['id_movimiento_almacen'];//$row_id['last_id']id asignado al movimiento de devolución
//echo "here 2 : {$this->external_return_movement_id}";	           
		            }
		        }
    		}//fin de for $i

		}
	
		public function insertReturnDetail(){
		//echo "Este es el valor de pagado : {$this->was_payed}";

			$sql = "SELECT pagado AS was_payed FROM ec_pedidos WHERE id_pedido = {$this->sale_id}";
			$stm = $this->link->query( $sql ) or die( "Error al verificar si la venta esta pagada : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			$this->was_payed = $row['was_payed'];

			if( $this->was_payed == 0 ){
				$sql = "SELECT pagado FROM ec_pedidos WHERE id_pedido = {$this->sale_id}";
				$stm = $this->link->query( $sql ) or die( "Error al consultar si el pedido esta pagado : {$this->link->error}" );
				$row = $stm->fetch_row( );
				if( $row[0] == 1 ){
					die( "La nota esta pagada y no debe de entrar en esta condicion." );
				} 
				/*INSERT INTO ec_movimiento_detalle ( id_movimiento_almacen_detalle, id_movimiento, id_producto, 
            			cantidad, cantidad_surtida, id_pedido_detalle, id_oc_detalle, id_proveedor_producto, 
            			id_equivalente, sincronizar )*/
            	$sql ="SELECT 
                            IF( pd.es_externo = 1,
                            	{$this->external_return_movement_id},
                            	{$this->internal_return_movement_id}
                           	) AS movement_header_id,
                            IF(p.id_producto_ordigen IS NULL,pd.id_producto,p.id_producto_ordigen) AS product_id,
                            IF(p.id_producto_ordigen IS NULL,pd.cantidad,(pd.cantidad*p.cantidad)) AS quantity
                        FROM ec_pedidos_detalle pd
                        LEFT JOIN ec_productos_detalle p 
                        ON p.id_producto=pd.id_producto
                        WHERE pd.id_pedido = {$this->sale_id}";
            	$stm = $this->link->query( $sql ) or die( "Error al consultar todo el detalle de movimiento devolución : {$this->link->error}" );
				while( $dev_row = $stm->fetch_assoc() ){
					$sql = "CALL spMovimientoAlmacenDetalle_inserta ( {$dev_row['movement_header_id']}, {$dev_row['product_id']}, {$dev_row['quantity']}, 
						{$dev_row['quantity']}, -1, -1, NULL, 14, NULL )";
					$exc_procedure = $this->link->query( $sql ) or die( "Error al mandar llamar procedure spMovimientoAlmacenDetalle_inserta : {$this->link->error} {$sql}" );
				}
		//echo $sql;	
			}else{
			//die( "Esta pagado" );
				$sql = "SELECT pagado FROM ec_pedidos WHERE id_pedido = {$this->sale_id}";
				$stm = $this->link->query( $sql ) or die( "Error al consultar si el pedido esta pagado : {$this->link->error}" );
				$row = $stm->fetch_row( );
				if( $row[0] == 0 ){
					die( "La nota esta pagada y no debe de entrar en esta condicion." );
				}
			}
			//die( 'Deter' );
			foreach ($this->internal_products as $key => $product ) { //actualizamos el detalle del pedido
				//extraemos datos del detalle del pedido
		    	$sql = "SELECT pd.cantidad,
		                        pd.id_pedido_detalle,
		                        pd.es_externo,
		                        ROUND(((pd.monto-pd.descuento)/pd.cantidad)-IF(pd.descuento>0,0,(pd.precio)*(IF(pe.descuento=0,0,(pe.descuento*100/pe.subtotal))/100)),6)*{$product['return_quantity']}
		                        FROM ec_pedidos_detalle pd
		                        LEFT JOIN ec_pedidos pe ON pd.id_pedido=pe.id_pedido 
		                        WHERE pd.id_pedido_detalle ='{$product['sale_detail_id']}'
		                        GROUP BY pd.id_pedido_detalle";
		    //die( $sql );
		        $eje1 = $this->link->query( $sql )or die("Error al consultar la cantidad de productos a devolver 1 : {$sql} {$this->link->error} ");
		    	$r = $eje1->fetch_row();
	    		
	    		$sql2="UPDATE ec_pedidos_detalle SET 
	                    cantidad = ( cantidad - {$product['return_quantity']} ),
	                    monto = cantidad*precio,
	                    modificado = 1
	    		  		WHERE id_pedido_detalle = '{$product['sale_detail_id']}'";//-{$_GET["can{$i}"]}//descuento = (IF(descuento=0,0,$des[0]*cantidad))
	    		$eje2 = $this->link->query( $sql2 ) or die("Error al actualizar el detalle de pedido : {$this->link->error} ");

	    //comprueba si el producto es maquilado
	            $sql = "SELECT 
	            			id_producto_ordigen,
	            			cantidad 
	            		FROM ec_productos_detalle 
	            		WHERE id_producto='{$product['product_id']}'"; 
	            $eje_maq = $this->link->query($sql) or die("Error al consultar el origen de la maquila : {$this->link->error}");
	            if( $eje_maq->num_rows > 0  ){
	                $r_maq = $eje_maq->fetch_row();
	                $product['product_id']=$r_maq[0];
	                $product['return_quantity'] = ( $r_maq[1] * $product['return_quantity'] );
	            }//inserta el detalle de la devolucion
		    	
		    	$ins_det="INSERT INTO ec_devolucion_detalle(id_devolucion_detalle,id_devolucion,id_producto,cantidad, id_pedido_detalle)
		                    SELECT NULL,IF( {$product['is_external']} = 0, {$this->internal_return_id}, {$this->external_return_id} ),
		                    '{$product['product_id']}','{$product['return_quantity']}', '{$product['sale_detail_id']}'";////VALUES(NULL,'$id_dev','{$_GET["idp{$i}"]}','{$_GET["can{$i}"]}')
		    	$insDD = $this->link->query( $ins_det ) or die("Error al insertar detalle de la devolución : {$ins_det} {$this->link->error} ");

/*implementacion Oscar 2023 para detonar devolucion de exhibicion*/
		        $sql = "UPDATE ec_temporal_exhibicion te 
		                INNER JOIN ec_temporal_exhibicion_proveedor_producto tepp
		                ON te.id_temporal_exhibicion = tepp.id_temporal_exhibicion
		                SET te.tiene_devolucion = 1, tepp.tiene_devolucion = 1
		                WHERE te.id_pedido = {$this->sale_id}
		                AND tepp.id_producto = {$product['product_id']}";
		        $stm_aux = $this->link->query( $sql ) or die( "Error al marcar exhibicion con devolucion : {$this->link->error}" );
/*fin de cambio Oscar 2023*/

		    //si la nota esta pagada insertamos el detalle del movimiento por devolución de los productos devueltos
				if( $this->was_payed == 1 ){
		            /*$ins_mov_det="INSERT INTO ec_movimiento_detalle ( id_movimiento_almacen_detalle, id_movimiento, id_producto, 
            			cantidad, cantidad_surtida, id_pedido_detalle, id_oc_detalle, id_proveedor_producto, 
            			id_equivalente, sincronizar ) 
		            SELECT 
			            NULL,
			            IF( {$product['is_external']} = 0, {$this->internal_return_movement_id}, {$this->external_return_movement_id}),
			            '{$product['product_id']}',
			            '{$product['return_quantity']}',
			            '{$product['return_quantity']}',
			            -1,
			            -1,
			            NULL,
			            0,
			            0";*/
					$movement_header_id = ( $product['is_external'] == 0 ? $this->internal_return_movement_id : $this->external_return_movement_id );
					$ins_mov_det = "CALL spMovimientoAlmacenDetalle_inserta ( {$movement_header_id}, {$product['product_id']}, {$product['return_quantity']}, 
					{$product['return_quantity']}, -1, -1, NULL, 14, NULL )";
		            $eje = $this->link->query( $ins_mov_det ) or die( "Error al insertar el detalle de movimiento por devolución 1 : {$ins_mov_det} {$this->link->error} " );
				}//fin de si esta pagado

		    //suma los pagos
		        if( $product['is_external']== 0 ){//si es producto interno
		            $this->internal_return_amount += $r[3];
		   //echo "here_amount : { $this->internal_return_amount}";
		        }else if( $product['is_external']== 1 ){//si es producto externo
		            $this->external_return_amount += $r[3];
		        }
			}

			foreach ($this->external_products as $key => $product ) { //actualizamos el detalle del pedido
				//extraemos datos del detalle del pedido
		    	$sql = "SELECT 
		    				pd.cantidad,
	                        pd.id_pedido_detalle,
	                        pd.es_externo,
	                        ROUND(((pd.monto-pd.descuento)/pd.cantidad)-IF(pd.descuento>0,0,(pd.precio)*(IF(pe.descuento=0,0,(pe.descuento*100/pe.subtotal))/100)),6)*{$product['return_quantity']}
	                    FROM ec_pedidos_detalle pd
	                    LEFT JOIN ec_pedidos pe ON pd.id_pedido=pe.id_pedido 
	                    WHERE pd.id_pedido_detalle ='{$product['sale_detail_id']}'";
		        $eje1 = $this->link->query( $sql )or die("Error al consultar la cantidad de productos a devolver 2 : {$sql} {$this->link->error} ");
		    	$r = $eje1->fetch_row();
	    		
	    		$sql2="UPDATE ec_pedidos_detalle SET 
	                    cantidad = ( cantidad - {$product['return_quantity']} ),
	                    monto = cantidad*precio,

	                    modificado = 1
	    		  		WHERE id_pedido_detalle = '{$product['sale_detail_id']}'";//-{$_GET["can{$i}"]}//descuento = (IF(descuento=0,0,$des[0]*cantidad)),
	    		$eje2 = $this->link->query( $sql2 ) or die("Error al actualizar el detalle de pedido : {$this->link->error} ");

	    //comprueba si el producto es maquilado
	            $sql = "SELECT 
	            			id_producto_ordigen,
	            			cantidad 
	            		FROM ec_productos_detalle 
	            		WHERE id_producto='{$product['product_id']}'"; 
	            $eje_maq = $this->link->query($sql) or die("Error al consultar el origen de la maquila : {$this->link->error}");
	            if( $eje_maq->num_rows > 0  ){
	                $r_maq = $eje_maq->fetch_row();
	                $product['product_id']=$r_maq[0];
	                $product['return_quantity'] = ( $r_maq[1] * $product['return_quantity'] );
	            }//inserta el detalle de la devolucion
		    	
		    	$ins_det="INSERT INTO ec_devolucion_detalle(id_devolucion_detalle,id_devolucion,id_producto,cantidad, id_pedido_detalle)
		                    SELECT NULL,IF( {$product['is_external']} = 0, {$this->internal_return_id}, {$this->external_return_id} ),
		                    '{$product['product_id']}','{$product['return_quantity']}', '{$product['sale_detail_id']}'";////VALUES(NULL,'$id_dev','{$_GET["idp{$i}"]}','{$_GET["can{$i}"]}')
		    	$insDD = $this->link->query( $ins_det ) or die("Error al insertar detalle de la devolución : {$ins_det} {$this->link->error} ");
		    	
		    //si la nota esta pagada insertamos el detalle del movimiento por devolución de los productos devueltos
				if( $this->was_payed == 1 ){
		            /*$ins_mov_det="INSERT INTO ec_movimiento_detalle ( id_movimiento_almacen_detalle, id_movimiento, id_producto, 
            			cantidad, cantidad_surtida, id_pedido_detalle, id_oc_detalle, id_proveedor_producto, 
            			id_equivalente, sincronizar ) 
		            SELECT
			            NULL,
			            IF( {$product['is_external']} = 0, {$this->internal_return_movement_id}, {$this->external_return_movement_id}),
			            '{$product['product_id']}',
			            '{$product['return_quantity']}',
			            '{$product['return_quantity']}',
			            -1,
			            -1,
			            NULL,
			            0,
			            0";*/
					$movement_header_id = ( $product['is_external'] == 0 ? $this->internal_return_movement_id : $this->external_return_movement_id );
					$ins_mov_det = "CALL spMovimientoAlmacenDetalle_inserta ( {$movement_header_id}, {$product['product_id']}, {$product['return_quantity']}, 
					{$product['return_quantity']}, -1, -1, NULL, 14, NULL )";
		            $eje = $this->link->query( $ins_mov_det ) or die( "Error al insertar el detalle de movimiento por devolución 2 : {$this->link->error} " );
				}//fin de si esta pagado

		    //suma los pagos
		        if( $product['is_external']==0 ){//si es producto interno
		            $this->internal_return_amount += $r[3];
		        }else if( $product['is_external']==1 ){//si es producto externo
		            $this->external_return_amount += $r[3];
		        }
			}	
		}

		public function insertReturnPayment(){
	//insertamos el pago de la devolucion
			$this->total_abonado=0;
			if( $this->was_payed == 1 ){
			    /*for($i=0;$i<=1;$i++){
			        if( ( $i == 0 && $this->internal_counter > 0 ) || ( $i == 1 && $this->external_counter > 0 ) ){
			            $insPD="INSERT INTO ec_devolucion_pagos(id_devolucion_pago,id_devolucion,id_tipo_pago,monto,referencia,es_externo,fecha,hora,id_cajero, id_sesion_caja )
			    		  VALUES(NULL,";
			        //id de la devolucion
			            if($i==0){
			                $insPD .= $this->internal_return_id;
			            }else if( $i == 1 ){
			                $insPD .= $this->external_return_id;                    
			            }
			            $insPD .=",1,";
			        //monto de la devolucion
			            if($i == 0){
			                $insPD .= $this->internal_return_amount.",'',0";
			            }else if( $i == 1 ){
			                $insPD .= $this->external_return_amount.",'',1";           
			            }
			            $insPD .= ", NOW(), NOW(), {$this->teller_id}, {$this->teller_session_id} )";
			           
//echo $insPD;
			            $insert = $this->link->query( $insPD ) or die( "Error al insertar el pago de la devolución 1 : {$this->link->error}" );
			                           
			        }//fin de if si son validos
			    }//fin de for i*/

			    //externa
			    if( $this->external_return_amount > 0 ){
			        /*$sql = "INSERT INTO ec_devolucion_pagos (id_devolucion_pago,id_devolucion,id_tipo_pago,monto,referencia,es_externo,fecha,hora,id_cajero, id_sesion_caja )
			       	VALUES( NULL, {$this->external_return_id}, 1,{$datos_1[0]},'{$datos_1[0]}',1,now(),now(),{$this->teller_id}, {$this->teller_session_id} )";
			        $eje = $this->link->query($sql) or die("Error al insertar el pago de la devolución externa : {$sql} {$this->link->error}");*/
			    	$sql = "UPDATE ec_devolucion SET monto_devolucion = {$this->external_return_amount} WHERE id_devolucion = {$this->external_return_id}";
			        $eje = $this->link->query($sql) or die("Error al actualizar pago de cebecera devolución externa : {$sql} {$this->link->error}");
			    }
			//interna
			    if( $this->internal_return_amount > 0 ){
			        /*$sql="INSERT INTO ec_devolucion_pagos (id_devolucion_pago,id_devolucion,id_tipo_pago,monto,referencia,es_externo,fecha,hora,id_cajero, id_sesion_caja )
			        VALUES( NULL, {$this->internal_return_id},1,{$datos_1[1]},'{$datos_1[1]}',0,now(),now(), {$this->teller_id}, {$this->teller_session_id} )";
			        $eje = $this->link->query( $sql ) or die( "Error al insertar el pago de la devolución interna : {$sql} {$this->link->error}");*/
			    	$sql = "UPDATE ec_devolucion SET monto_devolucion = {$this->internal_return_amount} WHERE id_devolucion = {$this->internal_return_id}";
			        $eje = $this->link->query($sql) or die("Error al actualizar pago de cebecera devolución interna : {$sql} {$this->link->error}");         
			    }   
			    $this->total_abonado=$datos_1[2];  
			}else{
			    $sql = "SELECT 
			            SUM(IF(pp.es_externo=1,pp.monto,0))-IF(ax.devExternos IS NULL,0,ax.devExternos) as externos,
			            SUM(IF(pp.es_externo=0,pp.monto,0))-IF(ax.devInternos is null,0,ax.devInternos )as internos,
			            SUM(pp.monto)-IF(ax.totalDev is null,0,ax.totalDev) as total 
			        FROM(
			            SELECT 
			                {$this->sale_id} as id_pedido,
			                SUM(IF(dev.id_devolucion is null,0,IF(dp.es_externo=1,dp.monto,0))) as devExternos,
			                SUM(IF(dev.id_devolucion is null,0,IF(dp.es_externo=0,dp.monto,0))) as devInternos,
			                SUM(IF(dev.id_devolucion IS NULL,0,dp.monto)) as totalDev
			                FROM ec_devolucion dev
			                LEFT JOIN ec_devolucion_pagos dp ON dev.id_devolucion=dp.id_devolucion
			                WHERE dev.id_pedido = {$this->sale_id}
			            )ax
			        LEFT JOIN ec_pedido_pagos pp ON pp.id_pedido=ax.id_pedido
			        WHERE pp.id_pedido = {$this->sale_id}";
			// die($sql);
			    $eje = $this->link->query($sql) or die("Error al consultar pago de la devolución 2 : {$this->link->error} ");
			    
			    $datos_1 = $eje->fetch_row();
			//insertamos las devoluciones completas
			    //externa
			    if($datos_1[0]>0){
			        /*$sql = "INSERT INTO ec_devolucion_pagos (id_devolucion_pago,id_devolucion,id_tipo_pago,monto,referencia,es_externo,fecha,hora,id_cajero, id_sesion_caja )
			       	VALUES( NULL, {$this->external_return_id}, 1,{$datos_1[0]},'{$datos_1[0]}',1,now(),now(),{$this->teller_id}, {$this->teller_session_id} )";
			        $eje = $this->link->query($sql) or die("Error al insertar el pago de la devolución externa : {$sql} {$this->link->error}");*/
			    	$sql = "UPDATE ec_devolucion SET monto_devolucion = {$datos_1[0]} WHERE id_devolucion = {$this->external_return_id}";
			        $eje = $this->link->query($sql) or die("Error al actualizar pago de cebecera devolución externa : {$sql} {$this->link->error}");
			    }
			//interna
			    if($datos_1[1]>0){
			        /*$sql="INSERT INTO ec_devolucion_pagos (id_devolucion_pago,id_devolucion,id_tipo_pago,monto,referencia,es_externo,fecha,hora,id_cajero, id_sesion_caja )
			        VALUES( NULL, {$this->internal_return_id},1,{$datos_1[1]},'{$datos_1[1]}',0,now(),now(), {$this->teller_id}, {$this->teller_session_id} )";
			        $eje = $this->link->query( $sql ) or die( "Error al insertar el pago de la devolución interna : {$sql} {$this->link->error}");*/
			    	$sql = "UPDATE ec_devolucion SET monto_devolucion = {$datos_1[1]} WHERE id_devolucion = {$this->internal_return_id}";
			        $eje = $this->link->query($sql) or die("Error al actualizar pago de cebecera devolución interna : {$sql} {$this->link->error}");         
			    }   
			    $this->total_abonado=$datos_1[2];  
			//actualizamos los pagos para anularlos en los cálculos
			//    $sql="UPDATE ec_pedido_pagos SET referencia=monto WHERE id_pedido = {$this->sale_id}";
			//    $eje = $this->link->query( $sql ) or die( "Error al actualizar la referencia de los pagos : {$this->link->error}" );
			    
			}//fin de si no esta pagada la nota de venta
//echo '<br>hasta aqui termina el pago de devolución</br>';
		}


		public function finishReturn(){
			$sql = "SELECT 
						ROUND( 1 - (total/subtotal), 6 ) AS discount
					FROM ec_pedidos
					WHERE id_pedido = {$this->sale_id}";
			$stm = $this->link->query( $sql ) or die( "Error al consultar descuento del ticket : {$sql} {$this->link->error}" );
			$row = $stm->fetch_assoc();
			$porcDesc = $row['discount'];
			//Actualizamos el monto del pedio anterior y generamos el ticket...
			    $subTotal="SELECT 
			    			SUM( monto ),
			    			SUM( descuento ) 
			    		FROM ec_pedidos_detalle 
			    		WHERE id_pedido = '{$this->sale_id}'";//consultamos las sumas de los productos del pedido
			    $calc = $this->link->query($subTotal) or die("Error al calcular el nuevo monto del pedido : {$this->link->error}");
			    $subTotal = $calc->fetch_row();    
			//checamos si hay descuento
			    if($subTotal[1]==0){
			        $descFinal=$subTotal[0]*$porcDesc;
			        $auxz=$subTotal[0];
			        $subTotal[0]=$auxz;
			    }else{
			        $descFinal=$subTotal[1];
			    }
//url+='&extra=*es_apart='+es_apartado+'*id_ped='+$("#id_pedido_apartado").val();
		//recupera datos de la devolución
			$sql = "SELECT 
						p.id_pedido AS sale_id,
						SUM( devP.monto ) AS amount,
						IF( p.pagado = 1, 0, 1 ) AS is_not_payed,
					/*implementacion Oscar 2023 para que respete el precio de lista si es mayoreo*/
						IF( p.tipo_pedido = 0 , '', CONCAT( '&tv=1&aWRfcHJlY2lv=', p.tipo_pedido ) ) AS sale_type,
						p.descuento AS discount/*,
						p.id_devoluciones AS returns_ids*/
					FROM ec_pedidos p
					LEFT JOIN ec_devolucion dev
					ON dev.id_pedido = p.id_pedido
					LEFT JOIN ec_devolucion_pagos devP
					ON devP.id_devolucion = dev.id_devolucion
					WHERE p.id_pedido = {$this->sale_id}";
			$stm_url = $this->link->query( $sql ) or die( "Error al consultar detalles finales de la devolución : {$this->link->error}" );
			$row = $stm_url->fetch_assoc();
		//oscar 2023 para enviar datos de la devolucion
			$row['returns_ids'] = "{$this->internal_return_id}~{$this->external_return_id}";
	
			$extra = "&es_apart={$row['is_not_payed']}&id_ped={$row['sale_id']}&dsc={$row['discount']}"; 
			$extra .= "&id_dev=" . $row['returns_ids'] . $row['sale_type'];//implementacion Oscar 2023 para que respete el precio de lista si es mayoreo
			$row['amount'] = round( $this->internal_return_amount + $this->external_return_amount, 6 );
			$extra=str_replace("*", "&", $extra);
    		$url_recarga = '../../touch_desarrollo/index.php?scr=nueva-venta&s_f_c=' . $row['amount'];
    		$url_recarga .= $extra . "&abonado=".$this->total_abonado;

    		$url_db = '../touch_desarrollo/index.php?scr=nueva-venta&s_f_c=' . $row['amount'];
    		$url_db .= $extra . "&abonado=".$this->total_abonado;

		    $sql="UPDATE ec_devolucion SET observaciones='$url_db' WHERE id_pedido = {$this->sale_id}";
		    $eje = $this->link->query($sql)or die("Error al actualizar observaciones en las devoluciones : {$this->link->error}" );

		//actualizamos monto del pedido y marcamos que este fue modificado
		    $actPed="UPDATE ec_pedidos SET descuento = '{$descFinal}',subtotal='$subTotal[0]',total=($subTotal[0]-descuento),modificado=1 WHERE id_pedido = '{$this->sale_id}'";
		    $actualiza = $this->link->query( $actPed ) or die( "Error al actualizar cabecera de Pedido : {$this->link->error}" );
		   
/*implementacion Oscar 2023-12-19 para actualizar referencia de la nota de venta y a devolucion*/
	        $sql = "UPDATE ec_pedidos_referencia_devolucion 
	                    SET total_venta = ( total_venta - ( {$this->internal_return_amount} + {$this->external_return_amount} ) )
	                WHERE id_pedido = {$this->sale_id}";
	        $reference_stm = $this->link->query( $sql ) or die( "Error al actualizar la referencia de la devolucion : {$this->link->error}");
/*fin de cambio Oscar 2023-12-19*/

		    //if(mysql_query("COMMIT")){//autorizamos transacción
		       // if($es_completa==1){
		        //imprimimos el ticket de la devolución

/*deshabilitado por Oscar 2024-04-30
if( !include('imprimeDev.php') ){
die("Error al generar ticket de devolución");
}
 */
		//regresa el id de la devolución 
		    //return 'ok|'.$id_dev."|".$total_abonado."|".$url_recarga."&id_dev=".$id_dev_interna."~".$id_dev_externa;
			return $url_recarga;
		}
	}
?>