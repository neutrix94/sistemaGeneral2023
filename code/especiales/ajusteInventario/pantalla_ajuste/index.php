<?php
	if( !isset( $_GET['warehouse_id'] ) ){
		$host  = $_SERVER['HTTP_HOST'];
		$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
		$file = 'permiso.php';
		header("Location: http://{$host}{$uri}/{$file}");
	}

	$warehouse_id = $_GET['warehouse_id'];

	include('../../../../conect.php');//incluimos libreria de conexion
	include('../../../../conexionMysqli.php');//incluimos libreria de conexion
	include( 'ajax/functions.php' );
	include( 'ajax/inventoryAdjustment.php' );
	$inventory = new inventoryAdjustment( $link, $sucursal_id );
//	extract($_GET);
	if($sucursal_id==-1){
		$sucursal=1;	
	}else if($sucursal_id>=1){
		$sucursal=$sucursal_id;
	}
	if(isset($id_suc_adm)){
		$id_suc_adm=base64_decode($id_suc_adm);//decodificamos la variable
		$sucursal=$id_suc_adm;
	}
	$WHERE = " AND ma.id_sucursal= '{$sucursal}' ";
?>
<!DOCTYPE html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<script language="JavaScript" src="../../../../js/jquery-1.10.2.min.js"></script>
		<script language="Javascript" src="js/funcionesAjusteInvntario.js"></script>
		<link rel="stylesheet" type="text/css" href="css/AjusteInventarioStyles.css">
		<link rel="stylesheet" type="text/css" href="../../../../css/bootstrap/css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="../../../../css/icons/css/fontello.css">
		<script type="text/javascript">
			var current_warehouse = <?php echo $warehouse_id; ?>;
		</script>
	</head>
<body>
<div id="global" onclick="oculta_res_busc();">
<center>

<!-- emergente -->
	<!--div class="emergent">
		<div tabindex="1" style="position: relative; top : 0 !important; left: 90%; z-index:1;">
			<button 
				class="btn btn-danger"
				onclick="close_emergent();"
			>X</button>
		</div>
		<div class="emergent_content" tabindex="1"></div>
	</div-->

	<div class="emergent"><!-- style="display: block;" -->
		<div class="row">
			<div class="col-8"></div>
			<div class="col-2 text-end">
				<button 
					type="button" 
					class="btn btn-danger close_emergent_btn"
					onclick="close_emergent();"
				>
					X
				</button>
			</div>
			<div class="col-2"></div>
			<div class="col-2"></div>
			<div class="col-8 emergent_content" tabindex="1"></div>
		</div>
	</div>
	<style type="text/css">
		.text-right{
			text-align: right !important;
			border: 1px solid;
		}
		.emergent_close_btn{
			position: relative !important;
			right: -10% !important;
			top: 5%;
		}
	</style>

	<div id="emergente" class="emerge">
		<center>
		<div style="background:rgba(0,0,0,.7);border:1px solid red;top:170px;position:relative;width:60%;height:300px;border-radius:15px;">
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
	<div id="header">
		<div class="row" >
			<div class="col-3" style="padding:5px;">
						<?php include('buscador/buscador.php');?>
			</div>
				<div class="col-3" width="25%">
						<label for="cambiaSuc" style="color : white ;">Sucursal:</label><br>
					<?php 
						include('controlaSucursales.php');
					?>
				</div>
				<div class="col-3">
					<label for="warehouse" style="color : white ;">Almacen:</label><br>
					<?php
						echo $inventory->getStoreWharehouses( $warehouse_id );
					?>

				</div>
				<div class="col-3" width="25%">
					<br>
						<button type="button" 
							class="btn btn-success" 
							onclick="<?php echo 'guarda('.$sucursal.');';?>"
						>
							<i class="icon-ok-circle">Guardar</i>
						</button>
						<input type="hidden" id="cambios" >
					</p>
				</div>		
		</div>
	</div>
	<br>
	<div id="listado" style="text-align:center;width:100%;overflow:scroll;height:430px; padding : 20px;"><!--  -->
		<table border="0"  class="table table-bordered" id="enc" width="80%" >
		<!--style="position:absolute;left:10%;border-radius:5px;height:60px;background:rgba(225,0,0,.6);"-->
			<thead class="header_sticky_top">
				<tr>
					<!--<td width="10%" align="center" class="titulo">Ubicación</td>-->
					<th width="10%" align="center" class="titulo">Ubic Alm</th>
					<th width="10%" align="center" class="titulo">Orden Lista</th>
					<th width="35.5%" align="center" class="titulo">Descripcion</th>
					<th width="10%" align="center" class="titulo">Temp</th>
					<th width="10%" align="center" class="titulo">Inv Sistema</th>
					<th width="10%" align="center" class="titulo">Inv Conteo</th>
					<th align="center" class="titulo">Diferencia</th>
					<!--th width="20px"></th-->
				</tr>
			</thead>
			<tbody id="adjustment_content">
			<?php
		/*Modificacion Oscar 22.02.2018*/
				$c_a="SELECT id_almacen FROM ec_almacen WHERE id_sucursal=$sucursal AND es_almacen=1";
				$eje=mysql_query($c_a)or die("Error al consultar almacen primario!!!\n\n".$c_a."\n\n".mysql_error());
				$alm=mysql_fetch_row($eje);
				$sql="SELECT	
						/*0*/ax.id_productos,
						/*1*/ax.nombre,
						/*2*/ax.virtual_inventory,
						/*3*/ax.order_list,
						/*4*/ax.product_location,
						/*5*/ax.is_maquiled,
						/*6*/ax.tmp,
						/*7*/SUM( IF( cit.id_producto = ax.id_productos AND cit.id_almacen = {$warehouse_id}, cit.total_en_piezas, 0 ) )
					FROM(
						SELECT 
								/*0*/p.id_productos,
								/*1*/p.nombre,
								/*2*/FORMAT( IF(md.id_producto IS NULL,0,IF(SUM(md.cantidad*tm.afecta) IS NULL,0,SUM(md.cantidad*tm.afecta))), 2 ) AS virtual_inventory,
								/*3*/p.orden_lista AS order_list,
								/*4*/IF($sucursal=1,p.ubicacion_almacen,sp.ubicacion_almacen_sucursal) AS product_location,
								/*5*/(SELECT IF( id_producto IS NULL, 0, id_producto ) FROM ec_productos_detalle WHERE id_producto_ordigen = p.id_productos LIMIT 1) AS is_maquiled,
								/*6*/NULL as tmp
							FROM ec_productos p /*ON i.id_producto=p.id_productos*/
							LEFT JOIN sys_sucursales_producto sp ON p.id_productos=sp.id_producto AND sp.id_sucursal IN($sucursal) AND sp.estado_suc=1
							LEFT JOIN ec_movimiento_detalle md ON sp.id_producto=md.id_producto
							LEFT JOIN ec_movimiento_almacen ma ON md.id_movimiento = ma.id_movimiento_almacen AND ma.id_almacen=$alm[0] $WHERE
							LEFT JOIN ec_tipos_movimiento tm ON ma.id_tipo_movimiento = tm.id_tipo_movimiento
							/*LEFT JOIN ec_conteo_inventario_tmp cit
							ON cit.id_producto = p.id_productos*/
							WHERE p.id_productos>2  
							/*AND cit.id_almacen = {$warehouse_id}
							AND cit.ya_fue_contado = '1'
							AND cit.pospuesto = '0'
							AND cit.ya_realizo_movimientos = '0'*/
							AND p.es_maquilado=0 
							AND sp.es_externo=0
							GROUP BY p.id_productos
							ORDER BY p.orden_lista ASC
						)ax
						LEFT JOIN ec_conteo_inventario_tmp cit
						ON cit.id_producto = ax.id_productos
						AND cit.id_almacen = {$warehouse_id}
						WHERE cit.id_almacen = {$warehouse_id}
						AND cit.ya_fue_contado = '1'
						AND cit.pospuesto = '0'
						/* deshabilitado por Oscar 2023 por error de sumatoria en productos AND cit.ya_realizo_movimientos = '0'*/
						GROUP BY ax.id_productos
						ORDER BY ax.order_list ASC";
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
					echo make_row( $row, $c, 'readonly' );
					$sql_prov_prod = "SELECT
									/*0*/ax.id_productos,
									/*1*/ax.nombre,
									/*2*/ax.inventario,
									/*3*/ax.orden_lista,
									/*4*/IF($sucursal=1,
										CONCAT( ppua.letra_ubicacion_desde, ppua.numero_ubicacion_desde  ),
										ax.ubicacion_almacen_sucursal) AS location,
									/*5*/ax.id_proveedor_producto,
									/*6*/ax.is_maquiled,
									/*7*/ax.total_en_piezas,
									/*8*/ax.id_conteo_inventario_tmp
								FROM(
									SELECT 
										/*0*/p.id_productos,
										/*1*/CONCAT(p.nombre, IF( pp.id_proveedor_producto IS NULL, '', CONCAT( ' - ', pp.clave_proveedor, ' ( ', pp.presentacion_caja, ' pzas por caja )', ' <b>id_p_p : ', pp.id_proveedor_producto, '</b>' ) ) ) AS nombre,
										/*2*/FORMAT( SUM( IF( mdp.id_movimiento_detalle_proveedor_producto IS NULL, 0, ( mdp.cantidad * tm.afecta) ) ), 2) AS inventario,
										/*3*/p.orden_lista,
										/*4*/sp.ubicacion_almacen_sucursal,
										/*5*/pp.id_proveedor_producto,
										/*6*/(SELECT IF( id_producto IS NULL, 0, id_producto ) FROM ec_productos_detalle WHERE id_producto_ordigen = p.id_productos LIMIT 1) as is_maquiled,
										/*7*/cit.total_en_piezas,
										/*8*/cit.id_conteo_inventario_tmp
									FROM ec_productos p 
									LEFT JOIN sys_sucursales_producto sp ON p.id_productos=sp.id_producto 
									AND sp.id_sucursal IN($sucursal) AND sp.estado_suc=1
									LEFT JOIN ec_proveedor_producto pp ON pp.id_producto = p.id_productos
									LEFT JOIN ec_movimiento_detalle_proveedor_producto mdp 
									ON mdp.id_proveedor_producto = pp.id_proveedor_producto
									AND mdp.id_almacen = '{$warehouse_id}'
									LEFT JOIN ec_tipos_movimiento tm ON mdp.id_tipo_movimiento = tm.id_tipo_movimiento
									LEFT JOIN ec_conteo_inventario_tmp cit
									ON cit.id_proveedor_producto = pp.id_proveedor_producto 
									WHERE pp.id_producto = '{$row[0]}'
									AND cit.id_almacen = '{$warehouse_id}'
									GROUP BY pp.id_proveedor_producto
								) ax
								LEFT JOIN ec_proveedor_producto_ubicacion_almacen ppua
								ON ppua.id_proveedor_producto = ax.id_proveedor_producto
								GROUP BY ax.id_proveedor_producto";
				//die( $sql );
				$prov_prod = mysql_query( $sql_prov_prod ) or die( "Error al consultar detalle de inventario {$sql_prov_prod} " . mysql_error() );
				while ( $row_prod_prod = mysql_fetch_row( $prov_prod ) ) {
					echo make_row( $row_prod_prod, $c );
				}
			?> 

			<?php
				$current_product = $row[0];
				}
			?>
			</tbody>
			</table>
		</div><!--cierra div listado-->

		<div class="row">
			<div class="col-3">
			</div>
			<div class="col-6">
				<br>
				<button 
					type="button"
					class="btn btn-warning form-control"
					onclick="product_count_reset();"
				>
					<i class="icon-spin3">Resetear conteo de productos *</i>
				</button>
			</div>
			<div class="col-1">
				<br>
				<button 
					type="button"
					class="btn btn-info"
					onclick="show_help( 'reset_product' );"
					style="border-radius : 50%;"
				>
					<i class="icon-help"></i>
				</button>
			</div>
		</div>
<input type="hidden" id="tope" value="<?php echo $c;?>">
<!--variable de sucursales-->
<input type="hidden" id="id_de_sucursal" value="<?php echo $sucursal;?>">
	<!--/div><Se cierra div #contenido-->
	<br>
<!--TOPE PARA NO GENERAR ERRORES-->
		<div class="footer" id="footer" style="padding:10px;">
			<div class="row">
				<div class="col-3"></div>
				<div class="col-6">
					<button type="button" class="btn btn-light" onclick="link(1);">
						<i class="icon-home-1">Panel Principal</i>
					</button>
				</div>
			</div>
		</div>
</div>
</body>
<script type="text/javascript">
//extraemos el total de filas
	$(function() {
		topeAbajo=document.getElementById('tope').value;
		//alert(topeAbajo);
	});

	function show_help( type ){
		var content;
		switch( type ){
			case 'reset_product' : 
				content = reset_product_help;
			break;
		}
		show_emergent( content, true, false );
	}
	function show_emergent( content, btn_acept = false, show_close_btn = true ){
		if( btn_acept != false ){
			content += `<div class="row">
							<div class="col-2"></div>
							<div class="col-8">
								<button
									class="btn btn-success form-control"
									onclick="close_emergent();"
								>
									<i class="icon-ok-circled">Acceptar</i>
								</button>
							</div>
						</div>`;
		}
		$( '.emergent_content' ).html( content );
		$( '.emergent' ).css( 'display', 'block' );
		$( '.close_emergent_btn' ).css( 'display', ( show_close_btn ? "block" : "none" ) );
	}

	function close_emergent(){
		$( '.emergent_content' ).html( '' );
		$( '.emergent' ).css( 'display', 'none' );
	}

	var reset_product_help = `
		<div class="row">
			<div class="col-12">
				<h4>Este botón sirve para resetear los registros que ya fueron ajustados, es necesario haber ajustado todos los productos del rango seleccionado</h4>
				<br>
			</div>
		</div>`;
		
	//llamadas asincronas
	function ajaxR(url){
		if(window.ActiveXObject)
		{		
			var httpObj = new ActiveXObject("Microsoft.XMLHTTP");
		}
		else if (window.XMLHttpRequest)
		{		
			var httpObj = new XMLHttpRequest();	
		}
		httpObj.open("POST", url , false, "", "");
		httpObj.send(null);
		return httpObj.responseText;
	}



</script>
