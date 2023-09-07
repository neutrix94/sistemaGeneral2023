<?php
	$comment = ( isset( $_POST['comment'] ) ? $_POST['comment'] : '' );
	$code = ( isset( $_POST['code'] ) ? $_POST['code'] : '' );
?>
<div class="">
	<h4>Descripcion</h4>
	<textarea class="form-control script" id="script_description"><?php echo $comment;?></textarea>
	<h4>Script :</h4><!--  o subir archivo( .sql, .txt )<input type="file"> -->
	<textarea class="form-control script" id="script_box"><?php echo $code;?></textarea>
	<br>
	<p class="text-center">
		<input type="checkbox" id="excecute_script"><label for="excecute_script">Ejecutar Script</label>
	</p>
	<br>
	<button 
		id="save_btn"
		class="btn btn-success form-control"
		onclick="save_script();">
		Guardar
	</button>
	<br>
	<br>
	<button 
		id="save_btn"
		class="btn btn-danger form-control"
		onclick="close_emergent();">
		Cancelar
	</button>
</div>