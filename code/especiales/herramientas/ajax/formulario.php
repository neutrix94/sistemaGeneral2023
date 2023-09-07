<head>
	<link rel="stylesheet" href="css/estilos.css">
</head>

<?php
/*implementación Oscar 2021 para ejecutar consultas con MYSQLI*/
	include('../../../../config.inc.php');
	$link = mysqli_connect($dbHost, $dbUser, $dbPassword, $dbName);
	$link->set_charset("utf8");

	$id_h=$_POST['id'];
	$result="";
	if($id_h!=0){
		$sql="SELECT 
				$id_h,/*0*/
				titulo,/*1*/
				consulta,/*2*/
				descripcion,/*3*/
				campo_filtro_sucursal,/*4*/
				campo_filtro_fecha1,/*5*/
				campo_filtro_fecha2,/*6*/
				campo_filtro_familia,/*7*/
				campo_filtro_tipo,/*8*/
				campo_filtro_subtipo,/*9*/
				campo_filtro_color,/*10*/
				campo_filtro_almacen,/*11*/
				campo_filtro_es_externo,/*12*/
				tipo_herramienta/*13*/
			FROM sys_herramientas 
			WHERE id_herramienta=$id_h";
		$eje= $link->query($sql)or die("Error al consultar los datos de la herramienta!!!\n" . $link->error);
		//$r=mysql_fetch_row($eje);
		$r = $eje->fetch_row();
	}
	if($r[0]=='' || $r[0]==null){
		$r[0]="(Automático)";
	}

	$placeholder='placeholder="$CARACTER_REEMPLAZAR|campo_comparacion|tipo_elemento|consulta_combo/Formato fecha|onchange|id_elemento_html|titulo"';
/*implementacion Oscar 2021 para poner el tipo de consulta*/
	$combo_tipos = "<select id=\"query_type\" class=\"form-control\" style=\"width:70%; display:inline;\">"
			. "<option value=\"Consulta\" " . ($r[13]== 'Consulta' ? 'selected' :null) . ">Consulta</option>"
			. "<option value=\"Herramienta\"" . ($r[13]== 'Herramienta' ? 'selected' :null) . ">Herramienta</option>"
		. "</select>";

	echo '<button type="button" title="Cerrar" onclick="document.getElementById(\'emergente\').style.display=\'none\';" class="btn_cerrar">X</button>';
	echo '<form>';
		echo '<table class="tabla_formulario">';
				echo '<tr>';
					echo '<td class="titulo" width="20%">ID: <input type="text" id="id_herramienta" value="'.$r[0].'" class="entrada_form" style="width:50%;" disabled></td>';
					echo '<td width="80%" align="left" style="color:white;"> Tipo : ' . $combo_tipos . '</td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td>Título:</td>';
					echo '<td><textarea id="titulo" class="entrada_form">'.$r[1].'</textarea></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td class="titulo">Consulta:</td>';
					echo '<td><textarea id="consulta" class="entrada_form">'.$r[2].'</textarea></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td class="titulo">Descripción:</td>';
					echo '<td><textarea id="descripcion" class="entrada_form">'.$r[3].'</textarea></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td class="titulo">Filtro Sucursal:</td>';
					echo '<td><textarea id="campo_filtro_sucursal" class="entrada_form" '.$placeholder.'>'.$r[4].'</textarea></td>';
				echo '</tr>';

				echo '<tr>';
					echo '<td class="titulo">Filtro Fecha 1:</td>';
					echo '<td><textarea id="campo_filtro_fecha_1" class="entrada_form" '.$placeholder.'>'.$r[5].'</textarea></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td class="titulo">Filtro Fecha 2:</td>';
					echo '<td><textarea id="campo_filtro_fecha_2" class="entrada_form" '.$placeholder.'>'.$r[6].'</textarea></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td class="titulo">Filtro Familia:</td>';
					echo '<td><textarea id="campo_filtro_familia" class="entrada_form" '.$placeholder.'>'.$r[7].'</textarea></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td class="titulo">Filtro Tipo:</td>';
					echo '<td><textarea id="campo_filtro_tipo" class="entrada_form" '.$placeholder.'>'.$r[8].'</textarea></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td class="titulo">Filtro Subtipo:</td>';
					echo '<td><textarea id="campo_filtro_subtipo" class="entrada_form" '.$placeholder.'>'.$r[9].'</textarea></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td class="titulo">Filtro Color:</td>';
					echo '<td><textarea id="campo_filtro_color" class="entrada_form" '.$placeholder.'>'.$r[10].'</textarea></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td class="titulo">Filtro Almacen</td>';
					echo '<td><textarea id="campo_filtro_almacen" class="entrada_form" '.$placeholder.'>'.$r[11].'</textarea></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td class="titulo">Filtro es Externo</td>';
					echo '<td><textarea id="campo_filtro_es_externo" class="entrada_form" '.$placeholder.'>'.$r[12].'</textarea></td>';
				echo '</tr>';
			/*botones*/
				echo '<tr>';
					echo '<td colspan="2" align="center">';
						echo '<table>';
							echo '<tr>';
									echo '<td><button class="btnsemergente" type="button" onclick="guarda();">Guardar</button></td>';
									echo '<td><button class="btnsemergente" type="button" onclick="guarda(0);">Guardar Nuevo</button></td>';
									echo '<td><button class="btnsemergente" type="button">Cancelar</button></td>';
							echo '</tr>';
						echo '</table>';
					echo '</td>';
				echo '</tr>';
		echo '</table>';
	echo '</form>';
?>