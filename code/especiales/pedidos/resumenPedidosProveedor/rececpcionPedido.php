<?php
	include("../../../../conectMin.php");
//prepara informacion inicial
	if( isset( $_GET['href']) ){
		$id_oc=$_GET['href'];
		$sql="SELECT 
				oc.folio,
				prov.nombre_comercial,
				prov.id_proveedor 
			FROM ec_ordenes_compra oc
			LEFT JOIN ec_proveedor prov ON oc.id_proveedor=prov.id_proveedor 
			WHERE oc.id_orden_compra=$id_oc";
		$eje=mysql_query($sql)or die("Error al consultar e folio de OC!!!\n\n".$sql);
		$row=mysql_fetch_row($eje);
		$folio=$row[0];
		$proveedor=$row[1];
		$id_proveedor=$row[2];
	}else{
		$id_proveedor = '';
	}
//obtener los proveedores
	$sql = "SELECT
			id_proveedor, 
			nombre_comercial
			FROM ec_proveedor
			WHERE id_proveedor > 1";
	$exc = mysql_query( $sql ) or die( "Error al consultar la lista de proveedores : " . mysql_error() );
	$provider_combo = '<select 
							id="id_prov" 
							class="form-control"
							onchange="getProviderInvoices( this );"
						>';
	$provider_combo .= '<option value="0">-- Seleccionar --</option>';
	while( $r = mysql_fetch_row( $exc ) ){
		$provider_combo .= "<option value=\"{$r[0]}\"" . ($r[0] == $id_proveedor ? " selected" : "" ) . ">{$r[1]}</option>";
	}
	$provider_combo .= '</select>';

//	echo '<input type="hidden" value="'.$id_proveedor.'" id="id_prov">';

	
?>
<!DOCTYPE html>
<html>
<head>
	<title>Recepción de Órdenes de Compra</title>
<script type="text/javascript" src="../../../../js/jquery-1.10.2.min.js"></script>
<script type="text/javascript" src="funcionesRecepcPed.js"></script>
<link rel="stylesheet" type="text/css" href="../../../../css/bootstrap/css/bootstrap.css">
<link rel="stylesheet" type="text/css" href="../../../../css/icons/css/fontello.css">
<link rel="stylesheet" type="text/css" href="styles.css">
<script type="text/javascript" src="js/productProviderFunctions.js"></script>
<script type="text/javascript" src="js/dataGrid.js"></script>
<script type="text/javascript" src="../../../../js/jquery-ui.js"></script>

<script type="text/javascript">
	var global_meassures_home_path = '../../../../';
	var global_meassures_include_jquery = 0;
	var global_meassures_path_camera_plugin = '../../../';
	var global_save_meassure_img_path = '../../recepcionBodega/';
	var global_save_meassure_type = 1;
</script>

</head>
<body>
	<div class="emergente">
		<div class="row">
			<div class="col-12 emergent_content"></div>
				<button 
					type="button" 
					class="emrgent_btn_close"
					onclick="close_emergent();"
				>
					X
				</button>
		</div>
	</div>
<!-- Emergente 2 -->
	<div class="emergent_2">
		<div class="emergent_content_2"></div>
	</div>
<!-- Emergente 3 -->
	<div class="emergent_3">
		<div class="emergent_content_3" tabindex="100"></div>
	</div>


	<div id="global_seeker_response"></div>

	<div class="global">
		<div class="enc" ><!-- onclick="document.getElementById('res_busc_folio').style.display='none';" -->
			<input type="hidden" id="id_recepcion" value="0">
			<div class="row" style="color : white; text-align : center;">
				<div class="col-2">
					Buscar Productos : </br>
					<input 
						type="text" 
						id="input_buscador" 
						class="form-control" 
						onkeyup="busca_txt(event);"
						disabled
					>
					<div id="res_busc"></div>
				</div>
				<div class="col-2">
					Proveedor:<br>
						<!--input type="text" class="info_inicial" value="<?php //echo $proveedor;?>" disabled-->
					<?php echo $provider_combo; ?>
				</div>
				<div class="col-2">
					Folio Rem:<br>
					<div class="input-group">
						<input 
							type="text" 
							class="form-control" 
							id="ref_nota_1"  
							onkeyup="seek_invoice(event,this, 'remissions');"
						>
						<button 
							class="btn btn-success add_remission"
							onclick="addRemission();"
							title="Agregar Remisión"
						>
							<i class="icon-plus"></i>
						</button>

						<button 
							class="btn btn-danger clean_remission"
							onclick="cleanRemission();"
							title="Resetear Remisión"
						>
							<i class="icon-cancel-alt-filled"></i>
						</button>
					</div>
					<div id="remissions_response"></div>	
				</div>

				<div class="col-2">
					Folio Rec:<br>
					<input 
						type="text" 
						class="form-control" 
						id="ref_nota_2"  
						onkeyup="seek_invoice(event,this, 'receptions');"
						disabled
					>
					<input
						type="hidden"
						id="warehose_reception_id"
					>
					<div id="receptions_response"></div>	
				</div>
				<!--div class="col-2">
					Folio de Remisión:<br>
					<input type="text" class="form-control" id="ref_nota_1" style="width:150px;" onkeyup="busca_folio(event,this);">
					<div id="res_busc_folio"></div>						
				</div-->
				<div class="col-2">
					Monto<br>
					<input 
						type="number" 
						class="form-control" 
						id="monto_nota" 
						disabled>
					
				</div>
				<div class="col-1">
					Piezas Rem<br>
					<input 
						type="number" 
						class="form-control" 
						id="pzas_remision" 
						disabled
					>
				</div>
				<div class="col-1">
					Piezas Rec:<br>
					<input 
						type="number" 
						class="form-control" 
						id="pzas_recibidas" 
						disabled
					>
				</div>
			</div>
			<!--table width="95%" style="position: absolute;top:0;">
				<tr-->
					<!--td style="color:white;font-size:18px;">
						Buscar Productos : </br>
						<input type="text" id="input_buscador" class="form-control" onkeyup="busca_txt(event);">
						<div id="res_busc"></div>
					</td-->
					<!--td align="center" style="color:white;font-size:18px;">
						Proveedor:<br>
						<!--input type="text" class="info_inicial" value="<?php //echo $proveedor;?>" disabled-->
						<?php// echo $provider_combo; ?>
					<!--/td-->
					<!--td align="center" style="color:white;font-size:18px;">
						<!-- Folio OC:<br><input type="text" class="form-control" value="<?php //echo $folio;?>" disabled-->
					<!--/td>
					<!--td align="center" style="color:white;font-size:18px;">
						Folio de Remisión:<br><input type="text" class="form-control" id="ref_nota_1" style="width:150px;" onkeyup="busca_folio(event,this);">
						<div id="res_busc_folio"></div>						
					</td-->
					<!--td  align="center" style="color:white;font-size:18px;">
						Monto de Remisión:<br><input type="number" class="form-control" id="monto_nota" style="width:150px;" disabled>
					</td-->
					<!--td  align="center" style="color:white;font-size:18px;">
						Piezas en Remision:<br><input type="number" class="form-control" id="pzas_remision" style="width:150px;" disabled>
					</td-->
					<!--td  align="center" style="color:white;font-size:18px;">
						Piezas Recibidas:<br><input type="number" class="form-control" id="pzas_recibidas" style="width:150px;" disabled>
					</td
				</tr>
			</table>-->
		</div>
		<div  id="contenido" style="margin-top : 20px;">
		<center>
		<!--p align="left" class="subtitulo"><b>Productos</b></p-->
			<table class="" width="90%"><!-- onclick="document.getElementById('res_busc_folio').style.display='none';" -->
				<tr>
					<th class="enc_grid" width="8%">Ubic</th>
					<th class="enc_grid product_description" width="20%">Descripción</th>
					<th class="enc_grid" width="7%">Modelo</th>
					<th class="enc_grid" width="8%">Pendiente de Recibir</th>
					<th class="enc_grid" width="8%">Piezas por Caja</th>
					<th class="enc_grid" width="8%">Cajas Recibidas</th>
					<th class="enc_grid" width="8%">Piezas Sueltas 
						<button 
							type="button"
							class="btn"
							onclick="helper( 7 );"
							style="font-size : 120%;"
						><i class="icon-question"></i></button></th>
					<th class="enc_grid" width="8%">Precio Pieza</th>
					<th class="enc_grid" width="8%">% Desc</th>
					<th class="enc_grid" width="8%">Total Piezas</th>
					<th class="enc_grid" width="8%">Monto</th>
					<th class="enc_grid delete_enc">Quitar</th>
				</tr>
				</table>
				<div class="contenido_tabla">
				<table width="100%" class="table table-bordered table-striped"><!-- onclick="document.getElementById('res_busc_folio').style.display='none';" -->
				<tbody id="table_body">
				</tbody>
			</table>
			</div><!--Cerramos el div de la tabla-->
		<!-- acotaciones -->
			<div class="row">
				<div class="col-2" style="vertical-aling : middle;">
				</div>

				<div class="col-2">
					<i class="icon-bookmark" style="color:white; font-size:200%;"></i>Normal
				</div>
				<div class="col-2">
					<i class="icon-bookmark" style="color:green; font-size:200%;"></i>Validada
				</div>
				<div class="col-2">
					<i class="icon-bookmark" style="color:yellow; font-size:200%;"></i>Sin proveedor
				</div>
				<div class="col-2">
					<i class="icon-bookmark" style="color:rgba( 225, 0, 0, .5); font-size:200%;"></i>Sin pedido
				</div>
			</div>
		</center>
			
		<input type="hidden" id="id_oc" value="<?php echo $id_oc;?>">

		<div>
		<div class="footer">
		<br>
			<table width="100%">
				<tr>
					<td width="25%" align="center">
						<a href="../../../../index.php" class="btn btn-light">
							<i class="icon-home-outline">Regresar al panel</i>
						</a>
					</td>
					<td width="25%" align="center">
						<button type="button" onclick="guarda_recepcion();" class="btn btn-light">
							<!--img src="../../../../img/especiales/save.png" width="20px"-->
							<i class="icon-floppy">Guardar</i>
						</button>
					</td>
					<td width="25%" align="center">
						<button type="button" onclick="finish_reception();" class="btn btn-light">
							<!--img src="../../../../img/especiales/save.png" width="20px"-->
							<i class="icon-right-big">Guardar y Finalizar</i>
						</button>
					</td>
					<td width="25%" align="center">
						<a href="../../../general/listados.php?tabla=ZWNfb3JkZW5lc19jb21wcmE=&no_tabla=Mg==" class="btn btn-light">
							<i class="icon-th-list-outline">Ver listado</i>
						</a>
					</td>
				</tr>
			</table>
		</div>
	</div>
</body>
</html>