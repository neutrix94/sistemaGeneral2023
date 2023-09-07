<?php 
	$subsql="SELECT id_categoria,nombre from ec_categoria";
	$ejeSub=mysql_query($subsql);
	if(!$ejeSub){
		die("Error!!!\n".mysql_error()."\n".$subSql);
	}
?>
<tr id="fila0" style="background:#FFFF99;" onclick="resalta(0);" class="fila">
	<td>	
<!--codigo-->
		<input type="text" id="2,0" class="modificable" value=""
		onkeyup="valida(event,2,0)" onclick="resaltacelda(2,0);" style="width:100px;">
	</td>
<!--orden de lista-->
	<td>
		<input type="text" id="3,0" class="modificable" value=""
		onkeyup="valida(event,3,0)" onclick="resaltacelda(3,0);" style="width:100px;">
	</td>
<!--ubicacion de almacen-->

	<td>
		<input type="text" id="4,0" value="" class="modificable"
		onkeyup="valida(event,4,0)" onclick="resaltacelda(4,0);" style="width:100px;">
	</td>
<!--categoria-->
	<td>
		<select id="5,0" class="opciones" onkeyup="valida(event,5,0);" onchange="activaDependiente(0,1);" style="width:100px;">
		<?php
			while ($res=mysql_fetch_row($ejeSub)){
		?>
				<option value="<?php echo $res[0];?>"><?php echo $res[1];?></option>
		<?php
			}//ceramos while
		?>
		</select>
	</td>
<!--subcategoria-->
	<td>
		<div id="combo,2,0" class="divCombo">
			<select id="6,0" class="opciones" onclick="carga(1,6,0);"
			onkeyup="valida(event,6,0);" onchange="activaDependiente(0,2);" style="width:100px;">
				<option value='1'>General</option>
			</select>
		</div>
	<td>
<!--id_subtipo-->
	<td>
		<div id="combo,3,0" class="divCombo">
			<select id="15,0" class="opciones" onclick="carga(1,15,0);"	onchange="" style="width:100px;">
				<option></option>
			</select>
		</div>
<!--precio venta-->
	<td>
		<input type="text" id="7,0" value="" class="modificable" onclick="resaltacelda(7,0);" style="width:80px;">
	</td>
<!--precio compra-->
	<td>
		<input type="text" id="8,0" value="" class="modificable" onclick="resaltacelda(8,0);" style="width:80px;">
	</td>
<!--maquilado-->
	<td>
		<div style="width:80px;" class="enc">
			<input type="checkbox" id="9,0" class="ch" onclick="seleccion(9,0);">
		</div>
	</td>
<!--nombre etiqueta-->
	<td>
		<input type="text" id="10,0" value="" class="modificable" onclick="resaltacelda(10,0);" style="width:150px;">
	</td>
<!--codigo de barras 1-->
	<td>
		<input type="text" id="11,0" value="" class="modificable" onclick="resaltacelda(11,0);" style="width:150px;">
	</td>
<!--codigo de barras 2-->
	<td>
		<input type="text" id="12,0" value="" class="modificable"
		onkeyup="valida(event,12,0)" onclick="resaltacelda(12,0);" style="width:150px;">
	</td>
<!--codigo de barras 3-->
	<td>
		<input type="text" id="13,0" value="" class="modificable"
		onkeyup="valida(event,13,0)" onclick="resaltacelda(13,0);" style="width:150px;">
	</td>
<!--codigo de barras 4-->
	<td>
		<input type="text" id="14,0" value="" class="modificable"
		onkeyup="valida(event,14,0)" onclick="resaltacelda(14,0);" style="width:150px;">
	</td>
<!--habilitado-->
	<td>
		<div style="width:80px;" class="enc">
			<input type="checkbox" id="16,0" class="ch" checked 
			onkeyup="valida(event,16,0);" onclick="seleccion(16,0);">
		</div>
	</td>
<!--alertas-->
	<td>
		<div style="width:80px;" class="enc">
			<input type="checkbox" id="17,0" class="ch"
			onkeyup="valida(event,16,0)" onclick="seleccion(17,0);">
		</div>
	</td>
<!--paletas-->
	<td>
		<div style="width:80px;" class="enc">
			<input type="checkbox" id="18,0" class="ch"
			onkeyup="valida(event,18,0)" onclick="seleccion(18,0);">
		</div>
	</td>
</tr>