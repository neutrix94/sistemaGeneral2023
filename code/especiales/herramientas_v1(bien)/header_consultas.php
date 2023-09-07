<?php
	include("../../../conectMin.php");
	
	$id_h=$_POST['id_herramienta'];

	$sql="SELECT 
			consulta,
			campo_filtro_sucursal,
			campo_filtro_fecha1,
			campo_filtro_fecha2,
			campo_filtro_familia,
			campo_filtro_tipo,
			campo_filtro_subtipo,
			campo_filtro_color,
			campo_filtro_almacen,
			campo_filtro_es_externo
		FROM sys_herramientas WHERE id_herramienta=$id_h";
		$eje=mysql_query($sql)or die("Error al consultar los filtros de la herramienta!!!");

		$r=mysql_fetch_row($eje);
		echo $r[0]."___";
/*0-$CARACTER_REEMPLAZAR|1-campo_comparacion|2-tipo_elemento|3-consulta_combo/Formato fecha|4-onchange|5-id_elemento_html*/
		echo '<table><tr>';
		$filtros_listados='';
		for($i=1;$i<sizeof($r);$i++){
			if($r[$i]!='' && $r[$i]!=null){
				echo '<td>';
				$arr=explode("|", $r[$i]);
			//si es combo
				if($arr[2]=='combo'){
					echo '<select class="seleccion" id="'.$arr[5].'" datosDB="'.$arr[3].'" onchange="'.$arr[4].'" caracter_cambio="'.$arr[0].'" campo_filtrar="'.$arr[1].'">';
						$eje_1=mysql_query($arr[3])or die("Error al consultar los datos del combo!!!<br>".mysql_error()."<br>".$arr[3]);
							echo '<option class="opciones1" value="0">--Ver Todo--</option>';
							while($r1=mysql_fetch_row($eje_1)){
								echo '<option class="opciones2" value="'.$r1[0].'">'.$r1[1].'</option>';
					}
					echo '</select>';					
				}
			//si es fecha
				else if($arr[2]=='fecha'){
					echo '<input type="text" class="fech" id="'.$arr[5].'" value="" datosDB="'.$arr[3].'" onclick="'.$arr[4].'" caracter_cambio="'.$arr[0].'" campo_filtrar="'.$arr[1].'">';
				}	
				echo '</td>';
				$filtros_listados.=$arr[5]."|";
		}
	}
		echo '<td>';
			echo '<button class="btnGenerar" type="button" onclick="genera_consulta('.$id_h.');">Generar</button>';
		echo '</td>';
		echo '<td><button class="btnGenerar" onclick="exporta_grid();">Exportar</button></td>';
		echo '</table><tr>';
		echo '<input type="hidden" id="lista_filtros" value="'.$filtros_listados.'">';
?>
