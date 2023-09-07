<?php
	$c=$_POST['contador'];//recibimos el contador actual
	if($c%2!=0){
		$clase="color_1";
	}else{
		$clase="color_2";
	}
?>
			<tr id="<?php echo 'fila_'.$c;?>" class="<?php echo $clase?>">
				<td width="50%" align="center">
					<input type="text" id="<?php echo 'ruta_'.$c;?>" placeholder="Ruta destino" name="ruta_destino[]" class="entrada_txt">
				</td>
				<td width="20%" align="center">
					<input type="button" id="<?php echo 'abre_'.$c;?>" value="Seleccionar archivo" onclick="selecciona_archivo(<?php echo $c;?>);">
					<input type="file" id="<?php echo 'archivo_'.$c;?>" name="archivo[]" placeholder="" class="archivo" onchange="muestra_nombre(1);">					
				</td>
				<td width="30%" align="center">
					<input type="text" id="<?php echo 'info_'.$c;?>" placeholder="" class="entrada_txt">					
				</td>
				<td width="10%" align="center">
					<img src="../../../img/especiales/menos.png" width="30px" title="Quitar">	
				</td>
			</tr>	