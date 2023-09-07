<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Registro</title>
	<link rel="stylesheet" href="css/estilos.css">
</head>
<body>

	<div class="formRegistro">
		
		<div class="regTitulo">

			<label for="formRegistro" class="titulo">CREAR NUEVA CUENTA DE CLIENTE</label>
			<h1 class="linea"></h1>

		</div>
		<div class="form1">
			<div class="info"><label for="">INFORMACIÓN PERSONAL</label></div>
			<form class="datosClientesLinea">
				
				<input type="text" class="infoPersonal 1" placeholder="ID Cliente*" disabled="">
				<input type="text" class="infoPersonal 2" placeholder="Celular*">
				<input type="text" class="infoPersonal 3" placeholder="Nombre*" autofocus="">
				<input type="text" class="infoPersonal 4" placeholder="Id lista precio">
				<input type="text" class="infoPersonal 5" placeholder="RFC*">
				<input type="text" class="infoPersonal 6" placeholder="Fecha alta">
				<input type="text" class="infoPersonal 7" placeholder="Telefono*">
				<input type="text" class="infoPersonal 8" placeholder="Fecha modificación">

			</form>

		</div>
		<div class="form2">
			
			<div class="info"><label for="">INFORMACIÓN DE INICIO DE SESIÓN</label></div>
			<form class="datosSesion">

				<input type="text" class="infoSesion" placeholder="Correo*">
				<input type="text" class="infoSesion" placeholder="Contraseña*">
				<input type="text" class="infoSesion" placeholder="Confimar contraseña*">

			</form>


		</div>

		<div class="form3">
			
			<button class="btncreaCuenta">CREAR UNA CUNETA</button>

		</div>
	
	</div>
</body>
</html>

