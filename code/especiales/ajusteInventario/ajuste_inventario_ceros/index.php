<?php
	include('../../../../conect.php');//incluimos libreria de conexion
	include('../../../../conexionMysqli.php');//incluimos libreria de conexion1
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

	function getWarehouses( $store_id, $warehouse_id = null, $link ){
		$sql = "SELECT id_almacen, nombre FROM ec_almacen WHERE id_sucursal = {$store_id}";
		$stm = $link->query( $sql ) or die( "Error al consultar los almacenes : {$link->error}" );
		$resp = "<option value=\"0\">--Seleccionar--</option>";
		while( $row = $stm->fetch_row() ){
			$resp .= "<option value=\"{$row[0]}\"" . ( $row[0] == $warehouse_id ? ' selected' : '' ) . ">{$row[1]}</option>";
		}
		return $resp;
	}
?>
<script language="JavaScript" src="../../../../js/jquery-1.10.2.min.js"></script>
<script language="Javascript" src="js/funcionesAjusteInvntario.js"></script>
<link rel="stylesheet" type="text/css" href="css/AjusteInventarioStyles.css">
<link rel="stylesheet" type="text/css" href="../../../../css/bootstrap/css/bootstrap.css">
<link rel="stylesheet" type="text/css" href="../../../../css/icons/css/fontello.css">
<div id="global" onclick="oculta_res_busc();">
<center>	


	<div class="emergent"><!-- style="display: block;" -->
		<div class="row">
			<br><br>
			<div class="col-8"></div>
			<div class="col-2 text-right">
				<button 
					type="button" 
					class="btn btn-danger emergent_close_btn no_visible"
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
		.no_visible{
			display: none;
		}
	</style>

	<div id="emergente" class="emerge" style="display:none;">
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
						Sucursal : <?php include('controlaSucursales.php');?>
					</p>
				</td>
				<td width="25%">
					<p style="color:white;">
						Almacen : <select class="form-control" id="warehouse" onchange="change_btn_type();">
						<?php echo getWarehouses( $sucursal_id, $warehouse_id, $link );  ?>
						</select>
					</p>
				<td width="25%">
					<p id="btn_save_container" class="no_visible">
						<input type="button" value="Guardar Modificaciones" class="guarda" onclick="<?php echo 'guarda('.$sucursal.');';?>">
						<input type="hidden" id="cambios" >
					</p>
					<p id="btn_get_inventory_container">
						<button 
							type="button" 
							class="btn btn-warning" 
							onclick="change_warehouse();"
						>Obtener inventarios
						</button>
						<input type="hidden" id="cambios" >
					</p>
				</td>
			</tr>			
		</table>
	</div>
		<br><!--Damos un espacio-->
	<table border="0"  class="" id="enc" width="80%" style="position:absolute;left:10%;border-radius:5px;height:60px;background:rgba(225,0,0,.6);">
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
			</table>
			<br><br><br>
	<div id="contenido">
		<div id="listado" style="text-align:center;width:100%;overflow:scroll;height:430px;">
			<table id="formInv" class="table table-bordered" width="100%">
				<tbody id="products_and_product_providers_list"></tbody>
				<tfoot></tfoot>
			</table>
		</div><!--cierra div listado-->
<input type="hidden" id="tope" value="<?php echo $c;?>">
<!--variable de sucursales-->
<input type="hidden" id="id_de_sucursal" value="<?php echo $sucursal;?>">
	</div><!--Se cierra div #contenido-->
	<br>
<!--TOPE PARA NO GENERAR ERRORES-->
	<div class="footer" id="footer" style="padding:10px;">
		<div class="row">
			<div class="col-6 text-center">
				<button 
					type="button" 
					class="btn btn-danger" 
					id="panel" 
					onclick="link(1);"
				><i class="icon-home-1">Panel Principal</i>
				</button>
			</div>
			<div class="col-6 text-center">
				<button 
					type="button" 
					class="btn btn-warning" 
					id="panel" 
					onclick="export_to_csv();"
				><i class="icon-file-excel">Descargar CSV</i>
				</button>
			</div>
		</div>
	</div>
</div>

<form id="TheForm" method="post" action="ajax/guardaAjuste.php" target="TheWindow">
		<input type="hidden" name="fl" value="csv" />
		<input type="hidden" id="datos" name="datos" value=""/>
</form>

<script type="text/javascript">
//extraemos el total de filas
	$(function() {
		topeAbajo=document.getElementById('tope').value;
		//alert(topeAbajo);
	});
	<?php
		if( isset( $_GET['store_id'] ) && isset( $_GET['warehouse_id'] ) ){
			echo "getInventory( {$_GET['store_id']}, {$_GET['warehouse_id']} );";
		}else{

		}
	?>
</script>
