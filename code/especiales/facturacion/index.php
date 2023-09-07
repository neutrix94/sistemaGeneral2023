<?php
//conexiones a la base de datos
	include( '../../../conectMin.php' );//sesión
	include( '../../../conexionMysqli.php' );
	include( 'ajax/db.php' );
	//die( $user_id );
?>

<!DOCTYPE html>
<head>
<!-- Redireccionamiento https -->
    <script type="text/javascript">
    /*if (location.protocol !== 'https:') {
        location.replace(`https:${location.href.substring(location.protocol.length)}`);
    }*/
    </script>
<!-- -->
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="../../../css/bootstrap/css/bootstrap.css">
	<link href="../../../css/icons/css/fontello.css" rel="stylesheet" type="text/css"  media="all" />
	<link rel="stylesheet" type="text/css" href="css/styles.css">
	<script type="text/javascript" src="../../../js/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="js/functions.js"></script>
	<!--script type="text/javascript" src="js/locations.js"></script-->
	<title>Facturacion General</title>
</head>
<body>
	<div class="emergent">
		<div class="row">
			<div class="col-12 emergent_content" tabindex="1"></div>
		</div>
	</div>

	<div class="emergent_2">
		<div class="row">
			<div class="col-12 emergent_content_2" tabindex="1"></div>
		</div>
	</div>
	<div class="global_container">
		<div class="header">
			<!--div class="row"-->
				<div class="mnu_item list active" onclick="show_view( this, '.list');">
					<i class="icon-th-list-outline"></i><br>
					Listado
				</div>
				<div class="mnu_item bill" onclick="show_view( this, '.bill');">
					<i class="icon-plus-circle"></i><br><!--icon-pin-->
					Factura
				</div>
				<div class="mnu_item payments" onclick="show_view( this, '.payments');">
					<i class="icon-dollar"></i><br>
					Pagos
				</div>
				<div class="mnu_item save_bill" onclick="show_view( this, '.save_bill');">
					<i class="icon-floppy-1"></i><br>
					Guardar
				</div>
			<!--/div-->
		</div>

		<div class="content_container">
			<div class="content_item list ">
				<?php 
					include( 'views/list.php' );
				?>
			</div>

			<div class="content_item bill hidden"><!--hidden-->
				<?php 
					include( 'views/bill.php' );
				?>
			</div>

			<div class="content_item payments hidden">
				<?php 
					include( 'views/payments.php' );
				?>
			</div>

			<div class="content_item save_bill hidden">
				<?php 
					include( 'views/save_bill.php' );
				?>
			</div>

		</div>

		<div class="footer">
			<div class="row">
				<div class="col-6 txt_alg_left">
					<button 
						class="btn btn-light"
						onclick="redirect('home');"
					>
						<i class="icon-home-1"></i>
					</button>
				</div>

				<div class="col-6 txt_alg_right">
					<button class="btn btn-light">
						<i class="icon-off"></i>
					</button>
				</div>
			</div>
		</div>
	</div>
	<input type="hidden" id="dont_request_reception_measures">
</body>
</html>
<!--script type="text/javascript">
	if( ! getPermissions() ){

	}else{
		getGlobalConfig();
		if( !localStorage.getItem( 'block_id' )){
			insertReceptionBlock();
		}else{
			global_block_id = localStorage.getItem( 'block_id' );
		}
	}
//	alert( global_block_id );
</script-->