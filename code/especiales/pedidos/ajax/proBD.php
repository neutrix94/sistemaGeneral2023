<?php
	include("../../../../conectMin.php");
	extract($_POST);
/*Implementación Oscar 13.02.2019 para guardar la nota en la tabla de productos*/
	if($fl=='nota_producto'){
		$sql="UPDATE ec_productos SET observaciones='$txt' WHERE id_productos=$id";
		$eje=mysql_query($sql)or die("Error al insertar la norta en la tabla de productos!!!\n\n".mysql_error()."\n\n".$sql);
		die("ok");
	}
/*Fin de cambio Oscar 13.02.2019*/
	
	if($fl=='actualizar'){
		
		$datos=explode("|",$arr);

		mysql_query("BEGIN");//marcamos inicio de transacción
		$sql="DELETE FROM ec_oc_detalle WHERE id_orden_compra=$id_compra AND id_producto NOT IN(";
		for($i=0;$i<sizeof($datos)-1;$i++){
			$arr=explode("~",$datos[$i]);
			$sql.=$arr[0];
			if($i<sizeof($datos)-1){		
				$sql.=",";
			}
		}
		$sql.=")";
		$sql=str_replace(",)", ")", $sql);
		$sql=str_replace(" AND id_productos NOT IN(,)", "", $sql);
		$sql=str_replace(" AND id_productos NOT IN()", "", $sql);
		$eje=mysql_query($sql);
		if(!$eje){
			mysql_query("ROLLBACK");//cancelamos transacción 
			die("Error al eliminar detalle de orden de compra para reemplazarlos!!!\n\n".$sql."\n\n".mysql_error());
		}
	//reinsertamos el detalle de la orden de compra
		for($i=0;$i<sizeof($datos)-1;$i++){
			$arr=explode("~",$datos[$i]);
			$id_pr=$arr[0];
			$sql_remplaza="";
			//$sql_remplaza="INSERT INTO ec_oc_detalle VALUES(null,$id_compra,$id_pr,$arr[1],$arr[2],0,0,0,'$arr[3]')";//,'$arr[3]'
			$sql_remplaza="UPDATE ec_oc_detalle SET cantidad=$arr[1] WHERE id_orden_compra=$id_compra AND id_producto=$arr[0]";
			$eje1=mysql_query($sql_remplaza);
			if(!$eje1){
				$error=mysql_error();
				mysql_query("ROLLBACK");
				die("Error al actualizar el detalle de compra\n\n".$sql_remplaza."\n\n".$error);
			}
		}//fin de for j
		mysql_query( "COMMIT" );
		die( 'ok|' );
	}
//insertamos nuevo proveedor
	if($fl==1){
		mysql_query( "BEGIN" );//marca inicio de transacción
	/*eliminamos los proveedores existentes para remplazarlos
		$sql="DELETE FROM ec_proveedor_producto WHERE id_producto=$id";
		$eje=mysql_query($sql);
		if(!$eje){
			mysql_query("ROLLBACK");//cancelamos transacción 
			die("Error al eliminar proveedores por producto para reemplazarlos!!!\n\n".$sql."\n\n".mysql_error());
		}*/
	//descomprimimos registros
		$arr=explode("°",$info);
		for( $i = 0; $i < sizeof($arr); $i++ ){
			$arr1=explode("~",$arr[$i]);//descomprimimos datos por registro
			if($arr1[0]!=""){
				//$prec_pza=ROUND($p_pr/$c_pr,2);//calculamos precio por pieza
				$prec_caja = ROUND( $arr1[1] * $arr1[2], 2 );//calculamos precio por pieza	
				
				if($arr1[4]==0){	
					$sql="INSERT INTO ec_proveedor_producto ( id_proveedor_producto, id_proveedor, id_producto, precio, 
						presentacion_caja, precio_pieza, clave_proveedor, fecha_alta, sincronizar ) 
					VALUES(null, '{$arr1[0]}', '{$id}' ,'{$prec_caja}', '{$arr1[2]}', '{$arr1[1]}', '{$arr1[3]}', NOW(), '1')";
				}else{
					$sql="UPDATE ec_proveedor_producto SET precio = '{$prec_caja}', presentacion_caja = '{$arr1[2]}', 
					precio_pieza = '{$arr1[1]}', clave_proveedor='{$arr1[3]}', ultima_actualizacion = NOW() 
					WHERE id_proveedor_producto = '{$arr1[4]}'";
				}
				//echo $sql;
				$eje=mysql_query( $sql );
				if( !$eje ){
					mysql_query( "ROLLBACK" );//cancelamos transacción 
					die( "Error al insertar/actualizar proveedor-producto!!!\n\n" . $sql . "\n\n" . mysql_error() );
				}
			}
		}//fin de for i
		mysql_query( "COMMIT" );//autorizamos transacción
		die( "ok|Modificado exitosamente!!!" );
	}
/*
//actualizamos proveedor de proveedor
	if($fl==2){
		$prec_pza=ROUND($nvo_prec/$nva_cant,2);//calculamos precio por pieza
		//die($prec_pza);
		$sql="UPDATE ec_proveedor_producto SET precio=$nvo_prec,presentacion_caja=$nva_cant,precio_pieza=$prec_pza WHERE id_proveedor_producto=$id";
		$eje=mysql_query($sql)or die("Error al modificar el producto con proveedor\n\n".$sql."\n\n".mysql_error());
		die('ok|ok');
	}
//eliminamos proveedor
	if($fl==3){
		$sql="DELETE FROM ec_proveedor_producto WHERE id_proveedor_producto=$id";
		$eje=mysql_query($sql)or die("Error al eliminar el proveedor del producto\n\n".$sql."\n\n".mysql_error());
		die('ok|ok');
	}*/
//modidifcamos estado de producto en susucrsales y estacionalidad alta
	if($fl==4){
		$arr=explode("°",$info);//descomprimimos datos
		mysql_query("BEGIN");//marcas inicio de transacción
		for($i=0;$i<sizeof($arr);$i++){
			$arr1=explode("~",$arr[$i]);
			$arr3=explode("-",$arr1[0]);
		//actualizamos los estados de sucursal
			$sql="UPDATE sys_sucursales_producto SET estado_suc=$arr3[0] WHERE id=$arr3[1]";
			$eje=mysql_query($sql);
			if(!$eje){
				mysql_query("ROLLBACK");//cancelamos transaccion 
				die("Error al actualizar el estado del prducto en la sucursal\n\n".$sql."\n\n".mysql_error());
			}
			$arr4=explode("-",$arr1[1]);
		//actualizamos la estacionalidad
			$sql="UPDATE ec_estacionalidad_producto SET maximo=$arr4[0] WHERE id_estacionalidad_producto=$arr4[1]";
			$eje=mysql_query($sql);
			if(!$eje){
				mysql_query("ROLLBACK");//cancelamos transaccion 
				die("Error al actualizar el estado del prducto en la sucursal\n\n".$sql."\n\n".mysql_error());
			}


/*implementación Oscar 10.09.2018 para actualizar estacionalidad final si se trata de estacionalidad final*/
		$sql="SELECT
				est.es_alta,/*0*/
                s.id_sucursal,/*1*/
                s.factor_estacionalidad_final,/*2*/
                (SELECT id_estacionalidad FROM ec_estacionalidad WHERE id_sucursal=s.id_sucursal AND es_alta=0),/*3*/
                estProd.id_producto/*4*/
            FROM ec_estacionalidad_producto estProd
            LEFT JOIN ec_estacionalidad est ON estProd.id_estacionalidad=est.id_estacionalidad
            JOIN sys_sucursales s ON est.id_sucursal=s.id_sucursal 
            WHERE estProd.id_estacionalidad_producto=$arr4[1]";
       
       	$eje=mysql_query($sql)or die("Error al consultar el tipo de estacionalidad!!!".mysql_error());
        $res=mysql_fetch_row($eje);
    	if($res[0]==1){
    	/*actualizamos la estacionalidad dependiente*/
    		$dato_nvo=round($arr4[0]*$res[2]);
            //actualizamos la estacionalidad final
            $sql="UPDATE ec_estacionalidad_producto SET
                    maximo=$dato_nvo
                WHERE id_estacionalidad=$res[3] AND id_producto=$res[4]";
        //die($sql);
	        if(!mysql_query($sql)){
                $error=mysql_error();
                mysql_query("ROLLBACK");
                die("Error al actualizar la estacionalidad dependiente!!!".$sql."\n\n".$error);
            }
    	}
/*fin de cambio Oscar 10.09.2018*/	


		}//fin de for i
		mysql_query("COMMIT");//autorizamos transacción
		die("ok|ok");
	}
	/*
//cambiamos estado de producto en sucursal
	if($fl==4){
		$sql="UPDATE sys_sucursales_producto SET estado_suc=$nvo_dto WHERE id=$id_reg";
		$eje=mysql_query($sql)or die("Error al modificar configuración del producto en la tablas sys_sucursales_producto\n\n".$sql."\n\n".mysql_error());
		die('ok|ok');
	}
//editamos estacionalidad máxima del producto
	if($fl==5){
	//consultamos estacionalidad alta de la sucursal
		$sql="SELECT 
				MAX(ep.maximo),
				ep.id_estacionalidad_producto
			FROM ec_estacionalidad_producto ep
			LEFT JOIN ec_estacionalidad e ON ep.id_estacionalidad=e.id_estacionalidad
			LEFT JOIN sys_sucursales s ON e.id_sucursal=s.id_sucursal
			WHERE ep.id_producto=$id_pr AND e.id_sucursal=$id_sucur AND e.nombre LIKE '%ALTA%'";
		$eje=mysql_query($sql)or die("Error al consultar id de estacionalidad alta!!!\n\n".$sql."\n\n".mysql_error());
		if(mysql_num_rows($eje)!=1){
			die("Algo está equivocado!!!");
		}
	//extraemos id de estacionalidad alta de la sucursal
		$r=mysql_fetch_row($eje);
		$id_estac_prod=$r[1];
	//actualizamos la cantidad máxima de la estacionalidad
		$sql="UPDATE ec_estacionalidad_producto SET maximo=$dato WHERE id_estacionalidad_producto=$id_estac_prod";
		$eje=mysql_query($sql)or die("Error al modificar estacionalidad alta!!!\n\n".$sql."\n\n".mysql_error());
		die('ok|ok');

	}*/
//eliminamos precio de producto
	if($fl==6){
		mysql_query("BEGIN");//marcaos inicio de transacción
		$sql="DELETE FROM ec_precios_detalle WHERE id_producto=$id";
		$eje=mysql_query($sql);
		if(!$eje){
			mysql_query("ROLLBACK");//cancelamos transacción
			die("Error al eliminar precios para remplazarlos!!!\n\n".$sql."\n\n".mysql_error());
		}
		$arr=explode("°",$info);
	//insertamos los nuevos detalles de precios
		for($i=0;$i<sizeof($arr);$i++){
			$arr1=explode("~",$arr[$i]);
			$sql="INSERT INTO ec_precios_detalle ( id_precio_detalle, id_precio, de_valor, a_valor, precio_venta, precio_etiqueta, id_producto, es_oferta, alta, ultima_actualizacion, sincronizar )
			VALUES(null,$arr1[0],$arr1[1],$arr1[2],$arr1[3],$arr1[3],$id,$arr1[4],NOW(),NOW(),1)";
			$eje=mysql_query($sql);
			if(!$eje){
				mysql_query("ROLLBACK");//cancelamos transacción
				die("Error al eliminar precios para remplazarlos!!!\n\n".$sql."\n\n".mysql_error());
			}
		}//termina for i
		mysql_query("COMMIT");//autorizamos transacción
		die('ok|ok');
	}
/*
//actualizamos precio de producto
	if($fl==7){
		$sql="UPDATE ec_precios_detalle SET de_valor='$cant_min',a_valor='$cant_max',precio_venta='$mont',precio_etiqueta='$mont' WHERE id_precio_detalle=$id";
		$eje=mysql_query($sql)or die("Error al actualizar precio!!!\n\n".$sql."\n\n".mysql_error());
		die('ok|ok');
	}
//insertamosnuevo precio //id:id_prec,cant_min:de,cant_max:a,mont:monto,oferta:ofta,id_producto:id_p,fl:flg
	if($fl==8){
		$sql="INSERT INTO ec_precios_detalle SET id_precio_detalle=null,id_precio='$id_prec',de_valor='$cant_min',a_valor='$cant_max',precio_venta='$mont',precio_etiqueta='$mont',id_producto='$id_producto',es_oferta=$oferta";
		$eje=mysql_query($sql)or die("Error al insertar precio!!!\n\n".$sql."\n\n".mysql_error());
	//obtenemos el id del registro
		die("ok|".mysql_insert_id()."|".$c);
	}
*/
//habilitamos/deshabilitamos resurtimiento
	if($fl==9){
		$sql="UPDATE ec_productos SET es_resurtido=$valor WHERE id_productos=$id_p";
		$eje=mysql_query($sql)or die("Error al modificar resurtimiento!!!\n\n".$sql."\n\n".mysql_error());
		die('ok|ok');
	}

//guardamos el pedido
	if($fl==10){
		mysql_query("BEGIN");//marcamos inicio de transacción
		$ids_asignados="";
		//$arr=str_replace("Â", "", $arr);//implementacion oscar por fallo
		$provs_prod=explode("#",$arr);//descomprimimos arreglo por id de proveedor
		for($i=0;$i<sizeof($provs_prod);$i++){
			$provs=explode("°",$provs_prod[$i]);
			$id_prov=$provs[0];//guardamos id de proveedor
			if($provs[1]!=""||$provs[1]!=null){	
				$sql="INSERT INTO ec_ordenes_compra SET
					/*1*/	id_orden_compra=null,
					/*2*/	id_proveedor=$id_prov,
					/*3*/	id_estatus_oc=2,/*Se guarda con status pendiente de surtir*/
					/*4*/	subtotal=0,
					/*5*/	iva=0,
					/*6*/	total=0,
					/*7*/	isr_ret=0,
					/*8*/	iva_ret=0,
					/*9*/	dias_pago=0,
					/*10*/	pagada=0,
					/*11*/	id_sucursal=$user_sucursal,
					/*12*/	folio='N/A',
					/*13*/	fecha=NOW(),
					/*14*/	id_usuario=$user_id,
					/*15*/	surtida=0,
					/*16*/	observaciones='',
					/*17*/	fue_req=0,
					/*18*/	hora=NOW(),
					/*19*/	id_concepto_oc=1";
				$eje=mysql_query($sql);
				if(!$eje){
					mysql_query("ROLLBACK");
					die("Error al insertar encabezado de compra\n\n".$sql."\n\n".mysql_error());
				}
				$id_compra=mysql_insert_id();
				//if($i>0){echo "provs:".$provs[1]."\n\n";}
				$prods=explode("|",$provs[1]);
				for($j=0;$j<sizeof($prods)-1;$j++){
					$arr_prods=explode("~",$prods[$j]);
					//$arr_prods[4]=base64_decode($arr_prods[4]);
					$sql1="INSERT INTO ec_oc_detalle SET
								id_oc_detalle=NULL,
								id_orden_compra=$id_compra,
								id_producto=$arr_prods[0],
								cantidad=$arr_prods[1],
								precio=$arr_prods[2],
								iva=0,
								ieps=0,
								cantidad_surtido=0,
								observaciones='$arr_prods[3]',
								id_proveedor_producto=$arr_prods[4]";
					$eje1=mysql_query($sql1);

				//echo ( $sql1 );
					if(!$eje1){
						mysql_query("ROLLBACK");
						die("Error al insertar detalle de compra\n\n".$sql1."\n\n".mysql_error());
					}
				}//fin de for j
			//consultamos el moto total de la compra
				$sql="SELECT SUM(cantidad*precio) FROM ec_oc_detalle WHERE id_orden_compra=$id_compra";
				$eje=mysql_query($sql);
				if(!$eje){
					mysql_query("ROLLBACK");//cancelamos transacción
					die("Error al consultar el monto total de la compra!!!\n\n".$sql."\n\n".mysql_error());
				}
				$row=mysql_fetch_row($eje);
				$monto_compra=$row[0];
			//actualizamos el monto de la cabecera de compra
				$sql="UPDATE ec_ordenes_compra SET subtotal='{$monto_compra}', total='{$monto_compra}', folio=(SELECT CONCAT('CO',CURRENT_DATE()+0,$id_compra)) WHERE id_orden_compra=$id_compra";
				$eje=mysql_query($sql);
				if(!$eje){
					mysql_query("ROLLBACK");//cancelamos transacción
					die("Error al actualizar monto total y folio de la orden de compra!!!\n\n".$sql."\n\n".mysql_error());
				}
				$ids_asignados.=$id_compra."~";
			}//termina if
		}//termina for principal
		mysql_query("COMMIT");
		die('ok|'.$ids_asignados);//regresamos ids asignados a cabeceras de ordenes de compra
	}

	if($fl=='status_prd'){
		$accion="habilitado";
		if($valor==0){
			$accion="deshabilitado";
		}
		$sql="UPDATE ec_productos SET habilitado=$valor WHERE id_productos=$id";
		//die($sql);
		$eje=mysql_query($sql)or die("Error al actualizar el status del producto!!!<br>".mysql_error()."<br>".$sql);
		die("Producto ".$accion." exitosamente!");
	}

	if($fl=='omitir_web'){
		$accion="habilitado";
		if($valor==0){
			$accion="deshabilitado";
		}
		$sql="UPDATE ec_productos SET omitir_alertas=$valor WHERE id_productos=$id";
		//die($sql);
		$eje=mysql_query($sql)or die("Error al actualizar el status del producto!!!<br>".mysql_error()."<br>".$sql);
		die("Producto ".$accion." de Pagina Web exitosamente!");
	}
?>