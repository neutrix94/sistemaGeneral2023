<?php
	include( '../../../../conect.php' );
	include( '../../../../conexionMysqli.php' );
	include( 'ajax/inventoryAdjustment.php' );
	$inventory = new inventoryAdjustment( $link, $sucursal_id );
?>
<!DOCTYPE html>
<head>
	<title>Ajuste por conteo</title>
	<script language="JavaScript" src="../../../../js/jquery-1.10.2.min.js"></script>
	<link rel="stylesheet" type="text/css" href="../../../../css/bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="../../../../css/icons/css/fontello.css">
	<!--<script language="Javascript" src="js/funcionesAjusteInvntario.js"></script>-->
	<!--incluimos la librería de Oscar-->
	<script type="text/javascript" src="../../../../js/passteriscoByNeutrix.js"></script>
	<link rel="stylesheet" type="text/css" href="css/AjusteInventarioStyles.css">
</head>
<body>
	<div class="emergent" style="display: block;"><!-- style="display: block;" -->
		<div class="row">
			<div class="col-8 emergent_content" tabindex="1">
				<p align="center" style="font-size:180%;padding:15px;">Pantalla de Ajuste Inventario 2023</p>
				<p align="left" style="font-size:110%px;padding:15px;">
					Selecciona almacén
				</p>
				<div class="row">
					<div class="col-2"></div>
					<div class="col-8">
						<?php echo $inventory->getStoreWharehouses();?>
					</div>
				</div><br>
				<p align="left" style="font-size:110%px;padding:15px;">
					Ingresa Contraseña de encargado:
				</p>
				<div class="row">
					<div class="col-2"></div>
					<div class="col-8">
						<input type="password" id="pass_enc" class="form-control">
						<br>
						<button 
							type="button" 
							class="btn btn-success form-control" 
							onclick="verifica_permiso();"
						>
							<i class="icon-ok-circle">Aceptar</i>
						</button>
						<br><br>
						<button 
							type="button" 
							class="btn btn-danger form-control" 
							onclick="redirect( 'home' );"
						>
							<i class="icon-cancel-circled">Cancelar y Salir</i>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>

<script type="text/javascript">
	
	function redirect( type ){
		switch( type ){
			case 'home' : 
				if( confirm( "Salir de esta pantalla?" ) )
					location.href="../../../../index.php?";
			break;
		}
	}
	function verifica_permiso(){
	//sacamos el valor de la contraseña
		var pass=$("#pass_enc").val();
		//alert( pass );
		var warehouse_id =  $( '#warehouse_id' ).val();
		if( warehouse_id == 0 ){
			alert( "Debes de Seleccionar un almacen válido!" );
			$( '#warehouse_id' ).focus();
			return false;
		}
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/guardaAjuste.php',
			cache:false,
			data:{fl:'verifica_pass',clave:pass},
			success:function(dat){
				if(dat!='ok'){
					alert("La contraseña del encargado es incorrecta!!!");
					$("#pass_enc").val('');
					$("#pass_enc_1").val('');
					$("#pass_enc_1").focus();
					return false;
				}else{
					location.href='index.php?warehouse_id=' + warehouse_id;
				}
			}
		});
	}
</script>
