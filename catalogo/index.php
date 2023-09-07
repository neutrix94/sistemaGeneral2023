<?php include 'web.php'; ?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Ubicaciones</title>
	<link rel="stylesheet" href="css/estilos.css">
	<script src="js/all.js"></script>
</head>
<body>

	<header>

		<nav class="menu2 ocultar">
			<ul>
				<li><a class="Inicio" href="index0.php" title="">INICIO</a></li>

				<?php
							
							$sql="SELECT id_sucursal, nombre, direccion, descripcion FROM sys_sucursales
							 WHERE id_sucursal = '3' OR id_sucursal = '4'
								ORDER BY descripcion ASC";
							$eje=mysql_query($sql)or die("Error al consultar familias!!!<br>".mysql_error());
							while($r=mysql_fetch_row($eje)){
					  			echo '<li><a href="#'.$r[0].'" title="">'.$r[1].'<br>'.$r[2].'</a>
								
								
					  				</li>';

								}	
						?>

			</ul>	
		</nav>

		<nav class="menu2">
				<ul>
					<li class="Inicio1"><a class="Inicio" href="index0.php" title="">INICIO</a></li>
						<?php
							
							$sql="SELECT id_sucursal, nombre, direccion, descripcion FROM sys_sucursales
							 WHERE id_sucursal = '2' OR id_sucursal = '3'
							 	OR id_sucursal = '4'
							 	OR id_sucursal = '8'
								OR id_sucursal = '9'
								OR id_sucursal = '10'
								ORDER BY descripcion ASC";
							$eje=mysql_query($sql)or die("Error al consultar familias!!!<br>".mysql_error());
							$posicion = 0;
							while($r=mysql_fetch_row($eje)){
					  			echo '<li class="'.$r[1].'"><a href="#'.$r[0].'" title="">'.$r[1].'<br>'.$r[2].'</a>
								
								
					  				</li>';

								}	
						?>
				
				</ul>
			</nav>

		</header>	

	<div class="genUbicacion">

		<div class="casa">
			
			<a target="_blank" href="https://goo.gl/maps/FWp79Tp2Zeczaz1v8" id="4" class="tooltip">Casa de las luces (Cuautitlán)<span class="tooltiptext">Ver Ubicación</span><img src="img/maps.png" class="mapa locacion"></a>
				<p>
					AVENIDA PROLONGACIÓN MORELOS S/N<br><br>
					COL. LAZARO CARDENAS, CUAUTITLÁN, MÉX.<br><br>
					A 1 MIN DEL SUBURBANO CUAUTITLÁN <br>
					FRENTE A BODEGA AURRERA <br><br>

					HORARIO: 10:00 A 22:00 DE LUNES A DOMINGO
				</p>
				<div class="ilustracion"><img class="imagenPequeña" src="img/cuauti.jpg" onclick="zoom('visible', this.src)"></div>
		

		</div>
		
		<div class="trojes">
			
			<a target="_blank" href="https://goo.gl/maps/d6GhmwXNQZu4o6aE8" id="3" class="tooltip">Casa de las luces (Trojes)<span class="tooltiptext">Ver Ubicación</span><img src="img/maps.png" class="mapa locacion"></a>
				<p>
					TEYAHUALCO 1, COL. EL TEJOCOTE<br><br>
					CUAUTITLÁN, MÉX.<br><br>
					HORARIO: 10:00 A 22:00 DE LUNES A DOMINGO
				</p>
				<div class="ilustracion"><img class="imagenPequeña" src="img/trojes.jpg" onclick="zoom('visible', this.src)"></div>

		</div>
		
		<div class="lopez">
			
			<a target="_blank" href="https://goo.gl/maps/beVskjWkSt8b7TGW8" id="8" class="tooltip">Casa de las luces (López)<span class="tooltiptext">Ver Ubicación</span><img src="img/maps.png" class="mapa locacion"></a>
			<p>
				VIA JOSÉ LOPEZ PORTILLO KM 23 174<br><br>
				SANTA MARIA CUAUTEPEC, TULTITLÁN, MÉX.<br><br>
				A 100 MTS DE COSMOPOL <br>	 	 
				ESTACION MEXIBUS REAL DEL BOSQUE <br><br>
				HORARIO: 10:00 A 22:00 DE LUNES A DOMINGO
			</p>
			<div class="ilustracion"><img class="imagenPequeña" src="img/lopez.jpg" onclick="zoom('visible', this.src)"></div>
		</div>
		<div class="lago">
			
			<a target="_blank" href="https://goo.gl/maps/kdtftmcBQWBPefdw7" id="9" class="tooltip">Casa de las luces (Lago de Guadalupe)<span class="tooltiptext">Ver Ubicación</span><img src="img/maps.png" class="mapa locacion"></a>
			<p>
				AVENIDA LAGO DE GUADALUPE<br><br>
				COL. SAN MATEO TECOLOAPAN, ATIZAPAN, MÉX.<br>
				CD. LOPEZ MATEOS<br><br>
				A UN COSTADO DEL ZORRO ABARROTERO <br><br>
				HORARIO: 10:00 A 22:00 DE LUNES A DOMINGO
			</p>
			<div class="ilustracion"><img class="imagenPequeña" src="img/lago.jpg" onclick="zoom('visible', this.src)"></div>
		</div>
		<div class="centrourbano">
			
			<a target="_blank" href="#" id="10" class="tooltip">Casa de las luces (Centro Urbano)<span class="tooltiptext">Ver Ubicación</span><img src="img/maps.png" class="mapa locacion"></a>
			<p>
				AV. 1 DE MAYO ESQ. CON AV. TEOTIHUACAN<br><br>
				COL. ATLANTA, CUAUTITLÁN IZCALLI, MÉX.<br><br>
				HORARIO: 10:00 A 22:00 DE LUNES A DOMINGO
			</p>
			<div class="ilustracion"><img class="imagenPequeña" src="img/cu.jpg" onclick="zoom('visible', this.src)"></div>
		</div>
		<div class="sanmiguel">
			
			<a target="_blank" href="#" id="2" class="tooltip">Casa de las luces (San Miguel)<span class="tooltiptext">Ver Ubicación</span><img src="img/maps.png" class="mapa locacion"></a>
			<p>
				PLAZA SAN MIGUEL<br><br>
				CUAUTITLÁN IZCALLI, MÉX.<br><br>
				HORARIO: 10:00 A 22:00 DE LUNES A DOMINGO
			</p>
			<div class="ilustracion"><img class="imagenPequeña" src="img/sanmiguel.jpg" onclick="zoom('visible', this.src)"></div>
		</div>

	</div>

	<div id="zoom" class="zoomimg">
		<a class="cerrar" href="javascript: zoom('hidden', '')">X</a>
		<img id="imagenGrande" src=''">
	</div> 
	
</body>
</html>

<script language="javascript">
function zoom(visibilidad, imagen) {

document.getElementById('zoom').style.visibility = visibilidad;
document.getElementById('imagenGrande').src = imagen;

}
</script> 

<style>

	@font-face{
		font-family: 'Roboto';
		src: url(fonts/Roboto-Regular.ttf);
	}
	
	.genUbicacion {
		display: grid;
		grid-template-columns: 80%;
		justify-content: center;
		grid-row-gap: 20%;
		justify-content: center;
		position: absolute;
		background-color: transparent;
		width: 100%;
		height: 92%;
		top: 50px;
		overflow-y: scroll;
		z-index: -1;
	}

	header {
		position: fixed;
		width: 100%;
		height: 50px;
	}

	/* menu y submenus*/

	.ocultar {
		display: none;
	}

	.menu2  ul {
		width: 100%;
		list-style: none;
		justify-content: center;
		background-color: #FE2E2E;
		z-index: 1000;

	}

	.menu2 > ul {
		display: flex;
		height: 50px;
	}

	.menu2 li {
		text-align: center;
		flex-grow: 0.1;
	}

	.menu2 li a:hover {
		background: rgba(0,0,0,0.3);
	}

	.menu2 ul li a {
		display: block;
		padding: 6px 5px;
		color: #ffffff;
		text-decoration: none;
	}

	.menu2 ul li .Inicio {
		padding-top: 15.5px;
		padding-bottom: 15.5px;
	}

	#submenu2 {
		position: relative;
		z-index: 10;
		background-color: #FE2E2E;

	}

	#submenu2 > li a {
		padding: 10px 5px;
	}

	.menu2 li ul  {
		display: none;
		position: absolute;
		opacity: 0.9;

	}

	.menu2 li:hover > ul {
		display: block;
	}

	.centrourbano, .trojes, .casa, .lopez, .lago, .sanmiguel {
		position: relative;
		width: 100%;
		height: 350px;
		background-color: rgba(100,100,100,0.5);
		border: 1px solid grey;
			
	}

	.casa {
		margin-top: 5%;
	}

	.sanmiguel {
		margin-bottom: 23%;
	}

	.centrourbano a, .trojes a, .casa a, .lopez a, .lago a, .sanmiguel a {
		position: relative;
		top: 5%;
		left: 2%;
		text-decoration: none;
		color: black;
		font-weight: bold;
		font-size: 20px;
		/*border: 1px solid black;*/
		height: 100px;
	}

	p {
		font-family: 'Roboto';
		position: absolute;
		top: 40%;
		left: 3%;
		font-weight: 25px;
	}

	.mapa {
		position: relative;
		width: 5%;
		height: 14%;
	}


	.locacion {
		padding-left: 2%;
	}

	.ilustracion {
		position: relative;
		width: 50%;
		height: 80%;
		/*border: 1px solid black;*/
		float: right;
		top: 10%;
		right: 3%;
	}

	.ilustracion img{
		cursor: pointer;
		position: relative;
		width: 100%;
		height: 100%;
	}


	/*Emergente imagen*/

	.zoomimg {
		visibility: hidden;
		display: flex;
		justify-content: center;
		align-items: center;
		position: absolute;
		width: 100%;
		height: 100%;
		background-color: rgba(0,0,0,0.8);
	}

	.zoomimg img {
		width: 50%;
		height: 75%;
	}

	.zoomimg a {
		text-decoration: none;
		font-weight: bold;
		background-color: transparent;
		font-size: 30px;
	}

	.tooltip .tooltiptext {
		position: relative;
  		visibility: hidden;
  		width: 120px;
  		background-color: green;
  		color: black;
  		text-align: center;
  		border-radius: 6px;
  		padding: 5px 0;
		font-weight: 10px;
  		/* Position the tooltip */
  		position: absolute;
  		z-index: 1;
	}

	.tooltip:hover .tooltiptext {
  		visibility: visible;
	}

	/*=============AJUSTE PARA SMARTPHONE==============*/
@media (max-width: 480px){

	.genUbicacion {
		/*border: 1px solid black;*/
		grid-template-columns: 90%;
		width: 100%;
		top: 100px;
	}

	.ocultar {
		display: flex;
	}

	.ocultar ul {
		background-color: brown;
	}

	.CASA, .TROJES {
		display: none;
	}


	.Inicio1 {
		display: none;
	}

	.menu2 ul li .Inicio {
		padding-top: 18px;
		padding-bottom: 18px;
	}

	.menu2 ul {
		top: 50px;
		height: 50px;
	}


	.menu2 ul li a {
		padding: 11px 12px;
		font-size: 12px;
	}

	.centrourbano, .trojes, .casa, .lopez, .lago, .sanmiguel {
		width: 100%;
		height: 700px;
	}

	.centrourbano span, .trojes span, .casa span, .lopez span, .lago span, .sanmiguel span {
		visibility: hidden;
		position: relative;
		left: 10%;		
			
	}

	.centrourbano a, .trojes a, .casa a, .lopez a, .lago a, .sanmiguel a {
		font-size: 15px;	
	}

	.mapa {
		width: 11%;
		height: 6%;
	}

	.locacion {
		padding-left: 2%;
	}

	.ilustracion {
		float: none;
		position: absolute;
		right: 0%;
		width: 80%;
		height: 50%;
		left: 10%;
	}

	.ilustracion {
		top: 18%;
	}

	p {
		top: 75%;
		left: 1.5%;
	}

	.zoomimg img {
		width: 90%;
		height: 60%;
	}

	.tooltip:hover .tooltiptext {
  		visibility: hidden;
	}

</style>