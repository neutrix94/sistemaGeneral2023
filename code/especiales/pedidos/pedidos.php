<?php
	include("../../../conectMin.php");
//implementacion Oscar 23.11.2019 para que no deje abrir desde un logueo que no sea matriz
	if($user_sucursal!=1){
		die("<script>alert('Necesita estar logueado como Matriz para poder acceder a esta pantalla!!!');location.href='../../../index.php?';</script>");
	}
//fin de cambio Oscar 23.11.2019

/*Implementación de Oscar 19.06.2018 para utilizar la pantalla para edición y */
//verificamos si existe un id de orden de compra
	if(isset($_GET['id_oc'])){
		$accion=$_GET['acc'];//extraemos la acción que tendrá a pantalla
		$id_oc=$_GET['id_oc'];//extraemos el id de la orden de compra si existe
		$ocultaFiltros=' style="display:none;" ';
		$ocultaBotones='';
	//sacamos los datos de la orden de compra
		$sql="SELECT id_proveedor,id_estatus_oc FROM ec_ordenes_compra WHERE id_orden_compra=$id_oc";
		$eje=mysql_query($sql)or die("Error al consultar los datos de la oc\n\n".$sql."\n\n".mysql_error());
		$datos_oc=mysql_fetch_row($eje);
		$status_oc=$datos_oc[1];//guardamos el estatus de la órden de compra
		//die($status_oc);
	}else{ 
		$accion=0;
		$id_oc=0;
		$ocultaFiltros='';
		$ocultaBotones=' style="display:none;" ';
	}
//construimos variables ocultas
	echo '<input type="hidden" id="tipo_accion" value="'.$accion.'">';
	echo '<input type="hidden" id="id_orden_compra" value="'.$id_oc.'">';
?>
<!DOCTYPE html>
<html>
<head>
	<title>Pantalla de Pedidos ( Compras )</title>
<!--Librerias de calendario-->
<link rel="stylesheet" type="text/css" href="../../../css/gridSW_l.css"/>
<script type="text/javascript" src="../../../js/calendar.js"></script>
<script type="text/javascript" src="../../../js/calendar-es.js"></script>
<script type="text/javascript" src="../../../js/calendar-setup.js"></script>
<!---->
<script type="text/javascript" src="../../../js/jquery-1.10.2.min.js"></script>
<script type="text/javascript" src="../../../js/jquery-ui.js"></script>
<script type="text/javascript">
	$( function() {
		$( "#simula_tooltip_grafica" ).draggable();
  	} );
	//$( function() {
    $( "#sortable" ).sortable();
    $( "#sortable" ).disableSelection();
  //} );
</script>
<script type="text/javascript" src="js/funcionesPedido.js"></script>
<script type="text/javascript" src="js/emergentes.js"></script>
<script type="text/javascript" src="js/simulador_grafica.js"></script>
<script src="../../../js/Highcharts-7.0.3/code/highcharts.js"></script>
<script src="../../../js/Highcharts-7.0.3/code/highcharts.js"></script>

<link rel="stylesheet" type="text/css" href="../../../css/bootstrap/css/bootstrap.css">

<!--link rel="stylesheet" type="text/css" href="../../../css/bootstrap/js/bootstrap.css"-->
<script type="text/javascript" src="../../../css/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="../../../css/bootstrap/js/bootstrap.bundle.min.js"></script>

<link rel="stylesheet" type="text/css" href="recepcionPedidos/styles.css">
<script type="text/javascript" src="recepcionPedidos/js/productProviderFunctions.js"></script>
<script type="text/javascript" src="recepcionPedidos/js/dataGrid.js"></script>
<script type="text/javascript" src="js/notes.js"></script>
<!-- Seteo de variables para plugin de fotos -->
<script type="text/javascript">
	var global_meassures_home_path = '../../../';
	var global_meassures_include_jquery = 0;
	var global_meassures_path_camera_plugin = '../../../';
	var global_save_meassure_img_path = '../recepcionBodega/';
	var global_save_meassure_type = 1;
	//var global_product_provider_path = "pedidos";
</script>
</head>
<body>

	<div class="emergent_proyection">
		<div class="emergent_content_proyection">

		</div>
	</div>

	<div class="emergente">
		<div class="row">
			<!--div class="col-1"></div-->
			<div class="col-12 emergent_content"></div>
			<!--div class="col-1"-->
				<button 
					type="button" 
					class="emrgent_btn_close"
					onclick="close_emergent();"
				>
					X
				</button>
			<!--/div-->
		</div>
	</div>
<!-- Emergente 2 -->
	<div class="emergent_2">
		<div class="emergent_content_2" tabindex="100"></div>
	</div>

<!-- Emergente 3 -->
	<div class="emergent_3">
		<div class="emergent_content_3" tabindex="100"></div>
	</div>

<!--div simulador de tooltip-->
	<div id="simula_tooltip_grafica">
	</div>

<!--div simulador de tooltip-->
	<div id="simula_tooltip">
	</div>
<!--subemergente-->
<div id="subemergente">
</div>
<!--div de emergente-->
	<div id="emer_prod">
		<p id="contEmergente" align="center" style="position:relative;width:50%;top:200px;border:2px solid white;left:25%;color:white;font-size:30px;">
			Cargando...<br>
			<img src="../../../img/img_casadelasluces/load.gif" width="25%;">
		</p>
	</div>
<!--Finaliza div emergente-->
<!--div global-->
<style>
	*{
		margin:0;
	}
	input[type=checkbox]{
  /* Double-sized Checkboxes */
  -ms-transform: scale(1.2); /* IE */
  -moz-transform: scale(1.2); /* FF */
  -webkit-transform: scale(1.2); /* Safari and Chrome */
  -o-transform: scale(1.2); /* Opera */
  padding: 10px;
	}
	#global{
		width:100%;
		height:100%;
		border:0;
		position: absolute;
		margin:0;
		/*padding:0;*/
		background-image: url(../../../img/img_casadelasluces/bg8.jpg);
	}
	#enc{
		width:98.5%;
		padding: 10px;	
		background:#83B141;
		color:white;
	}
	#busc{
		width:90%;
		padding: 12px;
		border-radius: 8px;
	}
	.comb{
		padding: 5px;
		width:80%;
		border-radius: 5px;
	}
	.combo{
		padding: 8px;
		border-radius : 5px;
		border : 1px solid gray;
	}
	.ch{
		padding:10px;
	}
	#cont{
		width: 97%;
		height:70%;
		border:1px solid;
		left:1%;
		position:absolute;
	}
	#t2{
		height: 420px;
		overflow: scroll;
	}
	#res_busc{
		width: 30%;
		height: 250px;
		background-color: white;
		position: absolute;
		overflow-y: auto;
		display: none;
		z-index: 1000;
	}
	#footer{
		position: absolute;
		bottom: .3%;
		width:100%;
		height: 12%;
		background:#83B141;
	}
	th{
		background: rgba(225,0,0,.5);
		color: white;
		height: 40px;
		font-size:90%;
	}
	#emer_prod{
		position: absolute;
		z-index: 2000;
		width: 100%;
		height: 100%;
		background: rgba(0,0,0,.7);
		display:none;
	}
	#lista_de_proveedores{
		width: 20%;
		background: white;
		color: black;
		top:38px;
		position:absolute;
		z-index: 1;
		display: none;
		text-align: left;
		font-size: 15px;
		max-height: 80%;
		overflow-y: auto; 
	}
	.provider_container_check{
		margin-left: 5px;
		margin-right: 5px;
		/*margin : 1px;*/
		padding : 10px;
		box-shadow: 1px 1px 10px rgba( 0, 0, 0, 0.5 );
	}
	.provider_container_check>input{
		margin-right : 10px;
	}
	.provider_container_check>label{
		position : relative;
		width: calc(100% - 25px);
		font-size: 90%;
	}

	#fechas_filtro{
		position: absolute;
		background: white;
		width: 20%;
		z-index: 1;
		color: black;
		display:none;
	}
	.fechas{
		width: 60%;
		padding: 5px;
	}
	.entrada_txt{
		width:98%;
		text-align: right;
		padding: 8px;
	}
	.bot{
		padding: 5px;
		border-radius: 5px;
		align-items: center;
	}
	.bot:hover{
		background:#ADFF2F;
		color:white;
	}
	.subtitulo{
		color:white;
		font-size: 80% !important;
	}
	#simula_tooltip{position:fixed;width: 29%;max-height:80%;background: white;border:2px solid gray;z-index: 5000;
		display: block; border-radius: 0 20px 20px 20px;overflow-y:auto; right: 0;
	}
	#simula_tooltip_grafica{position:absolute;width: 50%;background: white;border:2px solid gray;top:0;z-index: 200;
		border-radius: 0 20px 20px 20px;overflow-y:auto;display: none;right:2%;height: 450px;top:100px;overflow:scroll; /*max-height:80%;*/
	}
	#subemergente{position: absolute;z-index: 10000;width: 100%;height: 100%;top: 0%;left:0%;background: rgba(0,0,0,.8);overflow: auto;display: none;}
	
	.black{
		color : black !important;
	}
	input[type=checkbox ]{
		transform : scale( 1.8 );
	}
	.emergent_proyection{
		position : fixed;
		background-color: rgba( 0,0,0,.5 );
		width: 100%;
		height: 100%;
		top : 0;
		height: 100%;
		left : 0;
		z-index: 3000;
		display: none;

	}
	.emergent_content_proyection{
		position: relative;
		width: 60%;
		left : 20%;
		top : 10%;
		background-color: white;
	}
	#proyection_container{
		display : none;
	}
	.header_fixed{
		position: sticky;
		top: 0;
		background-color: red;
	}
	.box_shadow{
		box-shadow: 1px 1px 15px rgba( 0, 0 , 0, .5 );
	}
	.overflow_hidden{
		overflow: hidden;
	}
</style>
	<div id="global">
		<div id="enc" onclick="ocultar_busqueda();">
			<table width="100%" border="0">
				<tr>
					<td rowspan="2" width="20%">
						<input type="text" id="busc" placeholder="Buscar..." onkeyup="busqueda(event,this);">
						<div id="res_busc"></div>
					</td>
				</tr>
				<tr>
				<!---->
					<td align="center">
						<p class="subtitulo"><b>Desh:</b></p>
						<input type="checkbox" id="st_prd">;
					</td>
				<!--filtro de resurtimiento-->
					<td width="8%" colspan="3" width="40%" align="center" <?php echo $ocultaFiltros;?>>
							<p class="subtitulo"><b>Mostrar:</b></p>
							<select class="comb" id="resurt_prod">
								<option value="1">Resurtibles</option>
								<option value="2">No Resurtibles</option>
								<option value="3">Todos</option>
							</select>
					</td>
				<!--combo de familias-->
					<td align="center" width="8%" <?php echo $ocultaFiltros;?>>
						<p class="subtitulo"><b>Familia:</b></p>
						<select onchange="carga_combo(this,1);" id="fam" class="comb">
							<option value="-1">Todas</option>
						<?php
							$sql="SELECT id_categoria,nombre FROM ec_categoria WHERE id_categoria>1";
							$eje=mysql_query($sql)or die("Error al consultar familias!!!\n\n".$sql."\n\n".mysql_error());
							while($r=mysql_fetch_row($eje)){
								echo '<option value="'.$r[0].'">'.$r[1].'</option>';
							}
						?>
						</select>
					</td>
				<!--combo de tipos-->
					<td align="center" width="8%" <?php echo $ocultaFiltros;?>>
						<p class="subtitulo"><b>Tipo:</b></p>
						<select onchange="carga_combo(this,2);" id="tpo" class="comb">
							<option value="-1">------Filtrar------</option>
						</select>
					</td>
				<!--combo de subtipos-->
					<td width="8%" align="center" <?php echo $ocultaFiltros;?>>
						<p class="subtitulo"><b>Subtipo:</b></p>
						<select onchange="" id="sub_tpo" class="comb">
							<option value="-1">------Filtrar------</option>
						</select>
					</td>
				<!--Check de productos nuevos-->
					<td <?php echo $ocultaFiltros;?>>
						<p class="subtitulo" align="center"><b>Prods Nvos</b>
							<input type="checkbox" id="prods_nvos">			
						</p>	
					</td>
				<!--Check de excluir los que no se piden-->
					<td>
					<?php
						if($status_oc==4||$id_oc!=0){
							$st='style="display:none;"';
						}//fin del if staus OC
					?>
							<p class="subtitulo" align="center" <?php echo $st;?>><b>Mostrar No Pide</b>
								<input type="checkbox" id="incluye_invalidos" checked>			
							</p>	
					<?php
						if($status_oc==4){
							echo '<p align="center" style="color:white;font-size:25px;"><b>Orden de compra finalizada (Solo Vizualización)</b></p>';
						}
					?>
					</td>
				<!--Mostrar proveddores con precio en cero-->
					<td>
						<p class="subtitulo" align="center" <?php echo $st;?>><b>Orden Precio</b>		
						</p>	
							<select id="product_provider_type" class="form-control" style="width : 100px !important"><!-- class="form-control" -->
								<option value="0">-- Seleccionar --</option>
								<option value="1">Último comprado</option>
								<option value="2">Precio más bajo</option>
								<option value="3">Último precio actualizado</option>
							</select>	
					</td>

				<!--Mostrar proveddores con precio en cero-->
					<td>
						<p class="subtitulo" align="center" <?php echo $st;?>><b>Sin Precio</b>
							<input type="checkbox" id="incluye_sin_precio_proveedor">			
						</p>	
					</td>

				<!--Filtro para mostrar/ocultar productos ya pedidos-->
					<td width="8%" align="center">
						<p class="subtitulo" align="center"><b>Pendientes</b>
						<select class="comb" id="filt_pendientes">
							<option value="-1">Todos</option>
							<option value="1">Omitir pendientes de Recibir</option>
							<option value="2">Solo pendientes de Recibir</option>
						</select>
					</td>
				<!--Proveedores-->
					<td width="8%" align="center" <?php echo $ocultaFiltros;?>>
					<p style="position:relative;top:0;" class="subtitulo"><b>Proveedores:</b></p>
						<input type="button" value="Mostrar" id="mostrar_btn" onclick="ver_lista_prov(1);" style="padding:8px;">
						<div id="lista_de_proveedores">
							<div style="position : sticky; top: 0px; background : white !important; z-index : 5;">
								<p align="right">
									<input type="button" onclick="ver_lista_prov(0);" value="x" style="background:red;color:white;position:absolute;left:90%;">
								</p>
								<p align="center">
									<input type="checkbox" checked id="tod_prov" onclick="marca_desmarca();">Todos
								</p>
							</div>
						<?php
							$sql="SELECT id_proveedor,nombre_comercial FROM ec_proveedor";
							$eje=mysql_query($sql)or die("Error al consultar proveedores!!!\n\n".$sql."\n\n".mysql_error());
							$c=0;
						//	echo '<br>';
							$ids_pr="";
							while($r=mysql_fetch_row($eje)){
								$c++;
								$ids_pr .= ( $ids_pr != "" ? "," : "" );
								$ids_pr.=$r[0];
								echo '<div class="provider_container_check">';
									echo '<input type="checkbox" class="ch" id="pr_'.$c.'" value="'.$r[0].'" checked onchange="check_individual('.$c.');">';
									echo '<label onclick="set_provider_check_value( ' . $c . ' );">' . $r[1] . '</label>';
								echo "</div>";
							}
							echo '<br><input type="hidden" id="num_provs" value="'.$c.'">';//guardamos número de proveedores existentes
						?>
						</div>
					</td>
				<!--Tipo de pedido-->
					<td width="10%" align="center" <?php echo $ocultaFiltros;?>>
						<p class="subtitulo" align="center"><b>Tipo de Pedido</b>
						<select class="comb" onchange="muestra_fechas(this);" id="tipo_pedido">
							<option value="-1" onclick="muestra_fechas(0);">---Seleccionar---</option>
							<option value="1" onclick="muestra_fechas(0);">Pedido Inicial</option>
							<option value="2" onclick="muestra_fechas(1);">Resurtimiento</option>
							<option value="3" onclick="muestra_fechas(0);">Libre</option>
						</select>
					<!--Filtrado de fechas-->
						<div id="fechas_filtro"	>
							<p align="right" style="padding:5px;"><input type="button" value="-" title="minimizar" onclick="muestra_fechas(0);"></p>
							<table width="100%">
								<tr>
									<td colspan="1" align="center">Inicio temp ant</td>
									<td colspan="1" align="center">Día equivalente</td>
								</tr>
								<tr>
									<td width="50%">DE: <input type="text" id="fta_1"  onfocus="calendario(this);" class="fechas"></td>
									<td width="50%">A: <input type="text" id="fta_2"  onfocus="calendario(this);" class="fechas"></td>
								</tr>
								<tr>
									<td colspan="1" align="center"><br>Inicio temp actual</td>
									<td colspan="1" align="center"><br>Día actual</td>
								</tr>
								<tr>
									<td width="50%">DE: <input type="text" id="ftc_1" onfocus="calendario(this);" class="fechas"></td>
									<td width="50%">A: <input type="text" id="ftc_2" onfocus="calendario(this);" class="fechas"></td>
								</tr>
							</table><br>
						</div>
					</td>
				<!--Factor, boton generar pedido-->
					<td align="center" width="15%">
					<?php
						if($id_oc==0){
							echo '<b class="subtitulo">Factor:</b><input type="txt" value="1" style="width:40px;padding:8px;border-radius:5px;" id="factor">
							<input type="button" value="Generar" id="genera" style="padding:8px;border-radius:5px;margin:5px;" onclick="carga_pedido();">';
						}			
					?>
					</td>
				</tr>
			</table>
		</div><br>
	<!--Finaliza div de encabezado-->
	<!--Comienza div de contenido-->
		<div id="cont">
		<center>
				<table width="100%" border="0" id="list_prods_grid" onclick="ocultar_busqueda();">
					<tr>
						<th width="7%">Res</th>
						<th width="8%">Ord List</th>
						<th width="15%">Descripción</th>
						<th width="8%">Total de Entradas<br><span id="periodo_entradas"></span></th><!--Se implementa span para meter año de entradas-->
						<th width="8%">Ventas<br><span id="periodo_ventas"></span></th><!--Se implementa span para meter año de entradas-->
						<th width="8%">Inv final</th>
						<th width="8%">Pedido</th>
						<th width="8%">Precio</th>
						<th width="8%">Cajas</th>
						<th width="8%">Total</th>
						<th width="7%">Config</th>
						<th width="7%">Quitar</th>
						<!--<th width="1.5%"></th>-->
					</tr>
			</table>
				<!--<tr>
					<td colspan="12" width="100%" style="margin:0;padding:0;">-->
						<div id="t2" style="margin:0;padding:0;width:101.3%;" onclick="ocultar_busqueda();">
						<!--creamos la tabla por si se requiere agregar sin generar pedido automático Oscar 16.08.2018-->
							<table border="0" width="99.99%" style="margin:0;padding:0;" id="lista_de_prods">	
							</table>
							<input type="hidden" id="filas_totales" value="0">
						<!--Fin de cambo OScar 16.08.2018-->
						</div>
					<!--</td>
				</tr>-->
		<!--aqui guardamos id de proveedores-->
			<input type="hidden" id="ids_provs" value="<?php if($id_oc==0){echo $ids_pr;}else{echo $datos_oc[0];}?>">
			</center>			
		</div>
	<!--Finaliza div de contenido-->
	<!--Comienza div de pie de pagina-->
		<div id="footer">
			<table width="100%" height="100%" border="0" style="top:0px;position:relative;" border="0">
				<tr>
				<!--Botón de regresar a panel-->
					<td width="10%" align="center">
						<button type="button" class="bot" value="Regresar al panel"  onclick="location.href='../../../index.php'" style="background:transparent;">
							<img src="../../../img/img_casadelasluces/logocasadelasluces-easy.png" height="35px;"><br>Panel
						</button>
					</td>
				<!--Botón de ir al listado-->
					<td width="10%" align="center">
						<button type="button" class="bot" value="Regresar al panel"  onclick="location.href='../../general/listados.php?tabla=ZWNfb3JkZW5lc19jb21wcmE=&no_tabla=MQ=='"
						style="background:transparent;">
							<img src="../../../img/iconolista.png" height="35px;"><br>Listado
						</button>
					</td>
				<!--Botón de enviar pedidos por correo-->
					<td width="20%" align="center">
						<button type="button" onclick="" id="env_correos" class="bot" <?php echo $ocultaBotones;?>>
							<img src="../../../img/especiales/email.png" width="30px"><br>Enviar pedido<br>a proveedor(es)
						</button>
					</td>
				<!--Botón de descargar en excel-->
					<td width="10%" align="center">
						<button id="desc_prev" class="bot" onclick="descarga_previo();">
							<img src="../../../img/especiales/exportaCSV1.png" height="30px"><br>Descargar
						</button>						
					</td>
				<!--Botón de guardado-->
					<td width="10%" align="center">
					<?php
						if($status_oc<4){
					?>
						<button id="guardado_pedido" class="bot" onclick="<?php if($id_oc==0){echo 'guarda_pedido();';}else{echo 'guarda_cambios_oc();';}?>">
							<img src="../../../img/especiales/save.png" height="30px"><br>Guardar
						</button>
					<?php
						}//cerramos el if del status OC
					?>
					</td>
					
					<td align="center">
					<label style="color : white; font-size : 50%;">Tipo de Consulta :</label>	
						<select
							id="development_test_1" 
							onchange="show_development_emergent_message( this );"
							style="padding : 8px; border : 1px solid silver; border-radius : 5px; width : 200px;"
						>
							<option value="original">Consulta Original</option>
							<option value="omitir_condicion">Omitir Orden de Precio</option>
						</select>
					</td>
				
				<!--Botón de exportar csv de pedidos-->
					<td width="20%" align="center">
						<button type="button" onclick="descargarCSVpedidos(<?php echo '\''.$id_oc.'~\'';?>);" id="desc_ped_csv" class="bot" <?php echo $ocultaBotones;?>>
							<img src="../../../img/especiales/exportaCSV1.png" width="40px"><br>Descargar CSV de pedido(s)
						</button>						
					</td>
					</td>
					<td width="20%" align="center">
						<button class="bot" onclick="recarga_pantalla();"><!--location.reload()-->
							<img src="../../../img/especiales/reset.png" height="30px"><br>Nuevo
						</button>
					</td>
				</tr>
			</table>
		</div>
	</div>

<!--implementación Oscar 29.06.2018 para exportación en Excel-->
	<form id="TheForm" method="post" action="ajax/afterSave.php" target="TheWindow">
			<input type="hidden" name="fl" value="3" />
			<input type="hidden" id="datos" name="datos" value=""/>
	</form>
<!--Fin de cambio 29.05.2018-->

</body>
</html>
<!--script type="text/javascript">
	
		//$( '#mostrar_btn' ).click();
	//alert( '' );
</script-->