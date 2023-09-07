<?php
	include( '../../../conectMin.php' );//sesion
	include( '../../../conexionMysqli.php' );//conexion

	function get_prices( $link ){
		$sql = "SELECT 
					id_precio, 
					nombre 
				FROM ec_precios 
				WHERE clave_precio IS NOT NULL 
				AND clave_precio != ''
				AND nombre LIKE '%20%'";
		$eje = $link->query( $sql ) or die( "Error al consultar las listas de precios : {$link->error}" );
		$resp = '<select id="price_id" class="form-control">';
		while ( $r = $eje->fetch_row() ){
			$resp .= '<option value="' . $r[0] . '">' . $r[1] . '</option>';
		}
		$resp .= '</select>';
		return $resp;
	}

?>
<!DOCTYPE html>
<head>
	<script type="text/javascript" src="../../../js/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="../../../js/papaparse.min.js"></script>
	<script type="text/javascript" src="functions.js"></script>
<!-- bootstrap -->
	<link rel="stylesheet" type="text/css" href="../../../css/bootstrap/css/bootstrap.min.css"/>
	<script type="text/javascript" src="../../../css/bootstrap/js/bootstrap.bundle.min.js"></script>
	<title></title>
</head>
	<style type="text/css">
		.list_products{
			max-height: 400px;
			overflow-y : auto; 
		}
		.row{
			padding: 10px;
		}
		.col-sm-4{
			vertical-align: middle;
		}
	</style>
<body>
	<html>
	<head>
		<title></title>
	</head>
	<body>
		<div>
			<!--form class="form-inline">
				<input type="file" id="imp_csv_prd" style="display:none;">
				<p class="nom_csv">
					<input type="text" id="txt_info_csv" style="display:none;" disabled>
				</p>
			</form-->

			<form>
			<div class="row">
				<div class="col-sm-1">
					<img src="../../../img/img_casadelasluces/Logo.png" width="50px">
				</div>
				<div class="col-sm-3">
					<?php
						echo get_prices( $link );
					?>
				</div>
				<div class="col-sm-4">
					<input type="file" id="imp_csv_prd" class="form-control">
				</div>
				<div class="col-sm-4">
					<button type="button" id="submit-file" class="btn btn-warning">Importar</button>
				</div>
			</div>
			</form>
		</div>
		<div class="row list_products">
			<div class="col-sm-1">
			</div>
			<div class="col-sm-10">
				<table class="table table-striped">
					<thead>
						<tr>
							<th>Producto</th>
							<th>Cantidad</th>
							<th>Precio</th>
							<th>Subtotal</th>
						</tr>
					</thead>
					<tbody id="previous">
					
					</tbody>
					<!--tfoot>
						<tr>
							<th>Total</th>
						</tr>
					</tfoot-->
				</table>	
			</div>
			<div class="col-sm-1">
			</div>
		</div>
		<div class="row">
			<div class="col-sm-5"></div>
			<div class="col-sm-2">
				<h2>Total : </h2>
			</div>
			<div class="col-sm-4">
				<input type="number" id="total" class="form-control" readonly>
			</div>
			<div class="col-sm-1"></div>
		</div>
		<div class="row">
			<div class="col-sm-1"></div>
			<div class="col-sm-10">
				<input type="hidden" id="header_id" value="0">
				<button type="button" id="send_button" onclick="redirection();" class="btn btn-success form-control" disabled>
					Continuar con la venta
				</button>
			</div>
			<div class="col-sm-1"></div>
		</div>
	</body>
	</html>
</body>
</html>
<script type="text/javascript">

	$('#submit-file').on("click",function(e){
		e.preventDefault();
		$( '#price_id' ).attr('disabled', true);
		$( '#submit-file' ).attr('disabled', true);
		$('#imp_csv_prd').parse({
			config: {
				delimiter:"auto",
				complete: dataImport,
			},
		 		before: function(file, inputElem){
		 			$("#espacio_importa").css("display","none");//ocultamos el botón de búsqueda
			//console.log("Parsing file...", file);
			},
				error: function(err, file){
		   			console.log("ERROR:", err, file);
				alert("Error!!!:\n"+err+"\n"+file);
			},
		 		complete: function(){
				//console.log("Done with all files");
			}
		});
	});
		var detail = new Array();
		var row_metadata = new Array( 
				Array('id', 'fixed'),
				Array('orden_lista', 'fixed'), 
				Array('nombre', 'fixed'), 
				Array('precio', 'fixed'), 
				Array('cantidad', 'dinamyc'), 
				Array('subtotal', 'fixed')
		);



		//detectamos archivo cargado
				$("#imp_csv_prd").change(function(){
        			var fichero_seleccionado = $(this).val();
      				var nombre_fichero_seleccionado = fichero_seleccionado.replace(/.*[\/\\]/, '');
       				if(nombre_fichero_seleccionado!=""){
        				$("#bot_imp_estac").css("display","none");//ocultamos botón de importación
        				$("#submit-file").css("display","block");//mostramos botón de inserción
        				$("#txt_info_detalle_oc_csv").val(nombre_fichero_seleccionado);//asignamos nombre del archivo seleccionado
        				$("#txt_info_detalle_oc_csv").css("display","block");//volvemos visible el nombre del archivo seleccionado
        				//$("#importa_csv_icon").css("display","none");
        			}else{
        				alert("No se seleccionó ningun Archivo CSV!!!");
        				return false;
        			}
    			});
</script>