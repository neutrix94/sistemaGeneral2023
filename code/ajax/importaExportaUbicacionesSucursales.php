<?php
	include('../../conectMin.php');
	$fl=$_POST['fl_ubic'];
	$id_sucursal=$_POST['sucursal_ubic'];
	//die('here');

	if($fl=='exporta_ubicaciones'){
		$data="ID PRODUCTO,ORDEN DE LISTA,ALFANUMERICO,NOMBRE,INVENTARIO EN SUCURSAL(ALMACEN PRIMARIO),UBICACION\n";
		$nombre="ubicaciones".$id_sucursal.".csv";
		$sql="SELECT 
				p.id_productos,
				p.orden_lista,
				REPLACE(p.clave,',','*'),
				REPLACE(p.nombre,',',' '),
				SUM(IF(ma.id_movimiento_almacen IS NULL,0,(md.cantidad*tm.afecta))),
				sp.ubicacion_almacen_sucursal
				FROM ec_productos p
				LEFT JOIN sys_sucursales_producto sp ON sp.id_producto=p.id_productos
				AND sp.id_sucursal IN($id_sucursal)
				LEFT JOIN ec_movimiento_detalle md ON md.id_producto=p.id_productos
				LEFT JOIN ec_movimiento_almacen ma ON ma.id_movimiento_almacen=md.id_movimiento
				LEFT JOIN ec_tipos_movimiento tm ON tm.id_tipo_movimiento=ma.id_tipo_movimiento
				LEFT JOIN ec_almacen alm ON alm.id_almacen=ma.id_almacen 
				AND alm.es_almacen=1 AND alm.id_sucursal IN($id_sucursal)
				WHERE sp.estado_suc=1
				GROUP BY p.id_productos
				ORDER BY p.orden_lista ASC";
		$eje=mysql_query($sql)or die("Error al consultar las ubicaciones por sucursal!!!<br>".mysql_error()."<br>".$sql);
		$tam=mysql_num_rows($eje);
		for($i=0;$i<$tam;$i++){
			$r=mysql_fetch_row($eje);
			$data.=$r[0].",".$r[1].",".$r[2].",".$r[3].",".$r[4].",".$r[5];
			if($i<($tam-1)){
				$data.="\n";//salto de linea
			}
		}
//		die($tam);
	//generamos descarga
		header('Content-Type: aplication/octect-stream');
		header('Content-Transfer-Encoding: Binary');
		header('Content-Disposition: attachment; filename="'.$nombre.'"');
		echo(utf8_decode($data));
		die('');
	}

	if($fl=='importa_ubicaciones'){
		$arr=explode("|",$_POST['datos_ubic_sucs']);
		//die('fk: '.$_POST['datos_ubic_sucs']);
		mysql_query("BEGIN");
		for($i=0;$i<sizeof($arr);$i++){
			$aux=explode(",", $arr[$i]);
			if($aux[0]!='' && $aux[0]!=null){
				if($aux[1]=='' || $aux[1]==null){
					$aux[1]="";
				}
				$sql="UPDATE sys_sucursales_producto SET ubicacion_almacen_sucursal='$aux[1]' WHERE id_producto='$aux[0]' AND id_sucursal='$id_sucursal'";
				
				$eje=mysql_query($sql);
				if(!$eje){
					$error=mysql_error();
					mysql_query("ROLLBACK");
					die("Error al actualizar la ubicacion de producto en sucursal!!!<br>".$error."<br>".$sql);
				}
			}
		}//fin de for $i
		mysql_query("COMMIT");
		//die('<script>alert("Ubicaciones Actualizadas Exitosamente!!!");window.close();</script>');
		die("Ubicaciones Actualizadas Exitosamente!!!");
	}
?>