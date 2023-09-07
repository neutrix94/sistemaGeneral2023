<?php
	include( 'ajax/builder.php' );
	$builder = new Builder( $link );
?>

	<div style="padding:10px; max-height : 300px !important; overflow-y : auto !important;" id="finish_invoices_container">
		<?php
			echo $builder->buildInvoiceListFinish( null, $link );
		?>
	</div>
	<div style="padding:10px;">
	<?php
		include( 'location_form.php' );
	?>
	</div>