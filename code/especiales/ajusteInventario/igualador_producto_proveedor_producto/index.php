<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Igualacion de Inventarios</title>
	<script language="JavaScript" src="../../../../js/jquery-1.10.2.min.js"></script>
	<script language="Javascript" src="js/functions.js"></script>
	<link rel="stylesheet" type="text/css" href="css/styles.css">
	<link rel="stylesheet" type="text/css" href="../../../../css/bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="../../../../css/icons/css/fontello.css">
</head>
<body>
	<?php
	include('../../../../conect.php');//incluimos libreria de conexion
	include('../../../../conexionMysqli.php');//incluimos libreria de conexion
	include( 'ajax/functions.php' );
	//extract($_GET);
	if( !isset( $_GET['store_id'] ) || !isset( $_GET['warehouse_id'] ) ){
		die( "<script>location.href=\"./configuration.php\";</script>" );
	//	die( 'here' );
		//die( "<script type=\"text/JavaScript\">alert();location.href=\"permiso.php\";</script>" );
	}
	
	include( 'ajax/inventoryAdjustment.php' );
	$inventory = new inventoryAdjustment( $link );
	$store_id = $_GET['store_id'];
	$warehouse_id = $_GET['warehouse_id'];
	$maquiled = $_GET['maquiled'];/*implementacion Oscar 2023 para mnostrar maquilados o productos normales*/ 

?>
	<div id="global"><!-- onclick="oculta_res_busc();" -->
	<center>	


	<!--div class="emergent"> style="display: block;" --
		<div class="row">
			<div class="col-8"></div>
			<div class="col-2 text-right">
				<button 
					type="button" 
					class="btn btn-danger emergent_close_btn"
					onclick="close_emergent();"
				>
					X
				</button>
			</div>
			<div class="col-2"></div>
			<div class="col-2"></div>
			<div class="col-8 emergent_content" tabindex="1"></div>
		</div>
	</div-->
	<style type="text/css">
	</style>

	<div id="emergente" class="emergent">
		<center>
		<div style="background:rgba(0,0,0,.7);border:1px solid red;top:170px;position:absolute;width:60%; left : 20%; height:300px;border-radius:15px;z-index : 200 ;">
			<div id="cont_vta_emerge">
				<p id="info_emerge" style="color:white;font-size:25px;" align="center">
					<br><br><br><br>
					<span style="color:white;font-size:30px;">Cargando inventario...</span><br>
					<img src="../../../../img/img_casadelasluces/load.gif" height="100px" width="100px">
				</p>
			</div>
		</div>
		</center>
	</div>

	<div id="encabezado" class="row" style="width:100%;height:80px;background:#83B141;">
		<div class="col-3">
			<label style="color : white">Sucursal:</label>
			<?php echo $inventory->getStores( $store_id, null, true );?>
		</div>
		<div class="col-3">
			<label style="color : white">Almacén:</label>
			<?php echo $inventory->getStoreWharehouses( $store_id, $warehouse_id, true );?>
		</div>
		
		<div class="col-5" style="vertical-align : middle;">
			<button 
				type="button" 
				value="Guardar Modificaciones" 
				class="btn btn-success" 
				onclick="<?php echo 'guarda('.$store_id.');';?>">
				<i class="icon-ok-circle">Guardar Modificaciones</i>
			</button>
			<input type="hidden" id="cambios" >
		</div>
	</div>
		<br><!--Damos un espacio-->
	<div style="position : relative; max-height : 78% !important; overflow-y : auto;">
	<table border="0"  class="table table-striped table-bordered" id="enc" width="80%" >
		<!-- style="position:absolute;left:10%;border-radius:5px;height:60px;background:rgba(225,0,0,.6);" -->
		<thead class="header_sticky">	
			<tr>
				<!--<td width="10%" align="center" class="titulo">Ubicación</td>-->
				<td width="10%" align="center" class="invisible">Ubic Alm</td>
				<td width="10%" align="center" class="titulo">Orden Lista</td>
				<td width="35.5%" align="center" class="titulo">Descripcion</td>
				<td width="10%" align="center" class="invisible">Temp</td>
				<td width="10%" align="center" class="titulo">Inv Producto</td>
				<td width="10%" align="center" class="titulo">Inv Proveedor Producto</td>
				<td align="center" class="titulo">Diferencia</td>
			</tr>
		</thead>
		<tbody id="adjustment_content">
			<?php
				$sql="SELECT
						ax1.product_id,
						ax1.product_name,
						ax1.product_inventory,
						ax1.order_list,
						ax1.product_location,
						ax1.is_maquiled,
						ax1.product_provider_inventories
					FROM(
						SELECT
							ax.product_id,
							ax.product_name,
							ax.product_inventory,
							ax.order_list,
							ax.product_location,
							ax.is_maquiled,
							FORMAT( SUM( IF( mdp.id_movimiento_detalle_proveedor_producto IS NULL, 0, ( mdp.cantidad * tm.afecta) ) ), 4) AS product_provider_inventories
						FROM(
							SELECT 
								/*0*/p.id_productos AS product_id,
								/*1*/p.nombre AS product_name,
								/*2*/FORMAT( 
										IF(md.id_producto IS NULL,
											0,
											IF(SUM(md.cantidad*tm.afecta) IS NULL,
												0,
												SUM(md.cantidad*tm.afecta)
											)
										), 4 
									) AS product_inventory,
								/*3*/p.orden_lista AS order_list,
								/*4*/IF( {$store_id} = 1,
										p.ubicacion_almacen,
										sp.ubicacion_almacen_sucursal
									) AS product_location,
								/*5*/(SELECT 
										IF( id_producto IS NULL, 
											0, 
											id_producto 
										) 
									FROM ec_productos_detalle 
									WHERE id_producto_ordigen = p.id_productos 
									LIMIT 1) AS is_maquiled
								FROM ec_productos p /*ON i.id_producto=p.id_productos*/
								LEFT JOIN sys_sucursales_producto sp 
								ON p.id_productos=sp.id_producto 
							/*deshabilitado por Oscar 2023
								AND sp.id_sucursal IN( {$store_id} ) 
								AND sp.estado_suc=1

							fin de cambio Oscar 2023*/
								LEFT JOIN ec_movimiento_detalle md 
								ON sp.id_producto=md.id_producto
								LEFT JOIN ec_movimiento_almacen ma 
								ON md.id_movimiento = ma.id_movimiento_almacen 
								AND ma.id_almacen = {$warehouse_id} 
								AND ma.id_sucursal= {$store_id}
								LEFT JOIN ec_tipos_movimiento tm 
								ON ma.id_tipo_movimiento = tm.id_tipo_movimiento			
								WHERE p.id_productos>2 
								AND p.es_maquilado = '{$maquiled}' /*implementacion Oscar 2023 para mnostrar maquilados o productos normales*/ 
								/*AND sp.es_externo=0*/
							/*oscar 2023*/
								AND sp.id_sucursal IN( {$store_id} ) 
								/*AND sp.estado_suc=1 deshabilitadfo por oscar 2023*/
								AND ma.id_almacen = {$warehouse_id} 
							/* fin de cambio oscar 2023*/
								GROUP BY p.id_productos 
								ORDER BY p.orden_lista ASC	
							)ax
							LEFT JOIN ec_proveedor_producto pp 
							ON pp.id_producto = ax.product_id
							LEFT JOIN ec_movimiento_detalle_proveedor_producto mdp 
							ON mdp.id_proveedor_producto = pp.id_proveedor_producto
							AND mdp.id_almacen = '{$warehouse_id}'
							LEFT JOIN ec_tipos_movimiento tm 
							ON mdp.id_tipo_movimiento = tm.id_tipo_movimiento
							GROUP BY ax.product_id 
							ORDER BY ax.order_list ASC
						)ax1
						WHERE ax1.product_inventory != ax1.product_provider_inventories
						GROUP BY ax1.product_id 
						ORDER BY ax1.order_list ASC";
			//die( "SQL : " . $sql );
				$stm = $link->query($sql);
				if( ! $stm ){
					die("Error al consultar inventario...\n{$sql}\n\n{$link->error}");
				}
				//die( $sql );
		//declaramos contador
				$c=0;
				$global_counter = 0;
				$current_product = 0;
				while( $row = $stm->fetch_assoc() ){
					$c++;//incrementamos contador
					if ( $current_product != $row[0] ){
						$global_counter ++;
					}
					if( $global_counter % 2 == 0 ){
						$color='#FFFF99';
					}else{
						$color='#CCCCCC';
					}
					echo make_row( $row, $c );//, readonly
					$current_product = $row[0];
				}
			?>
			</table>
		</div>
<input type="hidden" id="tope" value="<?php echo $c;?>">
<!--variable de sucursales-->
<input type="hidden" id="store_id" value="<?php echo $store_id;?>">
<input type="hidden" id="warehouse_id" value="<?php echo $warehouse_id;?>">
	</div><!--Se cierra div #contenido-->
	<br>
<!--TOPE PARA NO GENERAR ERRORES-->
		<div class="footer text-center" id="footer" style="padding:10px;">
			<div class="row">
				<div class="col-3"></div>
				<div class="col-3 text-center">
					<button 
							type="button" 
							class="guarda btn btn-light" 
							id="panel" 
							onclick="redirect('configuration');"
					>
						<i class="icon-wrench-4">Reconfigurar</i>
					</button>
				</div>
				<div class="col-3 text-center">	
					<button 
							type="button" 
							class="guarda btn btn-light" 
							id="" 
							onclick="redirect('home');"
					>
						<i class="icon-home-1">Ir al Panel</i>
					</button>
				</div>
			<div class="col-3"></div>
		</div>
</div>
</body>
</html>
<script type="text/javascript">
	$( "#emergente" ).css( 'display', 'none' );
</script>