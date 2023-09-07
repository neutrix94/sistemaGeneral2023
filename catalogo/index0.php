<?php 

	if(isset($_GET['suc'])){
		$suc_activa=$_GET['suc'];
	}else{
		$suc_activa=1;
	}
	
	include 'web.php';

 ?>
<!DOCTYPE html>
<html>
<head>
<style type="text/css">
.grid input[type=checkbox] {
  transform: scale(1.5);
  border: 0;
}
</style>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Casa de las Luces</title>
	<link rel="stylesheet" href="css/estilos.css">
	<link rel="stylesheet" href="css/flexboxgrid.min.css">
	<script src="js/all.js"></script>
	<script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="js/menuCategorias.js"></script>
</head>

</head>

<body>
	<?php include 'header.php'; ?>
		<button class="btnverProductos" onclick="carga_categorias();">

			<img src="img/productos_icono.png"><p class="ver"><br>
			Ver<br>Productos</p>

		</button>  
	
		
	<div class="prueba">
		
		<div class="mover">

			<div href="" onclick="plusDivs(-1)" class="next"><i style="" class="fas fa-chevron-left"></i></div>
			<div href="" onclick="plusDivs(1)" class="prev"><i style="" class="fas fa-chevron-right"></i></div>
			
		</div>

		<div class="slider">

			
					<img class="slide mov img" src="img/uno.jpg">
				
					<img class="slide mov img" src="img/dos.jpg">
				
					<img class="slide mov img" src="img/tres.jpg">
			
		
		</div>
		
	
		<div class="grid">

			<h1>FAMILIAS</h1>
		
			<?php
							
				$c=0;
				while ($fila = mysql_fetch_row($familia)) {
				$c++;

				echo '<div class="familias" id="familia_'.$c.'">

					<div class="grid-item 1 p"><img src="img/'.$c.'.png"></div>
					<div class="grid-item 2 d" onclick="location.href=\'productos.php?cat='.$fila[0].'\';"><a class="indexfam">'.$fila[1].'</a></div>
					<p align="center">
					
						<input type="checkbox" id="chk_fam_'.$c.'" value="'.$fila[0].'" onclick="colorea_familias('.$c.');"> <b style="padding-left:10px;">Seleccionar</b>
					</p>
					</div>';

				}

				echo '<input type="hidden" id="tope_categorias" value="'.$c.'">';

				/*	if (checkbox(this.checked) == true) {
				echo "seleccionado";
				}	*/

			?>

		</div>

		<footer class="footer">
			
			<div class="reds">

				<div><a class="azul" target="_blank" href="https://www.facebook.com/LacasadelasLucesMX/"><i class="fab fa-facebook-square cont1"></i>Facebook</a></div>
				<div class="bajo"><a class="blanco" target="_blank" href="https://www.instagram.com/?hl=es-la"><i class="fab fab fa-instagram cont1"></i>Instagram</a></div>				
				
			</div>
			<div class="dir">
				
				<p>
					AVENIDA PROLONGACIÓN MORELOS S/N COL. LAZARO CARDENAS,  CUAUTITLÁN, MÉX <br>
					A 1 MIN DEL SUBURBANO CUAUTITLÁN<br>
					FRENTE A BODEGA AURRERA <br>
					HORARIO: 10:00 A 22:00 DE LUNES A DOMINGO
				</p>

			</div>
			<div class="tels">

				<div><a href="#" class="blanco"><i class="fas fa-phone-alt cont1"></i>55 11063685</a></div>
				<div class="bajo"><a class="verde" href="#"><i class="fab fa-whatsapp cont1 verde1"></i>55 60606050</a></div>
				
			</div>

		</footer>

    </div>

<script>//cambio manual slider
	var slideIndex = 1;
	showDivs(slideIndex);

	function plusDivs(n) {
  		showDivs(slideIndex += n);
	}

	function currentDiv(n) {
  		showDivs(slideIndex = n);
	}

	function showDivs(n) {
	  var i;
  	var x = document.getElementsByClassName("slide");
  	var dots = document.getElementsByClassName("mov");
  	if (n > x.length) {slideIndex = 1}
  	if (n < 1) {slideIndex = x.length}
  	for (i = 0; i < x.length; i++) {
   	 x[i].style.display = "none";  
  	}
  	for (i = 0; i < dots.length; i++) {
    	dots[i].className = dots[i].className.replace("img", "");
  	}
  	x[slideIndex-1].style.display = "flex";  
  	dots[slideIndex-1].className += "img";
	}
</script>



<script>//cambio automatico slider
var myIndex = 0;
carousel();

function carousel() {
  var i;
  var x = document.getElementsByClassName("slide");
  for (i = 0; i < x.length; i++) {
    x[i].style.display = "none";  
  }
  myIndex++;
  if (myIndex > x.length) {myIndex = 1}    
  x[myIndex-1].style.display = "flex";  
  setTimeout(carousel, 5000);
}
</script>

</body>
</html>

<style>
	
	.dir {
		display: flex;
		justify-content: center;
		align-items: center;
		position: relative;
		width: 60%;
		height: 80%;
		border-left: 1px solid grey;
		border-right: 1px solid grey;
	}

	.tels div {
		position: relative;
		left: -5px;
	}

	.cont1 {
		padding-right: 25px;
	}

	.bajo {
		padding-top: 10px;
	}

	.dir p {
		font-size: 12px;
	}


	.blanco {
		text-decoration: none;
		color: white;
	}

	.verde {
		text-decoration: none;
		color: green;
	}

	.verde1 {
		font-size: 20px;
	}

	.azul {
		color: blue;
		text-decoration: none;
	}


	@media (max-width: 480px){

		.dir {
			width: 51%;
		}

		.dir p {
			line-height: 12px;
			font-size: 9px;
		}

		.reds a, .tels a {
			font-size: 10px;
		}

		.cont1 {
			padding-right: 15px;
		}


	}


</style>