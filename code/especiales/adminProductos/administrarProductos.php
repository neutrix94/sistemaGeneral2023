<link href="css/estiloProd.css" rel="stylesheet" type="text/css"  media="all" />
<script language="JavaScript" src="../../../js/jquery-1.10.2.min.js"></script>
<script language="JavaScript" src="js/funcionesAdminProd.js"></script>
<?php
	if(include('../../../conect.php')){
		//echo 'el archivo si fue encontrada';
	}else{
		echo 'no se encuentra el archivo de conexión';
		return false;
	}
	$sql="SELECT p.id_productos,
		/*1*/	p.nombre,
		/*2*/	p.clave,
		/*3*/	p.orden_lista,
		/*4*/	p.ubicacion_almacen,
		/*5*/	c.nombre,
		/*6*/	s.nombre,
		/*7*/	p.precio_venta,
		/*8*/	p.precio_compra,
		/*9*/	p.es_maquilado,
		/*10*/	p.nombre_etiqueta,
		/*11*/	p.codigo_barras_1,
		/*12*/	p.codigo_barras_2,
		/*13*/	p.codigo_barras_3,
		/*14*/	p.codigo_barras_4,
		/*15*/	st.nombre,
		/*16*/	p.habilitado,
		/*17*/	p.omitir_alertas,
		/*18*/	p.muestra_paleta
			FROM ec_productos p
			LEFT JOIN ec_categoria c ON p.id_categoria=c.id_categoria
			LEFT JOIN ec_subcategoria s ON p.id_subcategoria=s.id_subcategoria
			LEFT JOIN ec_subtipos st ON s.id_subcategoria=st.id_tipo
			WHERE id_productos>0 AND id_productos<1900  AND habilitado=1 ORDER BY orden_lista ASC";
	$eje=mysql_query($sql);
	if(!$eje){
		die(mysql_error().'<br>'.$sql);
	}
	$sql1="SELECT nombre from ec_productos WHERE id_productos>0 AND id_productos<1900 AND habilitado=1 ORDER BY orden_lista ASC";
	$eje1=mysql_query($sql1);
	if(!$eje1){
		die(mysql_error().'<br>'.$sql1);
	}
?>
<div id="general" style="width:100%;height:100%;">
	<div id="arriba">
		<table width="100%">
			<tr>
				<td width="50%">
					<p style="padding:10px;"><?php include('../buscador/buscador.php');?></p>		
				</td>
				<td width="50%">
					<a href="javascript:registraNuevo();"><p style="color:white;" id="reg">Guardar</p></a>
				</td>
			</tr>
		</table>
	</div>
	<br>
<center>
	<div id="encabezado">
	<!--Encabezado de nombre producto-->
		<div id="enc1">
			<table border="0">
				<tr>
					<td><div class="enc" style="vertical-align:middle;">Nombre del Producto</div></td>
				</tr>
			</table>
		</div>
		<div id="enc2">
			<table border="0">
				<tr>
					<td><div class="enc" style="width:100px;">Código</div></td>
					<td><div class="enc" style="width:100px;">Orden Lista</div></td>
					<td><div class="enc" style="width:100px;">Ubic almacen</div></td>
					<td><div class="enc" style="width:100px;">Categoria</div></td>
					<td><div class="enc" style="width:100px;">Subcategoria</div></td>
					<td><div class="enc" style="width:100px;">id_subtipo</div></td>
					<td><div class="enc" style="width:80px;">Prec Venta</div></td>
					<td><div class="enc" style="width:80px;">Prec Compra</div></td>
					<td><div class="enc" style="width:80px;">Maquilado</div></td>
					<td><div class="enc" style="width:150px;">Nom Etiqueta</div></td>
					<td><div class="enc" style="width:150px;">C barras 1</div></td>
					<td><div class="enc" style="width:150px;">C barras 2</div></td>
					<td><div class="enc" style="width:150px;">C barras 3</div></td>
					<td><div class="enc" style="width:150px;">C barras 4</div></td>
					<td><div class="enc" style="width:80px;">Habilitado</div></td>
					<td><div class="enc" style="width:80px;">Alertas</div></td>
					<td><div class="enc" style="width:80px;">Paleta</div></td>
				</tr>
			</table>
		</div>
	</div>

	<div id="listado" style="border:1px solid red;">
		<div id="fijo" onscroll="sincScroll('fijo');">
			<table id="listado" border="0" width="100%" height="100%;">
			<?php
				include('filaDeNombre.php');
				$c=0;//declaramos contador en ceros
				//$num=mysql_num_rows($eje1);
				//extract($row);//extraemos resultados para usarlos como variables
			//comenzamos a llenar 
				while($row1=mysql_fetch_row($eje1)){
			//intercalamos color
					$c++;
					if($c%2==0){
						$color='#FFFF99';
					}else{
						$color='#CCCCCC';
					}
			?>
				<tr id="<?php echo 'fil'.$c;?>" style="background:<?php echo $color;?>;" class="fila" onclick="resalta(<?php echo $c;?>);">
				<!--nombre del producto-->
					<td>
						<input type="text" id="<?php echo '1,'.$c;?>" class="modificable" value="<?php echo $row1[0];?>"
						onkeyup="valida(event,1,<?php echo $c;?>)" onclick="resaltacelda(<?php echo '1,'.$c;?>);" style="width:100%">
					</td>
				</tr>
			<?php
				}//cierra while
			?>	
			</table>
		<!--Guardamos total de resultados-->
			<input type="hidden" value="<?php echo $c;?>" id="numTotal">

		</div>
		<div id="dinamico" onscroll="sincScroll('dinamico');">
			<table border="0">
		<?php
			include('filaFormulario.php');
			$c=0;//declaramos contador en ceros
			while($row=mysql_fetch_row($eje)){
			//intercalamos color
				$c++;
				if($c%2==0){
					$color='#FFFF99';
				}else{
					$color='#CCCCCC';
				}
			//maquila
				if($row[9]==1){
					$maquila='checked';
				}else{
					$maquila='';
				}
			//habilitado
				if($row[16]==1){
					$habilit='checked';
				}else{
					$habilit='';
				}
			//alertas
				if($row[17]==1){
					$aler='checked';
				}else{
					$aler='';
				}
			//paleta
				if($row[18]==1){
					$paleta='checked';		
				}else{
					$aler='';
				}
			?>
		<tr id="<?php echo 'fila'.$c;?>" style="background:<?php echo $color;?>" onclick="resalta(<?php echo $c;?>);" class="fila">
			<td>
		<!--variable de id de producto-->
				<input type="hidden" id="<?php echo '0,'.$c;?>" value="<?php echo $row[0];?>">
		<!--codigo-->
				<input type="text" id="<?php echo '2,'.$c;?>" class="modificable" value="<?php echo $row[2];?>"
				onkeyup="valida(event,2,<?php echo $c;?>)" onclick="resaltacelda(<?php echo '2,'.$c;?>);" style="width:100px;">
			</td>
		<!--orden de lista-->
			<td>
				<input type="text" id="<?php echo '3,'.$c;?>" class="modificable" value="<?php echo $row[3];?>"
				onkeyup="valida(event,3,<?php echo $c;?>)" onclick="resaltacelda(<?php echo '3,'.$c;?>);" style="width:100px;">
			</td>
			<td>
		<!--ubicacion de almacen-->
				<input type="text" id="<?php echo '4,'.$c;?>" value="<?php echo $row[4];?>" class="modificable"
				onkeyup="valida(event,4,<?php echo $c;?>)" onclick="resaltacelda(<?php echo '4,'.$c;?>);" style="width:100px;">
			</td>
		<!--categoria-->
			<td>
				<select id="<?php echo '5,'.$c;?>" class="opciones" onclick="<?php echo 'carga(5,'.$c.');' ;?>"
				onkeyup="valida(event,5,<?php echo $c;?>)" onchange="<?php echo 'combos(5,'.$c.');';?>" style="width:100px;">
					<option><?php echo $row[5];?></option>
				</select>
			</td>
		<!--subcategoria-->
			<td>
				<select id="<?php echo '6,'.$c;?>" class="opciones" onclick="<?php echo 'carga(1,6,'.$c.');' ;?>"
				onkeyup="valida(event,6,<?php echo $c;?>)" onchange="<?php echo 'combos(6,'.$c.');';?>" style="width:100px;">
					<option><?php echo $row[6];?></option>
				</select>
			<td>
		<!--id_subtipo-->
			<td>
				<div id="">
					<select id="<?php echo '15,'.$c;?>" class="opciones" onclick="<?php echo 'carga(1,15,'.$c.');' ;?>"
					onkeyup="valida(event,15,<?php echo $c;?>)" onchange="<?php echo 'combos(15,'.$c.');';?>" style="width:100px;">
						<option><?php echo $row[15];?></option>
					</select>
				</div>
		<!--precio venta-->
			<td>
				<input type="text" id="<?php echo '7,'.$c;?>" value="<?php echo $row[7];?>" class="modificable"
				onkeyup="valida(event,7,<?php echo $c;?>)" onclick="resaltacelda(<?php echo '7,'.$c;?>);" style="width:80px;">
			</td>
		<!--precio compra-->
			<td>
				<input type="text" id="<?php echo '8,'.$c;?>" value="<?php echo $row[8];?>" class="modificable"
				onkeyup="valida(event,8,<?php echo $c;?>)" onclick="resaltacelda(<?php echo '8,'.$c;?>);" style="width:80px;">
			</td>
		<!--maquilado-->
			<td>
				<div style="width:80px;" class="enc">
					<input type="checkbox" id="<?php echo '9,'.$c;?>" class="ch" <?php echo $maquila;?> 
					onkeyup="valida(event,9,<?php echo $c;?>)" onclick="<?php echo 'seleccion(9,'.$c.')';?>">
				</div>
			</td>
		<!--nombre etiqueta-->
			<td>
				<input type="text" id="<?php echo '10,'.$c;?>" value="<?php echo $row[10];?>" class="modificable"
				onkeyup="valida(event,10,<?php echo $c;?>)" onclick="resaltacelda(<?php echo '10,'.$c;?>);" style="width:150px;">
			</td>
		<!--codigo de barras 1-->
			<td>
				<input type="text" id="<?php echo '11,'.$c;?>" value="<?php echo $row[11];?>" class="modificable"
				onkeyup="valida(event,11,<?php echo $c;?>)" onclick="resaltacelda(<?php echo '11,'.$c;?>);" style="width:150px;">
			</td>
		<!--codigo de barras 2-->
			<td>
				<input type="text" id="<?php echo '12,'.$c;?>" value="<?php echo $row[12];?>" class="modificable"
				onkeyup="valida(event,12,<?php echo $c;?>)" onclick="resaltacelda(<?php echo '12,'.$c;?>);" style="width:150px;">
			</td>
		<!--codigo de barras 3-->
			<td>
				<input type="text" id="<?php echo '13,'.$c;?>" value="<?php echo $row[13];?>" class="modificable"
				onkeyup="valida(event,13,<?php echo $c;?>)" onclick="resaltacelda(<?php echo '13,'.$c;?>);" style="width:150px;">
			</td>
		<!--codigo de barras 4-->
			<td>
				<input type="text" id="<?php echo '14,'.$c;?>" value="<?php echo $row[14];?>" class="modificable"
				onkeyup="valida(event,14,<?php echo $c;?>)" onclick="resaltacelda(<?php echo '14,'.$c;?>);" style="width:150px;">
			</td>
		<!--habilitado-->
			<td>
				<div style="width:80px;" class="enc">
					<input type="checkbox" id="<?php echo '16,'.$c;?>" class="ch" <?php echo $habilit;?>
					onkeyup="valida(event,16,<?php echo $c;?>)" onclick="<?php echo 'seleccion(16,'.$c.')';?>">
				</div>
			</td>
		<!--alertas-->
			<td>

				<div style="width:80px;" class="enc">
					<input type="checkbox" id="<?php echo '17,'.$c;?>" class="ch" <?php echo $aler;?>
					onkeyup="valida(event,16,<?php echo $c;?>)" onclick="<?php echo 'seleccion(17,'.$c.')';?>">
				</div>
			</td>
		<!--paletas-->
			<td>
				<div style="width:80px;" class="enc">
					<input type="checkbox" id="<?php echo '18,'.$c;?>" class="ch" <?php echo $paleta;?> 
					onkeyup="valida(event,18,<?php echo $c;?>)" onclick="<?php echo 'seleccion(18,'.$c.')';?>">
				</div>
			</td>
		</tr>
<?php
	}//cierra while
?>		</tbody>
	</table>
		</div>
	</div>
	<div class="footer">
		<table>
			<tr>
				<td>
					<a href="../../../"><div class="divBoton" >PANEL PRINCIPAL</div></a>
				</td>
			</tr>
		</table>
	</div>
</center>
</div><!--Cierra el div general-->