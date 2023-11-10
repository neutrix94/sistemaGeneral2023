<?php
	include( '../../../../../config.inc.php' );
	include( '../../../../../conect.php' );
	include( '../../../../../conexionMysqli.php' );
	if( ! include( '../ajax/db.php' ) ){
		die( "No se incluyo la libreria bd.php" );
	}
	$stores = getStores( $link );
	//die( "Stores : " . $stores );
?>
<div class="row">
	<div class="col-6">
		<h4>Seleccionar Sucursal :</h4>
		<select class="form-control" id="store_id_combo" onchange="getWarehousesByStore();">
			<option value="0">-- Seleccionar --</option>
			<?php
				echo $stores;
			?>
		</select>
	</div>
	<div class="col-6">
		<h4>Seleccionar Almacen :</h4>
		<select class="form-control" id="warehouse_id_combo">
			<option value="0">-- Seleccionar --</option>
		</select>
	</div>
	<div class="col-4"></div>
	<div class="col-4 text-center">
		<button 
			class="btn btn-success"
			onclick="setAndBuildTansferList();"
		>
			<i>Continuar</i>
		</button>
	</div>
	<div class="col-4"></div>
</div>


<script type="text/javascript">
	function getWarehousesByStore(){
		var store_id = $( '#store_id_combo' ).val();//warehouse_id_combo
		var url = 'ajax/db.php?fl=getWarehousesByStore&store_id=' + store_id;
		var resp = ajaxR( url );
//alert( resp );
		$( '#warehouse_id_combo' ).empty();
		$( '#warehouse_id_combo' ).append( resp );
	}

	function setAndBuildTansferList(){
		var store_id_ax = $( '#store_id_combo' ).val();
		if( store_id_ax == 0 ){
			alert( "Debes de seleccionar una sucursal valida!" );
			return false;
		}
		var warehouse_id_ax = $( '#warehouse_id_combo' ).val();
		if( warehouse_id_ax == 0 ){
			alert( "Debes de seleccionar un almacen valido!" );
			return false;
		}
		localStorage.setItem( 'current_reception_store', store_id_ax );
		localStorage.setItem( 'current_reception_warehouse', warehouse_id_ax );
		getTransfersList();
		getResolutionBlocks();
		close_emergent();
	}
	function getTransfersList(){
		var url = "ajax/db.php?fl=getTransfersToReceive";
		url += "&store_id=" + localStorage.getItem( 'current_reception_store' );
		url += "&warehouse_id=" + localStorage.getItem( 'current_reception_warehouse' );
		//alert( url ); return false;
		var response = ajaxR( url );
		//if( response[0] == 'ok' ){
			$( '#transfers_list_content' ).empty(  );
			$( '#transfers_list_content' ).append( response );
		//}else{
		//	alert( "Error : " + response );
		//}
	}

	function getResolutionBlocks(){
		var url = "ajax/db.php?fl=getBlocksInResolution";
		url += "&store_id=" + localStorage.getItem( 'current_reception_store' );
		url += "&warehouse_id=" + localStorage.getItem( 'current_reception_warehouse' );
		var response = ajaxR( url ).split( '|' );
		if( response[0] == 'ok' ){
			$( '#blocks_resolution_list' ).empty(  );
			$( '#blocks_resolution_list' ).append( response[1] );
		}else{
			alert( "Error : " + response );
		}
	}

	function reset_store_and_warehouse(){
		localStorage.removeItem( 'current_reception_store' );
		localStorage.removeItem( 'current_reception_warehouse' );
		location.reload();
	}

	//getTransfersToReceive( $sucursal_id, $perfil_usuario, $link )
</script>
