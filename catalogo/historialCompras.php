<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Historial</title>
	<link rel="stylesheet" href="css/estilos.css">
</head>
<body>


	<div class="formHistorial">

		<div class="pedidos">

			<label for="">HISTORIAL PEDIDOS</label>
			
			<table class="historial" id="ejemplo">
				<tr>
					<th>PEDIDO #</th>
					<th>FECHA</th>
					<th colspan="2">ENVIAR A</th>
					<th>TOTAL</th>
					<th>STATUS</th>
					<th>FACTURA</th>
					<th>DETALLE</th>
				</tr>
				<tr>
					<td>123456</td>
					<td>25/10/2019</td>
					<td colspan="2">CLIENTE CLIENTE</td>
					<td>$1200.00</td>
					<td>ENVIADO</td>
					<td>--------</td>
					<td><a href="">Ver detalle</a></td>
				</tr>
				<tr>
					<td>748596</td>
					<td>25/10/2019</td>
					<td colspan="2">CLIENTE CLIENTE</td>
					<td>$800.00</td>
					<td>ENVIADO</td>
					<td>--------</td>
					<td><a href="">Ver detalle</a></td>
				</tr>
				<tr>
					<td>123456</td>
					<td>25/10/2019</td>
					<td colspan="2">CLIENTE CLIENTE</td>
					<td>$1200.00</td>
					<td>ENVIADO</td>
					<td>--------</td>
					<td><a href="">Ver detalle</a></td>
				</tr>
				<tr>
					<td>748596</td>
					<td>25/10/2019</td>
					<td colspan="2">CLIENTE CLIENTE</td>
					<td>$800.00</td>
					<td>ENVIADO</td>
					<td>--------</td>
					<td><a href="">Ver detalle</a></td>
				</tr>
				<tr>
					<td>123456</td>
					<td>25/10/2019</td>
					<td colspan="2">CLIENTE CLIENTE</td>
					<td>$1200.00</td>
					<td>ENVIADO</td>
					<td>--------</td>
					<td><a href="">Ver detalle</a></td>
				</tr>
				<tr>
					<td>748596</td>
					<td>25/10/2019</td>
					<td colspan="2">CLIENTE CLIENTE</td>
					<td>$800.00</td>
					<td>ENVIADO</td>
					<td>--------</td>
					<td><a href="">Ver detalle</a></td>
				</tr>
				<tr>
					<td>123456</td>
					<td>25/10/2019</td>
					<td colspan="2">CLIENTE CLIENTE</td>
					<td>$1200.00</td>
					<td>ENVIADO</td>
					<td>--------</td>
					<td><a href="">Ver detalle</a></td>
				</tr>
				<tr>
					<td>748596</td>
					<td>25/10/2019</td>
					<td colspan="2">CLIENTE CLIENTE</td>
					<td>$800.00</td>
					<td>ENVIADO</td>
					<td>--------</td>
					<td><a href="">Ver detalle</a></td>
				</tr>
				<tr>
					<td>123456</td>
					<td>25/10/2019</td>
					<td colspan="2">CLIENTE CLIENTE</td>
					<td>$1200.00</td>
					<td>ENVIADO</td>
					<td>--------</td>
					<td><a href="">Ver detalle</a></td>
				</tr>
				<tr>
					<td>748596</td>
					<td>25/10/2019</td>
					<td colspan="2">CLIENTE CLIENTE</td>
					<td>$800.00</td>
					<td>ENVIADO</td>
					<td>--------</td>
					<td><a href="">Ver detalle</a></td>
				</tr>
				<tr>
					<td>123456</td>
					<td>25/10/2019</td>
					<td colspan="2">CLIENTE CLIENTE</td>
					<td>$1200.00</td>
					<td>ENVIADO</td>
					<td>--------</td>
					<td><a href="">Ver detalle</a></td>
				</tr>
				<tr>
					<td>748596</td>
					<td>25/10/2019</td>
					<td colspan="2">CLIENTE CLIENTE</td>
					<td>$800.00</td>
					<td>ENVIADO</td>
					<td>--------</td>
					<td><a href="">Ver detalle</a></td>
				</tr>
			</tabele>
		</div>
		

	</div>
	
</body>
</html>

<style>
	
	.formHistorial {
		position: absolute;
		background-color: rgb(255,180,180,0.5);
		width: 60%;
		height: 58%;
	}	

	.pedidos {
		position: absolute;
		width: 96%;
		height: 80%;
		/*border: solid 1px;*/
		top: 10%;
		left: 2%;
		overflow-y: scroll;
	}

	.historial {
		position: relative;
		top: 15%;
		width: 100%;
		table-layout: fixed;
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

	#ejemplo {
		overflow: scroll;
		height:100px;
     	width:100%;
	}



</style>