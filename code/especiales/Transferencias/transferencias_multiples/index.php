<?php
	include( '../../../../conectMin.php' );
	include( '../../../../conexionMysqli.php' );
	include ( 'php/builder.php' );
	$sql = "SELECT 
				p.nuevo 
			FROM sys_permisos p 
			LEFT JOIN sys_users_perfiles prf ON prf.id_perfil = p.id_perfil 
			LEFT JOIN sys_users u ON prf.id_perfil = u.tipo_perfil 
			WHERE u.id_usuario = {$user_id} AND p.id_menu = 216";
	$eje = $link->query( $sql ) or die( "Error al consultar permisos de usuario : {$link->error}");
	$permiso = $eje->fetch_assoc();
?>
<!DOCTYPE html>
<head>
	<!--script type="text/javascript" src="../../../../js/jquery-1.10.2.min.js"></script-->
	<link rel="stylesheet" href="//code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">
  	<script src="https://code.jquery.com/jquery-3.6.0.js"></script>
  	<script src="https://code.jquery.com/ui/1.13.1/jquery-ui.js"></script>
	<link rel="stylesheet" type="text/css" href="../../../../css/bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="../../../../css/icons/css/fontello.css">
	<script type="text/javascript" src="../../../../css/bootstrap/js/bootstrap.bundle.min.js"></script>
	<script type="text/javascript" src="js/utils.js"></script>
	<script type="text/javascript" src="js/reasignation.js"></script>

	<link rel="stylesheet" type="text/css" href="css/styles.css">
	<title>Listado de Transferencias Múltiple</title>
</head>

<body>
	<div class="emergent">
		<div class="emergent_content">
			<p class="emergent_description">Cargando datos</p>
			<img src="../../../../img/img_casadelasluces/load.gif">
		</div>
	</div>

	<div class="subemergent">
		<div class="subemergent_content"></div>
	</div>
	
	<div class="container_">
		<div class="row header">
			<div class="col-sm-1">
				<img src="../../../../img/img_casadelasluces/Logo.png" width="60px" onclick="menu();" style="position: relative;top : -15px;">
			</div>
			<div class="col-sm-2">
				<input type="search" class="form-control" onkeyup="active_seeker( this, null );" onchange="active_seeker( this, null );" placeholder="Buscador..." id="seeker">
			</div>
			<div class="col-sm-2">
			<?php
				echo getWarehouses ( $link );//combo de almacenes
			?>
			</div>
			<div class="col-sm-2">
				<input type="date" class="form-control" id="date_in">
			</div>
			<div class="col-sm-2">
				<input type="date" class="form-control" id="date_out">
			</div>
			<div class="col-sm-1">
				<button type="button" class="btn btn-warning" onclick="dates_filter();">Filtrar</button>
			</div>
			<div class="col-sm-1">
				<button type="button" class="btn btn-info" onclick="clean_filters();">Limpiar</button>
			</div>
			<div class="col-sm-1">
			<?php
				if ( $permiso['nuevo'] == 1 ) {
			?>
				<button type="button" class="btn btn-success" onclick="location.href='../../Transferencias_desarrollo_racion/transf.php?is_list_transfer=1';">Nueva Transferencia</button>
			<?php
				}
			?>
			</div>
		</div>
		<?php 
			echo getStatusTransfers( $link, $user_id );//listados de transferencias
		?>
	</div>

	<div id="emergenteAutorizaTransfer" style="width:100%;height:100%;background:rgba(0,0,0,.8);position:fixed;display:none;z-index:100;top:0;left:0;">
		<br><br><br><br><br><br><br><br>
		<center>
		<!-- border-radius:20px; -->
			<div class="btn_close_emergent">
				<button 
					type="button" 
					class="btn btn-danger"
					onclick="close_emergent();"
				>X</button>
			</div>
			<div id="contenidoInfo" 
					style="border:1px solid white;width:60%;top:300px;background:rgba(0,0,0,0.5);
					max-height: 450px; overflow-y : auto;">
				<p align="center" id="textInfo" style="font-size:30px;color:white;">
					<b>Guardando transferencia...</b>
				</p>
				<p align="center" id="imgInfo">
					<img src="../../../../img/img_casadelasluces/load.gif" height="15%" width="17%">		
				</p>
			</div>
		</center>
	</div>

	<div class="mensajesPop" id="mensajesPop" style="display:none">
		<div class="ventanaMens">
			<b>¿Desea autorizar esta transferencia?</b>
			<div class="dbotcerr">
				<input type="button" value="X" onclick="document.getElementById('mensajesPop').style.display='none'" class="botonCerrMensaje">
			</div>
			<br>
			<div align="right">
				<input type="button" value="SI" onclick="autorizaTrans2(2)" class="botonCerrMensaje">
				<input type="button" value="NO" onclick="autorizaTrans2(5)" class="botonCerrMensaje">
			</div>	
		</div>
	</div>
</body>
</html>
<style>
		.mensajesPop{
			position:absolute;
			top: 0px;
			left: 0px;
			width:100%;
			height:200%;
			opacity:0.85;
			background:rgba(0,225,0,.5);
			z-index: 10;
		}
		
		.ventanaMens{
			position:absolute;
			top: 520px;
			left: 35%;
			width:380px;
			height:150px;
			z-index:1000000000;
			background:#FFFFFF;
			padding: 10px;
		}
		
		.botonCerrMensaje{
			
			background:#FFFFFF;
			border-style: solid;
   			border-color: #000000;
			border-width: 1px;
			width:65px;
			height:30px;
		}
		
		.dbotcerr{
			position:relative;
			top: -25px;
			left: 320px;
		}

	</style>
<script type="text/javascript">
	$( '.emergent' ).css( 'display', 'none' );
</script>
