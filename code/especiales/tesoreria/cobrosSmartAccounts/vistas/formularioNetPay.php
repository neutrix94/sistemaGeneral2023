<div class="row" style="padding : 20px;">
	<div id="message-container" class="text-center">
		<h2 class="text-center"><?php echo $resp->message;?></h2>
		<img src="../../../../img/img_casadelasluces/load.gif">
	</div>
<?php
	if( isset( $is_payment_petition ) && $is_payment_petition == true ){
?>
	<div class="row text-center">
		<div class="col-12 text-center">
			<!--button
				class="btn btn-danger"
				onclick="close_emergent();"
			>
				<i class="icon-cancel-circled">Cancelar y cerrar</i>
			</button-->
		</div>
		<!--Deshabilitado poroscar 2024-07-05 div class="col-6 text-center">
			<button
				class="btn btn-info"
				onclick="buscar_repuesta_peticion_por_folio( '<?php //echo $resp->folio_unico_transaccion;?>' );"
			>
				<i class="icon-arrows-cw">Recargar respuesta</i>
			</button>
		</div-->
		<div id="contador">00:00:00</div>
		<button style="display:none;" id="start">Start</button>
		<button style="display:none;" id="stop">Stop</button>
		<button style="display:none;" id="reset">Reset</button>
	</div>
<?php
	}else{
?>
	<button
		class="btn btn-danger"
		onclick="close_emergent();"
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
		$( '#start' ).click();
		informar_folio( '<?php echo $resp->folio_unico_transaccion;?>' );
	}, 100 );
// Crea una nueva conexión SSE
	var server_url = 'ajax/server_events.php?transaction_id=<?php echo $resp->folio_unico_transaccion;?>';
	var emergent_count_tmp = <?php echo $counter;?>;
	let horas = 0;

/**/
	let minutos = 0;
	let segundos = 0;
	let segundos_totales = 0;
	let intervalo;

	function actualizarContador() {
		segundos++;
		segundos_totales++;
		if( segundos_totales == $( '#max_execution_time' ).val() ){
			var content = `<div class="bg-danger text-center">
				<br>
				<br>
				<h2 class="text-light">Tiempo de espera agotado!</h2>
				<h2 class="text-light">Recarga la pagina y vuelve a escanear el ticket!</h2>
				<br>
				<br>
				<div class="row">
					<div class="col-3"></div>
					<div class="col-6 text-center">
						<button
							type="button"
							class="btn btn-warning form-control"
							onclick="location.reload();"
							style="font-size : 200% !important; border : 1px solid black;"
						>
							<i class="icon-spin3">OK</i>
						</button>
						<br>
						<br>
					</div>
				</div>
			</div>`;
			$( '.emergent_content' ).html( content );
			$( '.emergent' ).css( 'display', 'block' );
			return false;
		}
		if (segundos == 60) {
			segundos = 0;
			minutos++;
		}

		if (minutos == 60) {
			minutos = 0;
			horas++;
		}

		const formatoHoras = horas.toString().padStart(2, '0');
		const formatoMinutos = minutos.toString().padStart(2, '0');
		const formatoSegundos = segundos.toString().padStart(2, '0');

		document.getElementById('contador').innerText = `${formatoHoras}:${formatoMinutos}:${formatoSegundos}`;
	}

	document.getElementById('start').addEventListener('click', () => {
		if (!intervalo) {
			intervalo = setInterval(actualizarContador, 1000);
		}
	});

	document.getElementById('stop').addEventListener('click', () => {
		clearInterval(intervalo);
		intervalo = null;
	});

	document.getElementById('reset').addEventListener('click', () => {
		clearInterval(intervalo);
		intervalo = null;
		horas = 0;
		minutos = 0;
		segundos = 0;
		document.getElementById('contador').innerText = '00:00:00';
	});
</script>
