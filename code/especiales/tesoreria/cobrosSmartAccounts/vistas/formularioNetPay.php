<div class="row" style="padding : 20px;">
	<div id="message-container" class="text-center">
		<h2 class="text-center"><?php echo $resp->message;?></h2>
		<img src="../../../../img/img_casadelasluces/load.gif">
	</div>
<?php
	if( isset( $is_payment_petition ) && $is_payment_petition == true ){
?>
	<div class="row text-center">
		<div class="col-6 text-center">
			<button
				class="btn btn-info"
				onclick="buscar_repuesta_peticion_por_folio( '<?php echo $resp->folio_unico_transaccion;?>' );"
			>
				<i class="icon-arrows-cw">Recargar respuesta</i>
			</button>
		</div>
		<div class="col-6 text-center">
			<button
				class="btn btn-danger"
				onclick="stop_server_events( '<?php echo $resp->folio_unico_transaccion;?>' );"
			>
				<i class="icon-cancel-circled">Cancelar y cerrar</i>
			</button>
		</div>
	</div>
<?php
	}else{
?>
	<button
		class="btn btn-danger"
		onclick="stop_server_events( '<?php echo $resp->folio_unico_transaccion;?>' );"
	>
		<i class="icon-cancel-circled">Cancelar y cerrar</i>
	</button>
<?php
	}

	/*echo "<script type=\"text/JavaScript\">
	console.log( \"Si entra bien\" );
		informar_folio( '{$resp->folio_unico_transaccion}' );
	</script>";*/
?>
</div>	

<script>
	setTimeout( function (){ 
		//alert();
		informar_folio( '<?php echo $resp->folio_unico_transaccion;?>' );
	}, 100 );
// Crea una nueva conexión SSE
	var server_url = 'ajax/server_events.php?transaction_id=<?php echo $resp->folio_unico_transaccion;?>';
	var emergent_count_tmp = <?php echo $counter;?>;
	
</script>
