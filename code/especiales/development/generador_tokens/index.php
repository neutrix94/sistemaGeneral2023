<?php
	include('../../../../conectMin.php');
	include('../../../../conexionMysqli.php');
	include('ajax/tokensGenerator.php');
	$tokensGenerator = new tokensGenerator( $link, $user_id );
	$stores = $tokensGenerator->getStores();

?>
<!DOCTYPE html>
<html>
<head>
	<title>Tokens</title>
<!-- JQuery -->
	<script type="text/javascript" src="../../../../js/jquery-1.10.2.min.js"></script>
<!-- Estilos -->
	<link rel="stylesheet" href="css/estilos.css">
	<link rel="stylesheet" type="text/css" href="../../../../css/gridSW_l.css"/>
	<link rel="stylesheet" type="text/css" href="../../../../css/icons/css/fontello.css">
<!-- bootstrap -->
	<link rel="stylesheet" type="text/css" href="../../../../css/bootstrap/css/bootstrap.min.css"/>
	<script type="text/javascript" src="../../../../css/bootstrap/js/bootstrap.bundle.min.js"></script>
</head>
<body>
	<div class="row">
		<div class="col-4">
			<button class="btn btn-light" onclick="if( confirm( 'Salir de esta pantalla?' ) ){ location.href='../../../../index.php?'; }">
					<img src="../../../../img/img_casadelasluces/Logo.png" width="100px">
			</button>
		</div>
		<div class="col-8">
			<h4 class="text-left">Tokens de Asistencias</h4><br><br>
		</div>
		<div class="col-4 text-center">
			Seleccionar sucursal :
			<?php echo $stores; ?>
		</div>
		<div class="col-4 text-center" id="user_combo">
			Seleccionar usuario :
		</div>
		<div class="col-4 text-center">
			<br>
			<button
				class="btn btn-success"
				onclick="getToken();"
			>
				Generar token de Asistencia
			</button>
		</div>
		<div class="col-3"></div>
		<div class="col-6">
			<h5 class="text-center">Token</h5>
			<input type="text" class="form-control" id="token">
		</div>
		<div class="col-3"></div>
	</div>
</body>

<script type="text/javascript">
	function getStoresUsers(){
		var store_id = $( '#store_id' ).val();
		$.ajax({ type : 'post', url : 'ajax/tokensGenerator.php', data : { tokenFl : 'getStoresUsers', store : store_id }, 
			success : function( dat ){ 
				buildUserCombo( dat ); 
			} 
		});
	}

	function buildUserCombo( dat ){
		$( '#user_id' ).remove();
		$( '#user_combo' ).append( dat );
	}

	function getToken(){
		var store_id = $( "#store_id" ).val();
		if( store_id == 0 ){
			alert( "Es necesario seleccionar una sucursal" ); 
			$( "#store_id" ).focus();
			return false;
		}
		var user_id = $( "#user_id" ).val();
		if( user_id == 0 ){
			alert( "Es necesario seleccionar un usario" ); 
			$( "#user_id" ).focus();
			return false;
		}
		//alert( store_id + '|' + user_id );
		$.ajax({ type : 'post', url : 'ajax/tokensGenerator.php', data : 
			{ tokenFl : 'getToken', store : store_id, user : user_id  }, 
			success : function( dat ){ 
				$( '#token' ).val( dat );
			} 
		});
	}
</script>
</html>