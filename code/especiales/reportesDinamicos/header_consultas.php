<?php
	include("../../../conectMin.php");
	include("../../../conexionMysqli.php");
	
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
		FROM sys_reportes_dinamicos 
		WHERE id_reporte_dinamico = {$id_h}";
		$eje = $link->query( $sql )or die("Error al consultar los filtros de la herramienta : {$link->error}");

		$r= $eje->fetch_row();
		echo $r[0]."___";
/*0-$CARACTER_REEMPLAZAR|1-campo_comparacion|2-tipo_elemento|3-consulta_combo/Formato fecha|4-onchange|5-id_elemento_html*/
		//echo '<div class="row" style="top:0;">';
		$filtros_listados='';
		for($i=1;$i<sizeof($r);$i++){
			if($r[$i]!='' && $r[$i]!=null){
				echo '<div class="col-2" style="margin : 10px !important;">';
				$arr=explode("|", $r[$i]);
				echo "<h6 class=\"titulo_filtro\" style=\"padding : 0; margin-top : 0; font-size : 80%;\">{$arr[6]}</h6>";
			//si es combo
				if( $arr[2] == 'combo' ){
					echo '<select class="seleccion" style="width:100%;" id="'.$arr[5].'" datosDB="'.$arr[3].'" onchange="'.$arr[4].'" caracter_cambio="'.$arr[0].'" campo_filtrar="'.$arr[1].'">';
						$eje_1=$link->query($arr[3])or die( "Error al consultar los datos del combo!!!<br>{$link->error}<br>{$arr[3]}" );
						echo '<option class="opciones1" value="0">--Ver Todo--</option>';
						while($r1=$eje_1->fetch_row()){
							echo '<option class="opciones2" value="'.$r1[0].'">'.$r1[1].'</option>';
						}
					echo '</select>';					
				}
			//si es fecha
				else if( $arr[2] == 'fecha' ){
					echo '<input type="text" style="width:100%;" id="'.$arr[5].'" value="" datosDB="'.$arr[3].'" onclick="'.$arr[4].'" caracter_cambio="'.$arr[0].'" campo_filtrar="'.$arr[1].'">';
				}
			//si es fecha y hora
				else if ( $arr[2] == 'datetime' ){
					echo '<input type="datetime-local" class="fech" id="'.$arr[5].'" datosDB="'.$arr[3].'" onclick="'.$arr[4].'" caracter_cambio="'.$arr[0].'" campo_filtrar="'.$arr[1].'">';
				}
				echo '</div>';
				$filtros_listados.=$arr[5]."|";

		}
	}
		echo '<div class="col-2" style="vertical-align : middle;">';
			echo '<button class="btnGenerar" type="button" onclick="genera_consulta('.$id_h.');">Generar</button>';
		echo '</div>';
		echo '<div class="col-2" style="vertical-align : middle;"><button class="btnGenerar" onclick="exporta_grid('.$id_h.');">Exportar</button></div>';
	//echo '</div>';//<tr>
		echo '<input type="hidden" id="lista_filtros" value="'.$filtros_listados.'">';
?>
