<!DOCTYPE html>
<html>
<head>
	<title>Interface</title>
<script type="text/javascript" src="../../../js/jquery-1.10.2.min.js"></script>
</head>
<body>
	<div class="global">
<!--		<center><span>Consultas Prediseñadas</span></center>-->
		<div id="izquierda">
			<table width="100%;">
				<tr><td align="center">Consultas</td></tr>
				<tr>
					<td><button onclick="carga_filtros(1,'busc_prod');" class="opc_btn" id="btn_1">Inventario en matriz con precio de compra</button></td>
				</tr>
				<tr>
					<td><button onclick="carga_filtros(2,'busc_prod');" class="opc_btn" id="btn_2"></button></td>
				</tr>
				<tr>
					<td><button onclick="carga_filtros(3,'busc_prod');" class="opc_btn" id="btn_3"></button></td>
				</tr>
				<tr>
					<td><button onclick="carga_filtros(4,'busc_prod');" class="opc_btn" id="btn_4"></button></td>
				</tr>
				<tr>
					<td><button onclick="carga_filtros(5,'busc_prod');" class="opc_btn" id="btn_5"></button></td>
				</tr>
				<tr>
					<td><button onclick="carga_filtros(6,'busc_prod');" class="opc_btn" id="btn_6"></button></td>
				</tr>
				<tr>
					<td><button onclick="carga_cfiltros(7,'busc_prod');" class="opc_btn" id="btn_7"></button></td>
				</tr>
				<tr>
					<td><button onclick="carga_filtros(8,'busc_prod');" class="opc_btn" id="btn_8"></button></td>
				</tr>
				<tr>
					<td><button onclick="carga_filtros(9,'busc_prod');" class="opc_btn" id="btn_9"></button></td>
				</tr>
				<tr>
					<td><button onclick="carga_filtros(10,'busc_prod');" class="opc_btn" id="btn_10"></button></td>
				</tr>
				<tr>
					<td><button onclick="carga_consulta(11,'busc_prod');" class="opc_btn" id="btn_11"></button></td>
				</tr>
				<tr>
					<td><button onclick="carga_consulta(12,'busc_prod');" class="opc_btn" id="btn_12"></button></td>
				</tr>
				<tr>
					<td><button onclick="carga_consulta(13,'busc_prod');" class="opc_btn" id="btn_13"></button></td>
				</tr>
				<tr>
					<td><button onclick="carga_consulta(14,'busc_prod');" class="opc_btn" id="btn_14"></button></td>
				</tr>
				<tr>
					<td><button onclick="carga_consulta(15,'busc_prod');" class="opc_btn" id="btn_15"></button></td>
				</tr>
				<tr>
					<td><button onclick="carga_consulta(16,'busc_prod');" class="opc_btn" id="btn_16"></button></td>
				</tr>
				<tr>
					<td><button onclick="carga_consulta(17,'busc_prod');" class="opc_btn" id="btn_17"></button></td>
				</tr>
				<tr>
					<td><button onclick="carga_consulta(18,'busc_prod');" class="opc_btn" id="btn_18"></button></td>
				</tr>
			</table>
		</div>
		<div id="derecha">
			<div id="filtros"></div>
		</div>
	</div>
</body>
</html>

<style type="text/css">
	/*.global{position: absolute;width: 100%;height: 100%;top:0;width: 0;}*/
	#izquierda{position: absolute;float:left;width: 25%; height: 95%;border: 0px solid;background:#FF9C33; overflow: auto;}
	#derecha{position: absolute;float:right;width: 74%; height: 95%; border: 0px solid;background: rgba(0,0,0,.5);right: 5px;overflow: auto;}	
	.opc_btn{width: 100%;padding: 8px;font-size: 15px;background: #278178;}
	.opc_btn:hover{padding: 15px;color:white;background: green;}
	#filtros{width: 100%;align-items: center;}
</style>

<script type="text/javascript">
	function carga_consulta(flag,extra){
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/consultas.php',
			cache:false,
			data:{fl:flag,extra},
			success:function(dat){

			}
		});
	}

	function carga_filtros(id){
	//enviamos datos por ajax para obtener los filtros
		$.ajax({
			type:'post',
			url:'header_consultas.php',
			cache:false,
			data:{id_herramienta:id},
			success:function(dat){
				$("#filtros").html(dat);
			}
		});
	}
</script>