<?php
	include('../../../conect.php');//incluimos libreria de conexion
	include( 'ajax/functions.php' );
	extract($_GET);
	if($sucursal_id==-1){
		$sucursal=1;	
	}else if($sucursal_id>=1){
		$sucursal=$sucursal_id;
	}
	if(isset($id_suc_adm)){
		$id_suc_adm=base64_decode($id_suc_adm);//decodificamos la variable
		$sucursal=$id_suc_adm;
	}
		$WHERE=' AND ma.id_sucursal='.$sucursal;
?>
<script language="JavaScript" src="../../../js/jquery-1.10.2.min.js"></script>
<script language="Javascript" src="js/funcionesAjusteInvntario.js"></script>
<link rel="stylesheet" type="text/css" href="../../../css/bootstrap/css/bootstrap.css">
<link rel="stylesheet" type="text/css" href="css/AjusteInventarioStyles.css">
<div id="global" onclick="oculta_res_busc();">
<center>	
	<div id="emergente" class="emerge">
		<center>
			<div style="background:rgba(0,0,0,.7);border:1px solid red;top:170px;position:relative;width:60%;height:300px;border-radius:15px;">
				<div id="cont_vta_emerge">
					<p id="info_emerge" style="color:white;font-size:25px;" align="center">
						<br><br><br><br>
						<span style="color:white;font-size:30px;">Cargando inventario...</span><br>
						<img src="../../../img/img_casadelasluces/load.gif" height="100px" width="100px">
					</p>
				</div>
			</div>
		</center>
	</div>
	<div id="encabezado" style="width:100%;height:80px;background:#83B141;">
		<table style="padding:10px;" width="100%" >
			<tr style="padding:5px;">
				<td width="50%">
					<p>
						<?php include('buscador/buscador.php');?>
					</p>				
				</td>
				<td width="25%">
					<p style="color:white;">	
						Sucursal:<?php include('controlaSucursales.php');?>
					</p>
				</td>
				<td width="25%">
				
				<td width="25%">
					<p>
						<input type="button" value="Guardar Modificaciones" class="guarda" onclick="<?php echo 'guarda('.$sucursal.');';?>">
						<input type="hidden" id="cambios" >
					</p>
				</td>
			</tr>			
		</table>
	</div>
		<br><!--Damos un espacio-->
	<table class="table" border="0" id="enc" width="80%" style="position:absolute;left:10%;border-radius:5px;height:60px;background:rgba(225,0,0,.6);">
		<thead>
			<tr>
				<!--<td width="10%" align="center" class="titulo">Ubicación</td>-->
				<td width="10%" align="center" class="titulo">Ubic Alm</td>
				<td width="10%" align="center" class="titulo">Orden Lista</td>
				<td width="35.5%" align="center" class="titulo">Descripcion</td>
				<td width="10%" align="center" class="titulo">Temp</td>
				<td width="10%" align="center" class="titulo">Inv virtual</td>
				<td width="10%" align="center" class="titulo">Inv fisico</td>
				<td align="center" class="titulo">Diferencia</td>
				<td width="20px"></td>
			</tr>
		</thead>
	</table>
			<br><br><br>
	<div id="contenido">
		<div id="listado" style="text-align:center;width:100%;overflow:scroll;height:430px;">
		<center>
			<table id="formInv" width="100%">
			<?php
		/*Modificacion Oscar 22.02.2018*/
				$c_a="SELECT id_almacen FROM ec_almacen WHERE id_sucursal=$sucursal AND es_almacen=1";
				$eje=mysql_query($c_a)or die("Error al consultar almacen primario!!!\n\n".$c_a."\n\n".mysql_error());
				$alm=mysql_fetch_row($eje);
				$sql="SELECT 
							p.id_productos,
							p.nombre,
							IF(md.id_producto IS NULL,0,IF(SUM(md.cantidad*tm.afecta) IS NULL,0,SUM(md.cantidad*tm.afecta))),
							p.orden_lista,
							IF($sucursal=1,p.ubicacion_almacen,sp.ubicacion_almacen_sucursal)
						FROM ec_productos p /*ON i.id_producto=p.id_productos*/
						LEFT JOIN sys_sucursales_producto sp ON p.id_productos=sp.id_producto AND sp.id_sucursal IN($sucursal) AND sp.estado_suc=1
						LEFT JOIN ec_movimiento_detalle md ON sp.id_producto=md.id_producto
						LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento = ma.id_movimiento_almacen AND ma.id_almacen=$alm[0] $WHERE
						LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento = tm.id_tipo_movimiento
						WHERE p.id_productos>2 AND p.es_maquilado=0 AND sp.es_externo=0";
				$sql .= " GROUP BY p.id_productos ORDER BY p.orden_lista ASC";
				$cons=mysql_query($sql);
		//die($sql);
				if(!$cons){
					die("Error al consultar invenario...\n".$sql."\n\n".mysql_error());
				}
				//die( $sql );
		//declaramos contador
				$c=0;
				$global_counter = 0;
				$current_product = 0;
				while($row=mysql_fetch_row($cons)){
					$c++;//incrementamos contador
				//
				if ( $current_product != $row[0] ){
					$global_counter ++;
				}
				if( $global_counter % 2 == 0 ){
					$color='#FFFF99';
				}else{
					$color='#CCCCCC';
				}
			//consulta detalle de inventario por proveedor producto
				$sql_prov_prod="SELECT 
						p.id_productos,
						CONCAT(p.nombre, IF( pp.id_proveedor_producto IS NULL, '', CONCAT( ' - ', pp.clave_proveedor ) ) ) AS nombre,
						SUM( IF( mdp.id_movimiento_almacen_detalle IS NULL, 0, ( mdp.cantidad * tm.afecta) ) ) AS inventario,
						p.orden_lista,
						IF($sucursal=1,p.ubicacion_almacen,sp.ubicacion_almacen_sucursal),
						pp.id_proveedor_producto
					FROM ec_productos p 
					LEFT JOIN sys_sucursales_producto sp ON p.id_productos=sp.id_producto 
					AND sp.id_sucursal IN($sucursal) AND sp.estado_suc=1
					LEFT JOIN ec_movimiento_detalle md ON sp.id_producto=md.id_producto
					LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento = ma.id_movimiento_almacen 
					AND ma.id_almacen=$alm[0] /*$WHERE*/
					LEFT JOIN ec_proveedor_producto pp ON pp.id_producto = p.id_productos
					LEFT JOIN ec_movimiento_detalle_proveedor_producto mdp 
					ON mdp.id_movimiento_almacen_detalle = md.id_movimiento_almacen_detalle
					AND mdp.id_proveedor_producto = pp.id_proveedor_producto
					LEFT JOIN ec_tipos_movimiento tm ON mdp.id_tipo_movimiento = tm.id_tipo_movimiento
					WHERE pp.id_producto = '$row[0]'
					GROUP BY pp.id_proveedor_producto";
					//die($sql);
				$prov_prod = mysql_query( $sql_prov_prod ) or die( "Error al consultar detalle de inventario " . mysql_error() );
				while ( $row_prod_prod = mysql_fetch_row( $prov_prod ) ) {
					echo make_row( $row_prod_prod, $c );
				}
				echo make_row( $row, $c, 'readonly' );
			?> 

			<?php
				$current_product = $row[0];
				}
			?>
			</table>
		</div><!--cierra div listado-->
<input type="hidden" id="tope" value="<?php echo $c;?>">
<!--variable de sucursales-->
<input type="hidden" id="id_de_sucursal" value="<?php echo $sucursal;?>">
	</div><!--Se cierra div #contenido-->
	<br>
<!--TOPE PARA NO GENERAR ERRORES-->
		<div class="footer" id="footer" style="padding:10px;">
			<p>
				<input type="button" class="guarda" id="panel" value="Panel Principal" onclick="link(1);">
			</p>
		</div>
</div>
