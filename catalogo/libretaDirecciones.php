<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Direcciones</title>
	<link rel="stylesheet" href="css/estilos.css">
</head>
<body>
	
	<div class="libretaDirecciones">
			
			<label for="">LIBRETA DE DIRECCIONES</label>

			<table class="direcciones">
				<tr>
					<th>Direccion de pago</th>
					<th>Direccion de envio</th>
				</tr>
				<tr>
				</tr>
			</table>
	</div>

</body>
</html>

<style>
	
	.libretaDirecciones {
		position: absolute;
		width: 60%;
		height: 58%;
		border: none;
		top: 5%;
		left: 15%;
		border: 1px solid black;
	}

	.libretaDirecciones label {
		font-weight: bold;
	}

	.direcciones {
		position: relative;
		top: 15%;
		left: 5%;
		width: 90%;
		border: 1px solid black;
	}

	table, th, td {
  		border-bottom: 1px solid black;
  		border-collapse: collapse;
	}

	table, td {
  		border-bottom: none;
	}

	th, td {
  		padding: 5px;
  		text-align: left;    
	}

	a {
		text-decoration: none;
	}

</style>