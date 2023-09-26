<?php
//conexiones a la base de datos
	include( '../../../../config.ini.php' );
	include( '../../../../conectMin.php' );//sesión
	include( '../../../../conexionMysqli.php' );
	include( 'ajax/db.php' );
?>
<!DOCTYPE html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" type="text/css" href="../../../../css/bootstrap/css/bootstrap.css">
		<link href="../../../../css/icons/css/fontello.css" rel="stylesheet" type="text/css"  media="all" />
		<link rel="stylesheet" type="text/css" href="css/styles.css">
		<script type="text/javascript" src="../../../../js/jquery-1.10.2.min.js"></script>
		<script type="text/javascript" src="../../../../js/jquery-ui.js"></script>
		<script type="text/javascript" src="js/functions.js"></script>
		<title>Surtimiento de Transferencias</title>
	</head>
	<body>
	<?php
		echo '<input type="hidden" id="user_id" value="' . $user_id . '" >';
	?>

	<audio id="audio" controls>
		<source type="audio/wav" src="../../../../files/scanner.mp3">
	</audio>

		<div class="emergent">
			<div class="btn_close_container">
				<button
					class="btn btn-danger"
					onclick="close_emergent();"
				>
					X
				</button>
			</div>
			<div class="emergent_content" tabindex="1"></div>
		</div>
		<div class="emergent_2">
			<div class="btn_close_container">
				<button
					class="btn btn-danger"
					onclick="close_emergent();"
				>
					X
				</button>
			</div>
			<div class="emergent_content_2" tabindex="1"></div>
		</div>

		<div class="global_container">
			<div class="header">
				<div class="row">
					<div class="mnu_item invoices active" onclick="show_view( this, '.assignment_list' );">
						<i class="icon-menu"></i><br>
						Transferencias
					</div>
					<div class="mnu_item source hidden" onclick="show_view( this, '.supply' );">
						<i class="icon-plus-circle"></i><br>
						Surtimiento
					</div>
					<div class="mnu_item list hidden" onclick="show_view( this, '.list_supplied' );">
						<i class="icon-th-list"></i><br>
						Surtido
					</div>
				</div>
			</div>

			<div class="content_container">
				<div class="content_item assignment_list active">
					<?php 
						include( 'views/assignment_list.php' );
					?>
				</div>

				<div class="content_item supply hidden"><!-- hidden -->
					<?php 
						include( 'views/supply.php' );
					?>
				</div>


				<div class="content_item list_supplied hidden"><!-- hidden -->
					<?php 
						include( 'views/listSupply.php' );
					?>
				</div>

			</div>

			<div class="footer">
				<div class="row">
					<div class="col-12 text-right">
						<button 
							class="btn btn-light"
							onclick="redirect('home');"
						>
							<i class="icon-home-1"></i>
						</button>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>