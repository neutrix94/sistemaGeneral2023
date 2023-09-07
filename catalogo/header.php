<!DOCTYPE html>
<html>
<head><meta http-equiv="Content-Type" content="text/html; charset=euc-jp">

	
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Casa de las Luces</title>
	<link rel="stylesheet" href="css/estilos.css">
	<link rel="stylesheet" href="css/flexboxgrid.min.css">
	<script src="js/all.js"></script>
	<script src="js/menuCategorias.js" type="text/JavaScript"></script>
	

</head>

<body>

	<header>

		<button name="menubar" id="menubar" onclick="myFunction()" class="dropbtn menubar">­­­­&#9776;</button>
			<div id="myDropdown" class="dropdown-content despliega">
				
				<h3>CATEGORIAS</h3>
			
  			</div>
		<div class="botones">
	
 	 	<input type="text" class="buscar" id="buscador" placeholder="Buscar producto..." onkeyup="buscar_prds(event,this);">
 		<a href="JavaScript:buscar_prds(event,this,1);" class="btnbuscar"><i style="" class="icono fas fa-search"></i></a>
 		<div id="res_busc" style="position: absolute;background: white;width:100%;height:250px;top:32px;overflow:auto;display: none;"></div>
 	
 	</div>

		</div>	

		<nav class="menu">
			<ul>
				<li><a href="index0.php" title="">Inicio</a></li>
				<li><a href="index.php" title="">Sucursales</a>
				<!--
				<ul id="submenu">
					<?php

						/*

						$sql="SELECT id_sucursal,nombre FROM sys_sucursales WHERE id_sucursal != '-1' AND id_sucursal != '1'
						AND id_sucursal != '5'
						AND id_sucursal != '6'
						AND id_sucursal != '7'
						ORDER BY id_sucursal";
						$eje=mysql_query($sql)or die("Error al consultar sucursales!!!<br>".mysql_error());
						while($r=mysql_fetch_row($eje)){
						  echo '<li><a href="#" title="">'.$r[1].'<br>hola</a></li>';

						}*/
					?>
				</ul>
				-->
				</li>

				<li><a href="#" title="">Productos</a>
					<ul id="submenu">
						<?php
							$sql="SELECT id_categoria,nombre FROM ec_categoria WHERE id_categoria != '1' AND id_categoria != '35'";
							$eje=mysql_query($sql)or die("Error al consultar familias!!!<br>".mysql_error());
							while($r=mysql_fetch_row($eje)){
					  			echo '<li><a href="productos.php?cat='.$r[0].'" title="">'.$r[1].'</a></li>';

							}
						?>
					</ul> 

				</li>
				<li><a href="#" title="">Contacto</a>
					<ul id="submenu">
						<li><a href="#"><i class="fas fa-phone-alt cont"></i>55 11063685</a></li>
						<li><a href="#"><i class="fab fa-whatsapp cont verde"></i>55 60606050</a></li>
						<li><a target="_blank" href="https://www.facebook.com/LacasadelasLucesMX/"><i class="fab fa-facebook-square cont azul"></i>Facebook</a></li>
						<li><a target="_blank" href="https://www.instagram.com/?hl=es-la"><i class="fab fab fa-instagram cont"></i>Instagram</a></li>
					</ul>

				</li>
			</ul>
		</nav>

	  </header>  

</body>
</html>

<script>
	
	/* When the user clicks on the button,
toggle between hiding and showing the dropdown content */
function myFunction() {
  document.getElementById("myDropdown").classList.toggle("show");
}

// Close the dropdown menu if the user clicks outside of it
window.onclick = function(event) {
  if (!event.target.matches('.dropbtn')) {
    var dropdowns = document.getElementsByClassName("dropdown-content");
    var i;
    for (i = 0; i < dropdowns.length; i++) {
      var openDropdown = dropdowns[i];
      if (openDropdown.classList.contains('show')) {
        openDropdown.classList.remove('show');
      }
    }
  }
}

</script>

<style>
	.cont {
		display: flex;
		position: relative;
		float: left;
		left: 8px;
		font-size: 18px;
	}

</style>

