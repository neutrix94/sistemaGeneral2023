document.addEventListener("DOMContentLoaded", () => {
	const $resultados = document.querySelector("#resultados");
	var readCodesArr = [];
	var barCode = '';
	var validateBD = false;
	var foundCode = false;
	Quagga.init({
		inputStream: {
			constraints: {
				width: 1920,
				height: 1080,
			},
			name: "Live",
			type: "LiveStream",
			target: document.querySelector('#contenedor')    // Or '#yourElement' (optional)
		},
		decoder: {
			readers: ['code_128_reader','ean_reader']
		}
	}, function (err) {
		if (err) {
			console.log(err);
			return
		}
		console.log("Initialization finished. Ready to start");
		Quagga.start();
	});

	Quagga.onDetected((data) => {
		if(window.opener){
			if(!foundCode){
				barCode = data.codeResult.code;
				readCodesArr.push(barCode);
				console.log(JSON.stringify(readCodesArr));
				//Genera comparación de código de barras
				if( (readCodesArr.length == 2 && readCodesArr[0] == readCodesArr[1]) || ( readCodesArr.length == 3 && ( (readCodesArr[0] == readCodesArr[2]) || (readCodesArr[1] == readCodesArr[2]) ) ) ){
						validateBD = true;
				}
				if(validateBD){
					//Pide confirmación para validar en BD
					var msjConfirmacion = 'El código por procesar es: '+barCode+ ' - Se identificaron los siguientes valores al escanear: - ' + JSON.stringify(readCodesArr);
					if (msjConfirmacion) {
						var request=ajaxR(window.location.href.replace('/scannerBC/quaggajs/leer.html','')+"/touch_desarrollo/ajax/buscaProductosCB.php?codigo="+data.codeResult.code);
						var respuesta = (request) ? JSON.parse(request) : '';
						document.getElementById("videoMesageQ").innerHTML = 'Buscando...';

						//Interpreta respuesta de búsqueda
						if(respuesta.code && respuesta.code==200){
							foundCode = true;
							console.log('Se encontró producto:');
							//Setea valores recuperados
							var searchString = respuesta.products[0].orden_lista + ' | ' + respuesta.products[0].nombre + ' | ' + respuesta.products[0].precio_compra + ' | ' + data.codeResult.code ;
							data.inputText = searchString;
							data.msjConfirmacion = msjConfirmacion;
							// var audio = new Audio('../scannerBC/zxing/library/scanner.mp3');
							// audio.play();
							window.opener.onCodigoLeido(data);
							window.close();
						}else{
							console.log('No se ha encontrado producto');
							data.inputText = 'Parece que el código: '+ data.codeResult.code +' no está registrado. Intenta nuevamente.';
							data.error = true;
							document.getElementById("videoMesageQ").innerHTML = data.inputText;
							readCodesArr = [];
							barCode = '';
							validateBD = false;
							foundCode = false;
						}
					} else {
						readCodesArr = [];
						barCode = '';
						validateBD = false;
						foundCode = false;
					}
				}
			}
		}
	});

	Quagga.onProcessed(function (result) {
		var drawingCtx = Quagga.canvas.ctx.overlay,
			drawingCanvas = Quagga.canvas.dom.overlay;

		if (result) {
			if (result.boxes) {
				drawingCtx.clearRect(0, 0, parseInt(drawingCanvas.getAttribute("width")), parseInt(drawingCanvas.getAttribute("height")));
				result.boxes.filter(function (box) {
					return box !== result.box;
				}).forEach(function (box) {
					Quagga.ImageDebug.drawPath(box, { x: 0, y: 1 }, drawingCtx, { color: "green", lineWidth: 2 });
				});
			}

			if (result.box) {
				Quagga.ImageDebug.drawPath(result.box, { x: 0, y: 1 }, drawingCtx, { color: "#00F", lineWidth: 2 });
			}

			if (result.codeResult && result.codeResult.code) {
				Quagga.ImageDebug.drawPath(result.line, { x: 'x', y: 'y' }, drawingCtx, { color: 'red', lineWidth: 3 });
			}
		}
	});
	function ajaxR(url)
	{
		 if(window.ActiveXObject){
				 var httpObj = new ActiveXObject("Microsoft.XMLHTTP");
		 }
		 else if (window.XMLHttpRequest)
		 {
				 var httpObj = new XMLHttpRequest();
		 }
		 httpObj.open("POST", url , false, "", "");
		 httpObj.send(null);
		 return httpObj.responseText;
	};

	//Función para detener lectura
	document.getElementById('closeModalBC').addEventListener('click', () => {
		//Cierra modal y restablece lector
		console.log('Close');
		window.opener.onCodigoLeido('closed');
		window.close();
	})

});
