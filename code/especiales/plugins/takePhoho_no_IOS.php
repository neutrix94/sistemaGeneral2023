
	<div id="video_container">
		<video id="video"></video>
		<img src="http://localhost/General2022/img/frames/largo_por_alto-removebg-preview.png" id="frame" width="100%">
	</div>
	<br>
	<div id="options_buttons">
		<button type="button" onclick="openCamera()" class="btn btn-info">
			<i class="icon-instagram" id="camera_btn">Abrir Camara</i>
		</button>
		<button type="button" id="boton" class="btn btn-success">
			<i class="icon-picture-outline">Tomar foto</i>
		</button>
		<p id="estado">
		</p>
		<div class="row">
			<div class="col-4">
				<img src="" id="img_1" width="100%">
			</div>
			<div class="col-4">
				<img src="" id="img_2" width="100%">
			</div>
			<div class="col-4">
				<img src="" id="img_3" width="100%">
			</div>
		</div>
		<canvas id="canvas" style="display: none;"></canvas>
	</div>

<script>

	// Declaramos elementos del DOM
	var $video = document.getElementById("video"),
		$canvas = document.getElementById("canvas"),
		$boton = document.getElementById("boton"),
		$estado = document.getElementById("estado")
		$video_container = document.getElementById("video_container");
	var localStream = 1;

	function tieneSoporteUserMedia() {
	    return !!(navigator.getUserMedia || (navigator.mozGetUserMedia || navigator.mediaDevices.getUserMedia) || navigator.webkitGetUserMedia || navigator.msGetUserMedia)
	}
	function _getUserMedia() {
	    if( localStream != 1 ){
    		document.getElementById( 'video_container' ).style.display = 'none';
    		document.getElementById ('camera_btn' ).innerHTML = 'Abrir Cámara';
    		$boton.style.display = 'none';
    		localStream.getTracks().forEach(function(track) {
			  track.stop();
			});
    		localStream = 1;
    		return false;
	    }
	    return ( (navigator.mozGetUserMedia || navigator.getUserMedia || navigator.mediaDevices.getUserMedia) || navigator.webkitGetUserMedia || navigator.msGetUserMedia).apply(navigator, arguments);
	   // return ( navigator.mediaDevices.getUserMedia ).apply(navigator, arguments);
	}
	//if (tieneSoporteUserMedia()) {
	function openCamera(){
	    _getUserMedia(
	        { audio : false, video: {facingMode: 'environment'} },
	        function (stream) {
	    		document.getElementById( 'video_container' ).style.display = 'block';
	    		document.getElementById( 'camera_btn' ).innerHTML = 'Cerrar Cámara';
	    		$boton.style.display = 'block';
	        	localStream = stream;
//	            console.log("Permiso concedido");
				$video.srcObject = stream;
				$video.play();

				//Escuchar el click
				$boton.addEventListener("click", function(){
					//Pausar reproducción
					$video.pause();
					//Obtener contexto del canvas y dibujar sobre él
					var contexto = $canvas.getContext("2d");
					$canvas.width = $video.videoWidth;
					$canvas.height = $video.videoHeight;
				//creación de imágen
					contexto.drawImage($video, 0, 0, $canvas.width, $canvas.height);
				//linea horizontal
					contexto.lineWidth = 3;
					contexto.strokeStyle = "#f00";
					contexto.beginPath();
					contexto.moveTo(100, 100);
					contexto.lineTo(100, 420);
					contexto.stroke();
				//linea vertical
					contexto.lineWidth = 3;
					contexto.strokeStyle = "#f00";
					contexto.beginPath();
					contexto.moveTo(100, 420);
					contexto.lineTo(550, 420);
					contexto.stroke();

					var foto = $canvas.toDataURL(); //Esta es la foto, en base 64
					//$estado.innerHTML = "Enviando foto. Por favor, espera...";
					var xhr = new XMLHttpRequest();
					xhr.open("POST", "ajax/db.php?fl=savePhoto", true);
					xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
					xhr.send(encodeURIComponent(foto)); //Codificar y enviar

					xhr.onreadystatechange = function() {
					    if(xhr.readyState == XMLHttpRequest.DONE && xhr.status == 200) {
					        console.log("La foto fue enviada correctamente");
					        console.log(xhr);
					      //  $estado.innerHTML = "Foto guardada con éxito. Puedes verla <a target='_blank' href='" + xhr.responseText + "'> aquí</a>";
					    	document.getElementById( 'img_1' ).setAttribute( 'src' , xhr.responseText );
					    }
					}

					//Reanudar reproducción
					$video.play();
				});
	        }, function (error) {
	            console.log("Permiso denegado o error: ", error);
	            $estado.innerHTML = "No se puede acceder a la cámara, o no diste permiso.";
	        });
	//	}
	}

	/*} else {
	    alert("Lo siento. Tu navegador no soporta esta característica");
	    $estado.innerHTML = "Parece que tu navegador no soporta esta característica. Intenta actualizarlo.";
	}*/
</script>

<style type="text/css">
	#video_container{
		position: relative;
		width: 100%;
		left: 0%;
		display: none;
	}
	#options_buttons{
		position: relative;
		top : 0;
	}
	#video{
		position: relative;
		width: 100%;
		left: 0%;
		z-index: 1;
	} 
	#frame{
		position: relative;
		margin-top: -87%;
		left: 0%;
		z-index: 2;
		width: 100%;
	}
	#boton{
		display: none;
	}
	/*
	#video, #frame{
		position: absolute;
		top: 0;
		left: 0;
	}
	#video{
		z-index: 10 !important;
	}
	#video::after{
		position: absolute;
		top: 0;
		content: "example";
		z-index: 20 !important;
	}
	#frame{
		z-index: 100000 !important;
		width: 130% !important;
		height: 500px;
	}*/
</style>