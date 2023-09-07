<?php
	include( '../../../../conect.php' );
	include( '../../../../conexionMysqli.php' );
	include( 'ajax/inventoryAdjustment.php' );
	$inventory = new inventoryAdjustment( $link, $sucursal_id );
?>
<!DOCTYPE html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Ajuste por conteo</title>
	<script language="JavaScript" src="../../../../js/jquery-1.10.2.min.js"></script>
	<link rel="stylesheet" type="text/css" href="../../../../css/bootstrap/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="../../../../css/icons/css/fontello.css">
	<!--<script language="Javascript" src="js/funcionesAjusteInvntario.js"></script>-->
	<!--incluimos la librería de Oscar-->
	<script type="text/javascript" src="../../../../js/passteriscoByNeutrix.js"></script>
	<link rel="stylesheet" type="text/css" href="css/styles.css">
</head>
<body>
	<div class="emergent" style="display: block;"><!-- style="display: block;" -->
	<br>
		<div class="row" style="max-height : 90%; overflow : auto;">
			<div class="col-2"></div>
			<div class="col-8 emergent_content" tabindex="1">
				<p align="center" style="font-size:150%;padding:15px;">Pantalla de Igualación de inventario Producto e inventario Proveedor Producto</p>
				<p align="left" style="font-size:120%;padding:15px;">
					Selecciona Sucursal
				</p>
				<div class="row">
					<div class="col-2"></div>
					<div class="col-8">
						<?php echo $inventory->getStores( $sucursal_id, "change_warehouse_combo( this )", null );?>
					</div>
				</div>
				<p align="left" style="font-size:120%;padding:15px;">
					Selecciona almacén
				</p>
				<div class="row">
					<div class="col-2"></div>
					<div class="col-8">
						<?php echo $inventory->getStoreWharehouses( ( $sucursal_id == -1 ? -100 : $sucursal_id ) );?>
					</div>
				</div>
<!-- implementacion Oscar 2023 para mnostrar maquilados o productos normales -->
				<div class="row">
					<p align="left" style="font-size:120%;padding:15px;">
						Tipo de productos : 
					</p>
					<div class="col-2"></div>
					<div class="col-8">
						<select id="products_type" class="form-control">
							<option value="0">Productos Normales</option>
							<option value="1">Productos Maquilados</option>
						</select>
					</div>
				</div>
<!-- fin de cambio Oscar 2023 -->
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
				<br>
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

		var store_id =  $( '#config_store_id' ).val();
		if( warehouse_id == 0 ){
			alert( "Debes de Seleccionar un almacen válido!" );
			$( '#config_store_id' ).focus();
			return false;
		}

		var warehouse_id =  $( '#config_warehouse_id' ).val();
		if( warehouse_id == 0 ){
			alert( "Debes de Seleccionar un almacen válido!" );
			$( '#config_warehouse_id' ).focus();
			return false;
		}
		var maquiled =  $( '#products_type' ).val();/*implementacion Oscar 2023 para mnostrar maquilados o productos normales*/ 

	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/guardaAjuste.php',
			cache:false,
			data:{fl:'verifica_pass',clave:pass},
			success:function(dat){
				//alert( dat );
				if(dat!='ok'){
					alert("La contraseña del encargado es incorrecta!!!");
					$("#pass_enc").val('');
					$("#pass_enc_1").val('');
					$("#pass_enc_1").focus();
					return false;
				}else{
					location.href='index.php?store_id=' + store_id + '&warehouse_id=' + warehouse_id + '&maquiled=' + maquiled;/*implementacion Oscar 2023 para mnostrar maquilados o productos normales*/ 
				}
			}
		});
	}

	function change_warehouse_combo( obj ){
		var store = $( obj ).val();
		var url = "ajax/inventoryAdjustment.php?store_id=" + store;
		var response = ajaxR( url );
		$( "#config_warehouse_id" ).empty();
		$( "#config_warehouse_id" ).html( response );
	}
</script>

<style type="text/css">
	@media only screen and (max-width: 600px) {
 		* {
    		font-size: 90%;
 		}
	}
</style>