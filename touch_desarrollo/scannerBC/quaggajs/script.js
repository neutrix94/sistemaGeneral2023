document.addEventListener("DOMContentLoaded", () => {
	const $btnEscanear = document.querySelector("#btnEscanear"),
	//$input = document.querySelector("#codigo");
	$input = document.getElementById('buscadorLabel');
	$message = document.getElementById('msjConfirmacion');
	$inputB = document.getElementById('cantidad2');
	$btnEscanear.addEventListener("click", ()=>{
		window.open("../scannerBC/quaggajs/leer.html");
	});

	window.onCodigoLeido = datosCodigo => {
		// var request=ajaxR("../touch_desarrollo/ajax/buscaProductosCB.php?codigo="+datosCodigo.codeResult.code);
		// var respuesta = (request) ? JSON.parse(request) : '';

		//Interpreta respuesta de búsqueda
		// if(respuesta.code && respuesta.code==200){
		// 		console.log('Se encontró producto');
		// 		//Setea valores recuperados
		// 		var searchString = respuesta.products[0].orden_lista + ' | ' + respuesta.products[0].nombre + ' | ' + respuesta.products[0].precio_compra + ' | ' + datosCodigo.codeResult.code ;
		// 		$input.value = searchString;
		// 		$inputB.focus();
		// 		var audio = new Audio('../scannerBC/zxing/library/scanner.mp3');
		// 		audio.play();
		// }else{
		// 	console.log('No se ha encontrado producto');
		// 	hasError = true;
		// 	countTime = 0;
		// 	statusText = 'Parece que el código: '+ datosCodigo.codeResult.code +' no está registrado. Intenta nuevamente.';
		// 	datosCodigo.error = true;
		// }

		if(datosCodigo != 'closed' && !datosCodigo.error){
				console.log('Se encontró producto');
				//Setea valores recuperados
				$input.value = datosCodigo.inputText;
				$message.textContent = datosCodigo.msjConfirmacion;
				$inputB.focus();
				var audio = new Audio('../scannerBC/zxing/library/scanner.mp3');
				audio.play();
		}else{
			console.log('No se ha encontrado producto');
			statusText = datosCodigo.inputText;

		}

	}
});
