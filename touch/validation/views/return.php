<div class="row">
	<div class="col-sm-1"></div>
	<div class="col-sm-10" style="max-height : 350px;"><!-- overflow-y : auto; height : 200px;-->
		<div class="input-group">
			<input 
				type="text" 
				id="return_seeker" 
				class="form-control"
				placeholder="Escaner código de barras / Buscar producto"
				onkeyup="evitarCaracteresEspeciales( event );seek_product_to_return( this, event );"
			>
			<button
				type="button"
				class="btn btn-warning"
				onclick="seek_product_to_return( '#return_seeker', 'intro' );"
			>
				<i class="icon-barcode"></i>
			</button>
		</div>
		<div id="return_seeker_response"></div>
	</div>
</div>

<div class="row">
	Detalle de Devolución
	<div class="accordion" id="accordionExample" style="max-height : 300px; overflow-y : auto;">
		<?php
//die( "tkt : " . $ticket_id );
			echo build_accordeon( $ticket_id, 1, $this->link );
		?>
	</div>
</div>
<br>
<div class="row text-center">
	<h5 style="color : red;">Al Finalizar la edición de la nota de venta, Pide al <b>Encargado</b> que ingrese su contraseńa para continuar :</h5>
	<div class="col-sm-3"></div>
	<div class="col-sm-6">
		<input type="password" id="mannager_password" class="form-control" style="box-shadow : 1px 1px 10px rgba( 0,225,0,0.5 );">
		<br>
		<br>
		<button
			type="button"
			class="btn btn-success form-control"
			onclick="finish_sale_return_before( <?php echo $ticket_id;?> );"
		>
			<i class="icon-ok-circle">Finalizar Edición</i>
		</button>
		<br>
		<br>
		<button
			type="button"
			class="btn btn-danger form-control"
			onclick="close_emergent();"
			id="close_emergent_btn"
		>
			<i class="icon-cancel-circled">Cancelar</i>
		</button>
	</div>
	<div class="col-sm-3"></div>
</div>
<?php

	/*function getReturnOptions( $link, $option_id = null ){
		$sql = "SELECT 
					id_motivo_devolucion AS return_motive_id, 
					nombre_motivo AS motive_name
				FROM ec_motivos_devolucion";
	}*/
	
	function build_accordeon( $ticket_id, $numero = 1, $link){
		include( 'ajax/db.php' );
		$validationTicket = new validationTicket( $link );
		$cont = 0;
		$resp = "";
		$sql="SELECT
				ax.row_id,
				ax.name,
				ax.quantity,
				ax.id_productos AS product_id
			FROM(
				SELECT
					pd.id_pedido_detalle AS row_id,
					p.nombre AS name,
					pd.cantidad - IF( p.es_maquilado = 0, 
						SUM( IF( pvu.id_pedido_validacion IS NULL, 0, pvu.piezas_validadas - ( - pvu.piezas_devueltas ) ) ),
						(SELECT
							ROUND( SUM( IF( pvu.id_pedido_validacion IS NULL, 0, pvu.piezas_validadas - ( - pvu.piezas_devueltas ) ) ) / cantidad )
							FROM ec_productos_detalle
							WHERE id_producto = p.id_productos
						)
					) AS quantity,
					p.id_productos
				FROM ec_pedidos_detalle pd
				LEFT JOIN ec_productos p
				ON p.id_productos = pd.id_producto
				LEFT JOIN ec_pedidos_validacion_usuarios pvu
				ON pvu.id_pedido_detalle = pd.id_pedido_detalle
				WHERE pd.id_pedido = {$ticket_id}
				GROUP BY pd.id_pedido_detalle
			)ax
			WHERE 1/*ax.quantity > 0 deshabilitado por Oscar 2023*/
			GROUP BY ax.row_id";
		//echo $sql;
		$eje = $link->query($sql)or die( "Error al consultar las herramientas : <br>{$link->error}<br><textarea>{$sql}</textarea>" );

		while( $r = $eje->fetch_row() ){
			$resp .= '<div class="accordion-item">';
		    	$resp .= '<h2 class="accordion-header" id="heading_'.$numero .'_'.$cont.'">';
			    	$resp .= '<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_'.$numero .'_'.$cont.'"'
			    	. ' aria-expanded="true" aria-controls="collapse_'.$numero .'_'.$cont.'" onclick=""'
			    	. 'id="herramienta_'.$numero .'_' . $cont . '" class="opc_btn">';
			        $resp .= $r[1] . " ( <b>{$r[2]}</b> ) pza" . ( $r[2] > 1 ? 's' : '' );
			      	$resp .= '</button>';
		    	$resp .= '</h2>';
		    	$resp .= '<div id="collapse_'.$numero .'_'.$cont.'" class="accordion-collapse collapse description" aria-labelledby="heading_'.$numero .'_' . $cont . '" data-bs-parent="#accordionExample">';
			    	$resp .= '<div class="accordion-body">';
			    	$resp .= $validationTicket->getValidationHistoric( $r[3], $ticket_id, NULL );
			    	//$resp .= getScannsDetail( $ticket_id,  );
			    	$resp .= '</div>';
		    	$resp .= '</div>';
		  	$resp .= '</div>';
			$cont ++;
		}
		$resp.= '<input type="hidden" id="contador_herramientas_' . $numero . '" value="' . $cont . '">';
		//$resp .= '</div>';
		return $resp;
	}
?>


<script type="text/javascript">
	function seek_product_to_return( obj, e ){
		var key = e.keyCode;
		if( key != 13 && e != 'intro' ){
			return false;
		}
		var url = "ajax/db.php?fl=getTicketDetailByProduct&txt=" + $( obj ).val().trim();
		url += "&ticket_id=" + localStorage.getItem( 'current_ticket' );
		var response = ajaxR( url ).trim().split( '|' );
		var response_case = response[0]; 
//alert( response[0] );
		switch( response_case ){
			case 'seeker':
				$( '#return_seeker_response' ).html( response[1] );
				$( '#return_seeker_response' ).css( 'display', 'block' );
			break;

			case 'quick_scann':
				$( '#return_seeker_response' ).html( response[1] );
				$( '#return_seeker_response' ).css( 'display', 'block' );
					load_ticketDetail();//recrga validacion
					getReturnPrevious();//recarga detalle de la validacion
					setTimeout( function (){
					$( '#return_seeker_response' ).css( 'display', 'none' );
					}, 3000 );
					$( '#close_emergent_btn' ).attr( 'disabled', true );

			break;

			case 'error':
				alert_scann( 'error' );
				$( '#return_seeker_response' ).html( response[1] );
				$( '#return_seeker_response' ).css( 'display', 'block' );
					setTimeout( function (){
						$( '#return_seeker_response' ).css( 'display', 'none' );
						load_ticketDetail();//recrga validacion
						getReturnPrevious();//recarga detalle de la validacion
					}, 3000 );
			break;

			default : 
				$( '#return_seeker_response' ).html( response );
				$( '#return_seeker_response' ).css( 'display', 'block' );
			break;	
		}
		$( '#return_seeker' ).val( '' );
	}

	function getValidationHistoric( product_id, ticket_id, sale_detail_id ){
		var url = "ajax/db.php?fl=getValidationHistoric&product_id=" + product_id;
		url += "&ticket_id=" + ticket_id + "&sale_detail_id=" + sale_detail_id;
		//alert( url );
		var response = ajaxR( url );
		//alert( response );
		$( '#return_seeker_response' ).html( response );
		$( '#return_seeker_response' ).css( 'display', 'block' );
	//emergente de aviso
		show_return_message();
	}

	function show_return_message(){
		//alert();
		var resp = `<br><div class="row">
						<div class="col-12 text-center">
							<h5 style="font-size : 300%;">Obligatorio revisar físicamente este producto. </h5>
							<h5 style="font-size : 200%;">En caso de tener este producto empacado, debes sacarlo para contarlo</h5>
							<p style="font-size : 150%;">Escribe la palabra 'DESEMPACADO' para continuar</p>
						</div>
						<div class="col-2"></div>
						<div class="col-8">
							<input type="text" id="return_msg_validation" class="form-control" onkeyup="string_to_upper_case( this );">
							<br>
							<button
								class="btn btn-success form-control"
								type="button"
								onclick="hide_return_message();"
							>
								<i class="icon-ok-circle">Aceptar</i>
							</button>
						</div>

					<div><br>`;
		$( '.emergent_content_2' ).html( resp );
		$( '.emergent_2' ).css( 'display', 'block' );
	}

	function string_to_upper_case( obj ){
		$( obj ).val( $( obj ).val().toUpperCase() );
	}
	function hide_return_message(){
		if( $( '#return_msg_validation' ).val() == '' ){
			alert( "El campo de confirmación no puede ir vacío." );
			$( '#return_msg_validation' ).focus();
			return false;
		}

		if( $( '#return_msg_validation' ).val().toLowerCase() != 'desempacado' ){
			alert( "El mesnaje de confirmación es incorrecto, escribe la palabra 'DESEMPACADO' para continuar." );
			$( '#return_msg_validation' ).select();
			return false;
		}
		close_emergent_2();
	}

	function save_return_product( ticket_id, sale_detail_id ){
	//recorre informacion de los productos a devolver
		var product_provider_request_data = "";
		var product_request_data = "";
		var sale_detail_id;
		//var limit = $( '#validation_resumen_list tr' ).length - 1;
		var limit = $( '#validation_resumen_list tr' ).length;
//alert( "limit : " + limit );
		if( !validateReturnProduct() ){
			return false;
		}
	//alert( limit );
		var stop = false;
		$( '#validation_resumen_list tr' ).each( function( index ){
			if( $( '#vrs_row_2_' + index ).val() > 0 && index < limit ){
				/*if( parseInt( $( '#vrs_row_3_' + index ).val() ) > parseInt( $( '#vrs_row_2_' + index ).html().trim() ) ){
					alert( "La cantidad que se va a devolver no puede ser mayor a la cantidad que " 
						+ ( index < ( limit - 1 ) ? "se validó" : "esta pendiente por validar"  ) );
					$( '#vrs_row_3_' + index ).select();
					stop = true;
					return false;
				}*/
				//if( index < ( limit - 1 ) ){
					product_provider_request_data += ( product_provider_request_data == "" ? "" : "|~|" );
					product_provider_request_data += $( "#vrs_row_0_" + index ).html().trim() + "~";
					product_provider_request_data += $( "#vrs_row_1_" + index ).html().trim() + "~";
					product_provider_request_data += $( "#vrs_row_2_" + index ).val() + "~";
					product_provider_request_data += $( "#vrs_row_3_" + index ).html().trim();
					sale_detail_id = $( "#vrs_row_0_" + index ).html().trim();

				//}else{

				//}
			}else if( parseFloat( $( '#row_without_validation' ).html().trim() ) > 0 && index == ( limit - 1 ) ){
				//product_request_data += ( product_request_data == "" ? "" : "|~|" );
				//alert( index + "\n" + $( "#vrs_row_0_" + index ).html() );
				product_request_data += $( "#vrs_row_0_" + index ).html().trim() + "~";
				product_request_data += $( "#vrs_row_1_" + index ).html().trim() + "~";
				product_request_data += $( "#row_without_validation" ).html().trim() + "~";
				product_request_data += $( "#vrs_row_3_" + index ).html().trim();
				sale_detail_id = $( "#vrs_row_3_" + index ).html().trim();
			}
		});
	//alert( product_provider_request_data + "\n" + product_request_data ); return false;
		if( stop ){
			return false;
		}else{
			var url =  "ajax/db.php?fl=saveSaleReturn&ticket_id=" + ticket_id;
			url += "&return_whith_validation=" + product_provider_request_data;
			url += "&return_whithout_validation=" + product_request_data;
			url += "&sale_detail_id=" + sale_detail_id;

	//alert(url ); return false;
			var response = ajaxR( url ).trim().split( '|' );
			if( response[0] == 'ok' ){			
				load_ticketDetail();//recrga validacion
				getReturnPrevious();//recarga detalle de la validacion
			}else{
				alert( "Error : " + response[0] );
			}
			$( '#close_emergent_btn' ).attr( 'disabled', true );
		}
//alert( `${product_provider_request_data} \n ${product_request_data}` );
	}
  
	function validateMannagerPassword(){
		var pss = $( '#mannager_password' ).val();
		if( pss == "" ){
			alert( "La contraseña del encargado no puede ir vacía " );
			$( '#mannager_password' ).focus();
			return false;
		}
		var url = "ajax/db.php?fl=validateMannagerPassword&pass=" + pss; 
		var response = ajaxR( url );
		if( response.trim() != 'ok' ){
			alert( response );
			return false;
		}
		return true;
	}

	function finish_sale_return_before( ticket_id ){
		if( ! validateMannagerPassword() ){
			return false;
		}
		global_ticket_has_return = 1;
		close_emergent();
	}

	function prevent_negative_number_( obj, e ){
		if( isNaN( obj.value ) ){
			alert( "En este campo solo puedes capturar números." );
			obj.value = '';
			var id = obj.id + `_alerta`;
			$( `#${id}` ).removeClass( "hidden" );
			setTimeout( function(){
				$( `#${id}` ).addClass( "hidden" );
			}, 5000 );
		}
		if( obj <= 0 ){
			alert( "No son admitidos valores menores a cero." );
			$( obj ).val('');
			return false;
		}
	}
</script>