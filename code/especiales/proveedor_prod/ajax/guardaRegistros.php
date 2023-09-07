<?php
	include("../../../../conectMin.php");
	$fl=$_POST['flag'];
	
	if($fl=='resetear_precios'){
		$id_proveedor=$_POST['id_pro'];
		$sql="UPDATE ec_proveedor_producto SET precio=0,precio_pieza=0 WHERE id_proveedor=$id_proveedor"; 
		$eje=mysql_query($sql)or die("Error al cambiar a cero todos los precios de venta del proveedor ".$id_proveedor."!!!");
	//regresamos la respuesta
		die('ok');
	}

	extract($_POST);
	//die($info);
//descomponemos info
	$arr=explode("|",$info);
	mysql_query("BEGIN");//marcamos inicio de transacción
	for($i=0;$i<sizeof($arr)-1;$i++){
		$arr2=explode("~",$arr[$i]);
	//jutamos las claves de proveedor
		$cods=explode("\n",$arr2[1]);
		$arr2[1]="";//reseteamos posición del arreglo
		for($k=0;$k<sizeof($cods);$k++){
			if($cods[$k]!=""){
				$arr2[1].=$cods[$k];
			}
			if($cods[$k]!=""&&$k<sizeof($cods)-1){
				$arr2[1].=",";
			}
		}
//die($arr2[1]);
	
	//insertamos el registro en tabla de proveedor por producto
		$pco_caja=ROUND($arr2[2]*$arr2[3],2);
	//verificamos si ya existe el producto para este proveedor
		$sql="SELECT id_proveedor_producto 
				FROM ec_proveedor_producto
				WHERE id_producto='$arr2[0]' AND id_proveedor='$id_prov'";
		$eje=mysql_query($sql);
		if(!$eje){
			mysql_query("ROLLBACK");//deshacemos transacción
			die("Error al consultar existencia del registro en tabla ec_proveedor_producto!!!\n\n".$sql."\n\n".mysql_error());
		}
	//si no existe; insertamos registro
		if(mysql_num_rows($eje)==0){
			$sql="INSERT INTO ec_proveedor_producto VALUES(/*1*/null,/*2*/{$id_prov},/*3*/{$arr2[0]},/*4*/{$pco_caja},/*5*/{$arr2[3]},/*6*/{$arr2[2]},
				/*7*/'{$arr2[1]}',/*8*/null,/*9*/null)";
//die("1_".$sql);
			$eje=mysql_query($sql);
			if(!$eje){
				$error=mysql_error();
				mysql_query("ROLLBACK");//deshacemos transacción
				die("Error al insertar en tabla ec_proveedor_producto!!!\n\n".$sql."\n\n".$error);
			}
		}
	//si ya existe actualizamos el registro
		else{
			$id_reg=mysql_fetch_row($eje);
			$sql="UPDATE ec_proveedor_producto 
					SET precio='$pco_caja',presentacion_caja='$arr2[3]',precio_pieza='$arr2[2]',clave_proveedor='$arr2[1]'
					WHERE id_proveedor_producto=$id_reg[0]";
//die("2_".$sql);
			$eje=mysql_query($sql);
			if(!$eje){
				mysql_query("ROLLBACK");//deshacemos transacción
				die("Error al actualizar registro en tabla ec_proveedor_producto!!!\n\n".$sql."\n\n".mysql_error());
			}
		}
	/*actualizamos el precio de recepciones que no tienen precio y corresponden a este proveedor
			//$sql="SELECT id_proveedor,precio_pieza,presentacion_caja FROM ec_proveedor_producto WHERE id_producto=$arr2[0] AND id_proveedor=$id_prov";
			//$eje_1=mysql_fetch_row($sql);
			//$r_1=mysql_fetch_row($eje_1);
			$sql="UPDATE ec_oc_recepcion_detalle rd
				INNER JOIN ec_oc_recepcion r ON rd.id_oc_recepcion=r.id_oc_recepcion
				SET rd.precio_pieza=$arr2[2],rd.monto=(rd.piezas_recibidas*$arr2[2])
				WHERE r.id_proveedor=$id_prov
				AND rd.id_producto=$arr2[0]
				AND rd.precio_pieza=0;";
			
			$eje_2=mysql_query($sql);
			
			if(!$eje_2){
				$error=mysql_error();
				mysql_query("ROLLBACK");
				die("Error al actualizar detalles de remisiones pendientes de precio!!!".$error."\n".$sql);
			}*/
	}
	mysql_query("COMMIT");//autorizamos transacción
	echo 'ok';
?>