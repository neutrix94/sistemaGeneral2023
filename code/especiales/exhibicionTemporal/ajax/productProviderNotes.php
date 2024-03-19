<?php
	include( '../../../../conect.php' );
	include( '../../../../conexionMysqli.php' );
	include( 'exhibitionProducts.php' );
	$eP = new exhibitionProducts( $link );
	$product_provider_id = ( isset( $_GET['product_provider_id'] ) ? $_GET['product_provider_id'] : $_POST['product_provider_id'] );
	$data = $eP->getProductProviderNotes( $product_provider_id, $user_sucursal );	
	//var_dump( $data );	
?>
<div class="row">
	<h4 class="text-center"><?php echo $data['product_description'];?></h4>
	<div class="col-4">
		Total : 
	</div>
	<div class="col-8">
		<input type="number" class="form-control" value="<?php echo $data['total_quantity'];?>" readonly>
		<hr><hr>
	</div>
	<div class="col-4">
		Cantidad Muro : 
	</div>
	<div class="col-8">
		<input type="number" class="form-control" value="<?php echo $data['wall_pieces'];?>" readonly>
	</div>
	<div class="col-4">
		Nota Muro : 
	</div>
	<div class="col-8">
		<textarea class="form-control"><?php echo $data['wall_notes'];?></textarea>
		<hr><hr>
	</div>

	<div class="col-4">
		Cantidad Colgar : 
	</div>
	<div class="col-8">
		<input type="number" class="form-control" value="<?php echo $data['hang_pieces'];?>" readonly>
	</div>
	<div class="col-4">
		Nota Colgar : 
	</div>
	<div class="col-8">
		<textarea class="form-control"><?php echo $data['hang_notes'];?></textarea>
		<hr><hr>
	</div>

	<div class="col-4">
		Cantidad Adicional : 
	</div>
	<div class="col-8">
		<input type="number" class="form-control" value="<?php echo $data['aditional_pieces'];?>" readonly>
	</div>
	<div class="col-4">
		Nota Adicional : 
	</div>
	<div class="col-8">
		<textarea class="form-control"><?php echo $data['aditional_notes'];?></textarea>		
		<hr><hr>
	</div>
	<div class="col-3"></div>
	<div class="col-6">
		<button
			class="btn btn-success form-control"
			onclick="close_emergent();"
		>
			<i class="icon-ok-circle">Aceptar</i>
		</button>
	</div>
</div>