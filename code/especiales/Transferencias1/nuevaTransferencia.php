<?php
	require('../../../conectMin.php');//incluimos libreria de conexion
	require('consultaTransferencia.php');//incluimos el archivo que contiene las consultas de la transferencia
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf8" />
<link href="css2/estilo.css" rel="stylesheet" type="text/css"  media="all" />
<link href="css2/fontello.css" rel="stylesheet" type="text/css"  media="all" />
<script language="JavaScript" src="js/scriptFunciones.js"></script>
<script language="JavaScript" src="js/jquery-1.10.2.min.js"></script>
<!--Modificación Oscar 12.11.2018 para importar librería que convienrte texto en password-->
	<script type="text/javascript" src="../../../js/passteriscoByNeutrix.js"></script>
<!--Fin de cambio Oscar 12.11.2018-->
<style type="text/css">
	input[type=number]::-webkit-inner-spin-button, 
	input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none; 
    margin: 0; 
    -moz-appearance: textfield;
}
</style>
<!--////////////////////COMIENZA CUERPO DE PANTALLA\\\\\\\\\\\\\\\\\\\-->
<body onload="enfocar();">
<center>
<!--Ventana para verificacion de presentaciones-->
<div id='cargandoPres' style="width:100%;height:100%;position:absolute;background:rgba(0,0,0,.8);display:none;z-index:100;">
	<center><br><br><br><br><br><br>
		<p style="border:0px solid;width:60%;height:200px;padding:30px; background:rgba(255,0,0,.5);color:white;font-size:30px;" id="mensaje_pres">
		</p>
		<p>
		</p>
	</center>
</div>

<!--div de titulo-->
<div id="general" style="width:100%;height:85%; background-image:url(img/bg8.jpg);">
<div id="resultado" style="border:0"></div>
<div id="titulo" style="width:100%;background:#83B141;">
	<table width="100%">
		<tr>
			<td width="50%">	
				<div style="float:left;width:100%;">
					<span align="left" style="padding:5px;">
						<font size="5px" color="#000000"><i class="icon-telegram"></i><?php echo /*$n1.' hacia '.$n2*/$datos_encabezado[0];?></font>
					</span>	
					<table style="float:right;">
						<tr>
							<td width="50%">
								<input type="button" value="<?php if($status==1){echo 'Guardar Cambios';}else{echo 'Guardar';}?>" id="confirma" 
								onclick="<?php if($status==1){ echo 'desenfocar(2);';}else{echo 'desenfocar(1);';}?>;" <?php if($status>1){echo 'style="display:none;"';} ?>/>
								<!--
						<?php //if($status==1){echo 'actualizar();';}else{ echo 'confirmar();';} ?>" <?php //if($status>1){echo 'disabled';}?>
								-->
								<!--src="../../../img/confirma.png"-->
							</td>
							<td width="50%"></td>
						</tr>
					</table>
					</span>
				</div>
			</td>
			<td width="50%">
		<!--div de buscador-->
				<div id="titulo" style="text-align:right;float:right;width:100%;">
					<p style="padding:5px" align="center">
						<table width="100%" border="0">
							<tr>
								<td width="60%" align="center">
									<p style="color:white;font-size:120%;">Producto:
									<input type="text" style="padding:8px; width:70%; border-radius:5px;z-index:100;" onkeyup="buscar(event);" 
									id="buscador" class="buscador" />
									</p>
									<input type="hidden" id="auxBusqueda">
								</td>
								<td width="20%" align="center">
									<span style="color:white;font-size:120%;">Cantidad</span>
									<input type="text" style="width:40px; padding:8px; border-radius:5px;" onkeyup="accionar(event);" placeholder="cant." id="agrega">
								</td>
								<td width="10%" align="center">
									<a href="javascript:agregarFila();"><font size="5px" color="#000000"><i class="icon-plus-circled"></i></font></a>
								</td>
								<td width="10%" align="left" valign="bottom">
									<button style="color:gray;background:white;padding:5px;border-radius:50%;font-size:20px;"
									onclick="accionar(event);">
										<b>+</b>
									</button>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<div id="resBus" class="lista_producto" style="display:none; position:fixed; z-index:3;width:40%;border: 2px solid blue; 
									background:white;"></div>  
  		               				<input type="hidden" name="id_productoN" value="" />
								</td>
							</tr>
						</table>
					</p>
				</div>
			</td>
		</tr>
	</table>
</div>
<BR>
<!--div de tabla de transferencia-->
<div id="tabla" style="width:98%;background:rgba(0,0,0,.3);border-radius:5px;" onclick="ocultaResultados();">
<table  width="100%" class="rellenar" id="encabezado" style="border-radius:10px;"><!--COMIENZA TABLA-->
<!--definimos encabezados-->
    <thead>
    <tr>
    	<th colspan="9" bgcolor="#FFFFFF" align="left"></th>
	</tr>	
		<th width="10%" height="40px">Orden</th>
		<th width="40%">Descripción</th>
		<th width="11%">I. Origen</th>
		<th width="11%">I. Destino</th>
		<th width="10%">E. Máxima</th>
		<!--<th width="10%">Present.</th>-->

		<th width="10%">Pedir</th>
		<!--<th width="10%">Total (piezas)</th>-->
		<th width="8%">Quitar</th>
	</thead>
   </table>
    <div id="contenidoTabla" style="width:101%;height:360px;"><!--DIV QUE CONTIENE DATOS DE TABLA    onscroll="recorre(1);-->
    <table class="rellenar" id="transferencias" border="0">
	<tbody>
	<?php
/*******PRUEBA*******/
	$producto_prueba=2084;
/*************/
//sacamos el año desde mysql

	$sql="SELECT YEAR(CURRENT_DATE)";
	$eje_fcha=mysql_query($sql)or die("Error al consultar año actual!!!\n\n".mysql_error());
	$year_act=mysql_fetch_row($eje_fcha);
	$act_year=$year_act[0];
//llenamos tabla de datos	
	while($row=mysql_fetch_assoc($res)){//mientras se encuentren resultados en el arreglo de la consulta;
	/**/
/*Proceso de la ración*/
	if($row['raciona']==1 && $id_tipo!=2){
	//ponemos el producto con stock bajo en matriz
		$sql="UPDATE sys_sucursales_producto SET stock_bajo=1 WHERE id_producto={$row['ID']}";/*id_sucursal=1 AND */ 
		$eje_auxiliar_1=mysql_query($sql)or die("Error al marcar en stock bajo los productos para las sucursales!!!\n\n".$sql);

	/*sacamos la presentación del producto si es que tiene*/
		$sql="SELECT IF(pp.id_producto_presentacion IS NULL,1,pp.cantidad) 
			FROM ec_productos p
			LEFT JOIN ec_productos_presentaciones pp ON p.id_productos=pp.id_producto
			WHERE p.id_productos={$row['ID']}";
		$eje_presentacion=mysql_query($sql)or die("Error al consultar la presentación del producto!!!<br>".$sql."<br>".mysql_error());
		$present=mysql_fetch_row($eje_presentacion);
		$presentacion=$present[0];

	/*sacamos el inventario de los almacenes principales y ventas totales del año actual*/
		$sql="SELECT
				aux.id_producto,
				(IF(aux.inventarioAlmacenesPrincipales IS NULL,0,aux.inventarioAlmacenesPrincipales))/{$presentacion} AS inventarioAlmacenesPrincipales,
				IF(aux.ventas_totales IS NULL,0,aux.ventas_totales) AS ventas_totales
			FROM(
				SELECT
					p.id_productos as id_producto,
					SUM(IF(alm.es_almacen=1,(md.cantidad*tm.afecta),0)) AS inventarioAlmacenesPrincipales,
					SUM(IF(alm.es_almacen=1 AND tm.id_tipo_movimiento=2 AND alm.es_externo=0 AND ma.fecha like '%$act_year%',
						md.cantidad,
						0
						)
					) AS ventas_totales
				FROM ec_productos p
				LEFT JOIN ec_movimiento_detalle md ON p.id_productos=md.id_producto
				LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento=ma.id_movimiento_almacen
				LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
				LEFT JOIN ec_almacen alm ON ma.id_almacen=alm.id_almacen
				LEFT JOIN sys_sucursales s ON alm.id_sucursal=s.id_sucursal
				LEFT JOIN sys_sucursales_producto sp on s.id_sucursal=sp.id_sucursal AND p.id_productos=sp.id_producto
				WHERE s.activo=1 
				AND s.id_sucursal>0
				AND sp.estado_suc=1
				AND p.id_productos={$row['ID']}
			)aux";
//die($sql);
			$eje_auxiliar=mysql_query($sql)or die("Error al consultar valores base para racionar la transferencia!!!<br>".mysql_error()."<br>".$sql);
			$auxiliar=mysql_fetch_row($eje_auxiliar);

		/*sacamos el porcentaje de ventas de la sucursal y el inventario actual de la misma*/
			$sql="SELECT 
					aux.id_sucursal,
					IF(aux.racion is null,0,aux.racion),
					IF(aux.inventarioAlmacenPrincipalPorSucursal IS NULL,0,aux.inventarioAlmacenPrincipalPorSucursal) as inventarioAlmacenPrincipalPorSucursal
				FROM(
					SELECT
						s.id_sucursal,
						( (SUM(IF(ma.id_movimiento_almacen IS NOT NULL AND /*ma*/alm.id_sucursal=s.id_sucursal AND alm.es_externo=0 
							AND tm.id_tipo_movimiento=2 AND ma.fecha like '%$act_year%',
								md.cantidad,
								0)
							) 
						)/{$auxiliar[2]}/*ventas totales*/)*{$auxiliar[1]}/*inventario almacen principal*/ AS racion,/*total de ventas del año actual*/
						SUM(IF(alm.es_almacen=1 AND ma.id_sucursal=s.id_sucursal,(md.cantidad*tm.afecta),0)) as inventarioAlmacenPrincipalPorSucursal
					FROM ec_productos p 
					LEFT JOIN ec_movimiento_detalle md on p.id_productos=md.id_producto
					LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento=ma.id_movimiento_almacen
					LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
					LEFT JOIN ec_almacen alm ON ma.id_almacen=alm.id_almacen
					LEFT JOIN sys_sucursales s ON alm.id_sucursal=s.id_sucursal
					LEFT JOIN sys_sucursales_producto sp_1 ON s.id_sucursal=sp_1.id_sucursal AND sp_1.id_producto={$row['ID']}
					WHERE p.id_productos={$row['ID']}
					AND sp_1.estado_suc=1/*habilitado en la sucursal*/
					AND s.id_sucursal>1/*sucursal mayor a matriz*/
					GROUP BY s.id_sucursal/*agrupamos por sucursal*/
				)aux
				GROUP BY aux.id_sucursal";
			/*insertamos la ración en casa sucursal*/
//die($sql);
			$eje_auxiliar_1=mysql_query($sql)or die("Error al calcular la primera ración de cada sucursal!!!<br>".mysql_error()."<br>".$sql);

			while($row_aux=mysql_fetch_row($eje_auxiliar_1)){

				$sql="UPDATE sys_sucursales_producto SET racion_1=ROUND(({$row_aux[1]})-({$row_aux[2]}/{$presentacion})) * {$presentacion}  
						WHERE id_sucursal={$row_aux[0]} AND id_producto={$row['ID']}";
				$eje_auxiliar_2=mysql_query($sql)or die("Error al insertar raciones!!!<br>".mysql_error()."<br>".$sql);
/*if($row['ID']==$producto_prueba){
				echo $sql.'<br><br>';
}*/
			}
//die('');

/************************SEGUNDA RACIÓN**********************************/
	/*sacamos el inventario de los almacenes principales y ventas totales del año actual*/
		$sql="SELECT
				aux.id_producto,
				(IF(aux.inventarioAlmacenesPrincipales IS NULL,0,aux.inventarioAlmacenesPrincipales))/{$presentacion} AS inventarioAlmacenesPrincipales,
				IF(aux.ventas_totales IS NULL,0,aux.ventas_totales) AS ventas_totales
			FROM(
				SELECT
					p.id_productos as id_producto,
					SUM(IF(alm.es_almacen=1,(md.cantidad*tm.afecta),0)) AS inventarioAlmacenesPrincipales,
					SUM(IF(alm.es_almacen=1 AND tm.id_tipo_movimiento=2 AND alm.es_externo=0 AND ma.fecha like '%$act_year%',
						md.cantidad,
						0
						)
					) AS ventas_totales
				FROM ec_productos p
				LEFT JOIN ec_movimiento_detalle md ON p.id_productos=md.id_producto
				LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento=ma.id_movimiento_almacen
				LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
				LEFT JOIN ec_almacen alm ON ma.id_almacen=alm.id_almacen
				LEFT JOIN sys_sucursales s ON alm.id_sucursal=s.id_sucursal
				LEFT JOIN sys_sucursales_producto sp on s.id_sucursal=sp.id_sucursal AND p.id_productos=sp.id_producto
				WHERE s.activo=1 
				AND s.id_sucursal>0
				AND sp.estado_suc=1
				AND (sp.racion_1>0 OR sp.id_sucursal=1)
				AND p.id_productos={$row['ID']}
			)aux";
//die($sql);
			$eje_auxiliar=mysql_query($sql)or die("Error al consultar valores base para racionar la transferencia!!!<br>".mysql_error()."<br>".$sql);
			$auxiliar=mysql_fetch_row($eje_auxiliar);

		/*sacamos el porcentaje de ventas de la sucursal y el inventario actual de la misma*/
			$sql="SELECT 
					aux.id_sucursal,
					IF(aux.racion is null,0,aux.racion),
					IF(aux.inventarioAlmacenPrincipalPorSucursal IS NULL,0,aux.inventarioAlmacenPrincipalPorSucursal) as inventarioAlmacenPrincipalPorSucursal
				FROM(
					SELECT
						s.id_sucursal,
						( (SUM(IF(ma.id_movimiento_almacen IS NOT NULL AND ma.id_sucursal=s.id_sucursal AND alm.es_externo=0 
							AND tm.id_tipo_movimiento=2 AND ma.fecha like '%$act_year%',
								md.cantidad,
								0)
							) 
						)/{$auxiliar[2]})*{$auxiliar[1]} AS racion,/*total de ventas del año actual*/
						SUM(IF(alm.es_almacen=1 AND ma.id_sucursal=s.id_sucursal,(md.cantidad*tm.afecta),0)) as inventarioAlmacenPrincipalPorSucursal
					FROM ec_productos p 
					LEFT JOIN ec_movimiento_detalle md on p.id_productos=md.id_producto
					LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento=ma.id_movimiento_almacen
					LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
					LEFT JOIN ec_almacen alm ON ma.id_almacen=alm.id_almacen
					LEFT JOIN sys_sucursales s ON alm.id_sucursal=s.id_sucursal
					LEFT JOIN sys_sucursales_producto sp_1 ON s.id_sucursal=sp_1.id_sucursal AND sp_1.id_producto={$row['ID']}
					WHERE p.id_productos={$row['ID']}
					AND sp_1.estado_suc=1/*habilitado en la sucursal*/
					AND s.id_sucursal>1/*sucursal mayor a matriz*/
					AND (sp_1.racion_1>0 OR sp_1.id_sucursal=1)
					GROUP BY s.id_sucursal/*agrupamos po sucursal*/
				)aux
				GROUP BY aux.id_sucursal";
			/*insertamos la ración en casa sucursal*/
//			die($sql);
			$eje_auxiliar_1=mysql_query($sql)or die("Error al calcular la primera ración de cada sucursal!!!<br>".mysql_error()."<br>".$sql);

			while($row_aux=mysql_fetch_row($eje_auxiliar_1)){

				$sql="UPDATE sys_sucursales_producto SET racion_2=ROUND(({$row_aux[1]})-({$row_aux[2]}/{$presentacion})) * {$presentacion}  
						WHERE id_sucursal={$row_aux[0]} AND id_producto={$row['ID']}";
				$eje_auxiliar_2=mysql_query($sql)or die("Error al insertar raciones!!!<br>".mysql_error()."<br>".$sql);
/*if($row['ID']==$producto_prueba){
				echo $sql.'<br><br>';
}*/				//echo $sql.'<br><br>';
			}
	
//die('ok');
/***************************TERCERA RACIÓN*******************************/

	/*sacamos el inventario de los almacenes principales y ventas totales del año actual*/
		$sql="SELECT
				aux.id_producto,
				(IF(aux.inventarioAlmacenesPrincipales IS NULL,0,aux.inventarioAlmacenesPrincipales))/{$presentacion} AS inventarioAlmacenesPrincipales,
				IF(aux.ventas_totales IS NULL,0,aux.ventas_totales) AS ventas_totales
			FROM(
				SELECT
					p.id_productos as id_producto,
					SUM(IF(alm.es_almacen=1,(md.cantidad*tm.afecta),0)) AS inventarioAlmacenesPrincipales,
					SUM(IF(alm.es_almacen=1 AND tm.id_tipo_movimiento=2 AND alm.es_externo=0 AND ma.fecha like '%$act_year%',
						md.cantidad,
						0
						)
					) AS ventas_totales
				FROM ec_productos p
				LEFT JOIN ec_movimiento_detalle md ON p.id_productos=md.id_producto
				LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento=ma.id_movimiento_almacen
				LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
				LEFT JOIN ec_almacen alm ON ma.id_almacen=alm.id_almacen
				LEFT JOIN sys_sucursales s ON alm.id_sucursal=s.id_sucursal
				LEFT JOIN sys_sucursales_producto sp on s.id_sucursal=sp.id_sucursal AND p.id_productos=sp.id_producto
				WHERE s.activo=1 
				AND s.id_sucursal>0
				AND sp.estado_suc=1
				AND (sp.racion_2>0 OR sp.id_sucursal=1)
				AND p.id_productos={$row['ID']}
			)aux";
//die($sql);
			$eje_auxiliar=mysql_query($sql)or die("Error al consultar valores base para racionar la transferencia!!!<br>".mysql_error()."<br>".$sql);
			$auxiliar=mysql_fetch_row($eje_auxiliar);

		/*sacamos el porcentaje de ventas de la sucursal y el inventario actual de la misma*/
			$sql="SELECT 
					aux.id_sucursal,
					IF(aux.racion is null,0,aux.racion),
					IF(aux.inventarioAlmacenPrincipalPorSucursal IS NULL,0,aux.inventarioAlmacenPrincipalPorSucursal) as inventarioAlmacenPrincipalPorSucursal
				FROM(
					SELECT
						s.id_sucursal,
						( (SUM(IF(ma.id_movimiento_almacen IS NOT NULL AND ma.id_sucursal=s.id_sucursal AND alm.es_externo=0 
							AND tm.id_tipo_movimiento=2 AND ma.fecha like '%$act_year%',
								md.cantidad,
								0)
							) 
						)/{$auxiliar[2]})*{$auxiliar[1]} AS racion,/*total de ventas del año actual*/
						SUM(IF(alm.es_almacen=1 AND ma.id_sucursal=s.id_sucursal,(md.cantidad*tm.afecta),0)) as inventarioAlmacenPrincipalPorSucursal
					FROM ec_productos p 
					LEFT JOIN ec_movimiento_detalle md on p.id_productos=md.id_producto
					LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento=ma.id_movimiento_almacen
					LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
					LEFT JOIN ec_almacen alm ON ma.id_almacen=alm.id_almacen
					LEFT JOIN sys_sucursales s ON alm.id_sucursal=s.id_sucursal
					LEFT JOIN sys_sucursales_producto sp_1 ON s.id_sucursal=sp_1.id_sucursal AND sp_1.id_producto={$row['ID']}
					WHERE p.id_productos={$row['ID']}
					AND sp_1.estado_suc=1/*habilitado en la sucursal*/
					AND s.id_sucursal>1/*sucursal mayor a matriz*/
					AND (sp_1.racion_2>0 OR sp_1.id_sucursal=1)
					GROUP BY s.id_sucursal/*agrupamos po sucursal*/
				)aux
				GROUP BY aux.id_sucursal";
			/*insertamos la ración en casa sucursal*/
//			die($sql);
			$eje_auxiliar_1=mysql_query($sql)or die("Error al calcular la primera ración de cada sucursal!!!<br>".mysql_error()."<br>".$sql);

			while($row_aux=mysql_fetch_row($eje_auxiliar_1)){

				$sql="UPDATE sys_sucursales_producto SET racion_3=ROUND( (ROUND( ({$row_aux[1]}) - ({$row_aux[2]}/{$presentacion}) ))*{$presentacion} )
						WHERE id_sucursal={$row_aux[0]} AND id_producto={$row['ID']}";
				$eje_auxiliar_2=mysql_query($sql)or die("Error al insertar raciones!!!<br>".mysql_error()."<br>".$sql);
//if($row['ID']==$producto_prueba){
//				echo $sql.'<br><br>';
//}
//				echo $sql.'<br><br>';
			}
	/*comparamos la suma de las raciones*/
		$sql="SELECT 
				aux.total_raciones,
				SUM(IF(ma.id_movimiento_almacen IS NULL OR ma.id_almacen!=1,0,(md.cantidad*tm.afecta))) as inventarioMatriz
			FROM(
				SELECT
					SUM(racion_3) AS total_raciones  
				FROM sys_sucursales_producto 
				WHERE id_producto={$row['ID']}
			)aux
			JOIN ec_productos p ON p.id_productos={$row['ID']}
			LEFT JOIN ec_movimiento_detalle md ON md.id_producto=p.id_productos
			LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento=ma.id_movimiento_almacen
			LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento=tm.id_tipo_movimiento
			WHERE p.id_productos={$row['ID']}";

		$eje_verif=mysql_query($sql)or die("Error al consultar piezas faltantes en las raciones!!!<br>".mysql_error()."<br>".$sql);
		$row_verif=mysql_fetch_row($eje_verif);
		if($row_verif[0]<$row_verif[1]){
			$diferencia=$row_verif[1]-$row_verif[0];
		//sacamos la sucursal que mas vende
			$sql="SELECT id_sucursal FROM sys_sucursales_producto WHERE id_producto={$row['ID']} ORDER BY racion_3 DESC LIMIT 1";
			$eje_suc_max=mysql_query($sql)or die("Error al consultar la sucursal que más vende!!!<br>".mysql_error()."<br>".$sql);
			$suc_max=mysql_fetch_row($eje_suc_max);
		/**/
			$sql="UPDATE sys_sucursales_producto SET racion_3=(racion_3+{$diferencia}) WHERE id_sucursal={$suc_max[0]} AND id_producto={$row['ID']}";
			$eje_restante=mysql_query($sql)or die("Error al asignar las piezas restantes a la sucursal que más vende!!!<br>".mysql_error()."<br>".$sql);
		}

	//consultamos la cantidad racionada des´pupes del proceso para cambiar el valor en la transferencia
		$sql="SELECT racion_3 FROM sys_sucursales_producto WHERE id_sucursal={$destino} AND id_producto={$row['ID']}";
		$eje_auxiliar_1=mysql_query($sql)or die("Error al consultar la ración del producto para la transferencia!!!\n\n".$sql);
		$row_aux=mysql_fetch_row($eje_auxiliar_1);
		$row['CantidadPresentacion']=$row_aux[0]/$presentacion;
		//die("enttra!!!".$row['ID']);
	}
/*fIN DE IMPLEMENTACIÓN PARA RACIONAR*/
//die('');

		$invalida="";
		$titulo_caja_txt='';//variabe de tooltip que indica que el producto entró en ración
	//if($row['CantidadPresentacion']>0){
	//implementacion de Oscar 21.02.2017
		if($row['InvOr']<$row['cantidadSurtir'] || $row['raciona']==1){
			$resalta_rojo='style="color:red;"';
			if($row['raciona']==1){
				$titulo_caja_txt=' title="Producto racionado entre sucursales; si requiere más solicite autorización" ';
			}
		}else{
			$resalta_rojo="";
		}
		if($id_tipo==6 AND $row['InvOr']<=0){//$row['InvOr']<0 $row['cantidadSurtir']<=0 or 
				//echo'no';
			}else{
				if(isset($row['valido'])){
		//			echo 'valido: '.$row['valido'];
					$aux=$row['valido'];
					if($aux==1){

						$validos++;	
					}else if($aux==0){
		//				echo 'here';
						$invalida="";//aqui sehabilitamos(por ahora no se usa)
					}
				}	
				$c++;//incrementamos contador
				if($c%2==0){
					echo '<tr id="fila'.$c.'" bgcolor="#FFFF99" class="filas">';//onclick="resaltar('.$c.');"
				}else{	
					echo '<tr id="fila'.$c.'" bgcolor="#CCCCCC" class="filas">';//onclick="resaltar('.$c.');"
				}
//if($row['CantidadPresentacion']>0){
	?>

	<!--Orden de lista-->
		<td align="right" width="10%" onclick="<?php echo 'resaltar('.$c.');';?>" id="<?php echo '0_'.$c;?>" <?php echo $resalta_rojo;?>><?php echo $row['ordenLista'];?></td>
		
	<!--Implementación Oscar 26.02.2019 para agregar el código alfanumérico (oculto) en la transferencia-->
		<td style="display:none;" id="<?php echo 'clave_'.$c;?>"><?php echo $row['clave'];?></td>
	<!--Fin de cambio Oscar 26.02.2019-->

    <!--Id del producto (celda oculta)-->
        <td id="<?php echo '1_'.$c;?>" style="display:none;"><?php echo $row['ID'];?></td>
	<!--Nombre del producto-->
		<td align="left" width="40%" title="<?php echo 'Clave: '.$row['clave'];?>" id="<?php echo '2_'.$c;?>" onclick="<?php echo 'resaltar('.$c.');';?>" <?php echo $resalta_rojo;?>><?php echo $row['Nombre'];?></td>
    <!--Inventario Almacén Origen-->
		<td align="right" width="11%" id="<?php echo '3_'.$c;?>" <?php echo $resalta_rojo;?>><?php echo $row['InvOr'];?></td>
    <!--Inventario Almacén Destino-->
		<td align="right" width="11%" id="<?php echo '4_'.$c;?>" <?php echo $resalta_rojo;?> onclick="<?php echo 'resaltar('.$c.');';if($user_sucursal==$destino){echo 'editaCelda(4,'.$c.');';}?>"><?php echo $row['InvDes'];?></td>
	<!--Estacionalidad máxima-->
		<td width="10%" align="right" id="<?php echo '5_'.$c;?>"  <?php echo $resalta_rojo;?> onclick="<?php if($user_sucursal==$destino){echo 'editaCelda(5,'.$c.');';}?>"><?php echo $row['maximo'];?></td>
	<!--Cantidad por pedir-->
	<td align="center" width="10%">
        	<input type="number" onkeydown="detiene(event);" class="pedir" id="<?php echo '6_'.$c;?>" value="<?php if($id_tipo==6){echo $row['InvOr'];}else{echo $row['CantidadPresentacion']*$row['Presentacion'];}?>"
     		onkeyup="<?php echo 'validar(event,'.$c.',2);operacion(event,'.$c.');';?>"
     		onclick="<?php echo 'resaltar('.$c.');';?>" <?php if($status!=1 && $status!=''){echo ' disabled ';}echo $invalida;?> <?php echo $resalta_rojo.' '.$titulo_caja_txt;?>/>
        </td>
   	<!--Id del detalle de la estacionalidad (celda oculta)-->
        <td  id="<?php echo '7_'.$c; ?>" style="display:none;"><?php echo $row['idEstProd'];?></td>
    <!--cantidad de la presentación-->
    	<td id="<?php echo '8_'.$c; ?>" style="display:none;"><?php echo $row['Presentacion'];?></td>
<!--Implementación Oscar 24.03.2019 para tener indicador de producto por racionar-->
    	<td id="<?php echo '9_'.$c; ?>" style="display:none;"><?php echo $row['raciona'];?></td>
<!--Fin de cambio Oscar 24.03.2019-->
<!--Implementación Oscar 16.04.2019 para agregar al csv ubicación de matriz y de sucursal destino-->
    	<td id="<?php echo '10_'.$c; ?>" style="display:none;"><?php echo $row['ubicacion_matriz'];?></td>
    	<td id="<?php echo '11_'.$c; ?>" style="display:none;"><?php echo $row['ubicacion_almacen_sucursal'];?></td>
<!--Fin de cambio Oscar 16.04.2019-->
    	
    <!--opción para eliminar la fila-->
		<td align="center" width="8%">
			<a href="<?php if($status==6){echo'javascript:restringe();';}else{echo 'javascript:eliminarFila('.$c.',1);';}?>" style="text-decoration:none;">
				<font color="#FF0000" size="+3"><i class="icon-cancel-circled"></i></font><font size="-1">&nbsp;</font><font size="-1"></font>
			</a>    
		</td>
	<?php
		 echo'</tr>';
		 //print_r($extae);
		}//fin de else
//}/*Fin de cambio Oscar 28.04.2019 para no tomar los valores que no piden*/
	}//fin de while	
//sacamos almacen principal de sucursal actual
?>

<!--Declaramos variables ocultas en base a las variables recibidas por POST-->
<input type="hidden" value="<?php echo $origen;?>" id="orig"/>
<input type="hidden" value="<?php echo $destino;?>" id="dest"/>
<input type="hidden" value="<?php echo $al_origen;?>" id="almOrigen"/>
<input type="hidden" value="<?php echo $al_destino;?>" id="almDestino"/>
<input type="hidden" value="<?php echo $id_tipo;?>" id="tipo"/>
<input type="hidden" value="<?php if($c!=0 && $c!=null){echo $c;}else{ echo '0';}?>" id="cont"/>
<input type="hidden" value="" id="transfe"/>
<input type="hidden" value="0" id="mov">
<!--echo 'valor de c:'.$c;-->
<!--Finalizamos declaracion de variables ocultas-->
</tbody>
</div>
</table>
</div>
<br/>
<div>
<table width="100%" border="1">
<?php 
//MOSTRAMOS CONTEO DE REGISTROS VALIDOS.
	echo '<font color="black">Total:<b id="cont_visual_1">'.$c.'</b><br>validos:<b id="cont_visual_2">'.$validos.'</b></font><br>';
	//if($status==1){
		echo '<input type="hidden" value="0" id="modificaciones" disabled>';
		if(isset($status)){	
			$sql="SELECT nombre FROM ec_estatus_transferencia WHERE id_estatus='$status'";
            $ejecuta=mysql_query($sql)or die(mysql_error());
            
            if($status==6){
                $permisos=" (No Modificable)";
            }else{
                $permisos=" (modificable)";
            }
        	$nomStatus=mysql_fetch_row($ejecuta);
			echo '<font color="white">Status:'.$nomStatus[0].$permisos.'</font>';
		}
?>
<!--implementación de Oscar 28.05.2018 para exportación de Tranferencias-->
	<p align="right" style="top:-65;position:relative;color:white;padding:8px;border:0;width:100%;right:30px;">
		<table style="position:absolute;right:5%;bottom:12%;">
			<tr>
				<td>
					<a href="javascript:exportaTransferencia('formato_limpio');" style="text-decoration:none;color:white;">
						<img src="../../../img/especiales/exportaCSV1.png" width="40px"><br><b style="color:black;">Exportar<br>Formato<br>en Limpio</b>
					</a>
				</td>
				<td>
					<a href="javascript:exportaTransferencia();" style="text-decoration:none;color:white;">
						<img src="../../../img/especiales/exportaCSV1.png" width="40px"><br><b style="color:black;">Exportar</b>
					</a>
				</td>
				<td>
					<a href="javascript:exportaTransferencia('orden_almacen');" style="text-decoration:none;color:white;<?php if(!isset($idTransfer)){echo 'display:none;';}?>">
						<img src="../../../img/especiales/exportaCSV1.png" width="40px"><br><b style="color:black;">Exportar<br>en Orden</b>
					</a>					
				</td>
				<td id='espacio_importa' <?php if($id_tipo!=5){echo 'style="display:none;"';}?>>
					<a href="javascript:exportaTransferencia(1);" style="text-decoration:none;color:white;">
						<img src="../../../img/especiales/importaCSV1.png" width="40px"><br><b style="color:black;">Importar</b>
					</a>
				</td>
				<td>
					<form class="form-inline">
						<input type="file" id="imp_csv_prd" style="display:none;">
						<p class="nom_csv">
							<input type="text" id="txt_info_csv" style="display:none;" disabled>
						</p>
					</form>
					<td>
						<button type="submit" id="submit-file" style="display:none;background_transparent;" class="bot_imp">
							<img src="../../../img/especiales/sube.png" height="40px;">
							<br><btyle="color:black;">Cargar Archivo</b>
						</button>
					</form>
					</td>
				</td>
			</tr>
		</table>
	</p>
<!--Fin de cambio-->

</table>
</div>
</div><!--FIN DE DIV GENERAL-->
</center>

<!--implementación Oscar 29.05.2018 para exportación en Excel-->
	<form id="TheForm" method="post" action="ajax/modificaTransferencia.php" target="TheWindow">
			<input type="hidden" id="fl" name="fl" value="1" />
			<input type="hidden" id="datos" name="datos" value=""/>
	</form>
<!--Fin de cambio 29.05.2018-->
<?php include('pieDePagina.php');?>
</body>
</html>

<!--implementación Oscar 28.09.2018 para importación de csv-->
	<script language="JavaScript" src="js/papaparse.min.js"></script>
<!--Fin de cambio-->
