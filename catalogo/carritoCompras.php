<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Carrito de compras</title>
	<link rel="stylesheet" href="css/estilos.css">

</head>
<body>
	
	<div class="formCarrito">
	
		<div class="carrTitulo">

			<label for="Carrito" class="titulo">CARRITO DE COMPRAS</label>
			<h1 class="linea"></h1>
			
		</div>

		<div class="carr0">

		<div class="carr1">

		<table class="carritoTabla">	
			
			<thead class="tablaEncabezados">
				<tr>
					<th scope="col"><span>ARTICULO</span></th>
					<th scope="col"><span>PRECIO</span></th>
					<th scope="col"><span>CANTIDAD</span></th>
					<th scope="col"><span>SUBTOTAL</span></th>
				</tr>
			</thead>
				<tbody class="ajuste">
					<tr>
						<td class="prodDesc">
							<a href="">
								<span>
									<span>
										<img src="img/inf_santa.png" alt="">
									</span>
								</span>
							</a>
							<div class="descripcion">
								<strong>
									<a href="">Inflable santa 60 cm Inflable santa 60 cm Inflable santa 60 cm Inflable santa 60</a>
								</strong>
							</div>
						</td>
						<td>4500</td>
						<td>1</td>
						<td>4500</td>
					</tr>

					<tr>
						<td colspan="100" class="btnEl">
							<div class="contbtnEliminar">
			
								<button class="btnEliminarCarrito" onmouseout="this.style.color='grey'" onmouseover="this.style.color='white'">Eliminar artículo</button>

							</div>
						</td>
					</tr>
					<tr>
						<td class="prodDesc">
							<a href="">
								<span>
									<span>
										<img src="img/inf_santa.png" alt="">
									</span>
								</span>
							</a>
							<div class="descripcion">
								<strong>
									<a href="">Inflable santa 60 cm Inflable santa 60 cm Inflable santa 60 cm Inflable santa 60</a>
								</strong>
							</div>
						</td>
						<td>4500</td>
						<td>1</td>
						<td>4500</td>
					</tr>
					
					<tr>
						<td colspan="100" class="btnEl">
							<div class="contbtnEliminar">
			
								<button class="btnEliminarCarrito" onmouseout="this.style.color='grey'" onmouseover="this.style.color='white'">Eliminar artículo</button>

							</div>
						</td>
					</tr>
					<tr>
						<td class="prodDesc">
							<a href="">
								<span>
									<span>
										<img src="img/inf_santa.png" alt="">
									</span>
								</span>
							</a>
							<div class="descripcion">
								<strong>
									<a href="">Inflable santa 60 cm Inflable santa 60 cm Inflable santa 60 cm Inflable santa 60</a>
								</strong>
							</div>
						</td>
						<td>4500</td>
						<td>1</td>
						<td>4500</td>
					</tr>
					
					<tr>
						<td colspan="100" class="btnEl">
							<div class="contbtnEliminar">
			
								<button class="btnEliminarCarrito" onmouseout="this.style.color='grey'" onmouseover="this.style.color='white'">Eliminar artículo</button>

							</div>
						</td>
					</tr>
					

				</tbody>
				

		</table>	

		</div>
		
			<div class="btns3">

						<button class="btn1" onmouseout="this.style.color='white'" onmouseover="this.style.color='yellow'">SEGUIR COMPRANDO</button>
				
				<div class="btns2">	
					
						<button class="btn2" onmouseout="this.style.color='white'" onmouseover="this.style.color='yellow'">VACIAR CARRITO</button>
					
						<button class="btn3" onmouseout="this.style.color='white'" onmouseover="this.style.color='yellow'">ACTUALIZAR CARRITO</button>

				</div>	
			</div>

		</div>

			<div class="carr2">
				<div class="carrTitulo">

					<label for="Carrito" class="resTitulo">RESUMEN</label>
			
				</div>

				<div class="subTotal sub1"><p>Subtotal</p><p>$4,500.00</p></div>
				<div class="envio sub1"><p>Envío</p><p>$499.00</p></div>
				<div class="totalPedido sub1"><p>Total del pedido</p><p>4,999.00</p></div>
				<span class="delimiter1"></span>
				<button class="btnPagar" onmouseout="this.style.color='white'" onmouseover="this.style.color='yellow'">IR A PAGAR</button>

			</div>
		
		</div>

</body>
</html>

<style>
	
	
	

	.formCarrito {
		display: block;
		position: absolute;
		width: 100%;
		height: 100%;
		background-color: rgba(189,189,189,0.5);
	}

	.carr0 {
		display: block;
		position: relative;
		border: 1px solid black;
		top: 20%;
		width: 65%;
		height: 60%;
		left: 2%;

	}

	.carr1 {
		position: absolute;
		width: 100%;
		height: 32%;
		border-bottom: solid 1px grey;
		float: left;
	}

	.ajuste {
		width: 100%;
		height: 100%;
	}



	tbody tr td img {
		position: relative;
		width: 150%;
		height: 150%;
	}

	.fotoDesc {
		border: 1px solid black;
		display: flex;
		width: 30%;
		height: 50%;
	}

	.descripcion {
		position: absolute;
		border: 1px solid red;
		width: 80%;
		left: 18%;
		height: auto;
		top: 20%;
	}

	.descripcion a {
		text-decoration: none;
		color: black;
		font-weight: 10;
	}

	.contbtnEliminar {
		display: flex;
		position: relative;
		justify-content: flex-end;
		align-items: center;
		border-bottom: solid 1px grey;
		width: 101.4%;
		left: -0.8%;
		height: 100%;
	}

	.btnEliminarCarrito {
		cursor: pointer;
		position: relative;
		color: grey;
		width: 150px;
		height: 40px;
		right: 2%;
		border: none;
		background-color: white;
	}

	.btnEliminarCarrito:hover {
		background-color: rgb(0,0,0,0.5);
	}

	.btnEl {
		position: relative;
		height: 70px;
		/*border: 1px solid black;*/
	}

	.carr2 {
		display: block;
		justify-content: center;
		position: absolute;
		width: 25%;
		height: 70%;
		float: right;
		border: solid 1px;
		top: 20%;
		right: 2%;
	}

	table, th, td {
  		border-bottom: 1px solid grey;
  		border-collapse: collapse;
	}

	table, td {
  		border-bottom: none;
	}

	th, td {
  		padding: 5px;
  		text-align: left;    
	}

	td {
		display: table-cell;
		vertical-align: inherit;
	}

	.carritoTabla {
		position: relative;
		width: 100%;
	}

	.prodDesc {
		display: flex;
		align-items: center;
		/*border: 1px solid black;*/
		position: relative;
		top: 10%;
		height: 100%;
	}

	.prodDesc img {
		position: relative;
		left: 20%;
		top: 50%;
	}

	.prodDesc span {
		position: relative;
		top: 10%;
	}

	.prodDesc a {
		position: relative;
		width: 9%;
		height: 4%;
		/*border: 1px solid black;*/
	}

	.articulo:first-child {
	width: 45%;
	}

	.resTitulo {
		display: flex;
		position: absolute;
		left: 5%;
		top: 5%;
		font-weight: bold;
	}

	.subTotal {
		position: relative;
		width: 89%;
		height: 8%;
		border-top: 1px solid black;
		border-bottom: 1px solid black;
		
	}

	.envio {
		position: relative;
		width: 89%;
		height: 8%;
		border-bottom: 1px solid black;
	
	}

	.totalPedido {
		position: relative;
		border-bottom: none;
		width: 89%;
		height: 16%;
		
	}

	.sub1 {
		display: flex;
		justify-content: space-between;
		align-items: center;
		top: 15%;
		left: 6%;
		border-color: grey;

	}

	.delimiter1 {
		position: relative;
		top: 15%;
		border-top: 1px solid grey;
    	display: block;
    	margin: 10px 0rem;
	}

	.btnPagar {
		position: absolute;
		width: 90%;
		height: 8%;
		top: 58%;
		left: 5%;
		border: none;
		background-color: red;
		color: white;
		font-size: 14px;
		cursor: pointer;

	}

	.btnPagar:hover {
		background-color: rgb(39,102,49,0.8);
	}

	.btns3 {
		display: flex;
		justify-content: space-between;
		align-items: center;
		position: absolute;
		top: 80%;
		width: 100%;
		height: 10%;
		border: solid 1px black;

	}

	.btns2 {
		position: relative;
		justify-content: space-between;
		align-items: center;
		display: flex;
		/*border: 1px solid black;*/
		width: 47%;
		height: 70%;
	}

	.btn1, .btn2, .btn3 {
		position: relative;
		width: 170px;
		height: 37px;
		border: none;
		background-color: rgb(0,0,0);
		color: white;
		font-size: 14px;
		cursor: pointer;
	}

	.btn1:hover, .btn2:hover, .btn3:hover {
		background-color: rgb(39,102,49,0.8);
	}

	

</style>