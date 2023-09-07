<?php
	function abre($conexion){
		$abrir="UPDATE ec_sincronizacion SET en_proceso=1 WHERE id_sincronizacion=1";
		$eje=mysql_query($abrir,$conexion);
		if(!$eje){
			return 'error';
		}
		return 'ok';
	}
	function cierra($local,$linea){//Actualizamos el registro
		if($local=='' || $linea==''){
			die('variables vacias en funcion cierra...');
		}
		$nuevaSinc=getDateTime1($linea);//TOMAMOS LA HORA DEL SERVIDOR EN LINEA
		echo "\nnueva sincronizacion: ".$nuevaSinc."\n";
		$sqlCierra="UPDATE ec_sincronizacion SET ultima_sincronizacion='$nuevaSinc' WHERE id_sincronizacion=1";
		$ok=mysql_query($sqlCierra,$local);
		if(!$ok){
			mysql_query('rollback',$local);
			mysql_query('rollback',$linea);
			die("Error al actualizar fecha de sincronizacion\n".mysql_error($local)."\n".$sqlCierra);
		}
		$cierraL="UPDATE ec_sincronizacion SET en_proceso=0 WHERE id_sincronizacion=1";
		$eje=mysql_query($cierraL,$linea);
		if(!$eje){
			mysql_query('rollback',$local);
			mysql_query('rollback',$linea);
			die('Error al liberar el servidor en Linea');
		}
	//aprobamos transacciones
		mysql_query('commit',$local);
		mysql_query('commit',$linea);
			//echo'<br>**************Finaliza Sincronización**************<br>';
			echo '<p align="right">
					<span>Fin de Sincronización:</span>
					<input type="text" value="'.$nuevaSinc.'" style="padding:5px;width:150px;" disabled>
				</p>';
			echo '<br><br>Sincronización satisfactoria';
		return false;
	}

	function getDateTime1($conexion){//obtenemos hora y fecha
		$s="SELECT DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')";
		$r=mysql_query($s,$conexion);
		$rs=mysql_fetch_row($r);
		
		return $rs[0];
	}

	extract($_POST);
	//echo 'id_sucursal: '.$sucursal_id;
	$suc=$sucursal_id;
	require('conexionSincronizar.php');//incluimos conexion a ambas bd
	//echo 'ultima sincronizacion :'.$ultimaSinc;
	mysql_query('begin',$local);
	mysql_query('begin',$linea);
?>
<div align="right" style="left:300px;" id="botCierra">
	<input type="button" style="padding:10px;background:red;color:white;" value="X" onclick="cierraSinc();"><!--display:none;-->
</div>
<?php
//verificamos que otra sucursal no este sincronizando
$sql="SELECT en_proceso FROM ec_sincronizacion WHERE id_sincronizacion=1";
$eje=mysql_query($sql,$linea);
if(!$eje){
	die("Error al verificar que alguien mas este sincronizando!!!\n".mysql_error($linea)."\n".$sql);
}
$res=mysql_fetch_row($eje);
if($res[0]==1){
	die('<p align="center"><font size="30px" color="white">Servidor ocupado<br>Intente en 5 minutos!!!</font></p>');
}
$comenzar=abre($linea);
if($comenzar!='ok'){
	die('Error al poner Servidor en proceso de Sincronizacion');
}
?>
<div style="background:white;">
<div style="background:rgba(0,225,0,.2)">
	<p align="right">
		<span>Inicio de Sincronización:</span>
		<input type="text" value="<?php echo getDateTime1($linea); ?>" style="padding:5px;width:150px;" disabled>
	</p>
<center>
	<table width="80%" border="1">
		<tr width="100%" style="background:rgba(225,0,0,.5);">
			<td colspan="2" width="50%" align="center">
				<font color="white">Descripción</font>
			</td>
			<td colspan="2" width="50%" align="center">
				<font color="white">Resultado</font>
			</td>
		</tr>
<?php
/*///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
||																																					   ||
||															Comenzamos sincronización de Sucursales													   ||
||																																					   ||
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////*/

$sucInsert=0;
$sucAct=0;
$sql="SELECT /*1*/id_sucursal,/*2*/nombre,/*3*/telefono,/*4*/direccion,/*5*/descripcion,/*6*/id_razon_social,/*7*/id_encargado,/*8*/activo,/*9*/logo,
			/*10*/multifacturacion,/*11*/id_precio,/*12*/descuento,/*13*/prefijo,/*14*/usa_oferta,/*15*/alertas_resurtimiento,/*16*/id_estacionalidad
		FROM sys_sucursales WHERE alta>'$ultimaSinc'";
$eje=mysql_query($sql,$linea);
if(!$eje){
	mysql_query('rollback',$local);
	mysql_query('rollback',$linea);
	die("Error en la consulta!!!\n".mysql_error($linea)."\n".$sql);
}
$sB=mysql_num_rows($eje);
if($sB>0){
	while($row=mysql_fetch_row($eje)){
		$aux="INSERT INTO sys_sucursales(/*1*/id_sucursal,/*2*/nombre,/*3*/telefono,/*4*/direccion,/*5*/descripcion,/*6*/id_razon_social,/*7*/id_encargado,
						/*8*/activo,/*9*/logo,/*10*/multifacturacion,/*11*/id_precio,/*12*/descuento,/*13*/prefijo,/*14*/usa_oferta,/*15*/alertas_resurtimiento,
						/*16*/id_estacionalidad)
				VALUES('$row[0]','$row[1]','$row[2]','$row[3]','$row[4]','$row[5]','$row[6]','$row[7]','$row[8]','$row[9]','$row[10]','$row[11]','$row[12]',
						'$row[13]','$row[14]','1')";
		$eje2=mysql_query($aux,$local);
		if(!$eje2){
			$comp="SELECT id_sucursal FROM sys_sucursales WHERE id_sucursal=$row[0]";
			$ej=mysql_query($comp,$local);
			if(!$ej){
				die('error!!!'.mysql_error($local));
			}
			$nS=mysql_num_rows($ej);
			if($nS==1){
		//remplazamos la sucursal
				$subcons="UPDATE sys_sucursales SET

							/*2*/nombre='$row[1]',
							/*3*/telefono='$row[2]',
							/*4*/direccion='$row[3]',
							/*5*/descripcion='$row[4]',
							/*6*/id_razon_social='$row[5]',
							/*7*/id_encargado='$row[6]',
							/*8*/activo='$row[7]',
							/*9*/logo='$row[8]',
							/*10*/multifacturacion='$row[9]',
							/*11*/id_precio='$row[10]',
							/*12*/descuento='$row[11]',
							/*13*/prefijo='$row[12]',
							/*14*/usa_oferta='$row[13]',
							/*15*/alertas_resurtimiento='$row[14]',
							/*16*/id_estacionalidad='$row[15]'
						WHERE id_sucursal=$row[0]";
				$rempla=mysql_query($subcons,$local);
				if(!$rempla){
					mysql_query('rollback',$local);
					mysql_query('rollback',$linea);
					die('no se pudo rempzar la sucursal'.mysql_error($local));
				}
				$sucAct++;
			}else{
				mysql_query('rollback',$local);
				mysql_query('rollback',$linea);
				die('error diferente'.mysql_error($local));	
			}
		}
		$sucInsert++;//incrementamos contador
	}
}
echo '<tr style="background:rgba(0,0,225,.5);"><td colspan="4"><font color="white">Sucursales</font></td></tr>';
echo '<tr><td>Sucursales por insertar:</td><td>'.$sB.'</td>';
echo '<td>Sucursales bajadas:</td><td>'.$sucInsert.'</td>';
echo '<tr><td colspan="4">Sucursales actualizadas:</td><td>'.$sucAct.'</td></tr>';

/*///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
||																																					   ||
||															Comenzamos sincronización de Productos 													   ||
||																																					   ||
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////*/
//cierra($local);
//die('reiniciado');
?>
<?php
//marcamos inicio de transacciones
mysql_query('BEGIN',$local);
mysql_query('BEGIN',$linea);

echo '<tr style="background:rgba(0,0,225,.5);"><td colspan="4" align="center"><font color="white">Productos</font></td></tr>';
//buscamos productos para bajar
	$sqlProd="SELECT id_productos, 
					clave,
					nombre,
					id_categoria,
					id_subcategoria,
					precio_venta_mayoreo,
					precio_compra,
					observaciones,
					inventariado,
					es_maquilado,
					desc_gral,
					nombre_etiqueta,
					orden_lista,
					ubicacion_almacen,
					codigo_barras_1,
					codigo_barras_2,
					codigo_barras_3,
					codigo_barras_4,
					id_subtipo,
					id_numero_luces,
					id_color,
					id_tamano,
					habilitado,
					omitir_alertas,
					muestra_paleta
				FROM ec_productos";
	$sqlBP=$sqlProd.' '."WHERE alta>'$ultimaSinc'";
	
	$ejecutaBP=mysql_query($sqlBP,$linea);
	if(!$ejecutaBP){
		mysql_query('rollback',$local);
		die('<br>ERROR!!!<br>'.mysql_error($linea));
	}
	$numBP=mysql_num_rows($ejecutaBP);
	echo '<tr><td>Productos a bajar:</td><td>'.$numBP.'</td>';
	$instP=0;
	$remp=0;
	if($numBP>0){
		while($datos=mysql_fetch_row($ejecutaBP)){
			$sql2="INSERT INTO ec_productos(id_productos,clave,nombre,id_categoria,id_subcategoria,precio_venta_mayoreo,precio_compra,
											observaciones,inventariado,es_maquilado,desc_gral,nombre_etiqueta,orden_lista,
											ubicacion_almacen,codigo_barras_1,codigo_barras_2,codigo_barras_3,codigo_barras_4,
											id_subtipo,id_numero_luces,id_color,id_tamano,habilitado,omitir_alertas,
											muestra_paleta)
							VALUES($datos[0],'$datos[1]','$datos[2]','$datos[3]','$datos[4]','$datos[5]','$datos[6]','$datos[7]','$datos[8]','$datos[9]',
								'$datos[10]','$datos[11]','$datos[12]','$datos[13]','$datos[14]','$datos[15]','$datos[16]','$datos[17]','$datos[18]',
								'$datos[19]','$datos[20]','$datos[21]',
									'$datos[22]','$datos[23]','$datos[24]')";
			
			$insPLocal=mysql_query($sql2,$local);
			
			if($insPLocal){
				$instP++;
			}else{
				$consC="SELECT id_productos FROM ec_productos where id_productos=$datos[0]";
				$ejeC=mysql_query($consC,$local);
				$nR=mysql_num_rows($ejeC);
				$errores="err";
				if($nR>0){
					$sql2="UPDATE ec_productos
								SET clave='$datos[1]',
								nombre='$datos[2]',
								id_categoria='$datos[3]',
								id_subcategoria='$datos[4]',
								precio_venta_mayoreo='$datos[5]',
								precio_compra='$datos[6]',
								observaciones='$datos[7]',
								inventariado='$datos[8]',
								es_maquilado='$datos[9]',
								desc_gral='$datos[10]',
								nombre_etiqueta='$datos[11]',
								orden_lista='$datos[12]',
								ubicacion_almacen='$datos[13]',
								codigo_barras_1='$datos[14]',
								codigo_barras_2='$datos[15]',
								codigo_barras_3='$datos[16]',
								codigo_barras_4='$datos[17]',
								id_subtipo='$datos[18]',
								id_numero_luces='$datos[19]',
								id_color='$datos[20]',
								id_tamano='$datos[21]',
								habilitado='$datos[22]',
								omitir_alertas='$datos[23]',
								muestra_paleta='$datos[24]'
								WHERE id_productos=$datos[0]";
						$remplaza=mysql_query($sql2,$local) or die("<br>ERROR!!!<br>".$sql2);
						if(!$remplaza){
							mysql_query('rollback',$local);
							mysql_query('rollback',$linea);
							die("Error!!!\n".mysql_error($local)."\n".$remplaza);
						}
						$remp++;
				}else{
					echo '  no'.$datos[0].', <br>'.$sql2;
				}
			}
		}
	}
	echo '<td>Productos insertados localmente:</td><td>'.$instP.'</td></tr>';
	echo '<tr><td colspan="4" align="center">Productos remplazados localmente: '.$remp.'</td>';

/***************************************************buscamos actualizaciones de producto***************************************************/

	$sqlBA=$sqlProd.' '."WHERE 	ultima_modificacion>'$ultimaSinc'";
	$ejeBA=mysql_query($sqlBA,$linea);
	$nAP=mysql_num_rows($ejeBA);
	echo '<tr><td>Actualizaciones de productos por bajar:</td><td>'.$nAP.'</td>';
	$act=0;
	if($nAP>0){
		while($datos=mysql_fetch_row($ejeBA)){
			$sql2="UPDATE ec_productos
								SET clave='$datos[1]',
								nombre='$datos[2]',
								id_categoria='$datos[3]',
								id_subcategoria='$datos[4]',
								precio_venta_mayoreo='$datos[5]',
								precio_compra='$datos[6]',
								observaciones='$datos[7]',
								inventariado='$datos[8]',
								es_maquilado='$datos[9]',
								desc_gral='$datos[10]',
								nombre_etiqueta='$datos[11]',
								orden_lista='$datos[12]',
								ubicacion_almacen='$datos[13]',
								codigo_barras_1='$datos[14]',
								codigo_barras_2='$datos[15]',
								codigo_barras_3='$datos[16]',
								codigo_barras_4='$datos[17]',
								id_subtipo='$datos[18]',
								id_numero_luces='$datos[19]',
								id_color='$datos[20]',
								id_tamano='$datos[21]',
								habilitado='$datos[22]',
								omitir_alertas='$datos[23]',
								muestra_paleta='$datos[24]'
								WHERE id_productos=$datos[0]";
		//die($sql2);
			$ejecuta2=mysql_query($sql2,$local) or die('<br>ERROR!!!<br>'.$sql2);
			if(!$ejecuta2){
				mysql_query("rollback",$local);
				mysql_query("rollback",$linea);
				die("Error!!!\n".mysql_error($local)."\n".$sql2);
			}
			$act++;
	}
}//fin de if
	echo '<td>Actualizaciones de productos insertadas: '.$act.'<td></tr>';

/*///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
||																																					   ||
||															Comenzamos sincronización de Inventario								   					   ||
||																																					   ||
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////*/

//checamos si se sincronizacn inventarios 
if($sInv==0){
	//
}else if($sInv==1){
	include('sincInventarios.php');
}else{
	die("Error al verificar si los inventarios serán sincronizados....!!!");
}

/*///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
||																																					   ||
||															Comenzamos sincronización de estacionalidades							   				   ||
||																																					   ||
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////*/
//buscamos estacionalidades para subir
$estS="SELECT id_estacionalidad,id_producto,minimo,medio,maximo
			FROM ec_estacionalidad_producto
			WHERE ultima_modificacion>'$ultimaSinc'";
$extEstS=mysql_query($estS,$local) or die('error al consultar estacionalidades localmente<br>'."\n".mysql_error($local)."\n".$estS);
$numEstS=mysql_num_rows($extEstS);
	$estInsLi=0;//contador de estacionalidades insertadas en linea
	if($numEstS>0){
//echo "Estacionalidades subidas:\n";
		while($estLo=mysql_fetch_row($extEstS)){
			$generaActLi="UPDATE ec_estacionalidad_producto SET minimo=$estLo[2],medio=$estLo[3],maximo=$estLo[4] 
								WHERE id_estacionalidad=$estLo[0] AND id_producto=$estLo[1]";
//echo "\n".$generaActLi;
			$actEstLi=mysql_query($generaActLi);
			if(!$actEstLi){
				mysql_query('rollback',$local);
				mysql_query('rollback',$linea);
				die('<br>no se actualizó estacionalidad de producto: '.$estLo[1]);
			}
				$estInsLi++;
		}
	}
//buscamos estacionalidades para bajar
$estBL="SELECT id_estacionalidad,id_producto,minimo,medio,maximo
			FROM ec_estacionalidad_producto
			WHERE ultima_modificacion>'$ultimaSinc'";
	//die($estBL);
$extEsBL=mysql_query($estBL,$linea) or die('error al consultar estacionalidades en linea<br>'.$estBL);
$numEstB=mysql_num_rows($extEsBL);
	$estInsLo=0;//contador de estacionalidades insertadas en linea
	if($numEstB>0){
//echo "Estacionalidades bajadas:\n";
		while($estLo=mysql_fetch_row($extEsBL)){
			$generaActLo="UPDATE ec_estacionalidad_producto SET minimo=$estLo[2],medio=$estLo[3],maximo=$estLo[4] 
								WHERE id_estacionalidad=$estLo[0] AND id_producto=$estLo[1]";
//echo "\n".$generaActLo;
			$actEstLo=mysql_query($generaActLo,$local);
			if(!$actEstLo){
				mysql_query('rollback',$local);
				mysql_query('rollback',$linea);
				die('<br>no se actualizó estacionalidad de producto: '.$estLo[1]);
			}
				$estInsLo++;
		}
	}
	echo '<tr style="background:rgba(0,0,225,.5);"><td colspan="4" align="center"><font color="white">Estacionalidades</font></td></tr>';
	echo '<tr><td>Estacionalidades por subir:</td><td>'.$numEstS.'</td>';
	echo '<td>estacionalidades subidas:</td><td>'.$estInsLi.'</td></tr>';
	echo '<td>Estacionalidades bajadas:</td><td>'.$estInsLo.'</td></tr>';
	echo '<tr><td>Estacionalidades por bajar:</td><td>'.$numEstB.'</td>';
/*///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
||																																					   ||
||															Comenzamos sincronización de listas de Precios							   				   ||
||																																					   ||
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////*/
$contX=0;
//buscamos si hay nuevas listas de precios para insertar
$descLP="SELECT id_precio,fecha,nombre,id_usuario
			FROM ec_precios WHERE alta>'$ultimaSinc'";
$ejeDescLP=mysql_query($descLP,$linea);
if(!$ejeDescLP){
	mysql_query("rollback",$local);
	mysql_query("rollback",$linea);
	die("Error al consultar listas de precios en linea\n".mysql_error($linea)."\n".$descLP);
}
$prec=mysql_num_rows($ejeDescLP);
	$nvaLista=0;
	if($prec>0){//si hay listas por insertar
		while($nvo=mysql_fetch_row($ejeDescLP)){
			$nuevaLista="INSERT INTO ec_precios(id_precio,fecha,nombre,id_usuario)
								VALUES('$nvo[0]','$nvo[1]','$nvo[2]',$nvo[3])";
			$insertaListaP=mysql_query($nuevaLista,$local) or die('<br>La lista no fue insertada localmente'.$nuevaLista);
			if(!$insertaListaP){
				$rempl="UPDATE FROM ec_precios SET fecha='$nvo[1]',nombre='$nvo[2]',id_usuario='$nvo[3]'
					WHERE id_precio='$nvo[0]'";
				$ejeRemp=mysql_query($rempl,$local);
				if(!$rempl){
					mysql_query('rollback',$local);
					mysql_query('rollback',$linea);
					die("Error al insertar listas de precios");		
				}
			}
				$nvaLista++;
		}//fin de while
	}//fin de if $prec>0

//buscamos lista de precio asignada a la sucursal
	$l_p_s="SELECT id_precio FROM sys_sucursales WHERE id_sucursal=$suc";
	$elps=mysql_query($l_p_s,$local) or die('<br>ERROR!!!'.$l_p_s);
	if($elps){
		$lp=mysql_fetch_assoc($elps);
	}
	$id_lista_precio=$lp['id_precio'];
	//die('lista de precio: '.$id_lista_precio);

//buscamos nuevos precios para insertar
	$consPre="SELECT id_precio_detalle,id_precio,de_valor,a_valor,precio_venta,precio_oferta,id_producto,es_oferta
					FROM ec_precios_detalle
					WHERE id_precio=$id_lista_precio 
					AND alta>'$ultimaSinc'";
	$ejeConsPre=mysql_query($consPre,$linea) or die('<br>ERROR:<br>'.$consPre);
	$nPre=mysql_num_rows($ejeConsPre);
	$pIns=0;
	if($nPre>0){
		while($pr=mysql_fetch_row($ejeConsPre)){
			$genP="INSERT INTO ec_precios_detalle(id_precio_detalle,id_precio,de_valor,a_valor,precio_venta,precio_oferta,id_producto,es_oferta)
						VALUES($pr[0],$pr[1],$pr[2],$pr[3],$pr[4],$pr[5],$pr[6],$pr[7])";
			$insP=mysql_query($genP,$local);
			if(!$insP){
				$com="SELECT id_precio_detalle FROM ec_precios_detalle WHERE id_precio_detalle=$pr[0]";
				$ejeCom=mysql_query($com,$local);
				if(!$ejeCom){
					mysql_query('rollback',$local);
					mysql_query('rollback',$linea);	
					die("Error al verificar que el detalle de precio existe localmente\n".$com);
				}
				$nDP=mysql_num_rows($ejeCom);
				if($nDP<=0){
					mysql_query('rollback',$local);
					mysql_query('rollback',$linea);	
					die("Error, el detalle de precio no existe localmente");					
				}
				$rem="UPDATE ec_precios_detalle SET id_precio='$pr[1]',de_valor='$pr[2]',a_valor='$pr[3]',precio_venta='$pr[4]',
				precio_oferta='$pr[5]',id_producto='$pr[6]',es_oferta=$pr[7] WHERE id_precio_detalle='$pr[0]'";
				$ejeRemp=mysql_query($rem,$local);
				if(!$ejeRemp){
					mysql_query('rollback',$local);
					mysql_query('rollback',$linea);	
					die("Error al remplazar el detalle de precio localmente\n".$rem);
				}else{
					$contX++;
				}
			}
			$pIns++;
		}
	}

//buscamos actualizaciones en precios existentes
	$precMod="SELECT id_precio_detalle,de_valor,a_valor,precio_venta,precio_oferta,id_producto,es_oferta
					FROM ec_precios_detalle 
					WHERE id_precio=$id_lista_precio 
					AND ultima_actualizacion>'$ultimaSinc'";

	$ejePrecMod=mysql_query($precMod,$linea) or die('<br>Error en la consulta:'.$precMod);
	$numPrec=mysql_num_rows($ejePrecMod);
	$actP=0;
	if($numPrec>0){
		while($datP=mysql_fetch_row($ejePrecMod)){
			$genAct="UPDATE ec_precios_detalle SET de_valor=$datP[1],a_valor=$datP[2],precio_venta=$datP[3],precio_oferta=$datP[4],es_oferta=$datP[6]
							WHERE id_precio_detalle=$datP[0] and id_producto=$datP[5]";
			//die("Query:\n".$genAct);
			$actPre=mysql_query($genAct,$local)or die('<br>ERROR!!!<br>'.$genAct);
			if(!$actPre){
				mysql_query('rollback',$local);
				mysql_query('rollback',$linea);
				die('error');
			}	
				$actP++;
		}
	}
//checamos si hay 
	$sql="SELECT ";

	echo '<tr style="background:rgba(0,0,225,.5);"><td colspan="4" align="center"><font color="white">Listas de Precios</font></td></tr>';
	echo '<tr><td>listas de precios por insertar:</td><td>'.$prec.'</td>';
	echo '<td>Listas insertadas:</td><td>'.$nvaLista.'</td></tr>';
	echo '<tr><td>Detalles de Precios por insertar:</td><td>'.$nPre.'</td>';
	echo '<td>Detalles de Precios insertados:</td><td>'.$pIns.'</td></tr>';
	echo '<tr><td>Precios a modificar localmente:</td><td>'.$numPrec.'</td>';
	echo '<td>Precios modificados localmente:</td><td>'.$actP.'</td></tr>';
	echo '<tr><td colspan="4">Precios Remplazados:'.$contX.'</td></tr>';
/*///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
||																																					   ||
||															Comenzamos sincronización de maquila    								   				   ||
||																																					   ||
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////*/

//buscamos nuevos productos de maquila
	$conMP="SELECT id_maquila,folio,fecha,id_usuario,id_producto,cantidad,id_sucursal,activa
				FROM ec_maquila
				WHERE alta>'$ultimaSinc'";
	$ejeMP=mysql_query($conMP,$linea) or die('<br>ERROR!!!<br>'.$conMP);
	$nM=mysql_num_rows($ejeMP);
	$mI=0;
	if($nM>0){
		while($m=mysql_fetch_row($ejeMP)){
			$creaM="INSERT INTO ec_maquila(id_maquila,folio,fecha,id_usuario,id_producto,cantidad,id_sucursal,activa)
							VALUES('$m[0]','$m[1]','$m[2]','$m[3]','$m[4]','$m[5]','$m[6]','$m[7]')";
			$insM=mysql_query($creaM,$local);
			if(!$insM){
			//comprobamos si ya existe la maquila
				$comp="SELECT id_maquila FROM ec_maquila WHERE id_maquila=$m[0]";
				$ejeComp=mysql_query($comp,$local);
				if(!$ejeComp){
					mysql_query('rollback',$local);
					mysql_query('rollback',$linea);
					die('Error al comprobar Maquila'."\n".$comp);
				}
				$nuM=mysql_num_rows($ejeComp);
				if($nuM<=0){
					mysql_query('rollback',$local);
					mysql_query('rollback',$linea);
					die("Error, esta maquila no existe localmente!!!\n".$act);
				}
				$act="UPDATE ec_maquila SET folio='$m[1]',fecha='$m[2]',id_usuario='$m[3]',id_producto='$m[4]',cantidad='$m[5]',id_sucursal='$m[6]',
				activa='$m[7]' WHERE id_maquila=$m[0]";
				$ejeAct=mysql_query($act,$local);
				if(!$act){
					mysql_query('rollback',$local);
					mysql_query('rollback',$linea);
					die("La maquila no se pudo insertar ni remplazar!!!\n".$act);
				}
			}
			$mI++;
		}//fin de while
	}
	echo '<tr style="background:rgba(0,0,225,.5);"><td colspan="4" align="center"><font color="white">Maquila</font></td></tr>';
	echo '<tr><td>Maquilas a descargar:</td><td>'.$nM.'</td>';
	echo '<td>Maquilas insertadas localmente:</td><td>'.$mI.'</td></tr>';

//buscamos detalles de maquila para bajar
	$bMD="SELECT id_producto_detalle,id_producto,id_producto_ordigen,cantidad
			FROM ec_productos_detalle 
			WHERE alta>'$ultimaSinc'";
	$ejeMD=mysql_query($bMD,$linea) or die('<br>ERROR!!!<br>'.$bMD);
	$nMD=mysql_num_rows($ejeMD);

	$dI=0;
	if($nMD>0){
		while($dMD=mysql_fetch_row($ejeMD)){
			$creaD="INSERT INTO ec_productos_detalle(id_producto_detalle,id_producto,id_producto_ordigen,cantidad)
							VALUES($dMD[0],'$dMD[1]','$dMD[2]','$dMD[3]')";
			$ejeD=mysql_query($creaD,$local);
			if(!$ejeD){
				$com="SELECT id_producto_detalle FROM ec_productos_detalle WHERE id_producto_detalle=$dMD[0]";
				$ejeComp=mysql_query($com,$local);
				if(!$ejeComp){
					mysql_query('rollback',$local);
					mysql_query('rollback',$linea);
					die("Error al comprobar detalle de Maquila\n".$com);
				}
				$nuD=mysql_num_rows($ejeComp);
				if($nuD<=0){
					mysql_query('rollback',$local);
					mysql_query('rollback',$linea);
					die("Error, este detalle de maquila no existe\n".$comp);
				}
				$act="UPDATE ec_productos_detalle SET id_producto='$dMD[1]',id_producto_ordigen='$dMD[2]',cantidad='$dMD[3]' 
						WHERE id_producto_detalle=$dMD[0]";
				$ejeAct=mysql_query($act,$local);
				if(!$ejeAct){
					mysql_query('rollback',$local);
					mysql_query('rollback',$linea);
					die("Error al remplazar detalle de maquila\n".$act);
				}
			}
			$dI++;
		}//fin de while
	}

	echo '<tr><td>Detalles de maquila a insertar:</td><td>'.$nMD.'</td>';
	echo '<td>Detalles de maquila insertados:</td><td>'.$dI.'</td></tr>';
//buscamos nuevos productos por insertar en la lista
	/*PENDIENTE (PREGUNTAR SI UTILIZAR TRIGGER O MANUAL)*/
//buscamos actualizaciones en detalle maquila
	$aDM="SELECT id_producto_detalle,id_producto,cantidad
			FROM ec_productos_detalle
			WHERE ultima_modificacion>'$ultimaSinc'";
	$ejeADM=mysql_query($aDM,$linea);
	$nADM=mysql_num_rows($ejeADM);
	$dMA=0;
	if($nADM>0){
		while($dA=mysql_fetch_row($ejeADM)){
			$genA="UPDATE ec_productos_detalle SET cantidad='$dA[2]'
						WHERE id_producto_detalle=$dA[0] AND id_producto=$dA[1]";
			$ejeAct=mysql_query($genA,$local);
			if(!$ejeAct){
				mysql_query('rollback',$local);
				mysql_query('rollback',$linea);
				die("Error al actualiza el detalle de maquila\n".$genA);
			}
			$dMA++;
		}//fin de while
	}
	echo '<tr><td>Detalles de maquila a Actualizar:</td><td>'.$nADM.'</td>';
	echo '<td>Detalles de Maquila actualizados:</td><td>'.$dMA.'</td></tr>';

/*///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
||																																					   ||
||															Comenzamos sincronización de Movimientos de almacen										   ||
||																																					   ||
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////*/
	$mA="SELECT 
		/*0*/	id_movimiento_almacen,
		/*1*/	id_tipo_movimiento,
		/*2*/	id_usuario,
		/*3*/	id_sucursal,
		/*4*/	fecha,
		/*5*/	hora,
		/*6*/	observaciones,
		/*7*/	id_pedido,
		/*8*/	id_orden_compra,
		/*9*/	lote,
		/*10*/	id_maquila,
		/*11*/	id_transferencia,
		/*12*/	id_almacen
		FROM ec_movimiento_almacen
		WHERE sincronizar=1 AND ultima_sincronizacion>'$ultimaSinc'";
	$ejeMA=mysql_query($mA,$linea);
	if(!$ejeMA){
		mysql_query('rollback',$local);
		mysql_query('rollback',$linea);
		echo 'Error al buscar movimientos de almacen por sincronizar!!!'."\n".mysql_error()."\n".$mA;
	}else{
		echo 'por bajar:'.mysql_num_rows($ejeMA); 
		while($rw=mysql_fetch_row($ejeMA)){
			$aux="INSERT INTO ec_movimiento_almacen(id_tipo_movimiento,id_usuario,id_sucursal,fecha,hora,observaciones,id_pedido,
				id_orden_compra,lote,id_maquila,id_transferencia,id_almacen,id_equivalente)
				VALUES('$rw[1]','$rw[2]','$rw[3]','$rw[4]','$rw[5]','$rw[6]','$rw[7]','$rw[8]','$rw[9]','$rw[10]','$rw[11]','$rw[12]','$rw[0]')";
			$insMov=mysql_query($aux,$local);
			if(!$insMov){
				mysql_query('rollback',$local);
				mysql_query('rollback',$linea);
				die("Error al insertar movimientos de Almacen");
			}
		//sacamos el di inserttado localmente
			$idM=mysql_insert_id($local);
		//sacamos los detalles del movimiento
			$subc="SELECT 
				/**/	id_producto,
				/**/	cantidad,
				/**/	cantidad_surtida,
				/**/	id_pedido_detalle,
				/**/	id_oc_detalle 
					FROM ec_movimiento_detalle
					WHERE id_movimiento=$rw[0]";
			$ejeSub=mysql_query($subc,$linea);
			if(!$ejeSub){
				mysql_query('rollback',$local);
				mysql_query('rollback',$linea);
				echo 'Error al extraer los detalles del movimineto de almacen!!!'."\n".mysql_error($linea)."\n".$subc;
			}else{
			//insertamos el detalle localmente
				while($rd=mysql_fetch_row($ejeSub)){
					$genD="INSERT INTO ec_movimiento_detalle(id_movimiento,id_producto,cantidad,cantidad_surtida,id_pedido_detalle,id_oc_detalle)
								VALUES('$idM','$rd[0]','$rd[1]','$rd[2]','$rd[3]','$rd[4]')";
					$ins=mysql_query($genD,$local);
					if(!$ins){
						mysql_query('rollback',$local);
						mysql_query('rollback',$linea);
						echo 'error al insertar el detalle del movimiento localmente!!!'."\n".mysql_error($local)."\n".$genD;
					}else{
//echo "\n".'ok';
					}
				}//fin de while ec_movimiento_detalle
			}
		}//fin de while de ec_movimiento_almacen
	}
//die('corte de pureba');


/*///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
||																																					   ||
||															Comenzamos sincronización de Sucursales Producto										   ||
||																																					   ||
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////*/
//declaramos contadores:
	$s_s_p_b=0;//bajados
//bajamos las modificaciones
	$sql="SELECT 
	/*0*/	id_producto,
	/*1*/	estado_presentacion,
	/*2*/	num_presentacion,
	/*3*/	minimo_surtir,
	/*4*/	estado_suc,
	/*5*/	nombre_presentacion,
	/*6*/	id_sucursal
		FROM sys_sucursales_producto WHERE ultima_modificacion>'$ultimaSinc'";
	//die($sql);
	$eje=mysql_query($sql,$linea);
	if(!$eje){
		die('Error al extraer datos de sys_sucursales_producto!!!'."\n".mysql_error($linea).$sql);
	}
	$n=mysql_num_rows($eje);
	if($n>0){
	//actualizamos localmente
		while($row=mysql_fetch_row($eje)){
			$sql2="UPDATE sys_sucursales_producto 
						SET estado_presentacion=$row[1],num_presentacion='$row[2]',minimo_surtir='$row[3]',
							estado_suc=$row[4],nombre_presentacion='$row[5]'
							WHERE id_sucursal=$row[6] AND id_producto=$row[0]";
		/*/aqui es una prueba
			mysql_query('rollback',$local);
			mysql_query('rollback',$linea);
			die($sql2);
			return false;
		/*///fin de prueba
			$eje2=mysql_query($sql2,$local);
			if(!$eje2){
				mysql_query('rollback',$local);
				mysql_query('rollback',$linea);
				die('Error al actualizar sucursales producto'."\n".mysql_error($local)."\n".$sql2);
			}
			$s_s_p_b++;//incrementamos contador
		}//fin de while
	}
	echo '<tr style="background:rgba(0,0,225,.5);"><td colspan="4" align="center"><font color="white">SUCURSALES PRODUCTO:</td>'.
	'</tr><tr><td>Por bajar</td><td>'.$n.'</td><td>bajados:</td><td>'
	.$s_s_p_b.'</td></tr>';

/*///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
||																																					   ||
||															Comenzamos sincronización de Listas de precios por borrar								   ||
||																																					   ||
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////*/

$sql="SELECT id_referencia FROM ec_registros_eliminar WHERE tabla='ec_precios_detalle' AND hora_eliminacion>'$ultimaSinc'";
$eje=mysql_query($sql,$linea);
if(!$eje){
	mysql_query('rollback',$local);
	mysql_query('rollback',$linea);
	die("Error al consultar los precios eliminados en linea!!!\n".$sql."\n".mysql_error($linea));
}
$nE=mysql_num_rows($eje);
$cE=0;
if($nE>0){
	while($rw=mysql_fetch_row($eje)){
		$aux="DELETE FROM ec_precios_detalle WHERE id_precio_detalle=$rw[0]";
		$eje2=mysql_query($aux,$local);
		if(!$eje2){
			mysql_query('rollback',$local);
			mysql_query('rollback',$linea);
			die("Error al eliminar precios!!!\n".$aux);
		}
		$cE++;
	}
}
echo '<tr style="background:rgba(0,0,225,.5);"><td colspan="4" align="center"><font color="white">Eliminacion de Precios</td>'.
	'</tr><tr><td>Por eliminar</td><td>'.$nE.'</td><td>Eliminados:</td><td>'.$cE.'</td></tr>';

/*///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
||																																					   ||
||															Comenzamos sincronización de Transferencias								   				   ||
||																																					   ||
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////*/
include('sincronizaTransferencia.php');
//abrimos inicio de sincronizacion de transferencias
	$iniciamos=marcaInicio($local,2);
	if($iniciamos!='actualizado'){
		mysql_query('rollback',$local);
		mysql_query('rollback',$linea);
		die("Error al marcar inicio de sincronizacion de transferencias!!!\n".$iniciamos);
	}
//consultamos ultima sincronizacion respecto a transferencias
	$ultSincTrans=marcaSincronizacion($local,2);
	if($ultSincTrans=='ERROR al extraer tiempo de ultima sincronización'){
		mysql_query('rollback',$local);
		mysql_query('rollback',$linea);
		die("Error al marcar sincronizacion\n".$ultSincTrans);
	}
				//echo'ultima sincronizacion de transferencias: '.$iniciamos;

//consultamos el ultimo id_global registrado en la BD
	$sql="SELECT MAX(id_global) FROM ec_transferencias";
	$eje=mysql_query($sql,$local);
	if(!$eje){
		die("Error al consultar el ultimo id_registrado!!!".mysql_error($local)."\n".$sql);
	}
	$lastID=mysql_fetch_row($eje);
	$last_id=$lastID[0];
//die($last_id);
//Buscamos las transferencias para bajar desde DB en linea
	$sqlBT="SELECT
	/*0*/	id_transferencia,
	/*1*/	id_usuario,
	/*2*/	folio,
	/*3*/	fecha,
	/*4*/	hora,
	/*5*/	id_sucursal_origen,
	/*6*/	id_sucursal_destino,
	/*7*/	observaciones,
	/*8*/	id_razon_social_venta,
	/*9*/	id_razon_social_compra,
	/*10*/	facturable,
	/*11*/	porc_ganancia,
	/*12*/	id_almacen_origen,
	/*13*/	id_almacen_destino,
	/*14*/	id_tipo,
	/*15*/	id_estado,
	/*16*/	id_sucursal,
	/*17*/	es_resolucion
			FROM ec_transferencias
			WHERE (id_sucursal_origen=$suc OR id_sucursal_destino=$suc)
			AND(id_estado=3 AND id_transferencia>$last_id)
			OR (id_estado=6 AND ultima_actualizacion>'$ultSincTrans')";
	//die($sqlBT);
	$consultaBajarT=mysql_query($sqlBT,$linea);//ejecutamos en linea
	if(!$consultaBajarT){
		mysql_query('rollback',$local);
		mysql_query('rollback',$linea);
		die("Error al consultar transferencias pendientes por descargar!!!\n".mysql_error($linea)."\n".$sqlBT);//mandamos error generado en linea
	}
	$bajaTrans=mysql_num_rows($consultaBajarT);
	$transBajadas=0;//contador de transferencias bajadas
	if($bajaTrans>0){
		while($daTB=mysql_fetch_row($consultaBajarT)){
		//actualizamos status de transferencias existentes
			if($daTB[15]==6 && $daTB[17]==0){
				$sql="UPDATE ec_transferencias SET id_estado=6 WHERE id_global=$daTB[0]";
				$eje=mysql_query($sql,$local);
				if(!$eje){
					mysql_query('rollback',$local);
					mysql_query('rollback',$local);
					die("Error al actualizar transferencia a status SALIDA DE TRANSFERENCIA\n".mysql_error($local)."\n".$sql);
				}
			}
			else{
				$creaTra="INSERT INTO ec_transferencias(id_global,id_usuario,folio,fecha,hora,id_sucursal_origen,id_sucursal_destino,observaciones,
								id_razon_social_venta,id_razon_social_compra,facturable,porc_ganancia,id_almacen_origen,id_almacen_destino,
								id_tipo,id_estado,id_sucursal,es_resolucion)
						VALUES('$daTB[0]','$daTB[1]','$daTB[2]','$daTB[3]','$daTB[4]','$daTB[5]','$daTB[6]','$daTB[7]',
								'$daTB[8]','$daTB[9]','$daTB[10]','$daTB[11]','$daTB[12]','$daTB[13]','$daTB[14]','1','$daTB[16]','$daTB[17]')";
				$insTra=mysql_query($creaTra,$local);
				if(!$insTra){
					mysql_query('rollback',$local);
					mysql_query('rollback',$linea);
					die("Error al insertar transferencia localmente!!!\n".mysql_error()."\n".$creaTra);
				}
				$idTransferLocal=mysql_insert_id($local);//consultamos el id de la transferencia local
				echo '<br>Id de transferencia local: '.$idTransferLocal;
				$transBajadas++;
				$genDetalleT="SELECT id_producto_or,
								id_producto_de,
								cantidad,
								id_presentacion,
								cantidad_presentacion,
								cantidad_salida,
								cantidad_salida_pres,
								cantidad_entrada,
								cantidad_entrada_pres,
								resolucion,
								referencia_resolucion
							FROM ec_transferencia_productos
							WHERE id_transferencia=$daTB[0]";
				//	echo $genDetalleT;
				$obtDetalleT=mysql_query($genDetalleT,$linea);
//echo '<tr><td>detalles encontrados:</td><td>'.mysql_num_rows($obtDetalleT).'</td>';

				if($obtDetalleT){
					$detallesTB=0;
					while($ro=mysql_fetch_row($obtDetalleT)){
					//Insertamos el detalle de transferencia localmente
						$sqlDeTrans="INSERT INTO ec_transferencia_productos
										SET	
											id_transferencia=$idTransferLocal,
											id_producto_or='".$ro[0]."',
											id_producto_de='".$ro[1]."',
											cantidad='".$ro[2]."',
											id_presentacion='".$ro[3]."',
											cantidad_presentacion='".$ro[4]."',
											cantidad_salida='".$ro[5]."',
											cantidad_salida_pres='".$ro[6]."',
											cantidad_entrada='".$ro[7]."',
											cantidad_entrada_pres='".$ro[8]."',
											resolucion='".$ro[9]."',
											referencia_resolucion='".$ro[10]."'";
						//echo'<br>'.$sqlDeTrans;
						$bajaDeTrans=mysql_query($sqlDeTrans,$local);
						if(!$bajaDeTrans){
							mysql_query('rollback',$local);
							mysql_query('rollback',$linea);
							die('error');
						}
							$detallesTB++;
					}//finaliza while
//echo'<td>Detalles bajados:</td><td>'.$detallesTB.'</td></tr>';
					}
				$actSt="UPDATE ec_transferencias SET id_estado=3 WHERE id_global=$daTB[0]";//actualizamos status para activar trigger
				$ejeActSt=mysql_query($actSt,$local);
				if(!$ejeActSt){
					mysql_query('rollback',$local);
					mysql_query('rollback',$linea);
					die("Error al insertar transferencia localmente!!!\n".mysql_error()."\n".$actSt);
				}
			//actualizamos a terminada si la transferencia es debido a una resolucion
				if($daTB[15]==6 AND $daTB[17]==1){
					$ax="UPDATE ec_transferencias SET id_estado=6 WHERE id_global=$daTB[0]";
					$act=mysql_query($ax,$local);
					if(!$act){
						mysql_query('rollback',$local);
						mysql_query('rollback',$linea);
						die("Error al actualizar la tranferecnia por resolucion a TERMINADA!!!\n".mysql_error()."\n".$ax);
					}
				}
		}
	}
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//Buscamos las transferencias pendientes de subir
	$sqlT="SELECT id_transferencia,id_global,id_estado FROM ec_transferencias
			WHERE (id_estado=3 AND id_global=0)
			OR (id_estado=6 AND ultima_sincronizacion >'$ultSincTrans')
			AND id_transferencia >0";
	$ejecutaT=mysql_query($sqlT,$local);
	if($ejecutaT){
		$nT=mysql_num_rows($ejecutaT);
		$transfer=0;
		if($nT>0){
			while($datosT=mysql_fetch_row($ejecutaT)){
				if($datosT[2]==6){
				//actualizamos la transferencia como TERMINADA en linea
					$generaT="UPDATE ec_transferencias SET id_estado=6 WHERE id_transferencia=$datosT[1]";
					$actStaT=mysql_query($generaT,$linea);
					if(!$actStaT){
						mysql_query('rollback',$local);
						mysql_query('rollback',$linea);
						die('La transferencia no pudo ser actualizada a status TERMINADA en linea!!!');
					}
				}else{
					$res=subeTransfer($datosT[0]);
					if($res=='ok'){
						//die('ok');
					}else{
						mysql_query('rollback',$local);
						mysql_query('rollback',$linea);
						die("Error al subir transferencia en Linea");
					}
				}
				$transfer++;
			}//finaliza while
		}//finaliza if $nT>0
	}//fin if($ejecutaT)
echo '<tr style="background:rgba(0,0,225,.5);"><td colspan="4" align="center"><font color="white">Transferencias</font></td></tr>';
echo'<tr><td>Transferencias por bajar:</td><td>'.$bajaTrans.'</td>';
echo '<td>Transferencias bajadas:</td><td>'.$transBajadas.'</td></tr>';
echo'<tr><td>Transferencias por subir:</td><td>'.$nT.'</td>';
echo '<td>Transferencias subidas:</td><td>'.$transfer.'</td></tr>';
//cerramos sincronizacion general
$dato=getDateTime1($linea);	
cierraTrans($local,2,$dato);
cierra($local,$linea);
?>

</table>
</center>
</div>
<br><br>	
</div>