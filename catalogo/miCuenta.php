<?php include 'web.php'; ?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>Mi cuenta</title>
	<link rel="stylesheet" href="css/estilos.css">
	<script src="js/all.js"></script>
	<script src="https://code.jquery.com/jquery-3.2.1.js"></script>

</head>
<body>

	<div class="miCuenta">
		<header class="encabezado">
			
			<div class="contacto">
				<div class="redes uno">Contactanos: cdelasluces@gmail.com</div>
				<div class="redes dos"><i class="fas fa-phone-alt phone"></i>55 11063685</div>
				<div class="redes tres"><a target="_blank" href="https://www.facebook.com/LacasadelasLucesMX/"><i class="fab fa-facebook-square face"></i>Facebook</a></div>
				<div class="redes tres insta"><a target="_blank" href="https://www.instagram.com/?hl=es-la"><i class="fab fab fa-instagram face"></i>Instagram</a></div>
			</div>
			<div class="cuenta">
				<div class="micuenta"><a href="">Micuenta</a></div>
				<div class="micuenta ir"><a href="">Ir a pagar</a></div>
			</div>

		</header>
		<div class="contenedor3">
			<div class="logoCasa"><img src="img/logo-casa.png" alt=""></div>
			<div class="buscador"><input type="text" placeholder="Buscar..."></div>
			<div class="actions">
				<button><i class="fas fa-search"></i></button>
			</div>
			<div class="usuario">
				<div class="carritoUser">
					<div class="contador"><p>0</p></div>
					<a class="iconoCarrito" href="carritoCompras.php" onmouseout="this.style.color='black'" onmouseover="this.style.color='#E3C00E'">
						<i class="fas fa-shopping-cart"></i>
					</a>
				</div>
				<div class="carritoUser"><i class="far fa-user iconoUser"></i></div>
				<div class="cuentaSesion">
					<a href="" class="Cuenta">Mi cuenta</a>
					<a href="" class="cerrarSesion">Cerrar sesión</a>
				</div>
			</div>
		</div>

			
			<nav class="menu2">
				<ul>
					<li><a href="index.php" title="">Inicio</a></li>

						<?php
							$sql1="SELECT nombre FROM ec_subcategoria
							WHERE id_categoria != '1' AND id_categoria!= '35'";

							$sql="SELECT id_categoria,nombre FROM ec_categoria
							 WHERE id_categoria != '1' AND id_categoria != '35'";
							$eje=mysql_query($sql)or die("Error al consultar familias!!!<br>".mysql_error());
							while($r=mysql_fetch_row($eje)){
					  			echo '<li><a href="productos.php?cat='.$r[0].'" title="">'.$r[1].'</a>
								
										<ul id="submenu">
											<li><a href="productos.php?cat='.$r[0].'" title="">'.$r[1].'</a></li>	
										</ul>

					  				</li>';

								}	
						?>
				
				</ul>
			</nav>


		<div class="filtra">
			<div class="filtra1">
				
				<ul class="items">
					<li class="seleccion"><a href="" onmouseout="this.style.color='white'" onmouseover="this.style.color='yellow'">Mi cuenta</a></li>
					<li class="seleccion"><a href="#" onclick="cargaContenido()" onmouseout="this.style.color='white'" onmouseover="this.style.color='yellow'">Mis pedidos</a></li>
					<li class="seleccion"><a href="" onmouseout="this.style.color='white'" onmouseover="this.style.color='yellow'">Mi lista de articulos</a></li>
					<li><span class="delimiter"></span></li>
					<li class="seleccion"><a href="" onmouseout="this.style.color='white'" onmouseover="this.style.color='yellow'">Libreta de direcciones</a></li>
					<li class="seleccion"><a href="" onmouseout="this.style.color='white'" onmouseover="this.style.color='yellow'">Informacion de laCuenta</a></li>
					<li><span class="delimiter"></span></li>
					<li class="casa01">La casa de las luces</li>
				</ul>
				
			</div>
			<div class="filtra2" id="contenido">
				
			</div>
		</div>

	</div>
	
</body>
</html>

<script>
	
	function cargaContenido() {
		$('#contenido').load('historialCompras.php');
	}

</script>




<style>
	
	.items {
		margin: 10% 0 0;
	}
	
	.items li a{
		text-decoration: none;
		list-style: none;
		font-size: 14px;
		color: white;
		display: block;
		padding-top: 4px;
		padding-bottom: 4px;
		padding-left: 5px;

	}

	.items li {
		color: white;
		font-size: 14px;
		border-left: 3px solid transparent;
    	display: block;
    	padding: 5px 18px 5px 15px;
	}

	.items::before {
		content: " ";
		display: table;

	}

	.items li a::before {
		content: " ";
		display: table;

	}

	.items li a::after {
		content: " ";
		display: table;

	}

	.items .seleccion:hover {
		background: rgba(185,0,0,0.5);
	}
	
	.items .casa01 {
		position: relative;
		width: 55%;	
		left: 17%;
		/*border: 1px solid black;*/
	}

	

	.delimiter {
		border-top: 1px solid #d1d1d1;
    	display: block;
    	margin: 10px 0.1rem;
	}

	.actions {
		position: relative;
		top: 44%;
		left: -19%;
	}

	.actions button {
		background-color: transparent;
		border: none;
		color: grey;
	}

	@font-face{
	font-family: 'Roboto';
	src: url(fonts/Roboto-Regular.ttf);
	}
	
	.miCuenta {
		position: absolute;
		width: 100%;
		height: 100%;
		background-color: rgba(189,189,189,0.5);
	}

	.encabezado {
		display: flex;
		position: relative;
		align-items: center;
		justify-content: space-between;
		width: 85%;
		height: 10%;
		left: 7.5%;
		background-color: transparent;
		border-bottom: 1px solid black;
	}

	.contacto {
		display: flex;
		align-items: center;
		justify-content: flex-start;
		width: 60%;
		height: 90%;
		/*border: 1px solid black;*/
		/*background-color: rgba(0,0,0,0.5);*/
	}

	.cuenta {
		display: flex;
		align-items: center;
		justify-content: flex-end;
		position: relative;
		width: 30%;
		height: 90%;
		/*border: 1px solid black;*/
		/*background-color: rgba(0,0,0,0.5);*/
	}

	.redes {
		font-family: 'Roboto';
		font-size: 15px;
		display: flex;
		align-items: center;
		justify-content: center;
		border-right: 1px solid grey;
		width: 18%;
		height: 25%;
	}

	.uno {
		/*border: 1px solid black;*/
		display: flex;
		justify-content: flex-start;
		width: 40%;
	}

	.tres {
		display: flex;
		align-items: center;
	}

	.tres a {
		display: flex;
		position: relative;
		align-items: center;
		text-decoration: none;
		color: black;
		font-family: 'Roboto';
		font-size: 15px;
	}

	.micuenta {
		display: flex;
		justify-content: center;
		align-items: center;
		border-right: 1px solid grey;
		width: 25%;
		height: 25%;
	}

	.micuenta a {
		font-family: 'Roboto';
		font-size: 15px;
		text-decoration: none;
		color: black;
	}

	.ir {
		border-right: none;
	}

	.face {
		padding-right: 5%;
		font-size: 22px;
		color: red;
	}

	.insta {
		border-right: none;
	}

	.phone {
		padding-right: 5%;
		font-size: 18px;
		color: red;
	}

	.contenedor3 {
		position: relative;
		left: 5%;
		width: 90%;
		height: 21%;
		display: flex;
		justify-content: space-around;
		/*border: 1px solid black;*/
	}

	.logoCasa {
		display: flex;
		align-items: center;
		justify-content: center;
		width: 10%;
		height: 100%;
		/*border: 1px solid black;*/
	}

	.logoCasa img {
		width: 60%;
		height: 80%;
	}

	.buscador {
		display: flex;
		align-items: center;
		justify-content: center;
		width: 50%;
		height: 100%;
		/*border: 1px solid black;*/
	}

	.buscador input {
		border-color: red;
		padding-left: 2%;
		width: 50%;
		height: 25%;
		border-radius: 50px;
		font-size: 14px;
		color: grey;
	}

	.usuario {
		display: flex;
		align-items: center;
		justify-content: center;
		width: 20%;
		height: 100%;
		/*border:1px solid black;*/
	}

	.carritoUser {
		display: flex;
		align-items: center;
		justify-content: center;
		width: 25%;
		height: 40%;
		/*border: 1px solid black;*/
	}

	.contador {
		display: flex;
		justify-content: center;
		align-items: center;
		position: relative;
		top: -36%;
		left: 58%;
		width: 38%;
		height: 45%;
		background-color: red;
		color: white;
		border-radius: 15px;
		z-index: 1;
	}

	.contador p {
		position: relative;
		top: 5.5%;
		left: 2%;
		font-size: 14px;
	}

	.iconoCarrito {
		position: relative;
		text-decoration: none;
		color: black;
		font-size: 25px;
		left: -10%;
	}

	.iconoUser {
		font-size: 25px;
	}

	.cuentaSesion {
		display: block;
		/*border: 1px solid black;*/
		width: 35%;
	}
	
	.Cuenta {
		float: right;
		font-size: 14px;
		font-family: 'Roboto';
		text-decoration: none;
		color: black;
	}

	.cerrarSesion {
		float: right;
		text-decoration: none;
		font-size: 14px;
		font-family: 'Roboto';
		color: black;
	}

	.subCat {
		display: flex;
		width: 100%;
		height: 8%;
		background-color: brown;
		/*border: 1px solid black;*/
	}

	.filtra {
		display: flex;
		justify-content: center;
		width: 100%;
		height: 60.5%;
		/*border: 1px solid black;*/
	}

	.filtra1 {
		position: relative;
		top: 10%;
		width: 18%;
		height: 80%;
		/*border: 1px solid black;*/
		background-color: #FE2E2E;
	}

	.seccion1 {
		display: block;
		position: relative;
		top: 10%;
		width: 100%;
		height: 40%;
		border-bottom: 2px solid grey;
	}

	.seccion1 a {
		display: flex;
		position: relative;
		width: 100%;
		top: 13%;
		text-decoration: none;
		color: white;
		margin-top: 0.5%;
		left: 3%;
		font-family: 'Roboto';
		/*border: 1px solid black;*/
	}

	.seccion1 a:hover {
		background: rgba(0,0,0,0.5);
	}

	.seccion2 {
		position: relative;
		top: 10%;
		width: 100%;
		height: 30%;
		border-bottom: 2px solid grey;
	}

	.seccion2 a {
		display: flex;
		position: relative;
		width: 100%;
		top: 20%;
		text-decoration: none;
		color: white;
		margin-top: 0.5%;
		margin-left: 3%;
		font-family: 'Roboto';
	}

	.seccion2 a:hover {
		background: rgba(0,0,0,0.5);
	}


	.seccion3 {
		position: relative;
		top: 10%;
		width: 100%;
		height: 18%;
		/*border: 1px solid black;*/
	}

	.filtra2 {
		width: 70%;
		height: 100%;
		border: 1px solid black;
		border-bottom: none;
		border-top: none;
		/*overflow-y: scroll;*/
	}
	
	/* menu y submenus*/

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
		flex-grow: 0.05;
	}

	.menu2 li a:hover {
		background: rgba(0,0,0,0.3);
	}

	.menu2 ul li a {
		display: block;
		padding: 15.5px 20px;
		color: #ffffff;
		text-decoration: none;
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


</style>