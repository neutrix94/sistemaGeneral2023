<?php
	include( '../../../../conexionMysqli.php' );
	include( 'exhibitionProducts.php' );
	$eP = new exhibitionProducts( $link );
	$exhibition_id = ( isset( $_GET['exhibition_id'] ) ? $_GET['$xhibition_id'] : $_POST['exhibition_id'] );
//	die( "HERE : {$exhibition_id}" );
	$details = $eP->getExhibitionProductProviderToEdit( $exhibition_id );
?>
	<div class="row text-center" style="color : black;"><!-- tabla_emerge1 -->
		<h4 style="">Captura la cantidad de productos tomada de cada almacén</h4>
		<table class="table table-bordered table-striped">
			<thead>
				<tr>
					<th>MODELO</th>
					<th>CANTIDAD TOMADA</th>
					<th>CANTIDAD SE LLEVO CLIENTE</th>
				</tr>
			</thead>
			<tbody id="exhibition_product_providers_update">
<?php
	foreach ( $details as $key => $detail ) {
		echo "<tr id=\"pp_detail_0_{$key}\" value=\"{$detail['product_provider_exhibition_id']}\">
			<td id=\"pp_detail_1_{$key}\">{$detail['provider_clue']}</td>
			<td id=\"pp_detail_2_{$key}\">{$detail['quantity']}</td>
			<td>
				<input type=\"number\" id=\"pp_detail_3_{$key}\" class=\"form-control\">
			</td>
		</tr>";
	}
?>
		</tbody>
	</table>
	</div>

	<div class="row">
		<div class="col-2"></div>
		<div class="col-8">
			<br><br>
			<button 
				type="button" 
				class="btn btn-success form-control" 
				onclick="update_exhibition_rows();">
				<i class="icon-ok-circle">Aceptar</i>
			</button>
			<br><br>
			<button 
				type="button" 
				class="btn btn-danger form-control" 
				onclick="close_emergent()">
				<i class="icon-ok-circle">Cancelar</i>
			</button>
		</div>
		<div class="col-4"></div>
	<!--Modificación Oscar 10.11.2018 para convertir cuadro de texto en cuadro de contraseña-->
		<!--input type="hidden" value="" id="pss_encargado" onkeydown="cambiar(this,event,'pss_encargado');"-->
	<!--Fin de cambio-->
	</div>

	<style type="text/css">
		.ent_txt_emerg{
			padding: 10px;border-radius: 5px;
		}
		.tabla_emerge1{
			position:relative;width:80%;left:0;background: transparent;border:0;border-collapse:collapse;
		}
		.group_card{
			box-shadow : 1px 1px 5px rgba( 0,0,0,.5 );
			box-shadow: 1px 1px 10px rgba( 0,0,0,.5 ) !important;
			padding : 2px;
			margin : 0;
		}
	</style>

	<script type="text/javascript">
		function update_exhibition_rows(){
			var data = "";
			var comprobation = true;
			$( '#exhibition_product_providers_update tr' ).each( function( index ){
				if( $( '#pp_detail_3_' + index ).val() == '' ){
					comprobation = index;
					return false;
				}
				data += ( data == '' ? '' : '|~|' );
				data += $( '#pp_detail_0_' + index ).attr( 'value' );//id detalle
				data += '|' + $( '#pp_detail_3_' + index ).val();//cantidad
			});
			if( comprobation != true ){
				alert( "Los campos 'CANTIDAD SE LLEVO CLIENTE' no pueden ir vacios!" );
				$( '#pp_detail_3_' + comprobation ).focus();
				return false;
			}
			if( data == '' ){
				alert( "Error : no hay cambios por guardar!" );
				return false;
			}
			//alert( data );return false;

	//envia datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/exhibitionProducts.php',
			cache:false,
			data:{ exhibition_flag : 'updateExhibitionRows', data : data },
			success:function(dat){
				var aux=dat.split("|");
				if(aux[0]!='ok'){
					alert("Error!!!\n"+dat);
				}else{
					alert( aux[1] );
					close_emergent();
					getExhibitionPending();
				}
			}
		});
		}
	</script>
