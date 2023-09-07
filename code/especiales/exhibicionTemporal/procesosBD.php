<?php
	include('../../../conectMin.php');
	$flag=$_POST['fl'];


/**************************************Buscador***************************/
	if($flag=='busqueda'){
		//die($_POST['txt']);
		$sql="SELECT 
					te.id_temporal_exhibicion,
					p.nombre,
					(te.cantidad-te.piezas_recibidas)-te.piezas_agotadas
				FROM ec_temporal_exhibicion te
				LEFT JOIN ec_productos p ON te.id_producto=p.id_productos
				WHERE te.id_sucursal=$user_sucursal AND (";
	//realizamos búsqueda de presición
		$clave=explode(" ",$_POST['txt']);
		for($i=0;$i<sizeof($clave);$i++){	
			if($clave[$i]!="" && $clave[$i]!=null){
				if($i>0){
					$sql.=" AND ";
				}
				$sql.="p.nombre LIKE '%".$clave[$i]."%'";
			}
		}//fin de for $i
		$sql.=") AND ((te.cantidad-te.piezas_recibidas)-te.piezas_agotadas)>0";//cerramos paréntesis de coincidencias
		$eje=mysql_query($sql)or die("Error al buscar coincidencias!!!\n\n".mysql_error()."\n\n".$sql);
		
		echo 'ok|<table border="0" width="100%">';
		$cont=0;
		while($r=mysql_fetch_row($eje)){
			$cont++;
			echo '<tr width="100%" id="fila_opc_'.$cont.'" onkeyup="valida_tca_busc(event,'.$r[0].','.$cont.');" onclick="enfoca('.$r[0].')" tabindex="'.$cont.'">';
				echo '<td id="b1_'.$cont.'" style="display:none;">'.$r[0].'</td>';
				echo '<td width="98%" style="padding:8px;">'.$r[1].' '.$r[2].'</td>';				
			echo '</tr>';
		}
	}

/**************************************Proceso de almacenamiento en BD***************************/
	if($flag=='guarda'){
	//separamos los datos
		$array=explode("|",$_POST['arr']);
		mysql_query("BEGIN");//marcamos ele inicio de la transacción
	//extraemos los almacenes
		$sql="(SELECT id_almacen
				from ec_almacen where id_sucursal=$user_sucursal AND es_almacen=1)
				UNION 
				(SELECT id_almacen
				from ec_almacen where id_sucursal=$user_sucursal AND es_almacen=0)";
	$eje1=mysql_query($sql)or die("Error al consultar los almacénes de la sucursal!!!\n\n".$sql."\n\n".mysql_error());
	

	//insertamos la cabecera del movimiento de almacén
		if($_POST['mov_alm']>0){
			for($i=0;$i<=1;$i++){
				$r=mysql_fetch_row($eje1);
				//echo $r[0]."|";
				$obs="Movimiento de entrada desde exhibición";
				$tipo=1;
				if($i==1){
					$obs="Movimiento de salida hacia almacén principal";
					$tipo=2;
				}
			//insertamos cabecera
				$sql="INSERT INTO ec_movimiento_almacen VALUES(/*1*/null,/*2*/$tipo,/*3*/$user_id,/*4*/$user_sucursal,/*5*/now(),/*6*/now(),
					/*7*/'$obs',/*8*/-1,/*9*/-1,/*10*/'',/*11*/-1,/*12*/-1,/*13*/$r[0],/*14*/0,/*15*/0,/*16*/'0000-00-00 00:00:00',/*17*/now())";
				$eje=mysql_query($sql);
				if(!$eje){
					$error=mysql_error();
					mysql_query("ROLLBACK");//cancelamos la transacción
					die("Error al insertar ".$obs."\n\n".$sql."\n\n".$error);
				}
			//capturamos los ids asignados a las cabeceras
				if($i==0){
					$id_entrada=mysql_insert_id();
				}else if($i==1){
					$id_salida=mysql_insert_id();
				}
			}//fin de de for i
		}
	//actualizamos los registros e insertamos el detalle de movimiento de almacen
		for($i=0;$i<sizeof($array)-1;$i++){
			$dat=explode("~",$array[$i]);
			$sql="UPDATE ec_temporal_exhibicion SET piezas_recibidas=(piezas_recibidas+$dat[1]),piezas_agotadas=(piezas_agotadas+$dat[2])
			 WHERE id_temporal_exhibicion=$dat[0]";
			//ECHO '<BR>'.$sql;
			$eje=mysql_query($sql);
			if(!$eje){
				$error=mysql_error();
				mysql_query("ROLLBACK");//cancelamos la transacción
				die("Error al actualizar el registro temporal de exhibición!!!\n\n".$sql."\n\n".$error);
			}
		//insertamos el detalle de los movimientos 
			if($dat[2]!=0&&$dat[2]!=''&&$dat[2]!=null){
				for($j=0;$j<=1;$j++){
				//intercalamos id de movimiento de entrada y salida
					$id_mov=$id_entrada;
					if($j==1){
						$id_mov=$id_salida;
					}
					$cantidad=$dat[2];
					$sql="INSERT INTO ec_movimiento_detalle VALUES(null,$id_mov,$dat[3],$cantidad,$cantidad,-1,-1)";
					//echo $sql."\n";
					$eje_1=mysql_query($sql);
					if(!$eje_1){
						$error=mysql_error();
						mysql_query("ROLLBACK");//cancelamos la transacción
						die("Error al insertar el detalle del movimiento!!!\n\n".$sql."\n\n".$error);
					}
				//	echo 'sale de  salida';
				}//fin de for $i
			}
		}//fin de for i
	//autorizamos la transacción
		mysql_query("COMMIT");
		echo 'ok|';
	}	
?>