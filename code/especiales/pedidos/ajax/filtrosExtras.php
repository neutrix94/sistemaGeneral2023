<?php 
	//echo 'Hola mundo<br>';
	//die($_GET['id']);
?>
	<p align="right">
		<input type="button" value="X" onclick="cierra_eme_prod(1);"
		style="padding:10px;top:20px;right:20px;position:relative;background:red;color:white;border-radius:5px;font-size:25px;">
	</p>

	<p align="center" class="filtros_extras" style="right:50%;">De nivel<br>
		<select id="cmb_var_1" class="comb_extra"><!-- onchange="cambiaVariablesNiveles(1);"-->
			<option value="-1">
				--Seleccionar--
			</option>
			<option value="1">
				Mínimo
			</option>
			<option value="2">
				Medio
			</option>
			<option value="3">
				Máximo
			</option>
		</select>
	</p>

	<p align="center" class="filtros_extras" style="right:30%;">A nivel<br>
		<select id="cmb_var_2" class="comb_extra">
			<option value="-1">
				--Seleccionar--
			</option>
			<option value="1">
				Mínimo
			</option>
			<option value="2">
				Medio
			</option>
			<option value="3">
				Máximo
			</option>
		</select>
	</p>
	<p align="center" style="position:absolute;top:45%;right:47.5%;">
		<button onclick="cambiar_var_insumos();" style="border-radius:10px;padding:10px;">
			Aceptar
		</button>
	</p>

	<style>
		.filtros_extras{
			position:absolute;
			top:30%;
			width:20%;
			font-size:18px;
			color:white;
		}
		.comb_extra{
			padding: 10px;
			border-radius: 10px;
		}
	</style>