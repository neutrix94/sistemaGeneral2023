<?php
	//extraemos datos
	extract($_POST);
//extraemos informacion de variables
	$atr=explode('|',$combo);
	$opciones=explode('|',$info);
?>

	<select id="<?php echo $atr[2].','.$atr[1];?>" class="opciones"
			onkeyup="valida(event,6,0);" <?php if($atr[0]==3){/*echo 'sin accion'*/}else{ echo 'onchange="activaDependiente(0,2);"';} ?> style="width:100px;">
		<?php
			for($i=1;$i<=$opciones[0];$i++){
				$valores=explode('~',$opciones[$i]);
		?>
				<option value="<?php echo $valores[0];?>"><?php echo $valores[1];?></option>
		<?php
			}
		?>
	</select>